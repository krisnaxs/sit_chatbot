<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ConsumableTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsumableTransactionController extends Controller
{
    /**
     * Daftar transaksi konsumable.
     */
    public function index(Request $request)
    {
        $query = ConsumableTransaction::with([
            'consumable',
            'user',
            'location',
            'asset',
            'requestedBy',
            'approvedBy'
        ]);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('consumable_id')) {
            $query->where('consumable_id', $request->consumable_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->paginate(25)
            ->withQueryString();

        $consumables = Consumable::orderBy('name')->get();
        $users = User::active()->orderBy('name')->get();

        $stats = [
            'total_in' => ConsumableTransaction::in()->sum('quantity'),
            'total_out' => ConsumableTransaction::out()->sum('quantity'),
            'total_return' => ConsumableTransaction::return()->sum('quantity'),
        ];

        return view('consumable-transactions.index', compact(
            'transactions',
            'consumables',
            'users',
            'stats'
        ));
    }

    /**
     * Form tambah transaksi.
     */
    public function create(Request $request)
    {
        // 🆕 Pre-load dengan total_out & total_return
        $consumable = $request->filled('consumable_id')
            ? Consumable::withSum(['transactions as total_out' => fn($q) => $q->where('type', 'out')], 'quantity')
                ->withSum(['transactions as total_return' => fn($q) => $q->where('type', 'return')], 'quantity')
                ->find($request->consumable_id)
            : null;

        // 🆕 Type default dari query string
        $defaultType = $request->get('type', 'out');

        $consumables = Consumable::orderBy('name')->get();
        $users = User::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();
        $assets = Asset::orderBy('serial_number')->get();

        return view('consumable-transactions.create', compact(
            'consumable',
            'consumables',
            'users',
            'locations',
            'assets',
            'defaultType'   // 🆕
        ));
    }

    /**
     * Simpan transaksi baru + update stok konsumable.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consumable_id' => 'required|exists:consumables,id',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:in,out,return',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'asset_id' => 'nullable|exists:assets,id',
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $consumable = Consumable::findOrFail($validated['consumable_id']);

        // Validasi stok untuk transaksi 'out'
        if ($validated['type'] === 'out') {
            if ($consumable->stock_available < $validated['quantity']) {
                return back()
                    ->withInput()
                    ->with('error', "Stok tidak cukup. Tersedia: {$consumable->stock_available} {$consumable->unit}.");
            }
        }

        // Validasi untuk 'return'
        if ($validated['type'] === 'return') {
            $maxReturn = $consumable->stock_total - $consumable->stock_available;
            if ($validated['quantity'] > $maxReturn) {
                return back()
                    ->withInput()
                    ->with('error', "Jumlah return melebihi yang pernah keluar. Maks: {$maxReturn}.");
            }
        }

        $validated['requested_by'] = auth()->id();
        $validated['approved_by'] = auth()->id();

        DB::transaction(function () use ($validated, $consumable) {
            // Simpan transaksi
            ConsumableTransaction::create($validated);

            // Update stok sesuai tipe transaksi
            switch ($validated['type']) {
                case 'in':
                    // Barang masuk: total & available naik
                    $consumable->increment('stock_total', $validated['quantity']);
                    $consumable->increment('stock_available', $validated['quantity']);
                    break;

                case 'out':
                    // Barang keluar: available turun (total tetap)
                    $consumable->decrement('stock_available', $validated['quantity']);
                    break;

                case 'return':
                    // Barang kembali: available naik (total tetap)
                    $consumable->increment('stock_available', $validated['quantity']);
                    break;
            }
        });

        return redirect()
            ->route('siam.consumable-transactions.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Detail transaksi.
     */
    public function show(ConsumableTransaction $transaction)
    {
        $transaction->load([
            'consumable',
            'user',
            'location',
            'asset',
            'requestedBy',
            'approvedBy'
        ]);

        return view('consumable-transactions.show', compact('transaction'));
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'type',
            'consumable_id',
            'user_id',
            'date_from',
            'date_to',
        ]);

        $filename = 'transaksi-konsumable-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new ConsumableTransactionsExport($filters), $filename);
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $query = ConsumableTransaction::with([
            'consumable',
            'user',
            'location',
            'asset',
            'requestedBy',
            'approvedBy',
        ]);

        // Terapkan filter sama seperti index()
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('consumable_id')) {
            $query->where('consumable_id', $request->consumable_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }

        $transactions = $query->orderByDesc('transaction_date')->get();

        $summary = [
            'total' => $transactions->count(),
            'total_in' => $transactions->where('type', 'in')->sum('quantity'),
            'total_out' => $transactions->where('type', 'out')->sum('quantity'),
            'total_return' => $transactions->where('type', 'return')->sum('quantity'),
        ];

        $pdf = Pdf::loadView('consumable-transactions.pdf', compact('transactions', 'summary'))
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('transaksi-konsumable-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Hapus transaksi + rollback stok.
     */
    public function destroy(ConsumableTransaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $consumable = $transaction->consumable;

            // Rollback stok (kebalikan dari store)
            switch ($transaction->type) {
                case 'in':
                    // Batalkan barang masuk
                    $consumable->decrement('stock_total', $transaction->quantity);
                    $consumable->decrement('stock_available', $transaction->quantity);
                    break;

                case 'out':
                    // Batalkan barang keluar → kembalikan ke available
                    $consumable->increment('stock_available', $transaction->quantity);
                    break;

                case 'return':
                    // Batalkan return → kurangi available
                    $consumable->decrement('stock_available', $transaction->quantity);
                    break;
            }

            $transaction->delete();
        });

        return redirect()
            ->route('siam.consumable-transactions.index')
            ->with('success', 'Transaksi dihapus & stok dikembalikan.');
    }
}
