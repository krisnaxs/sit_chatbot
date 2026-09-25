<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount('users', 'currentAssets')
            ->orderBy('building')
            ->orderBy('floor')
            ->paginate(25);

        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        // 🆕 Ambil daftar departemen aktif untuk dropdown Divisi
        $departments = Department::active()->orderBy('name')->get();

        return view('locations.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'building' => 'required|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room' => 'required|string|max:100',
            'division' => 'nullable|string|max:100',   // nilai dari dropdown (nama departemen)
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Location::create($validated);

        return redirect()->route('siam.locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        // 🆕 Ambil daftar departemen aktif untuk dropdown Divisi
        $departments = Department::active()->orderBy('name')->get();

        return view('locations.edit', compact('location', 'departments'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'building' => 'required|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room' => 'required|string|max:100',
            'division' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $location->update($validated);

        return redirect()->route('siam.locations.index')
            ->with('success', 'Lokasi berhasil diupdate.');
    }

    public function destroy(Location $location)
    {
        if ($location->currentAssets()->count() > 0) {
            return back()->with('error', 'Lokasi masih dipakai aset.');
        }

        $location->delete();

        return redirect()->route('siam.locations.index')
            ->with('success', 'Lokasi dihapus.');
    }
}
