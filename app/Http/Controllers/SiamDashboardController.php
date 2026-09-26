<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetOwnership;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class SiamDashboardController extends Controller
{
    public function index(Request $request)
    {
        // ============================================================
        // 0. FILTER DASHBOARD
        // ============================================================
        $filters = [
            'category_id' => $request->get('category_id'),
            'brand' => $request->get('brand'),
            'model' => $request->get('model'),
            'year' => $request->get('year'),
            'ownership_type' => $request->get('ownership_type'),
        ];

        $hasFilter = collect($filters)->filter()->isNotEmpty();

        // ============================================================
        // BASE QUERY — dipakai untuk semua summary & chart
        // ============================================================
        $assetBaseQuery = Asset::query();

        if (!empty($filters['category_id'])) {
            $assetBaseQuery->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['brand'])) {
            $assetBaseQuery->where('brand', $filters['brand']);
        }
        if (!empty($filters['model'])) {
            $assetBaseQuery->where('model', $filters['model']);
        }
        if (!empty($filters['year'])) {
            $assetBaseQuery->whereYear('purchase_date', $filters['year']);
        }
        if (!empty($filters['ownership_type'])) {
            $assetBaseQuery->where('ownership_type', $filters['ownership_type']);
        }

        // ============================================================
        // 1. RINGKASAN ASET
        // ============================================================
        $assetStats = [
            'total' => (clone $assetBaseQuery)->count(),
            'available' => (clone $assetBaseQuery)->where('status', 'available')->count(),
            'in_use' => (clone $assetBaseQuery)->where('status', 'in_use')->count(),
            'loaned' => (clone $assetBaseQuery)->where('status', 'loaned')->count(),
            'maintenance' => (clone $assetBaseQuery)->where('status', 'maintenance')->count(),
            'retired' => (clone $assetBaseQuery)->where('status', 'retired')->count(),
            'lost' => (clone $assetBaseQuery)->where('status', 'lost')->count(),
            'owned' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->count(),
            'leased' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->count(),
        ];

        // ============================================================
        // 2. NILAI ASET (dibatasi filter yg relevan)
        // ============================================================
        // Catatan: kalau filter ownership_type = 'leased', maka total_purchase = 0
        //           kalau filter ownership_type = 'owned', maka monthly_lease = 0
        $skipPurchase = ($filters['ownership_type'] === 'leased');
        $skipMonthly = ($filters['ownership_type'] === 'owned');

        $assetValues = [
            'total_purchase' => $skipPurchase
                ? 0
                : (clone $assetBaseQuery)
                    ->where('ownership_type', 'owned')
                    ->sum('purchase_price') ?? 0,

            'monthly_lease' => $skipMonthly
                ? 0
                : AssetOwnership::where('ownership_type', 'leased')
                    ->when($hasFilter, function ($q) use ($filters) {
                        $q->whereHas('asset', function ($qa) use ($filters) {
                            if (!empty($filters['category_id']))
                                $qa->where('category_id', $filters['category_id']);
                            if (!empty($filters['brand']))
                                $qa->where('brand', $filters['brand']);
                            if (!empty($filters['model']))
                                $qa->where('model', $filters['model']);
                            if (!empty($filters['year']))
                                $qa->whereYear('purchase_date', $filters['year']);
                        });
                    })
                    ->sum('monthly_cost') ?? 0,
        ];

        // ============================================================
        // 3. BREAKDOWN PER HAK KEPEMILIKAN
        // ============================================================
        $ownershipBreakdown = [
            'owned' => [
                'total' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->count(),
                'available' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'available')->count(),
                'in_use' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'in_use')->count(),
                'loaned' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'loaned')->count(),
                'maintenance' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'maintenance')->count(),
                'retired' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'retired')->count(),
                'lost' => (clone $assetBaseQuery)->where('ownership_type', 'owned')->where('status', 'lost')->count(),
                'value' => $skipPurchase
                    ? 0
                    : (clone $assetBaseQuery)->where('ownership_type', 'owned')->sum('purchase_price') ?? 0,
            ],
            'leased' => [
                'total' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->count(),
                'available' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'available')->count(),
                'in_use' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'in_use')->count(),
                'loaned' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'loaned')->count(),
                'maintenance' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'maintenance')->count(),
                'retired' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'retired')->count(),
                'lost' => (clone $assetBaseQuery)->where('ownership_type', 'leased')->where('status', 'lost')->count(),
                'monthly_cost' => $skipMonthly
                    ? 0
                    : AssetOwnership::where('ownership_type', 'leased')
                        ->when($hasFilter, function ($q) use ($filters) {
                            $q->whereHas('asset', function ($qa) use ($filters) {
                                if (!empty($filters['category_id']))
                                    $qa->where('category_id', $filters['category_id']);
                                if (!empty($filters['brand']))
                                    $qa->where('brand', $filters['brand']);
                                if (!empty($filters['model']))
                                    $qa->where('model', $filters['model']);
                                if (!empty($filters['year']))
                                    $qa->whereYear('purchase_date', $filters['year']);
                            });
                        })
                        ->sum('monthly_cost') ?? 0,
            ],
        ];

        // ============================================================
        // 4. BREAKDOWN PER KATEGORI
        // ============================================================
        $categoryBreakdown = (clone $assetBaseQuery)
            ->select('category_id', 'ownership_type', 'status', DB::raw('COUNT(*) as total'))
            ->with('category:id,name')
            ->groupBy('category_id', 'ownership_type', 'status')
            ->get()
            ->groupBy('category_id')
            ->map(function ($rows) {
                $first = $rows->first();
                $ownedRows = $rows->where('ownership_type', 'owned');
                $leasedRows = $rows->where('ownership_type', 'leased');

                return [
                    'category' => $first->category?->name ?? 'Tanpa Kategori',
                    'total' => $rows->sum('total'),
                    'owned' => [
                        'total' => $ownedRows->sum('total'),
                        'available' => $ownedRows->where('status', 'available')->sum('total'),
                        'in_use' => $ownedRows->where('status', 'in_use')->sum('total'),
                        'maintenance' => $ownedRows->where('status', 'maintenance')->sum('total'),
                        'loaned' => $ownedRows->where('status', 'loaned')->sum('total'),
                    ],
                    'leased' => [
                        'total' => $leasedRows->sum('total'),
                        'available' => $leasedRows->where('status', 'available')->sum('total'),
                        'in_use' => $leasedRows->where('status', 'in_use')->sum('total'),
                        'maintenance' => $leasedRows->where('status', 'maintenance')->sum('total'),
                        'loaned' => $leasedRows->where('status', 'loaned')->sum('total'),
                    ],
                ];
            })
            ->sortByDesc('total')
            ->values();

        $catBreakdownLabels = $categoryBreakdown->pluck('category')->toArray();
        $catBreakdownOwned = $categoryBreakdown->pluck('owned.total')->toArray();
        $catBreakdownLeased = $categoryBreakdown->pluck('leased.total')->toArray();

        // ============================================================
        // 5. BREAKDOWN PER MODEL / TYPE
        // ============================================================
        $modelBreakdown = (clone $assetBaseQuery)
            ->select('model', 'brand', 'ownership_type', 'status', DB::raw('COUNT(*) as total'))
            ->whereNotNull('model')
            ->groupBy('model', 'brand', 'ownership_type', 'status')
            ->get()
            ->groupBy('model')
            ->map(function ($rows) {
                $first = $rows->first();
                $ownedRows = $rows->where('ownership_type', 'owned');
                $leasedRows = $rows->where('ownership_type', 'leased');

                return [
                    'model' => $first->model,
                    'brand' => $first->brand,
                    'total' => $rows->sum('total'),
                    'owned' => [
                        'total' => $ownedRows->sum('total'),
                        'available' => $ownedRows->where('status', 'available')->sum('total'),
                        'in_use' => $ownedRows->where('status', 'in_use')->sum('total'),
                        'maintenance' => $ownedRows->where('status', 'maintenance')->sum('total'),
                        'loaned' => $ownedRows->where('status', 'loaned')->sum('total'),
                    ],
                    'leased' => [
                        'total' => $leasedRows->sum('total'),
                        'available' => $leasedRows->where('status', 'available')->sum('total'),
                        'in_use' => $leasedRows->where('status', 'in_use')->sum('total'),
                        'maintenance' => $leasedRows->where('status', 'maintenance')->sum('total'),
                        'loaned' => $leasedRows->where('status', 'loaned')->sum('total'),
                    ],
                ];
            })
            ->sortByDesc('total')
            ->take(15)   // 🆕 batasi biar chart tidak terlalu padat
            ->values();

        $modelBreakdownLabels = $modelBreakdown->pluck('model')->toArray();
        $modelBreakdownOwned = $modelBreakdown->pluck('owned.total')->toArray();
        $modelBreakdownLeased = $modelBreakdown->pluck('leased.total')->toArray();

        // ============================================================
        // 6. CHART: KOMPOSISI KEPEMILIKAN
        // ============================================================
        $ownershipLabels = ['Hak Milik', 'Sewa'];
        $ownershipData = [
            $ownershipBreakdown['owned']['total'],
            $ownershipBreakdown['leased']['total'],
        ];

        // ============================================================
        // 7. CHART: ASET PER KATEGORI
        // ============================================================
        $assetsByCategory = (clone $assetBaseQuery)
            ->select('category_id', DB::raw('COUNT(*) as total'))
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $categoryLabels = $assetsByCategory->pluck('category.name')->toArray();
        $categoryData = $assetsByCategory->pluck('total')->toArray();

        // ============================================================
        // 8. CHART: ASET PER STATUS
        // ============================================================
        $statusLabels = ['Tersedia', 'Dipakai', 'Dipinjam', 'Perbaikan', 'Pensiun', 'Hilang'];
        $statusKeys = ['available', 'in_use', 'loaned', 'maintenance', 'retired', 'lost'];
        $statusData = array_map(fn($k) => $assetStats[$k] ?? 0, $statusKeys);

        // ============================================================
        // 9. CHART: ASET PER TAHUN
        // ============================================================
        $assetsByYear = (clone $assetBaseQuery)
            ->selectRaw('YEAR(purchase_date) as year, COUNT(*) as total')
            ->whereNotNull('purchase_date')
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('total', 'year');

        $yearLabels = $assetsByYear->keys()->toArray();
        $yearData = $assetsByYear->values()->toArray();

        // ============================================================
        // 10. ALERT / WARNING
        // ============================================================
        $assetIds = (clone $assetBaseQuery)->pluck('id');

        $overdueLoans = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->with(['asset', 'user'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $expiringContracts = Asset::where('ownership_type', 'leased')
            ->when($hasFilter, fn($q) => $q->whereIn('id', $assetIds))
            ->whereHas('ownership', function ($q) {
                $q->whereNotNull('contract_end')
                    ->whereDate('contract_end', '>=', today())
                    ->whereDate('contract_end', '<=', now()->addDays(30));
            })
            ->with('ownership')
            ->limit(10)
            ->get();

        $expiringWarranties = Asset::whereNotNull('warranty_expire')
            ->when($hasFilter, fn($q) => $q->whereIn('id', $assetIds))
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->limit(10)
            ->get();

        // Konsumable tetap global
        $lowStockConsumables = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available')
            ->limit(10)
            ->get();

        // ============================================================
        // 11. TREN PERBAIKAN 6 BULAN
        // ============================================================
        $maintenanceTrend = AssetMaintenance::select(
            DB::raw("DATE_FORMAT(start_date, '%Y-%m') as bulan"),
            DB::raw('COUNT(*) as total')
        )
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->where('start_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $maintenanceLabels = [];
        $maintenanceData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i)->format('Y-m');
            $maintenanceLabels[] = now()->subMonths($i)->format('M Y');
            $maintenanceData[] = $maintenanceTrend[$bulan] ?? 0;
        }

        // ============================================================
        // 12. AKTIVITAS TERBARU
        // ============================================================
        $recentMaintenances = AssetMaintenance::with(['asset', 'vendor'])
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentLoans = AssetLoan::with(['asset', 'user'])
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentSerah = AssetAssignment::with(['asset', 'user'])
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->orderByDesc('assigned_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => ['type' => 'serah', 'model' => $a, 'at' => $a->assigned_at]);

        $recentKembali = AssetAssignment::with(['asset', 'user'])
            ->when($hasFilter, fn($q) => $q->whereIn('asset_id', $assetIds))
            ->whereNotNull('returned_at')
            ->orderByDesc('returned_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => ['type' => 'kembali', 'model' => $a, 'at' => $a->returned_at]);

        $recentAssignments = collect($recentSerah)
            ->merge($recentKembali)
            ->sortByDesc('at')
            ->take(5)
            ->values();

        $recentActivities = Activity::with('causer')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // ============================================================
        // 13. KONSUMABLE STATS (global)
        // ============================================================
        $consumableStats = [
            'total' => Consumable::count(),
            'low_stock' => Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
                ->where('stock_available', '>', 0)->count(),
            'out_stock' => Consumable::where('stock_available', 0)->count(),
            'transactions_this_month' => ConsumableTransaction::whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->count(),
        ];

        // ============================================================
        // 14. TOP STATISTIK
        // ============================================================
        $topUsers = User::withCount([
            'currentAssets' => function ($q) use ($hasFilter, $assetIds) {
                if ($hasFilter)
                    $q->whereIn('assets.id', $assetIds);
            }
        ])
            ->having('current_assets_count', '>', 0)
            ->orderByDesc('current_assets_count')
            ->limit(5)
            ->get();

        $topMaintenancedAssets = Asset::withCount('maintenances')
            ->when($hasFilter, fn($q) => $q->whereIn('id', $assetIds))
            ->having('maintenances_count', '>', 0)
            ->orderByDesc('maintenances_count')
            ->limit(5)
            ->get();

        $topBrands = (clone $assetBaseQuery)
            ->select('brand', DB::raw('COUNT(*) as total'))
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ============================================================
        // 15. USER & VENDOR STATS
        // ============================================================
        $userStats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'with_assets' => User::whereHas('currentAssets')->count(),
        ];

        $vendorStats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('is_active', true)->count(),
        ];

        // ============================================================
        // 16. DROPDOWN FILTER (dependent: category → brand → model)
        // ============================================================
        $filterCategories = AssetCategory::where('is_consumable', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Brands: kalau category dipilih → filter by category
        $filterBrands = Asset::select('brand')
            ->when(!empty($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        // Models: kalau category & brand dipilih → filter by keduanya
        $filterModels = Asset::select('model', 'brand')
            ->whereNotNull('model')
            ->when(!empty($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->when(!empty($filters['brand']), fn($q) => $q->where('brand', $filters['brand']))
            ->distinct()
            ->orderBy('model')
            ->get();

        // Years: filter by category/brand/model kalau ada
        $filterYears = Asset::selectRaw('YEAR(purchase_date) as year')
            ->whereNotNull('purchase_date')
            ->when(!empty($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->when(!empty($filters['brand']), fn($q) => $q->where('brand', $filters['brand']))
            ->when(!empty($filters['model']), fn($q) => $q->where('model', $filters['model']))
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('siam.dashboard', compact(
            'assetStats',
            'assetValues',
            'ownershipBreakdown',
            'categoryBreakdown',
            'modelBreakdown',
            'modelBreakdownLabels',
            'modelBreakdownOwned',
            'modelBreakdownLeased',
            'filterCategories',
            'catBreakdownLabels',
            'catBreakdownOwned',
            'catBreakdownLeased',
            'ownershipLabels',
            'ownershipData',
            'overdueLoans',
            'lowStockConsumables',
            'expiringContracts',
            'expiringWarranties',
            'categoryLabels',
            'categoryData',
            'statusLabels',
            'statusData',
            'yearLabels',
            'yearData',
            'maintenanceLabels',
            'maintenanceData',
            'recentMaintenances',
            'recentLoans',
            'recentAssignments',
            'recentActivities',
            'consumableStats',
            'topUsers',
            'topMaintenancedAssets',
            'topBrands',
            'userStats',
            'vendorStats',
            'filters',
            'hasFilter',
            'filterBrands',
            'filterModels',
            'filterYears',
        ));
    }
}
