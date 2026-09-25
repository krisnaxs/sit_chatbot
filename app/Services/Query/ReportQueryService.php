<?php

namespace App\Services\Query;

use App\Models\Chat;
use App\Models\ConsumableTransaction;
use App\Models\Knowledge;
use App\Models\PendingKnowledge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportQueryService
{
    public function tryAnswer(string $pesan): ?array
    {
        $lower = Str::lower($pesan);

        // 1. Chat hari ini
        if (
            $this->matchAny($lower, ['chat', 'percakapan', 'chatbot']) &&
            $this->matchAny($lower, ['hari ini', 'today', 'berapa', 'jumlah'])
        ) {
            return $this->countChatToday();
        }

        // 2. Knowledge
        if (
            $this->matchAny($lower, ['knowledge', 'pengetahuan']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'total'])
        ) {
            return $this->countKnowledge();
        }

        // 3. Pending AI
        if (
            $this->matchAny($lower, ['pending', 'ai belum'])
        ) {
            return $this->countPendingAI();
        }

        // 4. Transaksi konsumable bulan ini
        if (
            $this->matchAny($lower, ['transaksi', 'transaction']) &&
            $this->matchAny($lower, ['konsumable', 'consumable', 'bulan ini'])
        ) {
            return $this->countConsumableTransactions();
        }

        // 5. Barang masuk/keluar bulan ini
        if (
            $this->matchAny($lower, ['barang masuk', 'stok masuk', 'in']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'bulan ini'])
        ) {
            return $this->sumConsumableIn();
        }

        if (
            $this->matchAny($lower, ['barang keluar', 'stok keluar', 'out']) &&
            $this->matchAny($lower, ['berapa', 'jumlah', 'bulan ini'])
        ) {
            return $this->sumConsumableOut();
        }

        return null;
    }

    protected function countChatToday(): array
    {
        $today = Chat::whereDate('waktu', today())->count();
        $week = Chat::whereBetween('waktu', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        return [
            "Chat hari ini: **{$today}**\nChat minggu ini: **{$week}**",
            'database'
        ];
    }

    protected function countKnowledge(): array
    {
        $total = Knowledge::count();
        $withFile = Knowledge::whereNotNull('file_path')->count();

        return [
            "Total knowledge: **{$total}**\nDengan file: **{$withFile}**",
            'database'
        ];
    }

    protected function countPendingAI(): array
    {
        $pending = PendingKnowledge::where('status', 'pending')->count();
        $approved = PendingKnowledge::where('status', 'approved')->count();
        $rejected = PendingKnowledge::where('status', 'rejected')->count();

        return [
            "**Pending AI**\n" .
            "• Menunggu review: **{$pending}**\n" .
            "• Sudah disetujui: **{$approved}**\n" .
            "• Ditolak: **{$rejected}**",
            'database'
        ];
    }

    protected function countConsumableTransactions(): array
    {
        $count = ConsumableTransaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        return ["Transaksi konsumable bulan ini: **{$count}**", 'database'];
    }

    protected function sumConsumableIn(): array
    {
        $total = ConsumableTransaction::where('type', 'in')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('quantity');

        return ["Total barang masuk bulan ini: **{$total} unit**", 'database'];
    }

    protected function sumConsumableOut(): array
    {
        $total = ConsumableTransaction::where('type', 'out')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('quantity');

        return ["Total barang keluar bulan ini: **{$total} unit**", 'database'];
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
