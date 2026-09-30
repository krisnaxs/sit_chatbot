<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\User;
use Illuminate\Http\Request;

class AssetLoanController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetLoan::with(['asset', 'user', 'approvedBy', 'assetRequest']);

        // Filter existing
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('asset_id'))
            $query->where('asset_id', $request->asset_id);
        if ($request->filled('user_id'))
            $query->where('user_id', $request->user_id);

        // 🆕 Filter origin
        if ($request->filled('origin')) {
            if ($request->origin === 'request') {
                $query->fromRequest();
            } elseif ($request->origin === 'manual') {
                $query->manual();
            }
        }

        // 🆕 Filter search — cari di multiple kolom
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Cari di nomor pengajuan (via relasi)
                $q->whereHas('assetRequest', function ($qa) use ($search) {
                    $qa->where('request_number', 'like', "%{$search}%");
                })
                    // Cari di SN aset
                    ->orWhereHas('asset', function ($qa) use ($search) {
                        $qa->where('serial_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    // Cari di nama peminjam
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    })
                    // Cari di tujuan
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $loans = $query->orderByDesc('loan_date')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $assets = Asset::orderBy('serial_number')->get();
        $users = User::active()->orderBy('name')->get();

        return view('loans.index', compact('loans', 'assets', 'users'));
    }

    public function create(Request $request)
    {
        $asset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        $assets = Asset::whereIn('status', ['available', 'loaned'])->orderBy('serial_number')->get();
        $users = User::active()->orderBy('name')->get();

        return view('loans.create', compact('asset', 'assets', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id' => 'required|exists:users,id',
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:loan_date',
            'purpose' => 'nullable|string|max:255',
            'condition_on_loan' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'borrowed';
        $validated['approved_by'] = auth()->id();

        $loan = AssetLoan::create($validated);
        $loan->asset->update(['status' => 'loaned']);

        return redirect()
            ->route('siam.loans.index')
            ->with('success', 'Peminjaman aset berhasil dicatat.');
    }

    public function show(AssetLoan $loan)
    {
        // 🆕 Load 'assetRequest' juga
        $loan->load(['asset', 'user', 'approvedBy', 'assetRequest']);

        return view('loans.show', compact('loan'));
    }

    public function returnAsset(Request $request, AssetLoan $loan)
    {
        $validated = $request->validate([
            'returned_at' => 'required|date',
            'condition_on_return' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'returned';
        $loan->update($validated);
        $loan->asset->update([
            'status' => 'available',
            'current_user_id' => null,
        ]);

        return redirect()
            ->route('siam.loans.index')
            ->with('success', 'Aset berhasil dikembalikan.');
    }

    public function destroy(AssetLoan $loan)
    {
        $loan->delete();

        return redirect()
            ->route('siam.loans.index')
            ->with('success', 'Data peminjaman dihapus.');
    }
}
