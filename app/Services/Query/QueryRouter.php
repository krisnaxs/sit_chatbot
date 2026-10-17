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


    private const COMPLEX_QUERY_PATTERNS = [
        '/\b(20\d{2})\b.*\b(laptop|pc|komputer|printer|monitor|server|router)\b/i',
        '/\b(laptop|pc|komputer|printer|monitor|server|router)\b.*\b(20\d{2})\b/i',
        '/\b(type|tipe|model|brand|merek|merk)\s+(apa|apa saja|apa aja)\b/i',
        '/\b(laptop|pc|komputer|printer|monitor|server)\b.*\b(ready|tersedia|available|rusak|maintenance|dipinjam|loaned)\b.*\b(apa|apa saja|list|daftar)\b/i',
        '/\b(tahun|year)\b.*\b(type|tipe|model|brand|merek|merk)\b/i',
        '/\b(type|tipe|model|brand|merek|merk)\b.*\b(tahun|year|20\d{2})\b/i',
        '/\b(brand|merek|merk|model|type|tipe)\s+[a-z0-9]+\s+(tahun|20\d{2})/i',
        '/\b(laptop|pc|komputer|printer|monitor|server)\b.*\b(paling|terbanyak|top|tertinggi|terbesar|mayoritas)\b/i',
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
        if ($this->isComplexQuery($pesan)) {
            Log::info('queryrouter.skipped_complex_query', [
                'pesan' => $pesan,
            ]);
            return null;
        }
        $mappingResult = $this->tryMapping($pesan);
        if ($mappingResult) {
            return $mappingResult;
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


    protected function tryMapping(string $pesan): ?array
    {
        $handlers = \App\Services\Query\QueryMapping::handlers();
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection('ai_readonly');

        try {
            foreach ($handlers as $pattern => $callback) {
                if (!preg_match($pattern, $pesan)) {
                    continue;
                }

                try {
                    $result = $callback();

                    if (!$result || !isset($result[0])) {
                        continue;
                    }
                    if (!$this->isRelevant($pesan, $result[0])) {
                        Log::info('queryrouter.mapping_not_relevant', [
                            'pattern' => $pattern,
                            'pesan' => $pesan,
                            'answer_preview' => mb_substr($result[0], 0, 100),
                        ]);
                        continue;
                    }

                    Log::info('queryrouter.mapping_hit', [
                        'pattern' => $pattern,
                        'pesan' => $pesan,
                    ]);
                    return [
                        $result[0],
                        $result[1] ?? 'database',
                        $result[2] ?? null,
                    ];
                } catch (\Throwable $e) {
                    Log::warning('queryrouter.mapping_error', [
                        'pattern' => $pattern,
                        'pesan' => $pesan,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            DB::setDefaultConnection($original);
        }

        return null;
    }


    protected function isRelevant(string $pesan, string $answer): bool
    {
        $lower = Str::lower($pesan);
        $answerLower = Str::lower($answer);
        if (mb_strlen(trim($answer)) < 3) {
            return false;
        }
        if (preg_match('/\b(type|tipe|model)\s+apa\b/i', $lower)) {
            if (
                preg_match('/\btype|tipe|model\b/i', $answerLower) === 0
                && preg_match('/\b(thinpad|thinkpad|zenbook|macbook|ideapad|latitude|probook|elitebook)\b/i', $answerLower) === 0
            ) {
                return false;
            }
        }
        if (preg_match('/\b(brand|merek|merk)\s+apa\b/i', $lower)) {
            if (
                preg_match('/\b(dell|hp|lenovo|asus|acer|apple|samsung|toshiba|epson|canon|brother)\b/i', $answerLower) === 0
                && preg_match('/\bdaftar\s+brand\b|\bdistinct\b|\bberikut\s+brand\b/i', $answerLower) === 0
            ) {
                return false;
            }
        }
        if (preg_match('/\bberapa\b/i', $lower)) {
            if (preg_match('/\d+/', $answer) === 0) {
                return false;
            }
        }
        if (preg_match('/\bsiapa\b/i', $lower)) {
            if (
                preg_match('/\b(nama|pemegang|user|pegawai|dipegang|pakai)\b/i', $answerLower) === 0
                && preg_match('/[A-Z][a-z]+(?:\s+[A-Z][a-z]+)+/', $answer) === 0  // Nama orang
            ) {
                return false;
            }
        }
        if (preg_match('/\b(20\d{2})\b/', $pesan, $ym)) {
            $year = $ym[1];
            if (
                !str_contains($answer, $year)
                && !preg_match('/\btahun\s+(pembelian|beli|perolehan|pengadaan)\b/i', $answerLower)
            ) {
                return false;
            }
        }
        if (preg_match('/\b(ready|tersedia|available)\b/i', $lower)) {
            if (
                preg_match('/\b(tersedia|ready|available|siap)\b/i', $answerLower) === 0
                && preg_match('/\bstatus\b/i', $answerLower) === 0
            ) {
                return false;
            }
        }
        $categories = ['laptop', 'pc', 'komputer', 'printer', 'monitor', 'server', 'router', 'scanner'];
        $askedCategory = null;
        foreach ($categories as $cat) {
            if (preg_match('/\b' . preg_quote($cat, '/') . '\b/i', $lower)) {
                $askedCategory = $cat;
                break;
            }
        }

        if ($askedCategory) {
            foreach ($categories as $cat) {
                if ($cat === $askedCategory) {
                    continue;
                }
                if (
                    preg_match('/\b' . preg_quote($cat, '/') . '\b/i', $answerLower)
                    && !preg_match('/\b' . preg_quote($askedCategory, '/') . '\b/i', $answerLower)
                ) {
                    return false;
                }
            }
        }

        return true;
    }


    protected function isComplexQuery(string $pesan): bool
    {
        foreach (self::COMPLEX_QUERY_PATTERNS as $pattern) {
            if (preg_match($pattern, $pesan)) {
                return true;
            }
        }
        return false;
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
