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
        if (preg_match('/(?:sn|serial|hostname)[\s:]*([A-Z0-9\-_]+)/i', $pesan, $m)) {
            return $this->findAssetBySerial($m[1]);
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
            ->first();

        if (!$asset) {
            return ["Aset dengan SN/hostname **{$serial}** tidak ditemukan.", 'database'];
        }

        $jawaban = "**{$asset->serial_number}**";
        if ($asset->hostname)
            $jawaban .= " ({$asset->hostname})";
        $jawaban .= "\n";
        $jawaban .= "• Kategori: " . ($asset->category?->name ?? '-') . "\n";
        $jawaban .= "• Brand/Model: {$asset->brand} {$asset->model}\n";
        $jawaban .= "• Status: {$this->statusLabel($asset->status)}\n";
        $jawaban .= "• Pemakai: " . ($asset->currentUser?->name ?? '-') . "\n";
        $jawaban .= "• Lokasi: " . ($asset->currentLocation?->full_name ?? '-');

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
