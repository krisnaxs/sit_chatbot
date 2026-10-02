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

    /**
     * Form bulk create — input banyak unit aset identik sekaligus.
     */
    /**
     * Form bulk create — input banyak unit aset identik sekaligus.
     */
    public function bulkCreate()
    {
        $categories = AssetCategory::active()->where('is_consumable', false)->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $users = User::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $brands = AssetType::active()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $assetTypes = AssetType::active()->orderBy('model')->get(['id', 'brand', 'model']);

        return view('assets.bulk-create', compact(
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
     * Preview serial & asset code yang akan di-generate (AJAX, tanpa simpan).
     * Support 2 mode: auto-generate & manual (paste list).
     */
    public function bulkPreview(Request $request)
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $autoSerial = filter_var($request->input('auto_serial', true), FILTER_VALIDATE_BOOLEAN);
        $autoCode = filter_var($request->input('auto_code', true), FILTER_VALIDATE_BOOLEAN);

        // === Build serials ===
        $serials = [];
        if (!$autoSerial) {
            $text = (string) $request->input('serial_list', '');
            $serials = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
                fn($s) => $s !== ''
            ));
        } else {
            $prefix = (string) $request->input('serial_prefix', '');
            $start = max(1, (int) $request->input('serial_start', 1));
            $pad = max(0, (int) $request->input('serial_pad', 4));

            for ($i = 0; $i < $qty; $i++) {
                $n = $start + $i;
                $serials[] = $prefix . ($pad > 0 ? str_pad($n, $pad, '0', STR_PAD_LEFT) : $n);
            }
        }

        // === Build codes ===
        $codes = [];
        if (!$autoCode) {
            $text = (string) $request->input('code_list', '');
            $codes = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
                fn($s) => $s !== ''
            ));
        } elseif ($request->filled('code_prefix')) {
            $prefix = (string) $request->input('code_prefix');
            $start = max(1, (int) $request->input('code_start', 1));
            $pad = max(0, (int) $request->input('code_pad', 4));

            for ($i = 0; $i < $qty; $i++) {
                $n = $start + $i;
                $codes[] = $prefix . ($pad > 0 ? str_pad($n, $pad, '0', STR_PAD_LEFT) : $n);
            }
        }

        // === Build rows ===
        $rows = [];
        $max = max(count($serials), count($codes), $qty);
        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                'no' => $i + 1,
                'serial' => $serials[$i] ?? null,
                'code' => $codes[$i] ?? null,
            ];
        }

        // === Cek duplikat ===
        $dupSerial = $serials ? Asset::whereIn('serial_number', $serials)->pluck('serial_number')->toArray() : [];
        $dupCode = $codes ? Asset::whereIn('asset_code', $codes)->pluck('asset_code')->toArray() : [];

        return response()->json([
            'rows' => $rows,
            'dup_serial' => $dupSerial,
            'dup_code' => $dupCode,
        ]);
    }

    /**
     * Simpan banyak unit aset sekaligus.
     * Support 2 mode: auto-generate & manual (paste list).
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            // === DATA UMUM ===
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
            'status' => 'required|in:available,in_use,retired,lost',
            'condition_percent' => 'nullable|integer|min:0|max:100',
            'condition_notes' => 'nullable|string',
            'notes' => 'nullable|string',

            // === JUMLAH ===
            'quantity' => 'required|integer|min:1|max:500',

            // === AUTO SERIAL ===
            'serial_prefix' => 'nullable|string|max:50',
            'serial_start' => 'nullable|integer|min:1',
            'serial_pad' => 'nullable|integer|min:0|max:6',
            'serial_list' => 'nullable|string',

            // === AUTO CODE ===
            'code_prefix' => 'nullable|string|max:50',
            'code_start' => 'nullable|integer|min:1',
            'code_pad' => 'nullable|integer|min:0|max:6',
            'code_list' => 'nullable|string',

            // === HOSTNAME ===
            'hostname_prefix' => 'nullable|string|max:50',
            'hostname_start' => 'nullable|integer|min:1',
            'hostname_pad' => 'nullable|integer|min:0|max:6',

            // === ASSIGN ===
            'assign' => 'nullable|array',
            'assign.user_id' => 'nullable|exists:users,id',
            'assign.location_id' => 'nullable|exists:locations,id',
            'assign.department_id' => 'nullable|exists:departments,id',
            'assign.assigned_at' => 'nullable|date',
            'assign.condition_on_assign' => 'nullable|integer|min:0|max:100',
            'assign.notes' => 'nullable|string',
        ]);

        // Validasi kondisional
        if ($validated['status'] === 'in_use' && empty($validated['assign']['user_id'])) {
            return back()->withInput()
                ->with('error', 'Untuk status "Dipakai", wajib isi pegawai penerima.');
        }

        $qty = (int) $validated['quantity'];

        // ============================================================
        // === GENERATE / PARSE SERIAL NUMBERS ===
        // ============================================================
        $serials = [];
        $isManualSerial = !empty(trim($validated['serial_list'] ?? ''));

        if ($isManualSerial) {
            $serials = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $validated['serial_list'])),
                fn($s) => $s !== ''
            ));

            if (count($serials) !== $qty) {
                return back()->withInput()->with(
                    'error',
                    "Jumlah Serial Number manual (" . count($serials) . ") tidak sama dengan jumlah unit ($qty)."
                );
            }
        } else {
            if (empty($validated['serial_prefix'])) {
                return back()->withInput()->with('error', 'Prefix serial wajib diisi untuk mode auto.');
            }
            $serialStart = (int) ($validated['serial_start'] ?? 1);
            $serialPad = (int) ($validated['serial_pad'] ?? 0);

            for ($i = 0; $i < $qty; $i++) {
                $num = $serialStart + $i;
                $serials[] = $validated['serial_prefix']
                    . ($serialPad > 0 ? str_pad($num, $serialPad, '0', STR_PAD_LEFT) : $num);
            }
        }

        // Cek duplikat serial (1 query)
        $dupSerial = Asset::whereIn('serial_number', $serials)->pluck('serial_number')->toArray();
        if (!empty($dupSerial)) {
            return back()->withInput()
                ->with('error', 'Serial number berikut sudah ada: ' . implode(', ', array_slice($dupSerial, 0, 10)));
        }

        // ============================================================
        // === GENERATE / PARSE ASSET CODES ===
        // ============================================================
        $codes = [];
        $isManualCode = !empty(trim($validated['code_list'] ?? ''));

        if ($isManualCode) {
            $codes = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $validated['code_list'])),
                fn($s) => $s !== ''
            ));

            if (count($codes) !== $qty) {
                return back()->withInput()->with(
                    'error',
                    "Jumlah Asset Code manual (" . count($codes) . ") tidak sama dengan jumlah unit ($qty)."
                );
            }
        } elseif (!empty($validated['code_prefix'])) {
            $codeStart = (int) ($validated['code_start'] ?? 1);
            $codePad = (int) ($validated['code_pad'] ?? 0);

            for ($i = 0; $i < $qty; $i++) {
                $num = $codeStart + $i;
                $codes[] = $validated['code_prefix']
                    . ($codePad > 0 ? str_pad($num, $codePad, '0', STR_PAD_LEFT) : $num);
            }
        }

        // Cek duplikat code
        if (!empty($codes)) {
            $dupCode = Asset::whereIn('asset_code', $codes)->pluck('asset_code')->toArray();
            if (!empty($dupCode)) {
                return back()->withInput()
                    ->with('error', 'Asset code berikut sudah ada: ' . implode(', ', array_slice($dupCode, 0, 10)));
            }
        }

        // ============================================================
        // === GENERATE HOSTNAMES (opsional) ===
        // ============================================================
        $hostnames = [];
        if (!empty($validated['hostname_prefix'])) {
            $hostStart = (int) ($validated['hostname_start'] ?? 1);
            $hostPad = (int) ($validated['hostname_pad'] ?? 0);

            for ($i = 0; $i < $qty; $i++) {
                $num = $hostStart + $i;
                $hostnames[] = $validated['hostname_prefix']
                    . ($hostPad > 0 ? str_pad($num, $hostPad, '0', STR_PAD_LEFT) : $num);
            }
        }

        // ============================================================
        // === SIMPAN (1 transaksi, batch insert) ===
        // ============================================================
        $count = DB::transaction(function () use ($validated, $qty, $serials, $codes, $hostnames) {
            $now = now();
            $assignUserId = $validated['assign']['user_id'] ?? null;
            $assignLocationId = $validated['assign']['location_id'] ?? null;
            $isAssign = $validated['status'] === 'in_use' && $assignUserId;

            // Build rows untuk Asset::insert()
            $rows = [];
            for ($i = 0; $i < $qty; $i++) {
                $rows[] = [
                    'asset_code' => $codes[$i] ?? null,
                    'serial_number' => $serials[$i],
                    'hostname' => $hostnames[$i] ?? null,
                    'brand' => $validated['brand'],
                    'model' => $validated['model'],
                    'category_id' => $validated['category_id'],
                    'specification' => isset($validated['specification'])
                        ? json_encode($validated['specification'])
                        : null,
                    'os' => $validated['os'] ?? null,
                    'os_license' => $validated['os_license'] ?? null,
                    'ownership_type' => $validated['ownership_type'],
                    'purchase_date' => $validated['purchase_date'] ?? null,
                    'purchase_price' => $validated['purchase_price'] ?? null,
                    'warranty_expire' => $validated['warranty_expire'] ?? null,
                    'status' => $validated['status'],
                    'condition_percent' => $validated['condition_percent'] ?? 100,
                    'condition_notes' => $validated['condition_notes'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'current_user_id' => $isAssign ? $assignUserId : null,
                    'current_location_id' => $isAssign ? $assignLocationId : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Asset::insert($rows);

            // Ambil ID baru
            $newAssets = Asset::whereIn('serial_number', $serials)
                ->get(['id', 'serial_number']);

            // === Ownership batch ===
            $ownerships = [];
            foreach ($newAssets as $a) {
                $ownerships[] = [
                    'asset_id' => $a->id,
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'ownership_type' => $validated['ownership_type'],
                    'purchase_price' => $validated['ownership_type'] === 'owned'
                        ? ($validated['purchase_price'] ?? null) : null,
                    'invoice_number' => $validated['ownership_type'] === 'owned'
                        ? ($validated['invoice_number'] ?? null) : null,
                    'contract_number' => $validated['ownership_type'] === 'leased'
                        ? ($validated['invoice_number'] ?? null) : null,
                    'contract_start' => $validated['ownership_type'] === 'leased'
                        ? ($validated['purchase_date'] ?? null) : null,
                    'contract_end' => $validated['ownership_type'] === 'leased'
                        ? ($validated['contract_end'] ?? null) : null,
                    'monthly_cost' => $validated['ownership_type'] === 'leased'
                        ? ($validated['monthly_cost'] ?? null) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            AssetOwnership::insert($ownerships);

            // === Assignment batch ===
            if ($isAssign) {
                $assignments = [];
                foreach ($newAssets as $a) {
                    $assignments[] = [
                        'asset_id' => $a->id,
                        'user_id' => $assignUserId,
                        'location_id' => $validated['assign']['location_id'] ?? null,
                        'department_id' => $validated['assign']['department_id'] ?? null,
                        'assigned_at' => $validated['assign']['assigned_at'] ?? $now,
                        'condition_on_assign' => $validated['assign']['condition_on_assign']
                            ?? ($validated['condition_percent'] ?? 100),
                        'notes' => $validated['assign']['notes'] ?? null,
                        'assigned_by' => auth()->id(),
                        'received_by' => $assignUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                AssetAssignment::insert($assignments);
            }

            return $newAssets->count();
        });

        return redirect()
            ->route('siam.assets.index')
            ->with('success', "{$count} aset berhasil ditambahkan sekaligus.");
    }
    /**
     * Halaman monitoring realtime semua aset agent-monitored.
     */
    /**
     * Halaman monitoring realtime semua aset agent-monitored.
     */
    public function monitoring(Request $request)
    {
        // ============================================================
        // === QUERY DASAR (dipakai untuk CARD + TABEL) ===
        // ============================================================
        $baseQuery = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->whereHas('category', fn($q) => $q->where('is_agent_monitored', true));

        // Filter yang MENGARUHI card & tabel
        if ($request->filled('category_id')) {
            $baseQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('asset_status')) {
            $baseQuery->where('status', $request->asset_status);
        }

        if ($request->filled('wifi')) {
            $baseQuery->where('last_wifi_ssid', $request->wifi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('last_ip', 'like', "%{$search}%")
                    ->orWhere('last_logged_user', 'like', "%{$search}%")
                    ->orWhereHas('currentUser', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // ============================================================
        // === STATS (dihitung dari baseQuery — IKUT TERFILTER) ===
        // ============================================================
        $now = now();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'online' => (clone $baseQuery)->where('last_seen_at', '>=', $now->copy()->subMinutes(10))->count(),
            'idle' => (clone $baseQuery)->whereBetween('last_seen_at', [
                $now->copy()->subMinutes(60),
                $now->copy()->subMinutes(10),
            ])->count(),
            'offline' => (clone $baseQuery)->where('last_seen_at', '<', $now->copy()->subMinutes(60))
                ->whereNotNull('last_seen_at')->count(),
            'never' => (clone $baseQuery)->whereNull('last_seen_at')->count(),
        ];

        $lifecycleStats = [
            'available' => (clone $baseQuery)->where('status', 'available')->count(),
            'in_use' => (clone $baseQuery)->where('status', 'in_use')->count(),
            'loaned' => (clone $baseQuery)->where('status', 'loaned')->count(),
            'maintenance' => (clone $baseQuery)->where('status', 'maintenance')->count(),
            'retired' => (clone $baseQuery)->where('status', 'retired')->count(),
            'lost' => (clone $baseQuery)->where('status', 'lost')->count(),
        ];

        // ============================================================
        // === QUERY TABEL (baseQuery + filter agent-status) ===
        // ============================================================
        $tableQuery = clone $baseQuery;

        if ($request->filled('status')) {
            match ($request->status) {
                'online' => $tableQuery->where('last_seen_at', '>=', now()->subMinutes(10)),
                'idle' => $tableQuery->whereBetween('last_seen_at', [
                    now()->subMinutes(60),
                    now()->subMinutes(10),
                ]),
                'offline' => $tableQuery->where('last_seen_at', '<', now()->subMinutes(60))
                    ->whereNotNull('last_seen_at'),
                'never' => $tableQuery->whereNull('last_seen_at'),
                default => null,
            };
        }

        $tableQuery->orderByRaw('last_seen_at IS NULL ASC')
            ->orderByDesc('last_seen_at');

        $assets = $tableQuery->paginate($request->get('per_page', 30))->withQueryString();

        // === Coverage ===
        $coverage = $stats['total'] > 0
            ? round(($stats['total'] - $stats['never']) / $stats['total'] * 100, 1)
            : 0;

        // === Filter options ===
        $categories = \App\Models\AssetCategory::where('is_agent_monitored', true)
            ->where('is_consumable', false)
            ->orderBy('name')
            ->get();

        $wifiList = Asset::whereHas('category', fn($q) => $q->where('is_agent_monitored', true))
            ->select('last_wifi_ssid')
            ->whereNotNull('last_wifi_ssid')
            ->distinct()
            ->orderBy('last_wifi_ssid')
            ->pluck('last_wifi_ssid');

        return view('assets.monitoring', compact(
            'assets',
            'stats',
            'lifecycleStats',
            'coverage',
            'categories',
            'wifiList'
        ));
    }

    /**
     * Halaman peta keberadaan aset (Leaflet.js).
     */
    public function map(Request $request)
    {
        // Base query: hanya aset agent-monitored yang punya koordinat
        $query = Asset::with(['category', 'currentUser', 'currentLocation'])
            ->whereHas('category', fn($q) => $q->where('is_agent_monitored', true))
            ->whereNotNull('last_lat')
            ->whereNotNull('last_lng');

        // Filter by status
        if ($request->filled('status')) {
            match ($request->status) {
                'online' => $query->where('last_seen_at', '>=', now()->subMinutes(10)),
                'idle' => $query->whereBetween('last_seen_at', [
                    now()->subMinutes(60),
                    now()->subMinutes(10),
                ]),
                'offline' => $query->where('last_seen_at', '<', now()->subMinutes(60))
                    ->whereNotNull('last_seen_at'),
                default => null,
            };
        }

        // Filter by kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('last_logged_user', 'like', "%{$search}%")
                    ->orWhereHas('currentUser', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Get aset (maks 500 untuk performa peta)
        $assets = $query->orderByDesc('last_seen_at')
            ->limit(500)
            ->get();

        // Stats
        $stats = [
            'total' => $assets->count(),
            'online' => $assets->filter(fn($a) => $a->isOnline())->count(),
            'idle' => $assets->filter(fn($a) => $a->isIdle())->count(),
            'offline' => $assets->filter(fn($a) => $a->isOffline())->count(),
        ];

        // Categories untuk filter
        $categories = AssetCategory::where('is_agent_monitored', true)
            ->where('is_consumable', false)
            ->orderBy('name')
            ->get();

        // Format data untuk JavaScript
        $assetsData = $assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'hostname' => $asset->hostname ?? '-',
                'brand_model' => trim("{$asset->brand} {$asset->model}"),
                'serial_number' => $asset->serial_number,
                'category' => $asset->category?->name,
                'lat' => (float) $asset->last_lat,
                'lng' => (float) $asset->last_lng,
                'status' => $asset->agent_status_color,
                'status_label' => $asset->agent_status_label,
                'last_seen' => $asset->last_seen_at?->diffForHumans(),
                'current_user' => $asset->currentUser?->name,
                'logged_user' => $asset->last_logged_user,
                'location' => $asset->currentLocation?->full_name,
                'ip' => $asset->last_ip,
                'wifi_ssid' => $asset->last_wifi_ssid,
                'location_source' => $asset->location_source,
                'detail_url' => route('siam.assets.show', $asset),
            ];
        })->values()->toArray();

        return view('assets.map', compact(
            'assets',
            'assetsData',
            'stats',
            'categories'
        ));
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
