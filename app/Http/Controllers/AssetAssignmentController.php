<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\BeritaAcara;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Services\BeritaAcaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    public function __construct(
        protected BeritaAcaraService $baService
    ) {
    }

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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                    ->orWhereHas('asset', function ($qa) use ($search) {
                        $qa->where('serial_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('hostname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }
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

        $assetSummaryQuery = Asset::query();

        if ($request->filled('search')) {
            $assetSummaryQuery->where(function ($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->search}%")
                    ->orWhere('brand', 'like', "%{$request->search}%")
                    ->orWhere('model', 'like', "%{$request->search}%")
                    ->orWhere('hostname', 'like', "%{$request->search}%");
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

        $hasFilter = $request->filled('search')
            || $request->filled('asset_id')
            || $request->filled('brand')
            || $request->filled('model')
            || $request->filled('user_id')
            || $request->filled('location_id')
            || $request->filled('status');

        $assignments = $query->orderByDesc('updated_at')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $assets = Asset::orderBy('serial_number')->get();
        $users = User::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $brands = Asset::select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->pluck('brand');
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
        $assetsQuery = Asset::whereIn('status', ['available', 'in_use'])
            ->orderBy('serial_number');
        if ($asset) {
            $assets = Asset::where(function ($q) use ($asset) {
                $q->whereIn('status', ['available', 'in_use'])
                    ->orWhere('id', $asset->id);
            })
                ->orderBy('serial_number')
                ->get();
        } else {
            $assets = $assetsQuery->get();
        }

        $users = User::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        // 🆕 Pejabat penandatangan (admin & support only)
        $penandatangan = User::active()
            ->whereIn('role', ['admin'])
            ->orderBy('name')
            ->get();

        return view('assignments.create', compact(
            'asset',
            'assets',
            'users',
            'locations',
            'departments',
            'penandatangan'
        ));
    }

    /**
     * Simpan assignment baru + opsional generate BAST.
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
            'hostname' => 'nullable|string|max:100',
            'notes' => 'nullable|string',

            // 🆕 Data Berita Acara Serah Terima
            'buat_berita_acara' => 'nullable|boolean',
            'pihak_pertama_id' => 'required_if:buat_berita_acara,1|nullable|exists:users,id',
            'pihak_pertama_jabatan' => 'nullable|string|max:100',
            'pihak_pertama_nip' => 'nullable|string|max:30',
            'tempat_ba' => 'nullable|string|max:100',
        ]);

        // Validasi kondisional: hanya role admin/support yang bisa jadi pihak pertama
        if ($request->boolean('buat_berita_acara') && !empty($validated['pihak_pertama_id'])) {
            $pihakPertama = User::find($validated['pihak_pertama_id']);
            if (!$pihakPertama || !in_array($pihakPertama->role, ['admin', 'support'])) {
                return back()->withInput()
                    ->with('error', 'Pihak Pertama harus Admin atau Support.');
            }
        }

        $result = DB::transaction(function () use ($request, $validated) {
            $asset = Asset::findOrFail($validated['asset_id']);

            // Tutup assignment lama kalau ada
            AssetAssignment::where('asset_id', $asset->id)
                ->whereNull('returned_at')
                ->update(['returned_at' => now()]);

            $assignment = AssetAssignment::create([
                'asset_id' => $validated['asset_id'],
                'user_id' => $validated['user_id'],
                'location_id' => $validated['location_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'assigned_at' => $validated['assigned_at'],
                'condition_on_assign' => $validated['condition_on_assign'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'assigned_by' => auth()->id(),
                'received_by' => $validated['user_id'],
            ]);

            $updateData = [
                'status' => 'in_use',
                'current_user_id' => $validated['user_id'],
                'current_location_id' => $validated['location_id'] ?? null,
            ];
            if (!empty($validated['hostname'])) {
                $updateData['hostname'] = $validated['hostname'];
            }
            $asset->update($updateData);

            // 🆕 Generate BAST
            $ba = null;
            if ($request->boolean('buat_berita_acara') && !empty($validated['pihak_pertama_id'])) {
                $pihakPertama = User::find($validated['pihak_pertama_id']);
                $ba = $this->baService->createSerahTerima(
                    $assignment->fresh(['asset.category', 'user']),
                    $pihakPertama,
                    [
                        'pihak_pertama_jabatan' => $validated['pihak_pertama_jabatan'] ?? null,
                        'pihak_pertama_nip' => $validated['pihak_pertama_nip'] ?? null,
                        'tempat_ba' => $validated['tempat_ba'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                    ]
                );
            }

            return ['assignment' => $assignment, 'ba' => $ba];
        });

        $message = 'Aset berhasil di-assign / dipindahkan ke user baru.';
        if ($result['ba']) {
            $message .= " Berita Acara Serah Terima {$result['ba']->nomor_ba} berhasil dibuat.";
        }

        return redirect()
            ->route('siam.assets.show', $validated['asset_id'])
            ->with('success', $message);
    }

    /**
     * Kembalikan aset ke kantor + opsional generate BAP.
     */
    public function returnAsset(Request $request, AssetAssignment $assignment)
    {
        $validated = $request->validate([
            'returned_at' => 'required|date',
            'condition_on_return' => 'nullable|integer|min:0|max:100',
            'hostname' => 'nullable|string|max:100',
            'notes' => 'nullable|string',

            // 🆕 Data Berita Acara Pengembalian
            'buat_berita_acara' => 'nullable|boolean',
            'pihak_pertama_id' => 'required_if:buat_berita_acara,1|nullable|exists:users,id',
            'pihak_pertama_jabatan' => 'nullable|string|max:100',
            'pihak_pertama_nip' => 'nullable|string|max:30',
            'tempat_ba' => 'nullable|string|max:100',
        ]);

        // Cegah double return
        if ($assignment->returned_at) {
            return back()->with('error', 'Aset ini sudah pernah dikembalikan.');
        }

        // Validasi kondisional: pihak pertama harus admin/support
        if ($request->boolean('buat_berita_acara') && !empty($validated['pihak_pertama_id'])) {
            $pihakPertama = User::find($validated['pihak_pertama_id']);
            if (!$pihakPertama || !in_array($pihakPertama->role, ['admin', 'support'])) {
                return back()->withInput()
                    ->with('error', 'Pihak Pertama harus Admin atau Support.');
            }
        }

        $ba = DB::transaction(function () use ($request, $validated, $assignment) {
            // 1. Update assignment
            $assignment->update([
                'returned_at' => $validated['returned_at'],
                'condition_on_return' => $validated['condition_on_return'] ?? null,
                'notes' => $validated['notes'] ?? $assignment->notes,
            ]);

            // 2. Update asset jadi available
            $assetUpdateData = [
                'status' => 'available',
                'current_user_id' => null,
                'current_location_id' => null,
            ];
            if ($request->filled('hostname')) {
                $assetUpdateData['hostname'] = $request->hostname;
            }
            $assignment->asset->update($assetUpdateData);

            // 3. 🆕 Generate BAP
            $ba = null;
            if ($request->boolean('buat_berita_acara') && !empty($validated['pihak_pertama_id'])) {
                $pihakPertama = User::find($validated['pihak_pertama_id']);
                $ba = $this->baService->createPengembalian(
                    $assignment->fresh(['asset.category', 'user']),
                    $pihakPertama,
                    [
                        'pihak_pertama_jabatan' => $validated['pihak_pertama_jabatan'] ?? null,
                        'pihak_pertama_nip' => $validated['pihak_pertama_nip'] ?? null,
                        'tempat_ba' => $validated['tempat_ba'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                    ]
                );
            }

            return $ba;
        });

        $message = 'Aset berhasil dikembalikan.';
        if ($ba) {
            $message .= " Berita Acara Pengembalian {$ba->nomor_ba} berhasil dibuat.";
        }

        return redirect()
            ->route('siam.assets.show', $assignment->asset_id)
            ->with('success', $message);
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
