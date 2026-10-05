<?php

namespace App\Services\Query;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\AssetRequest;
use App\Models\Chat;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\Department;
use App\Models\Knowledge;
use App\Models\Location;
use App\Models\PendingKnowledge;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class QueryMapping
{
    /**
     * Mapping: array of [pattern => callback]
     * Pattern dicek dengan preg_match
     * Callback return [answer, 'database'] atau null
     */
    public static function handlers(): array
    {
        return [
            // ═══════════════════════════════════════════════════
            // ASSET CONTROLLER
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?aset\b/i' => [self::class, 'totalAssets'],

            '/\bberapa\s+aset\s+(tersedia|ready|available)\b/i' => fn() => self::countAssetsByStatus('available'),
            '/\bberapa\s+aset\s+(rusak|maintenance|perbaikan)\b/i' => fn() => self::countAssetsByStatus('maintenance'),
            '/\bberapa\s+aset\s+(dipinjam|loaned)\b/i' => fn() => self::countAssetsByStatus('loaned'),
            '/\bberapa\s+aset\s+(hilang|lost)\b/i' => fn() => self::countAssetsByStatus('lost'),
            '/\bberapa\s+aset\s+(pensiun|retired)\b/i' => fn() => self::countAssetsByStatus('retired'),

            '/\bberapa\s+aset\s+(hak\s+milik|owned)\b/i' => fn() => self::countAssetsByOwnership('owned'),
            '/\bberapa\s+aset\s+(sewa|leased)\b/i' => fn() => self::countAssetsByOwnership('leased'),

            '/\bsummary\s+(status\s+)?aset\b/i' => [self::class, 'assetSummary'],
            '/\bstatus\s+aset\s+(apa\s+)?saja\b/i' => [self::class, 'assetSummary'],

            // ═══════════════════════════════════════════════════
            // ASSET CATEGORY
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(kategori|category)\b/i' => [self::class, 'countCategories'],
            '/\bdaftar\s+(kategori|category)\b/i' => [self::class, 'listCategories'],

            // ═══════════════════════════════════════════════════
            // ASSET TYPE / BRAND / MODEL
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(tipe|type|model|brand|merek)\b/i' => [self::class, 'countAssetTypes'],
            '/\bdaftar\s+(brand|merek)\b/i' => [self::class, 'listBrands'],
            '/\bdaftar\s+(model|tipe|type)\b/i' => [self::class, 'listModels'],

            // ═══════════════════════════════════════════════════
            // ASSET ASSIGNMENT
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(serah\s*terima|assignment)\s+(aktif|sedang)\b/i' => [self::class, 'activeAssignments'],
            '/\btop\s+user.*aset\b/i' => [self::class, 'topUsersWithAssets'],

            // ═══════════════════════════════════════════════════
            // ASSET LOAN
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(peminjaman|loan)\s+(aktif|sedang)\b/i' => [self::class, 'activeLoans'],
            '/\b(peminjaman|loan)\s+(terlambat|overdue|telat)\b/i' => [self::class, 'overdueLoans'],

            // ═══════════════════════════════════════════════════
            // ASSET MAINTENANCE
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+aset\s+(sedang\s+)?(diperbaiki|perbaikan|maintenance)\b/i' => [self::class, 'countMaintenance'],
            '/\btop\s+aset\s+(sering\s+)?rusak\b/i' => [self::class, 'topMaintenanced'],
            '/\bbiaya\s+(perbaikan|maintenance|servis)\b/i' => [self::class, 'totalMaintenanceCost'],

            // ═══════════════════════════════════════════════════
            // ASSET REQUEST
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(request|pengajuan)\s+(pending|menunggu)\b/i' => [self::class, 'countPendingRequests'],
            '/\bberapa\s+(request|pengajuan)\s+(approved|disetujui)\b/i' => [self::class, 'countApprovedRequests'],
            '/\bberapa\s+(request|pengajuan)\s+(rejected|ditolak)\b/i' => [self::class, 'countRejectedRequests'],

            // ═══════════════════════════════════════════════════
            // CONSUMABLE
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?konsumable\b/i' => [self::class, 'countConsumables'],
            '/\bkonsumable\s+(stok\s+)?(rendah|habis|low)\b/i' => [self::class, 'lowStockConsumables'],
            '/\bdaftar\s+konsumable\b/i' => [self::class, 'listConsumables'],
            '/\bsiapa\s+(saja\s+)?(yang\s+)?pakai\s+(mouse|keyboard|kabel|flashdisk)/i' => [self::class, 'consumableUsers'],

            // ═══════════════════════════════════════════════════
            // CONSUMABLE TRANSACTION
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+transaksi\s+konsumable\b/i' => [self::class, 'countConsumableTransactions'],
            '/\bbarang\s+masuk\s+bulan\s+ini\b/i' => [self::class, 'sumConsumableIn'],
            '/\bbarang\s+keluar\s+bulan\s+ini\b/i' => [self::class, 'sumConsumableOut'],

            // ═══════════════════════════════════════════════════
            // USER
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?(user|pegawai|karyawan)\b/i' => [self::class, 'countUsers'],
            '/\bberapa\s+(user|pegawai)\s+(aktif|active)\b/i' => [self::class, 'countActiveUsers'],
            '/\bdaftar\s+(user|pegawai)\s+(aktif|active)\b/i' => [self::class, 'listActiveUsers'],
            '/\buser\s+(yang\s+)?(belum\s+)?pegang\s+aset\b/i' => [self::class, 'usersWithAssets'],

            // ═══════════════════════════════════════════════════
            // DEPARTMENT
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(departemen|department|divisi)\b/i' => [self::class, 'countDepartments'],
            '/\bdaftar\s+(departemen|department|divisi)\b/i' => [self::class, 'listDepartments'],

            // ═══════════════════════════════════════════════════
            // LOCATION
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(lokasi|location|ruangan|ruang)\b/i' => [self::class, 'countLocations'],
            '/\bdaftar\s+(lokasi|location|ruangan|ruang)\b/i' => [self::class, 'listLocations'],

            // ═══════════════════════════════════════════════════
            // VENDOR
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(vendor|supplier|penyedia)\b/i' => [self::class, 'countVendors'],
            '/\bdaftar\s+(vendor|supplier)\b/i' => [self::class, 'listVendors'],
            '/\b(biaya|cost)\s+sewa\s+(bulanan|per\s+bulan)\b/i' => [self::class, 'monthlyLeaseCost'],
            '/\bkontrak\s+(hampir\s+)?(habis|berakhir|expired)\b/i' => [self::class, 'expiringContracts'],

            // ═══════════════════════════════════════════════════
            // KNOWLEDGE
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?knowledge\b/i' => [self::class, 'countKnowledge'],
            '/\bberapa\s+(pending\s+)?(ai|knowledge)\s+pending\b/i' => [self::class, 'countPendingKnowledge'],

            // ═══════════════════════════════════════════════════
            // CHAT
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?chat\b/i' => [self::class, 'countChats'],
            '/\bberapa\s+chat\s+hari\s+ini\b/i' => [self::class, 'countChatsToday'],
            '/\btop\s+pertanyaan\b/i' => [self::class, 'topQuestions'],

            // ═══════════════════════════════════════════════════
            // ACTIVITY LOG
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(log|activity|aktivitas)\b/i' => [self::class, 'countActivities'],
            '/\b(log|aktivitas)\s+hari\s+ini\b/i' => [self::class, 'activitiesToday'],
            '/\b(log|aktivitas)\s+kemarin\b/i' => [self::class, 'activitiesYesterday'],
            '/\blogin\s+(terakhir|terbaru)\b/i' => [self::class, 'recentLogins'],

            // ═══════════════════════════════════════════════════
            // APP
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(total\s+)?(app|aplikasi)\b/i' => [self::class, 'countApps'],
            '/\bberapa\s+(app|aplikasi)\s+aktif\b/i' => [self::class, 'countActiveApps'],

            // ═══════════════════════════════════════════════════
            // AGENT REGISTRY
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+(pending\s+)?agent\b/i' => [self::class, 'countPendingAgents'],
            '/\bberapa\s+(agent\s+)?token\s+aktif\b/i' => [self::class, 'countActiveTokens'],
            '/\bberapa\s+agent\s+online\b/i' => [self::class, 'countOnlineAgents'],

            // ═══════════════════════════════════════════════════
            // BACKUP
            // ═══════════════════════════════════════════════════

            '/\bberapa\s+backup\b/i' => [self::class, 'countBackups'],
        ];
    }

    // ═══════════════════════════════════════════════════════
    // HANDLERS
    // ═══════════════════════════════════════════════════════

    public static function totalAssets(): array
    {
        $total = Asset::count();
        $owned = Asset::where('ownership_type', 'owned')->count();
        $leased = Asset::where('ownership_type', 'leased')->count();
        return ["📊 Total Aset: **{$total}** unit\n• 🟢 Hak Milik: {$owned}\n• 🟠 Sewa: {$leased}", 'database'];
    }

    public static function countAssetsByStatus(string $status): array
    {
        $labels = [
            'available' => 'tersedia',
            'maintenance' => 'rusak/perbaikan',
            'loaned' => 'dipinjam',
            'lost' => 'hilang',
            'retired' => 'pensiun',
        ];
        $count = Asset::where('status', $status)->count();
        $label = $labels[$status] ?? $status;
        return ["Ada **{$count}** aset dengan status **{$label}**.", 'database'];
    }

    public static function countAssetsByOwnership(string $ownership): array
    {
        $label = $ownership === 'owned' ? 'hak milik' : 'sewa';
        $count = Asset::where('ownership_type', $ownership)->count();
        return ["Ada **{$count}** aset dengan status **{$label}**.", 'database'];
    }

    public static function assetSummary(): array
    {
        $statuses = ['available' => 'Tersedia', 'in_use' => 'Dipakai', 'loaned' => 'Dipinjam', 'maintenance' => 'Perbaikan', 'retired' => 'Pensiun', 'lost' => 'Hilang'];
        $out = "📊 **Summary Status Aset:**\n\n";
        $total = 0;
        foreach ($statuses as $key => $label) {
            $count = Asset::where('status', $key)->count();
            $out .= "• {$label}: {$count} unit\n";
            $total += $count;
        }
        $out .= "\n**Total: {$total} unit**";
        return [$out, 'database'];
    }

    public static function countCategories(): array
    {
        $total = AssetCategory::count();
        $active = AssetCategory::where('is_active', true)->count();
        return ["Ada **{$total}** kategori aset ({$active} aktif).", 'database'];
    }

    public static function listCategories(): array
    {
        $cats = AssetCategory::where('is_active', true)->orderBy('name')->limit(30)->pluck('name');
        if ($cats->isEmpty())
            return ["Belum ada kategori.", 'database'];
        return ["📁 **Daftar Kategori:**\n\n" . $cats->map(fn($n) => "• {$n}")->implode("\n"), 'database'];
    }

    public static function countAssetTypes(): array
    {
        $total = AssetType::count();
        return ["Ada **{$total}** brand & model terdaftar.", 'database'];
    }

    public static function listBrands(): array
    {
        $brands = Asset::select('brand')->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand');
        if ($brands->isEmpty())
            return ["Belum ada brand.", 'database'];
        return ["🏷️ **Daftar Brand:**\n\n" . $brands->map(fn($n) => "• {$n}")->implode("\n"), 'database'];
    }

    public static function listModels(): array
    {
        $models = Asset::select('model', 'brand')->whereNotNull('model')->distinct()->orderBy('brand')->limit(50)->get();
        if ($models->isEmpty())
            return ["Belum ada model.", 'database'];
        $out = "💻 **Daftar Model:**\n\n";
        foreach ($models as $m)
            $out .= "• {$m->brand} {$m->model}\n";
        return [$out, 'database'];
    }

    public static function activeAssignments(): array
    {
        $count = \App\Models\AssetAssignment::whereNull('returned_at')->count();
        return ["Ada **{$count}** serah terima aktif.", 'database'];
    }

    public static function topUsersWithAssets(): array
    {
        $users = User::withCount('currentAssets')->having('current_assets_count', '>', 0)->orderByDesc('current_assets_count')->limit(10)->get();
        if ($users->isEmpty())
            return ["Belum ada data.", 'database'];
        $out = "👥 **Top 10 User Pemegang Aset:**\n\n";
        foreach ($users as $i => $u)
            $out .= ($i + 1) . ". {$u->name} — {$u->current_assets_count} unit\n";
        return [$out, 'database'];
    }

    public static function activeLoans(): array
    {
        $count = \App\Models\AssetLoan::where('status', 'borrowed')->count();
        return ["Ada **{$count}** peminjaman aktif.", 'database'];
    }

    public static function overdueLoans(): array
    {
        $count = \App\Models\AssetLoan::where('status', 'borrowed')->whereDate('due_date', '<', today())->count();
        return ["Ada **{$count}** peminjaman terlambat.", 'database'];
    }

    public static function countMaintenance(): array
    {
        $count = \App\Models\AssetMaintenance::whereIn('status', ['open', 'in_progress'])->count();
        return ["Ada **{$count}** aset sedang dalam perbaikan.", 'database'];
    }

    public static function topMaintenanced(): array
    {
        $data = Asset::withCount('maintenances')->having('maintenances_count', '>', 0)->orderByDesc('maintenances_count')->limit(5)->get();
        if ($data->isEmpty())
            return ["Belum ada data perbaikan.", 'database'];
        $out = "🔧 **Top 5 Aset Paling Sering Diperbaiki:**\n\n";
        foreach ($data as $i => $a)
            $out .= ($i + 1) . ". {$a->serial_number} ({$a->brand} {$a->model}) — {$a->maintenances_count}x\n";
        return [$out, 'database'];
    }

    public static function totalMaintenanceCost(): array
    {
        $total = \App\Models\AssetMaintenance::sum('cost') ?? 0;
        return ["💰 Total biaya perbaikan: **Rp " . number_format($total, 0, ',', '.') . "**", 'database'];
    }

    public static function countPendingRequests(): array
    {
        $count = AssetRequest::where('status', 'pending')->count();
        return ["Ada **{$count}** pengajuan yang masih menunggu persetujuan.", 'database'];
    }

    public static function countApprovedRequests(): array
    {
        $count = AssetRequest::where('status', 'approved')->count();
        return ["Ada **{$count}** pengajuan yang sudah disetujui.", 'database'];
    }

    public static function countRejectedRequests(): array
    {
        $count = AssetRequest::where('status', 'rejected')->count();
        return ["Ada **{$count}** pengajuan yang ditolak.", 'database'];
    }

    public static function countConsumables(): array
    {
        $total = Consumable::count();
        return ["Ada **{$total}** item konsumable terdaftar.", 'database'];
    }

    public static function lowStockConsumables(): array
    {
        $items = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')->orderBy('stock_available')->limit(10)->get();
        if ($items->isEmpty())
            return ["Semua stok konsumable aman. ✅", 'database'];
        $total = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')->count();
        $out = "📉 **{$total} Konsumable Stok Rendah:**\n\n";
        foreach ($items as $c)
            $out .= "• {$c->name}: {$c->stock_available}/{$c->stock_minimum} {$c->unit}\n";
        return [$out, 'database'];
    }

    public static function listConsumables(): array
    {
        $items = Consumable::orderBy('name')->limit(30)->get();
        if ($items->isEmpty())
            return ["Belum ada konsumable.", 'database'];
        $out = "📦 **Daftar Konsumable:**\n\n";
        foreach ($items as $c)
            $out .= "• {$c->name} — Stok: {$c->stock_available} {$c->unit}\n";
        return [$out, 'database'];
    }

    public static function consumableUsers(): array
    {
        $out = "👥 **Pemakai Konsumable (berdasarkan transaksi):**\n\n";
        $transactions = ConsumableTransaction::with(['consumable', 'user'])->where('type', 'out')->orderByDesc('transaction_date')->limit(20)->get();
        if ($transactions->isEmpty())
            return ["Belum ada transaksi konsumable.", 'database'];
        foreach ($transactions as $t)
            $out .= "• {$t->user?->name} — {$t->consumable?->name} ({$t->quantity} {$t->consumable?->unit})\n";
        return [$out, 'database'];
    }

    public static function countConsumableTransactions(): array
    {
        $count = ConsumableTransaction::whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->count();
        return ["Ada **{$count}** transaksi konsumable bulan ini.", 'database'];
    }

    public static function sumConsumableIn(): array
    {
        $total = ConsumableTransaction::where('type', 'in')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('quantity');
        return ["Total barang masuk bulan ini: **{$total} unit**.", 'database'];
    }

    public static function sumConsumableOut(): array
    {
        $total = ConsumableTransaction::where('type', 'out')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('quantity');
        return ["Total barang keluar bulan ini: **{$total} unit**.", 'database'];
    }

    public static function countUsers(): array
    {
        $total = User::count();
        return ["Ada **{$total}** user terdaftar.", 'database'];
    }

    public static function countActiveUsers(): array
    {
        $count = User::where('is_active', true)->count();
        return ["Ada **{$count}** user aktif.", 'database'];
    }

    public static function listActiveUsers(): array
    {
        $users = User::where('is_active', true)->orderBy('name')->limit(30)->get();
        if ($users->isEmpty())
            return ["Tidak ada user aktif.", 'database'];
        $out = "👥 **Daftar User Aktif:**\n\n";
        foreach ($users as $u)
            $out .= "• {$u->name}" . ($u->position ? " ({$u->position})" : "") . "\n";
        return [$out, 'database'];
    }

    public static function usersWithAssets(): array
    {
        $with = User::whereHas('currentAssets')->count();
        $without = User::doesntHave('currentAssets')->count();
        return ["👤 **Statistik User & Aset:**\n• User yang pegang aset: **{$with}**\n• User yang belum pegang: **{$without}**", 'database'];
    }

    public static function countDepartments(): array
    {
        $total = Department::count();
        return ["Ada **{$total}** departemen terdaftar.", 'database'];
    }

    public static function listDepartments(): array
    {
        $items = Department::where('is_active', true)->orderBy('name')->limit(30)->get();
        if ($items->isEmpty())
            return ["Belum ada departemen.", 'database'];
        $out = "🏢 **Daftar Departemen:**\n\n";
        foreach ($items as $d)
            $out .= "• {$d->name}" . ($d->code ? " ({$d->code})" : "") . "\n";
        return [$out, 'database'];
    }

    public static function countLocations(): array
    {
        $total = Location::count();
        return ["Ada **{$total}** lokasi terdaftar.", 'database'];
    }

    public static function listLocations(): array
    {
        $items = Location::where('is_active', true)->orderBy('building')->limit(30)->get();
        if ($items->isEmpty())
            return ["Belum ada lokasi.", 'database'];
        $out = "📍 **Daftar Lokasi:**\n\n";
        foreach ($items as $l)
            $out .= "• {$l->building}" . ($l->floor ? " Lt.{$l->floor}" : "") . ($l->room ? " - {$l->room}" : "") . "\n";
        return [$out, 'database'];
    }

    public static function countVendors(): array
    {
        $total = Vendor::count();
        return ["Ada **{$total}** vendor terdaftar.", 'database'];
    }

    public static function listVendors(): array
    {
        $items = Vendor::where('is_active', true)->orderBy('name')->limit(30)->get();
        if ($items->isEmpty())
            return ["Belum ada vendor.", 'database'];
        $out = "🏭 **Daftar Vendor:**\n\n";
        foreach ($items as $v)
            $out .= "• {$v->name}" . ($v->type ? " ({$v->type})" : "") . "\n";
        return [$out, 'database'];
    }

    public static function monthlyLeaseCost(): array
    {
        $total = \App\Models\AssetOwnership::where('ownership_type', 'leased')->sum('monthly_cost') ?? 0;
        return ["💰 Biaya sewa bulanan: **Rp " . number_format($total, 0, ',', '.') . "**", 'database'];
    }

    public static function expiringContracts(): array
    {
        $items = \App\Models\AssetOwnership::whereNotNull('contract_end')->whereDate('contract_end', '>=', today())->whereDate('contract_end', '<=', now()->addDays(30))->with(['asset', 'vendor'])->orderBy('contract_end')->limit(10)->get();
        if ($items->isEmpty())
            return ["Tidak ada kontrak yang berakhir dalam 30 hari.", 'database'];
        $total = \App\Models\AssetOwnership::whereNotNull('contract_end')->whereDate('contract_end', '>=', today())->whereDate('contract_end', '<=', now()->addDays(30))->count();
        $out = "⚠️ **{$total} Kontrak Sewa Berakhir < 30 Hari:**\n\n";
        foreach ($items as $o)
            $out .= "• {$o->asset?->serial_number} — {$o->vendor?->name} ({$o->contract_end->diffForHumans()})\n";
        return [$out, 'database'];
    }

    public static function countKnowledge(): array
    {
        $total = Knowledge::count();
        return ["Ada **{$total}** entri knowledge.", 'database'];
    }

    public static function countPendingKnowledge(): array
    {
        $pending = PendingKnowledge::where('status', 'pending')->count();
        return ["Ada **{$pending}** pending knowledge menunggu review.", 'database'];
    }

    public static function countChats(): array
    {
        $total = Chat::count();
        return ["Total **{$total}** chat tercatat.", 'database'];
    }

    public static function countChatsToday(): array
    {
        $count = Chat::whereDate('waktu', today())->count();
        return ["Ada **{$count}** chat hari ini.", 'database'];
    }

    public static function topQuestions(): array
    {
        $items = Chat::select('pesan', DB::raw('COUNT(*) as total'))->groupBy('pesan')->orderByDesc('total')->limit(10)->get();
        if ($items->isEmpty())
            return ["Belum ada data.", 'database'];
        $out = "🔥 **Top 10 Pertanyaan:**\n\n";
        foreach ($items as $i => $q)
            $out .= ($i + 1) . ". {$q->pesan} ({$q->total}x)\n";
        return [$out, 'database'];
    }

    public static function countActivities(): array
    {
        $total = Activity::count();
        return ["Ada **{$total}** log aktivitas.", 'database'];
    }

    public static function activitiesToday(): array
    {
        $count = Activity::whereDate('created_at', today())->count();
        return ["Ada **{$count}** log aktivitas hari ini.", 'database'];
    }

    public static function activitiesYesterday(): array
    {
        $count = Activity::whereDate('created_at', now()->subDay())->count();
        return ["Ada **{$count}** log aktivitas kemarin.", 'database'];
    }

    public static function recentLogins(): array
    {
        $logs = Activity::where('log_name', 'auth')->where('description', 'like', '%Login berhasil%')->with('causer')->latest()->limit(10)->get();
        if ($logs->isEmpty())
            return ["Belum ada data login.", 'database'];
        $out = "🔐 **Login Terakhir:**\n\n";
        foreach ($logs as $a)
            $out .= "• {$a->causer?->name} — {$a->created_at->diffForHumans()}\n";
        return [$out, 'database'];
    }

    public static function countApps(): array
    {
        $total = \App\Models\App::count();
        return ["Ada **{$total}** aplikasi di portal.", 'database'];
    }

    public static function countActiveApps(): array
    {
        $count = \App\Models\App::where('is_active', true)->count();
        return ["Ada **{$count}** aplikasi aktif.", 'database'];
    }

    public static function countPendingAgents(): array
    {
        $count = \App\Models\PendingAgent::whereNull('approved_at')->whereNull('rejected_at')->count();
        return ["Ada **{$count}** pending agent menunggu approval.", 'database'];
    }

    public static function countActiveTokens(): array
    {
        $count = \App\Models\AgentToken::where('is_active', true)->count();
        return ["Ada **{$count}** agent token aktif.", 'database'];
    }

    public static function countOnlineAgents(): array
    {
        $count = Asset::where('last_seen_at', '>=', now()->subMinutes(10))->count();
        return ["Ada **{$count}** agent online (5 menit terakhir).", 'database'];
    }

    public static function countBackups(): array
    {
        $dir = storage_path('app/backups');
        $count = file_exists($dir) ? count(glob("{$dir}/*")) : 0;
        return ["Ada **{$count}** file backup tersimpan.", 'database'];
    }
}
