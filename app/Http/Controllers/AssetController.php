<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetOwnership;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Daftar aset dengan filter lengkap + summary statistik.
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'currentUser', 'currentLocation']);

        // ============================================================
        // SEARCH
        // ============================================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('currentUser', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // ============================================================
        // FILTER
        // ============================================================
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('ownership_type')) {
            $query->where('ownership_type', $request->ownership_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }
        if ($request->filled('location_id')) {
            $query->where('current_location_id', $request->location_id);
        }
        if ($request->filled('user_id')) {
            $query->where('current_user_id', $request->user_id);
        }

        // ============================================================
        // SUMMARY (dari hasil filter SEBELUM pagination)
        // ============================================================
        $summary = [
            'total' => (clone $query)->count(),
            'available' => (clone $query)->where('status', 'available')->count(),
            'in_use' => (clone $query)->where('status', 'in_use')->count(),
            'loaned' => (clone $query)->where('status', 'loaned')->count(),
            'maintenance' => (clone $query)->where('status', 'maintenance')->count(),
            'retired' => (clone $query)->where('status', 'retired')->count(),
            'lost' => (clone $query)->where('status', 'lost')->count(),
            'owned' => (clone $query)->where('ownership_type', 'owned')->count(),
            'leased' => (clone $query)->where('ownership_type', 'leased')->count(),
        ];

        // Apakah ada filter aktif?
        $hasFilter = $request->filled('search')
            || $request->filled('category_id')
            || $request->filled('ownership_type')
            || $request->filled('status')
            || $request->filled('brand')
            || $request->filled('model')
            || $request->filled('location_id')
            || $request->filled('user_id');

        // ============================================================
        // SORT
        // ============================================================
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowed = ['created_at', 'asset_code', 'serial_number', 'brand', 'model', 'status'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // ============================================================
        // PAGINATION
        // ============================================================
        $assets = $query->paginate($request->get('per_page', 25))->withQueryString();

        // ============================================================
        // Dropdown data
        // ============================================================
        $categories = AssetCategory::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();

        $brands = Asset::select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->pluck('brand');

        // Model + jumlah unit (difilter kalau ada category_id atau brand)
        $models = Asset::select('model', 'brand', DB::raw('COUNT(*) as total'))
            ->whereNotNull('model')
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            })
            ->when($request->filled('brand'), function ($q) use ($request) {
                $q->where('brand', $request->brand);
            })
            ->groupBy('model', 'brand')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        // Statistik global (selalu total keseluruhan)
        $stats = [
            'total' => Asset::count(),
            'owned' => Asset::owned()->count(),
            'leased' => Asset::leased()->count(),
            'in_use' => Asset::inUse()->count(),
            'available' => Asset::available()->count(),
        ];

        return view('assets.index', compact(
            'assets',
            'categories',
            'locations',
            'users',
            'brands',
            'models',
            'stats',
            'summary',      // 🆕
            'hasFilter'     // 🆕
        ));
    }

    /**
     * Detail aset + history lengkap.
     */
    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'currentUser',
            'currentLocation',
            'ownership.vendor',
            'assignments.user',
            'assignments.location',
            'assignments.assignedBy',
            'assignments.receivedBy',
            'loans.user',
            'maintenances.vendor',
            'attachments',
        ]);

        return view('assets.show', compact('asset'));
    }

    /**
     * Form tambah aset.
     */
    public function create()
    {
        $categories = AssetCategory::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();

        $brands = AssetType::active()
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $assetTypes = AssetType::active()
            ->orderBy('model')
            ->get(['id', 'brand', 'model']);

        return view('assets.create', compact(
            'categories',
            'locations',
            'users',
            'vendors',
            'brands',
            'assetTypes'
        ));
    }

    /**
     * Simpan aset baru + ownership-nya.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|unique:assets,serial_number',
            'asset_code' => 'nullable|unique:assets,asset_code',
            'hostname' => 'nullable|string|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'category_id' => 'required|exists:asset_categories,id',
            'specification' => 'nullable|array',
            'os' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:100',
            'ownership_type' => 'required|in:owned,leased',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'warranty_expire' => 'nullable|date',
            'status' => 'required|in:available,in_use,loaned,maintenance,retired,lost',
            'condition_percent' => 'nullable|integer|min:0|max:100',
            'condition_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',

            'vendor_id' => 'nullable|exists:vendors,id',
            'invoice_number' => 'nullable|string|max:100',
            'monthly_cost' => 'nullable|numeric|min:0',
            'contract_end' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('assets', 'public');
            }

            $asset = Asset::create([
                'asset_code' => $validated['asset_code'] ?? null,
                'serial_number' => $validated['serial_number'],
                'hostname' => $validated['hostname'] ?? null,
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'category_id' => $validated['category_id'],
                'specification' => $validated['specification'] ?? null,
                'os' => $validated['os'] ?? null,
                'os_license' => $validated['os_license'] ?? null,
                'ownership_type' => $validated['ownership_type'],
                'purchase_date' => $validated['purchase_date'] ?? null,
                'purchase_price' => $validated['purchase_price'] ?? null,
                'warranty_expire' => $validated['warranty_expire'] ?? null,
                'status' => $validated['status'],
                'condition_percent' => $validated['condition_percent'] ?? null,
                'condition_notes' => $validated['condition_notes'] ?? null,
                'photo_path' => $validated['photo_path'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            AssetOwnership::create([
                'asset_id' => $asset->id,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'ownership_type' => $validated['ownership_type'],
                'purchase_price' => $validated['ownership_type'] === 'owned' ? ($validated['purchase_price'] ?? null) : null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'contract_number' => $validated['ownership_type'] === 'leased' ? ($validated['invoice_number'] ?? null) : null,
                'contract_start' => $validated['ownership_type'] === 'leased' ? ($validated['purchase_date'] ?? null) : null,
                'contract_end' => $validated['ownership_type'] === 'leased' ? ($validated['contract_end'] ?? null) : null,
                'monthly_cost' => $validated['ownership_type'] === 'leased' ? ($validated['monthly_cost'] ?? null) : null,
            ]);

            return $asset;
        });

        $asset = Asset::where('serial_number', $validated['serial_number'])->first();

        return redirect()
            ->route('siam.assets.show', $asset)
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    /**
     * Form edit aset.
     */
    public function edit(Asset $asset)
    {
        $asset->load('ownership');

        $categories = AssetCategory::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();

        $brands = AssetType::active()
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $assetTypes = AssetType::active()
            ->orderBy('model')
            ->get(['id', 'brand', 'model']);

        return view('assets.edit', compact(
            'asset',
            'categories',
            'locations',
            'users',
            'vendors',
            'brands',
            'assetTypes'
        ));
    }

    /**
     * Update aset + ownership.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'serial_number' => 'required|unique:assets,serial_number,' . $asset->id,
            'asset_code' => 'nullable|unique:assets,asset_code,' . $asset->id,
            'hostname' => 'nullable|string|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'category_id' => 'required|exists:asset_categories,id',
            'specification' => 'nullable|array',
            'os' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:100',
            'ownership_type' => 'required|in:owned,leased',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'warranty_expire' => 'nullable|date',
            'status' => 'required|in:available,in_use,loaned,maintenance,retired,lost',
            'condition_percent' => 'nullable|integer|min:0|max:100',
            'condition_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',

            'vendor_id' => 'nullable|exists:vendors,id',
            'invoice_number' => 'nullable|string|max:100',
            'monthly_cost' => 'nullable|numeric|min:0',
            'contract_end' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $validated, $asset) {
            if ($request->hasFile('photo')) {
                if ($asset->photo_path && \Storage::disk('public')->exists($asset->photo_path)) {
                    \Storage::disk('public')->delete($asset->photo_path);
                }
                $validated['photo_path'] = $request->file('photo')->store('assets', 'public');
            }

            $asset->update([
                'asset_code' => $validated['asset_code'] ?? $asset->asset_code,
                'serial_number' => $validated['serial_number'],
                'hostname' => $validated['hostname'] ?? null,
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'category_id' => $validated['category_id'],
                'specification' => $validated['specification'] ?? null,
                'os' => $validated['os'] ?? null,
                'os_license' => $validated['os_license'] ?? null,
                'ownership_type' => $validated['ownership_type'],
                'purchase_date' => $validated['purchase_date'] ?? null,
                'purchase_price' => $validated['purchase_price'] ?? null,
                'warranty_expire' => $validated['warranty_expire'] ?? null,
                'status' => $validated['status'],
                'condition_percent' => $validated['condition_percent'] ?? null,
                'condition_notes' => $validated['condition_notes'] ?? null,
                'photo_path' => $validated['photo_path'] ?? $asset->photo_path,
                'notes' => $validated['notes'] ?? null,
            ]);

            $ownership = $asset->ownership;

            if ($ownership) {
                $ownership->update([
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'ownership_type' => $validated['ownership_type'],
                    'purchase_price' => $validated['ownership_type'] === 'owned' ? ($validated['purchase_price'] ?? null) : null,
                    'invoice_number' => $validated['ownership_type'] === 'owned' ? ($validated['invoice_number'] ?? null) : null,
                    'contract_number' => $validated['ownership_type'] === 'leased' ? ($validated['invoice_number'] ?? null) : null,
                    'contract_start' => $validated['ownership_type'] === 'leased' ? ($validated['purchase_date'] ?? null) : null,
                    'contract_end' => $validated['ownership_type'] === 'leased' ? ($validated['contract_end'] ?? null) : null,
                    'monthly_cost' => $validated['ownership_type'] === 'leased' ? ($validated['monthly_cost'] ?? null) : null,
                ]);
            } else {
                AssetOwnership::create([
                    'asset_id' => $asset->id,
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'ownership_type' => $validated['ownership_type'],
                    'purchase_price' => $validated['ownership_type'] === 'owned' ? ($validated['purchase_price'] ?? null) : null,
                    'invoice_number' => $validated['invoice_number'] ?? null,
                    'contract_number' => $validated['ownership_type'] === 'leased' ? ($validated['invoice_number'] ?? null) : null,
                    'contract_start' => $validated['ownership_type'] === 'leased' ? ($validated['purchase_date'] ?? null) : null,
                    'contract_end' => $validated['ownership_type'] === 'leased' ? ($validated['contract_end'] ?? null) : null,
                    'monthly_cost' => $validated['ownership_type'] === 'leased' ? ($validated['monthly_cost'] ?? null) : null,
                ]);
            }
        });

        return redirect()
            ->route('siam.assets.show', $asset)
            ->with('success', 'Aset berhasil diupdate.');
    }

    /**
     * Hapus aset.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->photo_path && \Storage::disk('public')->exists($asset->photo_path)) {
            \Storage::disk('public')->delete($asset->photo_path);
        }

        $asset->delete();

        return redirect()
            ->route('siam.assets.index')
            ->with('success', 'Aset berhasil dihapus.');
    }
}
