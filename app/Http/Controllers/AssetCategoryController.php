<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::withCount('assets', 'consumables')
            ->orderBy('name')
            ->paginate(25);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:asset_categories,code',
            'is_consumable' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        AssetCategory::create($validated);

        return redirect()->route('siam.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(AssetCategory $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, AssetCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:asset_categories,code,' . $category->id,
            'is_consumable' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('siam.categories.index')
            ->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(AssetCategory $category)
    {
        if ($category->assets()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai aset.');
        }

        $category->delete();

        return redirect()->route('siam.categories.index')
            ->with('success', 'Kategori dihapus.');
    }
}
