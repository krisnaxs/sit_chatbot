<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLoan;
use App\Models\ConsumableTransaction;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
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

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status_pensiun')) {
            match ($request->status_pensiun) {
                'pensiun' => $query->pensiun(),
                'belum_pensiun' => $query->belumPensiun(),
                'akan_pensiun' => $query->akanPensiun(30),
                default => null,
            };
        }

        $users = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        // 🆕 Trash count (admin only)
        $trashCount = auth()->user()->isAdmin()
            ? User::onlyTrashed()->count()
            : 0;

        return view('users.index', compact('users', 'departments', 'trashCount'));
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
            'waktu_pensiun' => ['nullable', 'date'],
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
            'waktu_pensiun.date' => 'Format tanggal pensiun tidak valid.',
        ]);

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
            'waktu_pensiun' => $data['waktu_pensiun'] ?? null,
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
            'waktu_pensiun' => ['nullable', 'date'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah dipakai user lain.',
            'nip.unique' => 'NIP sudah dipakai user lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'waktu_pensiun.date' => 'Format tanggal pensiun tidak valid.',
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
        $user->waktu_pensiun = $data['waktu_pensiun'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Download template import user.
     */
    public function downloadTemplate()
    {
        $filename = 'template-import-user-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new \App\Exports\UsersTemplateExport(), $filename);
    }

    /**
     * Form import user.
     */
    public function importForm()
    {
        return view('users.import');
    }

    /**
     * Proses import user dari Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.mimes' => 'Format file harus: xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new \App\Imports\UsersImport();
            Excel::import($import, $request->file('file'));

            $success = $import->successCount;
            $skipped = $import->skipCount;
            $failed = count($import->failures());
            $msg = " Import selesai. Berhasil: {$success} user.";
            if ($skipped > 0) {
                $msg .= " Dilewati: {$skipped} (email sudah ada).";
            }
            if ($failed > 0) {
                $msg .= " Gagal: {$failed} baris (lihat detail di bawah).";
            }

            return redirect()
                ->route('users.index')
                ->with('success', $msg)
                ->with('import_failures', $import->failures());

        } catch (\Exception $e) {
            \Log::error('Import user error: ' . $e->getMessage());

            return redirect()
                ->route('users.import.form')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request, User $user)
    {
        $currentUser = auth()->user();

        $isSelf = $currentUser->id === $user->id;
        $isAdmin = $currentUser->isAdmin();

        if (!$isSelf && !$isAdmin) {
            abort(403, 'Anda tidak berwenang mengubah password user ini.');
        }
        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if ($isSelf && !$isAdmin) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules, [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak cocok.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Reset password user ke default (khusus admin).
     */
    public function resetPassword(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa reset password user.');
        }
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.show', $user)
                ->with('error', 'Gunakan menu Ganti Password untuk akun Anda sendiri.');
        }

        $defaultPassword = 'password123';

        $user->password = Hash::make($defaultPassword);
        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Password user {$user->name} berhasil direset menjadi: {$defaultPassword}");
    }

    /**
     * Hapus user (soft delete — bisa direstore).
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        // Soft delete — data tetap ada di DB, bisa direstore
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus. Data dapat direstore oleh admin bila diperlukan.');
    }

    // ============================================================
    // 🆕 TRASH & RESTORE
    // ============================================================

    /**
     * Daftar user yang di-soft-delete (Trash).
     */
    public function trash(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa akses sampah user.');
        }

        $query = User::onlyTrashed()->with(['department', 'location']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('username', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('deleted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('deleted_at', '<=', $request->date_to);
        }

        $users = $query->orderByDesc('deleted_at')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $stats = [
            'total' => User::onlyTrashed()->count(),
            'bulan_ini' => User::onlyTrashed()
                ->whereMonth('deleted_at', now()->month)
                ->whereYear('deleted_at', now()->year)
                ->count(),
        ];

        return view('users.trash', compact('users', 'stats'));
    }

    /**
     * Restore user dari trash.
     */
    public function restore($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa restore user.');
        }

        $user = User::onlyTrashed()->findOrFail($id);

        // Cek konflik: email atau NIP mungkin sudah dipakai user lain
        $emailConflict = User::where('email', $user->email)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailConflict) {
            return redirect()
                ->route('users.trash')
                ->with('error', "Email {$user->email} sudah dipakai user lain. Ganti email user aktif dulu sebelum restore.");
        }

        if ($user->nip) {
            $nipConflict = User::where('nip', $user->nip)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($nipConflict) {
                return redirect()
                    ->route('users.trash')
                    ->with('error', "NIP {$user->nip} sudah dipakai user lain. Ganti NIP user aktif dulu sebelum restore.");
            }
        }

        $user->restore();

        return redirect()
            ->route('users.trash')
            ->with('success', "User {$user->name} berhasil direstore.");
    }


    /**
     * Force delete user — DINONAKTIFKAN.
     *
     * User tidak bisa dihapus permanen karena banyak relasi (BA, assignment, loan, dll)
     * yang membutuhkan data user sebagai referensi historis.
     *
     * Gunakan restore atau biarkan di trash.
     */
    public function forceDelete($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa akses.');
        }

        $user = User::onlyTrashed()->findOrFail($id);

        return redirect()
            ->route('users.trash')
            ->with('error', "User {$user->name} tidak bisa dihapus permanen karena terkait data historis (berita acara, assignment, dll). " .
                "Data user tetap tersimpan di sampah untuk keperluan audit. Gunakan fitur Restore jika ingin mengaktifkan kembali.");
    }

    /**
     * Restore semua user yang soft-deleted.
     */
    public function restoreAll()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa restore user.');
        }

        $trashed = User::onlyTrashed()->get();
        $restored = 0;
        $skipped = [];

        foreach ($trashed as $user) {
            // Skip kalau email/NIP konflik
            $emailConflict = User::where('email', $user->email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailConflict) {
                $skipped[] = $user->name;
                continue;
            }

            $user->restore();
            $restored++;
        }

        $msg = "{$restored} user berhasil direstore.";
        if (!empty($skipped)) {
            $msg .= " Dilewati: " . count($skipped) . " user (email/NIP sudah dipakai).";
        }

        return redirect()
            ->route('users.trash')
            ->with('success', $msg);
    }

    /**
     * Kosongkan trash (hapus permanen semua).
     */
    /**
     * Kosongkan trash (hapus permanen semua user).
     * Skip user yang masih pegang aset.
     */
    /**
     * Kosongkan trash — DINONAKTIFKAN untuk user.
     */
    public function emptyTrash()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa akses.');
        }

        return redirect()
            ->route('users.trash')
            ->with('error', 'Trash user tidak bisa dikosongkan karena data user dibutuhkan untuk audit trail (berita acara, history aset, dll). ' .
                'User yang dihapus tetap tersimpan di sampah.');
    }
}
