<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')
            ->orderBy('name')
            ->paginate(25);

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('siam.departments.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('siam.departments.index')
            ->with('success', 'Departemen berhasil diupdate.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Departemen tidak bisa dihapus, masih ada user.');
        }

        $department->delete();

        return redirect()->route('siam.departments.index')
            ->with('success', 'Departemen dihapus.');
    }
}
