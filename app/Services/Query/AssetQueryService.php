<?php

namespace App\Services\Query;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetType;
use App\Models\Consumable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetQueryService
{
    /**
     * 🆕 User yang sedang login (null = guest).
     */
    protected ?\App\Models\User $user = null;

    /**
     * 🆕 Set user context untuk role-based filter.
     */
    public function setUser(?\App\Models\User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * 🆕 Cek apakah user privileged (admin / support).
     */
    protected function isPrivileged(): bool
    {
        if (!$this->user) {
            return false;
        }
        $role = $this->user->role ?? null;
        return in_array($role, ['admin', 'support'], true);
    }

    protected array $statusMap = [
        'ready' => 'available',
        'tersedia' => 'available',
        'available' => 'available',
        'siap pakai' => 'available',
        'siap dipakai' => 'available',
        'kosong' => 'available',
        'idle' => 'available',
        'free' => 'available',
        'nganggur' => 'available',
        'belum dipakai' => 'available',
        'belum dipake' => 'available',
        'di pakai' => 'in_use',
        'di pake' => 'in_use',
        'sedang dipakai' => 'in_use',
        'lagi dipakai' => 'in_use',
        'sedang dipake' => 'in_use',
        'dipakai' => 'in_use',
        'dipake' => 'in_use',
        'terpakai' => 'in_use',
        'di gunakan' => 'in_use',
        'digunakan' => 'in_use',
        'digunain' => 'in_use',
        'in_use' => 'in_use',
        'in use' => 'in_use',
        'di pinjam' => 'loaned',
        'di pinjem' => 'loaned',
        'sedang dipinjam' => 'loaned',
        'dipinjam' => 'loaned',
        'dipinjem' => 'loaned',
        'pinjam' => 'loaned',
        'loaned' => 'loaned',
        'di perbaiki' => 'maintenance',
        'sedang diperbaiki' => 'maintenance',
        'diperbaiki' => 'maintenance',
        'perbaikan' => 'maintenance',
        'maintenance' => 'maintenance',
        'rusak' => 'maintenance',
        'servis' => 'maintenance',
        'pensiun' => 'retired',
        'retired' => 'retired',
        'tidak dipakai' => 'retired',
        'tidak dipake' => 'retired',
        'hilang' => 'lost',
        'ilang' => 'lost',
        'lost' => 'lost',
    ];

    protected array $categories = [
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
    ];

    protected array $brands = [
        'dell',
        'hp',
        'lenovo',
        'asus',
        'acer',
        'apple',
        'macbook',
        'toshiba',
        'samsung',
        'epson',
        'canon',
        'brother',
        'logitech',
        'tp-link',
        'cisco',
        'mikrotik',
        'seagate',
        'western digital',
        'wd',
        'sandisk',
        'kingston',
        'v-gen',
    ];

    protected array $ownershipMap = [
        'hak milik' => 'owned',
        'milik' => 'owned',
        'owned' => 'owned',
        'beli' => 'owned',
        'pembelian' => 'owned',
        'dibeli' => 'owned',
        'sewa' => 'leased',
        'sewaan' => 'leased',
        'leased' => 'leased',
        'rental' => 'leased',
        'kontrak' => 'leased',
    ];

    protected array $consumableKeywords = [
        'keyboard',
        'mouse',
        'hdd',
        'harddisk',
        'hard disk',
        'hardisk',
        'flashdisk',
        'flash disk',
        'ssd',
        'usb',
        'kabel',
        'lan',
        'hdmi',
        'headset',
        'charger',
        'tinta',
        'toner',
        'kertas',
        'atk',
        'pulpen',
        'pensil',
        'spidol',
    ];

    /**
     * 🆕 Kata umum untuk konsumable (termasuk typo umum).
     */
    protected array $consumableGeneralKeywords = [
        'konsumable',
        'consumable',
        'kosumable',
        'konsumebel',
        'konsumebel',
        'consumebel',
        'habis pakai',
        'barang habis pakai',
        'atk',
        'alat tulis',
    ];

    protected array $sisHardwareMap = [
        'papan ketik' => ['title' => 'Keyboard', 'emoji' => '⌨️', 'desc' => 'Keyboard fisik (USB/Bluetooth) atau keyboard bawaan laptop.'],
        'flash disk' => ['title' => 'Flashdisk', 'emoji' => '🔌', 'desc' => 'USB flash drive untuk transfer data.'],
        'hard disk' => ['title' => 'Hard Disk', 'emoji' => '💽', 'desc' => 'Hard disk eksternal/internal untuk penyimpanan data.'],
        'hardisk' => ['title' => 'Hard Disk', 'emoji' => '💽', 'desc' => 'Hard disk eksternal/internal untuk penyimpanan data.'],
        'kabel lan' => ['title' => 'Kabel LAN', 'emoji' => '🔗', 'desc' => 'Kabel jaringan Ethernet.'],
        'kabel hdmi' => ['title' => 'Kabel HDMI', 'emoji' => '🔗', 'desc' => 'Kabel HDMI untuk display.'],
        'keyboard' => ['title' => 'Keyboard', 'emoji' => '⌨️', 'desc' => 'Keyboard fisik (USB/Bluetooth) atau keyboard bawaan laptop.'],
        'flashdisk' => ['title' => 'Flashdisk', 'emoji' => '🔌', 'desc' => 'USB flash drive untuk transfer data.'],
        'harddisk' => ['title' => 'Hard Disk', 'emoji' => '💽', 'desc' => 'Hard disk eksternal/internal untuk penyimpanan data.'],
        'trackpad' => ['title' => 'Touchpad', 'emoji' => '🖱️', 'desc' => 'Touchpad bawaan laptop.'],
        'touchpad' => ['title' => 'Touchpad', 'emoji' => '🖱️', 'desc' => 'Touchpad bawaan laptop.'],
        'mouse' => ['title' => 'Mouse', 'emoji' => '🖱️', 'desc' => 'Mouse USB, wireless, atau touchpad laptop.'],
        'hdd' => ['title' => 'HDD', 'emoji' => '💽', 'desc' => 'Hard disk eksternal/internal untuk penyimpanan data.'],
        'ssd' => ['title' => 'SSD', 'emoji' => '💾', 'desc' => 'Solid State Drive untuk penyimpanan cepat.'],
        'usb' => ['title' => 'USB', 'emoji' => '🔌', 'desc' => 'USB drive / perangkat USB.'],
        'monitor' => ['title' => 'Monitor', 'emoji' => '🖥️', 'desc' => 'Monitor eksternal / layar tambahan.'],
        'printer' => ['title' => 'Printer', 'emoji' => '🖨️', 'desc' => 'Printer untuk cetak dokumen.'],
        'scanner' => ['title' => 'Scanner', 'emoji' => '📠', 'desc' => 'Scanner dokumen.'],
        'webcam' => ['title' => 'Webcam', 'emoji' => '📷', 'desc' => 'Kamera untuk video call / meeting.'],
        'headset' => ['title' => 'Headset', 'emoji' => '🎧', 'desc' => 'Headset / earphone untuk audio.'],
        'speaker' => ['title' => 'Speaker', 'emoji' => '🔊', 'desc' => 'Speaker eksternal.'],
        'ups' => ['title' => 'UPS', 'emoji' => '🔋', 'desc' => 'Uninterruptible Power Supply untuk backup daya.'],
        'charger' => ['title' => 'Charger', 'emoji' => '🔌', 'desc' => 'Adaptor / charger perangkat.'],
        'kabel' => ['title' => 'Kabel', 'emoji' => '🔗', 'desc' => 'Kabel data / power (HDMI, VGA, USB, LAN, dll).'],
        'lan' => ['title' => 'Kabel LAN', 'emoji' => '🔗', 'desc' => 'Kabel jaringan Ethernet.'],
        'hdmi' => ['title' => 'Kabel HDMI', 'emoji' => '🔗', 'desc' => 'Kabel HDMI untuk display.'],
    ];

    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // ============================================================
        // 1. SN/HOSTNAME PATTERN
        // ============================================================
        if (preg_match('/\b([A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*)\b/i', $pesan, $m)) {
            $identifier = strtoupper($m[1]);
            $blacklist = ['nya', 'ini', 'itu', 'apa', 'siapa', 'mana', 'berapa', 'yang', 'dan', 'atau'];
            if (!in_array(strtolower($identifier), $blacklist, true)) {
                $exists = Asset::where('serial_number', 'like', "%{$identifier}%")
                    ->orWhere('hostname', 'like', "%{$identifier}%")
                    ->orWhere('asset_code', 'like', "%{$identifier}%")
                    ->exists();

                if ($exists) {
                    return $this->handleAssetLookup($identifier, $lower);
                }
            }
        }

        // ============================================================
        // 2. "sn: xxx" EKSPLISIT
        // ============================================================
        if (preg_match('/(?:sn|serial|hostname|asset_code)[\s:]+([A-Z0-9\-_]+)/i', $pesan, $m)) {
            $identifier = $m[1];
            $blacklist = ['nya', 'ini', 'itu', 'apa', 'siapa', 'mana', 'berapa', 'yang'];
            if (!in_array(strtolower($identifier), $blacklist, true)) {
                return $this->handleAssetLookup($identifier, $lower);
            }
        }

        // ============================================================
        // 3. SIS/SIAM HARDWARE (PRIORITAS TINGGI)
        // ============================================================
        $isSisContext = $this->matchAny($lower, ['sis', 'siam', 'aplikasi', 'sistem', 'web', 'aplikasinya']);

        if ($isSisContext) {
            $matchedHardware = $this->detectHardware($lower);

            if ($matchedHardware) {
                return [
                    "{$matchedHardware['emoji']} **Tentang {$matchedHardware['title']} di SIS/SIAM**\n\n"
                    . "SIS dan SIAM adalah **aplikasi web**, bukan aplikasi desktop.\n\n"
                    . "• **Tidak ada {$matchedHardware['title']} virtual khusus** di dalam SIS/SIAM.\n"
                    . "• Kamu pakai **{$matchedHardware['title']}** milik perangkat sendiri (laptop/HP).\n"
                    . "• {$matchedHardware['desc']}\n"
                    . "• Kalau {$matchedHardware['title']} perangkatmu bermasalah, itu masalah **hardware** — bukan masalah SIS/SIAM.\n\n"
                    . "💡 Kalau butuh **{$matchedHardware['title']}** untuk kerja, ajukan permintaan ke **IT Support** atau cek stok di menu **Aset/Consumable**.",
                    'database'
                ];
            }

            if ($this->matchAny($lower, ['hardware', 'perangkat', 'peripheral', 'periferal', 'alat'])) {
                return [
                    "🖥️ **Hardware & Peripheral di SIS/SIAM**\n\n"
                    . "SIS dan SIAM adalah **aplikasi web**. Semua hardware yang kamu pakai adalah **perangkat fisik** milikmu sendiri atau aset kantor.\n\n"
                    . "**Yang sering ditanyakan:**\n"
                    . "• ⌨️ Keyboard — pakai keyboard perangkat sendiri\n"
                    . "• 🖱️ Mouse / Touchpad — pakai mouse/touchpad perangkat sendiri\n"
                    . "• 💽 HDD / SSD — penyimpanan internal perangkat\n"
                    . "• 🔌 Flashdisk / USB — untuk transfer data\n"
                    . "• 🖥️ Monitor — layar utama atau tambahan\n"
                    . "• 🖨️ Printer / Scanner — untuk cetak & scan dokumen\n"
                    . "• 🎧 Headset / Webcam — untuk meeting online\n\n"
                    . "**Tidak ada hardware virtual** di dalam SIS/SIAM. Kalau ada hardware bermasalah, itu urusan **IT Support**, bukan aplikasi.",
                    'database'
                ];
            }
        }

        // ============================================================
        // 4. KONSUMABLE UMUM (PRIORITAS SEBELUM CEK STATUS ASET)
        // ============================================================
        if ($this->matchAny($lower, $this->consumableGeneralKeywords)) {
            // Cek low stock?
            if ($this->matchAny($lower, ['stok rendah', 'stok habis', 'low stock', 'stok minim', 'habis', 'rendah', 'kosong', 'low'])) {
                return $this->listLowStockConsumable();
            }

            // Cek "siapa yang pakai"?
            if ($this->matchAny($lower, ['siapa', 'pakai', 'pemakai', 'gunakan', 'pengguna'])) {
                return $this->listConsumableUsers($lower);
            }

            // Cek "ready" / "tersedia"?
            if ($this->matchAny($lower, ['ready', 'tersedia', 'available', 'ada', 'stok ada'])) {
                return $this->listAvailableConsumables();
            }

            // Default: tampilkan semua konsumable
            return $this->listAllConsumables();
        }

        // ============================================================
        // 5. "Siapa yang pakai X?" — KHUSUS ADMIN/SUPPORT
        // ============================================================
        if (
            $this->matchAny($lower, ['siapa', 'siapa saja', 'siapa aja', 'user', 'pegawai', 'karyawan']) &&
            $this->matchAny($lower, ['pakai', 'memakai', 'gunakan', 'menggunakan', 'pegang', 'pinjam'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data pemakai hanya bisa diakses oleh **Admin** atau **Support**.\n\n"
                    . "Kalau kamu butuh info ini untuk keperluan kerja, hubungi **IT Support** ya.",
                    'database'
                ];
            }

            $consumableKeywords = [];
            foreach ($this->consumableKeywords as $kw) {
                if ($this->matchWord($lower, $kw)) {
                    $consumableKeywords[] = $kw;
                }
            }

            $assetCategory = $this->detectCategory($lower);

            if (!empty($consumableKeywords)) {
                return $this->listConsumableUsers($lower);
            }

            if ($assetCategory) {
                return $this->listAssetHoldersByCategory($assetCategory);
            }

            return [
                "Mau cek pemakai apa? Sebutkan lebih spesifik ya.\n\n"
                . "**Contoh:**\n"
                . "• \"siapa saja yang pakai laptop\"\n"
                . "• \"siapa saja yang pakai mouse\"\n"
                . "• \"siapa saja yang pakai keyboard\"\n"
                . "• \"siapa saja yang pakai printer\"",
                'database'
            ];
        }

        // ============================================================
        // 6. CONSUMABLE SPESIFIK (keyboard, mouse, dll)
        // ============================================================
        $consumableResult = $this->tryAnswerConsumable($lower);
        if ($consumableResult) {
            return $consumableResult;
        }

        // ============================================================
        // 7. QUERY ASSET
        // ============================================================
        if (
            $this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'total']) &&
            $this->matchAny($lower, array_keys($this->statusMap))
        ) {
            return $this->countAssetByStatus($lower);
        }
        if (
            $this->matchAny($lower, ['apa saja', 'sebutkan', 'daftar', 'list', 'tampilkan', 'lihat']) &&
            $this->matchAny($lower, array_keys($this->statusMap))
        ) {
            return $this->listAssetByStatus($lower);
        }
        if (
            $this->matchAny($lower, array_keys($this->statusMap)) &&
            $this->detectCategory($lower) &&
            !$this->matchAny($lower, ['berapa', 'jumlah', 'total', 'top', 'terbanyak', 'paling'])
        ) {
            return $this->countAssetByStatus($lower);
        }
        if (
            $this->matchAny($lower, array_keys($this->statusMap)) &&
            strlen($lower) < 40 &&
            !$this->matchAny($lower, ['berapa', 'jumlah', 'total', 'top', 'terbanyak', 'list', 'daftar']) &&
            !$this->containsSnPattern($pesan)
        ) {
            $hasOtherIntent = $this->matchAny($lower, [
                'top',
                'terbanyak',
                'paling',
                'brand',
                'merek',
                'model',
                'tipe',
                'user',
                'pegawai',
                'lokasi',
                'ruangan',
                'kategori',
                'pinjam',
                'loan',
                'maintenance',
                'perbaikan',
                'garansi',
                'kontrak',
                'sewa',
                'siapa',
                'pegang',
                'memegang',
                'dipegang',
                'pemakai',
            ]);

            if (!$hasOtherIntent) {
                return $this->countAssetByStatus($lower);
            }
        }
        if (
            $this->matchAny($lower, ['pinjam', 'peminjaman', 'loan']) &&
            $this->matchAny($lower, ['telat', 'terlambat', 'overdue', 'lewat'])
        ) {
            return $this->listOverdueLoans();
        }
        if (
            $this->matchAny($lower, ['perbaikan', 'maintenance', 'servis', 'rusak']) &&
            $this->matchAny($lower, ['berapa', 'ada', 'sedang', 'lagi', 'jumlah'])
        ) {
            return $this->countMaintenance();
        }
        if ($this->matchAny($lower, ['nilai', 'harga total', 'total pembelian', 'harga aset'])) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data nilai / harga aset hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->totalAssetValue();
        }
        if (
            $this->matchAny($lower, ['pinjam', 'peminjaman', 'loan']) &&
            $this->matchAny($lower, ['aktif', 'sedang', 'berjalan', 'berapa', 'daftar'])
        ) {
            return $this->listActiveLoans();
        }
        if (
            $this->matchAny($lower, ['serah terima', 'assignment', 'pegang']) &&
            $this->matchAny($lower, ['aktif', 'sedang', 'berapa', 'daftar', 'siapa'])
        ) {
            return $this->listActiveAssignments();
        }
        if (
            $this->matchAny($lower, ['kategori', 'category']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total', 'daftar'])
        ) {
            return $this->countCategories();
        }
        if (
            $this->matchAny($lower, ['brand', 'merek', 'model', 'tipe', 'type']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'daftar', 'list'])
        ) {
            return $this->countAssetTypes();
        }
        if (
            $this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'total']) &&
            $this->matchAny($lower, array_keys($this->ownershipMap))
        ) {
            return $this->countAssetByOwnership($lower);
        }
        if (
            $this->matchAny($lower, ['terbanyak', 'paling banyak', 'top', 'tertinggi', 'mayoritas', 'paling sering', 'sering dipakai']) &&
            $this->matchAny($lower, ['type', 'tipe', 'model', 'brand', 'merek', 'merk', 'laptop', 'komputer', 'aset', 'asset', 'barang']) &&
            !$this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'sewa', 'hak milik', 'milik'])
        ) {
            return $this->topAssetBy($lower);
        }
        if (
            $this->matchAny($lower, ['list', 'daftar', 'sebutkan', 'tampilkan', 'apa saja', 'lihat']) &&
            $this->matchAny($lower, ['kategori', 'category']) &&
            $this->detectCategory($lower)
        ) {
            return $this->listAssetsByCategory($lower);
        }
        if (
            $this->matchAny($lower, ['list', 'daftar', 'sebutkan', 'tampilkan', 'apa saja', 'lihat']) &&
            $this->matchAny($lower, ['brand', 'merek', 'merk']) &&
            $this->detectBrand($lower)
        ) {
            return $this->listAssetsByBrand($lower);
        }
        if (
            $this->matchAny($lower, ['list', 'daftar', 'sebutkan', 'tampilkan', 'apa saja', 'lihat']) &&
            $this->detectOwnership($lower)
        ) {
            return $this->listAssetsByOwnership($lower);
        }
        if (
            $this->matchAny($lower, ['tahun', 'year']) &&
            preg_match('/\b(20\d{2})\b/', $lower, $ym) &&
            $this->matchAny($lower, ['aset', 'asset', 'pembelian', 'beli'])
        ) {
            return $this->assetsByYear((int) $ym[1]);
        }
        if ($this->matchAny($lower, ['aset per tahun', 'aset tiap tahun', 'pengadaan per tahun', 'pembelian per tahun'])) {
            return $this->assetsByYearSummary();
        }
        if (
            $this->matchAny($lower, ['pegang', 'memegang', 'punya', 'pakai', 'gunakan', 'dipegang', 'pengang'])
            && !$this->matchAny($lower, ['berapa', 'jumlah', 'total', 'top', 'terbanyak', 'paling', 'list', 'daftar'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data pemegang aset hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            $userName = $this->extractUserName($pesan);
            if ($userName) {
                return $this->listAssetsByUser($pesan);
            }
        }
        if (
            $this->matchAny($lower, ['aset', 'asset', 'laptop', 'printer', 'monitor', 'pc']) &&
            $this->matchAny($lower, ['dipegang', 'pegang', 'memegang', 'pakai', 'gunakan', 'pengang']) &&
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'orang'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data pemegang aset hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->listAssetsByUser($pesan);
        }
        if (
            $this->matchAny($lower, ['garansi', 'warranty']) &&
            $this->matchAny($lower, ['hampir', 'segera', 'berakhir', 'habis', 'expired'])
        ) {
            return $this->assetsExpiringWarranty();
        }
        if (
            $this->matchAny($lower, ['kontrak', 'sewa', 'lease']) &&
            $this->matchAny($lower, ['berakhir', 'habis', 'selesai'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "Maaf, data kontrak sewa hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->assetsExpiringContract();
        }
        if (
            $this->matchAny($lower, ['tua', 'lama', 'pensiun', 'retire']) &&
            $this->matchAny($lower, ['aset', 'asset', 'rekomendasi', 'harus', 'sebaiknya'])
        ) {
            return $this->assetsNeedingRetire();
        }
        if (
            $this->matchAny($lower, ['bandingkan', 'perbandingan', 'compare', 'vs', 'dibanding']) &&
            $this->matchAny($lower, ['hak milik', 'owned', 'milik']) &&
            $this->matchAny($lower, ['sewa', 'lease', 'leased'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data perbandingan hak milik vs sewa hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->compareOwnership($lower);
        }
        if (
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'mayoritas']) &&
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan']) &&
            $this->matchAny($lower, ['aset', 'asset', 'pegang'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data user pemegang aset hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->topUsersWithAssets();
        }
        if (
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'mayoritas']) &&
            $this->matchAny($lower, ['lokasi', 'ruang', 'gedung', 'ruangan']) &&
            $this->matchAny($lower, ['aset', 'asset'])
        ) {
            return $this->topLocations();
        }
        if (
            $this->matchAny($lower, ['summary', 'ringkasan', 'rekap', 'statistik']) &&
            $this->matchAny($lower, ['aset', 'asset', 'status'])
        ) {
            return $this->totalStatusSummary();
        }
        if (
            $this->matchAny($lower, ['vendor', 'supplier']) &&
            $this->matchAny($lower, ['aset', 'asset', 'punya', 'dari'])
        ) {
            if (!$this->isPrivileged()) {
                return [
                    "🔒 Maaf, data vendor aset hanya bisa diakses oleh **Admin** atau **Support**.",
                    'database'
                ];
            }
            return $this->listAssetsByVendor($pesan);
        }

        return null;
    }

    /**
     * 🆕 List semua konsumable (default).
     */
    protected function listAllConsumables(): array
    {
        $q = Consumable::orderBy('name');
        $total = $q->count();
        $items = (clone $q)->limit(20)->get();

        if ($items->isEmpty()) {
            return ["Belum ada konsumable terdaftar di sistem.", 'database'];
        }

        $jawaban = "📦 **Daftar Konsumable** ({$total} item):\n\n";
        foreach ($items as $c) {
            $stok = (int) ($c->stock_available ?? 0);
            $min = (int) ($c->stock_minimum ?? 0);
            $unit = $c->unit ?? 'pcs';

            $status = '✅';
            if ($stok === 0) {
                $status = '⚠️ Habis';
            } elseif ($stok <= $min) {
                $status = '🟡 Rendah';
            }

            $jawaban .= "• **{$c->name}**";
            if ($c->brand) {
                $jawaban .= " ({$c->brand})";
            }
            $jawaban .= " — Stok: **{$stok}** {$unit} {$status}\n";
        }

        if ($total > 20) {
            $jawaban .= "\n_Menampilkan 20 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 List konsumable yang tersedia (stock_available > 0).
     */
    protected function listAvailableConsumables(): array
    {
        $q = Consumable::where('stock_available', '>', 0)
            ->orderBy('name');

        $total = $q->count();
        $items = (clone $q)->limit(20)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada konsumable yang tersedia saat ini.", 'database'];
        }

        $jawaban = "📦 **{$total} Konsumable Tersedia:**\n\n";
        foreach ($items as $c) {
            $stok = (int) ($c->stock_available ?? 0);
            $unit = $c->unit ?? 'pcs';

            $jawaban .= "• **{$c->name}**";
            if ($c->brand) {
                $jawaban .= " ({$c->brand})";
            }
            $jawaban .= " — Stok: **{$stok}** {$unit}\n";
        }

        if ($total > 20) {
            $jawaban .= "\n_Menampilkan 20 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 Handle asset lookup berdasarkan role.
     */
    protected function handleAssetLookup(string $identifier, string $lower): array
    {
        if ($this->isPrivileged()) {
            if ($this->matchAny($lower, ['hostname', 'host name', 'nama host'])) {
                return $this->findAssetHostname($identifier);
            }
            if ($this->matchAny($lower, ['sn', 'serial', 'serial number', 'nomor seri'])) {
                return $this->findAssetSerialNumber($identifier);
            }
            if ($this->matchAny($lower, ['siapa', 'pegang', 'memegang', 'pakai', 'gunakan', 'dipegang', 'pemakai'])) {
                return $this->whoHoldsAsset($identifier);
            }
            return $this->findAssetBySerial($identifier);
        }
        return $this->findAssetBySerialPublic($identifier);
    }

    /**
     * 🆕 Tampilkan aset untuk user biasa/guest (TANPA info pemegang).
     */
    protected function findAssetBySerialPublic(string $serial): array
    {
        $asset = Asset::with(['category', 'currentLocation'])
            ->where('serial_number', 'like', "%{$serial}%")
            ->orWhere('hostname', 'like', "%{$serial}%")
            ->orWhere('asset_code', 'like', "%{$serial}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan ID **{$serial}** tidak ditemukan.", 'database'];
        }

        $jawaban = "**{$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Status: {$this->statusLabel($asset->status)}\n"
            . "• Lokasi: " . ($asset->currentLocation?->full_name ?? '-');

        return [
            $jawaban,
            'database',
            [
                'type' => 'asset',
                'asset_id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'hostname' => $asset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    /**
     * 🆕 Coba jawab pertanyaan consumable spesifik (keyboard, mouse, dll).
     */
    protected function tryAnswerConsumable(string $lower): ?array
    {
        $matchedKeyword = null;
        foreach ($this->consumableKeywords as $kw) {
            if ($this->matchWord($lower, $kw)) {
                $matchedKeyword = $kw;
                break;
            }
        }
        if (!$matchedKeyword) {
            return null;
        }

        $brand = null;
        foreach ($this->brands as $b) {
            if ($this->matchWord($lower, $b)) {
                $brand = $b;
                break;
            }
        }

        try {
            $q = Consumable::where(function ($x) use ($matchedKeyword) {
                $x->where('name', 'like', "%{$matchedKeyword}%")
                    ->orWhere('model', 'like', "%{$matchedKeyword}%")
                    ->orWhere('brand', 'like', "%{$matchedKeyword}%");
            });

            if ($brand) {
                $q->where('brand', 'like', "%{$brand}%");
            }

            $items = $q->limit(10)->get();
        } catch (\Throwable $e) {
            \Log::warning('tryAnswerConsumable failed: ' . $e->getMessage());
            return null;
        }

        if ($items->isEmpty()) {
            return null;
        }

        if ($items->count() === 1) {
            return $this->formatConsumableDetail($items->first());
        }

        $total = $items->count();
        $jawaban = "📦 Ditemukan **{$total}** item consumable";
        if ($brand) {
            $jawaban .= " brand **{$brand}**";
        }
        $jawaban .= ":\n\n";

        foreach ($items as $c) {
            $stok = $c->stock_available ?? 0;
            $unit = $c->unit ?? 'pcs';
            $status = $stok > 0 ? '✅ Tersedia' : '⚠️ Habis';

            $jawaban .= "• **{$c->name}**";
            if ($c->brand) {
                $jawaban .= " ({$c->brand})";
            }
            if ($c->model) {
                $jawaban .= " — {$c->model}";
            }
            $jawaban .= "\n"
                . "  Stok: **{$stok}** {$unit} — {$status}\n";
        }

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 Format detail 1 consumable (harga hanya untuk admin/support).
     */
    protected function formatConsumableDetail($c): array
    {
        $stockAvailable = (int) ($c->stock_available ?? 0);
        $stockMinimum = (int) ($c->stock_minimum ?? 0);
        $stockTotal = (int) ($c->stock_total ?? 0);
        $unit = $c->unit ?? 'pcs';

        $status = $stockAvailable > 0 ? '✅ Tersedia' : '⚠️ Habis';
        $lowStock = $stockMinimum > 0 && $stockAvailable <= $stockMinimum;

        $jawaban = "📦 **{$c->name}**\n\n"
            . "• Brand: " . ($c->brand ?? '-') . "\n"
            . "• Model: " . ($c->model ?? '-') . "\n"
            . "• Unit: {$unit}\n"
            . "• Stok tersedia: **{$stockAvailable}** {$unit}\n"
            . "• Stok total: {$stockTotal} {$unit}\n"
            . "• Minimum stok: {$stockMinimum} {$unit}\n";

        if ($this->isPrivileged()) {
            $harga = '-';
            if (!empty($c->last_price)) {
                $harga = 'Rp ' . number_format((float) $c->last_price, 0, ',', '.');
            }
            $jawaban .= "• Harga terakhir: {$harga}\n";
        }

        $jawaban .= "• Status: {$status}\n";

        if ($lowStock) {
            $jawaban .= "\n⚠️ **Stok rendah!** Perlu segera restock.";
        }

        if (!empty($c->notes) && $this->isPrivileged()) {
            $jawaban .= "\n📝 Catatan: {$c->notes}";
        }

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 List user pemakai consumable (khusus admin/support).
     */
    protected function listConsumableUsers(string $lower): array
    {
        $keywords = [];
        foreach ($this->consumableKeywords as $kw) {
            if ($this->matchWord($lower, $kw)) {
                $keywords[] = $kw;
            }
        }

        if (empty($keywords)) {
            return [
                "Sebutkan consumable yang ingin dicek.\n\n"
                . "Contoh: \"siapa saja yang pakai mouse dan keyboard\"",
                'database'
            ];
        }

        try {
            $consumables = Consumable::where(function ($x) use ($keywords) {
                foreach ($keywords as $i => $kw) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $x->$method('name', 'like', "%{$kw}%")
                        ->orWhere('model', 'like', "%{$kw}%")
                        ->orWhere('brand', 'like', "%{$kw}%");
                }
            })->get();
        } catch (\Throwable $e) {
            \Log::warning('listConsumableUsers failed: ' . $e->getMessage());
            return ["Terjadi kesalahan saat mencari data consumable.", 'database'];
        }

        if ($consumables->isEmpty()) {
            return [
                "Consumable **" . implode(', ', $keywords) . "** tidak ditemukan.",
                'database'
            ];
        }

        if (!class_exists(\App\Models\ConsumableTransaction::class)) {
            return [
                "📦 Consumable **" . $consumables->pluck('name')->implode(', ') . "** ada.\n\n"
                . "⚠️ Tapi sistem **belum mencatat siapa yang memakainya**. "
                . "Data pemakai akan muncul kalau ada transaksi keluar/masuk.",
                'database'
            ];
        }

        $jawaban = "👥 **Pemakai Consumable:**\n\n";
        $hasData = false;

        foreach ($consumables as $c) {
            $jawaban .= "📦 **{$c->name}**";

            try {
                $transactions = \App\Models\ConsumableTransaction::where('consumable_id', $c->id)
                    ->with('user')
                    ->orderByDesc('transaction_date')
                    ->limit(10)
                    ->get();
            } catch (\Throwable $e) {
                $jawaban .= " — *error ambil data transaksi*\n\n";
                continue;
            }

            if ($transactions->isEmpty()) {
                $jawaban .= " — *belum ada pemakai tercatat*\n\n";
                continue;
            }

            $hasData = true;
            $jawaban .= " — **{$transactions->count()}** transaksi terakhir:\n";

            foreach ($transactions as $t) {
                $icon = match ($t->type ?? '') {
                    'in' => '📥',
                    'out' => '📤',
                    'return' => '↩️',
                    default => '•',
                };
                $user = $t->user?->name ?? 'Unknown';
                $qty = $t->quantity ?? '-';
                $unit = $c->unit ?? 'pcs';
                $date = $t->transaction_date
                    ? \Carbon\Carbon::parse($t->transaction_date)->format('d M Y')
                    : '-';

                $jawaban .= "  {$icon} {$user} — {$qty} {$unit} ({$date})\n";
            }
            $jawaban .= "\n";
        }

        if (!$hasData) {
            $jawaban .= "\n⚠️ *Belum ada transaksi tercatat untuk consumable ini.*";
        }

        return [$jawaban, 'database'];
    }

    protected function detectHardware(string $lower): ?array
    {
        foreach ($this->sisHardwareMap as $keyword => $info) {
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            if (preg_match($pattern, $lower)) {
                return $info;
            }
        }
        return null;
    }

    protected function findAssetHostname(string $identifier): array
    {
        $asset = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->where('serial_number', 'like', "%{$identifier}%")
            ->orWhere('hostname', 'like', "%{$identifier}%")
            ->orWhere('asset_code', 'like', "%{$identifier}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan ID **{$identifier}** tidak ditemukan.", 'database'];
        }

        $jawaban = "🔍 **{$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n**Hostname:** `" . ($asset->hostname ?? '-') . "`";

        return [
            $jawaban,
            'database',
            [
                'type' => 'asset',
                'asset_id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'hostname' => $asset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function findAssetSerialNumber(string $identifier): array
    {
        $asset = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->where('serial_number', 'like', "%{$identifier}%")
            ->orWhere('hostname', 'like', "%{$identifier}%")
            ->orWhere('asset_code', 'like', "%{$identifier}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan ID **{$identifier}** tidak ditemukan.", 'database'];
        }

        $jawaban = "🔍 **{$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n**Serial Number:** `" . ($asset->serial_number ?? '-') . "`";

        return [
            $jawaban,
            'database',
            [
                'type' => 'asset',
                'asset_id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'hostname' => $asset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function extractUserName(string $pesan): ?string
    {
        $skip = [
            'yang',
            'dan',
            'atau',
            'di',
            'ke',
            'dari',
            'aset',
            'laptop',
            'pc',
            'printer',
            'monitor',
            'siapa',
            'saya',
            'kan',
            'bertanya',
            'user'
        ];

        if (preg_match('/\b([a-z]+(?:\s+[a-z]+)?)\s+(pegang|memegang|punya|pakai|gunakan|pengang|dipegang)\b/i', $pesan, $m)) {
            $nama = trim($m[1]);
            if (!in_array(strtolower($nama), $skip, true) && strlen($nama) >= 3) {
                if (\App\Models\User::where('name', 'like', "%{$nama}%")->exists()) {
                    return $nama;
                }
            }
        }

        if (preg_match('/^([a-z]+(?:\s+[a-z]+)?)\s+(?:sn|serial|hostname|asset\s?code|nya)/i', $pesan, $m)) {
            $nama = trim($m[1]);
            if (!in_array(strtolower($nama), $skip, true) && strlen($nama) >= 3) {
                if (\App\Models\User::where('name', 'like', "%{$nama}%")->exists()) {
                    return $nama;
                }
            }
        }

        $words = preg_split('/\s+/', strtolower(trim($pesan)));
        for ($i = 0; $i < count($words) - 1; $i++) {
            $kandidat = $words[$i] . ' ' . $words[$i + 1];
            if (strlen($kandidat) < 5)
                continue;
            if (\App\Models\User::where('name', 'like', "%{$kandidat}%")->exists()) {
                return $kandidat;
            }
        }

        return null;
    }

    protected function countAssetByStatus(string $lower): ?array
    {
        $status = $this->detectStatus($lower);
        if (!$status) {
            return null;
        }

        $category = $this->detectCategory($lower);
        $brand = $this->detectBrand($lower);

        $q = Asset::where('status', $status);

        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand) {
            $q->where('brand', 'like', "%{$brand}%");
        }

        $count = $q->count();
        $label = $this->statusLabel($status);
        $filter = trim(($category ?? '') . ' ' . ($brand ?? ''));

        $jawaban = "Ada **{$count}** aset" . ($filter ? " {$filter}" : "") . " dengan status **{$label}**.";

        if ($count > 0) {
            $samples = (clone $q)->with('category')->limit(5)->get();
            $jawaban .= "\n\nContoh:\n";
            foreach ($samples as $a) {
                $jawaban .= "• {$a->serial_number}";
                if ($a->hostname) {
                    $jawaban .= " ({$a->hostname})";
                }
                $jawaban .= " — {$a->brand} {$a->model}\n";
            }
            if ($count > 5) {
                $jawaban .= "\n... dan **" . ($count - 5) . "** lainnya. Ketik \"**lanjut**\" untuk lihat semua.";
            }
        }

        session()->put('last_query_context', [
            'type' => 'asset_by_status',
            'status' => $status,
            'category' => $category,
            'brand' => $brand,
            'offset' => 5,
            'time' => now()->toDateTimeString(),
        ]);

        return [$jawaban, 'database'];
    }

    protected function listAssetByStatus(string $lower): ?array
    {
        $status = $this->detectStatus($lower);
        if (!$status) {
            return null;
        }

        $total = Asset::where('status', $status)->count();
        $assets = Asset::where('status', $status)->with('category')->limit(10)->get();

        if ($assets->isEmpty()) {
            return ["Tidak ada aset dengan status **{$this->statusLabel($status)}**.", 'database'];
        }

        $jawaban = "Daftar aset **{$this->statusLabel($status)}** (menampilkan " . $assets->count() . " dari {$total}):\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        session()->put('last_query_context', [
            'type' => 'asset_by_status',
            'status' => $status,
            'offset' => 10,
            'time' => now()->toDateTimeString(),
        ]);

        if ($total > 10) {
            $jawaban .= "\nSisa: **" . ($total - 10) . "**. Ketik \"lanjut\" untuk lihat berikutnya.";
        }

        return [$jawaban, 'database'];
    }

    protected function findAssetBySerial(string $serial): array
    {
        $asset = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->where('serial_number', 'like', "%{$serial}%")
            ->orWhere('hostname', 'like', "%{$serial}%")
            ->orWhere('asset_code', 'like', "%{$serial}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan ID **{$serial}** tidak ditemukan.", 'database'];
        }

        $jawaban = "**{$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Status: {$this->statusLabel($asset->status)}\n"
            . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n"
            . "• Pemegang: " . ($asset->currentUser?->name ?? '-') . "\n"
            . "• Lokasi: " . ($asset->currentLocation?->full_name ?? '-');
        return [
            $jawaban,
            'database',
            [
                'type' => 'asset',
                'asset_id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'hostname' => $asset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function whoHoldsAsset(string $identifier): array
    {
        $asset = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->where('serial_number', 'like', "%{$identifier}%")
            ->orWhere('hostname', 'like', "%{$identifier}%")
            ->orWhere('asset_code', 'like', "%{$identifier}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan ID **{$identifier}** tidak ditemukan.", 'database'];
        }

        $jawaban = "🔍 **{$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n"
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Status: {$this->statusLabel($asset->status)}\n"
            . "• Hak Kepemilikan: " . ($asset->ownership_type === 'owned' ? '🟢 Hak Milik' : '🟠 Sewa') . "\n";

        if ($asset->currentUser) {
            $jawaban .= "\n👤 **Pemegang saat ini:** {$asset->currentUser->name}";
            if ($asset->currentUser->position) {
                $jawaban .= " ({$asset->currentUser->position})";
            }
        } else {
            $jawaban .= "\n👤 **Pemegang saat ini:** *tidak ada* (aset tersedia)";
        }

        if ($asset->currentLocation) {
            $jawaban .= "\n📍 **Lokasi:** {$asset->currentLocation->full_name}";
        }
        return [
            $jawaban,
            'database',
            [
                'type' => 'asset',
                'asset_id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'hostname' => $asset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function listLowStockConsumable(): array
    {
        $q = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available');

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Semua konsumable stoknya aman. ✅", 'database'];
        }

        $jawaban = "⚠️ **{$total} konsumable** stoknya rendah:\n\n";
        foreach ($items as $c) {
            $jawaban .= "• {$c->name}: {$c->stock_available}/{$c->stock_minimum} {$c->unit}\n";
        }

        session()->put('last_query_context', [
            'type' => 'low_stock_consumable',
            'offset' => 10,
            'time' => now()->toDateTimeString(),
        ]);

        if ($total > 10) {
            $jawaban .= "\nSisa: **" . ($total - 10) . "**. Ketik \"lanjut\" untuk lihat berikutnya.";
        }

        return [$jawaban, 'database'];
    }

    protected function listOverdueLoans(): array
    {
        $q = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date');

        $total = $q->count();
        $loans = (clone $q)->limit(10)->get();

        if ($loans->isEmpty()) {
            return ["Tidak ada peminjaman yang terlambat. ✅", 'database'];
        }

        $jawaban = "**{$total} peminjaman terlambat**:\n\n";
        foreach ($loans as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            $jawaban .= " (jatuh tempo {$l->due_date?->diffForHumans()})\n";
        }

        session()->put('last_query_context', [
            'type' => 'overdue_loans',
            'offset' => 10,
            'time' => now()->toDateTimeString(),
        ]);

        if ($total > 10) {
            $jawaban .= "\nSisa: **" . ($total - 10) . "**. Ketik \"lanjut\" untuk lihat berikutnya.";
        }

        return [$jawaban, 'database'];
    }

    protected function listActiveLoans(): array
    {
        $q = AssetLoan::where('status', 'borrowed')
            ->with(['asset', 'user'])
            ->orderByDesc('loan_date');

        $total = $q->count();
        $loans = (clone $q)->limit(10)->get();

        if ($loans->isEmpty()) {
            return ["Tidak ada peminjaman aktif saat ini.", 'database'];
        }

        $jawaban = "**{$total} peminjaman aktif**:\n\n";
        foreach ($loans as $l) {
            $jawaban .= "• {$l->asset?->serial_number} — {$l->user?->name}";
            if ($l->due_date) {
                $jawaban .= " (jatuh tempo {$l->due_date->format('d M Y')})";
            }
            $jawaban .= "\n";
        }

        session()->put('last_query_context', [
            'type' => 'active_loans',
            'offset' => 10,
            'time' => now()->toDateTimeString(),
        ]);

        if ($total > 10) {
            $jawaban .= "\nSisa: **" . ($total - 10) . "**. Ketik \"lanjut\" untuk lihat berikutnya.";
        }

        return [$jawaban, 'database'];
    }

    protected function countMaintenance(): array
    {
        $count = AssetMaintenance::whereIn('status', ['open', 'in_progress'])->count();
        return ["Ada **{$count} aset** sedang dalam perbaikan.", 'database'];
    }

    protected function totalAssetValue(): array
    {
        $total = Asset::where('ownership_type', 'owned')->sum('purchase_price');
        $formatted = 'Rp ' . number_format($total, 0, ',', '.');
        return ["Total nilai aset (hak milik): **{$formatted}**", 'database'];
    }

    protected function listActiveAssignments(): array
    {
        $q = AssetAssignment::whereNull('returned_at')
            ->with(['asset', 'user'])
            ->orderByDesc('assigned_at');

        $total = $q->count();
        $assignments = (clone $q)->limit(10)->get();

        if ($assignments->isEmpty()) {
            return ["Tidak ada serah terima aktif saat ini.", 'database'];
        }

        $jawaban = "**{$total} serah terima aktif**:\n\n";
        foreach ($assignments as $a) {
            $jawaban .= "• {$a->asset?->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->user?->name}\n";
        }

        session()->put('last_query_context', [
            'type' => 'active_assignments',
            'offset' => 10,
            'time' => now()->toDateTimeString(),
        ]);

        if ($total > 10) {
            $jawaban .= "\nSisa: **" . ($total - 10) . "**. Ketik \"lanjut\" untuk lihat berikutnya.";
        }

        return [$jawaban, 'database'];
    }

    protected function countCategories(): array
    {
        $total = AssetCategory::count();
        $active = AssetCategory::where('is_active', true)->count();
        return ["Ada **{$total} kategori** aset (**{$active} aktif**).", 'database'];
    }

    protected function countAssetTypes(): array
    {
        $total = AssetType::count();
        $brands = AssetType::select('brand')->distinct()->count();
        return ["Ada **{$total} brand & model** terdaftar dari **{$brands} brand**.", 'database'];
    }

    protected function countAssetByOwnership(string $lower): ?array
    {
        $ownership = $this->detectOwnership($lower);
        if (!$ownership) {
            return null;
        }

        $category = $this->detectCategory($lower);
        $brand = $this->detectBrand($lower);

        $q = Asset::where('ownership_type', $ownership);

        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand) {
            $q->where('brand', 'like', "%{$brand}%");
        }

        $count = $q->count();
        $label = $this->ownershipLabel($ownership);
        $filter = trim(($category ?? '') . ' ' . ($brand ?? ''));

        $jawaban = "Ada **{$count}** aset" . ($filter ? " {$filter}" : "") . " dengan status **{$label}**.";

        if ($count > 0) {
            $samples = (clone $q)->with('category')->limit(5)->get();
            $jawaban .= "\n\nContoh:\n";
            foreach ($samples as $a) {
                $jawaban .= "• {$a->serial_number}";
                if ($a->hostname) {
                    $jawaban .= " ({$a->hostname})";
                }
                $jawaban .= " — {$a->brand} {$a->model}\n";
            }
            if ($count > 5) {
                $jawaban .= "\n... dan **" . ($count - 5) . "** lainnya. Ketik \"**lanjut**\" untuk lihat semua.";
            }
        }

        session()->put('last_query_context', [
            'type' => 'asset_by_ownership',
            'ownership' => $ownership,
            'category' => $category,
            'brand' => $brand,
            'offset' => 5,
            'time' => now()->toDateTimeString(),
        ]);

        return [$jawaban, 'database'];
    }

    protected function topAssetBy(string $lower): array
    {
        $status = $this->detectStatus($lower);
        $category = $this->detectCategory($lower);

        $groupBy = 'model';
        if ($this->matchAny($lower, ['brand', 'merek', 'merk'])) {
            $groupBy = 'brand';
        }

        $q = Asset::query();

        if ($status) {
            $q->where('status', $status);
        }
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }

        $results = (clone $q)
            ->select($groupBy, DB::raw('COUNT(*) as total'))
            ->whereNotNull($groupBy)
            ->groupBy($groupBy)
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $totalAll = (clone $q)->count();

        if ($results->isEmpty()) {
            return ["Tidak ada data aset.", 'database'];
        }

        $label = match ($groupBy) {
            'brand' => 'Brand',
            'model' => 'Model/Tipe',
            default => 'Kategori',
        };

        $filterDesc = trim(
            ($category ? " {$category}" : '') .
            ($status ? " (" . $this->statusLabel($status) . ")" : '')
        );

        $jawaban = "**Top {$label}** aset{$filterDesc} (dari total **{$totalAll}** unit):\n\n";

        foreach ($results as $i => $r) {
            $nama = $r->$groupBy;
            $count = $r->total;
            $percent = $totalAll > 0 ? round(($count / $totalAll) * 100, 1) : 0;

            $jawaban .= ($i + 1) . ". **{$nama}** — {$count} unit ({$percent}%)\n";
        }

        return [
            $jawaban,
            'database',
            [
                'type' => 'top_asset_by_model',
                'model' => $results->first()->$groupBy ?? null,
                'group_by' => $groupBy,
                'category' => $category,
                'status' => $status,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function listAssetsByCategory(string $lower): array
    {
        $category = $this->detectCategory($lower);
        $status = $this->detectStatus($lower);
        $ownership = $this->detectOwnership($lower);

        $q = Asset::with('category')->where(function ($x) use ($category) {
            $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                ->orWhere('model', 'like', "%{$category}%");
        });

        if ($status) {
            $q->where('status', $status);
        }
        if ($ownership) {
            $q->where('ownership_type', $ownership);
        }

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset kategori **{$category}**" . ($status ? " dengan status {$this->statusLabel($status)}" : "") . ".", 'database'];
        }

        $filterDesc = " kategori **{$category}**";
        if ($status) {
            $filterDesc .= " status **{$this->statusLabel($status)}**";
        }
        if ($ownership) {
            $filterDesc .= " **{$this->ownershipLabel($ownership)}**";
        }

        $jawaban = "📦 **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        if ($total > 10) {
            $jawaban .= "\nMenampilkan 10 pertama dari {$total}.";
        }

        return [$jawaban, 'database'];
    }

    protected function listAssetsByBrand(string $lower): array
    {
        $brand = $this->detectBrand($lower);
        $status = $this->detectStatus($lower);
        $ownership = $this->detectOwnership($lower);

        $q = Asset::with('category')->where('brand', 'like', "%{$brand}%");

        if ($status) {
            $q->where('status', $status);
        }
        if ($ownership) {
            $q->where('ownership_type', $ownership);
        }

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset brand **{$brand}**.", 'database'];
        }

        $filterDesc = " brand **{$brand}**";
        if ($status) {
            $filterDesc .= " status **{$this->statusLabel($status)}**";
        }
        if ($ownership) {
            $filterDesc .= " **{$this->ownershipLabel($ownership)}**";
        }

        $jawaban = "🏷️ **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->model}\n";
        }

        if ($total > 10) {
            $jawaban .= "\nMenampilkan 10 pertama dari {$total}.";
        }

        return [$jawaban, 'database'];
    }

    protected function listAssetsByOwnership(string $lower): array
    {
        $ownership = $this->detectOwnership($lower);
        $category = $this->detectCategory($lower);
        $brand = $this->detectBrand($lower);
        $status = $this->detectStatus($lower);

        $q = Asset::with('category')->where('ownership_type', $ownership);

        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }
        if ($brand) {
            $q->where('brand', 'like', "%{$brand}%");
        }
        if ($status) {
            $q->where('status', $status);
        }

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset dengan hak kepemilikan **{$this->ownershipLabel($ownership)}**.", 'database'];
        }

        $filterDesc = " **{$this->ownershipLabel($ownership)}**";
        if ($category) {
            $filterDesc .= " kategori **{$category}**";
        }
        if ($brand) {
            $filterDesc .= " brand **{$brand}**";
        }
        if ($status) {
            $filterDesc .= " status **{$this->statusLabel($status)}**";
        }

        $jawaban = "📋 **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        if ($total > 10) {
            $jawaban .= "\nMenampilkan 10 pertama dari {$total}.";
        }

        return [$jawaban, 'database'];
    }

    protected function assetsByYear(int $year): array
    {
        $q = Asset::with('category')->whereYear('purchase_date', $year);

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset yang dibeli tahun **{$year}**.", 'database'];
        }

        $jawaban = "📅 **{$total} aset dibeli tahun {$year}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        if ($total > 10) {
            $jawaban .= "\nMenampilkan 10 pertama dari {$total}.";
        }

        return [$jawaban, 'database'];
    }

    protected function assetsByYearSummary(): array
    {
        $data = Asset::selectRaw('YEAR(purchase_date) as year, COUNT(*) as total')
            ->whereNotNull('purchase_date')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data tahun pembelian.", 'database'];
        }

        $lines = ["📅 **Aset per Tahun Pembelian:**\n"];
        $grandTotal = 0;
        foreach ($data as $row) {
            $lines[] = "• **{$row->year}**: {$row->total} unit";
            $grandTotal += $row->total;
        }
        $lines[] = "\nTotal: **{$grandTotal}** unit";

        return [implode("\n", $lines), 'database'];
    }

    protected function listAssetsByUser(string $pesan): array
    {
        $nama = $this->extractUserName($pesan);

        if (!$nama) {
            return ["Sebutkan nama user dengan jelas. Contoh: \"aset yang dipegang Budi\".", 'database'];
        }

        $user = \App\Models\User::where('name', 'like', "%{$nama}%")->first();

        if (!$user) {
            return ["User **{$nama}** tidak ditemukan.", 'database'];
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return ["User **{$user->name}** sedang tidak memegang aset.", 'database'];
        }

        $jawaban = "👤 **{$user->name}** memegang **{$assets->count()}** aset:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• **{$a->serial_number}**";
            if ($a->hostname) {
                $jawaban .= " ({$a->hostname})";
            }
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }
        $firstAsset = $assets->first();
        return [
            $jawaban,
            'database',
            [
                'type' => 'user_assets',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'asset_id' => $firstAsset->id,
                'serial_number' => $firstAsset->serial_number,
                'hostname' => $firstAsset->hostname,
                'time' => now()->toDateTimeString(),
            ],
        ];
    }

    protected function assetsExpiringWarranty(): array
    {
        $assets = Asset::whereNotNull('warranty_expire')
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->orderBy('warranty_expire')
            ->limit(15)
            ->get();

        if ($assets->isEmpty()) {
            return ["✅ Tidak ada garansi yang berakhir dalam 60 hari ke depan.", 'database'];
        }

        $total = Asset::whereNotNull('warranty_expire')
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->count();

        $lines = ["⚠️ **{$total} Aset Garansi Berakhir < 60 Hari:**\n"];
        foreach ($assets as $a) {
            $days = $a->warranty_expire->diffInDays(now());
            $lines[] = "• {$a->serial_number} ({$a->brand} {$a->model}) — **{$days} hari lagi**";
        }

        if ($total > 15) {
            $lines[] = "\nMenampilkan 15 pertama dari {$total}.";
        }

        return [implode("\n", $lines), 'database'];
    }

    protected function assetsExpiringContract(): array
    {
        $ownerships = \App\Models\AssetOwnership::whereNotNull('contract_end')
            ->whereDate('contract_end', '>=', today())
            ->whereDate('contract_end', '<=', now()->addDays(30))
            ->with(['asset', 'vendor'])
            ->orderBy('contract_end')
            ->limit(15)
            ->get();

        if ($ownerships->isEmpty()) {
            return ["✅ Tidak ada kontrak sewa yang berakhir dalam 30 hari ke depan.", 'database'];
        }

        $total = \App\Models\AssetOwnership::whereNotNull('contract_end')
            ->whereDate('contract_end', '>=', today())
            ->whereDate('contract_end', '<=', now()->addDays(30))
            ->count();

        $lines = ["⚠️ **{$total} Kontrak Sewa Berakhir < 30 Hari:**\n"];
        foreach ($ownerships as $o) {
            $asset = $o->asset;
            if (!$asset) {
                continue;
            }
            $days = $o->contract_end->diffInDays(now());
            $lines[] = "• {$asset->serial_number} ({$asset->brand} {$asset->model}) — "
                . ($o->vendor?->name ?? '-') . " — **{$days} hari lagi**";
        }

        if ($total > 15) {
            $lines[] = "\nMenampilkan 15 pertama dari {$total}.";
        }

        return [implode("\n", $lines), 'database'];
    }

    protected function assetsNeedingRetire(): array
    {
        $oldAssets = Asset::whereIn('status', ['available', 'in_use'])
            ->whereNotNull('purchase_date')
            ->whereDate('purchase_date', '<=', now()->subYears(5))
            ->with('category')
            ->orderBy('purchase_date')
            ->limit(15)
            ->get();

        if ($oldAssets->isEmpty()) {
            return ["✅ Tidak ada aset yang perlu dipensiunkan saat ini.", 'database'];
        }

        $total = Asset::whereIn('status', ['available', 'in_use'])
            ->whereNotNull('purchase_date')
            ->whereDate('purchase_date', '<=', now()->subYears(5))
            ->count();

        $lines = ["🔴 **{$total} Aset Rekomendasi Pensiun (>5 tahun):**\n"];
        foreach ($oldAssets as $a) {
            $age = $a->purchase_date->diffInYears(now());
            $lines[] = "• {$a->serial_number} ({$a->brand} {$a->model}) — **{$age} tahun**";
        }

        if ($total > 15) {
            $lines[] = "\nMenampilkan 15 pertama dari {$total}.";
        }

        return [implode("\n", $lines), 'database'];
    }

    protected function compareOwnership(string $lower): array
    {
        $category = $this->detectCategory($lower);

        $q = Asset::query();
        if ($category) {
            $q->where(function ($x) use ($category) {
                $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                    ->orWhere('model', 'like', "%{$category}%");
            });
        }

        $owned = (clone $q)->where('ownership_type', 'owned')->count();
        $leased = (clone $q)->where('ownership_type', 'leased')->count();
        $total = $owned + $leased;

        if ($total === 0) {
            return ["Tidak ada data aset.", 'database'];
        }

        $ownedPct = round(($owned / $total) * 100, 1);
        $leasedPct = round(($leased / $total) * 100, 1);

        $ownedValue = (clone $q)->where('ownership_type', 'owned')->sum('purchase_price') ?? 0;
        $leasedMonthly = \App\Models\AssetOwnership::where('ownership_type', 'leased')
            ->when($category, function ($x) use ($category) {
                $x->whereHas('asset', function ($qa) use ($category) {
                    $qa->where(function ($y) use ($category) {
                        $y->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                            ->orWhere('model', 'like', "%{$category}%");
                    });
                });
            })
            ->sum('monthly_cost') ?? 0;

        $filterDesc = $category ? " kategori **{$category}**" : "";

        $jawaban = "📊 **Perbandingan Hak Milik vs Sewa{$filterDesc}**\n\n"
            . "• 🟢 **Hak Milik**: {$owned} unit ({$ownedPct}%)\n"
            . "  Nilai pembelian: Rp " . number_format($ownedValue, 0, ',', '.') . "\n"
            . "• 🟠 **Sewa**: {$leased} unit ({$leasedPct}%)\n"
            . "  Biaya bulanan: Rp " . number_format($leasedMonthly, 0, ',', '.') . "/bulan\n\n"
            . "**Total: {$total} unit**";

        return [$jawaban, 'database'];
    }

    protected function topUsersWithAssets(): array
    {
        $users = \App\Models\User::withCount('currentAssets')
            ->having('current_assets_count', '>', 0)
            ->orderByDesc('current_assets_count')
            ->limit(10)
            ->get();

        if ($users->isEmpty()) {
            return ["Belum ada user yang memegang aset.", 'database'];
        }

        $lines = ["👥 **Top 10 User Pemegang Aset:**\n"];
        foreach ($users as $i => $u) {
            $lines[] = ($i + 1) . ". **{$u->name}**"
                . ($u->position ? " ({$u->position})" : "")
                . " — **{$u->current_assets_count}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    protected function topLocations(): array
    {
        $locations = \App\Models\Location::withCount('currentAssets')
            ->having('current_assets_count', '>', 0)
            ->orderByDesc('current_assets_count')
            ->limit(10)
            ->get();

        if ($locations->isEmpty()) {
            return ["Belum ada data lokasi dengan aset.", 'database'];
        }

        $lines = ["📍 **Top 10 Lokasi dengan Aset Terbanyak:**\n"];
        foreach ($locations as $i => $l) {
            $lines[] = ($i + 1) . ". **{$l->full_name}** — **{$l->current_assets_count}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    protected function totalStatusSummary(): array
    {
        $statuses = [
            'available' => ['label' => 'Tersedia', 'emoji' => '🟢'],
            'in_use' => ['label' => 'Dipakai', 'emoji' => '🔵'],
            'loaned' => ['label' => 'Dipinjam', 'emoji' => '🟡'],
            'maintenance' => ['label' => 'Perbaikan', 'emoji' => '🟠'],
            'retired' => ['label' => 'Pensiun', 'emoji' => '⚫'],
            'lost' => ['label' => 'Hilang', 'emoji' => '🔴'],
        ];

        $lines = ["📊 **Summary Status Aset:**\n"];
        $total = 0;
        foreach ($statuses as $key => $info) {
            $count = Asset::where('status', $key)->count();
            $lines[] = "{$info['emoji']} {$info['label']}: **{$count}** unit";
            $total += $count;
        }
        $lines[] = "\n**Total: {$total} unit**";

        return [implode("\n", $lines), 'database'];
    }

    protected function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }

    protected function matchWord(string $haystack, string $word): bool
    {
        return (bool) preg_match('/\b' . preg_quote($word, '/') . '\b/i', $haystack);
    }

    protected function formatConsumableInfo($consumable, string $lower): array
    {
        if ($this->matchAny($lower, ['stok', 'stock'])) {
            $jawaban = "📦 **Stok {$consumable->name}**\n"
                . "• Tersedia: **{$consumable->stock_available}** {$consumable->unit}\n"
                . "• Minimum: {$consumable->stock_minimum}\n"
                . "• Total: {$consumable->stock_total}\n";

            if ($this->isPrivileged() && !empty($consumable->last_price)) {
                $harga = 'Rp ' . number_format((float) $consumable->last_price, 0, ',', '.');
                $jawaban .= "• Harga terakhir: {$harga}\n";
            }

            if ($consumable->stock_available <= $consumable->stock_minimum) {
                $jawaban .= "\n⚠️ **Stok rendah!** Perlu segera restock.";
            }

            return [$jawaban, 'database'];
        }

        if ($this->matchAny($lower, ['terakhir', 'last'])) {
            $last = \App\Models\ConsumableTransaction::where('consumable_id', $consumable->id)
                ->where('type', 'out')
                ->with('user')
                ->latest('transaction_date')
                ->first();

            if (!$last) {
                return ["Belum ada transaksi keluar untuk **{$consumable->name}**.", 'database'];
            }

            $userName = $last->user?->name ?? '-';
            $date = $last->transaction_date?->format('d M Y') ?? '-';
            $qty = $last->quantity ?? 0;

            return [
                "📤 **Terakhir Dipakai: {$consumable->name}**\n"
                . "• Oleh: **{$userName}**\n"
                . "• Tanggal: {$date}\n"
                . "• Jumlah: {$qty} {$consumable->unit}",
                'database'
            ];
        }
        $transactions = \App\Models\ConsumableTransaction::where('consumable_id', $consumable->id)
            ->with('user')
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get();

        if ($transactions->isEmpty()) {
            return ["Belum ada transaksi untuk **{$consumable->name}**.", 'database'];
        }

        $jawaban = "📋 **Riwayat Transaksi: {$consumable->name}**\n\n";
        foreach ($transactions as $t) {
            $icon = match ($t->type) {
                'in' => '📥',
                'out' => '📤',
                'return' => '↩️',
                default => '•',
            };
            $user = $t->user?->name ?? 'Admin';
            $date = $t->transaction_date?->format('d M Y') ?? '-';

            $jawaban .= "{$icon} {$t->type} — {$t->quantity} {$consumable->unit} — {$user} ({$date})\n";
        }

        return [$jawaban, 'database'];
    }

    protected function listAssetsByVendor(string $pesan): array
    {
        preg_match('/(?:vendor|supplier|dari)\s+([a-z\s]+?)(?:\s|$|punya|ada)/i', $pesan, $m);
        $nama = trim($m[1] ?? '');

        if (!$nama) {
            return ["Sebutkan nama vendor. Contoh: \"aset dari vendor PT Lenovo\".", 'database'];
        }

        $vendor = \App\Models\Vendor::where('name', 'like', "%{$nama}%")->first();

        if (!$vendor) {
            return ["Vendor **{$nama}** tidak ditemukan.", 'database'];
        }

        $ownerships = \App\Models\AssetOwnership::where('vendor_id', $vendor->id)
            ->with('asset')
            ->limit(20)
            ->get();

        if ($ownerships->isEmpty()) {
            return ["Vendor **{$vendor->name}** **tidak memiliki aset** terdaftar.", 'database'];
        }

        $total = \App\Models\AssetOwnership::where('vendor_id', $vendor->id)->count();

        $jawaban = "🏢 **Aset dari Vendor: {$vendor->name}** ({$total} unit)\n\n";
        foreach ($ownerships as $o) {
            $a = $o->asset;
            if (!$a)
                continue;

            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        if ($total > 20) {
            $jawaban .= "\n_Menampilkan 20 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    protected function listAssetHoldersByCategory(string $category): array
    {
        try {
            $assets = Asset::with(['category', 'currentUser', 'currentLocation'])
                ->where(function ($x) use ($category) {
                    $x->whereHas('category', fn($c) => $c->where('name', 'like', "%{$category}%"))
                        ->orWhere('model', 'like', "%{$category}%");
                })
                ->whereHas('currentUser')
                ->limit(50)
                ->get();
        } catch (\Throwable $e) {
            \Log::warning('listAssetHoldersByCategory failed: ' . $e->getMessage());
            return ["Terjadi kesalahan saat mencari data pemegang aset.", 'database'];
        }

        if ($assets->isEmpty()) {
            return [
                "Belum ada aset kategori **{$category}** yang sedang dipegang siapa pun.",
                'database'
            ];
        }

        $grouped = [];
        foreach ($assets as $a) {
            $userName = $a->currentUser?->name ?? 'Tidak diketahui';
            $grouped[$userName][] = $a;
        }

        $total = $assets->count();
        $userCount = count($grouped);

        $jawaban = "👥 **Pemegang Aset kategori {$category}**\n"
            . "({$total} unit dipegang oleh {$userCount} orang):\n\n";

        $i = 0;
        foreach ($grouped as $userName => $items) {
            if ($i >= 10) {
                $jawaban .= "\n_... dan " . (count($grouped) - 10) . " orang lainnya._";
                break;
            }

            $jawaban .= "👤 **{$userName}** — " . count($items) . " unit\n";
            foreach ($items as $a) {
                $jawaban .= "  • {$a->serial_number}";
                if ($a->hostname) {
                    $jawaban .= " ({$a->hostname})";
                }
                $jawaban .= " — {$a->brand} {$a->model}\n";
            }
            $jawaban .= "\n";
            $i++;
        }

        return [$jawaban, 'database'];
    }

    protected function containsSnPattern(string $pesan): bool
    {
        return (bool) preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*\b/i', $pesan);
    }

    protected function detectStatus(string $lower): ?string
    {
        foreach ($this->statusMap as $kata => $status) {
            if (str_contains($lower, $kata)) {
                return $status;
            }
        }
        return null;
    }

    protected function detectCategory(string $lower): ?string
    {
        foreach ($this->categories as $c) {
            if ($this->matchWord($lower, $c)) {
                return $c;
            }
        }
        return null;
    }

    protected function detectBrand(string $lower): ?string
    {
        foreach ($this->brands as $b) {
            if ($this->matchWord($lower, $b)) {
                return $b;
            }
        }
        return null;
    }

    protected function detectOwnership(string $lower): ?string
    {
        foreach ($this->ownershipMap as $kata => $ownership) {
            if (str_contains($lower, $kata)) {
                return $ownership;
            }
        }
        return null;
    }

    protected function ownershipLabel(string $ownership): string
    {
        return match ($ownership) {
            'owned' => 'hak milik',
            'leased' => 'sewa',
            default => $ownership,
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'tersedia',
            'in_use' => 'sedang dipakai',
            'loaned' => 'dipinjam',
            'maintenance' => 'dalam perbaikan',
            'retired' => 'pensiun',
            'lost' => 'hilang',
            default => $status,
        };
    }
}
