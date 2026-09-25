<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::withCount('assetOwnerships');

        if ($request->filled('type'))
            $query->where('type', $request->type);
        if ($request->filled('search'))
            $query->where('name', 'like', '%' . $request->search . '%');

        $vendors = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:sewa,pembelian,both',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Vendor::create($validated);

        return redirect()->route('siam.vendors.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:sewa,pembelian,both',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        return redirect()->route('siam.vendors.index')
            ->with('success', 'Vendor berhasil diupdate.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('siam.vendors.index')
            ->with('success', 'Vendor dihapus.');
    }
}
