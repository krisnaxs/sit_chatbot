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
    protected array $statusMap = [
        'ready' => 'available',
        'tersedia' => 'available',
        'available' => 'available',
        'siap' => 'available',
        'kosong' => 'available',
        'dipakai' => 'in_use',
        'in_use' => 'in_use',
        'in use' => 'in_use',
        'digunakan' => 'in_use',
        'dipinjam' => 'loaned',
        'pinjam' => 'loaned',
        'loaned' => 'loaned',
        'perbaikan' => 'maintenance',
        'maintenance' => 'maintenance',
        'rusak' => 'maintenance',
        'servis' => 'maintenance',
        'pensiun' => 'retired',
        'retired' => 'retired',
        'hilang' => 'lost',
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

    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // 1. Jumlah aset by status
        if (
            $this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'total']) &&
            $this->matchAny($lower, array_keys($this->statusMap))
        ) {
            return $this->countAssetByStatus($lower);
        }

        // 2. Daftar aset by status
        if (
            $this->matchAny($lower, ['apa saja', 'sebutkan', 'daftar', 'list', 'tampilkan', 'lihat']) &&
            $this->matchAny($lower, array_keys($this->statusMap))
        ) {
            return $this->listAssetByStatus($lower);
        }

        // 3. Aset by serial / hostname
        if (preg_match('/\b([A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*)\b/i', $pesan, $m)) {
            $identifier = strtoupper($m[1]);

            // Cek dulu apakah identifier ini benar-benar ada di database
            $exists = Asset::where('serial_number', 'like', "%{$identifier}%")
                ->orWhere('hostname', 'like', "%{$identifier}%")
                ->orWhere('asset_code', 'like', "%{$identifier}%")
                ->exists();

            if ($exists) {
                // Kalau ada pertanyaan "siapa yang pegang?" → jawab dengan handler khusus
                if ($this->matchAny($lower, ['siapa', 'pegang', 'memegang', 'pakai', 'gunakan', 'dipegang', 'pemakai'])) {
                    return $this->whoHoldsAsset($identifier);
                }
                // Kalau tidak, tampilkan detail aset
                return $this->findAssetBySerial($identifier);
            }
        }

        // ============================================================
        // 3b. Explicit prefix (SN:, serial:, hostname:)
        // ============================================================
        if (preg_match('/(?:sn|serial|hostname|asset_code)[\s:]+([A-Z0-9\-_]+)/i', $pesan, $m)) {
            $identifier = $m[1];
            if ($this->matchAny($lower, ['siapa', 'pegang', 'memegang', 'pakai', 'gunakan', 'dipegang'])) {
                return $this->whoHoldsAsset($identifier);
            }
            return $this->findAssetBySerial($identifier);
        }

        // 4. Konsumable low stock
        if (
            $this->matchAny($lower, ['konsumable', 'consumable', 'atk', 'habis pakai']) &&
            $this->matchAny($lower, ['habis', 'stok', 'stock', 'low', 'rendah', 'kosong'])
        ) {
            return $this->listLowStockConsumable();
        }

        // 5. Peminjaman terlambat
        if (
            $this->matchAny($lower, ['pinjam', 'peminjaman', 'loan']) &&
            $this->matchAny($lower, ['telat', 'terlambat', 'overdue', 'lewat'])
        ) {
            return $this->listOverdueLoans();
        }

        // 6. Berapa aset sedang diperbaiki
        if (
            $this->matchAny($lower, ['perbaikan', 'maintenance', 'servis', 'rusak']) &&
            $this->matchAny($lower, ['berapa', 'ada', 'sedang', 'lagi', 'jumlah'])
        ) {
            return $this->countMaintenance();
        }

        // 7. Nilai total aset
        if ($this->matchAny($lower, ['nilai', 'harga total', 'total pembelian', 'harga aset'])) {
            return $this->totalAssetValue();
        }

        // 8. Peminjaman aktif
        if (
            $this->matchAny($lower, ['pinjam', 'peminjaman', 'loan']) &&
            $this->matchAny($lower, ['aktif', 'sedang', 'berjalan', 'berapa', 'daftar'])
        ) {
            return $this->listActiveLoans();
        }

        // 9. Serah terima aktif
        if (
            $this->matchAny($lower, ['serah terima', 'assignment', 'pegang']) &&
            $this->matchAny($lower, ['aktif', 'sedang', 'berapa', 'daftar', 'siapa'])
        ) {
            return $this->listActiveAssignments();
        }

        // 10. Jumlah kategori
        if (
            $this->matchAny($lower, ['kategori', 'category']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total', 'daftar'])
        ) {
            return $this->countCategories();
        }

        // 11. Brand & model (asset types)
        if (
            $this->matchAny($lower, ['brand', 'merek', 'model', 'tipe', 'type']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'daftar', 'list'])
        ) {
            return $this->countAssetTypes();
        }

        // 11b. Jumlah aset by ownership (hak milik / sewa)
        if (
            $this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'total']) &&
            $this->matchAny($lower, array_keys($this->ownershipMap))
        ) {
            return $this->countAssetByOwnership($lower);
        }

        // 12. Tipe/model/brand terbanyak — JANGAN match kalau ada "berapa"/"sewa"/"hak milik"
        if (
            $this->matchAny($lower, ['terbanyak', 'paling banyak', 'top', 'tertinggi', 'mayoritas', 'paling sering', 'sering dipakai']) &&
            $this->matchAny($lower, ['type', 'tipe', 'model', 'brand', 'merek', 'merk', 'laptop', 'komputer', 'aset', 'asset', 'barang']) &&
            !$this->matchAny($lower, ['berapa', 'ada berapa', 'jumlah', 'sewa', 'hak milik', 'milik'])
        ) {
            return $this->topAssetBy($lower);
        }
        // ============================================================
        // 13. LIST ASET PER KATEGORI / BRAND / OWNERSHIP
        // ============================================================
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

        // ============================================================
        // 14. ASET PER TAHUN
        // ============================================================
        if (
            $this->matchAny($lower, ['tahun', 'year']) &&
            preg_match('/\b(20\d{2})\b/', $lower, $ym) &&
            $this->matchAny($lower, ['aset', 'asset', 'pembelian', 'beli'])
        ) {
            return $this->assetsByYear((int) $ym[1]);
        }

        if (
            $this->matchAny($lower, ['aset per tahun', 'aset tiap tahun', 'pengadaan per tahun', 'pembelian per tahun'])
        ) {
            return $this->assetsByYearSummary();
        }

        // ============================================================
        // 15. ASET BY USER / LOCATION
        // ============================================================
        if (
            $this->matchAny($lower, ['aset', 'asset']) &&
            $this->matchAny($lower, ['dipegang', 'pegang', 'memegang', 'pakai', 'gunakan']) &&
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'orang'])
        ) {
            return $this->listAssetsByUser($pesan);
        }

        if (
            $this->matchAny($lower, ['aset', 'asset']) &&
            $this->matchAny($lower, ['di ', 'lokasi', 'ruang', 'gedung', 'ruangan']) &&
            !$this->matchAny($lower, ['dipegang', 'dipegang siapa'])
        ) {
            return $this->listAssetsByLocation($pesan);
        }

        // ============================================================
        // 16. GARANSI HAMPIR HABIS
        // ============================================================
        if (
            $this->matchAny($lower, ['garansi', 'warranty']) &&
            $this->matchAny($lower, ['hampir', 'segera', 'berakhir', 'habis', 'expired'])
        ) {
            return $this->assetsExpiringWarranty();
        }

        // ============================================================
        // 17. KONTRAK SEWA HAMPIR BERAKHIR
        // ============================================================
        if (
            $this->matchAny($lower, ['kontrak', 'sewa', 'lease']) &&
            $this->matchAny($lower, ['berakhir', 'habis', 'selesai'])
        ) {
            return $this->assetsExpiringContract();
        }

        // ============================================================
        // 18. ASET TUA / REKOMENDASI PENSIUN
        // ============================================================
        if (
            $this->matchAny($lower, ['tua', 'lama', 'pensiun', 'retire']) &&
            $this->matchAny($lower, ['aset', 'asset', 'rekomendasi', 'harus', 'sebaiknya'])
        ) {
            return $this->assetsNeedingRetire();
        }

        // ============================================================
        // 19. BANDINGKAN OWNED vs LEASED
        // ============================================================
        if (
            $this->matchAny($lower, ['bandingkan', 'perbandingan', 'compare', 'vs', 'dibanding']) &&
            $this->matchAny($lower, ['hak milik', 'owned', 'milik']) &&
            $this->matchAny($lower, ['sewa', 'lease', 'leased'])
        ) {
            return $this->compareOwnership($lower);
        }

        // ============================================================
        // 20. TOP USER / LOKASI
        // ============================================================
        if (
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'mayoritas']) &&
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan']) &&
            $this->matchAny($lower, ['aset', 'asset', 'pegang'])
        ) {
            return $this->topUsersWithAssets();
        }

        if (
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'mayoritas']) &&
            $this->matchAny($lower, ['lokasi', 'ruang', 'gedung', 'ruangan']) &&
            $this->matchAny($lower, ['aset', 'asset'])
        ) {
            return $this->topLocations();
        }

        // ============================================================
        // 21. SUMMARY SEMUA STATUS
        // ============================================================
        if (
            $this->matchAny($lower, ['summary', 'ringkasan', 'rekap', 'statistik']) &&
            $this->matchAny($lower, ['aset', 'asset', 'status'])
        ) {
            return $this->totalStatusSummary();
        }

        return null;
    }

    // ============================================================
    // HANDLERS
    // ============================================================

    protected function countAssetByStatus(string $lower): ?array
    {
        $status = $this->detectStatus($lower);
        if (!$status)
            return null;

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
                if ($a->hostname)
                    $jawaban .= " ({$a->hostname})";
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
        if (!$status)
            return null;

        $total = Asset::where('status', $status)->count();
        $assets = Asset::where('status', $status)->with('category')->limit(10)->get();

        if ($assets->isEmpty()) {
            return ["Tidak ada aset dengan status **{$this->statusLabel($status)}**.", 'database'];
        }

        $jawaban = "Daftar aset **{$this->statusLabel($status)}** (menampilkan " . $assets->count() . " dari {$total}):\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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
            ->orWhere('asset_code', 'like', "%{$serial}%")   // 🆕
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

        return [$jawaban, 'database'];
    }

    /**
     * 🆕 Jawab "siapa yang pegang [hostname/SN]?"
     */
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

        return [$jawaban, 'database'];
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

        $jawaban = "**{$total} konsumable** stoknya rendah:\n\n";
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
            if ($l->due_date)
                $jawaban .= " (jatuh tempo {$l->due_date->format('d M Y')})";
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
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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

    /**
     * 🆕 Hitung aset by ownership (owned / leased).
     */
    protected function countAssetByOwnership(string $lower): ?array
    {
        $ownership = $this->detectOwnership($lower);
        if (!$ownership)
            return null;

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
                if ($a->hostname)
                    $jawaban .= " ({$a->hostname})";
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

    /**
     * 🆕 Top tipe/model/brand aset terbanyak.
     */
    protected function topAssetBy(string $lower): array
    {
        $status = $this->detectStatus($lower);
        $category = $this->detectCategory($lower);

        $groupBy = 'model';
        if ($this->matchAny($lower, ['brand', 'merek', 'merk'])) {
            $groupBy = 'brand';
        }

        $q = Asset::query();

        if ($status)
            $q->where('status', $status);
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

        return [$jawaban, 'database'];
    }

    // ============================================================
    // 🆕 HANDLER BARU
    // ============================================================

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
        if ($status)
            $filterDesc .= " status **{$this->statusLabel($status)}**";
        if ($ownership)
            $filterDesc .= " **{$this->ownershipLabel($ownership)}**";

        $jawaban = "📦 **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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

        if ($status)
            $q->where('status', $status);
        if ($ownership)
            $q->where('ownership_type', $ownership);

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset brand **{$brand}**.", 'database'];
        }

        $filterDesc = " brand **{$brand}**";
        if ($status)
            $filterDesc .= " status **{$this->statusLabel($status)}**";
        if ($ownership)
            $filterDesc .= " **{$this->ownershipLabel($ownership)}**";

        $jawaban = "🏷️ **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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
        if ($brand)
            $q->where('brand', 'like', "%{$brand}%");
        if ($status)
            $q->where('status', $status);

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada aset dengan hak kepemilikan **{$this->ownershipLabel($ownership)}**.", 'database'];
        }

        $filterDesc = " **{$this->ownershipLabel($ownership)}**";
        if ($category)
            $filterDesc .= " kategori **{$category}**";
        if ($brand)
            $filterDesc .= " brand **{$brand}**";
        if ($status)
            $filterDesc .= " status **{$this->statusLabel($status)}**";

        $jawaban = "📋 **{$total} aset{$filterDesc}:**\n\n";
        foreach ($items as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
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
        // Extract nama user
        preg_match('/(?:user|pegawai|karyawan|oleh|dari|pak|bu|mas|mbak|sdr|sdri)\s+([a-z]+)/i', $pesan, $m);
        $nama = $m[1] ?? null;

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
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        return [$jawaban, 'database'];
    }

    protected function listAssetsByLocation(string $pesan): array
    {
        // Extract keyword lokasi
        preg_match('/(?:di|lokasi|ruang|gedung|ruangan)\s+([a-z0-9\s]+?)(?:\?|$|,|\.|$)/i', $pesan, $m);
        $keyword = trim($m[1] ?? '');

        if (!$keyword) {
            return ["Sebutkan nama lokasi dengan jelas. Contoh: \"aset di ruang IT\".", 'database'];
        }

        $location = \App\Models\Location::where('building', 'like', "%{$keyword}%")
            ->orWhere('room', 'like', "%{$keyword}%")
            ->orWhere('full_name', 'like', "%{$keyword}%")
            ->first();

        if (!$location) {
            return ["Lokasi **{$keyword}** tidak ditemukan.", 'database'];
        }

        $assets = $location->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return ["Tidak ada aset di **{$location->full_name}**.", 'database'];
        }

        $jawaban = "📍 **{$location->full_name}** — **{$assets->count()} aset**:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        return [$jawaban, 'database'];
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
            if (!$asset)
                continue;
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

    // ============================================================
    // HELPERS
    // ============================================================

    protected function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n))
                return true;
        }
        return false;
    }

    protected function detectStatus(string $lower): ?string
    {
        foreach ($this->statusMap as $kata => $status) {
            if (str_contains($lower, $kata))
                return $status;
        }
        return null;
    }

    protected function detectCategory(string $lower): ?string
    {
        foreach ($this->categories as $c) {
            if (str_contains($lower, $c))
                return $c;
        }
        return null;
    }

    protected function detectBrand(string $lower): ?string
    {
        foreach ($this->brands as $b) {
            if (str_contains($lower, $b))
                return $b;
        }
        return null;
    }

    protected function detectOwnership(string $lower): ?string
    {
        foreach ($this->ownershipMap as $kata => $ownership) {
            if (str_contains($lower, $kata))
                return $ownership;
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
