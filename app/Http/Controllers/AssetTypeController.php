<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetType::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('brand', 'like', '%' . $request->search . '%')
                    ->orWhere('model', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        $assetTypes = $query->orderBy('brand')->orderBy('model')->paginate(25)->withQueryString();
        $brands = AssetType::select('brand')->distinct()->orderBy('brand')->pluck('brand');

        return view('asset-types.index', compact('assetTypes', 'brands'));
    }

    public function create()
    {
        $brands = AssetType::select('brand')->distinct()->orderBy('brand')->pluck('brand');
        return view('asset-types.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $exists = AssetType::where('brand', $validated['brand'])
            ->where('model', $validated['model'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', "Brand {$validated['brand']} dengan model {$validated['model']} sudah ada.");
        }

        AssetType::create($validated);

        return redirect()->route('siam.asset-types.index')
            ->with('success', 'Brand & model berhasil ditambahkan.');
    }

    public function edit(AssetType $assetType)
    {
        $brands = AssetType::select('brand')->distinct()->orderBy('brand')->pluck('brand');
        return view('asset-types.edit', compact('assetType', 'brands'));
    }

    public function update(Request $request, AssetType $assetType)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $exists = AssetType::where('brand', $validated['brand'])
            ->where('model', $validated['model'])
            ->where('id', '!=', $assetType->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', "Brand {$validated['brand']} dengan model {$validated['model']} sudah ada.");
        }

        $assetType->update($validated);

        return redirect()->route('siam.asset-types.index')
            ->with('success', 'Brand & model berhasil diupdate.');
    }

    public function destroy(AssetType $assetType)
    {
        $assetType->delete();

        return redirect()->route('siam.asset-types.index')
            ->with('success', 'Brand & model dihapus.');
    }
}
