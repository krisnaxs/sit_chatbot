<?php

namespace App\Services\Query;

use App\Models\Location;
use Illuminate\Support\Str;

class LocationQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // 1. Jumlah lokasi
        if (
            $this->matchAny($lower, ['lokasi', 'location', 'ruangan', 'ruang', 'gedung']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total'])
        ) {
            return $this->countLocations();
        }

        // 2. Daftar lokasi
        if (
            $this->matchAny($lower, ['lokasi', 'location', 'ruangan', 'ruang', 'gedung']) &&
            $this->matchAny($lower, ['daftar', 'list', 'tampilkan'])
        ) {
            return $this->listLocations();
        }

        // 3. Aset di lokasi tertentu
        if (
            $this->matchAny($lower, ['aset', 'asset']) &&
            $this->matchAny($lower, ['lokasi', 'ruangan', 'ruang', 'gedung'])
        ) {
            return $this->assetsByLocation($pesan);
        }

        return null;
    }

    protected function countLocations(): array
    {
        $total = Location::count();
        $active = Location::where('is_active', true)->count();

        return ["Ada **{$total} lokasi** terdaftar (**{$active} aktif**).", 'database'];
    }

    protected function listLocations(): array
    {
        $locations = Location::where('is_active', true)
            ->orderBy('building')->orderBy('floor')
            ->limit(15)->get();

        if ($locations->isEmpty()) {
            return ["Belum ada lokasi terdaftar.", 'database'];
        }

        $jawaban = "**" . $locations->count() . " lokasi aktif**:\n\n";
        foreach ($locations as $l) {
            $jawaban .= "• {$l->full_name}";
            if ($l->division)
                $jawaban .= " — {$l->division}";
            $jawaban .= "\n";
        }

        return [$jawaban, 'database'];
    }

    protected function assetsByLocation(string $pesan): array
    {
        // Cari lokasi yang disebut
        $location = Location::where('building', 'like', '%' . $this->extractKeyword($pesan) . '%')
            ->orWhere('room', 'like', '%' . $this->extractKeyword($pesan) . '%')
            ->first();

        if (!$location) {
            return ["Lokasi tersebut tidak ditemukan. Coba sebutkan gedung atau ruangan.", 'database'];
        }

        $assets = $location->currentAssets()->limit(10)->get();

        if ($assets->isEmpty()) {
            return ["Tidak ada aset di **{$location->full_name}**.", 'database'];
        }

        $jawaban = "**" . $assets->count() . " aset** di **{$location->full_name}**:\n\n";
        foreach ($assets as $a) {
            $jawaban .= "• {$a->serial_number}";
            if ($a->hostname)
                $jawaban .= " ({$a->hostname})";
            $jawaban .= " — {$a->brand} {$a->model}\n";
        }

        return [$jawaban, 'database'];
    }

    protected function extractKeyword(string $pesan): string
    {
        // Ambil kata setelah "di" atau "lokasi"
        if (preg_match('/(?:di|lokasi|ruang|gedung)\s+([a-z0-9]+)/i', $pesan, $m)) {
            return $m[1];
        }
        return '';
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
