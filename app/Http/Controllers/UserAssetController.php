<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\ConsumableTransaction;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Exports\UserAssetsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserAssetController extends Controller
{
    /**
     * Daftar user + aset/konsumabel yang dipegang.
     */
    public function index(Request $request)
    {
        $query = User::with([
            'department',
            'location',
            'currentAssets.category',
            'currentAssets.currentLocation',
            // 🆕 Load relasi tanggal untuk status aset
            'currentAssets.assignments' => function ($q) {
                $q->whereNull('returned_at')
                    ->latest('assigned_at')
                    ->limit(1);
            },
            'currentAssets.loans' => function ($q) {
                $q->where('status', 'borrowed')
                    ->latest('loan_date')
                    ->limit(1);
            },
            'currentAssets.maintenances' => function ($q) {
                $q->whereIn('status', ['open', 'in_progress'])
                    ->latest('start_date')
                    ->limit(1);
            },
            'activeLoans.asset',
            'consumableTransactions' => function ($q) {
                $q->where('type', 'out')
                    ->with(['consumable.category', 'location', 'asset'])
                    ->orderByDesc('transaction_date');
            },
        ])
            ->withCount([
                'currentAssets as total_assets',
                'activeLoans as total_loans',
                'consumableTransactions as total_consumables' => function ($q) {
                    $q->where('type', 'out');
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('has_asset')) {
            if ($request->has_asset === 'yes') {
                $query->has('currentAssets');
            } elseif ($request->has_asset === 'no') {
                $query->doesntHave('currentAssets');
            }
        }

        $users = $query->orderBy('name')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $departments = Department::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();

        // Summary
        $summary = [
            'total_users' => User::active()->count(),
            'users_with_assets' => User::active()->has('currentAssets')->count(),
            'users_without_assets' => User::active()->doesntHave('currentAssets')->count(),
            'total_assets_held' => Asset::whereNotNull('current_user_id')->count(),
            'total_loans_active' => AssetLoan::where('status', 'borrowed')->count(),
            'total_consumables_out' => ConsumableTransaction::where('type', 'out')
                ->whereNotNull('user_id')
                ->sum('quantity'),
        ];

        return view('user-assets.index', compact(
            'users',
            'departments',
            'locations',
            'summary'
        ));
    }

    /**
     * Export Excel — daftar user + aset + konsumabel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'department_id', 'location_id', 'has_asset']);

        $filename = 'user-aset-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new UserAssetsExport($filters), $filename);
    }

    /**
     * Export PDF — daftar user + aset + konsumabel.
     */
    public function exportPdf(Request $request)
    {
        $query = User::with([
            'department',
            'location',
            'currentAssets.category',
            'currentAssets.currentLocation',
            // 🆕 Load relasi tanggal (sama dengan index)
            'currentAssets.assignments' => function ($q) {
                $q->whereNull('returned_at')
                    ->latest('assigned_at')
                    ->limit(1);
            },
            'currentAssets.loans' => function ($q) {
                $q->where('status', 'borrowed')
                    ->latest('loan_date')
                    ->limit(1);
            },
            'currentAssets.maintenances' => function ($q) {
                $q->whereIn('status', ['open', 'in_progress'])
                    ->latest('start_date')
                    ->limit(1);
            },
            'activeLoans.asset',
            'consumableTransactions' => function ($q) {
                $q->where('type', 'out')
                    ->with(['consumable.category', 'location', 'asset'])
                    ->orderByDesc('transaction_date');
            },
        ])->withCount([
                    'currentAssets as total_assets',
                    'activeLoans as total_loans',
                    'consumableTransactions as total_consumables' => function ($q) {
                        $q->where('type', 'out');
                    },
                ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('has_asset')) {
            if ($request->has_asset === 'yes') {
                $query->has('currentAssets');
            } elseif ($request->has_asset === 'no') {
                $query->doesntHave('currentAssets');
            }
        }

        $users = $query->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $summary = [
            'total_users' => $users->count(),
            'users_with_assets' => $users->where('total_assets', '>', 0)->count(),
            'users_without_assets' => $users->where('total_assets', 0)->count(),
            'total_assets_held' => $users->sum('total_assets'),
            'total_loans_active' => $users->sum('total_loans'),
            'total_consumables_out' => $users->sum('total_consumables'),
        ];

        $pdf = Pdf::loadView('user-assets.pdf', compact('users', 'summary', 'departments'))
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('user-aset-' . now()->format('Ymd-His') . '.pdf');
    }
}
