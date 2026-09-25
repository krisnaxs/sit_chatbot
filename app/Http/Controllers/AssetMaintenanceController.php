<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Vendor;
use Illuminate\Http\Request;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetMaintenance::with(['asset.currentUser', 'asset.category', 'vendor']);

        // Filter status
        if ($request->filled('status'))
            $query->where('status', $request->status);

        // Filter type
        if ($request->filled('type'))
            $query->where('type', $request->type);

        // Filter asset_id (exact)
        if ($request->filled('asset_id'))
            $query->where('asset_id', $request->asset_id);

        // 🆕 Filter search: SN aset ATAU nama user pemakai
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($qa) use ($search) {
                    $qa->where('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                })
                    ->orWhereHas('asset.currentUser', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $maintenances = $query->orderByDesc('start_date')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        // Data dropdown filter
        $assets = Asset::with('currentUser')
            ->orderBy('serial_number')
            ->get();

        return view('maintenances.index', compact('maintenances', 'assets'));
    }

    public function create(Request $request)
    {
        $asset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        // 🆕 Filter aset berdasarkan search (SN / brand / model / nama user)
        $assetsQuery = Asset::with('currentUser')->orderBy('serial_number');

        if ($request->filled('search')) {
            $search = $request->search;
            $assetsQuery->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('currentUser', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $assets = $assetsQuery->limit(50)->get();
        $vendors = Vendor::active()->orderBy('name')->get();

        return view('maintenances.create', compact('asset', 'assets', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'type' => 'required|in:preventive,corrective,upgrade',
            'issue' => 'required|string|max:255',
            'action' => 'nullable|string',
            'technician' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:open,in_progress,done,cancelled',
            'condition_before' => 'nullable|integer|min:0|max:100',
            'condition_after' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $maintenance = AssetMaintenance::create($validated);

        // Update asset status
        if (in_array($validated['status'], ['open', 'in_progress'])) {
            $maintenance->asset->update(['status' => 'maintenance']);
        } else {
            $maintenance->asset->update(['status' => 'available']);
        }

        return redirect()
            ->route('siam.maintenances.show', $maintenance)
            ->with('success', 'Data perbaikan berhasil disimpan.');
    }

    public function show(AssetMaintenance $maintenance)
    {
        // 🆕 Load relasi termasuk currentUser dari asset
        $maintenance->load([
            'asset.category',
            'asset.currentUser',
            'asset.currentLocation',
            'vendor',
        ]);

        return view('maintenances.show', compact('maintenance'));
    }

    public function edit(AssetMaintenance $maintenance)
    {
        $maintenance->load(['asset.currentUser', 'vendor']);
        $vendors = Vendor::active()->orderBy('name')->get();

        return view('maintenances.edit', compact('maintenance', 'vendors'));
    }

    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,done,cancelled',
            'vendor_id' => 'nullable|exists:vendors,id',
            'action' => 'nullable|string',
            'technician' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date',
            'condition_after' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update($validated);

        // Update asset status berdasarkan status maintenance
        $asset = $maintenance->asset;

        if ($validated['status'] === 'done') {
            $asset->update([
                'status' => 'available',
                'condition_percent' => $validated['condition_after'] ?? $asset->condition_percent,
            ]);
        } elseif ($validated['status'] === 'cancelled') {
            $asset->update(['status' => 'available']);
        } elseif (in_array($validated['status'], ['open', 'in_progress'])) {
            $asset->update(['status' => 'maintenance']);
        }

        return redirect()
            ->route('siam.maintenances.show', $maintenance)
            ->with('success', 'Data perbaikan berhasil diupdate.');
    }

    public function complete(Request $request, AssetMaintenance $maintenance)
    {
        if (in_array($maintenance->status, ['done', 'cancelled'])) {
            return back()->with('error', 'Perbaikan ini sudah selesai atau dibatalkan.');
        }

        $validated = $request->validate([
            'condition_after' => 'required|integer|min:0|max:100',
            'action' => 'nullable|string',
            'technician' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'end_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update([
            'status' => 'done',
            'condition_after' => $validated['condition_after'],
            'action' => $validated['action'] ?? $maintenance->action,
            'technician' => $validated['technician'] ?? $maintenance->technician,
            'cost' => $validated['cost'] ?? $maintenance->cost,
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] ?? $maintenance->notes,
        ]);

        $maintenance->asset->update([
            'status' => 'available',
            'condition_percent' => $validated['condition_after'],
        ]);

        return redirect()
            ->route('siam.maintenances.show', $maintenance)
            ->with('success', 'Perbaikan ditandai selesai. Aset kembali tersedia.');
    }

    public function destroy(AssetMaintenance $maintenance)
    {
        $asset = $maintenance->asset;

        if (in_array($maintenance->status, ['open', 'in_progress'])) {
            $asset->update(['status' => 'available']);
        }

        $maintenance->delete();

        return redirect()
            ->route('siam.maintenances.index')
            ->with('success', 'Data perbaikan dihapus.');
    }
}
