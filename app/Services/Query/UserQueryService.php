<?php

namespace App\Services\Query;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Str;

class UserQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // 1. Jumlah user
        if (
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'pengguna']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total'])
        ) {
            return $this->countUsers($lower);
        }

        // 2. Daftar user aktif
        if (
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'pengguna']) &&
            $this->matchAny($lower, ['aktif', 'daftar', 'list', 'siapa'])
        ) {
            return $this->listActiveUsers();
        }

        // 3. Jumlah departemen
        if (
            $this->matchAny($lower, ['departemen', 'department', 'divisi', 'division']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total', 'daftar'])
        ) {
            return $this->countDepartments();
        }

        // 4. Info user tertentu by nama
        if (preg_match('/(?:user|pegawai|karyawan)\s+([a-z\s]+)/i', $pesan, $m)) {
            // Hanya match kalau ada kata kunci
            if ($this->matchAny($lower, ['siapa', 'cari', 'info', 'detail'])) {
                return $this->findUserByName(trim($m[1]));
            }
        }

        return null;
    }

    protected function countUsers(string $lower): array
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();
        $withAssets = User::whereHas('currentAssets')->count();

        return [
            "Total user: **{$total}**\n" .
            "• Aktif: **{$active}**\n" .
            "• Sedang pegang aset: **{$withAssets}**",
            'database'
        ];
    }

    protected function listActiveUsers(): array
    {
        $users = User::where('is_active', true)->orderBy('name')->limit(10)->get();

        if ($users->isEmpty()) {
            return ["Tidak ada user aktif.", 'database'];
        }

        $jawaban = "**" . $users->count() . " user aktif** (10 pertama):\n\n";
        foreach ($users as $u) {
            $jawaban .= "• {$u->name}";
            if ($u->position)
                $jawaban .= " — {$u->position}";
            if ($u->nip)
                $jawaban .= " (NIP: {$u->nip})";
            $jawaban .= "\n";
        }

        return [$jawaban, 'database'];
    }

    protected function countDepartments(): array
    {
        $total = Department::count();
        $active = Department::where('is_active', true)->count();

        return ["Ada **{$total} departemen** (**{$active} aktif**).", 'database'];
    }

    protected function findUserByName(string $nama): array
    {
        $user = User::with(['department', 'location', 'currentAssets'])
            ->where('name', 'like', "%{$nama}%")
            ->first();

        if (!$user) {
            return ["User dengan nama **{$nama}** tidak ditemukan.", 'database'];
        }

        $jawaban = "**{$user->name}**\n";
        $jawaban .= "• Email: {$user->email}\n";
        if ($user->nip)
            $jawaban .= "• NIP: {$user->nip}\n";
        if ($user->position)
            $jawaban .= "• Jabatan: {$user->position}\n";
        if ($user->department)
            $jawaban .= "• Departemen: {$user->department->name}\n";
        if ($user->location)
            $jawaban .= "• Lokasi: {$user->location->full_name}\n";
        $jawaban .= "• Status: " . ($user->is_active ? 'Aktif' : 'Nonaktif') . "\n";
        $jawaban .= "• Aset dipegang: **" . $user->currentAssets->count() . "** unit";

        return [$jawaban, 'database'];
    }

    protected function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n))
                return true;
        }
        return false;
    }
}
