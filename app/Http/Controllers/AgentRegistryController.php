<?php

namespace App\Http\Controllers;

use App\Models\AgentToken;
use App\Models\Asset;
use App\Models\PendingAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentRegistryController extends Controller
{
    /**
     * Halaman utama: pending agents + rejected + token aktif + stats.
     */
    public function index(Request $request)
    {
        // === Pending agents (belum di-approve & belum di-reject) ===
        $pendingQuery = PendingAgent::pending()->latest('attempted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        $pending = $pendingQuery->paginate(20, ['*'], 'pending_page');

        // === Rejected agents (device di-blacklist) ===
        $rejectedQuery = PendingAgent::rejected()->latest('rejected_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $rejectedQuery->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $rejected = $rejectedQuery->paginate(10, ['*'], 'rejected_page');

        // === Token aktif ===
        $tokenQuery = AgentToken::with('asset')
            ->where('is_active', true)
            ->latest('last_used_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $tokenQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('device_hostname', 'like', "%{$search}%")
                    ->orWhereHas('asset', function ($aq) use ($search) {
                        $aq->where('asset_code', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
            });
        }

        $tokens = $tokenQuery->paginate(20, ['*'], 'token_page');

        // === Stats ===
        $stats = [
            'pending_count' => PendingAgent::pending()->count(),
            'rejected_count' => PendingAgent::rejected()->count(),
            'active_tokens' => AgentToken::where('is_active', true)->count(),
            'revoked_tokens' => AgentToken::where('is_active', false)->count(),
            'online' => Asset::where('last_seen_at', '>=', now()->subMinutes(10))->count(),
            'idle' => Asset::whereBetween('last_seen_at', [
                now()->subMinutes(60),
                now()->subMinutes(10),
            ])->count(),
            'offline' => Asset::where('last_seen_at', '<', now()->subMinutes(60))
                ->whereNotNull('last_seen_at')->count(),
            'never_seen' => Asset::whereNull('last_seen_at')
                ->whereHas('category', fn($q) => $q->where('is_agent_monitored', true))
                ->count(),
        ];

        // === Daftar aset untuk dropdown approve (format array untuk JSON) ===
        $assets = Asset::with('category')
            ->whereDoesntHave('agentToken')
            ->whereHas('category', fn($q) => $q->where('is_agent_monitored', true))
            ->orderBy('asset_code')
            ->limit(500)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'asset_code' => $a->asset_code ?? '-',
                    'serial_number' => $a->serial_number,
                    'hostname' => $a->hostname,
                    'brand' => $a->brand,
                    'model' => $a->model,
                    'category' => $a->category?->name,
                    'label' => trim("{$a->asset_code} — {$a->brand} {$a->model} ({$a->category?->name})"),
                    'search_text' => strtolower(trim(
                        "{$a->asset_code} {$a->serial_number} {$a->hostname} {$a->brand} {$a->model} {$a->category?->name}"
                    )),
                ];
            })
            ->values();

        // === Pre-match pending agent dengan asset yang ada ===
        $pending->getCollection()->transform(function ($p) use ($assets) {
            $matched = $p->serial_number
                ? $assets->firstWhere('serial_number', $p->serial_number)
                : null;

            $p->suggested_asset_id = $matched['id'] ?? null;
            return $p;
        });

        return view('agent-registry.index', compact(
            'pending',
            'rejected',
            'tokens',
            'stats',
            'assets'
        ));
    }

    /**
     * Approve pending agent → generate token & link ke asset.
     */
    public function approve(Request $request, PendingAgent $pending)
    {
        // === GUARD: sudah di-reject ===
        if ($pending->rejected_at) {
            return back()->with('error', "Agent {$pending->hostname} sudah di-reject, tidak bisa di-approve.");
        }

        // === GUARD: sudah di-approve ===
        if ($pending->approved_at) {
            return back()->with('error', "Agent {$pending->hostname} sudah di-approve sebelumnya.");
        }

        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        // Cek kalau asset sudah punya token
        if ($asset->agentToken) {
            $token = $asset->agentToken;
            $token->update([
                'is_active' => true,
                'device_serial' => $pending->serial_number,
                'device_hostname' => $pending->hostname,
            ]);
        } else {
            // Generate token baru
            $token = AgentToken::create([
                'token' => Str::random(64),
                'name' => "{$asset->asset_code} Agent",
                'asset_id' => $asset->id,
                'is_active' => true,
                'device_serial' => $pending->serial_number,
                'device_hostname' => $pending->hostname,
            ]);
        }

        // Update hostname asset kalau belum ada
        if (!$asset->hostname) {
            $asset->update(['hostname' => $pending->hostname]);
        }

        // Tandai pending sudah di-approve
        $pending->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'asset_id' => $asset->id,
        ]);

        return back()->with(
            'success',
            "Agent {$pending->hostname} berhasil di-approve. Token akan dikirim ke device saat register berikutnya."
        );
    }

    /**
     * Reject pending agent → soft reject (tidak dihapus, tapi di-blacklist).
     */
    public function reject(Request $request, PendingAgent $pending)
    {
        // === GUARD: sudah di-reject ===
        if ($pending->rejected_at) {
            return back()->with('error', "Agent {$pending->hostname} sudah di-reject sebelumnya.");
        }

        // === GUARD: sudah di-approve ===
        if ($pending->approved_at) {
            return back()->with('error', "Agent {$pending->hostname} sudah di-approve, tidak bisa di-reject.");
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:200',
        ]);

        $pending->update([
            'rejected_at' => now(),
            'rejected_reason' => $validated['reason'] ?? 'Rejected by admin',
        ]);

        return back()->with(
            'success',
            "Pending agent {$pending->hostname} di-reject. Device ini tidak akan muncul lagi."
        );
    }

    /**
     * Hapus permanen pending agent yang sudah di-reject.
     */
    public function forceDelete(PendingAgent $pending)
    {
        $hostname = $pending->hostname;
        $pending->delete();

        return back()->with('success', "Pending agent {$hostname} dihapus permanen.");
    }

    /**
     * Bersihkan semua pending (yang belum di-approve/reject).
     */
    public function clear()
    {
        $count = PendingAgent::pending()->delete();
        return back()->with('success', "{$count} pending agent dibersihkan.");
    }

    /**
     * Bersihkan semua rejected (hapus permanen).
     */
    public function clearRejected()
    {
        $count = PendingAgent::rejected()->delete();
        return back()->with('success', "{$count} rejected agent dihapus permanen.");
    }

    /**
     * Revoke token (nonaktifkan).
     */
    public function revokeToken(AgentToken $token)
    {
        $token->update(['is_active' => false]);
        return back()->with('success', "Token {$token->name} dinonaktifkan.");
    }

    /**
     * Aktifkan kembali token.
     */
    public function activateToken(AgentToken $token)
    {
        $token->update(['is_active' => true]);
        return back()->with('success', "Token {$token->name} diaktifkan kembali.");
    }

    /**
     * Regenerate token (buat baru, revoke lama).
     */
    public function regenerateToken(AgentToken $token)
    {
        $token->update([
            'token' => Str::random(64),
            'is_active' => true,
        ]);

        return back()->with('success', "Token {$token->name} berhasil di-regenerate.");
    }
}
