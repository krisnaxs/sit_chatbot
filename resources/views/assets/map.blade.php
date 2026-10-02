@extends('layouts.app')

@section('title', 'Peta Aset — SIAM')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #map {
            height: calc(100vh - 350px);
            min-height: 500px;
            border-radius: 12px;
            z-index: 1;
        }

        .leaflet-popup-content {
            margin: 12px 16px;
            min-width: 240px;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 12px;
        }

        /* 🆕 Custom tooltip */
        .asset-tooltip {
            background: rgba(17, 24, 39, 0.95) !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            font-family: system-ui, sans-serif !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            white-space: nowrap !important;
        }

        .asset-tooltip::before {
            border-top-color: rgba(17, 24, 39, 0.95) !important;
        }

        .asset-tooltip .tooltip-hostname {
            font-weight: 700;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 2px;
        }

        .asset-tooltip .tooltip-user {
            font-size: 11px;
            opacity: 0.85;
        }

        .asset-tooltip .tooltip-status {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
            margin-top: 4px;
        }

        .asset-tooltip .tooltip-status.online {
            background: #10b981;
            color: white;
        }

        .asset-tooltip .tooltip-status.idle {
            background: #f59e0b;
            color: white;
        }

        .asset-tooltip .tooltip-status.offline {
            background: #ef4444;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto space-y-4">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('siam.assets.monitoring') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke Monitoring
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Peta Keberadaan Aset</h1>
                <p class="text-sm text-gray-500">Lokasi real-time berdasarkan Windows Location API / WiFi BSSID</p>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="location.reload()"
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
                    Daftar Aset
                </a>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('siam.assets.map') }}"
                class="bg-white rounded-lg shadow p-3 border-l-4 border-indigo-500 hover:shadow-md transition
                    {{ !request('status') ? 'ring-2 ring-indigo-300' : '' }}">
                <div class="text-xs text-gray-500 uppercase">Total di Peta</div>
                <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
            </a>
            <a href="{{ route('siam.assets.map', ['status' => 'online']) }}"
                class="bg-white rounded-lg shadow p-3 border-l-4 border-emerald-500 hover:shadow-md transition
                    {{ request('status') === 'online' ? 'ring-2 ring-emerald-300' : '' }}">
                <div class="text-xs text-gray-500 uppercase">🟢 Online</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['online'] }}</div>
            </a>
            <a href="{{ route('siam.assets.map', ['status' => 'idle']) }}"
                class="bg-white rounded-lg shadow p-3 border-l-4 border-yellow-500 hover:shadow-md transition
                    {{ request('status') === 'idle' ? 'ring-2 ring-yellow-300' : '' }}">
                <div class="text-xs text-gray-500 uppercase">🟡 Idle</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['idle'] }}</div>
            </a>
            <a href="{{ route('siam.assets.map', ['status' => 'offline']) }}"
                class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500 hover:shadow-md transition
                    {{ request('status') === 'offline' ? 'ring-2 ring-red-300' : '' }}">
                <div class="text-xs text-gray-500 uppercase">🔴 Offline</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['offline'] }}</div>
            </a>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-3">
            <form method="GET" action="{{ route('siam.assets.map') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">

                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari hostname / user / asset code..."
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="online" @selected(request('status') === 'online')>🟢 Online</option>
                        <option value="idle" @selected(request('status') === 'idle')>🟡 Idle</option>
                        <option value="offline" @selected(request('status') === 'offline')>🔴 Offline</option>
                    </select>
                </div>

                <div>
                    <select name="category_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                        Filter
                    </button>
                    <a href="{{ route('siam.assets.map') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- PETA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            @if (count($assetsData) > 0)
                <div id="map"></div>

                {{-- Legend --}}
                <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span>Online (&lt; 10 menit)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span>Idle (10-60 menit)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span>Offline (&gt; 60 menit)</span>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <span class="text-gray-400">
                            <strong>{{ count($assetsData) }}</strong> aset dengan koordinat
                        </span>
                        <span
                            class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold text-[10px] border border-indigo-200">
                            💡 Hover marker untuk lihat info
                        </span>
                    </div>
                </div>
            @else
                <div class="py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-gray-400 text-sm">Belum ada aset dengan koordinat.</p>
                    <p class="text-xs text-gray-500 mt-2">
                        Pastikan agent sudah kirim data lokasi (Windows Location API) atau BSSID WiFi sudah di-mapping
                        di tabel <code class="bg-gray-100 px-1 rounded">ap_locations</code>.
                    </p>
                    <a href="{{ route('siam.assets.monitoring') }}"
                        class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">
                        Kembali ke Monitoring
                    </a>
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assetsData = @json($assetsData);

            if (assetsData.length === 0) return;

            // ==== 1. Inisialisasi peta ====
            const avgLat = assetsData.reduce((sum, a) => sum + a.lat, 0) / assetsData.length;
            const avgLng = assetsData.reduce((sum, a) => sum + a.lng, 0) / assetsData.length;

            const map = L.map('map').setView([avgLat, avgLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // ==== 2. Custom marker ====
            function createIcon(status) {
                const colors = {
                    'green': '#10b981',
                    'yellow': '#f59e0b',
                    'red': '#ef4444',
                    'gray': '#6b7280',
                };
                const color = colors[status] || colors.gray;

                return L.divIcon({
                    className: '',
                    html: `<div style="
                        width: 28px; height: 28px;
                        background: ${color};
                        border: 3px solid white;
                        border-radius: 50%;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
                        display: flex; align-items: center; justify-content: center;
                    "></div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14],
                });
            }

            // ==== 3. Tambah marker + tooltip ====
            assetsData.forEach(function(asset) {
                const marker = L.marker([asset.lat, asset.lng], {
                    icon: createIcon(asset.status)
                }).addTo(map);

                // ============================================================
                // 🆕 TOOLTIP — Muncul saat hover
                // ============================================================
                const statusClass = asset.status === 'green' ? 'online' :
                    asset.status === 'yellow' ? 'idle' :
                    'offline';

                const tooltipContent = `
                    <div>
                        <div class="tooltip-hostname">${asset.hostname}</div>
                        <div class="tooltip-user">
                            ${asset.current_user || asset.logged_user || 'Belum ada user'}
                        </div>
                        <span class="tooltip-status ${statusClass}">
                            ${asset.status_label}
                        </span>
                    </div>
                `;

                marker.bindTooltip(tooltipContent, {
                    className: 'asset-tooltip',
                    direction: 'top',
                    offset: [0, -16],
                    opacity: 1,
                    sticky: false,
                });

                // ============================================================
                // POPUP — Muncul saat klik
                // ============================================================
                const popupHtml = `
                    <div style="font-family: system-ui;">
                        <div style="font-weight: 700; font-size: 14px; color: #1f2937; margin-bottom: 4px;">
                            ${asset.brand_model}
                        </div>
                        <div style="font-family: monospace; font-size: 11px; color: #6366f1; margin-bottom: 8px;">
                            ${asset.asset_code}
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px;">
                            <div>
                                <div style="color: #9ca3af;">Hostname</div>
                                <div style="color: #374151; font-family: monospace;">${asset.hostname}</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af;">Pemakai</div>
                                <div style="color: #374151;">${asset.current_user || asset.logged_user || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af;">Lokasi</div>
                                <div style="color: #374151;">${asset.location || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af;">WiFi</div>
                                <div style="color: #374151;">${asset.wifi_ssid || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af;">IP</div>
                                <div style="color: #374151; font-family: monospace;">${asset.ip || '-'}</div>
                            </div>
                            <div>
                                <div style="color: #9ca3af;">Terakhir</div>
                                <div style="color: #374151;">${asset.last_seen || '-'}</div>
                            </div>
                        </div>

                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb;
                            display: flex; justify-content: space-between; align-items: center;">
                            <span style="
                                font-size: 10px; padding: 2px 8px; border-radius: 999px; font-weight: 600;
                                background: ${asset.status === 'green' ? '#d1fae5' : asset.status === 'yellow' ? '#fef3c7' : '#fee2e2'};
                                color: ${asset.status === 'green' ? '#065f46' : asset.status === 'yellow' ? '#92400e' : '#991b1b'};
                            ">${asset.status_label}</span>
                            <a href="${asset.detail_url}"
                                style="font-size: 11px; color: #4f46e5; text-decoration: none; font-weight: 600;">
                                Detail →
                            </a>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupHtml, {
                    maxWidth: 300
                });
            });

            // ==== 4. Fit bounds ====
            if (assetsData.length > 1) {
                const bounds = L.latLngBounds(assetsData.map(a => [a.lat, a.lng]));
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });
            }
        });
    </script>
@endpush
