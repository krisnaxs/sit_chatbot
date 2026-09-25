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
        $query = AssetMaintenance::with(['asset', 'vendor']);

        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('type'))
            $query->where('type', $request->type);
        if ($request->filled('asset_id'))
            $query->where('asset_id', $request->asset_id);

        $maintenances = $query->orderByDesc('start_date')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $assets = Asset::orderBy('serial_number')->get();

        return view('maintenances.index', compact('maintenances', 'assets'));
    }

    public function create(Request $request)
    {
        $asset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        $assets = Asset::orderBy('serial_number')->get();
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
            ->route('siam.maintenances.index')
            ->with('success', 'Data perbaikan berhasil disimpan.');
    }

    public function show(AssetMaintenance $maintenance)
    {
        $maintenance->load(['asset', 'vendor']);
        return view('maintenances.show', compact('maintenance'));
    }

    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,done,cancelled',
            'action' => 'nullable|string',
            'technician' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date',
            'condition_after' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update($validated);

        // Update asset
        if ($validated['status'] === 'done' && $validated['condition_after'] ?? null) {
            $maintenance->asset->update([
                'status' => 'available',
                'condition_percent' => $validated['condition_after'],
            ]);
        } elseif (in_array($validated['status'], ['open', 'in_progress'])) {
            $maintenance->asset->update(['status' => 'maintenance']);
        }

        return redirect()
            ->route('siam.maintenances.index')
            ->with('success', 'Data perbaikan berhasil diupdate.');
    }

    public function destroy(AssetMaintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()
            ->route('siam.maintenances.index')
            ->with('success', 'Data perbaikan dihapus.');
    }
}
