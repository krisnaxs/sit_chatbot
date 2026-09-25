<?php

namespace App\Services\Query;

use App\Models\AssetOwnership;
use App\Models\Vendor;
use Illuminate\Support\Str;

class VendorQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // 1. Jumlah vendor
        if (
            $this->matchAny($lower, ['vendor', 'supplier', 'penyedia']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total'])
        ) {
            return $this->countVendors();
        }

        // 2. Daftar vendor
        if (
            $this->matchAny($lower, ['vendor', 'supplier', 'penyedia']) &&
            $this->matchAny($lower, ['daftar', 'list', 'tampilkan', 'siapa'])
        ) {
            return $this->listVendors();
        }

        // 3. Kontrak sewa hampir berakhir
        if (
            $this->matchAny($lower, ['kontrak', 'sewa', 'lease']) &&
            $this->matchAny($lower, ['berakhir', 'habis', 'selesai', 'expired'])
        ) {
            return $this->expiringContracts();
        }

        // 4. Total biaya sewa bulanan
        if (
            $this->matchAny($lower, ['biaya sewa', 'monthly cost', 'sewa bulanan'])
        ) {
            return $this->monthlyLeaseCost();
        }

        return null;
    }

    protected function countVendors(): array
    {
        $total = Vendor::count();
        $active = Vendor::where('is_active', true)->count();

        return ["Ada **{$total} vendor** (**{$active} aktif**).", 'database'];
    }

    protected function listVendors(): array
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->limit(10)->get();

        if ($vendors->isEmpty()) {
            return ["Belum ada vendor terdaftar.", 'database'];
        }

        $jawaban = "**" . $vendors->count() . " vendor aktif**:\n\n";
        foreach ($vendors as $v) {
            $jawaban .= "• {$v->name}";
            if ($v->type)
                $jawaban .= " — {$v->type}";
            if ($v->phone)
                $jawaban .= " ({$v->phone})";
            $jawaban .= "\n";
        }

        return [$jawaban, 'database'];
    }

    protected function expiringContracts(): array
    {
        $contracts = AssetOwnership::whereNotNull('contract_end')
            ->whereDate('contract_end', '>=', today())
            ->whereDate('contract_end', '<=', now()->addDays(30))
            ->with(['asset', 'vendor'])
            ->orderBy('contract_end')
            ->limit(10)->get();

        if ($contracts->isEmpty()) {
            return ["Tidak ada kontrak sewa yang berakhir dalam 30 hari ke depan. ✅", 'database'];
        }

        $jawaban = "**" . $contracts->count() . " kontrak** berakhir < 30 hari:\n\n";
        foreach ($contracts as $c) {
            $jawaban .= "• {$c->asset?->serial_number}";
            if ($c->asset?->hostname)
                $jawaban .= " ({$c->asset->hostname})";
            $jawaban .= " — " . ($c->vendor?->name ?? '-');
            $jawaban .= " (" . $c->contract_end->diffForHumans() . ")\n";
        }

        return [$jawaban, 'database'];
    }

    protected function monthlyLeaseCost(): array
    {
        $total = AssetOwnership::where('ownership_type', 'leased')->sum('monthly_cost');
        $formatted = 'Rp ' . number_format($total, 0, ',', '.');

        return ["Total biaya sewa bulanan: **{$formatted}**", 'database'];
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
