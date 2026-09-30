<?php

namespace App\Http\Controllers;

use App\Services\Ai\OllamaIntentParser;
use App\Services\Ai\IntentExecutor;
use App\Services\Ai\OllamaResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiAssetController extends Controller
{
    /**
     * 🆕 Keyword yang menandakan pesan berkaitan dengan data aset.
     * Dipakai untuk guard guest — kalau guest tanya hal ini, wajib login.
     */
    private const ASSET_KEYWORDS = [
        // Aset fisik
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
        // Konsumable
        'konsumable',
        'consumable',
        'stok',
        'stock',
        'atk',
        // Field aset
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
        // Ownership
        'hak milik',
        'sewa',
        'lease',
        'owned',
        'leased',
        'rental',
        // Pemegang
        'pemegang',
        'pegang',
        'dipegang',
        'pemakai',
        'memakai',
        'dipegang siapa',
        'yang pakai',
        'yang pegang',
        // Aksi pada aset
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
        // Lokasi
        'lokasi aset',
        'ruangan aset',
        'gedung aset',
        // Assignment
        'serah terima',
        'assignment',
        'di-assign',
        'diassign',
    ];

    public function __construct(
        protected OllamaIntentParser $parser,
        protected IntentExecutor $executor,
        protected OllamaResponseFormatter $formatter,
    ) {
    }

    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $pesan = trim($request->input('message'));

        // ═══════════════════════════════════════════════════════════
        // 🆕 GUARD 0: Guest yang tanya tentang aset → wajib login
        // ═══════════════════════════════════════════════════════════
        if (!auth()->check() && $this->isAssetQuery($pesan)) {
            return response()->json([
                'reply' => "🔒 Maaf, untuk mengakses data aset kamu harus login terlebih dahulu.\n\n"
                    . "Cara login:\n"
                    . "1. Klik tombol Login di pojok kanan atas\n"
                    . "2. Masukkan username & password SIAM kamu\n"
                    . "3. Setelah login, tanyakan lagi ke saya 😊\n\n"
                    . "_Kalau belum punya akun, hubungi IT Support._",
                'source' => 'auth_guard',
            ], 401);
        }

        // ═══════════════════════════════════════════════════════════
        // 🛡️ GUARD 1: Tolak intent tulis
        // ═══════════════════════════════════════════════════════════
        if ($this->isWriteIntent($pesan)) {
            return response()->json([
                'reply' => '🔒 Maaf, saya hanya bisa membaca data. '
                    . 'Untuk mengubah data, silakan gunakan menu Aset Management di SIAM.',
                'source' => 'readonly_guard',
            ]);
        }

        // 🧠 Step 1: Ollama pahami pertanyaan
        $intent = $this->parser->parse($pesan);

        if (!$intent) {
            // Ollama gagal parse → fallback ke query service langsung
            $direct = app(\App\Services\Query\QueryRouter::class)->tryAnswer($pesan);
            if ($direct) {
                return response()->json([
                    'reply' => $direct[0],
                    'source' => 'database_direct',
                ]);
            }

            return response()->json([
                'reply' => 'Maaf, saya belum bisa memahami pertanyaan itu. '
                    . 'Coba tanya dengan cara lain atau hubungi IT Support.',
                'source' => 'fallback',
            ]);
        }

        // ⚙️ Step 2: Laravel jalankan query
        $result = $this->executor->execute($intent);

        if (!$result) {
            return response()->json([
                'reply' => 'Maaf, saya tidak menemukan data untuk pertanyaan itu.',
                'source' => 'no_match',
                'intent' => $intent,
            ]);
        }

        // 💬 Step 3: Susun jawaban (opsional lewat Ollama untuk yang singkat)
        $reply = $this->formatter->format($pesan, $result['answer']);

        return response()->json([
            'reply' => $reply,
            'source' => 'database_ai',
            'intent' => $intent,
        ]);
    }

    // ============================================================
    // 🆕 GUARD HELPERS
    // ============================================================

    /**
     * 🆕 Deteksi apakah pesan berkaitan dengan data aset.
     * Dipakai untuk memblokir guest sebelum menyentuh database.
     */
    protected function isAssetQuery(string $pesan): bool
    {
        $lower = Str::lower($pesan);

        // 1️⃣ SN/hostname pattern (contoh: NB-T14-005, AST-2026-0001)
        if (preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*\b/i', $pesan)) {
            return true;
        }

        // 2️⃣ Keyword aset (word-boundary)
        foreach (self::ASSET_KEYWORDS as $kw) {
            $pattern = '/\b' . preg_quote($kw, '/') . '\b/i';
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        // 3️⃣ Kombinasi: kata tanya + objek
        $questionWords = [
            'berapa',
            'jumlah',
            'total',
            'daftar',
            'list',
            'siapa',
            'apa saja',
            'tampilkan',
            'lihat',
            'cari',
        ];
        $objectWords = [
            'aset',
            'laptop',
            'pc',
            'komputer',
            'printer',
            'monitor',
            'konsumable',
            'stock',
            'stok',
        ];

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

    /**
     * 🛡️ Deteksi intent tulis (hapus, ubah, tambah).
     */
    protected function isWriteIntent(string $pesan): bool
    {
        $lower = Str::lower($pesan);
        return (bool) preg_match(
            '/\b(hapus|delete|ubah|update|edit|ganti|tambah|create|insert|'
            . 'pindah|move|set|reset|hilangkan|buang|remove|drop|truncate)\b/i',
            $lower
        );
    }
}
