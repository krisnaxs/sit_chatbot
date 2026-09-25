<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    /**
     * Daftar assignment dengan filter lengkap + summary statistik aset.
     */
    public function index(Request $request)
    {
        $query = AssetAssignment::with([
            'asset.category',
            'user',
            'location',
            'department',
            'assignedBy',
        ]);

        // ============================================================
        // SEARCH: SN / brand / model / nama user
        // ============================================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($qa) use ($search) {
                    $qa->where('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                })
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // ============================================================
        // FILTER
        // ============================================================
        if ($request->filled('brand')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('brand', $request->brand);
            });
        }

        if ($request->filled('model')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('model', $request->model);
            });
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        // ============================================================
        // SUMMARY: dari tabel ASSETS (bukan assignment)
        // ============================================================
        $assetSummaryQuery = Asset::query();

        if ($request->filled('search')) {
            $assetSummaryQuery->where(function ($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->search}%")
                    ->orWhere('brand', 'like', "%{$request->search}%")
                    ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('brand')) {
            $assetSummaryQuery->where('brand', $request->brand);
        }

        if ($request->filled('model')) {
            $assetSummaryQuery->where('model', $request->model);
        }

        if ($request->filled('asset_id')) {
            $assetSummaryQuery->where('id', $request->asset_id);
        }

        $summary = [
            'total' => (clone $assetSummaryQuery)->count(),
            'available' => (clone $assetSummaryQuery)->where('status', 'available')->count(),
            'in_use' => (clone $assetSummaryQuery)->where('status', 'in_use')->count(),
            'loaned' => (clone $assetSummaryQuery)->where('status', 'loaned')->count(),
            'maintenance' => (clone $assetSummaryQuery)->where('status', 'maintenance')->count(),
            'retired' => (clone $assetSummaryQuery)->where('status', 'retired')->count(),
            'lost' => (clone $assetSummaryQuery)->where('status', 'lost')->count(),
        ];

        // ============================================================
        // Apakah ada filter aktif?
        // ============================================================
        $hasFilter = $request->filled('search')
            || $request->filled('asset_id')
            || $request->filled('brand')
            || $request->filled('model')
            || $request->filled('user_id')
            || $request->filled('location_id')
            || $request->filled('status');

        // ============================================================
        // SORT & PAGINATION
        // ============================================================
        $assignments = $query->orderByDesc('assigned_at')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        // ============================================================
        // Dropdown data
        // ============================================================
        $assets = Asset::orderBy('serial_number')->get();
        $users = User::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();

        // Brand unik dari tabel aset
        $brands = Asset::select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->pluck('brand');

        // Model + jumlah unit (difilter by brand kalau ada)
        $models = Asset::select('model', 'brand', DB::raw('COUNT(*) as total'))
            ->whereNotNull('model')
            ->when($request->filled('brand'), function ($q) use ($request) {
                $q->where('brand', $request->brand);
            })
            ->groupBy('model', 'brand')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        return view('assignments.index', compact(
            'assignments',
            'assets',
            'users',
            'locations',
            'brands',
            'models',
            'summary',
            'hasFilter'
        ));
    }

    /**
     * Form assign aset ke user.
     */
    public function create(Request $request)
    {
        $asset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        $assets = Asset::where('status', 'available')->orderBy('serial_number')->get();
        $users = User::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('assignments.create', compact('asset', 'assets', 'users', 'locations', 'departments'));
    }

    /**
     * Simpan assignment baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id' => 'required|exists:users,id',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_at' => 'required|date',
            'condition_on_assign' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['received_by'] = $request->user_id;

        $assignment = AssetAssignment::create($validated);

        // Update asset current state
        $assignment->asset->update([
            'status' => 'in_use',
            'current_user_id' => $assignment->user_id,
            'current_location_id' => $assignment->location_id,
        ]);

        return redirect()
            ->route('siam.assets.show', $assignment->asset_id)
            ->with('success', 'Aset berhasil di-assign ke user.');
    }

    /**
     * Kembalikan aset ke kantor.
     */
    public function returnAsset(Request $request, AssetAssignment $assignment)
    {
        $validated = $request->validate([
            'returned_at' => 'required|date',
            'condition_on_return' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $assignment->update($validated);

        // Update asset → available
        $assignment->asset->update([
            'status' => 'available',
            'current_user_id' => null,
            'current_location_id' => null,
        ]);

        return redirect()
            ->route('siam.assets.show', $assignment->asset_id)
            ->with('success', 'Aset berhasil dikembalikan.');
    }

    /**
     * Hapus record assignment.
     */
    public function destroy(AssetAssignment $assignment)
    {
        $assignment->delete();

        return redirect()
            ->route('siam.assignments.index')
            ->with('success', 'Record assignment dihapus.');
    }
}
