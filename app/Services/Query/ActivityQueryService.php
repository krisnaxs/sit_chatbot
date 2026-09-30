<?php

namespace App\Services\Query;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower(trim($pesan));

        if (strlen($lower) < 5) {
            return null;
        }

        // "berapa log activity / aktivitas"
        if (
            $this->matchAny($lower, ['log', 'activity', 'aktivitas', 'aktifitas']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total'])
        ) {
            return $this->countActivities($lower);
        }

        // "log aktivitas hari ini / kemarin / minggu ini"
        if (
            $this->matchAny($lower, ['log', 'activity', 'aktivitas', 'aktifitas']) &&
            $this->matchAny($lower, ['hari ini', 'today', 'kemarin', 'minggu ini', 'bulan ini'])
        ) {
            return $this->recentActivities($lower);
        }

        // "siapa yang login"
        if (
            $this->matchAny($lower, ['login', 'masuk']) &&
            $this->matchAny($lower, ['siapa', 'terakhir', 'terbaru'])
        ) {
            return $this->recentLogins();
        }

        // "aktivitas user [nama]"
        if (
            $this->matchAny($lower, ['aktivitas', 'activity', 'log', 'perbuatan']) &&
            $this->matchAny($lower, ['user', 'oleh', 'dari'])
        ) {
            return $this->activitiesByUser($lower);
        }

        // "log hapus / tambah / update"
        if (
            $this->matchAny($lower, ['log', 'activity', 'aktivitas']) &&
            $this->matchAny($lower, ['hapus', 'tambah', 'update', 'ubah', 'edit'])
        ) {
            return $this->activitiesByAction($lower);
        }

        return null;
    }

    private function countActivities(string $lower): array
    {
        $total = Activity::count();
        $today = Activity::whereDate('created_at', today())->count();
        $week = Activity::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        $jawaban = "📊 Statistik Activity Log\n\n"
            . "• Total: {$total}\n"
            . "• Hari ini: {$today}\n"
            . "• Minggu ini: {$week}";

        return [$jawaban, 'database'];
    }

    private function recentActivities(string $lower): array
    {
        $q = Activity::with('causer')->latest();

        if (str_contains($lower, 'hari ini') || str_contains($lower, 'today')) {
            $q->whereDate('created_at', today());
            $label = 'hari ini';
        } elseif (str_contains($lower, 'kemarin')) {
            $q->whereDate('created_at', now()->subDay());
            $label = 'kemarin';
        } elseif (str_contains($lower, 'minggu ini')) {
            $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $label = 'minggu ini';
        } elseif (str_contains($lower, 'bulan ini')) {
            $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $label = 'bulan ini';
        } else {
            $label = 'terbaru';
        }

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada log aktivitas {$label}.", 'database'];
        }

        $jawaban = "📋 {$total} Log Aktivitas ({$label})\n\n";
        foreach ($items as $a) {
            $causer = $a->causer?->name ?? 'System';
            $time = $a->created_at?->diffForHumans() ?? '-';
            $jawaban .= "• {$causer} — {$a->description} ({$time})\n";
        }

        if ($total > 10) {
            $jawaban .= "\n_Menampilkan 10 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    private function recentLogins(): array
    {
        $logs = Activity::where('log_name', 'auth')
            ->where('description', 'like', '%Login berhasil%')
            ->with('causer')
            ->latest()
            ->limit(10)
            ->get();

        if ($logs->isEmpty()) {
            return ["Belum ada data login.", 'database'];
        }

        $jawaban = "🔐 Login Terakhir\n\n";
        foreach ($logs as $a) {
            $causer = $a->causer?->name ?? 'Unknown';
            $time = $a->created_at?->diffForHumans() ?? '-';
            $ip = $a->properties['ip'] ?? '-';
            $jawaban .= "• {$causer} — {$time} (IP: {$ip})\n";
        }

        return [$jawaban, 'database'];
    }

    private function activitiesByUser(string $lower): array
    {
        // Extract nama user
        $nama = null;
        if (preg_match('/(?:user|oleh|dari)\s+([a-z]+(?:\s+[a-z]+)?)/i', $lower, $m)) {
            $nama = trim($m[1]);
        }

        if (!$nama) {
            return ["Sebutkan nama user. Contoh: \"aktivitas user Budi\".", 'database'];
        }

        $user = User::where('name', 'like', "%{$nama}%")->first();
        if (!$user) {
            return ["User {$nama} tidak ditemukan.", 'database'];
        }

        $q = Activity::where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->latest();

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["User {$user->name} belum punya log aktivitas.", 'database'];
        }

        $jawaban = "📋 Aktivitas {$user->name} ({$total} log)\n\n";
        foreach ($items as $a) {
            $time = $a->created_at?->diffForHumans() ?? '-';
            $jawaban .= "• {$a->description} ({$time})\n";
        }

        if ($total > 10) {
            $jawaban .= "\n_Menampilkan 10 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    private function activitiesByAction(string $lower): array
    {
        $action = null;
        if (str_contains($lower, 'hapus') || str_contains($lower, 'delete')) {
            $action = 'hapus';
        } elseif (str_contains($lower, 'tambah') || str_contains($lower, 'create')) {
            $action = 'tambah';
        } elseif (str_contains($lower, 'update') || str_contains($lower, 'ubah') || str_contains($lower, 'edit')) {
            $action = 'update';
        }

        if (!$action) {
            return ["Sebutkan aksi: hapus, tambah, atau update.", 'database'];
        }

        $q = Activity::where('description', 'like', "%{$action}%")
            ->with('causer')
            ->latest();

        $total = $q->count();
        $items = (clone $q)->limit(10)->get();

        if ($items->isEmpty()) {
            return ["Tidak ada log dengan aksi {$action}.", 'database'];
        }

        $jawaban = "📋 Log Aksi {$action} ({$total} log)\n\n";
        foreach ($items as $a) {
            $causer = $a->causer?->name ?? 'System';
            $time = $a->created_at?->diffForHumans() ?? '-';
            $jawaban .= "• {$causer} — {$a->description} ({$time})\n";
        }

        if ($total > 10) {
            $jawaban .= "\n_Menampilkan 10 pertama dari {$total}._";
        }

        return [$jawaban, 'database'];
    }

    private function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n))
                return true;
        }
        return false;
    }
}
