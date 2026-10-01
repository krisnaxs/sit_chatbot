<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetOwnership;
use App\Models\AssetType;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    /**
     * Daftar aset dengan filter lengkap + summary statistik.
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'currentUser', 'currentLocation']);

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
        if ($request->filled('category_id'))
            $query->where('category_id', $request->category_id);
        if ($request->filled('ownership_type'))
            $query->where('ownership_type', $request->ownership_type);
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('pemakai_status')) {
            match ($request->pemakai_status) {
                'perlu_ditarik' => $query->perluDitarik(30),
                'sudah_pensiun' => $query->dipegangPensiunan(),
                'akan_pensiun' => $query->akanDitarik(30),
                default => null,
            };
        }

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
        $hasFilter = $request->filled('search')
            || $request->filled('category_id')
            || $request->filled('ownership_type')
            || $request->filled('status')
            || $request->filled('pemakai_status')   //  tambahan
            || $request->filled('brand')
            || $request->filled('model')
            || $request->filled('location_id')
            || $request->filled('user_id')
            || $request->filled('year');

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowed = ['created_at', 'asset_code', 'serial_number', 'brand', 'model', 'status'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }
        $assets = $query->paginate($request->get('per_page', 25))->withQueryString();

        $categories = AssetCategory::active()->where('is_consumable', false)->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $brands = Asset::select('brand')->distinct()->whereNotNull('brand')->orderBy('brand')->pluck('brand');
        $models = Asset::select('model', 'brand', DB::raw('COUNT(*) as total'))
            ->whereNotNull('model')
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('brand'), fn($q) => $q->where('brand', $request->brand))
            ->groupBy('model', 'brand')->orderBy('brand')->orderBy('model')->get();
        $years = Asset::selectRaw('YEAR(purchase_date) as year')
            ->whereNotNull('purchase_date')->distinct()->orderByDesc('year')->pluck('year');

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
        $categories = AssetCategory::active()->where('is_consumable', false)->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $brands = AssetType::active()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $assetTypes = AssetType::active()->orderBy('model')->get(['id', 'brand', 'model']);

        return view('assets.create', compact(
            'categories',
            'locations',
            'users',
            'vendors',
            'departments',
            'brands',
            'assetTypes'
        ));
    }

    /**
     * Simpan aset baru + ownership + assignment/loan/maintenance.
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
            'vendor_id' => 'nullable|exists:vendors,id',
            'invoice_number' => 'nullable|string|max:100',
            'monthly_cost' => 'nullable|numeric|min:0',
            'contract_end' => 'nullable|date',
            'status' => 'required|in:available,in_use,loaned,maintenance,retired,lost',
            'condition_percent' => 'nullable|integer|min:0|max:100',
            'condition_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'assign' => 'nullable|array',
            'assign.user_id' => 'nullable|exists:users,id',
            'assign.location_id' => 'nullable|exists:locations,id',
            'assign.department_id' => 'nullable|exists:departments,id',
            'assign.hostname' => 'nullable|string|max:100',
            'assign.assigned_at' => 'nullable|date',
            'assign.condition_on_assign' => 'nullable|integer|min:0|max:100',
            'assign.notes' => 'nullable|string',
            'loan' => 'nullable|array',
            'loan.user_id' => 'nullable|exists:users,id',
            'loan.loan_date' => 'nullable|date',
            'loan.due_date' => 'nullable|date|after_or_equal:loan.loan_date',
            'loan.purpose' => 'nullable|string|max:255',
            'loan.condition_on_loan' => 'nullable|integer|min:0|max:100',
            'loan.notes' => 'nullable|string',
            'maintenance' => 'nullable|array',
            'maintenance.issue' => 'nullable|string|max:255',
            'maintenance.action' => 'nullable|string',
            'maintenance.vendor_id' => 'nullable|exists:vendors,id',
            'maintenance.technician' => 'nullable|string|max:100',
            'maintenance.cost' => 'nullable|numeric|min:0',
            'maintenance.start_date' => 'nullable|date',
            'maintenance.end_date' => 'nullable|date',
            'maintenance.condition_before' => 'nullable|integer|min:0|max:100',
            'maintenance.condition_after' => 'nullable|integer|min:0|max:100',
            'maintenance.notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'in_use' && empty($validated['assign']['user_id'])) {
            return back()->withInput()
                ->with('error', 'Untuk status "Dipakai", wajib isi data pegawai penerima.');
        }
        if ($validated['status'] === 'loaned' && empty($validated['loan']['user_id'])) {
            return back()->withInput()
                ->with('error', 'Untuk status "Dipinjam", wajib isi data peminjaman.');
        }
        if ($validated['status'] === 'maintenance' && empty($validated['maintenance']['issue'])) {
            return back()->withInput()
                ->with('error', 'Untuk status "Perbaikan", wajib isi detail perbaikan.');
        }

        $asset = DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('assets', 'public');
            }

            $currentUserId = null;
            $currentLocationId = null;

            if ($validated['status'] === 'in_use' && !empty($validated['assign']['user_id'])) {
                $currentUserId = $validated['assign']['user_id'];
                $currentLocationId = $validated['assign']['location_id'] ?? null;
            } elseif ($validated['status'] === 'loaned' && !empty($validated['loan']['user_id'])) {
                $currentUserId = $validated['loan']['user_id'];
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
                'current_user_id' => $currentUserId,
                'current_location_id' => $currentLocationId,
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

            if ($validated['status'] === 'in_use' && !empty($validated['assign']['user_id'])) {
                $a = $validated['assign'];
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'user_id' => $a['user_id'],
                    'location_id' => $a['location_id'] ?? null,
                    'department_id' => $a['department_id'] ?? null,
                    'assigned_at' => $a['assigned_at'] ?? now(),
                    'condition_on_assign' => $a['condition_on_assign'] ?? ($validated['condition_percent'] ?? 100),
                    'notes' => $a['notes'] ?? null,
                    'assigned_by' => auth()->id(),
                    'received_by' => $a['user_id'],
                ]);
                if (!empty($a['hostname'])) {
                    $asset->update(['hostname' => $a['hostname']]);
                }
            }

            if ($validated['status'] === 'loaned' && !empty($validated['loan']['user_id'])) {
                $l = $validated['loan'];
                AssetLoan::create([
                    'asset_id' => $asset->id,
                    'user_id' => $l['user_id'],
                    'loan_date' => $l['loan_date'] ?? now(),
                    'due_date' => $l['due_date'] ?? now()->addDays(3),
                    'purpose' => $l['purpose'] ?? null,
                    'condition_on_loan' => $l['condition_on_loan'] ?? ($validated['condition_percent'] ?? 100),
                    'notes' => $l['notes'] ?? null,
                    'status' => 'borrowed',
                    'approved_by' => auth()->id(),
                ]);
            }

            if ($validated['status'] === 'maintenance' && !empty($validated['maintenance']['issue'])) {
                $m = $validated['maintenance'];
                AssetMaintenance::create([
                    'asset_id' => $asset->id,
                    'vendor_id' => $m['vendor_id'] ?? null,
                    'type' => 'corrective',
                    'issue' => $m['issue'],
                    'action' => $m['action'] ?? null,
                    'technician' => $m['technician'] ?? null,
                    'cost' => $m['cost'] ?? 0,
                    'start_date' => $m['start_date'] ?? now()->format('Y-m-d'),
                    'end_date' => $m['end_date'] ?? null,
                    'status' => 'open',
                    'condition_before' => $m['condition_before'] ?? ($validated['condition_percent'] ?? 100),
                    'condition_after' => $m['condition_after'] ?? null,
                    'notes' => $m['notes'] ?? null,
                ]);
            }

            return $asset;
        });

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

        $categories = AssetCategory::active()->where('is_consumable', false)->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $brands = AssetType::active()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $assetTypes = AssetType::active()->orderBy('model')->get(['id', 'brand', 'model']);

        return view('assets.edit', compact(
            'asset',
            'categories',
            'locations',
            'users',
            'vendors',
            'departments',
            'brands',
            'assetTypes'
        ));
    }

    /**
     * Update aset + ownership + assignment/loan/maintenance.
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
            'assign' => 'nullable|array',
            'assign.user_id' => 'nullable|exists:users,id',
            'assign.location_id' => 'nullable|exists:locations,id',
            'assign.department_id' => 'nullable|exists:departments,id',
            'assign.hostname' => 'nullable|string|max:100',
            'assign.assigned_at' => 'nullable|date',
            'assign.condition_on_assign' => 'nullable|integer|min:0|max:100',
            'assign.notes' => 'nullable|string',
            'loan' => 'nullable|array',
            'loan.user_id' => 'nullable|exists:users,id',
            'loan.loan_date' => 'nullable|date',
            'loan.due_date' => 'nullable|date|after_or_equal:loan.loan_date',
            'loan.purpose' => 'nullable|string|max:255',
            'loan.condition_on_loan' => 'nullable|integer|min:0|max:100',
            'loan.notes' => 'nullable|string',
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

        if (
            $validated['status'] === 'in_use'
            && $asset->status !== 'in_use'
            && empty($validated['assign']['user_id'])
        ) {
            return back()->withInput()
                ->with('error', 'Untuk status "Dipakai", wajib isi data pegawai penerima.');
        }
        if (
            $validated['status'] === 'loaned'
            && $asset->status !== 'loaned'
            && empty($validated['loan']['user_id'])
        ) {
            return back()->withInput()
                ->with('error', 'Untuk status "Dipinjam", wajib isi data peminjaman.');
        }

        DB::transaction(function () use ($request, $validated, $asset) {
            if ($request->hasFile('photo')) {
                if ($asset->photo_path && Storage::disk('public')->exists($asset->photo_path)) {
                    Storage::disk('public')->delete($asset->photo_path);
                }
                $validated['photo_path'] = $request->file('photo')->store('assets', 'public');
            }

            $newStatus = $validated['status'];

            $currentUserId = null;
            $currentLocationId = null;

            if ($newStatus === 'in_use') {
                if (!empty($validated['assign']['user_id'])) {
                    $currentUserId = $validated['assign']['user_id'];
                    $currentLocationId = $validated['assign']['location_id'] ?? null;
                } else {
                    $currentUserId = $asset->current_user_id;
                    $currentLocationId = $asset->current_location_id;
                }
            } elseif ($newStatus === 'loaned') {
                if (!empty($validated['loan']['user_id'])) {
                    $currentUserId = $validated['loan']['user_id'];
                    $currentLocationId = null;
                } else {
                    $currentUserId = $asset->current_user_id;
                    $currentLocationId = $asset->current_location_id;
                }
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
                'status' => $newStatus,
                'condition_percent' => $validated['condition_percent'] ?? null,
                'condition_notes' => $validated['condition_notes'] ?? null,
                'photo_path' => $validated['photo_path'] ?? $asset->photo_path,
                'notes' => $validated['notes'] ?? null,
                'current_user_id' => $currentUserId,
                'current_location_id' => $currentLocationId,
            ]);

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

            if ($newStatus === 'in_use' && !empty($validated['assign']['user_id'])) {
                $a = $validated['assign'];

                $asset->assignments()
                    ->whereNull('returned_at')
                    ->update(['returned_at' => now()]);

                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'user_id' => $a['user_id'],
                    'location_id' => $a['location_id'] ?? null,
                    'department_id' => $a['department_id'] ?? null,
                    'assigned_at' => $a['assigned_at'] ?? now(),
                    'condition_on_assign' => $a['condition_on_assign'] ?? ($validated['condition_percent'] ?? 100),
                    'notes' => $a['notes'] ?? null,
                    'assigned_by' => auth()->id(),
                    'received_by' => $a['user_id'],
                ]);

                if (!empty($a['hostname'])) {
                    $asset->update(['hostname' => $a['hostname']]);
                }
            }

            if ($newStatus === 'loaned' && !empty($validated['loan']['user_id'])) {
                $l = $validated['loan'];
                AssetLoan::create([
                    'asset_id' => $asset->id,
                    'user_id' => $l['user_id'],
                    'loan_date' => $l['loan_date'] ?? now(),
                    'due_date' => $l['due_date'] ?? now()->addDays(3),
                    'purpose' => $l['purpose'] ?? null,
                    'condition_on_loan' => $l['condition_on_loan'] ?? ($validated['condition_percent'] ?? 100),
                    'notes' => $l['notes'] ?? null,
                    'status' => 'borrowed',
                    'approved_by' => auth()->id(),
                ]);
            }

            if (
                $newStatus === 'maintenance'
                && !empty($validated['maintenance']['issue'])
                && $asset->status !== 'maintenance'
            ) {
                $m = $validated['maintenance'];
                AssetMaintenance::create([
                    'asset_id' => $asset->id,
                    'vendor_id' => $m['vendor_id'] ?? null,
                    'type' => 'corrective',
                    'issue' => $m['issue'],
                    'action' => $m['action'] ?? null,
                    'technician' => $m['technician'] ?? null,
                    'cost' => $m['cost'] ?? 0,
                    'start_date' => $m['start_date'] ?? now()->format('Y-m-d'),
                    'end_date' => $m['end_date'] ?? null,
                    'status' => 'open',
                    'condition_before' => $m['condition_before'] ?? $asset->condition_percent,
                    'condition_after' => $m['condition_after'] ?? null,
                    'notes' => $m['notes'] ?? null,
                ]);
            }
        });

        $hasAssign = $validated['status'] === 'in_use' && !empty($validated['assign']['user_id']);
        $hasLoan = $validated['status'] === 'loaned' && !empty($validated['loan']['user_id']);
        $hasMaint = $validated['status'] === 'maintenance' && !empty($validated['maintenance']['issue']);

        $actions = [];
        if ($hasAssign)
            $actions[] = 'serah terima';
        if ($hasLoan)
            $actions[] = 'peminjaman';
        if ($hasMaint)
            $actions[] = 'perbaikan';

        $msg = empty($actions)
            ? 'Aset berhasil diupdate.'
            : 'Aset diupdate & ' . implode(', ', $actions) . ' berhasil dicatat.';

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
            'pemakai_status',   //  tambahan
            'brand',
            'model',
            'location_id',
            'user_id',
            'year',
        ]);
        $filename = 'daftar-aset-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new AssetsExport($filters), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = Asset::with(['category', 'currentUser', 'currentLocation']);
        if ($request->filled('category_id'))
            $query->where('category_id', $request->category_id);
        if ($request->filled('ownership_type'))
            $query->where('ownership_type', $request->ownership_type);
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('pemakai_status')) {
            match ($request->pemakai_status) {
                'perlu_ditarik' => $query->perluDitarik(30),
                'sudah_pensiun' => $query->dipegangPensiunan(),
                'akan_pensiun' => $query->akanDitarik(30),
                default => null,
            };
        }

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
     * Halaman QR code untuk 1 aset.
     */
    public function qrCode(Asset $asset)
    {
        return view('assets.qr', compact('asset'));
    }

    /**
     * Batch QR — cetak massal.
     */
    public function qrBatch(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('ids')) {
            $ids = is_array($request->ids)
                ? $request->ids
                : explode(',', $request->ids);
            $query->whereIn('id', $ids);
        } else {
            if ($request->filled('category_id'))
                $query->where('category_id', $request->category_id);
            if ($request->filled('status'))
                $query->where('status', $request->status);
            if ($request->filled('ownership_type'))
                $query->where('ownership_type', $request->ownership_type);
            if ($request->filled('brand'))
                $query->where('brand', $request->brand);
            if ($request->filled('model'))
                $query->where('model', $request->model);
            if ($request->filled('pemakai_status')) {
                match ($request->pemakai_status) {
                    'perlu_ditarik' => $query->perluDitarik(30),
                    'sudah_pensiun' => $query->dipegangPensiunan(),
                    'akan_pensiun' => $query->akanDitarik(30),
                    default => null,
                };
            }

            $query->limit(500);
        }

        $assets = $query->orderBy('asset_code')->get();

        return view('assets.qr-batch', compact('assets'));
    }

    public function destroy(Asset $asset)
    {
        if ($asset->photo_path && Storage::disk('public')->exists($asset->photo_path)) {
            Storage::disk('public')->delete($asset->photo_path);
        }
        $asset->delete();

        return redirect()
            ->route('siam.assets.index')
            ->with('success', 'Aset berhasil dihapus.');
    }
}
