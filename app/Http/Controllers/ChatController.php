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
        'saya',
        'kami',
        'kita',
        'mereka',
    ];

    /**
     * Halaman chat user (publik).
     * History diambil PER SESSION.
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

        [$jawaban, $sumber, $file] = $this->cariJawaban($pesan);

        // 🆕 Kalau jawaban dari AI, simpan ke pending knowledge (untuk review admin)
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

        // Response JSON — sertakan file kalau ada
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

    /**
     * 🆕 Simpan jawaban AI ke pending_knowledge untuk review.
     * Kalau sudah ada dengan pesan sama → increment frequency.
     */
    private function logPendingKnowledge(string $pesan, string $jawaban): void
    {
        // Normalize pesan biar "Halo" & "HALO" dianggap sama
        $pesanNorm = Str::lower(trim($pesan));

        // Cari pending yang mirip
        $existing = PendingKnowledge::whereRaw('LOWER(pesan_user) = ?', [$pesanNorm])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            // Sudah ada → increment frequency & update jawaban terbaru
            $existing->increment('frequency');
            $existing->jawaban_ai = $jawaban;

            // 🆕 Auto-approve kalau sering ditanya (> 5x) & jawaban cukup panjang
            if ($existing->frequency >= 5 && strlen($jawaban) > 50) {
                // Cek belum ada di knowledge
                $existsInKnowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanNorm])->exists();

                if (!$existsInKnowledge) {
                    Knowledge::create([
                        'kata_kunci' => Str::limit($pesan, 255),
                        'jawaban' => $jawaban,
                    ]);

                    $existing->status = 'approved';

                    \Log::info('Auto-approved pending knowledge', [
                        'pesan' => $pesan,
                        'frequency' => $existing->frequency,
                    ]);
                }
            }

            $existing->save();
        } else {
            // Belum ada → create baru
            PendingKnowledge::create([
                'pesan_user' => $pesan,
                'jawaban_ai' => $jawaban,
                'frequency' => 1,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Cari jawaban: knowledge dulu (exact → Scout fuzzy → rank), fallback ke Ollama.
     * Return: [jawaban, sumber, file]
     */
    private function cariJawaban(string $pesan): array
    {
        // Bersihkan tanda baca agar tokenisasi TNTSearch rapi
        $pesanBersih = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', Str::lower($pesan));
        $pesanBersih = preg_replace('/\s+/', ' ', trim($pesanBersih));

        if (empty($pesanBersih)) {
            return [$this->tanyaOllama($pesan), 'ai', null];
        }

        // ============================================================
        // LAYER 1: Exact match (paling cepat & akurat)
        // ============================================================
        $knowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanBersih])->first();

        if ($knowledge) {
            return [
                $knowledge->jawaban,
                'database',
                $this->extractFile($knowledge),
            ];
        }

        // ============================================================
        // LAYER 2: Fuzzy search via Scout + TNTSearch
        // Ambil top 20 kandidat, lalu pilih yang paling relevan
        // ============================================================
        $candidates = Knowledge::search($pesanBersih)->take(20)->get();

        $best = $this->pickBestMatch($candidates, $pesanBersih);

        if ($best) {
            return [
                $best->jawaban,
                'database',
                $this->extractFile($best),
            ];
        }

        // ============================================================
        // LAYER 3: Fallback ke Ollama
        // ============================================================
        return [$this->tanyaOllama($pesan), 'ai', null];
    }

    /**
     * Pilih knowledge yang paling relevan dari hasil Scout.
     * Skor = persentase kata penting pesan yang cocok dengan kata_kunci.
     * Typo-tolerant (levenshtein ≤ 1).
     *
     * Threshold: minimal 60% kata cocok → return, else null.
     */
    private function pickBestMatch($candidates, string $pesanBersih): ?Knowledge
    {
        if ($candidates->isEmpty())
            return null;

        // Ambil kata penting dari pesan user (buang stopword + kata pendek)
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

            // Hitung kata pesan yang cocok dengan kata kunci
            $matched = 0;
            foreach ($pesanWords as $pw) {
                foreach ($keyWords as $kw) {
                    if (
                        $pw === $kw ||
                        levenshtein($pw, $kw) <= 1 ||   // toleransi 1 typo
                        str_contains($kw, $pw) ||
                        str_contains($pw, $kw)
                    ) {
                        $matched++;
                        break;
                    }
                }
            }

            // Skor = persentase kata pesan yang cocok
            $score = $matched / count($pesanWords);

            // Bonus: kalau jumlah kata key ≈ jumlah kata pesan
            // (mencegah match ke knowledge yang terlalu pendek)
            $lengthRatio = min(count($keyWords), count($pesanWords)) /
                max(count($keyWords), count($pesanWords));
            $score = $score * (0.7 + 0.3 * $lengthRatio);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        // 🔥 THRESHOLD: minimal 60% kata cocok
        return $bestScore >= 0.6 ? $best : null;
    }

    /**
     * Helper: extract info file dari knowledge.
     */
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

    /**
     * Panggil Ollama pakai endpoint /api/chat.
     */
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
