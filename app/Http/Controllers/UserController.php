<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar user.
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'location']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        return view('users.index', compact('users', 'departments'));
    }

    /**
     * Form tambah user.
     */
    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();

        return view('users.create', compact('departments', 'locations'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nip' => ['nullable', 'string', 'max:30', 'unique:users,nip'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'role' => ['required', Rule::in(['admin', 'support', 'user'])],
            'is_active' => ['nullable'],
        ], [
            'nip.unique' => 'NIP sudah terdaftar.',
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'department_id.exists' => 'Departemen tidak valid.',
            'location_id.exists' => 'Lokasi tidak valid.',
        ]);

        // Auto-generate username dari email
        $username = User::generateUsername($data['email']);

        User::create([
            'nip' => $data['nip'] ?? null,
            'username' => $username,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'position' => $data['position'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'role' => $data['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', "User berhasil ditambahkan. Username: {$username}");
    }

    /**
     * Detail user + aset yang dipegang.
     */
    public function show(User $user)
    {
        $user->load([
            'department',
            'location',
            'currentAssets.category',
            'currentAssets.currentLocation',
            'assetAssignments.asset',
            'activeLoans.asset',
            'consumableTransactions.consumable',
        ]);

        return view('users.show', compact('user'));
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $departments = Department::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('full_name')->get();

        return view('users.edit', compact('user', 'departments', 'locations'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nip' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'nip')->ignore($user->id)
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'role' => ['required', Rule::in(['admin', 'support', 'user'])],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah dipakai user lain.',
            'nip.unique' => 'NIP sudah dipakai user lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->nip = $data['nip'] ?? null;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->department_id = $data['department_id'] ?? null;
        $user->position = $data['position'] ?? null;
        $user->location_id = $data['location_id'] ?? null;
        $user->role = $data['role'];
        $user->is_active = $request->has('is_active');

        // Update password hanya kalau diisi
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Catatan: username TIDAK diupdate otomatis.
        // Kalau mau regenerate saat email berubah, atur di Model User (event updating).
        // Atau aktifkan baris di bawah ini:
        // if ($user->isDirty('email')) {
        //     $user->username = User::generateUsername($user->email, $user->id);
        // }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        // Cegah admin hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
