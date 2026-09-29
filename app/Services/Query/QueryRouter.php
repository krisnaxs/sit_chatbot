<?php

namespace App\Services\Query;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryRouter
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
        // User & vendor (sensitif)
        'user',
        'pegawai',
        'karyawan',
        'vendor',
        'supplier',
        'lokasi',
        'ruangan',
    ];

    /**
     * Coba jawab dari semua service DB.
     * Return array [jawaban, sumber] atau null.
     */
    public function tryAnswer(string $pesan): ?array
    {
        $user = auth()->user();

        // ═══════════════════════════════════════════════════════════
        // 🆕 GUARD: Guest yang tanya tentang aset → wajib login
        // Lapisan pertahanan terakhir sebelum menyentuh database.
        // ═══════════════════════════════════════════════════════════
        if (!$user && $this->isAssetRelated($pesan)) {
            return [
                "🔒 Maaf, untuk mengakses **data aset** kamu harus **login** terlebih dahulu.\n\n"
                . "**Cara login:**\n"
                . "1. Klik tombol **Login** di pojok kanan atas\n"
                . "2. Masukkan username & password SIAM kamu\n"
                . "3. Setelah login, tanyakan lagi ke saya 😊\n\n"
                . "_Kalau belum punya akun, hubungi IT Support._",
                'database',
            ];
        }

        $services = [
            app(AssetQueryService::class),
            app(CrossQueryService::class),
            app(AnalyticsQueryService::class),
            app(SiamQueryService::class),
            app(UserQueryService::class),
            app(LocationQueryService::class),
            app(VendorQueryService::class),
            app(ReportQueryService::class),
            app(ActivityQueryService::class),
        ];

        // 🆕 Inject user ke service yang butuh
        foreach ($services as $service) {
            if (method_exists($service, 'setUser')) {
                $service->setUser($user);
            }
        }

        // 🆕 Paksa koneksi read-only selama query service AI berjalan
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection('ai_readonly');

        try {
            foreach ($services as $service) {
                $result = $service->tryAnswer($pesan);
                if ($result) {
                    return $result;
                }
            }
        } finally {
            // Selalu restore — apapun yang terjadi
            DB::setDefaultConnection($original);
        }

        return null;
    }

    // ============================================================
    // 🆕 GUARD HELPERS
    // ============================================================

    /**
     * 🆕 Deteksi apakah pesan berkaitan dengan data aset.
     * Dipakai untuk memblokir guest sebelum menyentuh database.
     */
    protected function isAssetRelated(string $pesan): bool
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
}
