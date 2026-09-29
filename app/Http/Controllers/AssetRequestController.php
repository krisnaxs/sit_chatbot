<?php
// app/Http/Controllers/AssetRequestController.php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetRequest;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetRequestController extends Controller
{
    // ═══════════════════════════════════════════
    //  USER SIDE — pengajuan
    // ═══════════════════════════════════════════

    /**
     * Daftar pengajuan milik user sendiri.
     */
    public function myRequests(Request $request)
    {
        $query = AssetRequest::with(['asset', 'consumable', 'approvedBy', 'location'])
            ->forUser(auth()->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        $requests = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => AssetRequest::forUser(auth()->id())->pending()->count(),
            'approved' => AssetRequest::forUser(auth()->id())->approved()->count(),
            'rejected' => AssetRequest::forUser(auth()->id())->rejected()->count(),
        ];

        return view('requests.my', compact('requests', 'summary'));
    }

    /**
     * Form pengajuan baru.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', AssetRequest::TYPE_LOAN);   // 🆕 default: loan

        $assets = Asset::whereIn('status', ['available', 'in_use'])
            ->orderBy('serial_number')
            ->get();

        $consumables = Consumable::where('stock_available', '>', 0)
            ->orderBy('name')
            ->get();

        $locations = Location::active()->orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('requests.create', compact(
            'type',
            'assets',
            'consumables',
            'locations',
            'departments'
        ));
    }

    /**
     * Simpan pengajuan baru (status: pending).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:loan,consumable',   // 🆕 hapus 'assignment'

            // Loan
            'asset_id' => 'required_if:type,loan|nullable|exists:assets,id',

            // Consumable
            'consumable_id' => 'required_if:type,consumable|nullable|exists:consumables,id',
            'quantity' => 'required_if:type,consumable|nullable|integer|min:1',

            // Loan
            'due_date' => 'nullable|date|after_or_equal:today',

            // Umum
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'nullable|date|after_or_equal:today',
        ], [
            'type.required' => 'Jenis pengajuan wajib dipilih.',
            'type.in' => 'Jenis pengajuan tidak valid.',
            'asset_id.required_if' => 'Aset wajib dipilih.',
            'consumable_id.required_if' => 'Konsumable wajib dipilih.',
            'quantity.required_if' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'purpose.required' => 'Alasan pengajuan wajib diisi.',
            'purpose.max' => 'Alasan maksimal 500 karakter.',
            'due_date.after_or_equal' => 'Tanggal kembali tidak boleh sebelum hari ini.',
            'needed_date.after_or_equal' => 'Tanggal dibutuhkan tidak boleh sebelum hari ini.',
        ]);

        // Validasi stok consumable
        if ($validated['type'] === AssetRequest::TYPE_CONSUMABLE) {
            $consumable = Consumable::find($validated['consumable_id']);
            if ($consumable->stock_available < $validated['quantity']) {
                return back()->withInput()
                    ->with('error', "Stok tidak cukup. Tersedia: {$consumable->stock_available} {$consumable->unit}.");
            }
        }

        $validated['request_number'] = AssetRequest::generateNumber();
        $validated['user_id'] = auth()->id();
        $validated['status'] = AssetRequest::STATUS_PENDING;

        $assetRequest = AssetRequest::create($validated);

        return redirect()
            ->route('requests.my')
            ->with('success', "Pengajuan {$assetRequest->request_number} berhasil dikirim. Menunggu persetujuan admin.");
    }

    /**
     * Detail pengajuan.
     * - Admin/support: bisa lihat semua
     * - User: hanya miliknya sendiri
     */
    public function show(AssetRequest $assetRequest)
    {
        $isStaff = auth()->user()->hasAnyRole(['admin', 'support']);
        $isOwner = $assetRequest->user_id === auth()->id();

        if (!$isStaff && !$isOwner) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $assetRequest->load([
            'user.department',
            'asset.category',
            'consumable.category',
            'location',
            'department',
            'approvedBy',
            'loan',
            'transaction',
        ]);

        return view('requests.show', compact('assetRequest'));
    }

    /**
     * User batalkan pengajuan yang masih pending.
     */
    public function cancel(AssetRequest $assetRequest)
    {
        if ($assetRequest->user_id !== auth()->id()) {
            abort(403);
        }
        if (!$assetRequest->isPending()) {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu bisa dibatalkan.');
        }

        $assetRequest->update(['status' => AssetRequest::STATUS_CANCELLED]);

        return redirect()
            ->route('requests.my')
            ->with('success', 'Pengajuan dibatalkan.');
    }

    // ═══════════════════════════════════════════
    //  ADMIN/SUPPORT SIDE — approval
    // ═══════════════════════════════════════════

    /**
     * Daftar semua pengajuan untuk admin/support.
     */
    public function index(Request $request)
    {
        $query = AssetRequest::with([
            'user.department',
            'asset',
            'consumable',
            'approvedBy',
            'location',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->orderByPriority()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'pending' => AssetRequest::pending()->count(),
            'approved' => AssetRequest::approved()->count(),
            'rejected' => AssetRequest::rejected()->count(),
            'pending_loan' => AssetRequest::pending()->ofType(AssetRequest::TYPE_LOAN)->count(),
            'pending_consumable' => AssetRequest::pending()->ofType(AssetRequest::TYPE_CONSUMABLE)->count(),
        ];

        $users = User::active()->orderBy('name')->get();

        return view('requests.index', compact('requests', 'stats', 'users'));
    }

    /**
     * Admin approve pengajuan → auto-execute sesuai type.
     */
    public function approve(Request $request, AssetRequest $assetRequest)
    {
        // Role check
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        // Cek status
        if (!$assetRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        // 🆕 Opsional: cegah self-approval (admin approve pengajuannya sendiri)
        // Kalau mau mengizinkan, comment baris di bawah ini.
        if ($assetRequest->user_id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menyetujui pengajuan Anda sendiri.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::transaction(function () use ($assetRequest, $validated) {
                $assetRequest->update([
                    'status' => AssetRequest::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_notes' => $validated['admin_notes'] ?? null,
                ]);

                match ($assetRequest->type) {
                    AssetRequest::TYPE_LOAN => $this->executeLoan($assetRequest, $validated),
                    AssetRequest::TYPE_CONSUMABLE => $this->executeConsumable($assetRequest),
                    default => throw new \Exception("Tipe pengajuan tidak dikenal: {$assetRequest->type}"),
                };
            });

            return redirect()
                ->route('requests.show', $assetRequest)
                ->with('success', 'Pengajuan disetujui & dieksekusi.');

        } catch (\Exception $e) {
            Log::error('Approve request failed', [
                'request_id' => $assetRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Admin reject pengajuan.
     */
    public function reject(Request $request, AssetRequest $assetRequest)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }
        if (!$assetRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $assetRequest->update([
            'status' => AssetRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('requests.show', $assetRequest)
            ->with('success', 'Pengajuan ditolak.');
    }

    // ═══════════════════════════════════════════
    //  EXECUTORS (private)
    // ═══════════════════════════════════════════

    /**
     * Eksekusi peminjaman aset.
     */
    private function executeLoan(AssetRequest $req, array $data): void
    {
        $asset = Asset::findOrFail($req->asset_id);

        $loan = AssetLoan::create([
            'asset_id' => $asset->id,
            'user_id' => $req->user_id,
            'loan_date' => now(),
            'due_date' => $data['due_date'] ?? $req->due_date ?? now()->addDays(3),
            'purpose' => $req->purpose,
            'condition_on_loan' => $asset->condition_percent ?? 100,
            'status' => 'borrowed',
            'approved_by' => auth()->id(),
        ]);

        $asset->update([
            'status' => 'loaned',
            'current_user_id' => $req->user_id,
        ]);

        $req->update(['loan_id' => $loan->id]);
    }

    /**
     * Eksekusi permintaan konsumable.
     */
    private function executeConsumable(AssetRequest $req): void
    {
        $consumable = Consumable::findOrFail($req->consumable_id);

        if ($consumable->stock_available < $req->quantity) {
            throw new \Exception("Stok tidak cukup. Tersedia: {$consumable->stock_available}, diminta: {$req->quantity}.");
        }

        $transaction = ConsumableTransaction::create([
            'consumable_id' => $consumable->id,
            'user_id' => $req->user_id,
            'type' => 'out',
            'quantity' => $req->quantity,
            'transaction_date' => now(),
            'location_id' => $req->location_id,
            'purpose' => $req->purpose,
            'notes' => $req->admin_notes,
            'requested_by' => $req->user_id,
            'approved_by' => auth()->id(),
        ]);

        $consumable->decrement('stock_available', $req->quantity);

        $req->update(['transaction_id' => $transaction->id]);
    }
}
