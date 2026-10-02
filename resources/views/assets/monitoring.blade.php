@extends('layouts.app')

@section('title', 'Monitoring Agent — SIAM')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #miniMap {
            height: 240px;
            border-radius: 12px;
            z-index: 1;
        }

        .leaflet-popup-content {
            margin: 12px 16px;
            min-width: 200px;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto" x-data="monitoringManager()">

        <div class="space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Monitoring Agent</h1>
                    <p class="text-sm text-gray-500">Status real-time semua aset yang terpasang agent</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="toggleAutoRefresh()"
                        :class="autoRefresh
                            ?
                            'bg-emerald-600 text-white hover:bg-emerald-700' :
                            'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="autoRefresh ? 'Auto: ON (30s)' : 'Auto: OFF'"></span>
                    </button>

                    <button type="button" @click="location.reload()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>

                    <a href="{{ route('siam.assets.index') }}"
                        class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 text-sm font-medium inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Daftar Aset
                    </a>
                </div>
            </div>

            {{-- COVERAGE BAR --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Coverage Agent</h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $stats['total'] - $stats['never'] }} dari {{ $stats['total'] }} aset sudah terpasang agent
                            @if ($stats['never'] > 0)
                                — <span class="text-amber-600 font-semibold">{{ $stats['never'] }} belum</span>
                            @endif
                        </p>
                    </div>
                    <span class="text-3xl font-bold text-indigo-600">{{ $coverage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="h-3 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-500"
                        style="width: {{ $coverage }}%"></div>
                </div>
            </div>

            {{-- STATISTIK --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <a href="{{ route('siam.assets.monitoring', ['status' => 'online']) }}"
                    class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500 hover:shadow-md transition
                        {{ request('status') === 'online' ? 'ring-2 ring-emerald-400' : '' }}">
                    <div class="text-xs text-gray-500 uppercase">Online</div>
                    <div class="text-2xl font-bold text-emerald-600">{{ $stats['online'] }}</div>
                    <div class="text-[10px] text-emerald-500 mt-1">Heartbeat &lt; 10 menit</div>
                </a>
                <a href="{{ route('siam.assets.monitoring', ['status' => 'idle']) }}"
                    class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500 hover:shadow-md transition
                        {{ request('status') === 'idle' ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="text-xs text-gray-500 uppercase">Idle</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['idle'] }}</div>
                    <div class="text-[10px] text-yellow-500 mt-1">10-60 menit</div>
                </a>
                <a href="{{ route('siam.assets.monitoring', ['status' => 'offline']) }}"
                    class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500 hover:shadow-md transition
                        {{ request('status') === 'offline' ? 'ring-2 ring-red-400' : '' }}">
                    <div class="text-xs text-gray-500 uppercase">Offline</div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['offline'] }}</div>
                    <div class="text-[10px] text-red-500 mt-1">&gt; 60 menit</div>
                </a>
                <a href="{{ route('siam.assets.monitoring', ['status' => 'never']) }}"
                    class="bg-white rounded-lg shadow p-4 border-l-4 border-slate-500 hover:shadow-md transition
                        {{ request('status') === 'never' ? 'ring-2 ring-slate-400' : '' }}">
                    <div class="text-xs text-gray-500 uppercase">Belum Install</div>
                    <div class="text-2xl font-bold text-slate-600">{{ $stats['never'] }}</div>
                    <div class="text-[10px] text-slate-500 mt-1">Tanpa agent</div>
                </a>
                <a href="{{ route('siam.assets.monitoring') }}"
                    class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500 hover:shadow-md transition
                        {{ !request('status') ? 'ring-2 ring-indigo-400' : '' }}">
                    <div class="text-xs text-gray-500 uppercase">Total</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
                    <div class="text-[10px] text-indigo-500 mt-1">Semua aset</div>
                </a>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.assets.monitoring') }}"
                    class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">

                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Cari (Hostname / SN / IP / User)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Contoh: LAP-HR-001 atau Budi"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                        <select name="category_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">WiFi</label>
                        <select name="wifi" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($wifiList as $wifi)
                                <option value="{{ $wifi }}" @selected(request('wifi') === $wifi)>
                                    {{ $wifi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status Agent</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="online" @selected(request('status') === 'online')>🟢 Online</option>
                            <option value="idle" @selected(request('status') === 'idle')>🟡 Idle</option>
                            <option value="offline" @selected(request('status') === 'offline')>🔴 Offline</option>
                            <option value="never" @selected(request('status') === 'never')>⚫ Belum Install</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.assets.monitoring') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left w-12">No</th>
                                <th class="px-3 py-2 text-left">SN</th>
                                <th class="px-3 py-2 text-left">Hostname</th>
                                <th class="px-3 py-2 text-left">Brand & Model</th>
                                <th class="px-3 py-2 text-left">OS</th>
                                <th class="px-3 py-2 text-left">IP</th>
                                <th class="px-3 py-2 text-left">WiFi</th>
                                <th class="px-3 py-2 text-left">User</th>
                                <th class="px-3 py-2 text-left">Lokasi</th>
                                <th class="px-3 py-2 text-center">Sumber</th>
                                <th class="px-3 py-2 text-center">Suhu</th>
                                <th class="px-3 py-2 text-center">Uptime</th>
                                <th class="px-3 py-2 text-left">Terakhir</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assets as $index => $asset)
                                @php
                                    $agentStatus = $asset->agent_status_label;
                                    $agentColor = match ($asset->agent_status_color) {
                                        'green' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'red' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                    $agentIcon = match ($asset->agent_status_color) {
                                        'green' => '🟢',
                                        'yellow' => '🟡',
                                        'red' => '🔴',
                                        default => '⚫',
                                    };

                                    $rowBg = match (true) {
                                        !$asset->hasAgent() => 'bg-slate-50/50',
                                        $asset->isOnline() => 'bg-emerald-50/30',
                                        $asset->isIdle() => 'bg-yellow-50/30',
                                        default => 'bg-red-50/30',
                                    };

                                    // Sumber lokasi badge
                                    $sourceBadge = match ($asset->location_source) {
                                        'windows_api' => [
                                            'icon' => '🪟',
                                            'label' => 'Windows API',
                                            'color' => 'bg-blue-100 text-blue-700',
                                        ],
                                        'bssid' => [
                                            'icon' => '📡',
                                            'label' => 'WiFi BSSID',
                                            'color' => 'bg-purple-100 text-purple-700',
                                        ],
                                        'manual' => [
                                            'icon' => '✍️',
                                            'label' => 'Manual',
                                            'color' => 'bg-gray-100 text-gray-700',
                                        ],
                                        'browser' => [
                                            'icon' => '🌐',
                                            'label' => 'Browser',
                                            'color' => 'bg-cyan-100 text-cyan-700',
                                        ],
                                        default => null,
                                    };

                                    $assetData = [
                                        'id' => $asset->id,
                                        'asset_code' => $asset->asset_code,
                                        'serial_number' => $asset->serial_number,
                                        'hostname' => $asset->hostname,
                                        'brand' => $asset->brand,
                                        'model' => $asset->model,
                                        'last_os' => $asset->last_os,
                                        'os' => $asset->os,
                                        'category' => $asset->category?->name,
                                        'ownership_label' => $asset->ownership_label,
                                        'status' => $asset->status,
                                        'status_label' => $asset->status_label,
                                        'condition_percent' => $asset->condition_percent,
                                        'current_user' => $asset->currentUser?->name,
                                        'current_location' => $asset->currentLocation?->full_name,
                                        'purchase_year' => $asset->purchase_date?->format('Y'),

                                        // Agent fields
                                        'last_ip' => $asset->last_ip,
                                        'last_mac' => $asset->last_mac,
                                        'last_wifi_ssid' => $asset->last_wifi_ssid,
                                        'last_wifi_bssid' => $asset->last_wifi_bssid,
                                        'last_logged_user' => $asset->last_logged_user,
                                        'last_uptime_hours' => $asset->last_uptime_hours,
                                        'last_cpu_temp' => $asset->last_cpu_temp,
                                        'last_seen_at' => $asset->last_seen_at?->format('d M Y H:i'),
                                        'last_seen_human' => $asset->last_seen_at?->diffForHumans(),
                                        'agent_version' => $asset->agent_version,
                                        'agent_status_label' => $agentStatus,
                                        'agent_status_color' => $asset->agent_status_color,
                                        'agent_icon' => $agentIcon,

                                        // Koordinat
                                        'last_lat' => $asset->last_lat ? (float) $asset->last_lat : null,
                                        'last_lng' => $asset->last_lng ? (float) $asset->last_lng : null,
                                        'location_source' => $asset->location_source,

                                        'routes' => [
                                            'show' => route('siam.assets.show', $asset),
                                            'edit' => route('siam.assets.edit', $asset),
                                            'qr' => route('siam.assets.qr', $asset),
                                            'assign' => route('siam.assignments.create', ['asset_id' => $asset->id]),
                                            'loan' => route('siam.loans.create', ['asset_id' => $asset->id]),
                                            'maintenance' => route('siam.maintenances.create', [
                                                'asset_id' => $asset->id,
                                            ]),
                                        ],
                                    ];
                                @endphp

                                <tr class="transition-colors hover:bg-indigo-50 {{ $rowBg }}">
                                    {{-- No --}}
                                    <td class="px-3 py-2 text-xs text-gray-500 font-medium cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        {{ $assets->firstItem() + $index }}
                                    </td>

                                    {{-- SN --}}
                                    <td class="px-3 py-2 font-mono text-xs text-indigo-700 cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        {{ $asset->serial_number }}
                                    </td>

                                    {{-- Hostname --}}
                                    <td class="px-3 py-2 cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        <div class="font-mono text-xs font-semibold text-gray-800">
                                            {{ $asset->hostname ?? '-' }}
                                        </div>
                                        <div class="text-[10px] text-gray-500">
                                            {{ $asset->asset_code }}
                                        </div>
                                    </td>

                                    {{-- Brand & Model --}}
                                    <td class="px-3 py-2 cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        <div class="font-semibold text-gray-800 text-xs">{{ $asset->brand }}</div>
                                        <div class="text-xs text-gray-600">{{ $asset->model }}</div>
                                    </td>

                                    {{-- OS --}}
                                    <td class="px-3 py-2 text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_os)
                                            @php
                                                $osShort = preg_replace('/\s+\d+\.\d+.*$/', '', $asset->last_os);
                                            @endphp
                                            <div class="text-gray-700 font-medium">{{ $osShort }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono">
                                                {{ \Illuminate\Support\Str::limit($asset->last_os, 30) }}
                                            </div>
                                        @elseif ($asset->os)
                                            <div class="text-gray-600 italic text-[11px]">
                                                {{ \Illuminate\Support\Str::limit($asset->os, 25) }}
                                                <span class="text-[10px] text-gray-400">(manual)</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- IP --}}
                                    <td class="px-3 py-2 font-mono text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        {{ $asset->last_ip ?? '-' }}
                                    </td>

                                    {{-- WiFi --}}
                                    <td class="px-3 py-2 text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_wifi_ssid)
                                            <div class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3 text-indigo-500 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                <span>{{ $asset->last_wifi_ssid }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    {{-- User --}}
                                    <td class="px-3 py-2 text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_logged_user)
                                            <div class="font-semibold text-gray-800">{{ $asset->last_logged_user }}</div>
                                            @if ($asset->currentUser)
                                                <div class="text-[10px] text-gray-500">
                                                    SIAM: {{ $asset->currentUser->name }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- Lokasi --}}
                                    <td class="px-3 py-2 text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->currentLocation)
                                            <div>{{ $asset->currentLocation->building }}</div>
                                            <div class="text-[10px] text-gray-500">
                                                {{ $asset->currentLocation->room }}
                                            </div>
                                        @elseif ($asset->last_lat && $asset->last_lng)
                                            <div class="text-indigo-600 font-semibold flex items-center gap-1">
                                                📍 GPS
                                            </div>
                                            <div class="text-[10px] text-gray-500 font-mono">
                                                {{ number_format($asset->last_lat, 4) }},
                                                {{ number_format($asset->last_lng, 4) }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- SUMBER LOKASI --}}
                                    <td class="px-3 py-2 text-center cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($sourceBadge)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $sourceBadge['color'] }}">
                                                {{ $sourceBadge['icon'] }} {{ $sourceBadge['label'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- Suhu --}}
                                    <td class="px-3 py-2 text-center text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_cpu_temp)
                                            @php
                                                $temp = (float) $asset->last_cpu_temp;
                                                $tempColor = match (true) {
                                                    $temp >= 80 => 'text-red-600 font-bold',
                                                    $temp >= 70 => 'text-orange-600 font-semibold',
                                                    $temp >= 60 => 'text-yellow-600',
                                                    default => 'text-emerald-600',
                                                };
                                            @endphp
                                            <span class="{{ $tempColor }}">{{ $temp }}°C</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    {{-- Uptime --}}
                                    <td class="px-3 py-2 text-center text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_uptime_hours)
                                            @php
                                                $uptime = $asset->last_uptime_hours;
                                                $uptimeLabel =
                                                    $uptime >= 24
                                                        ? floor($uptime / 24) . 'd ' . $uptime % 24 . 'j'
                                                        : $uptime . ' jam';
                                            @endphp
                                            <span class="text-gray-700">{{ $uptimeLabel }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    {{-- Terakhir --}}
                                    <td class="px-3 py-2 text-xs cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        @if ($asset->last_seen_at)
                                            <div class="text-gray-800">{{ $asset->last_seen_at->diffForHumans() }}</div>
                                            <div class="text-[10px] text-gray-500">
                                                {{ $asset->last_seen_at->format('H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Belum pernah</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-3 py-2 cursor-pointer"
                                        @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] rounded-full font-semibold border {{ $agentColor }}">
                                            {{ $agentIcon }} {{ $agentStatus }}
                                        </span>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-3 py-2 text-center" @click.stop>
                                        <div class="inline-flex gap-1">
                                            @if ($asset->last_lat && $asset->last_lng)
                                                <button type="button"
                                                    @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                                    title="Lihat lokasi di peta"
                                                    class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                            @endif

                                            <a href="{{ route('siam.assets.show', $asset) }}"
                                                title="Lihat detail lengkap"
                                                class="p-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="px-3 py-12 text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-16 w-16 text-gray-300 mx-auto mb-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-gray-400 text-sm">Tidak ada aset yang sesuai filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $assets->links() }}
                </div>
            </div>

        </div>

        {{-- MODAL DETAIL AGENT --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" @click.away="closeModal()">

                {{-- HEADER MODAL --}}
                <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white shrink-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                    x-text="selectedAsset?.category"></span>
                                <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                    x-text="selectedAsset?.ownership_label"></span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-bold inline-flex items-center gap-1">
                                    <span x-text="selectedAsset?.agent_icon"></span>
                                    <span x-text="selectedAsset?.agent_status_label"></span>
                                </span>
                            </div>
                            <h3 class="text-xl font-bold truncate"
                                x-text="selectedAsset ? selectedAsset.brand + ' ' + selectedAsset.model : ''"></h3>
                            <p class="text-sm text-white/80 font-mono mt-1">
                                SN: <span x-text="selectedAsset?.serial_number"></span>
                            </p>
                        </div>
                        <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY MODAL (Scrollable) --}}
                <div class="flex-1 overflow-y-auto">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        {{-- Info Aset --}}
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Info Aset</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                            <div>
                                <div class="text-xs text-gray-500">Kode Aset</div>
                                <div class="font-mono text-xs text-indigo-700" x-text="selectedAsset?.asset_code"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Hostname</div>
                                <div class="font-mono text-xs" x-text="selectedAsset?.hostname ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Pemakai (SIAM)</div>
                                <div class="text-xs" x-text="selectedAsset?.current_user ?? 'Belum dipakai'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Lokasi</div>
                                <div class="text-xs" x-text="selectedAsset?.current_location ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Tahun Pembelian</div>
                                <div class="text-xs" x-text="selectedAsset?.purchase_year ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Kondisi</div>
                                <div class="text-xs font-medium"
                                    x-text="selectedAsset?.condition_percent !== null ? selectedAsset.condition_percent + '%' : '-'">
                                </div>
                            </div>
                        </div>

                        {{-- Info Agent --}}
                        <h4
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pt-3 border-t border-gray-200">
                            Info Agent (Real-time)
                        </h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <div class="text-xs text-gray-500">IP Address</div>
                                <div class="font-mono text-xs" x-text="selectedAsset?.last_ip ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">MAC Address</div>
                                <div class="font-mono text-xs" x-text="selectedAsset?.last_mac ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">WiFi SSID</div>
                                <div class="text-xs" x-text="selectedAsset?.last_wifi_ssid ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">WiFi BSSID</div>
                                <div class="font-mono text-xs" x-text="selectedAsset?.last_wifi_bssid ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">User Login</div>
                                <div class="text-xs" x-text="selectedAsset?.last_logged_user ?? '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Uptime</div>
                                <div class="text-xs"
                                    x-text="selectedAsset?.last_uptime_hours
                                        ? (selectedAsset.last_uptime_hours >= 24
                                            ? Math.floor(selectedAsset.last_uptime_hours / 24) + 'd ' + (selectedAsset.last_uptime_hours % 24) + 'j'
                                            : selectedAsset.last_uptime_hours + ' jam')
                                        : '-'">
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Suhu CPU</div>
                                <div class="text-xs font-semibold"
                                    x-text="selectedAsset?.last_cpu_temp ? selectedAsset.last_cpu_temp + '°C' : '-'"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Versi Agent</div>
                                <div class="text-xs font-mono" x-text="selectedAsset?.agent_version ?? '-'"></div>
                            </div>

                            {{-- 🆕 OS --}}
                            <div class="col-span-2">
                                <div class="text-xs text-gray-500">Operating System</div>
                                <div class="text-xs" x-text="selectedAsset?.last_os ?? selectedAsset?.os ?? '-'"></div>
                            </div>

                            <div class="col-span-2">
                                <div class="text-xs text-gray-500">Terakhir Heartbeat</div>
                                <div class="text-xs">
                                    <span x-text="selectedAsset?.last_seen_at ?? 'Belum pernah'"></span>
                                    <span class="text-gray-500 ml-1"
                                        x-text="selectedAsset?.last_seen_human ? '(' + selectedAsset.last_seen_human + ')' : ''"></span>
                                </div>
                            </div>
                        </div>

                        {{-- LOKASI ASET (MINI MAP) --}}
                        <h4
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pt-3 border-t border-gray-200">
                            Lokasi Aset
                        </h4>

                        <template x-if="selectedAsset?.last_lat && selectedAsset?.last_lng">
                            <div>
                                <div id="miniMap" class="w-full" style="height: 240px;"></div>
                                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                    <div>
                                        <span class="font-mono" x-text="selectedAsset.last_lat.toFixed(6)"></span>,
                                        <span class="font-mono" x-text="selectedAsset.last_lng.toFixed(6)"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 font-semibold text-[10px]"
                                            x-text="selectedAsset.location_source ?? 'unknown'"></span>
                                        <a :href="'https://www.google.com/maps?q=' + selectedAsset.last_lat + ',' + selectedAsset
                                            .last_lng"
                                            target="_blank" class="text-indigo-600 hover:underline font-semibold">
                                            Google Maps →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="!selectedAsset?.last_lat || !selectedAsset?.last_lng">
                            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300 mx-auto mb-2"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-xs text-gray-500">Koordinat tidak tersedia.</p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Pastikan Windows Location Service aktif di laptop dan agent sudah kirim lokasi.
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="p-6">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>
                        <div class="grid grid-cols-2 gap-3">

                            <a :href="selectedAsset?.routes.show"
                                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition group">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-gray-800">Detail</div>
                                    <div class="text-xs text-gray-500">Info lengkap</div>
                                </div>
                            </a>

                            <a :href="selectedAsset?.routes.qr" target="_blank"
                                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-cyan-300 hover:bg-cyan-50 transition group">
                                <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-gray-800">QR Code</div>
                                    <div class="text-xs text-gray-500">Cetak QR</div>
                                </div>
                            </a>

                            @if (auth()->user()->hasAnyRole(['admin', 'support']))
                                <a :href="selectedAsset?.routes.edit"
                                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-violet-300 hover:bg-violet-50 transition group">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm text-gray-800">Edit</div>
                                        <div class="text-xs text-gray-500">Ubah data</div>
                                    </div>
                                </a>
                            @endif

                            <a :href="selectedAsset?.routes.assign"
                                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-teal-300 hover:bg-teal-50 transition group">
                                <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-gray-800">Assign</div>
                                    <div class="text-xs text-gray-500">Serah terima</div>
                                </div>
                            </a>

                            <a :href="selectedAsset?.routes.maintenance"
                                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition group">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-gray-800">Perbaikan</div>
                                    <div class="text-xs text-gray-500">Catat maintenance</div>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end shrink-0">
                    <button @click="closeModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

        {{-- TOAST --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-amber-600 border-amber-400/40 shadow-amber-500/50': toast.type === 'warning',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[130] flex items-center gap-3
                   min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">

            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        function monitoringManager() {
            return {
                selectedAsset: null,
                showModal: false,
                autoRefresh: false,
                refreshInterval: null,

                // untuk mini-map
                miniMap: null,
                miniMarker: null,
                _mapInitTimer: null,

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openModal(asset) {
                    this.selectedAsset = asset;
                    this.showModal = true;

                    this.$nextTick(() => {
                        clearTimeout(this._mapInitTimer);
                        this._mapInitTimer = setTimeout(() => {
                            this.initMiniMap();
                        }, 250);
                    });
                },

                closeModal() {
                    this.showModal = false;
                    this.selectedAsset = null;

                    if (this.miniMap) {
                        this.miniMap.remove();
                        this.miniMap = null;
                        this.miniMarker = null;
                    }
                },

                initMiniMap() {
                    const asset = this.selectedAsset;
                    if (!asset || !asset.last_lat || !asset.last_lng) {
                        return;
                    }

                    const mapEl = document.getElementById('miniMap');
                    if (!mapEl) return;

                    if (this.miniMap) {
                        this.miniMap.remove();
                        this.miniMap = null;
                    }

                    this.miniMap = L.map('miniMap', {
                        center: [asset.last_lat, asset.last_lng],
                        zoom: 17,
                        scrollWheelZoom: true,
                        zoomControl: true,
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.miniMap);

                    const color = asset.agent_status_color === 'green' ? '#10b981' :
                        asset.agent_status_color === 'yellow' ? '#f59e0b' :
                        asset.agent_status_color === 'red' ? '#ef4444' :
                        '#6b7280';

                    const icon = L.divIcon({
                        className: '',
                        html: `<div style="
                            width: 32px; height: 32px;
                            background: ${color};
                            border: 3px solid white;
                            border-radius: 50%;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
                        "></div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    });

                    this.miniMarker = L.marker([asset.last_lat, asset.last_lng], {
                        icon: icon
                    }).addTo(this.miniMap);

                    this.miniMarker.bindPopup(`
                        <div style="font-family: system-ui;">
                            <b>${asset.brand} ${asset.model}</b><br>
                            <span style="font-family: monospace; color: #6366f1;">${asset.asset_code}</span><br>
                            <small>${asset.hostname}</small>
                        </div>
                    `);

                    setTimeout(() => {
                        if (this.miniMap) {
                            this.miniMap.invalidateSize();
                        }
                    }, 300);
                },

                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;

                    if (this.autoRefresh) {
                        this.refreshInterval = setInterval(() => {
                            window.location.reload();
                        }, 30000);

                        this.showToast('Auto-refresh aktif — halaman reload tiap 30 detik', 'info');
                        sessionStorage.setItem('monitoring_auto_refresh', 'true');
                    } else {
                        clearInterval(this.refreshInterval);
                        this.refreshInterval = null;

                        this.showToast('Auto-refresh dimatikan', 'info');
                        sessionStorage.removeItem('monitoring_auto_refresh');
                    }
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },

                init() {
                    if (sessionStorage.getItem('monitoring_auto_refresh') === 'true') {
                        this.autoRefresh = true;
                        this.refreshInterval = setInterval(() => {
                            window.location.reload();
                        }, 30000);
                    }

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showModal) {
                            this.closeModal();
                        }
                    });

                    @if (session('success'))
                        this.showToast(@json(session('success')), 'success');
                    @endif

                    @if (session('error'))
                        this.showToast(@json(session('error')), 'error');
                    @endif
                }
            }
        }

        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>
@endpush
