<?php

namespace App\Services\Query;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetOwnership;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Str;

class SiamQueryService
{
    /**
     * Coba jawab pertanyaan tentang SIAM.
     * Return array [jawaban, sumber, context] atau null kalau tidak match.
     */
    public function tryAnswer(string $pesan): ?array
    {
        $msg = Str::lower(trim($pesan));

        // Skip kalau terlalu pendek
        if (strlen($msg) < 3) {
            return null;
        }

        // ============================================================
        // 1. TOTAL ASET / RINGKASAN
        // ============================================================
        if ($this->match($msg, ['berapa', 'total', 'jumlah']) && $this->match($msg, ['aset', 'asset'])) {
            // Hak milik
            if ($this->match($msg, ['hak milik', 'owned', 'milik sendiri', 'beli'])) {
                return $this->ownershipBreakdown('owned');
            }

            // Sewa
            if ($this->match($msg, ['sewa', 'lease', 'leased', 'rental'])) {
                return $this->ownershipBreakdown('leased');
            }

            // Status
            if ($this->match($msg, ['tersedia', 'available', 'siap dipakai'])) {
                return $this->statusBreakdown('available');
            }
            if ($this->match($msg, ['dipakai', 'in use', 'in_use', 'digunakan'])) {
                return $this->statusBreakdown('in_use');
            }
            if ($this->match($msg, ['dipinjam', 'loaned'])) {
                return $this->statusBreakdown('loaned');
            }
            if ($this->match($msg, ['perbaikan', 'maintenance', 'rusak', 'servis'])) {
                return $this->statusBreakdown('maintenance');
            }
            if ($this->match($msg, ['hilang', 'lost'])) {
                return $this->statusBreakdown('lost');
            }
            if ($this->match($msg, ['pensiun', 'retired'])) {
                return $this->statusBreakdown('retired');
            }

            // Total semua
            return $this->totalAssets();
        }

        // ============================================================
        // 2. KATEGORI / BRAND / MODEL
        // ============================================================
        if ($this->match($msg, ['kategori', 'category']) && $this->match($msg, ['aset', 'asset', 'list', 'daftar'])) {
            return $this->byCategory();
        }
        if ($this->match($msg, ['brand', 'merek', 'merk']) && $this->match($msg, ['aset', 'asset', 'list', 'daftar'])) {
            return $this->byBrand();
        }
        if ($this->match($msg, ['model', 'tipe', 'type']) && $this->match($msg, ['aset', 'asset', 'list', 'daftar'])) {
            return $this->byModel();
        }

        // ============================================================
        // 3. NILAI ASET
        // ============================================================
        if (
            $this->match($msg, ['nilai', 'harga', 'total pembelian'])
            && $this->match($msg, ['pembelian', 'beli', 'aset', 'aset hak milik'])
        ) {
            return $this->totalPurchaseValue();
        }

        if (
            $this->match($msg, ['biaya', 'cost', 'harga'])
            && $this->match($msg, ['sewa', 'lease', 'bulanan', 'per bulan'])
        ) {
            return $this->monthlyLeaseCost();
        }

        // ============================================================
        // 4. PEMINJAMAN
        // ============================================================
        if ($this->match($msg, ['terlambat', 'overdue', 'telat']) && $this->match($msg, ['pinjam', 'loan', 'peminjaman'])) {
            return $this->overdueLoans();
        }
        if ($this->match($msg, ['berapa', 'total', 'jumlah']) && $this->match($msg, ['pinjam', 'loan', 'peminjaman'])) {
            return $this->totalLoans();
        }

        // ============================================================
        // 5. PERBAIKAN
        // ============================================================
        if ($this->match($msg, ['sering', 'paling', 'top']) && $this->match($msg, ['rusak', 'perbaikan', 'maintenance'])) {
            return $this->topMaintenanced();
        }

        // ============================================================
        // 6. KONSUMABLE
        // ============================================================
        if ($this->match($msg, ['low stock', 'stok rendah', 'stok habis', 'stok minim'])) {
            return $this->lowStockConsumables();
        }
        if ($this->match($msg, ['konsumable', 'consumable', 'barang habis pakai'])) {
            return $this->totalConsumables();
        }

        // ============================================================
        // 7. USER
        // ============================================================
        if ($this->match($msg, ['berapa', 'total', 'jumlah']) && $this->match($msg, ['user', 'pegawai', 'karyawan'])) {
            return $this->totalUsers();
        }
        if (
            $this->match($msg, ['user', 'pegawai', 'karyawan'])
            && $this->match($msg, ['pegang', 'punya', 'memegang', 'memiliki'])
        ) {
            return $this->usersWithAssets();
        }

        return null;
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Cek apakah $msg mengandung salah satu kata dari $keywords.
     */
    private function match(string $msg, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($msg, $kw)) {
                return true;
            }
        }
        return false;
    }

    // ============================================================
    // HANDLERS
    // ============================================================

    private function totalAssets(): array
    {
        $total = Asset::count();
        $owned = Asset::where('ownership_type', 'owned')->count();
        $leased = Asset::where('ownership_type', 'leased')->count();

        $jawaban = "📊 **Total Aset SIAM: {$total} unit**\n\n"
            . "• 🟢 Hak Milik: **{$owned}** unit\n"
            . "• 🟠 Sewa: **{$leased}** unit";

        return [$jawaban, 'database'];
    }

    private function ownershipBreakdown(string $type): array
    {
        $label = $type === 'owned' ? 'Hak Milik' : 'Sewa';
        $emoji = $type === 'owned' ? '🟢' : '🟠';

        $total = Asset::where('ownership_type', $type)->count();

        $byStatus = Asset::where('ownership_type', $type)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $lines = ["{$emoji} **Aset {$label}: {$total} unit**\n"];
        $statusLabels = [
            'available' => 'Tersedia',
            'in_use' => 'Dipakai',
            'loaned' => 'Dipinjam',
            'maintenance' => 'Perbaikan',
            'retired' => 'Pensiun',
            'lost' => 'Hilang',
        ];

        foreach ($statusLabels as $key => $name) {
            $count = $byStatus[$key] ?? 0;
            if ($count > 0) {
                $lines[] = "• {$name}: {$count}";
            }
        }

        return [implode("\n", $lines), 'database'];
    }

    private function statusBreakdown(string $status): array
    {
        $labels = [
            'available' => ['label' => 'Tersedia', 'emoji' => '🟢'],
            'in_use' => ['label' => 'Dipakai', 'emoji' => '🔵'],
            'loaned' => ['label' => 'Dipinjam', 'emoji' => '🟡'],
            'maintenance' => ['label' => 'Perbaikan', 'emoji' => '🟠'],
            'retired' => ['label' => 'Pensiun', 'emoji' => '⚫'],
            'lost' => ['label' => 'Hilang', 'emoji' => '🔴'],
        ];

        $info = $labels[$status];
        $total = Asset::where('status', $status)->count();
        $owned = Asset::where('status', $status)->where('ownership_type', 'owned')->count();
        $leased = Asset::where('status', $status)->where('ownership_type', 'leased')->count();

        $jawaban = "{$info['emoji']} **Aset {$info['label']}: {$total} unit**\n\n"
            . "• 🟢 Hak Milik: {$owned}\n"
            . "• 🟠 Sewa: {$leased}";

        return [$jawaban, 'database'];
    }

    private function byCategory(): array
    {
        $data = Asset::selectRaw('category_id, COUNT(*) as total')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data aset.", 'database'];
        }

        $lines = ["📦 **Aset per Kategori:**\n"];
        foreach ($data as $row) {
            $name = $row->category?->name ?? 'Tanpa Kategori';
            $lines[] = "• {$name}: **{$row->total}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function byBrand(): array
    {
        $data = Asset::selectRaw('brand, COUNT(*) as total')
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data brand.", 'database'];
        }

        $lines = ["🏷️ **Aset per Brand:**\n"];
        foreach ($data as $row) {
            $lines[] = "• {$row->brand}: **{$row->total}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function byModel(): array
    {
        $data = Asset::selectRaw('model, brand, COUNT(*) as total')
            ->whereNotNull('model')
            ->groupBy('model', 'brand')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data model.", 'database'];
        }

        $lines = ["💻 **Aset per Model:**\n"];
        foreach ($data as $row) {
            $lines[] = "• {$row->brand} — {$row->model}: **{$row->total}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function totalPurchaseValue(): array
    {
        $total = Asset::where('ownership_type', 'owned')->sum('purchase_price') ?? 0;
        $count = Asset::where('ownership_type', 'owned')->count();

        $jawaban = "💰 **Total Nilai Pembelian Aset**\n\n"
            . "• Total: **Rp " . number_format($total, 0, ',', '.') . "**\n"
            . "• Jumlah aset hak milik: {$count} unit";

        return [$jawaban, 'database'];
    }

    private function monthlyLeaseCost(): array
    {
        $total = AssetOwnership::where('ownership_type', 'leased')->sum('monthly_cost') ?? 0;
        $count = Asset::where('ownership_type', 'leased')->count();

        $jawaban = "💸 **Biaya Sewa Bulanan**\n\n"
            . "• Total: **Rp " . number_format($total, 0, ',', '.') . " / bulan**\n"
            . "• Jumlah aset sewa: {$count} unit";

        return [$jawaban, 'database'];
    }

    private function overdueLoans(): array
    {
        $loans = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        if ($loans->isEmpty()) {
            return ["✅ Tidak ada peminjaman terlambat. Semua aman!", 'database'];
        }

        $total = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->count();

        $lines = ["🔴 **{$total} Peminjaman Terlambat:**\n"];
        foreach ($loans as $loan) {
            $sn = $loan->asset?->serial_number ?? '-';
            $user = $loan->user?->name ?? '-';
            $days = $loan->due_date?->diffForHumans();
            $lines[] = "• {$sn} — {$user} ({$days})";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function totalLoans(): array
    {
        $active = AssetLoan::whereIn('status', ['borrowed', 'approved'])->count();
        $returned = AssetLoan::where('status', 'returned')->count();
        $overdue = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())->count();

        $jawaban = "🔄 **Statistik Peminjaman**\n\n"
            . "• Aktif: **{$active}**\n"
            . "• Terlambat: **{$overdue}**\n"
            . "• Sudah dikembalikan: **{$returned}**";

        return [$jawaban, 'database'];
    }

    private function topMaintenanced(): array
    {
        $data = Asset::withCount('maintenances')
            ->having('maintenances_count', '>', 0)
            ->orderByDesc('maintenances_count')
            ->limit(5)
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data perbaikan.", 'database'];
        }

        $lines = ["🔧 **Top 5 Aset Paling Sering Diperbaiki:**\n"];
        foreach ($data as $i => $a) {
            $lines[] = ($i + 1) . ". {$a->serial_number} ({$a->brand} {$a->model}) — **{$a->maintenances_count}x**";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function lowStockConsumables(): array
    {
        $items = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available')
            ->limit(10)
            ->get();

        if ($items->isEmpty()) {
            return ["✅ Semua stok konsumable aman.", 'database'];
        }

        $total = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')->count();
        $lines = ["📉 **{$total} Konsumable Stok Rendah:**\n"];
        foreach ($items as $c) {
            $lines[] = "• {$c->name}: **{$c->stock_available}/{$c->stock_minimum}** {$c->unit}";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function totalConsumables(): array
    {
        $total = Consumable::count();
        $lowStock = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->where('stock_available', '>', 0)->count();
        $outStock = Consumable::where('stock_available', 0)->count();

        $jawaban = "📦 **Statistik Konsumable**\n\n"
            . "• Total item: **{$total}**\n"
            . "• Stok rendah: **{$lowStock}**\n"
            . "• Stok habis: **{$outStock}**";

        return [$jawaban, 'database'];
    }

    private function totalUsers(): array
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();

        $jawaban = "👥 **Statistik User**\n\n"
            . "• Total: **{$total}**\n"
            . "• Aktif: **{$active}**";

        return [$jawaban, 'database'];
    }

    private function usersWithAssets(): array
    {
        $count = User::whereHas('currentAssets')->count();
        $total = User::count();

        $jawaban = "👤 **User yang Memegang Aset:** **{$count}** dari {$total} user.";

        return [$jawaban, 'database'];
    }
}
