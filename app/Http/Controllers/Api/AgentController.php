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
        $asset = Asset::where('serial_number', $validated['serial_number'])->first();

        if (!$asset) {
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
        if ($asset->category && !$asset->category->is_agent_monitored) {
            return response()->json([
                'ok' => false,
                'error' => 'category_not_monitored',
                'message' => 'Kategori aset ini tidak termasuk monitoring agent.',
            ], 403);
        }
        $token = $asset->agentToken;

        if ($token) {
            if (!$token->is_active) {
                return response()->json([
                    'ok' => false,
                    'error' => 'token_revoked',
                    'message' => 'Agent untuk aset ini dinonaktifkan oleh admin.',
                ], 403);
            }
            $token->update([
                'last_used_at' => now(),
                'last_ip' => $validated['ip'] ?? null,
            ]);
            $this->syncHostname($asset, $validated['hostname']);

            return response()->json([
                'ok' => true,
                'token' => $token->token,
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'message' => 'Token sudah ada. Selamat menggunakan.',
            ]);
        }
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


    public function heartbeat(Request $request)
    {
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
            'last_os' => 'nullable|string|max:200',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_accuracy' => 'nullable|numeric|min:0',
            'location_source' => 'nullable|string|max:20',
        ]);
        $asset = $agentToken->asset;
        if (!$asset) {
            return response()->json([
                'error' => 'Asset tidak ditemukan untuk token ini',
            ], 404);
        }
        $locationId = null;
        $lastLat = null;
        $lastLng = null;
        $locationSource = null;
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $lastLat = $validated['latitude'];
            $lastLng = $validated['longitude'];
            $locationSource = $validated['location_source'] ?? 'agent';
        }
        if (!$lastLat && !empty($validated['wifi_bssid'])) {
            $ap = ApLocation::findByBssid($validated['wifi_bssid']);
            if ($ap) {
                $locationId = $ap->location_id;

                if ($ap->latitude && $ap->longitude) {
                    $lastLat = $ap->latitude;
                    $lastLng = $ap->longitude;
                    $locationSource = 'bssid';
                }
            }
        }
        DB::transaction(function () use ($asset, $validated, $agentToken, $locationId, $lastLat, $lastLng, $locationSource) {
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
                'last_os' => $validated['last_os'] ?? $asset->last_os,
                'agent_status' => 'online',
            ];
            if ($locationId) {
                $updateData['current_location_id'] = $locationId;
            }
            if ($lastLat && $lastLng) {
                $updateData['last_lat'] = $lastLat;
                $updateData['last_lng'] = $lastLng;
                $updateData['location_source'] = $locationSource;
            }

            $asset->update($updateData);
            $this->syncHostname($asset, $validated['hostname']);
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
            'lat' => $lastLat,
            'lng' => $lastLng,
            'location_source' => $locationSource,
            'server_time' => now()->toIso8601String(),
        ]);
    }


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


    private function syncHostname(Asset $asset, string $newHostname): void
    {
        if ($asset->hostname === $newHostname) {
            return;
        }

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
