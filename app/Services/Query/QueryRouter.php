<?php

namespace App\Services\Query;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueryRouter
{
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
        'user',
        'pegawai',
        'karyawan',
        'vendor',
        'supplier',
        'lokasi',
        'ruangan',
    ];

    public function tryAnswer(string $pesan): ?array
    {
        $user = auth()->user();
        if (!$user && $this->isAssetRelated($pesan)) {
            return [
                "🔒 Maaf, untuk mengakses data aset kamu harus login terlebih dahulu.\n\n"
                . "Cara login:\n"
                . "1. Klik tombol Login di pojok kanan atas\n"
                . "2. Masukkan username & password SIAM kamu\n"
                . "3. Setelah login, tanyakan lagi ke saya 😊\n\n"
                . "_Kalau belum punya akun, hubungi IT Support._",
                'database',
            ];
        }

        // ============================================================
        // ★ STEP 1: Coba QueryMapping (cepat, < 100ms) ★
        // ============================================================
        $mappingResult = $this->tryMapping($pesan);
        if ($mappingResult) {
            return $mappingResult;
        }

        // ============================================================
        // STEP 2: Fallback ke service-service existing
        // ============================================================
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
        foreach ($services as $service) {
            if (method_exists($service, 'setUser')) {
                $service->setUser($user);
            }
        }
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
            DB::setDefaultConnection($original);
        }

        return null;
    }

    /**
     * ★ Coba cocokkan pesan dengan QueryMapping.
     * Return [answer, 'database', context] atau null kalau tidak match.
     */
    protected function tryMapping(string $pesan): ?array
    {
        $handlers = \App\Services\Query\QueryMapping::handlers();

        // Cek koneksi ai_readonly juga untuk mapping
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection('ai_readonly');

        try {
            foreach ($handlers as $pattern => $callback) {
                if (preg_match($pattern, $pesan)) {
                    try {
                        $result = $callback();

                        if ($result && isset($result[0])) {
                            Log::info('queryrouter.mapping_hit', [
                                'pattern' => $pattern,
                                'pesan' => $pesan,
                            ]);

                            // Return format standar: [answer, sumber, context]
                            return [
                                $result[0],
                                $result[1] ?? 'database',
                                $result[2] ?? null,
                            ];
                        }
                    } catch (\Throwable $e) {
                        Log::warning('queryrouter.mapping_error', [
                            'pattern' => $pattern,
                            'pesan' => $pesan,
                            'error' => $e->getMessage(),
                        ]);
                        // Lanjut cek pattern berikutnya
                    }
                }
            }
        } finally {
            DB::setDefaultConnection($original);
        }

        return null;
    }

    protected function isAssetRelated(string $pesan): bool
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
