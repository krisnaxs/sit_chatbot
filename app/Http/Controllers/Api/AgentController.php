<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentToken;
use App\Models\ApLocation;
use App\Models\Asset;
use App\Models\AssetAgentLog;
use App\Models\PendingAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Auto-register: agent minta token berdasarkan serial number BIOS.
     * POST /api/agent/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'hostname' => 'required|string|max:100',
            'mac_address' => 'nullable|string|max:17',
            'ip' => 'nullable|ip',
            'wifi_ssid' => 'nullable|string|max:100',
            'wifi_bssid' => 'nullable|string|max:17',
            'logged_user' => 'nullable|string|max:100',
        ]);

        // === Cari asset by serial number ===
        $asset = Asset::where('serial_number', $validated['serial_number'])->first();

        if (!$asset) {
            // === Belum terdaftar → Pending Agent ===
            $pending = PendingAgent::updateOrCreate(
                ['serial_number' => $validated['serial_number']],
                [
                    'hostname' => $validated['hostname'],
                    'mac_address' => $validated['mac_address'] ?? null,
                    'ip' => $validated['ip'] ?? null,
                    'wifi_ssid' => $validated['wifi_ssid'] ?? null,
                    'wifi_bssid' => $validated['wifi_bssid'] ?? null,
                    'logged_user' => $validated['logged_user'] ?? null,
                    'attempted_at' => now(),
                    'attempt_count' => DB::raw('attempt_count + 1'),
                ]
            );

            Log::info('Agent register: asset tidak ditemukan', [
                'serial' => $validated['serial_number'],
                'hostname' => $validated['hostname'],
                'pending_id' => $pending->id,
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'asset_not_found',
                'message' => 'Asset belum terdaftar di SIAM. Sudah masuk antrian review admin.',
                'serial' => $validated['serial_number'],
                'hostname' => $validated['hostname'],
            ], 404);
        }

        // === Cek kategori monitored ===
        if ($asset->category && !$asset->category->is_agent_monitored) {
            return response()->json([
                'ok' => false,
                'error' => 'category_not_monitored',
                'message' => 'Kategori aset ini tidak termasuk monitoring agent.',
            ], 403);
        }

        // === Ambil / buat token ===
        $token = $asset->agentToken;

        if ($token) {
            if (!$token->is_active) {
                return response()->json([
                    'ok' => false,
                    'error' => 'token_revoked',
                    'message' => 'Agent untuk aset ini dinonaktifkan oleh admin.',
                ], 403);
            }

            // Update last used
            $token->update([
                'last_used_at' => now(),
                'last_ip' => $validated['ip'] ?? null,
            ]);

            // Auto-sync hostname (kalau berubah & tidak konflik)
            $this->syncHostname($asset, $validated['hostname']);

            return response()->json([
                'ok' => true,
                'token' => $token->token,
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'message' => 'Token sudah ada. Selamat menggunakan.',
            ]);
        }

        // === Generate token baru ===
        $token = AgentToken::create([
            'token' => Str::random(64),
            'name' => "{$asset->asset_code} Agent",
            'asset_id' => $asset->id,
            'is_active' => true,
            'device_serial' => $validated['serial_number'],
            'device_hostname' => $validated['hostname'],
            'last_used_at' => now(),
            'last_ip' => $validated['ip'] ?? null,
        ]);

        // Auto-sync hostname
        $this->syncHostname($asset, $validated['hostname']);

        Log::info('Agent register: token generated', [
            'asset_id' => $asset->id,
            'asset_code' => $asset->asset_code,
            'hostname' => $validated['hostname'],
        ]);

        return response()->json([
            'ok' => true,
            'token' => $token->token,
            'asset_id' => $asset->id,
            'asset_code' => $asset->asset_code,
            'message' => 'Registrasi berhasil. Simpan token ini.',
        ]);
    }

    /**
     * Heartbeat: agent kirim data berkala.
     * POST /api/agent/heartbeat
     */
    public function heartbeat(Request $request)
    {
        // === Autentikasi token ===
        $tokenStr = $request->bearerToken();
        if (!$tokenStr) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $agentToken = AgentToken::where('token', $tokenStr)
            ->where('is_active', true)
            ->first();

        if (!$agentToken) {
            return response()->json(['error' => 'Invalid or revoked token'], 401);
        }

        // === Validasi payload ===
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'hostname' => 'required|string|max:100',
            'mac_address' => 'nullable|string|max:17',
            'ip' => 'nullable|ip',
            'wifi_ssid' => 'nullable|string|max:100',
            'wifi_bssid' => 'nullable|string|max:17',
            'logged_user' => 'nullable|string|max:100',
            'uptime_hours' => 'nullable|integer|min:0',
            'cpu_temp' => 'nullable|numeric|min:0|max:150',
            'agent_version' => 'nullable|string|max:20',
        ]);

        // === Ambil asset ===
        $asset = $agentToken->asset;
        if (!$asset) {
            return response()->json([
                'error' => 'Asset tidak ditemukan untuk token ini',
            ], 404);
        }

        // === Olah lokasi dari BSSID ===
        $locationId = null;
        if (!empty($validated['wifi_bssid'])) {
            $ap = ApLocation::findByBssid($validated['wifi_bssid']);
            if ($ap) {
                $locationId = $ap->location_id;
            }
        }

        // === Update asset + simpan log ===
        DB::transaction(function () use ($asset, $validated, $agentToken, $locationId) {
            $updateData = [
                'last_seen_at' => now(),
                'last_ip' => $validated['ip'] ?? $asset->last_ip,
                'last_mac' => $validated['mac_address'] ?? $asset->last_mac,
                'last_wifi_ssid' => $validated['wifi_ssid'] ?? $asset->last_wifi_ssid,
                'last_wifi_bssid' => $validated['wifi_bssid'] ?? $asset->last_wifi_bssid,
                'last_logged_user' => $validated['logged_user'] ?? $asset->last_logged_user,
                'last_uptime_hours' => $validated['uptime_hours'] ?? $asset->last_uptime_hours,
                'last_cpu_temp' => $validated['cpu_temp'] ?? $asset->last_cpu_temp,
                'agent_version' => $validated['agent_version'] ?? $asset->agent_version,
                'agent_status' => 'online',
            ];

            // Update lokasi kalau BSSID terdaftar
            if ($locationId) {
                $updateData['current_location_id'] = $locationId;
            }

            $asset->update($updateData);

            // Auto-sync hostname (kalau berubah & tidak konflik)
            $this->syncHostname($asset, $validated['hostname']);

            // Simpan log
            AssetAgentLog::create([
                'asset_id' => $asset->id,
                'ip' => $validated['ip'] ?? null,
                'mac' => $validated['mac_address'] ?? null,
                'wifi_ssid' => $validated['wifi_ssid'] ?? null,
                'wifi_bssid' => $validated['wifi_bssid'] ?? null,
                'hostname' => $validated['hostname'],
                'logged_user' => $validated['logged_user'] ?? null,
                'uptime_hours' => $validated['uptime_hours'] ?? null,
                'cpu_temp' => $validated['cpu_temp'] ?? null,
                'reported_at' => now(),
            ]);

            // Update token last used
            $agentToken->update([
                'last_used_at' => now(),
                'last_ip' => $validated['ip'] ?? null,
            ]);
        });

        return response()->json([
            'ok' => true,
            'asset_id' => $asset->id,
            'asset_code' => $asset->asset_code,
            'status' => $asset->status,
            'location_id' => $locationId,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Info agent (debug).
     * GET /api/agent/me
     */
    public function me(Request $request)
    {
        $tokenStr = $request->bearerToken();
        $agentToken = AgentToken::where('token', $tokenStr)
            ->where('is_active', true)
            ->with('asset')
            ->first();

        if (!$agentToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return response()->json([
            'token_name' => $agentToken->name,
            'asset' => $agentToken->asset ? [
                'id' => $agentToken->asset->id,
                'asset_code' => $agentToken->asset->asset_code,
                'serial' => $agentToken->asset->serial_number,
                'hostname' => $agentToken->asset->hostname,
                'brand' => $agentToken->asset->brand,
                'model' => $agentToken->asset->model,
                'status' => $agentToken->asset->status,
            ] : null,
            'last_used' => $agentToken->last_used_at,
        ]);
    }

    /**
     * Helper: auto-sync hostname ke asset (kalau beda & tidak konflik).
     */
    private function syncHostname(Asset $asset, string $newHostname): void
    {
        if ($asset->hostname === $newHostname) {
            return; // tidak berubah
        }

        // Cek konflik dengan aset lain
        $conflict = Asset::where('hostname', $newHostname)
            ->where('id', '!=', $asset->id)
            ->exists();

        if ($conflict) {
            Log::warning('Agent: hostname conflict, skip sync', [
                'asset_id' => $asset->id,
                'old_hostname' => $asset->hostname,
                'new_hostname' => $newHostname,
            ]);
            return;
        }

        $asset->update(['hostname' => $newHostname]);
    }
}
