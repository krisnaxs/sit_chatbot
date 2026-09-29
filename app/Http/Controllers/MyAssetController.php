<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetRequest;
use App\Models\ConsumableTransaction;
use Illuminate\Http\Request;

class MyAssetController extends Controller
{
    /**
     * Daftar aset & konsumable yang dipegang user ini.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Aset yang sedang dipegang
        $assets = Asset::with(['category', 'currentLocation'])
            ->where('current_user_id', $userId)
            ->orderBy('asset_code')
            ->get();

        // Peminjaman aktif
        $activeLoans = AssetLoan::with(['asset'])
            ->where('user_id', $userId)
            ->where('status', 'borrowed')
            ->orderByDesc('loan_date')
            ->get();

        // Konsumable yang pernah diambil (transaksi out)
        $consumables = ConsumableTransaction::with(['consumable.category', 'location'])
            ->where('user_id', $userId)
            ->where('type', 'out')
            ->orderByDesc('transaction_date')
            ->limit(20)
            ->get();

        // 🆕 Pengajuan terbaru user ini
        $latestRequest = AssetRequest::with(['asset', 'consumable'])
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->first();

        // Summary
        $summary = [
            'total_assets' => $assets->count(),
            'total_loans' => $activeLoans->count(),
            'total_consumables' => ConsumableTransaction::where('user_id', $userId)
                ->where('type', 'out')
                ->sum('quantity'),
        ];

        return view('my-assets.index', compact(
            'assets',
            'activeLoans',
            'consumables',
            'summary',
            'latestRequest'
        ));
    }
}
