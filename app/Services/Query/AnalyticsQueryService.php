<?php

namespace App\Services\Query;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AssetOwnership;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower(trim($pesan));

        if (strlen($lower) < 5) {
            return null;
        }

        // ============================================================
        // 1. TOP USER PEGANG ASET
        // ============================================================
        if (
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'orang']) &&
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'paling', 'mayoritas', 'paling sering']) &&
            $this->matchAny($lower, ['aset', 'asset', 'pegang', 'memegang', 'punya'])
        ) {
            return $this->topUsersWithAssets();
        }

        // ============================================================
        // 2. TOP LOKASI DENGAN ASET TERBANYAK
        // ============================================================
        if (
            $this->matchAny($lower, ['lokasi', 'ruangan', 'ruang', 'gedung']) &&
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'paling'])
        ) {
            return $this->topLocations();
        }

        // ============================================================
        // 3. TOP VENDOR DENGAN ASET TERBANYAK
        // ============================================================
        if (
            $this->matchAny($lower, ['vendor', 'supplier', 'penyedia']) &&
            $this->matchAny($lower, ['top', 'terbanyak', 'paling banyak', 'paling'])
        ) {
            return $this->topVendors();
        }

        // ============================================================
        // 4. ASET PER TAHUN PEMBELIAN
        // ============================================================
        if (
            $this->matchAny($lower, ['tahun', 'year', 'per tahun']) &&
            $this->matchAny($lower, ['aset', 'asset', 'pembelian', 'pengadaan'])
        ) {
            return $this->assetsByYear();
        }

        // ============================================================
        // 5. BANDINGKAN OWNED vs LEASED PER KATEGORI
        // ============================================================
        if (
            $this->matchAny($lower, ['bandingkan', 'perbandingan', 'compare', 'vs']) &&
            $this->matchAny($lower, ['kategori', 'category'])
        ) {
            return $this->compareOwnershipByCategory();
        }

        // ============================================================
        // 6. ASET YANG HARUS PENSIUN (prediksi)
        // ============================================================
        if (
            $this->matchAny($lower, ['pensiun', 'retire', 'tua', 'lama']) &&
            $this->matchAny($lower, ['harus', 'sebaiknya', 'perlu', 'prediksi', 'rekomendasi'])
        ) {
            return $this->assetsToRetire();
        }

        // ============================================================
        // 7. ASET YANG SERING RUSAK TAHUN INI
        // ============================================================
        if (
            $this->matchAny($lower, ['sering', 'paling', 'top']) &&
            $this->matchAny($lower, ['rusak', 'perbaikan', 'maintenance']) &&
            $this->matchAny($lower, ['tahun ini', 'tahun', 'year', 'bulan ini'])
        ) {
            return $this->topMaintenancedThisYear();
        }

        // ============================================================
        // 8. GARANSI HAMPIR HABIS
        // ============================================================
        if (
            $this->matchAny($lower, ['garansi', 'warranty']) &&
            $this->matchAny($lower, ['hampir', 'segera', 'berakhir', 'habis', 'expired'])
        ) {
            return $this->expiringWarranties();
        }

        return null;
    }

    // ============================================================
    // HANDLERS
    // ============================================================

    private function topUsersWithAssets(): array
    {
        $users = User::withCount('currentAssets')
            ->having('current_assets_count', '>', 0)
            ->orderByDesc('current_assets_count')
            ->limit(10)
            ->get();

        if ($users->isEmpty()) {
            return ["Belum ada user yang memegang aset.", 'database'];
        }

        $lines = ["👥 **Top 10 User Pemegang Aset:**\n"];
        foreach ($users as $i => $u) {
            $lines[] = ($i + 1) . ". **{$u->name}**" . ($u->position ? " ({$u->position})" : "")
                . " — **{$u->current_assets_count}** unit";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function topLocations(): array
    {
        $locations = Location::withCount('currentAssets')
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

    private function topVendors(): array
    {
        $vendors = Vendor::withCount('assetOwnerships')
            ->having('asset_ownerships_count', '>', 0)
            ->orderByDesc('asset_ownerships_count')
            ->limit(10)
            ->get();

        if ($vendors->isEmpty()) {
            return ["Belum ada data vendor dengan aset.", 'database'];
        }

        $lines = ["🏢 **Top 10 Vendor dengan Aset Terbanyak:**\n"];
        foreach ($vendors as $i => $v) {
            $lines[] = ($i + 1) . ". **{$v->name}**" . ($v->type ? " ({$v->type})" : "")
                . " — **{$v->asset_ownerships_count}** aset";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function assetsByYear(): array
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
        $total = 0;
        foreach ($data as $row) {
            $lines[] = "• **{$row->year}**: {$row->total} unit";
            $total += $row->total;
        }
        $lines[] = "\nTotal: **{$total}** unit";

        return [implode("\n", $lines), 'database'];
    }

    private function compareOwnershipByCategory(): array
    {
        $data = Asset::selectRaw('category_id, ownership_type, COUNT(*) as total')
            ->with('category:id,name')
            ->groupBy('category_id', 'ownership_type')
            ->get()
            ->groupBy('category_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'category' => $first->category?->name ?? 'Tanpa Kategori',
                    'owned' => $rows->where('ownership_type', 'owned')->sum('total'),
                    'leased' => $rows->where('ownership_type', 'leased')->sum('total'),
                ];
            })
            ->sortByDesc(fn($x) => $x['owned'] + $x['leased'])
            ->values();

        if ($data->isEmpty()) {
            return ["Belum ada data.", 'database'];
        }

        $lines = ["📊 **Perbandingan Hak Milik vs Sewa per Kategori:**\n"];
        foreach ($data as $row) {
            $total = $row['owned'] + $row['leased'];
            $ownedPct = $total > 0 ? round(($row['owned'] / $total) * 100) : 0;
            $leasedPct = 100 - $ownedPct;
            $lines[] = "• **{$row['category']}** — Total: {$total}\n"
                . "  🟢 Owned: {$row['owned']} ({$ownedPct}%) | 🟠 Sewa: {$row['leased']} ({$leasedPct}%)";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function assetsToRetire(): array
    {
        // Aset tua (>5 tahun) yang masih aktif
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
            $lines[] = "\nMenampilkan 15 pertama dari {$total} aset.";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function topMaintenancedThisYear(): array
    {
        $year = now()->year;

        $data = Asset::withCount([
            'maintenances' => function ($q) use ($year) {
                $q->whereYear('start_date', $year);
            }
        ])
            ->having('maintenances_count', '>', 0)
            ->orderByDesc('maintenances_count')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return ["Belum ada data perbaikan tahun ini.", 'database'];
        }

        $lines = ["🔧 **Top 10 Aset Paling Sering Diperbaiki ({$year}):**\n"];
        foreach ($data as $i => $a) {
            $lines[] = ($i + 1) . ". {$a->serial_number} ({$a->brand} {$a->model}) — **{$a->maintenances_count}x**";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function expiringWarranties(): array
    {
        $assets = Asset::whereNotNull('warranty_expire')
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->orderBy('warranty_expire')
            ->limit(15)
            ->get();

        if ($assets->isEmpty()) {
            return ["✅ Tidak ada garansi yang berakhir dalam 60 hari.", 'database'];
        }

        $total = Asset::whereNotNull('warranty_expire')
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->count();

        $lines = ["⚠️ **{$total} Aset Garansi Berakhir < 60 Hari:**\n"];
        foreach ($assets as $a) {
            $end = $a->warranty_expire->format('d M Y');
            $days = $a->warranty_expire->diffInDays(now());
            $lines[] = "• {$a->serial_number} ({$a->brand} {$a->model}) — berakhir **{$end}** ({$days} hari lagi)";
        }

        if ($total > 15) {
            $lines[] = "\nMenampilkan 15 pertama dari {$total} aset.";
        }

        return [implode("\n", $lines), 'database'];
    }

    private function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }
}
