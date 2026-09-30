<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Knowledge;
use App\Models\PendingKnowledge;
use App\Services\AutoLearningService;
use App\Services\ChatMemoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
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
        'nya',
        'sn',
        'serial',
        'hostname',
        'kode aset',
        'asset code',
        'detail',
        'info',
        'lengkap',
        'jelaskan',
        'dia',
        'beliau',
        'orang itu',
        'itu',
        'tadi',
        'pegang',
        'dipegang',
        'pemegang',
        'yang pakai',
    ];

    private const CONTEXT_TTL = 30;
    private const INTENT_CACHE_TTL = 3600;
    private const INTENT_TIMEOUT = 8;
    private const QA_TIMEOUT = 60;

    private const ASSET_KEYWORDS = [
        'aset',
        'asset',
        'laptop',
        'notebook',
        'komputer',
        'pc',
        'desktop',
        'monitor',
        'printer',
        'scanner',
        'server',
        'router',
        'switch',
        'proyektor',
        'projector',
        'ups',
        'harddisk',
        'ssd',
        'flashdisk',
        'konsumable',
        'consumable',
        'stok',
        'stock',
        'atk',
        'sn',
        'serial',
        'hostname',
        'asset code',
        'kode aset',
        'kode barang',
        'brand',
        'merek',
        'model',
        'tipe',
        'garansi',
        'warranty',
        'hak milik',
        'sewa',
        'lease',
        'owned',
        'leased',
        'rental',
        'pemegang',
        'pegang',
        'dipegang',
        'pemakai',
        'memakai',
        'dipegang siapa',
        'yang pakai',
        'yang pegang',
        'pinjam',
        'peminjaman',
        'loan',
        'maintenance',
        'perbaikan',
        'servis',
        'rusak',
        'hilang',
        'pensiun',
        'retired',
        'lokasi aset',
        'ruangan aset',
        'gedung aset',
        'serah terima',
        'assignment',
        'di-assign',
        'diassign',
    ];

    // ============================================================
    // 🆕 ROLE-BASED ACCESS HELPERS
    // ============================================================

    /**
     * Cek apakah user privileged (bisa lihat semua aset).
     */
    private function isPrivileged(): bool
    {
        $user = auth()->user();
        if (!$user)
            return false;

        $role = $user->role ?? null;
        return in_array($role, ['admin', 'support'], true);
    }

    /**
     * Scope query aset berdasarkan role.
     * - admin/support → semua aset
     * - user biasa → hanya aset yang dipegangnya
     */
    private function scopeAssetQuery($query)
    {
        if ($this->isPrivileged()) {
            return $query;
        }
        return $query->where('current_user_id', auth()->id());
    }

    /**
     * Cek apakah user boleh lihat aset tertentu.
     */
    private function canViewAsset($asset): bool
    {
        if ($this->isPrivileged()) {
            return true;
        }
        return (int) $asset->current_user_id === (int) auth()->id();
    }

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

    public function send(Request $request)
    {
        $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $pesan = trim($request->input('pesan'));
        $sessionId = $request->session()->getId();
        $requestId = (string) Str::uuid();

        $memory = new ChatMemoryService($sessionId);

        Log::info('chat.send.start', [
            'request_id' => $requestId,
            'session_id' => $sessionId,
            'pesan_len' => strlen($pesan),
            'user_id' => auth()->id(),
            'is_guest' => !auth()->check(),
            'is_privileged' => $this->isPrivileged(),
        ]);

        // GUARD 0: Guest yang tanya tentang aset → wajib login
        if (!auth()->check() && $this->isAssetQuery($pesan)) {
            $jawaban = "🔒 Maaf, untuk mengakses data aset kamu harus login terlebih dahulu.\n\n"
                . "Cara login:\n"
                . "1. Klik tombol Login di pojok kanan atas\n"
                . "2. Masukkan username & password SIAM kamu\n"
                . "3. Setelah login, tanyakan lagi ke saya 😊\n\n"
                . "_Kalau belum punya akun, hubungi IT Support._";
            $sumber = 'auth_guard';
            $file = null;

            Log::info('chat.send.guest_blocked', [
                'request_id' => $requestId,
                'pesan' => $pesan,
            ]);
        }
        // GUARD 1: Tolak intent tulis
        elseif ($this->isWriteIntent($pesan)) {
            $jawaban = '🔒 Maaf, saya hanya bisa membaca data. '
                . 'Untuk mengubah data, silakan gunakan menu Aset Management di SIAM.';
            $sumber = 'readonly_guard';
            $file = null;
        }
        // FLOW NORMAL
        else {
            $followUp = $this->handleFollowUp($pesan, $request, $memory);

            if ($followUp) {
                [$jawaban, $sumber] = $followUp;
                $file = null;
            } else {
                [$jawaban, $sumber, $file] = $this->cariJawaban($pesan, $memory, $requestId);
            }
        }

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

        Log::info('chat.send.done', [
            'request_id' => $requestId,
            'sumber' => $sumber,
            'jawaban_len' => strlen($jawaban),
        ]);

        return response()->json([
            'status' => 'ok',
            'pesan' => $chat->pesan,
            'jawaban' => $chat->jawaban,
            'waktu' => $chat->waktu,
            'sumber' => $sumber,
            'file' => $fileResponse,
        ]);
    }

    private function isAssetQuery(string $pesan): bool
    {
        $lower = Str::lower($pesan);

        if (preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*\b/i', $pesan)) {
            return true;
        }

        foreach (self::ASSET_KEYWORDS as $kw) {
            $pattern = '/\b' . preg_quote($kw, '/') . '\b/i';
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        $questionWords = ['berapa', 'jumlah', 'total', 'daftar', 'list', 'siapa', 'apa saja', 'tampilkan', 'lihat', 'cari'];
        $objectWords = ['aset', 'laptop', 'pc', 'komputer', 'printer', 'monitor', 'konsumable', 'stock', 'stok'];

        $hasQuestion = false;
        foreach ($questionWords as $qw) {
            if (str_contains($lower, $qw)) {
                $hasQuestion = true;
                break;
            }
        }

        $hasObject = false;
        foreach ($objectWords as $ow) {
            if (preg_match('/\b' . preg_quote($ow, '/') . '\b/i', $lower)) {
                $hasObject = true;
                break;
            }
        }

        return $hasQuestion && $hasObject;
    }

    private function isWriteIntent(string $pesan): bool
    {
        $lower = Str::lower($pesan);
        return (bool) preg_match(
            '/\b(hapus|delete|ubah|update|edit|ganti|tambah|create|insert|'
            . 'pindah|move|set|reset|hilangkan|buang|remove|drop|truncate)\b/i',
            $lower
        );
    }

    // ============================================================
    // FOLLOW-UP HANDLER
    // ============================================================

    private function handleFollowUp(string $pesan, Request $request, ChatMemoryService $memory): ?array
    {
        $lower = Str::lower(trim($pesan));

        // PRIORITAS 0: User + "pegang/punya/pakai"
        $userAssetsByName = $this->tryAnswerUserAssetsByName($pesan, $memory);
        if ($userAssetsByName) {
            if (isset($userAssetsByName[2]) && is_array($userAssetsByName[2])) {
                $memory->remember('user_assets', $userAssetsByName[2], self::CONTEXT_TTL);
            }
            return $userAssetsByName;
        }

        // PRIORITAS 1: SN/hostname BARU di pesan
        $snAnswer = $this->tryAnswerBySnPattern($pesan, $memory);
        if ($snAnswer) {
            return $snAnswer;
        }

        // PRIORITAS 1.5: Konsumable
        if ($this->isConsumableQuery($lower, $memory)) {
            $consumableAnswer = $this->tryAnswerConsumableByKeyword($lower, $memory);
            if ($consumableAnswer) {
                return $consumableAnswer;
            }
        }

        // PRIORITAS 2: Field query dari memory asset terakhir
        $field = $this->detectFieldQuery($lower);
        if ($field) {
            $hasUserNameInMessage = $this->detectUserNameInMessage($pesan);

            if (!$hasUserNameInMessage) {
                $lastAsset = $memory->recall('asset');
                if ($lastAsset && isset($lastAsset['asset_id'])) {
                    return $this->answerAssetField((int) $lastAsset['asset_id'], $field, $memory);
                }

                $userCtx = $memory->recall('user_assets');
                if ($userCtx && isset($userCtx['asset_id'])) {
                    return $this->answerAssetField((int) $userCtx['asset_id'], $field, $memory);
                }
            }
        }

        // PRIORITAS 3: Resolve reference
        $ref = $this->resolveReference($lower, $memory);
        if ($ref) {
            if (isset($ref['user_id']) && $this->matchAny($lower, ['aset', 'pegang', 'punya', 'pakai'])) {
                return $this->answerUserAssets((int) $ref['user_id'], $memory);
            }
            if (isset($ref['asset_id']) && $this->matchAny($lower, ['detail', 'info', 'lengkap'])) {
                return $this->answerAssetField((int) $ref['asset_id'], 'full', $memory);
            }
        }

        // PRIORITAS 3.5: Follow-up "di pegang siapa" dari top_asset_by_model
        $ctxTopAsset = $memory->recall('top_asset_by_model');
        if ($ctxTopAsset && $this->matchAny($lower, ['pegang', 'dipegang', 'pemegang', 'yang pakai', 'siapa yang pakai'])) {
            return $this->followUpTopAssetByModel($ctxTopAsset, 0, 10, $memory);
        }

        // PRIORITAS 3.6: Implicit user
        $implicitUser = $this->tryAnswerImplicitUser($pesan, $memory);
        if ($implicitUser) {
            return $implicitUser;
        }

        // PRIORITAS 4: Question words → bukan follow-up (kecuali ada field)
        foreach (self::QUESTION_WORDS as $qw) {
            if (str_contains($lower, $qw)) {
                if ($field)
                    break;
                return null;
            }
        }

        // PRIORITAS 5: Follow-up keyword
        $isFollowUp = false;
        foreach (self::FOLLOWUP_KEYWORDS as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                $isFollowUp = true;
                break;
            }
        }

        if (!$isFollowUp) {
            return null;
        }

        $ctx = $memory->recall('asset_by_status')
            ?? $memory->recall('list_asset_by_status')
            ?? $memory->recall('count_asset_by_status')
            ?? $memory->recall('asset_by_ownership')
            ?? $memory->recall('count_asset_by_ownership')
            ?? $memory->recall('user_assets')
            ?? $memory->recall('list_asset_by_user')
            ?? $memory->recall('top_asset_by_model')
            ?? $memory->recall('low_stock_consumable')
            ?? $memory->recall('overdue_loans')
            ?? $memory->recall('list_overdue_loans')
            ?? $memory->recall('active_loans')
            ?? $memory->recall('list_active_loans')
            ?? $memory->recall('active_assignments');

        if (!$ctx) {
            return null;
        }

        return $this->executeFollowUp($ctx, $request, $memory);
    }

    private function executeFollowUp(array $ctx, Request $request, ChatMemoryService $memory): ?array
    {
        $type = $ctx['_type'] ?? $ctx['type'] ?? null;
        $offset = (int) ($ctx['offset'] ?? 0);
        $limit = 10;

        Log::info('executeFollowUp', [
            'type_original' => $type,
            'offset' => $offset,
        ]);

        $type = match ($type) {
            'list_asset_by_status', 'count_asset_by_status' => 'asset_by_status',
            'list_asset_by_ownership', 'count_asset_by_ownership' => 'asset_by_ownership',
            'list_asset_by_user', 'count_asset_by_user' => 'user_assets',
            'list_overdue_loans' => 'overdue_loans',
            'list_active_loans' => 'active_loans',
            default => $type,
        };

        Log::info('executeFollowUp.normalized', ['type' => $type]);

        return match ($type) {
            'top_asset_by_model' => $this->followUpTopAssetByModel($ctx, $offset, $limit, $memory),
            'user_assets' => $this->followUpUserAssets($ctx, $offset, $limit, $memory),
            'asset_by_status' => $this->followUpAssetByStatus($ctx, $offset, $limit, $memory),
            'asset_by_ownership' => $this->followUpAssetByOwnership($ctx, $offset, $limit, $memory),
            'low_stock_consumable' => $this->followUpLowStockConsumable($ctx, $offset, $limit, $memory),
            'overdue_loans' => $this->followUpOverdueLoans($ctx, $offset, $limit, $memory),
            'active_loans' => $this->followUpActiveLoans($ctx, $offset, $limit, $memory),
            'active_assignments' => $this->followUpActiveAssignments($ctx, $offset, $limit, $memory),
            default => null,
        };
    }

    // ============================================================
    // CORE: CARI JAWABAN
    // ============================================================

    private function cariJawaban(string $pesan, ChatMemoryService $memory, string $requestId): array
    {
        $lower = Str::lower($pesan);

        // 1️⃣ SN/hostname regex
        $snAnswer = $this->tryAnswerBySnPattern($pesan, $memory);
        if ($snAnswer) {
            return $snAnswer;
        }

        // 2️⃣ User + field spesifik
        $userAssetAnswer = $this->tryAnswerUserAssetField($pesan, $memory);
        if ($userAssetAnswer) {
            return $userAssetAnswer;
        }

        // 3️⃣ User + "pegang apa"
        $userAssetsByName = $this->tryAnswerUserAssetsByName($pesan, $memory);
        if ($userAssetsByName) {
            if (isset($userAssetsByName[2]) && is_array($userAssetsByName[2])) {
                $memory->remember('user_assets', $userAssetsByName[2], self::CONTEXT_TTL);
            }
            return $userAssetsByName;
        }

        // 3.5 Konsumable
        if ($this->isConsumableQuery($lower, $memory)) {
            $consumableAnswer = $this->tryAnswerConsumableByKeyword($lower, $memory);
            if ($consumableAnswer) {
                return array_merge($consumableAnswer, [null]);
            }
        }

        // 4️⃣ OLLAMA INTENT PARSER
        $intentResult = $this->tryOllamaIntent($pesan, $memory, $requestId);
        if ($intentResult) {
            return $intentResult;
        }

        // 5️⃣ QueryRouter regex fallback
        try {
            $dbAnswer = app(\App\Services\Query\QueryRouter::class)->tryAnswer($pesan);
            if ($dbAnswer) {
                if (isset($dbAnswer[2]) && is_array($dbAnswer[2])) {
                    session()->put('last_query_context', $dbAnswer[2]);
                    $type = $dbAnswer[2]['type'] ?? 'query';
                    $memory->remember($type, $dbAnswer[2], self::CONTEXT_TTL);
                }

                if (preg_match('/\b(konsumable|consumable|konsumebel|habis pakai|daftar konsumable)\b/i', $lower)) {
                    $memory->remember('consumable_list', [
                        'type' => 'consumable_list',
                        '_type' => 'consumable_list',
                        'time' => now()->toDateTimeString(),
                    ], self::CONTEXT_TTL, 'Daftar konsumable');
                }

                return [$dbAnswer[0], 'database', null];
            }
        } catch (\Throwable $e) {
            Log::warning('QueryRouter error', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);
        }

        // 6️⃣ AutoLearning
        $learning = null;
        try {
            $learning = app(AutoLearningService::class);
            $learned = $learning->findAnswer($pesan);

            if ($learned) {
                $learned->increment('frequency');
                return [$learned->answer, 'learned', null];
            }
        } catch (\Throwable $e) {
            Log::warning('AutoLearning findAnswer error', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);
            $learning = app(AutoLearningService::class);
        }

        // 7️⃣ Knowledge
        $knowledgeAnswer = $this->tryKnowledge($pesan);
        if ($knowledgeAnswer) {
            return $knowledgeAnswer;
        }

        // 8️⃣ Ollama Q&A fallback
        $jawaban = $this->tanyaOllama($pesan);

        if (strlen($jawaban) >= 50 && !str_contains($jawaban, 'Maaf,') && $learning) {
            try {
                $learning->process($pesan, $jawaban, 'ai');
            } catch (\Throwable $e) {
                Log::warning('AutoLearning process error', [
                    'request_id' => $requestId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [$jawaban, 'ai', null];
    }

    private function tryOllamaIntent(string $pesan, ChatMemoryService $memory, string $requestId): ?array
    {
        $cacheKey = 'intent:v2:' . md5(Str::lower(trim($pesan)));

        try {
            $intent = Cache::remember($cacheKey, self::INTENT_CACHE_TTL, function () use ($pesan) {
                return app(\App\Services\Ai\OllamaIntentParser::class)->parse($pesan);
            });
        } catch (\Throwable $e) {
            Log::warning('OllamaIntentParser error', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (!$intent || ($intent['intent'] ?? 'other') === 'other') {
            return null;
        }

        try {
            $result = app(\App\Services\Ai\IntentExecutor::class)->execute($intent);
        } catch (\Throwable $e) {
            Log::warning('IntentExecutor error', [
                'request_id' => $requestId,
                'intent' => $intent['intent'] ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (!$result || !isset($result['answer'])) {
            return null;
        }

        $rawType = $result['context']['type'] ?? ($intent['intent'] ?? 'intent');
        $type = match ($rawType) {
            'list_asset_by_status', 'count_asset_by_status' => 'asset_by_status',
            'list_asset_by_ownership', 'count_asset_by_ownership' => 'asset_by_ownership',
            'list_asset_by_user', 'count_asset_by_user' => 'user_assets',
            default => $rawType,
        };

        if (isset($result['context']) && is_array($result['context'])) {
            $params = $intent['params'] ?? [];

            $context = array_merge($result['context'], [
                'type' => $type,
                '_type' => $type,
                'status' => $result['context']['status'] ?? $params['status'] ?? null,
                'category' => $result['context']['category'] ?? $params['category'] ?? null,
                'brand' => $result['context']['brand'] ?? $params['brand'] ?? null,
                'model' => $result['context']['model'] ?? $params['model'] ?? null,
                'user_id' => $result['context']['user_id'] ?? null,
                'user_name' => $result['context']['user_name'] ?? $params['user_name'] ?? null,
            ]);

            if (($context['category'] ?? null) === 'all') {
                $context['category'] = null;
            }

            $memory->remember($type, $context, self::CONTEXT_TTL);

            Log::info('intent.context_saved', [
                'request_id' => $requestId,
                'type' => $type,
                'status' => $context['status'] ?? null,
                'offset' => $context['offset'] ?? 0,
            ]);
        } else {
            $memory->remember($type, [
                'type' => $type,
                '_type' => $type,
                'intent' => $intent,
                'offset' => 0,
            ], self::CONTEXT_TTL);

            Log::info('intent.context_saved_minimal', [
                'request_id' => $requestId,
                'type' => $type,
            ]);
        }

        Log::info('intent.executed', [
            'request_id' => $requestId,
            'intent' => $intent['intent'] ?? null,
            'has_context' => isset($result['context']),
        ]);

        return [$result['answer'], 'database', null];
    }

    private function tryAnswerBySnPattern(string $pesan, ChatMemoryService $memory): ?array
    {
        if (!preg_match('/\b([A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*)\b/i', $pesan, $m)) {
            return null;
        }

        $identifier = strtoupper($m[1]);
        $blacklist = ['nya', 'ini', 'itu', 'apa', 'siapa', 'mana', 'berapa', 'yang'];

        if (in_array(strtolower($identifier), $blacklist, true)) {
            return null;
        }

        // 🆕 Scope by role
        $query = \App\Models\Asset::with(['category', 'currentUser', 'currentLocation'])
            ->where(function ($q) use ($identifier) {
                $q->where('hostname', 'like', "%{$identifier}%")
                    ->orWhere('serial_number', 'like', "%{$identifier}%")
                    ->orWhere('asset_code', 'like', "%{$identifier}%");
            });

        $query = $this->scopeAssetQuery($query);
        $asset = $query->first();

        if (!$asset) {
            // 🆕 User biasa cari aset yang bukan miliknya
            if (!$this->isPrivileged() && auth()->check()) {
                // Cek apakah aset ada sama sekali (untuk feedback)
                $exists = \App\Models\Asset::where(function ($q) use ($identifier) {
                    $q->where('hostname', 'like', "%{$identifier}%")
                        ->orWhere('serial_number', 'like', "%{$identifier}%")
                        ->orWhere('asset_code', 'like', "%{$identifier}%");
                })->exists();

                if ($exists) {
                    return [
                        "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.\n\n"
                        . "Aset `{$identifier}` ditemukan di sistem, tapi bukan milikmu.\n"
                        . "_Hubungi Admin atau Support kalau kamu butuh info ini._",
                        'database',
                    ];
                }
            }
            return null;
        }

        $memory->remember('asset', [
            'asset_id' => $asset->id,
            'serial_number' => $asset->serial_number,
            'hostname' => $asset->hostname,
        ], self::CONTEXT_TTL, "Aset {$asset->hostname}");

        $lower = Str::lower($pesan);
        if ($this->matchAny($lower, ['siapa', 'pegang', 'memegang', 'pakai', 'gunakan', 'dipegang', 'pemakai', 'pemegang', 'yang pakai'])) {
            return $this->answerWhoHoldsAsset($asset, $memory);
        }

        $field = $this->detectFieldQuery($lower);
        if ($field) {
            return $this->answerAssetField($asset->id, $field, $memory);
        }

        return $this->answerAssetShort($asset, $memory);
    }

    private function detectUserNameInMessage(string $pesan): bool
    {
        $cleanPesan = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $pesan);
        $words = explode(' ', $cleanPesan);

        for ($i = 0; $i < count($words) - 1; $i++) {
            $kandidat = $words[$i] . ' ' . $words[$i + 1];
            if (strlen($kandidat) < 5)
                continue;

            if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                return true;
            }
        }

        return false;
    }

    private function tryKnowledge(string $pesan): ?array
    {
        $pesanBersih = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', Str::lower($pesan));
        $pesanBersih = preg_replace('/\s+/', ' ', trim($pesanBersih));

        if (empty($pesanBersih)) {
            return null;
        }

        $knowledge = Knowledge::whereRaw('LOWER(kata_kunci) = ?', [$pesanBersih])->first();

        if ($knowledge) {
            return [
                $knowledge->jawaban,
                'database',
                $this->extractFile($knowledge),
            ];
        }

        $candidates = Knowledge::search($pesanBersih)->take(20)->get();
        $best = $this->pickBestMatch($candidates, $pesanBersih);

        if ($best) {
            return [
                $best->jawaban,
                'database',
                $this->extractFile($best),
            ];
        }

        return null;
    }

    // ============================================================
    // FOLLOW-UP METHOD IMPLEMENTATIONS
    // ============================================================

    private function followUpTopAssetByModel(array $ctx, int $offset, int $limit, ChatMemoryService $memory): ?array
    {
        $model = $ctx['model'] ?? null;
        if (!$model) {
            return null;
        }

        // 🆕 Scope by role
        $query = \App\Models\Asset::with(['currentUser', 'category', 'currentLocation'])
            ->where('model', 'like', "%{$model}%")
            ->whereHas('currentUser')
            ->limit(50);

        $query = $this->scopeAssetQuery($query);
        $assets = $query->get();

        if ($assets->isEmpty()) {
            return [
                "Tidak ada aset {$model} yang sedang dipegang siapa pun.",
                'database',
            ];
        }

        $grouped = [];
        foreach ($assets as $a) {
            $name = $a->currentUser?->name ?? 'Tidak diketahui';
            $grouped[$name][] = $a;
        }

        $total = $assets->count();
        $userCount = count($grouped);

        // 🆕 Header berbeda untuk user biasa
        $header = $this->isPrivileged()
            ? "👥 Pemegang aset {$model}"
            : "📋 Aset {$model} yang Anda pegang";

        $jawaban = "{$header}\n"
            . "({$total} unit" . ($this->isPrivileged() ? " dipegang oleh {$userCount} orang" : "") . "):\n\n";

        $i = 0;
        foreach ($grouped as $name => $items) {
            if ($i >= 10) {
                $jawaban .= "\n_... dan " . (count($grouped) - 10) . " orang lainnya._";
                break;
            }

            $jawaban .= "👤 {$name} — " . count($items) . " unit\n";
            foreach ($items as $a) {
                $jawaban .= "  • {$a->hostname}";
                if ($a->serial_number && $a->serial_number !== $a->hostname) {
                    $jawaban .= " (SN: `{$a->serial_number}`)";
                }
                $jawaban .= "\n";
            }
            $jawaban .= "\n";
            $i++;
        }

        return [trim($jawaban), 'database'];
    }

    private function followUpUserAssets(array $ctx, int $offset, int $limit, ChatMemoryService $memory): ?array
    {
        $userId = $ctx['user_id'] ?? null;
        $assetId = $ctx['asset_id'] ?? null;

        if (!$userId) {
            return null;
        }

        // 🆕 User biasa cuma boleh lihat asetnya sendiri
        if (!$this->isPrivileged() && (int) $userId !== (int) auth()->id()) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
            ];
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return null;
        }

        if ($assetId) {
            $asset = \App\Models\Asset::with(['category', 'currentLocation'])
                ->find($assetId);

            if ($asset && $this->canViewAsset($asset)) {
                $jawaban = "📋 {$asset->hostname}";
                if ($asset->serial_number && $asset->serial_number !== $asset->hostname) {
                    $jawaban .= " (SN: `{$asset->serial_number}`)";
                }
                $jawaban .= "\n\n"
                    . "• Brand/Model: {$asset->brand} {$asset->model}\n"
                    . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
                    . "• Status: {$asset->status}\n"
                    . "• Tanggal Beli: " . ($asset->purchase_date?->format('d M Y') ?? '-') . "\n"
                    . "• Garansi: " . ($asset->warranty_expire?->format('d M Y') ?? '-') . "\n";

                if ($asset->purchase_price) {
                    $jawaban .= "• Harga: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n";
                }

                $memory->remember('asset', [
                    'asset_id' => $asset->id,
                    'serial_number' => $asset->serial_number,
                    'hostname' => $asset->hostname,
                ], self::CONTEXT_TTL, "Aset {$asset->hostname}");

                return [$jawaban, 'database'];
            }
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return ["User {$user->name} sedang tidak memegang aset.", 'database'];
        }

        $jawaban = "👤 {$user->name} memegang {$assets->count()} aset:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->hostname}";
            if ($a->serial_number && $a->serial_number !== $a->hostname) {
                $jawaban .= " (SN: `{$a->serial_number}`)";
            }
            $jawaban .= "\n"
                . "  • Beli: " . ($a->purchase_date?->format('d M Y') ?? '-') . "\n"
                . "  • Status: {$a->status}\n\n";
        }

        return [trim($jawaban), 'database'];
    }

    private function detectFieldQuery(string $lower): ?string
    {
        $normalized = preg_replace('/nya\b/i', '', $lower);
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        $fieldMap = [
            'serial_number' => ['sn', 'serial', 'nomor seri', 'serial number', 'no seri', 'noseri', 's/n', 'kode seri'],
            'hostname' => ['hostname', 'host name', 'nama komputer', 'nama pc', 'nama laptop', 'nama device', 'nama perangkat', 'pc name', 'nama host'],
            'asset_code' => ['asset code', 'kode aset', 'kode barang', 'kode inventaris', 'no aset', 'no asset', 'barcode', 'id aset', 'id asset', 'nomor aset', 'kd aset'],
            'brand' => ['brand', 'merek', 'merk', 'vendor barang', 'pembuat', 'pabrikan', 'manufaktur'],
            'model' => ['model', 'tipe', 'type', 'seri laptop', 'seri pc', 'varian'],
            'status' => ['status', 'kondisi', 'keadaan', 'posisi aset', 'aktif atau tidak', 'bisa dipakai', 'ready ga', 'ready gak'],
            'ownership' => ['hak kepemilikan', 'kepemilikan', 'milik', 'sewa', 'rental', 'status kepemilikan', 'punya siapa', 'hak milik'],
            'location' => ['lokasi', 'ruangan', 'ruang', 'tempat', 'posisi', 'ditaruh mana', 'ada di mana', 'disimpan di mana', 'site', 'cabang', 'lantai'],
            'user' => ['pemegang', 'pengguna', 'user', 'pic', 'penanggung jawab', 'siapa yang bawa', 'dipakai siapa', 'yang pegang', 'dipegang siapa', 'karyawan mana'],
            'spec' => ['spek', 'spesifikasi', 'spec', 'ram', 'prosesor', 'processor', 'ssd', 'harddisk', 'hdd', 'vga', 'jeroan', 'dapur pacu'],
            'os' => ['os', 'sistem operasi', 'windows', 'linux', 'mac', 'macos', 'ubuntu', 'operating system', 'win 10', 'win 11'],
            'garansi' => ['garansi', 'warranty', 'masa garansi', 'expired garansi', 'garansi sampai kapan', 'abis garansi'],
            'tanggal_beli' => ['tanggal beli', 'tanggal pembelian', 'kapan dibeli', 'tgl beli', 'kapan pengadaan', 'nota beli', 'tgl pengadaan', 'tanggal berapa', 'dari tanggal', 'tanggal', 'tgl'],
            'tahun_beli' => ['tahun beli', 'tahun pembelian', 'tahun pengadaan', 'tahun berapa beli', 'tahun berapa dibeli', 'beli tahun berapa', 'tahun perolehan', 'dibeli tahun', 'dibeli kapan'],
            'full' => ['detail lengkap', 'semua info', 'info lengkap', 'full detail', 'semuanya', 'tampilkan semua', 'all info', 'selengkapnya', 'profil aset'],
            'assigned_at' => ['sejak kapan', 'semenjak kapan', 'semenjak', 'dari kapan', 'kapan dipegang', 'mulai kapan', 'kapan di-assign', 'kapan diassign', 'sejak dipegang', 'dari dipegang', 'kapan mulai', 'tgl serah terima', 'kapan dikasih ke', 'mulai pakai'],
            'returned_at' => ['kapan dikembalikan', 'kapan selesai', 'kapan return', 'kapan kembali', 'tgl pengembalian', 'kapan dibalikin', 'dibalikin kapan'],
            'riwayat' => ['riwayat lengkap', 'riwayat aset', 'history lengkap', 'history aset', 'rekam jejak', 'log aset', 'jurnal aset'],
            'semua_pemegang' => ['siapa saja yang pernah pegang', 'siapa aja yang pernah pegang', 'pernah dipegang siapa', 'daftar pemegang', 'semua pemegang', 'mantan pemegang', 'siapa aja usernya', 'list user', 'user terdahulu', 'siapa aja yang pernah pakai'],
            'durasi' => ['berapa lama dipegang', 'berapa lama dipakai', 'sudah berapa lama dipegang', 'lama dipegang', 'durasi pakai', 'lama pemakaian', 'berapa bulan dipakai', 'berapa tahun dipakai'],
            'loan_info' => ['kapan dipinjam', 'sedang dipinjam siapa', 'siapa yang minjam', 'siapa yang meminjam', 'dipinjam ke siapa', 'status pinjam', 'lagi dipinjam', 'peminjam'],
            'loan_history' => ['riwayat peminjaman', 'riwayat pinjam', 'history peminjaman', 'pernah dipinjam siapa', 'log pinjam', 'daftar peminjam'],
            'maintenance_last' => ['kapan terakhir diperbaiki', 'terakhir servis', 'terakhir maintenance', 'kapan terakhir rusak', 'terakhir diservis', 'kapan terakhir oprek'],
            'maintenance_count' => ['berapa kali rusak', 'berapa kali diperbaiki', 'berapa kali maintenance', 'jumlah perbaikan', 'frekuensi rusak', 'sering rusak ga', 'berapa kali masuk servis'],
            'maintenance_history' => ['riwayat perbaikan', 'riwayat maintenance', 'riwayat servis', 'history perbaikan', 'log servis', 'catatan perbaikan', 'pernah rusak apa aja'],
            'maintenance_cost' => ['biaya perbaikan', 'biaya servis', 'biaya maintenance', 'total biaya perbaikan', 'habis biaya berapa', 'biaya rusak', 'ongkos servis', 'pengeluaran maintenance'],
            'maintenance_tech' => ['siapa teknisi', 'teknisi yang perbaiki', 'siapa yang servis', 'siapa mekaniknya', 'vendor servisnya siapa', 'servis di mana'],
            'movement' => ['riwayat movement', 'riwayat perpindahan', 'pernah dipindah', 'riwayat pindah', 'mutasi aset', 'riwayat mutasi', 'pindah dari mana', 'log perpindahan'],
            'umur' => ['umur aset', 'umur laptop', 'berapa umur', 'usia aset', 'sudah berapa tahun', 'laptop tahun berapa', 'sudah tua belum'],
            'nilai_buku' => ['nilai buku', 'harga sekarang', 'nilai saat ini', 'nilai jual', 'penyusutan', 'depresiasi', 'sisa harga', 'harga pasaran', 'valuasi'],
            'harga' => ['harga', 'harga beli', 'harga aset', 'harga perolehan', 'harga barang', 'harga pembelian', 'harganya', 'brp harganya', 'berapa harga', 'price'],
            'bandingkan' => ['bandingkan dengan', 'bedanya dengan', 'perbandingan dengan', 'komparasi', 'bagusan mana', 'vs', 'mending mana'],
            'konsumable_stock' => ['stok konsumable', 'stok consumable', 'stok barang habis pakai', 'sisa stok', 'stok sisa berapa', 'masih ada berapa', 'ketersediaan barang', 'stok gudang'],
            'konsumable_last' => ['terakhir dipakai siapa', 'terakhir pakai', 'terakhir keluar', 'siapa yang terakhir ambil', 'terakhir diambil siapa', 'pengambilan terakhir'],
            'konsumable_history' => ['riwayat konsumable', 'riwayat consumable', 'riwayat transaksi konsumable', 'log konsumable', 'catatan keluar masuk barang', 'mutasi konsumable'],
            'vendor_assets' => ['aset dari vendor', 'vendor punya aset', 'aset vendor', 'vendor mana', 'beli di vendor mana', 'toko mana', 'distributor mana', 'supplier mana', 'dibeli dari'],
            'vendor_contact' => ['kontak vendor', 'telepon vendor', 'phone vendor', 'nomor vendor', 'no telp vendor', 'email vendor', 'hubungi vendor', 'cp vendor', 'contact person vendor'],
        ];

        foreach ($fieldMap as $field => $keywords) {
            foreach ($keywords as $kw) {
                if (
                    preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower) ||
                    preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $normalized)
                ) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function answerAssetField(int $assetId, string $field, ChatMemoryService $memory): array
    {
        $asset = \App\Models\Asset::with(['category', 'currentUser', 'currentLocation'])
            ->find($assetId);

        if (!$asset) {
            return ["Aset tidak ditemukan.", 'database'];
        }

        // 🆕 Guard: user biasa hanya boleh lihat aset miliknya
        if (!$this->canViewAsset($asset)) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
            ];
        }

        $memory->remember('asset', [
            'asset_id' => $asset->id,
            'serial_number' => $asset->serial_number,
            'hostname' => $asset->hostname,
        ], self::CONTEXT_TTL, "Aset {$asset->serial_number}");

        if ($field === 'full') {
            return [$this->formatFullDetail($asset), 'database'];
        }

        $header = "🔍 {$asset->serial_number}";
        if ($asset->hostname) {
            $header .= " ({$asset->hostname})";
        }
        $header .= "\n\n";

        $jawaban = match ($field) {
            'serial_number' => $header . "Serial Number: `{$asset->serial_number}`",
            'hostname' => $header . "Hostname: " . ($asset->hostname ? "`{$asset->hostname}`" : '*tidak ada*'),
            'asset_code' => $header . "Asset Code: " . ($asset->asset_code ? "`{$asset->asset_code}`" : '*tidak ada*'),
            'brand' => $header . "Brand: {$asset->brand}",
            'model' => $header . "Model: {$asset->model}",
            'status' => $header . "Status: {$asset->status}",
            'ownership' => $header . "Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa'),
            'location' => $header . "Lokasi: " . ($asset->currentLocation?->full_name ?? '-'),
            'user' => $header . "Pemegang: " . ($asset->currentUser?->name ?? '-'),
            'spec' => $header . "Spesifikasi:\n" . $this->formatSpec($asset->specification),
            'os' => $header . "OS: " . ($asset->os ?? '-') . "\nLisensi: " . ($asset->os_license ?? '-'),
            'garansi' => $header . "Garansi: " . ($asset->warranty_expire?->format('d M Y') ?? '-'),
            'tanggal_beli' => $header . "Tanggal Pembelian: " . ($asset->purchase_date?->format('d M Y') ?? '-'),
            'tahun_beli' => $header . "Tahun Pembelian: " . ($asset->purchase_date?->format('Y') ?? '*tidak tercatat*'),
            'assigned_at' => $header . $this->formatAssignedInfo($asset),
            'returned_at' => $header . $this->formatReturnedInfo($asset),
            'riwayat' => $header . $this->formatFullHistory($asset),
            'semua_pemegang' => $header . $this->formatAllHolders($asset),
            'durasi' => $header . $this->formatHoldingDuration($asset),
            'loan_info' => $header . $this->formatLoanInfo($asset),
            'loan_history' => $header . $this->formatLoanHistory($asset),
            'maintenance_last' => $header . $this->formatLastMaintenance($asset),
            'maintenance_count' => $header . $this->formatMaintenanceCount($asset),
            'maintenance_history' => $header . $this->formatMaintenanceHistory($asset),
            'maintenance_cost' => $header . $this->formatMaintenanceCost($asset),
            'maintenance_tech' => $header . $this->formatMaintenanceTechnician($asset),
            'movement' => $header . $this->formatMovementHistory($asset),
            'umur' => $header . $this->formatAssetAge($asset),
            'nilai_buku' => $header . $this->formatBookValue($asset),
            'harga' => $header . "Harga Beli: " . ($asset->purchase_price
                ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.')
                : '*tidak tercatat*'),
            default => $header . "Field {$field} tidak dikenali.",
        };

        return [$jawaban, 'database'];
    }

    private function formatSpec($spec): string
    {
        if (!$spec)
            return '-';
        if (is_string($spec))
            $spec = json_decode($spec, true);
        if (!is_array($spec) || empty($spec))
            return '-';

        $lines = [];
        foreach ($spec as $k => $v) {
            $lines[] = "  • " . ucfirst($k) . ": {$v}";
        }
        return implode("\n", $lines);
    }

    private function formatFullDetail($asset): string
    {
        $jawaban = "🔍 Detail Lengkap: {$asset->serial_number}";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n";

        $jawaban .= "📋 Identitas\n"
            . "• Serial Number: `{$asset->serial_number}`\n";
        if ($asset->hostname) {
            $jawaban .= "• Hostname: `{$asset->hostname}`\n";
        }
        if ($asset->asset_code) {
            $jawaban .= "• Asset Code: `{$asset->asset_code}`\n";
        }

        $jawaban .= "\n💻 Hardware\n"
            . "• Brand: {$asset->brand}\n"
            . "• Model: {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n";

        if ($asset->specification) {
            $spec = is_string($asset->specification) ? json_decode($asset->specification, true) : $asset->specification;
            if (is_array($spec)) {
                foreach ($spec as $k => $v) {
                    $jawaban .= "• " . ucfirst($k) . ": {$v}\n";
                }
            }
        }

        $jawaban .= "\n📊 Status\n"
            . "• Status: {$asset->status}\n"
            . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n"
            . "• Pemegang: " . ($asset->currentUser?->name ?? '-') . "\n"
            . "• Lokasi: " . ($asset->currentLocation?->full_name ?? '-') . "\n";

        if ($asset->purchase_date) {
            $jawaban .= "\n📅 Pembelian\n"
                . "• Tanggal: {$asset->purchase_date->format('d M Y')}\n";
            if ($asset->purchase_price) {
                $jawaban .= "• Harga: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n";
            }
            if ($asset->warranty_expire) {
                $jawaban .= "• Garansi: {$asset->warranty_expire->format('d M Y')}\n";
            }
        }
        $jawaban .= "\n" . $this->formatAssignedInfo($asset);

        return $jawaban;
    }

    private function formatAssignedInfo($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->with('user', 'assignedBy')
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return "📅 Info Assignment\nAset ini sedang tidak dipegang siapa pun.";
        }

        $userName = $assignment->user?->name ?? 'tidak diketahui';
        $assignedAt = $assignment->assigned_at?->format('d M Y') ?? '-';
        $daysAgo = $assignment->assigned_at?->diffForHumans() ?? '-';

        $jawaban = "📅 Info Assignment\n"
            . "• Dipinjamkan ke: {$userName}\n"
            . "• Sejak: {$assignedAt} ({$daysAgo})\n";

        if ($assignment->assignedBy) {
            $jawaban .= "• Di-assign oleh: {$assignment->assignedBy->name}\n";
        }

        if ($assignment->condition_on_assign) {
            $jawaban .= "• Kondisi saat assign: {$assignment->condition_on_assign}%\n";
        }

        if ($assignment->notes) {
            $jawaban .= "• Catatan: {$assignment->notes}\n";
        }

        return $jawaban;
    }

    private function formatReturnedInfo($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNotNull('returned_at')
            ->with('user')
            ->latest('returned_at')
            ->first();

        if (!$assignment) {
            return "📅 Info Pengembalian\nAset ini belum pernah dikembalikan.";
        }

        $userName = $assignment->user?->name ?? 'tidak diketahui';
        $returnedAt = $assignment->returned_at?->format('d M Y') ?? '-';
        $daysAgo = $assignment->returned_at?->diffForHumans() ?? '-';

        $jawaban = "📅 Info Pengembalian\n"
            . "• Dikembalikan oleh: {$userName}\n"
            . "• Pada: {$returnedAt} ({$daysAgo})\n";

        if ($assignment->condition_on_return) {
            $jawaban .= "• Kondisi saat kembali: {$assignment->condition_on_return}%\n";
        }

        return $jawaban;
    }

    private function formatFullHistory($asset): string
    {
        $jawaban = "📜 Riwayat Lengkap Aset\n\n";
        $assignments = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('assigned_at')
            ->get();

        if ($assignments->isNotEmpty()) {
            $jawaban .= "👤 Serah Terima ({$assignments->count()})\n";
            foreach ($assignments->take(5) as $a) {
                $status = $a->returned_at ? '✅ Kembali' : '🔵 Aktif';
                $jawaban .= "• {$a->user?->name} — {$a->assigned_at?->format('d M Y')} ({$status})\n";
            }
            if ($assignments->count() > 5) {
                $jawaban .= "• ... dan " . ($assignments->count() - 5) . " lainnya\n";
            }
            $jawaban .= "\n";
        }

        $loans = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('loan_date')
            ->get();

        if ($loans->isNotEmpty()) {
            $jawaban .= "📤 Peminjaman ({$loans->count()})\n";
            foreach ($loans->take(3) as $l) {
                $jawaban .= "• {$l->user?->name} — {$l->loan_date?->format('d M Y')} ({$l->status})\n";
            }
            if ($loans->count() > 3) {
                $jawaban .= "• ... dan " . ($loans->count() - 3) . " lainnya\n";
            }
            $jawaban .= "\n";
        }

        $maintenances = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->orderByDesc('start_date')
            ->get();

        if ($maintenances->isNotEmpty()) {
            $jawaban .= "🔧 Perbaikan ({$maintenances->count()})\n";
            foreach ($maintenances->take(3) as $m) {
                $jawaban .= "• {$m->type} — {$m->start_date?->format('d M Y')} ({$m->status})\n";
            }
            if ($maintenances->count() > 3) {
                $jawaban .= "• ... dan " . ($maintenances->count() - 3) . " lainnya\n";
            }
            $jawaban .= "\n";
        }

        if ($assignments->isEmpty() && $loans->isEmpty() && $maintenances->isEmpty()) {
            $jawaban .= "_Belum ada riwayat untuk aset ini._";
        }

        return $jawaban;
    }

    private function formatAllHolders($asset): string
    {
        $assignments = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('assigned_at')
            ->get();

        if ($assignments->isEmpty()) {
            return "Aset ini belum pernah di-assign ke user mana pun.";
        }

        $jawaban = "👥 Semua Pemegang Aset ({$assignments->count()}x):\n\n";
        foreach ($assignments as $i => $a) {
            $status = $a->returned_at
                ? "✅ Kembali: {$a->returned_at->format('d M Y')}"
                : "🔵 Sedang dipegang";
            $jawaban .= ($i + 1) . ". {$a->user?->name}\n"
                . "   • Assign: {$a->assigned_at?->format('d M Y')}\n"
                . "   • {$status}\n";

            if ($a->returned_at && $a->assigned_at) {
                $duration = $a->assigned_at->diffInDays($a->returned_at);
                $jawaban .= "   • Durasi: {$duration} hari\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatHoldingDuration($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->with('user')
            ->latest('assigned_at')
            ->first();

        if (!$assignment) {
            return "Aset ini sedang tidak dipegang siapa pun.";
        }

        $days = $assignment->assigned_at?->diffInDays(now()) ?? 0;
        $userName = $assignment->user?->name ?? '-';
        $assignedAt = $assignment->assigned_at?->format('d M Y') ?? '-';

        $jawaban = "⏱️ Durasi Pemakaian\n"
            . "• Dipegang oleh: {$userName}\n"
            . "• Sejak: {$assignedAt}\n"
            . "• Sudah: {$days} hari";

        if ($days > 365) {
            $years = floor($days / 365);
            $months = floor(($days % 365) / 30);
            $jawaban .= " ({$years} tahun {$months} bulan)";
        } elseif ($days > 30) {
            $months = floor($days / 30);
            $jawaban .= " ({$months} bulan)";
        }

        return $jawaban;
    }

    private function formatLoanInfo($asset): string
    {
        $loan = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->whereIn('status', ['borrowed', 'approved', 'overdue'])
            ->with('user')
            ->latest('loan_date')
            ->first();

        if (!$loan) {
            return "Aset ini sedang tidak dipinjam siapa pun.";
        }

        $userName = $loan->user?->name ?? '-';
        $loanDate = $loan->loan_date?->format('d M Y') ?? '-';
        $dueDate = $loan->due_date?->format('d M Y') ?? '-';
        $daysLeft = $loan->due_date?->diffInDays(now()) ?? 0;
        $isOverdue = $loan->due_date && $loan->due_date->isPast();

        $jawaban = "📤 Info Peminjaman\n"
            . "• Dipinjam oleh: {$userName}\n"
            . "• Tanggal pinjam: {$loanDate}\n"
            . "• Jatuh tempo: {$dueDate}\n";

        if ($isOverdue) {
            $jawaban .= "• ⚠️ Terlambat {$daysLeft} hari!\n";
        } else {
            $jawaban .= "• Sisa waktu: {$daysLeft} hari\n";
        }

        if ($loan->purpose) {
            $jawaban .= "• Keperluan: {$loan->purpose}\n";
        }

        return $jawaban;
    }

    private function formatLoanHistory($asset): string
    {
        $loans = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->with('user')
            ->orderByDesc('loan_date')
            ->get();

        if ($loans->isEmpty()) {
            return "Aset ini belum pernah dipinjam.";
        }

        $jawaban = "📤 Riwayat Peminjaman ({$loans->count()}x):\n\n";
        foreach ($loans as $i => $l) {
            $status = match ($l->status) {
                'returned' => '✅ Kembali',
                'borrowed' => '🔵 Dipinjam',
                'overdue' => '🔴 Terlambat',
                'approved' => '🟡 Disetujui',
                default => $l->status,
            };

            $jawaban .= ($i + 1) . ". {$l->user?->name}\n"
                . "   • Pinjam: {$l->loan_date?->format('d M Y')}\n";

            if ($l->returned_at) {
                $jawaban .= "   • Kembali: {$l->returned_at->format('d M Y')}\n";
            } else {
                $jawaban .= "   • Jatuh tempo: {$l->due_date?->format('d M Y')}\n";
            }

            $jawaban .= "   • Status: {$status}\n\n";
        }

        return trim($jawaban);
    }

    private function formatLastMaintenance($asset): string
    {
        $m = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->latest('start_date')
            ->first();

        if (!$m) {
            return "Aset ini belum pernah diperbaiki.";
        }

        $jawaban = "🔧 Perbaikan Terakhir\n"
            . "• Tanggal: {$m->start_date?->format('d M Y')}\n"
            . "• Jenis: {$m->type}\n"
            . "• Masalah: {$m->issue}\n"
            . "• Status: {$m->status}\n";

        if ($m->technician) {
            $jawaban .= "• Teknisi: {$m->technician}\n";
        }
        if ($m->cost) {
            $jawaban .= "• Biaya: Rp " . number_format($m->cost, 0, ',', '.') . "\n";
        }

        return $jawaban;
    }

    private function formatMaintenanceCount($asset): string
    {
        $total = \App\Models\AssetMaintenance::where('asset_id', $asset->id)->count();
        $done = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->where('status', 'done')->count();
        $ongoing = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereIn('status', ['open', 'in_progress'])->count();

        $byType = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $jawaban = "🔧 Jumlah Perbaikan\n"
            . "• Total: {$total}x\n"
            . "• Selesai: {$done}x\n"
            . "• Sedang berjalan: {$ongoing}x\n";

        if ($byType->isNotEmpty()) {
            $jawaban .= "\nPer Jenis:\n";
            foreach ($byType as $type => $count) {
                $jawaban .= "• {$type}: {$count}x\n";
            }
        }

        return $jawaban;
    }

    private function formatMaintenanceHistory($asset): string
    {
        $maintenances = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->with('vendor')
            ->orderByDesc('start_date')
            ->get();

        if ($maintenances->isEmpty()) {
            return "Aset ini belum pernah diperbaiki.";
        }

        $jawaban = "🔧 Riwayat Perbaikan ({$maintenances->count()}x):\n\n";
        foreach ($maintenances as $i => $m) {
            $jawaban .= ($i + 1) . ". {$m->issue}\n"
                . "   • Tanggal: {$m->start_date?->format('d M Y')}\n"
                . "   • Jenis: {$m->type}\n"
                . "   • Status: {$m->status}\n";

            if ($m->action) {
                $jawaban .= "   • Tindakan: {$m->action}\n";
            }
            if ($m->technician) {
                $jawaban .= "   • Teknisi: {$m->technician}\n";
            }
            if ($m->cost) {
                $jawaban .= "   • Biaya: Rp " . number_format($m->cost, 0, ',', '.') . "\n";
            }
            if ($m->vendor) {
                $jawaban .= "   • Vendor: {$m->vendor->name}\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatMaintenanceCost($asset): string
    {
        $total = \App\Models\AssetMaintenance::where('asset_id', $asset->id)->sum('cost') ?? 0;
        $count = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereNotNull('cost')->where('cost', '>', 0)->count();

        $jawaban = "💰 Biaya Perbaikan\n"
            . "• Total biaya: Rp " . number_format($total, 0, ',', '.') . "\n"
            . "• Jumlah perbaikan berbiaya: {$count}x\n";

        if ($count > 0) {
            $avg = $total / $count;
            $jawaban .= "• Rata-rata: Rp " . number_format($avg, 0, ',', '.') . "\n";
        }

        return $jawaban;
    }

    private function formatMaintenanceTechnician($asset): string
    {
        $techs = \App\Models\AssetMaintenance::where('asset_id', $asset->id)
            ->whereNotNull('technician')
            ->select('technician', \DB::raw('COUNT(*) as total'))
            ->groupBy('technician')
            ->orderByDesc('total')
            ->get();

        if ($techs->isEmpty()) {
            return "Belum ada data teknisi untuk aset ini.";
        }

        $jawaban = "👨‍🔧 Teknisi yang Pernah Perbaiki:\n";
        foreach ($techs as $i => $t) {
            $jawaban .= ($i + 1) . ". {$t->technician} — {$t->total}x\n";
        }

        return trim($jawaban);
    }

    private function formatMovementHistory($asset): string
    {
        $movements = \App\Models\AssetMovement::where('asset_id', $asset->id)
            ->with(['fromLocation', 'toLocation', 'movable'])
            ->orderByDesc('moved_at')
            ->get();

        if ($movements->isEmpty()) {
            return "Aset ini belum pernah dipindahkan.";
        }

        $jawaban = "🚚 Riwayat Perpindahan ({$movements->count()}x):\n\n";
        foreach ($movements as $i => $m) {
            $from = $m->fromLocation?->full_name ?? '-';
            $to = $m->toLocation?->full_name ?? '-';

            $jawaban .= ($i + 1) . ". {$m->type}\n"
                . "   • Tanggal: {$m->moved_at?->format('d M Y')}\n";

            if ($from !== '-' || $to !== '-') {
                $jawaban .= "   • Dari: {$from}\n"
                    . "   • Ke: {$to}\n";
            }

            if ($m->notes) {
                $jawaban .= "   • Catatan: {$m->notes}\n";
            }
            $jawaban .= "\n";
        }

        return trim($jawaban);
    }

    private function formatAssetAge($asset): string
    {
        if (!$asset->purchase_date) {
            return "Tanggal pembelian aset ini tidak tercatat.";
        }

        $days = $asset->purchase_date->diffInDays(now());
        $years = floor($days / 365);
        $months = floor(($days % 365) / 30);

        $jawaban = "🎂 Umur Aset\n"
            . "• Tanggal beli: {$asset->purchase_date->format('d M Y')}\n"
            . "• Umur: {$years} tahun {$months} bulan ({$days} hari)\n";

        if ($asset->warranty_expire) {
            $sisaGaransi = $asset->warranty_expire->diffInDays(now(), false);
            if ($sisaGaransi > 0) {
                $jawaban .= "• Garansi sisa: {$sisaGaransi} hari\n";
            } else {
                $jawaban .= "• Garansi: sudah habis (" . abs($sisaGaransi) . " hari lalu)\n";
            }
        }

        return $jawaban;
    }

    private function formatBookValue($asset): string
    {
        if (!$asset->purchase_price || !$asset->purchase_date) {
            return "Data harga atau tanggal pembelian aset ini tidak lengkap.";
        }

        $price = $asset->purchase_price;
        $years = $asset->purchase_date->diffInYears(now());
        $depresiasi = min(0.8 * $years, 0.9);
        $currentValue = $price * (1 - $depresiasi);

        $jawaban = "💵 Nilai Buku Aset\n"
            . "• Harga beli: Rp " . number_format($price, 0, ',', '.') . "\n"
            . "• Umur: {$years} tahun\n"
            . "• Depresiasi: " . round($depresiasi * 100) . "%\n"
            . "• Nilai sekarang: Rp " . number_format($currentValue, 0, ',', '.') . "\n\n"
            . "_Estimasi berdasarkan depresiasi 20%/tahun._";

        return $jawaban;
    }

    private function resolveReference(string $lower, ChatMemoryService $memory): ?array
    {
        if (preg_match('/\b(dia|beliau|orang itu)\b/', $lower)) {
            return $memory->recall('user');
        }

        if (preg_match('/\b(yang tadi|yang td|yang barusan|yang itu)\b/', $lower)) {
            return $memory->recall();
        }

        return null;
    }

    private function answerUserAssets(int $userId, ChatMemoryService $memory): array
    {
        // 🆕 Guard: user biasa hanya boleh lihat asetnya sendiri
        if (!$this->isPrivileged() && (int) $userId !== (int) auth()->id()) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
            ];
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return ["User tidak ditemukan.", 'database'];
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return ["User {$user->name} sedang tidak memegang aset.", 'database'];
        }

        $memory->remember('user', [
            'user_id' => $user->id,
            'name' => $user->name,
            'position' => $user->position,
        ], self::CONTEXT_TTL, "User {$user->name}");

        if ($assets->count() === 1) {
            $a = $assets->first();
            $memory->remember('asset', [
                'asset_id' => $a->id,
                'serial_number' => $a->serial_number,
                'hostname' => $a->hostname,
            ], self::CONTEXT_TTL, "Aset {$a->serial_number}");
        }

        $jawaban = "👤 {$user->name} memegang {$assets->count()} aset:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->hostname}";
            if ($a->serial_number && $a->serial_number !== $a->hostname) {
                $jawaban .= " (SN: `{$a->serial_number}`)";
            }
            $jawaban .= "\n  {$a->brand} {$a->model}\n";
            if ($a->category) {
                $jawaban .= "  Kategori: {$a->category->name}\n";
            }
            $jawaban .= "\n";
        }

        return [trim($jawaban), 'database'];
    }

    private function answerWhoHoldsAsset($asset, ChatMemoryService $memory): array
    {
        // 🆕 Guard
        if (!$this->canViewAsset($asset)) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
            ];
        }

        $header = "🔍 {$asset->hostname}";
        if ($asset->serial_number && $asset->serial_number !== $asset->hostname) {
            $header .= "\nSN: `{$asset->serial_number}`";
        }
        $header .= "\n\n";

        $jawaban = $header
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Status: {$asset->status}\n";

        if ($asset->currentUser) {
            $jawaban .= "\n👤 Pemegang: {$asset->currentUser->name}";
            if ($asset->currentUser->position) {
                $jawaban .= " ({$asset->currentUser->position})";
            }
            $jawaban .= "\n";
        } else {
            $jawaban .= "\n👤 Pemegang: *tidak ada* (aset tersedia)\n";
        }

        if ($asset->currentLocation) {
            $jawaban .= "📍 Lokasi: {$asset->currentLocation->full_name}";
        }

        return [$jawaban, 'database'];
    }

    private function answerAssetShort($asset, ChatMemoryService $memory): array
    {
        // 🆕 Guard
        if (!$this->canViewAsset($asset)) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
            ];
        }

        $header = "🔍 {$asset->hostname}";
        if ($asset->serial_number && $asset->serial_number !== $asset->hostname) {
            $header .= "\nSN: `{$asset->serial_number}`";
        }
        $header .= "\n\n";

        $jawaban = $header
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Status: {$asset->status}\n"
            . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n";

        if ($asset->currentUser) {
            $jawaban .= "\n👤 Pemegang saat ini: {$asset->currentUser->name}";
            if ($asset->currentUser->position) {
                $jawaban .= " ({$asset->currentUser->position})";
            }
        } else {
            $jawaban .= "\n👤 Pemegang saat ini: *tidak ada* (aset tersedia)";
        }

        if ($asset->currentLocation) {
            $jawaban .= "\n📍 Lokasi: {$asset->currentLocation->full_name}";
        }

        return [$jawaban, 'database'];
    }

    private function tryAnswerUserAssetsByName(string $pesan, ChatMemoryService $memory): ?array
    {
        $lower = Str::lower($pesan);

        $skipPatterns = [
            'siapa yang pakai',
            'siapa saja yang pakai',
            'siapa aja yang pakai',
            'top user',
            'top pegawai',
            'paling banyak pegang',
            'aset apa saja',
            'laptop apa saja',
        ];
        foreach ($skipPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return null;
            }
        }

        if (!$this->matchAny($lower, ['pegang', 'memegang', 'punya', 'pakai', 'gunakan', 'dipegang', 'pinjam', 'dipinjam'])) {
            return null;
        }

        $userName = null;

        if (
            preg_match(
                '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\s+(?:pegang|memegang|punya|pakai|gunakan|dipegang|menggunakan|pinjam|dipinjam)/i',
                $pesan,
                $m
            )
        ) {
            $kandidat = trim($m[1]);
            if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                $userName = $kandidat;
            }
        }

        if (!$userName) {
            $words = preg_split('/\s+/', $pesan);
            for ($i = 0; $i < count($words) - 1; $i++) {
                $kandidat = $words[$i] . ' ' . $words[$i + 1];
                if (strlen($kandidat) < 5)
                    continue;
                if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                    $userName = $kandidat;
                    break;
                }
            }
        }

        if (!$userName) {
            foreach (preg_split('/\s+/', $pesan) as $word) {
                if (strlen($word) < 4)
                    continue;
                if (in_array(strtolower($word), ['yang', 'aset', 'laptop', 'pegang', 'punya', 'pakai', 'siapa', 'pinjam'], true)) {
                    continue;
                }
                if (\App\Models\User::where('name', 'like', "%{$word}%")->exists()) {
                    $userName = $word;
                    break;
                }
            }
        }

        if (!$userName || strlen($userName) < 3) {
            return null;
        }

        $user = \App\Models\User::where('name', 'like', "%{$userName}%")->first();
        if (!$user) {
            return null;
        }

        // 🆕 Guard: user biasa hanya boleh cari asetnya sendiri
        if (!$this->isPrivileged() && (int) $user->id !== (int) auth()->id()) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.\n\n"
                . "_Untuk melihat aset user lain, hubungi Admin atau Support._",
                'database',
                null,
            ];
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return [
                "User {$user->name} sedang tidak memegang aset.",
                'database',
                null,
            ];
        }

        $field = $this->detectFieldQuery($lower);
        $statusFilter = null;

        if (str_contains($lower, 'dipinjam') || str_contains($lower, 'pinjam')) {
            $statusFilter = 'loaned';
        } elseif (str_contains($lower, 'dipakai') || str_contains($lower, 'digunakan')) {
            $statusFilter = 'in_use';
        } elseif (str_contains($lower, 'tersedia') || str_contains($lower, 'available')) {
            $statusFilter = 'available';
        } elseif (str_contains($lower, 'rusak') || str_contains($lower, 'perbaikan')) {
            $statusFilter = 'maintenance';
        }

        if ($statusFilter) {
            $assets = $assets->filter(fn($a) => $a->status === $statusFilter);
        }

        if ($statusFilter || $field) {
            $statusLabel = $statusFilter ? ucfirst($statusFilter) : null;
            $jawaban = "👤 {$user->name}";
            if ($statusFilter) {
                $jawaban .= " — aset {$statusLabel}";
            }
            $jawaban .= ":\n\n";

            if ($assets->isEmpty()) {
                return [
                    "User {$user->name} tidak memiliki aset dengan filter tersebut.",
                    'database',
                    null,
                ];
            }

            foreach ($assets as $a) {
                $jawaban .= "• {$a->hostname}";
                if ($a->serial_number && $a->serial_number !== $a->hostname) {
                    $jawaban .= " (SN: `{$a->serial_number}`)";
                }
                $jawaban .= "\n";

                if ($field) {
                    $fieldValue = $this->getFieldValue($a, $field);
                    $fieldLabel = $this->getFieldLabel($field);
                    $jawaban .= "  {$fieldLabel}: {$fieldValue}\n";
                } else {
                    $jawaban .= "  • Status: {$a->status}\n";
                    $jawaban .= "  • Brand/Model: {$a->brand} {$a->model}\n";
                }
                $jawaban .= "\n";
            }

            return [trim($jawaban), 'database', null];
        }

        $memory->forget('asset');

        $memory->remember('user', [
            'user_id' => $user->id,
            'name' => $user->name,
            'position' => $user->position,
        ], self::CONTEXT_TTL, "User {$user->name}");

        if ($assets->count() === 1) {
            $a = $assets->first();
            $memory->remember('asset', [
                'asset_id' => $a->id,
                'serial_number' => $a->serial_number,
                'hostname' => $a->hostname,
            ], self::CONTEXT_TTL, "Aset {$a->hostname}");
        }

        $jawaban = "👤 {$user->name} memegang {$assets->count()} aset:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->hostname}";
            if ($a->serial_number && $a->serial_number !== $a->hostname) {
                $jawaban .= " (SN: `{$a->serial_number}`)";
            }
            $jawaban .= "\n  {$a->brand} {$a->model}\n";
            if ($a->category) {
                $jawaban .= "  Kategori: {$a->category->name}\n";
            }
            $jawaban .= "\n";
        }

        return [
            trim($jawaban),
            'database',
            [
                'type' => 'user_assets',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'asset_id' => $assets->first()->id,
                'serial_number' => $assets->first()->serial_number,
                'hostname' => $assets->first()->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    private function getFieldValue($asset, string $field): string
    {
        return match ($field) {
            'serial_number' => $asset->serial_number ?? '-',
            'hostname' => $asset->hostname ?? '-',
            'asset_code' => $asset->asset_code ?? '-',
            'brand' => $asset->brand ?? '-',
            'model' => $asset->model ?? '-',
            'status' => $asset->status ?? '-',
            'ownership' => $asset->ownership_type === 'owned' ? 'Hak Milik' : 'Sewa',
            'location' => $asset->currentLocation?->full_name ?? '-',
            'user' => $asset->currentUser?->name ?? '-',
            'garansi' => $asset->warranty_expire?->format('d M Y') ?? '-',
            'tanggal_beli' => $asset->purchase_date?->format('d M Y') ?? '-',
            'tahun_beli' => $asset->purchase_date?->format('Y') ?? '-',
            'harga' => $asset->purchase_price
                ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.')
                : '-',
            'umur' => $asset->purchase_date
                ? floor($asset->purchase_date->diffInDays(now()) / 365) . ' tahun'
                : '-',
            'assigned_at' => $this->resolveAssignedAt($asset),
            'returned_at' => $this->resolveReturnedAt($asset),
            'durasi' => $this->resolveDuration($asset),
            'loan_info' => $this->resolveLoanInfo($asset),
            'riwayat' => $this->resolveRiwayat($asset),
            default => '-',
        };
    }

    private function isConsumableQuery(string $lower, ChatMemoryService $memory): bool
    {
        $consumableKeywords = [
            'konsumable',
            'consumable',
            'konsumebel',
            'consumebel',
            'kosumable',
            'barang habis pakai',
            'habis pakai',
            'atk',
            'alat tulis',
            'keyboard',
            'mouse',
            'tinta',
            'toner',
            'kertas',
            'headset',
            'charger',
            'kabel',
            'usb',
            'flashdisk',
            'flash disk',
            'hdd',
            'harddisk',
            'hard disk',
            'hardisk',
            'hardisk eksternal',
        ];

        $hasConsumableKeyword = false;
        foreach ($consumableKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                $hasConsumableKeyword = true;
                break;
            }
        }

        if (!$hasConsumableKeyword) {
            return false;
        }

        if ($memory->recall('consumable') || $memory->recall('consumable_list')) {
            return true;
        }

        if (!$memory->recall('asset')) {
            return true;
        }

        $assetOnlyKeywords = [
            'cpu',
            'ram',
            'vga',
            'prosesor',
            'processor',
            'gpu',
            'screen',
            'layar',
            'os',
            'windows',
            'linux',
            'macos',
            'spek',
            'spesifikasi',
            'spec',
            'jeroan',
            'dapur pacu',
            'garansi',
            'warranty',
            'pemegang',
            'dipegang',
            'siapa',
        ];
        foreach ($assetOnlyKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                return false;
            }
        }

        if (preg_match('/\b(stok|sisa|terpakai|dipakai|habis|jumlah|rendah|minimum|restock|masuk|keluar)\b/i', $lower)) {
            return true;
        }

        return false;
    }

    private function tryAnswerConsumableByKeyword(string $lower, ChatMemoryService $memory): ?array
    {
        $keywords = [
            'hdd' => ['hdd', 'harddisk', 'hard disk', 'hardisk'],
            'flashdisk' => ['flashdisk', 'flash disk', 'usb drive'],
            'keyboard' => ['keyboard', 'papan ketik'],
            'mouse' => ['mouse'],
            'headset' => ['headset', 'earphone', 'headphone'],
            'charger' => ['charger', 'adaptor', 'adapter'],
            'tinta' => ['tinta', 'ink'],
            'toner' => ['toner'],
            'kertas' => ['kertas', 'paper'],
            'kabel' => ['kabel', 'cable'],
        ];

        $matchedCategory = null;
        foreach ($keywords as $category => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/\b' . preg_quote($alias, '/') . '\b/i', $lower)) {
                    $matchedCategory = $category;
                    break 2;
                }
            }
        }

        if (!$matchedCategory) {
            return null;
        }

        try {
            $consumables = \App\Models\Consumable::where(function ($q) use ($keywords, $matchedCategory) {
                foreach ($keywords[$matchedCategory] as $alias) {
                    $q->orWhere('name', 'like', "%{$alias}%")
                        ->orWhere('model', 'like', "%{$alias}%")
                        ->orWhere('brand', 'like', "%{$alias}%");
                }
            })->get();
        } catch (\Throwable $e) {
            Log::warning('tryAnswerConsumableByKeyword error', ['error' => $e->getMessage()]);
            return null;
        }

        if ($consumables->isEmpty()) {
            return null;
        }

        $first = $consumables->first();
        $memory->remember('consumable', [
            'type' => 'consumable',
            '_type' => 'consumable',
            'consumable_id' => $first->id,
            'name' => $first->name,
            'time' => now()->toDateTimeString(),
        ], self::CONTEXT_TTL, "Consumable {$first->name}");

        if ($consumables->count() === 1) {
            return $this->formatConsumableAnswer($consumables->first(), $lower);
        }

        $jawaban = "📦 Ditemukan " . $consumables->count() . " konsumable:\n\n";
        foreach ($consumables as $c) {
            $stok = (int) ($c->stock_available ?? 0);
            $unit = $c->unit ?? 'pcs';
            $jawaban .= "• **{$c->name}**";
            if ($c->brand)
                $jawaban .= " ({$c->brand})";
            $jawaban .= " — Stok: {$stok} {$unit}\n";
        }

        return [$jawaban, 'database'];
    }

    private function formatConsumableAnswer($c, string $lower): array
    {
        $available = (int) ($c->stock_available ?? 0);
        $total = (int) ($c->stock_total ?? 0);
        $minimum = (int) ($c->stock_minimum ?? 0);
        $unit = $c->unit ?? 'pcs';
        $terpakai = max(0, $total - $available);

        $isTerpakai = (bool) preg_match('/\b(terpakai|dipakai|pakai|keluar|habis|used)\b/i', $lower);
        $isStok = (bool) preg_match('/\b(stok|stoknya|sisa|available|tersedia|ready|ada)\b/i', $lower);

        if ($isTerpakai) {
            $jawaban = "📦 **{$c->name}**\n\n"
                . "• Sudah terpakai: **{$terpakai} {$unit}**\n"
                . "• Stok tersedia: {$available} {$unit}\n"
                . "• Stok total: {$total} {$unit}";
            return [$jawaban, 'database'];
        }

        if ($isStok) {
            $jawaban = "📦 **{$c->name}**\n\n"
                . "• Stok tersedia: **{$available} {$unit}**\n"
                . "• Stok total: {$total} {$unit}\n"
                . "• Sudah terpakai: {$terpakai} {$unit}";
            if ($minimum > 0)
                $jawaban .= "\n• Minimum: {$minimum} {$unit}";
            if ($available <= $minimum && $minimum > 0) {
                $jawaban .= "\n\n⚠️ Stok rendah! Perlu segera restock.";
            }
            return [$jawaban, 'database'];
        }

        $jawaban = "📦 **{$c->name}**";
        if ($c->brand)
            $jawaban .= " ({$c->brand})";
        $jawaban .= "\n\n"
            . "• Stok tersedia: **{$available} {$unit}**\n"
            . "• Stok total: {$total} {$unit}\n"
            . "• Sudah terpakai: {$terpakai} {$unit}";
        if ($minimum > 0)
            $jawaban .= "\n• Minimum: {$minimum} {$unit}";

        return [$jawaban, 'database'];
    }

    private function resolveAssignedAt($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->latest('assigned_at')
            ->first();

        if (!$assignment || !$assignment->assigned_at) {
            return '-';
        }

        $days = $assignment->assigned_at->diffInDays(now());
        return $assignment->assigned_at->format('d M Y')
            . " (sekarang, {$days} hari)";
    }

    private function resolveReturnedAt($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->first();

        if (!$assignment || !$assignment->returned_at) {
            return 'belum pernah dikembalikan';
        }

        return $assignment->returned_at->format('d M Y');
    }

    private function resolveDuration($asset): string
    {
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->latest('assigned_at')
            ->first();

        if (!$assignment || !$assignment->assigned_at) {
            return '-';
        }

        $days = $assignment->assigned_at->diffInDays(now());

        if ($days > 365) {
            $years = floor($days / 365);
            $months = floor(($days % 365) / 30);
            return "{$years} tahun {$months} bulan ({$days} hari)";
        } elseif ($days > 30) {
            $months = floor($days / 30);
            return "{$months} bulan ({$days} hari)";
        }

        return "{$days} hari";
    }

    private function resolveLoanInfo($asset): string
    {
        $loan = \App\Models\AssetLoan::where('asset_id', $asset->id)
            ->whereIn('status', ['borrowed', 'approved', 'overdue'])
            ->latest('loan_date')
            ->first();

        if (!$loan) {
            return 'tidak sedang dipinjam';
        }

        $user = $loan->user?->name ?? '-';
        $due = $loan->due_date?->format('d M Y') ?? '-';
        return "Dipinjam {$user}, jatuh tempo {$due}";
    }

    private function resolveRiwayat($asset): string
    {
        $total = \App\Models\AssetAssignment::where('asset_id', $asset->id)->count();
        $aktif = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->whereNull('returned_at')->count();
        return "{$total}x assign ({$aktif} aktif)";
    }

    private function getFieldLabel(string $field): string
    {
        return match ($field) {
            'serial_number' => 'Serial Number',
            'hostname' => 'Hostname',
            'asset_code' => 'Asset Code',
            'brand' => 'Brand',
            'model' => 'Model',
            'status' => 'Status',
            'ownership' => 'Hak Kepemilikan',
            'location' => 'Lokasi',
            'user' => 'Pemegang',
            'garansi' => 'Garansi',
            'tanggal_beli' => 'Tanggal Beli',
            'tahun_beli' => 'Tahun Beli',
            'harga' => 'Harga',
            'umur' => 'Umur',
            'assigned_at' => 'Sejak',
            'returned_at' => 'Dikembalikan',
            'durasi' => 'Durasi Pemakaian',
            'loan_info' => 'Info Peminjaman',
            'riwayat' => 'Riwayat',
            default => ucfirst($field),
        };
    }

    private function tryAnswerImplicitUser(string $pesan, ChatMemoryService $memory): ?array
    {
        $lower = Str::lower(trim($pesan));

        if ($this->matchAny($lower, ['pegang', 'punya', 'pakai', 'dipegang', 'pinjam', 'loan', 'berapa', 'apa', 'siapa', 'kapan'])) {
            return null;
        }

        $words = preg_split('/\s+/', $lower);
        if (count($words) > 4) {
            return null;
        }

        foreach (preg_split('/\s+/', $pesan) as $word) {
            $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
            if (strlen($clean) < 4)
                continue;
            if (in_array(strtolower($clean), ['kalau', 'nya', 'yang', 'dan', 'atau', 'untuk', 'apa', 'siapa'], true)) {
                continue;
            }

            $user = \App\Models\User::where('name', 'like', "%{$clean}%")->first();
            if ($user) {
                // 🆕 Guard: user biasa hanya boleh lihat asetnya sendiri
                if (!$this->isPrivileged() && (int) $user->id !== (int) auth()->id()) {
                    continue;
                }
                return $this->answerUserAssets($user->id, $memory);
            }
        }

        return null;
    }

    private function followUpAssetByStatus(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $status = $ctx['status']
            ?? $ctx['params']['status']
            ?? $ctx['intent']['params']['status']
            ?? null;

        $category = $ctx['category']
            ?? $ctx['params']['category']
            ?? $ctx['intent']['params']['category']
            ?? null;

        $brand = $ctx['brand']
            ?? $ctx['params']['brand']
            ?? $ctx['intent']['params']['brand']
            ?? null;

        if ($category === 'all')
            $category = null;
        if ($brand === 'all')
            $brand = null;

        Log::info('followUpAssetByStatus.resolved', [
            'status' => $status,
            'category' => $category,
            'brand' => $brand,
        ]);

        if (!$status) {
            Log::warning('followUpAssetByStatus.no_status', ['ctx' => $ctx]);
            return ["_Maaf, saya kehilangan konteks pencarian sebelumnya. Coba ulangi pertanyaan awal._", 'database'];
        }

        $q = \App\Models\Asset::with('category');
        $q->where('status', $status);

        // 🆕 Scope by role
        $q = $this->scopeAssetQuery($q);

        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }

        if ($brand) {
            $q->where('brand', 'like', "%{$brand}%");
        }

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('asset_by_status');
            return ["Tidak ada data lagi. Total: {$total} aset.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        // 🆕 Header berbeda untuk user biasa
        $header = $this->isPrivileged()
            ? "Menampilkan {$start}-{$end} dari {$total} aset"
            : "📋 Aset Anda (menampilkan {$start}-{$end} dari {$total})";

        $jawaban = "{$header}:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('asset_by_status', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa} aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('asset_by_status');
        }

        return [$jawaban, 'database'];
    }

    private function followUpAssetByOwnership(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $ownership = $ctx['ownership'] ?? null;
        $category = $ctx['category'] ?? null;
        $brand = $ctx['brand'] ?? null;

        if (!$ownership) {
            Log::warning('followUpAssetByOwnership.no_ownership', ['ctx' => $ctx]);
            return ["_Maaf, konteks kepemilikan hilang. Coba tanya ulang._", 'database'];
        }

        $q = \App\Models\Asset::with('category');
        $q->where('ownership_type', $ownership);

        // 🆕 Scope by role
        $q = $this->scopeAssetQuery($q);

        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand) {
            $q->where('brand', 'like', "%{$brand}%");
        }

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('asset_by_ownership');
            return ["Tidak ada data lagi. Total: {$total} aset.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $header = $this->isPrivileged()
            ? "Menampilkan {$start}-{$end} dari {$total} aset"
            : "📋 Aset Anda (menampilkan {$start}-{$end} dari {$total})";

        $jawaban = "{$header}:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('asset_by_ownership', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa} aset. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('asset_by_ownership');
        }

        return [$jawaban, 'database'];
    }

    private function followUpLowStockConsumable(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available');

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('low_stock_consumable');
            return ["Tidak ada data lagi. Total: {$total} konsumable.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan {$start}-{$end} dari {$total} konsumable stok rendah:\n\n";
        foreach ($items as $c) {
            $jawaban .= "• {$c->name}: {$c->stock_available}/{$c->stock_minimum} {$c->unit}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('low_stock_consumable', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa}. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('low_stock_consumable');
        }

        return [$jawaban, 'database'];
    }

    private function followUpOverdueLoans(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date');

        // 🆕 Scope: user biasa hanya lihat pinjaman sendiri
        if (!$this->isPrivileged()) {
            $q->where('user_id', auth()->id());
        }

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('overdue_loans');
            return ["Tidak ada data lagi. Total: {$total} peminjaman terlambat.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan {$start}-{$end} dari {$total} peminjaman terlambat:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            $jawaban .= " (jatuh tempo {$l->due_date?->diffForHumans()})\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('overdue_loans', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa}. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('overdue_loans');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveLoans(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetLoan::where('status', 'borrowed')
            ->with(['asset', 'user'])
            ->orderByDesc('loan_date');

        // 🆕 Scope
        if (!$this->isPrivileged()) {
            $q->where('user_id', auth()->id());
        }

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('active_loans');
            return ["Tidak ada data lagi. Total: {$total} peminjaman aktif.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan {$start}-{$end} dari {$total} peminjaman aktif:\n\n";
        foreach ($items as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            if ($l->due_date)
                $jawaban .= " (jatuh tempo {$l->due_date->format('d M Y')})";
            $jawaban .= "\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('active_loans', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa}. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('active_loans');
        }

        return [$jawaban, 'database'];
    }

    private function followUpActiveAssignments(array $ctx, int $offset, int $limit, ChatMemoryService $memory): array
    {
        $q = \App\Models\AssetAssignment::whereNull('returned_at')
            ->with(['asset', 'user'])
            ->orderByDesc('assigned_at');

        // 🆕 Scope
        if (!$this->isPrivileged()) {
            $q->where('user_id', auth()->id());
        }

        $total = $q->count();
        $items = (clone $q)->skip($offset)->take($limit)->get();

        if ($items->isEmpty()) {
            $memory->forget('active_assignments');
            return ["Tidak ada data lagi. Total: {$total} serah terima aktif.", 'database'];
        }

        $start = $offset + 1;
        $end = $offset + $items->count();

        $jawaban = "Menampilkan {$start}-{$end} dari {$total} serah terima aktif:\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->asset?->serial_number}";
            if ($a->asset?->hostname)
                $jawaban .= " ({$a->asset->hostname})";
            $jawaban .= " — {$a->user?->name}\n";
        }

        $ctx['offset'] = $end;
        $ctx['time'] = now()->toDateTimeString();
        $memory->remember('active_assignments', $ctx, self::CONTEXT_TTL);

        $sisa = $total - $end;
        if ($sisa > 0) {
            $jawaban .= "\nSisa: {$sisa}. Ketik \"lanjut\" untuk lihat berikutnya.";
        } else {
            $jawaban .= "\n✅ Semua data sudah ditampilkan.";
            $memory->forget('active_assignments');
        }

        return [$jawaban, 'database'];
    }

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

    private function tryAnswerUserAssetField(string $pesan, ChatMemoryService $memory): ?array
    {
        $lower = Str::lower($pesan);

        $field = null;
        if (preg_match('/\bsn\b|\bserial\b/i', $lower)) {
            $field = 'serial_number';
        } elseif (preg_match('/\bhostname\b/i', $lower)) {
            $field = 'hostname';
        } elseif (preg_match('/\basset\s?code\b|\bkode aset\b/i', $lower)) {
            $field = 'asset_code';
        }

        if (!$field) {
            return null;
        }

        $userName = null;

        if (
            preg_match(
                '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\s+(?:sn|serial|hostname|asset\s?code)/i',
                $pesan,
                $m
            )
        ) {
            $kandidat = trim($m[1]);
            if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                $userName = $kandidat;
            }
        }

        if (
            !$userName && preg_match(
                '/(?:sn|serial|hostname|asset\s?code)\s+(?:nya\s+)?([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i',
                $pesan,
                $m
            )
        ) {
            $kandidat = trim($m[1]);
            if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                $userName = $kandidat;
            }
        }

        if (!$userName || strlen($userName) < 3) {
            return null;
        }

        $user = \App\Models\User::where('name', 'like', "%{$userName}%")->first();
        if (!$user) {
            return null;
        }

        // 🆕 Guard
        if (!$this->isPrivileged() && (int) $user->id !== (int) auth()->id()) {
            return [
                "🔒 Maaf, kamu hanya bisa melihat **aset yang sedang kamu pegang**.",
                'database',
                null,
            ];
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return [
                "User {$user->name} sedang tidak memegang aset.",
                'database',
                null,
            ];
        }

        $memory->remember('user', [
            'user_id' => $user->id,
            'name' => $user->name,
            'position' => $user->position,
        ], self::CONTEXT_TTL, "User {$user->name}");

        if ($assets->count() === 1) {
            $a = $assets->first();
            $memory->remember('asset', [
                'asset_id' => $a->id,
                'serial_number' => $a->serial_number,
                'hostname' => $a->hostname,
            ], self::CONTEXT_TTL, "Aset {$a->serial_number}");
        }

        $fieldLabels = [
            'serial_number' => 'Serial Number',
            'hostname' => 'Hostname',
            'asset_code' => 'Asset Code',
        ];

        $jawaban = "👤 {$user->name} memegang {$assets->count()} aset:\n\n";
        foreach ($assets as $a) {
            $value = $a->{$field} ?? null;
            $label = $fieldLabels[$field] ?? $field;

            $jawaban .= "• {$a->hostname}";
            if ($a->serial_number && $field !== 'serial_number' && $a->serial_number !== $a->hostname) {
                $jawaban .= " (SN: `{$a->serial_number}`)";
            }
            $jawaban .= "\n"
                . "  {$label}: `" . ($value ?: '-') . "`\n"
                . "  {$a->brand} {$a->model}\n\n";
        }

        return [trim($jawaban), 'database', null];
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

    private function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n))
                return true;
        }
        return false;
    }

    private function tanyaOllama(string $pesan): string
    {
        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = trim(config('services.ollama.model', ''));
        $endpoint = $url . '/api/chat';

        if (empty($model)) {
            Log::error('Ollama: model kosong. Cek .env OLLAMA_MODEL.');
            return 'Maaf, konfigurasi AI belum lengkap. Hubungi admin.';
        }

        try {
            $response = Http::timeout(self::QA_TIMEOUT)->post($endpoint, [
                'model' => $model,
                'stream' => false,
                'options' => [
                    'temperature' => 0.5,
                    'num_predict' => 256,
                ],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $pesan],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $jawaban = trim($data['message']['content'] ?? '');

                return $jawaban !== '' ? $jawaban : 'Maaf, saya belum bisa menjawab pertanyaan itu.';
            }

            Log::warning('Ollama non-2xx', ['status' => $response->status()]);
            return 'Maaf, saya belum bisa menjawab pertanyaan itu.';
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection error: ' . $e->getMessage());
            return 'Maaf, layanan AI tidak dapat dihubungi. Cek apakah Ollama berjalan.';
        } catch (\Exception $e) {
            Log::error('Ollama exception: ' . $e->getMessage());
            return 'Maaf, layanan AI sedang tidak tersedia. Coba lagi nanti.';
        }
    }

    private function systemPrompt(): string
    {
        return "Kamu adalah SIS Assistant — asisten virtual untuk karyawan PLN UBP Suralaya.\n\n"
            . "KONTEKS SISTEM:\n"
            . "• SIS (Suralaya Information System) adalah portal informasi aplikasi & layanan IT perusahaan.\n"
            . "• SIAM (Sistem Informasi Aset Manajemen) adalah salah satu modul di dalam SIS untuk mengelola aset IT (laptop, PC, printer, monitor, konsumable).\n"
            . "• SIS dan SIAM adalah aplikasi web, bukan aplikasi desktop. Tidak ada 'keyboard virtual' atau 'keyboard khusus' di dalamnya — user pakai keyboard perangkat sendiri.\n\n"
            . "KEAHLIAN KAMU:\n"
            . "1. SIS & SIAM — portal aplikasi, manajemen aset IT.\n"
            . "2. IT Umum — hardware, software, jaringan, keamanan, database, Microsoft Office, email, printer, cloud, troubleshooting dasar.\n"
            . "3. Aplikasi internal — helpdesk, ERP, Maximo, IAM, dll.\n\n"
            . "ATURAN JAWAB:\n"
            . "• SELALU jawab dalam Bahasa Indonesia.\n"
            . "• Jawab SINGKAT — maksimal 3-4 kalimat, LANGSUNG ke inti.\n"
            . "• JANGAN mulai dengan sapaan 'Halo' kecuali user menyapa duluan.\n"
            . "• Kalau tidak tahu, katakan: *\"Maaf, saya belum punya info tentang itu. Coba tanya dengan cara lain atau hubungi IT Support.\"*\n"
            . "• JANGAN MENGARANG fakta yang tidak kamu ketahui.\n\n"
            . "KHUSUS — KODE ASET:\n"
            . "Kode seperti `NB-T14-005`, `AST-2026-0001`, `T14-SN-0005`, `PC-DESK-003` adalah HOSTNAME / SN / ASSET CODE dari aset IT perusahaan.\n"
            . "JANGAN menganggapnya sebagai nomor meteran listrik, nomor rekening, atau nomor seri barang lain.\n\n"
            . "GAYA:\n"
            . "Ramah, profesional, solutif. Gunakan emoji secukupnya. Jangan sebut dirimu AI atau language model.";
    }
}
