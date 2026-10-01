<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickRequestController extends Controller
{
    /**
     * Tampilkan form pengajuan quick.
     * Kalau belum login → tetap render view, view yang handle modal login.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', AssetRequest::TYPE_LOAN);

        $assets = Asset::whereIn('status', ['available', 'in_use'])
            ->orderBy('serial_number')
            ->get();

        $consumables = Consumable::where('stock_available', '>', 0)
            ->orderBy('name')
            ->get();

        $locations = Location::active()->orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('requests.quick', compact(
            'type',
            'assets',
            'consumables',
            'locations',
            'departments'
        ));
    }

    /**
     * Simpan pengajuan (wajib login).
     */
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect()
                ->route('requests.quick')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        $validated = $request->validate([
            'type' => 'required|in:loan,consumable',
            'asset_id' => 'required_if:type,loan|nullable|exists:assets,id',
            'consumable_id' => 'required_if:type,consumable|nullable|exists:consumables,id',
            'quantity' => 'required_if:type,consumable|nullable|integer|min:1',
            'due_date' => 'nullable|date|after_or_equal:today',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'nullable|date|after_or_equal:today',
        ], [
            'purpose.required' => 'Alasan pengajuan wajib diisi.',
            'asset_id.required_if' => 'Aset wajib dipilih.',
            'consumable_id.required_if' => 'Konsumable wajib dipilih.',
            'quantity.min' => 'Jumlah minimal 1.',
        ]);
        if ($validated['type'] === AssetRequest::TYPE_CONSUMABLE) {
            $consumable = Consumable::find($validated['consumable_id']);
            if ($consumable->stock_available < $validated['quantity']) {
                return back()->withInput()
                    ->with('error', "Stok tidak cukup. Tersedia: {$consumable->stock_available} {$consumable->unit}.");
            }
        }

        $assetRequest = DB::transaction(function () use ($validated) {
            $validated['request_number'] = AssetRequest::generateNumber();
            $validated['user_id'] = auth()->id();
            $validated['status'] = AssetRequest::STATUS_PENDING;

            return AssetRequest::create($validated);
        });
        return redirect()
            ->route('requests.quick.receipt', $assetRequest)
            ->with('success', 'Pengajuan berhasil dikirim!');
    }

    /**
     * Halaman surat pengajuan (receipt).
     * User hanya bisa lihat miliknya sendiri.
     */
    public function receipt(AssetRequest $assetRequest)
    {
        if (!auth()->check() || $assetRequest->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $assetRequest->load([
            'user.department',
            'asset.category',
            'consumable.category',
            'location',
            'department',
        ]);

        return view('requests.quick-receipt', compact('assetRequest'));
    }
}
