<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Knowledge;
use App\Models\PendingKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Stopwords — kata umum yang diabaikan saat matching.
     */
    private const STOPWORDS = [
        'yang',
        'dan',
        'atau',
        'di',
        'ke',
        'dari',
        'untuk',
        'pada',
        'dengan',
        'adalah',
        'itu',
        'ini',
        'saya',
        'kamu',
        'anda',
        'apa',
        'siapa',
        'bagaimana',
        'kapan',
        'dimana',
        'kenapa',
        'mengapa',
        'apakah',
        'dong',
        'sih',
        'ya',
        'kah',
        'lah',
        'kok',
        'gimana',
        'gini',
        'gitu',
        'kak',
        'min',
        'bang',
        'pak',
        'bu',
        'mas',
        'mbak',
        'bro',
        'gan',
        'tolong',
        'mohon',
        'bisa',
        'boleh',
        'mau',
        'ingin',
        'pengen',
        'coba',
        'saja',
        'aja',
        'juga',
        'sudah',
        'udah',
        'belum',
        'lagi',
        'versi',
        'nya',
        'tuh',
        'deh',
        'kami',
        'kita',
        'mereka',
    ];

    /**
     * 🆕 Kata tanya — kalau ada, JANGAN anggap follow-up.
     */
    private const QUESTION_WORDS = [
        'berapa',
        'apa',
        'siapa',
        'kapan',
        'dimana',
        'mana',
        'jenis',
        'tipe',
        'type',
        'kategori',
        'merk',
        'merek',
        'brand',
        'terbanyak',
        'terbanyak',
        'paling',
        'top',
        'tertinggi',
        'terbesar',
        'terendah',
        'tersedikit',
        'statistik',
        'summary',
        'rekap',
        'total',
        'jumlah',
        'nilai',
        'harga',
        'distribusi',
    ];

    /**
     * 🆕 Whitelist follow-up — pesan harus mengandung salah satu ini.
     */
    private const FOLLOWUP_KEYWORDS = [
        'lanjut',
        'selanjutnya',
        'next',
        'sisanya',
        'berikutnya',
        'yang lainnya',
        'yang lain',
        'lainnya',
        'yang itu',
        'yang tadi',
        'yang td',
        'yang barusan',
        'detailnya',
        'hak milik',
        'milik',
        'sewa',
        'owned',
        'leased',
    ];

    /**
     * Masa berlaku context (menit).
     */
    private const CONTEXT_TTL = 10;

    /**
     * Halaman chat user (publik).
     */
    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();

        $history = Chat::where('session_id', $sessionId)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return view('chat.index', compact('history'));
    }

    /**
     * Terima pesan user → cari jawaban → simpan → balikin JSON.
     */
    public function send(Request $request)
    {
        $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $pesan = trim($request->input('pesan'));
        $sessionId = $request->session()->getId();

        // 🆕 Cek follow-up DULU
        $followUp = $this->handleFollowUp($pesan, $request);

        if ($followUp) {
            [$jawaban, $sumber] = $followUp;
            $file = null;
        } else {
            [$jawaban, $sumber, $file] = $this->cariJawaban($pesan);
        }

        // Kalau jawaban dari AI, simpan ke pending knowledge
        if ($sumber === 'ai' && strlen($jawaban) > 30) {
            $this->logPendingKnowledge($pesan, $jawaban);
        }

        $chat = Chat::create([
            'session_id' => $sessionId,
            'pesan' => $pesan,
            'jawaban' => $jawaban,
            'sumber' => $sumber,
            'waktu' => now(),
            'file_path' => $file['path'] ?? null,
            'file_name' => $file['name'] ?? null,
            'file_type' => $file['type'] ?? null,
            'file_size' => $file['size'] ?? null,
        ]);

        $fileResponse = null;
        if ($chat->file_path) {
            $fileResponse = [
                'url' => asset('storage/' . $chat->file_path),
                'name' => $chat->file_name,
                'type' => $chat->file_type,
                'size' => $chat->file_size,
                'category' => Knowledge::detectCategory($chat->file_type),
            ];
        }

        return response()->json([
            'status' => 'ok',
            'pesan' => $chat->pesan,
            'jawaban' => $chat->jawaban,
            'waktu' => $chat->waktu,
            'sumber' => $sumber,
            'file' => $fileResponse,
        ]);
    }

    // ============================================================
    // 🆕 FOLLOW-UP HANDLER
    // ============================================================

    private function handleFollowUp(string $pesan, Request $request): ?array
    {
        $lower = Str::lower(trim($pesan));

        // 🆕 Kalau ada kata tanya → BUKAN follow-up
        foreach (self::QUESTION_WORDS as $qw) {
            if (str_contains($lower, $qw)) {
                return null;
            }
        }

        // Ambil context dari session
        $ctx = $request->session()->get('last_query_context');

        if (!$ctx) {
            return null;
        }

        // Context kedaluwarsa?
        if (isset($ctx['time'])) {
            try {
                $ctxTime = \Carbon\Carbon::parse($ctx['time']);
                if (now()->diffInMinutes($ctxTime) > self::CONTEXT_TTL) {
                    $request->session()->forget('last_query_context');
                    return null;
                }
            } catch (\Exception $e) {
                $request->session()->forget('last_query_context');
                return null;
            }
        }

        // 🆕 Whitelist: harus mengandung salah satu FOLLOWUP_KEYWORDS
        $isFollowUp = false;
        foreach (self::FOLLOWUP_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                $isFollowUp = true;
                break;
            }
        }

        if (!$isFollowUp) {
            return null;
        }

        return $this->executeFollowUp($ctx, $request);
    }

    private function executeFollowUp(array $ctx, Request $request): ?array
    {
        $type = $ctx['type'] ?? null;
        $offset = $ctx['offset'] ?? 0;
        $limit = 10;

        return match ($type) {
            'asset_by_status',
            'list_asset_by_status' => $this->followUpAssetByStatus($ctx, $offset, $limit, $request),

            'asset_by_ownership' => $this->followUpAssetByOwnership($ctx, $offset, $limit, $request),

            'low_stock_consumable' => $this->followUpLowStockConsumable($ctx, $offset, $limit, $request),

            'overdue_loans' => $this->followUpOverdueLoans($ctx, $offset, $limit, $request),

            'active_loans' => $this->followUpActiveLoans($ctx, $offset, $limit, $request),

            'active_assignments' => $this->followUpActiveAssignments($ctx, $offset, $limit, $request),

            default => null,
        };
    }

    private function followUpAssetByStatus(array $ctx, int $offset, int $limit, Request $request): array
    {
        $status = $ctx['status'] ?? null;
        $category = $ctx['category'] ?? null;
        $brand = $ctx['brand'] ?? null;

        $q = \App\Models\Asset::with('category');

        if ($status)
            $q->where('status', $status);
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand)
            $q->where('brand', 'like', "%{$brand}%");

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} aset**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** aset:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}** aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 Follow-up: aset by ownership (hak milik / sewa).
     */
    private function followUpAssetByOwnership(array $ctx, int $offset, int $limit, Request $request): array
    {
        $ownership = $ctx['ownership'] ?? null;
        $category = $ctx['category'] ?? null;
        $brand = $ctx['brand'] ?? null;

        $q = \App\Models\Asset::with('category');

        if ($ownership)
            $q->where('ownership_type', $ownership);
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand)
            $q->where('brand', 'like', "%{$brand}%");

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} aset**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** aset:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}** aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    private function followUpLowStockConsumable(array $ctx, int $offset, int $limit, Request $request): array
    {
        $q = \App\Models\Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} konsumable**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total} konsumable** stok rendah:\n\n";
        foreach ($items as $c) {
            $jawaban .= "• {$c->name}: {$c->stock_available}/{$c->stock_minimum} {$c->unit}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    private function followUpOverdueLoans(array $ctx, int $offset, int $limit, Request $request): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} peminjaman terlambat**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** peminjaman terlambat:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            $jawaban .= " (jatuh tempo {$l->due_date?->diffForHumans()})\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveLoans(array $ctx, int $offset, int $limit, Request $request): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->with(['asset', 'user'])
            ->orderByDesc('loan_date');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} peminjaman aktif**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** peminjaman aktif:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            if ($l->due_date)
                $jawaban .= " (jatuh tempo {$l->due_date->format('d M Y')})";
            $jawaban .= "\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveAssignments(array $ctx, int $offset, int $limit, Request $request): array
    {
        $q = \App\Models\AssetAssignment::whereNull('returned_at')
            ->with(['asset', 'user'])
            ->orderByDesc('assigned_at');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $request->session()->forget('last_query_context');
            return ["Tidak ada data lagi. Total: **{$total} serah terima aktif**.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan **{$start}-{$end}** dari **{$total}** serah terima aktif:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->asset?->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->user?->name}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $request->session()->put('last_query_context', $ctx);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: **{$sisa}**. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $request->session()->forget('last_query_context');
        }

        return [$jawaban, 'database'];
    }

    // ============================================================
    // LOG PENDING KNOWLEDGE
    // ============================================================

    private function logPendingKnowledge(string $pesan, string $jawaban): void
    {
        $pesanNorm = Str::lower(trim($pesan));

        $existing = PendingKnowledge::whereRaw('LOWER(pesan_user) = ?', [$pesanNorm])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $existing->increment('frequency');
            $existing->jawaban_ai = $jawaban;

            if ($existing->frequency >= 5 && strlen($jawaban) > 50) {
                $existsInKnowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanNorm])->exists();

                if (!$existsInKnowledge) {
                    Knowledge::create([
                        'kata_kunci' => Str::limit($pesan, 255),
                        'jawaban' => $jawaban,
                    ]);

                    $existing->status = 'approved';

                    Log::info('Auto-approved pending knowledge', [
                        'pesan' => $pesan,
                        'frequency' => $existing->frequency,
                    ]);
                }
            }

            $existing->save();
        } else {
            PendingKnowledge::create([
                'pesan_user' => $pesan,
                'jawaban_ai' => $jawaban,
                'frequency' => 1,
                'status' => 'pending',
            ]);
        }
    }

    // ============================================================
    // CARI JAWABAN
    // ============================================================

    private function cariJawaban(string $pesan): array
    {
        // LAYER 0: Query Database via QueryRouter
        $dbAnswer = app(\App\Services\Query\QueryRouter::class)->tryAnswer($pesan);
        if ($dbAnswer) {
            return [$dbAnswer[0], 'database', null];
        }

        // Bersihkan pesan
        $pesanBersih = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', Str::lower($pesan));
        $pesanBersih = preg_replace('/\s+/', ' ', trim($pesanBersih));

        if (empty($pesanBersih)) {
            return [$this->tanyaOllama($pesan), 'ai', null];
        }

        // LAYER 1: Exact match
        $knowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanBersih])->first();

        if ($knowledge) {
            return [
                $knowledge->jawaban,
                'database',
                $this->extractFile($knowledge),
            ];
        }

        // LAYER 2: Fuzzy search
        $candidates = Knowledge::search($pesanBersih)->take(20)->get();
        $best = $this->pickBestMatch($candidates, $pesanBersih);

        if ($best) {
            return [
                $best->jawaban,
                'database',
                $this->extractFile($best),
            ];
        }

        // LAYER 3: Fallback Ollama
        return [$this->tanyaOllama($pesan), 'ai', null];
    }

    private function pickBestMatch($candidates, string $pesanBersih): ?Knowledge
    {
        if ($candidates->isEmpty())
            return null;

        $pesanWords = collect(explode(' ', $pesanBersih))
            ->filter(fn($w) => strlen($w) >= 3 && !in_array($w, self::STOPWORDS))
            ->values()
            ->all();

        if (empty($pesanWords))
            return null;

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $item) {
            $keyLower = Str::lower($item->kata_kunci);
            $keyWords = collect(explode(' ', $keyLower))
                ->filter(fn($w) => strlen($w) >= 3 && !in_array($w, self::STOPWORDS))
                ->values()
                ->all();

            if (empty($keyWords))
                continue;

            $matched = 0;
            foreach ($pesanWords as $pw) {
                foreach ($keyWords as $kw) {
                    if (
                        $pw === $kw ||
                        levenshtein($pw, $kw) <= 1 ||
                        str_contains($kw, $pw) ||
                        str_contains($pw, $kw)
                    ) {
                        $matched++;
                        break;
                    }
                }
            }

            $score = $matched / count($pesanWords);

            $lengthRatio = min(count($keyWords), count($pesanWords)) /
                max(count($keyWords), count($pesanWords));
            $score = $score * (0.7 + 0.3 * $lengthRatio);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        return $bestScore >= 0.6 ? $best : null;
    }

    private function extractFile(Knowledge $knowledge): ?array
    {
        if (!$knowledge->file_path)
            return null;

        return [
            'path' => $knowledge->file_path,
            'name' => $knowledge->file_name,
            'type' => $knowledge->file_type,
            'size' => $knowledge->file_size,
        ];
    }

    // ============================================================
    // OLLAMA
    // ============================================================

    private function tanyaOllama(string $pesan): string
    {
        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = trim(config('services.ollama.model', ''));
        $endpoint = $url . '/api/chat';

        Log::info('=== OLLAMA REQUEST ===', [
            'url' => $endpoint,
            'model' => $model,
            'pesan' => $pesan,
        ]);

        if (empty($model)) {
            Log::error('Ollama: model kosong. Cek .env OLLAMA_MODEL.');
            return 'Maaf, konfigurasi AI belum lengkap. Hubungi admin.';
        }

        try {
            $response = Http::timeout(config('services.ollama.timeout', 60))->post($endpoint, [
                'model' => $model,
                'stream' => false,
                'options' => [
                    'temperature' => 0.5,
                    'num_predict' => 256,
                ],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Kamu adalah SIS Assistant, asisten virtual PLN UBP Suralaya. "
                            . "SELALU jawab dalam Bahasa Indonesia. "
                            . "Jawab SINGKAT (maksimal 3-4 kalimat). "
                            . "LANGSUNG ke inti pertanyaan tanpa basa-basi. "
                            . "JANGAN mulai jawaban dengan sapaan 'Halo' kecuali user menyapa duluan. "
                            . "Kalau pertanyaan tidak jelas atau di luar topik, "
                            . "katakan dengan sopan bahwa kamu hanya bisa membantu seputar aplikasi di SIT. "
                            . "Jangan menyebut dirimu sebagai AI atau language model.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $pesan,
                    ],
                ],
            ]);

            Log::info('=== OLLAMA RESPONSE ===', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $jawaban = trim($data['message']['content'] ?? '');

                if ($jawaban !== '') {
                    return $jawaban;
                }

                Log::warning('Ollama: response kosong');
                return 'Maaf, saya belum bisa menjawab pertanyaan itu.';
            }

            $errorMsg = $response->json('error') ?? $response->body();

            if ($response->status() === 404) {
                Log::error("Ollama 404: {$errorMsg}. Cek nama model: '{$model}'");
                return 'Maaf, model AI belum tersedia. Hubungi admin.';
            }

            if ($response->status() === 500) {
                Log::error("Ollama 500: {$errorMsg}");
                return 'Maaf, model AI sedang error. Coba lagi nanti.';
            }

            Log::warning("Ollama non-200 ({$response->status()}): {$errorMsg}");
            return 'Maaf, saya belum bisa menjawab pertanyaan itu.';

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection error: ' . $e->getMessage());
            return 'Maaf, layanan AI tidak dapat dihubungi. Cek apakah Ollama berjalan.';
        } catch (\Exception $e) {
            Log::error('Ollama exception: ' . $e->getMessage());
            return 'Maaf, layanan AI sedang tidak tersedia. Coba lagi nanti.';
        }
    }
}
