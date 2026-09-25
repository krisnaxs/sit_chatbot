<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\Consumable;
use Illuminate\Http\Request;

class ConsumableController extends Controller
{
    /**
     * Daftar konsumable.
     */
    public function index(Request $request)
    {
        $query = Consumable::with('category');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('brand', 'like', '%' . $request->search . '%')
                    ->orWhere('model', 'like', '%' . $request->search . '%');
            });
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter low stock
        if ($request->filled('low_stock')) {
            $query->lowStock();
        }

        $consumables = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = AssetCategory::consumable()->orderBy('name')->get();

        // Statistik
        $stats = [
            'total' => Consumable::count(),
            'low_stock' => Consumable::lowStock()->count(),
            'out_stock' => Consumable::where('stock_available', 0)->count(),
        ];

        return view('consumables.index', compact('consumables', 'categories', 'stats'));
    }

    /**
     * Form tambah konsumable.
     */
    public function create()
    {
        $categories = AssetCategory::consumable()->orderBy('name')->get();

        return view('consumables.create', compact('categories'));
    }

    /**
     * Simpan konsumable baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:asset_categories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'stock_total' => 'required|integer|min:0',
            'stock_available' => 'required|integer|min:0|lte:stock_total',
            'stock_minimum' => 'required|integer|min:0',
            'last_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'stock_available.lte' => 'Stok tersedia tidak boleh lebih dari stok total.',
        ]);

        Consumable::create($validated);

        return redirect()
            ->route('siam.consumables.index')
            ->with('success', 'Konsumable berhasil ditambahkan.');
    }

    /**
     * Detail konsumable + history transaksi.
     */
    public function show(Consumable $consumable)
    {
        $consumable->load([
            'category',
            'transactions.user',
            'transactions.location',
            'transactions.asset',
            'transactions.requestedBy',
            'transactions.approvedBy',
        ]);

        return view('consumables.show', compact('consumable'));
    }

    /**
     * Form edit konsumable.
     */
    public function edit(Consumable $consumable)
    {
        $categories = AssetCategory::consumable()->orderBy('name')->get();

        return view('consumables.edit', compact('consumable', 'categories'));
    }

    /**
     * Update konsumable.
     */
    public function update(Request $request, Consumable $consumable)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:asset_categories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'stock_total' => 'required|integer|min:0',
            'stock_available' => 'required|integer|min:0|lte:stock_total',
            'stock_minimum' => 'required|integer|min:0',
            'last_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'stock_available.lte' => 'Stok tersedia tidak boleh lebih dari stok total.',
        ]);

        $consumable->update($validated);

        return redirect()
            ->route('siam.consumables.index')
            ->with('success', 'Konsumable berhasil diupdate.');
    }

    /**
     * Hapus konsumable.
     */
    public function destroy(Consumable $consumable)
    {
        // Cek apakah ada transaksi
        if ($consumable->transactions()->count() > 0) {
            return back()->with('error', 'Konsumable tidak bisa dihapus karena ada history transaksi.');
        }

        $consumable->delete();

        return redirect()
            ->route('siam.consumables.index')
            ->with('success', 'Konsumable berhasil dihapus.');
    }
}
