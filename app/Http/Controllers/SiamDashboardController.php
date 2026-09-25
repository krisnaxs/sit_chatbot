<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class SiamDashboardController extends Controller
{
    public function index()
    {
        // ============================================================
        // 1. RINGKASAN ASET
        // ============================================================
        $assetStats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'available')->count(),
            'in_use' => Asset::where('status', 'in_use')->count(),
            'loaned' => Asset::where('status', 'loaned')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'retired' => Asset::where('status', 'retired')->count(),
            'lost' => Asset::where('status', 'lost')->count(),
            'owned' => Asset::where('ownership_type', 'owned')->count(),
            'leased' => Asset::where('ownership_type', 'leased')->count(),
        ];

        // ============================================================
        // 2. ALERT / WARNING
        // ============================================================

        // Peminjaman terlambat
        $overdueLoans = AssetLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->with(['asset', 'user'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Konsumable stok rendah
        $lowStockConsumables = Consumable::whereColumn('stock_available', '<=', 'stock_minimum')
            ->orderBy('stock_available')
            ->limit(10)
            ->get();

        // Kontrak sewa hampir berakhir (< 30 hari)
        $expiringContracts = Asset::where('ownership_type', 'leased')
            ->whereHas('ownership', function ($q) {
                $q->whereNotNull('contract_end')
                    ->whereDate('contract_end', '>=', today())
                    ->whereDate('contract_end', '<=', now()->addDays(30));
            })
            ->with('ownership')
            ->limit(10)
            ->get();

        // Garansi hampir habis (< 60 hari)
        $expiringWarranties = Asset::whereNotNull('warranty_expire')
            ->whereDate('warranty_expire', '>=', today())
            ->whereDate('warranty_expire', '<=', now()->addDays(60))
            ->limit(10)
            ->get();

        // ============================================================
        // 3. GRAFIK ASET PER KATEGORI
        // ============================================================
        $assetsByCategory = Asset::select('category_id', DB::raw('COUNT(*) as total'))
            ->with('category:id,name')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $categoryLabels = $assetsByCategory->pluck('category.name')->toArray();
        $categoryData = $assetsByCategory->pluck('total')->toArray();

        // ============================================================
        // 4. GRAFIK TREN PERBAIKAN (6 bulan terakhir)
        // ============================================================
        $maintenanceTrend = AssetMaintenance::select(
            DB::raw("DATE_FORMAT(start_date, '%Y-%m') as bulan"),
            DB::raw('COUNT(*) as total')
        )
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
        // 5. AKTIVITAS TERBARU
        // ============================================================
        $recentMaintenances = AssetMaintenance::with(['asset', 'vendor'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentLoans = AssetLoan::with(['asset', 'user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 🆕 Serah terima + pengembalian digabung, urut by aktivitas terbaru
        $recentSerah = AssetAssignment::with(['asset', 'user'])
            ->orderByDesc('assigned_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'type' => 'serah',
                'model' => $a,
                'at' => $a->assigned_at,
            ]);

        $recentKembali = AssetAssignment::with(['asset', 'user'])
            ->whereNotNull('returned_at')
            ->orderByDesc('returned_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'type' => 'kembali',
                'model' => $a,
                'at' => $a->returned_at,
            ]);

        $recentAssignments = collect($recentSerah)
            ->merge($recentKembali)
            ->sortByDesc('at')
            ->take(8)
            ->values();

        // 🆕 Activity Log terbaru
        $recentActivities = Activity::with('causer')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // ============================================================
        // 6. RINGKASAN KONSUMABLE
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
        // 7. TOP STATISTIK
        // ============================================================

        // Top 5 pemakai aset
        $topUsers = User::withCount('currentAssets')
            ->having('current_assets_count', '>', 0)
            ->orderByDesc('current_assets_count')
            ->limit(5)
            ->get();

        // Top 5 aset paling sering diperbaiki
        $topMaintenancedAssets = Asset::withCount('maintenances')
            ->having('maintenances_count', '>', 0)
            ->orderByDesc('maintenances_count')
            ->limit(5)
            ->get();

        // Top 5 brand dengan aset terbanyak
        $topBrands = Asset::select('brand', DB::raw('COUNT(*) as total'))
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ============================================================
        // 8. NILAI ASET
        // ============================================================
        $assetValues = [
            'total_purchase' => Asset::where('assets.ownership_type', 'owned')->sum('purchase_price') ?? 0,
            'monthly_lease' => \App\Models\AssetOwnership::where('ownership_type', 'leased')
                ->sum('monthly_cost') ?? 0,
        ];

        // ============================================================
        // 9. STATISTIK USER & VENDOR
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

        return view('siam.dashboard', compact(
            'assetStats',
            'overdueLoans',
            'lowStockConsumables',
            'expiringContracts',
            'expiringWarranties',
            'categoryLabels',
            'categoryData',
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
            'assetValues',
            'userStats',
            'vendorStats',
        ));
    }
}
