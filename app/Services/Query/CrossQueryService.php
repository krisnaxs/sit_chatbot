<?php

namespace App\Services\Query;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLoan;
use App\Models\User;
use Illuminate\Support\Str;

class CrossQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower(trim($pesan));

        if (strlen($lower) < 5) {
            return null;
        }

        // ============================================================
        // 1. "SIAPA YANG PEGANG [ASET]?"
        // ============================================================
        if (
            $this->matchAny($lower, ['siapa']) &&
            $this->matchAny($lower, ['pegang', 'memegang', 'punya', 'memakai', 'menggunakan']) &&
            $this->matchAny($lower, ['sn', 'serial', 'hostname', 'laptop', 'aset'])
        ) {
            return $this->whoHoldsAsset($pesan);
        }

        // ============================================================
        // 2. "[USER] PEGANG ASET APA SAJA?"
        // ============================================================
        if (
            $this->matchAny($lower, ['aset apa', 'aset apa saja', 'pegang apa', 'punya apa']) &&
            $this->matchAny($lower, ['user', 'pegawai', 'karyawan', 'si ', 'pak ', 'bu ', 'mas ', 'mbak '])
        ) {
            return $this->userAssets($pesan);
        }

        return null;
    }

    private function whoHoldsAsset(string $pesan): array
    {
        // Extract SN atau hostname dari pesan
        $keyword = $this->extractAssetKeyword($pesan);

        if (!$keyword) {
            return ["Sebutkan SN, hostname, atau nama aset dengan jelas. Contoh: \"siapa yang pegang NB-T14-001?\"", 'database'];
        }

        $asset = Asset::with(['currentUser', 'currentLocation', 'category'])
            ->where('serial_number', 'like', "%{$keyword}%")
            ->orWhere('hostname', 'like', "%{$keyword}%")
            ->orWhere('model', 'like', "%{$keyword}%")
            ->first();

        if (!$asset) {
            return ["Aset dengan kata kunci **{$keyword}** tidak ditemukan.", 'database'];
        }

        $jawaban = "🔍 **Info Aset {$asset->serial_number}**";
        if ($asset->hostname) {
            $jawaban .= " ({$asset->hostname})";
        }
        $jawaban .= "\n\n"
            . "• Brand/Model: {$asset->brand} {$asset->model}\n"
            . "• Kategori: " . ($asset->category?->name ?? '-') . "\n"
            . "• Status: {$asset->status}\n";

        if ($asset->currentUser) {
            $jawaban .= "• **Pemegang saat ini:** {$asset->currentUser->name}";
            if ($asset->currentUser->position) {
                $jawaban .= " ({$asset->currentUser->position})";
            }
            $jawaban .= "\n";
        } else {
            $jawaban .= "• **Pemegang saat ini:** tidak ada (aset tersedia)\n";
        }

        if ($asset->currentLocation) {
            $jawaban .= "• Lokasi: {$asset->currentLocation->full_name}";
        }

        return [$jawaban, 'database'];
    }

    private function userAssets(string $pesan): array
    {
        // Extract nama user (setelah "user", "pak", "bu", dll)
        $nama = $this->extractUserName($pesan);

        if (!$nama) {
            return ["Sebutkan nama user dengan jelas. Contoh: \"user Budi pegang aset apa saja?\"", 'database'];
        }

        $user = User::where('name', 'like', "%{$nama}%")->first();

        if (!$user) {
            return ["User **{$nama}** tidak ditemukan.", 'database'];
        }

        $assets = $user->currentAssets()->with('category')->limit(15)->get();

        if ($assets->isEmpty()) {
            return ["User **{$user->name}** sedang tidak memegang aset apa pun.", 'database'];
        }

        $total = $user->currentAssets()->count();
        $lines = ["👤 **Aset yang dipegang oleh {$user->name}** ({$total} unit):\n"];
        foreach ($assets as $a) {
            $lines[] = "• {$a->serial_number}";
            if ($a->hostname) {
                $lines[] = " ({$a->hostname})";
            }
            $lines[] = " — {$a->brand} {$a->model}";
        }

        if ($total > 15) {
            $lines[] = "\nMenampilkan 15 pertama dari {$total} aset.";
        }

        return [implode("", $lines), 'database'];
    }

    private function extractAssetKeyword(string $pesan): ?string
    {
        // Cari SN pattern (AST-2026-0001, T14-SN-0001, NB-T14-001)
        if (preg_match('/\b([A-Z]{2,}[-_][A-Z0-9]{2,}(?:[-_][A-Z0-9]+)*)\b/i', $pesan, $m)) {
            return $m[1];
        }
        // Fallback: kata setelah "aset", "laptop", dll
        if (preg_match('/(?:aset|laptop|pc|printer|monitor)\s+([a-z0-9\-]+)/i', $pesan, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractUserName(string $pesan): ?string
    {
        // Setelah "user", "pak", "bu", dll
        if (preg_match('/(?:user|pegawai|karyawan|pak|bu|mas|mbak|sdr|sdri)\s+([a-z]+)/i', $pesan, $m)) {
            return $m[1];
        }
        return null;
    }

    private function matchAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }
}
