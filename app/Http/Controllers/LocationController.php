<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // filter: aktif / nonaktif

        $locations = Location::withCount('users', 'currentAssets')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('building', 'like', "%{$search}%")
                        ->orWhere('floor', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhere('division', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('building')
            ->orderBy('floor')
            ->paginate(25)
            ->withQueryString();

        return view('locations.index', compact('locations', 'search', 'status'));
    }

    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('locations.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'building' => 'required|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room' => 'required|string|max:100',
            'division' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Location::create($validated);

        return redirect()->route('siam.locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
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
