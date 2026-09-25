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
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class AssetController extends Controller
{
    /**
     * Daftar aset dengan filter lengkap + summary statistik.
     */
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

        // 🆕 Filter tahun pembelian
        if ($request->filled('year')) {
            $query->whereYear('purchase_date', $request->year);
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
            || $request->filled('user_id')
            || $request->filled('year');

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
        $categories = AssetCategory::active()
            ->where('is_consumable', false)
            ->orderBy('name')
            ->get();
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

        // 🆕 Daftar tahun pembelian unik
        $years = Asset::selectRaw('YEAR(purchase_date) as year')
            ->whereNotNull('purchase_date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Statistik global (selalu total keseluruhan)
        $stats = [
            'total' => Asset::count(),
            'owned' => Asset::owned()->count(),
            'leased' => Asset::leased()->count(),
            'in_use' => Asset::inUse()->count(),
            'available' => Asset::available()->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'retired' => Asset::where('status', 'retired')->count(),
            'lost' => Asset::where('status', 'lost')->count(),
        ];

        return view('assets.index', compact(
            'assets',
            'categories',
            'locations',
            'users',
            'brands',
            'models',
            'stats',
            'summary',
            'hasFilter',
            'years'
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
        $categories = AssetCategory::active()
            ->where('is_consumable', false)
            ->orderBy('name')
            ->get();
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

        $categories = AssetCategory::active()
            ->where('is_consumable', false)
            ->orderBy('name')
            ->get();
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
     * Update aset + ownership + (opsional) maintenance.
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

            // 🆕 Field maintenance dari modal — semua nullable
            'maintenance' => 'nullable|array',
            'maintenance.issue' => 'nullable|string|max:255',
            'maintenance.action' => 'nullable|string',
            'maintenance.vendor_id' => 'nullable|exists:vendors,id',
            'maintenance.technician' => 'nullable|string|max:100',
            'maintenance.cost' => 'nullable|numeric|min:0',
            'maintenance.start_date' => 'nullable|date',
            'maintenance.end_date' => 'nullable|date|after_or_equal:maintenance.start_date',
            'maintenance.condition_before' => 'nullable|integer|min:0|max:100',
            'maintenance.condition_after' => 'nullable|integer|min:0|max:100',
            'maintenance.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $validated, $asset) {
            // === Update asset ===
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

            // === Update ownership ===
            $ownership = $asset->ownership;
            $ownershipData = [
                'vendor_id' => $validated['vendor_id'] ?? null,
                'ownership_type' => $validated['ownership_type'],
                'purchase_price' => $validated['ownership_type'] === 'owned' ? ($validated['purchase_price'] ?? null) : null,
                'invoice_number' => $validated['ownership_type'] === 'owned' ? ($validated['invoice_number'] ?? null) : null,
                'contract_number' => $validated['ownership_type'] === 'leased' ? ($validated['invoice_number'] ?? null) : null,
                'contract_start' => $validated['ownership_type'] === 'leased' ? ($validated['purchase_date'] ?? null) : null,
                'contract_end' => $validated['ownership_type'] === 'leased' ? ($validated['contract_end'] ?? null) : null,
                'monthly_cost' => $validated['ownership_type'] === 'leased' ? ($validated['monthly_cost'] ?? null) : null,
            ];

            if ($ownership) {
                $ownership->update($ownershipData);
            } else {
                AssetOwnership::create(array_merge($ownershipData, ['asset_id' => $asset->id]));
            }

            // 🆕 === Buat maintenance record HANYA kalau issue diisi ===
            if (!empty($validated['maintenance']) && !empty($validated['maintenance']['issue'])) {
                $maint = $validated['maintenance'];

                \App\Models\AssetMaintenance::create([
                    'asset_id' => $asset->id,
                    'vendor_id' => $maint['vendor_id'] ?? null,
                    'type' => 'corrective',
                    'issue' => $maint['issue'],
                    'action' => $maint['action'] ?? null,
                    'technician' => $maint['technician'] ?? null,
                    'cost' => $maint['cost'] ?? 0,
                    'start_date' => $maint['start_date'] ?? now()->format('Y-m-d'),
                    'end_date' => $maint['end_date'] ?? null,
                    'status' => 'open',
                    'condition_before' => $maint['condition_before'] ?? $asset->condition_percent,
                    'condition_after' => $maint['condition_after'] ?? null,
                    'notes' => $maint['notes'] ?? null,
                ]);
            }
        });

        $msg = (!empty($validated['maintenance']) && !empty($validated['maintenance']['issue']))
            ? 'Aset diupdate & perbaikan berhasil dicatat.'
            : 'Aset berhasil diupdate.';

        return redirect()
            ->route('siam.assets.show', $asset)
            ->with('success', $msg);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'search',
            'category_id',
            'ownership_type',
            'status',
            'brand',
            'model',
            'location_id',
            'user_id',
            'year',
        ]);

        $filename = 'daftar-aset-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new AssetsExport($filters), $filename);
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Asset::with(['category', 'currentUser', 'currentLocation']);

        // Terapkan filter yang sama seperti index()
        // (bisa refactor jadi method private applyFilters($query, $request))
        if ($request->filled('search')) { /* ... */
        }
        if ($request->filled('category_id'))
            $query->where('category_id', $request->category_id);
        if ($request->filled('ownership_type'))
            $query->where('ownership_type', $request->ownership_type);
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('brand'))
            $query->where('brand', $request->brand);
        if ($request->filled('model'))
            $query->where('model', $request->model);
        if ($request->filled('location_id'))
            $query->where('current_location_id', $request->location_id);
        if ($request->filled('user_id'))
            $query->where('current_user_id', $request->user_id);
        if ($request->filled('year'))
            $query->whereYear('purchase_date', $request->year);

        $assets = $query->orderBy('asset_code')->get();

        $summary = [
            'total' => $assets->count(),
            'available' => $assets->where('status', 'available')->count(),
            'in_use' => $assets->where('status', 'in_use')->count(),
            'maintenance' => $assets->where('status', 'maintenance')->count(),
            'owned' => $assets->where('ownership_type', 'owned')->count(),
            'leased' => $assets->where('ownership_type', 'leased')->count(),
        ];

        $pdf = Pdf::loadView('assets.pdf', compact('assets', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('daftar-aset-' . now()->format('Ymd-His') . '.pdf');
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
