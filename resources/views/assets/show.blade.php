@extends('layouts.app')

@section('title', 'Detail Aset — SIAM')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #assetMiniMap {
            height: 280px;
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
    <div class="max-w-7xl mx-auto" x-data="assetShowManager()">

        <div class="space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ route('siam.assets.index') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke daftar aset
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $asset->brand }} {{ $asset->model }}
                    </h1>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <p class="text-sm text-gray-500 font-mono">SN: {{ $asset->serial_number }}</p>
                        <a href="{{ route('siam.asset-types.index', ['search' => $asset->model]) }}" target="_blank"
                            class="text-xs text-indigo-600 hover:underline">
                            Lihat di Master →
                        </a>
                        @if ($asset->last_seen_at)
                            @php
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
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full font-semibold border {{ $agentColor }}">
                                {{ $agentIcon }} Agent {{ $asset->agent_status_label }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <span
                        class="px-3 py-1 text-sm rounded
                        {{ $asset->ownership_type === 'owned' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $asset->ownership_label }}
                    </span>
                    <span
                        class="px-3 py-1 text-sm rounded
                        {{ match ($asset->status) {
                            'available' => 'bg-green-100 text-green-700',
                            'in_use' => 'bg-blue-100 text-blue-700',
                            'loaned' => 'bg-yellow-100 text-yellow-700',
                            'maintenance' => 'bg-orange-100 text-orange-700',
                            'retired' => 'bg-gray-100 text-gray-700',
                            'lost' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        } }}">
                        {{ $asset->status_label }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Info Aset --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-4">Informasi Aset</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                            <div>
                                <dt class="text-gray-500">Kode Aset</dt>
                                <dd class="font-mono text-indigo-700">{{ $asset->asset_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Serial Number</dt>
                                <dd class="font-mono">{{ $asset->serial_number }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Brand</dt>
                                <dd class="font-medium">{{ $asset->brand ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Model</dt>
                                <dd class="font-medium">{{ $asset->model ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Hostname</dt>
                                <dd class="font-mono">{{ $asset->hostname ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Kategori</dt>
                                <dd>{{ $asset->category?->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">OS (Manual)</dt>
                                <dd>{{ $asset->os ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Lisensi OS</dt>
                                <dd>{{ $asset->os_license ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Tanggal Beli</dt>
                                <dd>{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Umur Aset</dt>
                                <dd>
                                    @if ($asset->purchase_date)
                                        {{ $asset->purchase_date->diffForHumans(null, true) }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Harga</dt>
                                <dd>{{ $asset->purchase_price ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.') : '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Garansi Expire</dt>
                                <dd>{{ $asset->warranty_expire?->format('d M Y') ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Kondisi</dt>
                                <dd>
                                    @if ($asset->condition_percent !== null)
                                        <span class="font-medium">{{ $asset->condition_percent }}%</span>
                                        <span class="text-gray-500 text-xs">—
                                            {{ $asset->condition_notes ?? 'tanpa catatan' }}</span>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if ($asset->specification)
                            <div class="mt-6 pt-4 border-t">
                                <h3 class="font-medium text-gray-700 mb-2 text-sm">Spesifikasi</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    @foreach ($asset->specification as $key => $val)
                                        <div>
                                            <dt class="text-gray-500 capitalize">{{ $key }}</dt>
                                            <dd>{{ $val }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif

                        @if ($asset->notes)
                            <div class="mt-6 pt-4 border-t">
                                <h3 class="font-medium text-gray-700 mb-2 text-sm">Catatan</h3>
                                <p class="text-sm text-gray-600">{{ $asset->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- ============================================================ --}}
                    {{-- INFO AGENT (REAL-TIME) --}}
                    {{-- ============================================================ --}}
                    @if ($asset->last_seen_at)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-semibold text-gray-800">Info Agent (Real-time)</h2>
                                        <p class="text-xs text-gray-500">Data dari agent yang berjalan di laptop</p>
                                    </div>
                                </div>

                                @php
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
                                @endphp
                                <span class="px-3 py-1 text-xs rounded-full font-bold border {{ $agentColor }}">
                                    {{ $agentIcon }} {{ $asset->agent_status_label }}
                                </span>
                            </div>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                                {{-- IP Address --}}
                                <div>
                                    <dt class="text-gray-500">IP Address</dt>
                                    <dd class="font-mono text-gray-800">{{ $asset->last_ip ?? '-' }}</dd>
                                </div>

                                {{-- MAC Address --}}
                                <div>
                                    <dt class="text-gray-500">MAC Address</dt>
                                    <dd class="font-mono text-gray-800">{{ $asset->last_mac ?? '-' }}</dd>
                                </div>

                                {{-- OS (dari agent) --}}
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500">Operating System</dt>
                                    <dd class="text-gray-800">
                                        {{ $asset->last_os ?? ($asset->os ?? '-') }}
                                        @if ($asset->last_os && $asset->os && $asset->last_os !== $asset->os)
                                            <span class="text-xs text-gray-400 ml-1">(update dari agent)</span>
                                        @endif
                                    </dd>
                                </div>

                                {{-- WiFi SSID --}}
                                <div>
                                    <dt class="text-gray-500">WiFi SSID</dt>
                                    <dd class="text-gray-800">
                                        @if ($asset->last_wifi_ssid)
                                            <div class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 text-indigo-500 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                {{ $asset->last_wifi_ssid }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>

                                {{-- WiFi BSSID --}}
                                <div>
                                    <dt class="text-gray-500">WiFi BSSID</dt>
                                    <dd class="font-mono text-gray-800">{{ $asset->last_wifi_bssid ?? '-' }}</dd>
                                </div>

                                {{-- User Login --}}
                                <div>
                                    <dt class="text-gray-500">User Login (Windows)</dt>
                                    <dd class="text-gray-800">{{ $asset->last_logged_user ?? '-' }}</dd>
                                </div>

                                {{-- Uptime --}}
                                <div>
                                    <dt class="text-gray-500">Uptime</dt>
                                    <dd class="text-gray-800">
                                        @if ($asset->last_uptime_hours)
                                            @php
                                                $u = $asset->last_uptime_hours;
                                                echo $u >= 24
                                                    ? floor($u / 24) . ' hari ' . $u % 24 . ' jam'
                                                    : $u . ' jam';
                                            @endphp
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>

                                {{-- Suhu CPU --}}
                                <div>
                                    <dt class="text-gray-500">Suhu CPU</dt>
                                    <dd class="font-semibold">
                                        @if ($asset->last_cpu_temp)
                                            @php
                                                $temp = (float) $asset->last_cpu_temp;
                                                $color = match (true) {
                                                    $temp >= 80 => 'text-red-600',
                                                    $temp >= 70 => 'text-orange-600',
                                                    $temp >= 60 => 'text-yellow-600',
                                                    default => 'text-emerald-600',
                                                };
                                            @endphp
                                            <span class="{{ $color }}">{{ $temp }}°C</span>
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>

                                {{-- Versi Agent --}}
                                <div>
                                    <dt class="text-gray-500">Versi Agent</dt>
                                    <dd class="font-mono text-gray-800">{{ $asset->agent_version ?? '-' }}</dd>
                                </div>

                                {{-- Terakhir Heartbeat --}}
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500">Terakhir Heartbeat</dt>
                                    <dd class="text-gray-800">
                                        @if ($asset->last_seen_at)
                                            {{ $asset->last_seen_at->format('d M Y H:i:s') }}
                                            <span class="text-xs text-gray-500">
                                                ({{ $asset->last_seen_at->diffForHumans() }})
                                            </span>
                                        @else
                                            Belum pernah
                                        @endif
                                    </dd>
                                </div>

                            </dl>

                            {{-- ============================================================ --}}
                            {{-- LOKASI ASET (MINI MAP) --}}
                            {{-- ============================================================ --}}
                            @if ($asset->last_lat && $asset->last_lng)
                                <div class="mt-6 pt-4 border-t border-gray-100">
                                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Lokasi Terakhir
                                        </h3>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span
                                                class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 font-semibold text-[10px]">
                                                {{ $asset->location_source ?? 'unknown' }}
                                            </span>
                                            <a href="https://www.google.com/maps?q={{ $asset->last_lat }},{{ $asset->last_lng }}"
                                                target="_blank" class="text-indigo-600 hover:underline font-semibold">
                                                Google Maps →
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Mini Map --}}
                                    <div id="assetMiniMap" class="w-full overflow-hidden border border-gray-200"></div>

                                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="font-mono">
                                            {{ number_format($asset->last_lat, 7) }},
                                            {{ number_format($asset->last_lng, 7) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Belum pernah heartbeat --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="text-center py-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-500 font-semibold">Aset ini belum pernah kirim heartbeat</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Pastikan agent sudah terinstall di laptop ini.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- History Pemakai --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800">
                                History Pemakai
                                <span class="text-xs text-gray-500 font-normal">
                                    ({{ $asset->assignments->count() }} record)
                                </span>
                            </h2>
                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                    + Assign ke User
                                </a>
                            @endif
                        </div>

                        @if ($asset->assignments->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum pernah di-assign ke user.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($asset->assignments as $a)
                                    <div
                                        class="flex items-start gap-3 p-3 rounded-xl border
                                        {{ $a->is_active ? 'border-blue-300 bg-blue-50' : 'border-gray-200' }}">
                                        <div class="flex-shrink-0">
                                            @if ($a->user)
                                                <a href="{{ route('users.show', $a->user) }}">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold hover:bg-indigo-700 transition">
                                                        {{ strtoupper(substr($a->user->name, 0, 1)) }}
                                                    </div>
                                                </a>
                                            @else
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-bold">
                                                    ?</div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if ($a->user)
                                                    <a href="{{ route('users.show', $a->user) }}"
                                                        class="font-medium text-indigo-600 hover:underline hover:text-indigo-800">
                                                        {{ $a->user->name }}
                                                    </a>
                                                @else
                                                    <span class="font-medium">-</span>
                                                @endif
                                                @if ($a->is_active)
                                                    <span class="px-2 py-0.5 text-xs rounded bg-blue-600 text-white">
                                                        Sedang Dipakai
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $a->user?->position ?? '-' }}
                                                @if ($a->department)
                                                    — {{ $a->department->name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                📅 {{ $a->assigned_at?->format('d M Y') ?? '-' }}
                                                →
                                                {{ $a->returned_at?->format('d M Y') ?? 'Sekarang' }}
                                                @if ($a->duration_days !== null)
                                                    <span class="text-gray-400">({{ $a->duration_days }} hari)</span>
                                                @endif
                                            </div>
                                            @if ($a->location)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    📍 {{ $a->location->full_name }}
                                                </div>
                                            @endif
                                            @if ($a->condition_on_assign !== null || $a->condition_on_return !== null)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Kondisi: serah {{ $a->condition_on_assign ?? '-' }}%
                                                    @if ($a->condition_on_return !== null)
                                                        → kembali {{ $a->condition_on_return }}%
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($a->notes)
                                                <div class="text-xs text-gray-500 mt-1 italic">{{ $a->notes }}</div>
                                            @endif

                                            @if ($a->is_active)
                                                {{-- 🆕 Tombol buka modal return --}}
                                                <button type="button"
                                                    @click="openReturnForm({{ $a->id }}, '{{ route('siam.assignments.return', $a) }}', '{{ addslashes($a->user?->name ?? 'user ini') }}', '{{ addslashes($asset->brand . ' ' . $asset->model) }}', '{{ $asset->serial_number }}', {{ $asset->condition_percent ?? 100 }})"
                                                    class="mt-2 text-xs px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                                                    Kembalikan Aset
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- History Peminjaman --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-4">
                            History Peminjaman
                            <span class="text-xs text-gray-500 font-normal">({{ $asset->loans->count() }} record)</span>
                        </h2>

                        @if ($asset->loans->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum ada peminjaman.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($asset->loans as $loan)
                                    <li
                                        class="border-l-2 {{ $loan->is_overdue ? 'border-red-500' : 'border-blue-400' }} pl-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($loan->user)
                                                <a href="{{ route('users.show', $loan->user) }}"
                                                    class="font-medium text-sm text-indigo-600 hover:underline hover:text-indigo-800">
                                                    {{ $loan->user->name }}
                                                </a>
                                            @else
                                                <span class="font-medium text-sm">-</span>
                                            @endif
                                            <span
                                                class="px-2 py-0.5 text-xs rounded
                                                {{ $loan->status === 'returned'
                                                    ? 'bg-green-100 text-green-700'
                                                    : ($loan->is_overdue
                                                        ? 'bg-red-100 text-red-700'
                                                        : 'bg-blue-100 text-blue-700') }}">
                                                {{ $loan->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            📅 {{ $loan->loan_date?->format('d M Y') }}
                                            → {{ $loan->due_date?->format('d M Y') }}
                                            @if ($loan->returned_at)
                                                <span class="text-green-600">(kembali
                                                    {{ $loan->returned_at->format('d M Y') }})</span>
                                            @endif
                                        </div>
                                        @if ($loan->purpose)
                                            <div class="text-xs text-gray-600 mt-1">Tujuan: {{ $loan->purpose }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- History Perbaikan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800">
                                History Perbaikan
                                <span class="text-xs text-gray-500 font-normal">({{ $asset->maintenances->count() }}
                                    record)</span>
                            </h2>
                            <a href="{{ route('siam.maintenances.create', ['asset_id' => $asset->id]) }}"
                                class="text-xs px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                                + Catat Perbaikan
                            </a>
                        </div>

                        @if ($asset->maintenances->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum ada perbaikan.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($asset->maintenances as $m)
                                    <li class="border-l-2 border-orange-400 pl-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium">{{ $m->issue }}</span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded
                                                {{ $m->status === 'done'
                                                    ? 'bg-green-100 text-green-700'
                                                    : ($m->status === 'in_progress'
                                                        ? 'bg-blue-100 text-blue-700'
                                                        : ($m->status === 'cancelled'
                                                            ? 'bg-gray-100 text-gray-700'
                                                            : 'bg-yellow-100 text-yellow-700')) }}">
                                                {{ $m->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $m->start_date?->format('d M Y') }}
                                            @if ($m->end_date)
                                                → {{ $m->end_date->format('d M Y') }}
                                            @endif
                                            • {{ $m->type_label }}
                                            @if ($m->vendor)
                                                • {{ $m->vendor->name }}
                                            @endif
                                        </div>
                                        @if ($m->action)
                                            <div class="text-xs text-gray-600 mt-1">Tindakan: {{ $m->action }}</div>
                                        @endif
                                        @if ($m->cost)
                                            <div class="text-xs text-gray-600 mt-1">Biaya: Rp
                                                {{ number_format($m->cost, 0, ',', '.') }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>

                {{-- KOLOM KANAN (1/3) --}}
                <div class="space-y-6">

                    {{-- Pemakai Saat Ini --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Pemakai Saat Ini</h2>
                        @if ($asset->currentUser)
                            <div class="flex items-center gap-3">
                                <a href="{{ route('users.show', $asset->currentUser) }}" class="block shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center text-lg font-bold hover:bg-indigo-700 transition">
                                        {{ $asset->currentUser->initial ?? strtoupper(substr($asset->currentUser->name, 0, 1)) }}
                                    </div>
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('users.show', $asset->currentUser) }}"
                                        class="font-medium truncate block text-indigo-600 hover:underline hover:text-indigo-800">
                                        {{ $asset->currentUser->name }}
                                    </a>
                                    <div class="text-xs text-gray-500 truncate">{{ $asset->currentUser->position }}</div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $asset->currentUser->department?->name }}</div>
                                </div>
                            </div>
                            @if ($asset->currentLocation)
                                <div class="mt-3 pt-3 border-t text-xs text-gray-600">
                                    📍 {{ $asset->currentLocation->full_name }}
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                                    Pindah / Assign Ulang
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic mb-3">Belum dipakai siapa pun.</p>
                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-xs px-3 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg font-medium">
                                    + Assign ke User
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Kepemilikan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Kepemilikan</h2>
                        @if ($asset->ownership)
                            <dl class="text-sm space-y-3">
                                <div>
                                    <dt class="text-gray-500 text-xs">Tipe</dt>
                                    <dd class="font-medium">{{ $asset->ownership_label }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 text-xs">Vendor</dt>
                                    <dd>{{ $asset->ownership->vendor?->name ?? '-' }}</dd>
                                </div>

                                @if ($asset->ownership->contract_number)
                                    <div class="pt-2 border-t">
                                        <dt class="text-gray-500 text-xs">No. Kontrak</dt>
                                        <dd class="font-mono text-xs">{{ $asset->ownership->contract_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 text-xs">Periode Sewa</dt>
                                        <dd class="text-xs">
                                            {{ $asset->ownership->contract_start?->format('d M Y') }}
                                            → {{ $asset->ownership->contract_end?->format('d M Y') }}
                                        </dd>
                                    </div>
                                    @if ($asset->ownership->monthly_cost)
                                        <div>
                                            <dt class="text-gray-500 text-xs">Biaya Sewa / Bulan</dt>
                                            <dd class="text-xs">Rp
                                                {{ number_format($asset->ownership->monthly_cost, 0, ',', '.') }}</dd>
                                        </div>
                                    @endif
                                    @if ($asset->ownership->days_until_expiry !== null)
                                        <div>
                                            <dt class="text-gray-500 text-xs">Sisa Waktu</dt>
                                            <dd
                                                class="{{ $asset->ownership->days_until_expiry < 30 ? 'text-red-600 font-medium' : '' }}">
                                                {{ $asset->ownership->days_until_expiry }} hari
                                            </dd>
                                        </div>
                                    @endif
                                @endif

                                @if ($asset->ownership->invoice_number)
                                    <div class="pt-2 border-t">
                                        <dt class="text-gray-500 text-xs">No. Invoice</dt>
                                        <dd class="font-mono text-xs">{{ $asset->ownership->invoice_number }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada data kepemilikan.</p>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Aksi</h2>
                        <div class="space-y-2">
                            <a href="{{ route('siam.assets.edit', $asset) }}"
                                class="block text-center text-sm px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                                ✏️ Edit Aset
                            </a>

                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.loans.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-sm px-3 py-2 bg-yellow-100 text-yellow-800 hover:bg-yellow-200 rounded-lg font-medium">
                                    📤 Pinjamkan Sementara
                                </a>
                            @endif

                            <form method="POST" action="{{ route('siam.assets.destroy', $asset) }}"
                                @submit.prevent="confirmDelete('{{ $asset->serial_number }}', $event)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full text-center text-sm px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg font-medium">
                                    🗑️ Hapus Aset
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- 🆕 MODAL RETURN ASET (dengan opsi BAP) --}}
        {{-- ============================================================ --}}
        <div x-show="showReturnForm" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showReturnForm = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
                @click.away="showReturnForm = false">

                {{-- HEADER --}}
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 px-6 py-5 text-white shrink-0">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Kembalikan Aset</h3>
                                <p class="text-sm text-white/80">Isi data pengembalian & berita acara</p>
                            </div>
                        </div>
                        <button type="button" @click="showReturnForm = false"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- FORM --}}
                <form x-ref="returnForm" method="POST" class="flex-1 overflow-y-auto p-6 space-y-4">
                    @csrf

                    {{-- Info Aset --}}
                    <div class="p-3 rounded-xl bg-gray-50 border">
                        <div class="text-xs text-gray-500">Aset yang akan dikembalikan</div>
                        <div class="font-semibold text-gray-800" x-text="returnForm.asset_name"></div>
                        <div class="text-xs font-mono text-gray-500" x-text="'SN: ' + returnForm.asset_sn"></div>
                        <div class="text-xs text-gray-500 mt-1" x-text="'Dipinjam oleh: ' + returnForm.user_name"></div>
                    </div>

                    {{-- Tanggal & Kondisi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Kembali <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="returned_at" required
                                value="{{ now()->format('Y-m-d\TH:i') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                            <input type="number" name="condition_on_return" min="0" max="100"
                                :value="returnForm.condition_on_return"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    {{-- Hostname --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Hostname (Opsional)</label>
                        <input type="text" name="hostname" placeholder="Kosongkan kalau tidak berubah"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Baik, minus baret halus di body"
                            class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>

                    {{-- 🆕 Opsi BAP --}}
                    <div x-data="{ buatBA: true }" class="border-t pt-4">
                        <div class="flex items-center gap-3 mb-3">
                            <input type="checkbox" name="buat_berita_acara" value="1" x-model="buatBA" checked
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                            <label class="font-semibold text-gray-700 text-sm cursor-pointer">
                                📄 Buat Berita Acara Pengembalian Otomatis
                            </label>
                        </div>

                        <div x-show="buatBA" x-cloak x-transition
                            class="space-y-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200">

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Pihak Pertama (Penerima Kembali) <span class="text-red-500">*</span>
                                </label>
                                <select name="pihak_pertama_id" :required="buatBA"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                                    <option value="">-- Pilih Pejabat --</option>
                                    @foreach (\App\Models\User::active()->whereIn('role', ['admin'])->orderBy('name')->get() as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} — {{ $p->position ?? 'Staff' }}
                                            ({{ strtoupper($p->role) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jabatan
                                        (Opsional)</label>
                                    <input type="text" name="pihak_pertama_jabatan" placeholder="Auto-fill dari user"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tempat BA</label>
                                    <input type="text" name="tempat_ba" value="Suralaya"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="text-xs text-emerald-700">
                                ℹ️ BAP akan dibuat dengan nomor format
                                <code class="font-mono font-bold">BAP-{{ now()->format('Y') }}-0001</code>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- FOOTER --}}
                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end gap-2 shrink-0">
                    <button type="button" @click="showReturnForm = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Batal
                    </button>
                    <button type="button" @click="submitReturn()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-emerald-500/30">
                        Ya, Kembalikan
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI (GENERIC) --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeConfirm()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-96"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center"
                        :class="{
                            'bg-red-100': confirmType === 'delete',
                            'bg-amber-100': confirmType === 'return',
                        }">
                        <svg x-show="confirmType === 'delete'" xmlns="http://www.w3.org/2000/svg"
                            class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <svg x-show="confirmType === 'return'" xmlns="http://www.w3.org/2000/svg"
                            class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1" x-text="confirmTitle"></h3>
                <p class="text-sm text-gray-500 text-center mb-6" x-html="confirmMessage"></p>

                <div class="flex gap-2">
                    <button type="button" @click="closeConfirm()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="flex-1 px-4 py-2.5 rounded-xl font-semibold transition text-white shadow-lg"
                        :class="{
                            'bg-red-600 hover:bg-red-700 shadow-red-500/30': confirmType === 'delete',
                            'bg-amber-600 hover:bg-amber-700 shadow-amber-500/30': confirmType === 'return',
                        }"
                        x-text="confirmButton"></button>
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
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
        function assetShowManager() {
            return {
                showConfirmModal: false,
                confirmType: '',
                confirmTitle: '',
                confirmMessage: '',
                confirmButton: '',
                pendingForm: null,
                pendingAction: null,

                // 🆕 State modal return
                showReturnForm: false,
                returnForm: {
                    action: '',
                    user_name: '',
                    asset_name: '',
                    asset_sn: '',
                    condition_on_return: 100,
                },

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                // 🆕 Buka modal return + populate
                openReturnForm(assignmentId, actionUrl, userName, assetName, assetSn, condition) {
                    this.returnForm.action = actionUrl;
                    this.returnForm.user_name = userName;
                    this.returnForm.asset_name = assetName;
                    this.returnForm.asset_sn = assetSn;
                    this.returnForm.condition_on_return = condition ?? 100;
                    this.showReturnForm = true;
                },

                // 🆕 Submit form return
                submitReturn() {
                    this.showReturnForm = false;

                    localStorage.setItem('flash_message', 'Aset berhasil dikembalikan.');
                    localStorage.setItem('flash_type', 'success');

                    const form = this.$refs.returnForm;
                    form.action = this.returnForm.action;
                    form.submit();
                },

                confirmReturn(event, userName) {
                    this.confirmType = 'return';
                    this.confirmTitle = 'Kembalikan Aset?';
                    this.confirmMessage =
                        `Aset ini akan dikembalikan dari <strong>${userName}</strong>. Status aset akan kembali menjadi <strong>Tersedia</strong>.`;
                    this.confirmButton = 'Ya, Kembalikan';
                    this.pendingForm = event.target.closest('form');
                    this.pendingAction = 'return';
                    this.showConfirmModal = true;
                },

                confirmDelete(serialNumber, event) {
                    this.confirmType = 'delete';
                    this.confirmTitle = 'Hapus Aset?';
                    this.confirmMessage =
                        `Aset dengan SN <strong>${serialNumber}</strong> akan dihapus permanen. Data yang dihapus tidak bisa dikembalikan.`;
                    this.confirmButton = 'Ya, Hapus';
                    this.pendingForm = event.target.closest('form');
                    this.pendingAction = 'delete';
                    this.showConfirmModal = true;
                },

                executeConfirm() {
                    if (this.pendingForm) {
                        this.pendingForm.submit();
                    }
                    this.closeConfirm();
                },

                closeConfirm() {
                    this.showConfirmModal = false;
                    this.pendingForm = null;
                    this.pendingAction = null;
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                init() {
                    // 🆕 Cek flash message dari localStorage (untuk toast setelah redirect)
                    this.$nextTick(() => {
                        const flashMsg = localStorage.getItem('flash_message');
                        const flashType = localStorage.getItem('flash_type') || 'success';

                        if (flashMsg) {
                            this.showToast(flashMsg, flashType);
                            localStorage.removeItem('flash_message');
                            localStorage.removeItem('flash_type');
                        }

                        @if (session('success'))
                            this.showToast(@json(session('success')), 'success');
                        @endif
                        @if (session('error'))
                            this.showToast(@json(session('error')), 'error');
                        @endif
                    });

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.showReturnForm) this.showReturnForm = false;
                            else if (this.showConfirmModal) this.closeConfirm();
                        }
                    });

                    // 🆕 Init Mini Map
                    @if ($asset->last_lat && $asset->last_lng)
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.initAssetMiniMap();
                            }, 300);
                        });
                    @endif
                },

                // 🆕 Init Asset Mini Map
                initAssetMiniMap() {
                    const mapEl = document.getElementById('assetMiniMap');
                    if (!mapEl) return;

                    const lat = {{ $asset->last_lat ?? 0 }};
                    const lng = {{ $asset->last_lng ?? 0 }};

                    const map = L.map('assetMiniMap').setView([lat, lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    const statusColor = '{{ $asset->agent_status_color }}';
                    const color = statusColor === 'green' ? '#10b981' :
                        statusColor === 'yellow' ? '#f59e0b' :
                        statusColor === 'red' ? '#ef4444' : '#6b7280';

                    const icon = L.divIcon({
                        className: '',
                        html: `<div style="
                            width: 36px; height: 36px;
                            background: ${color};
                            border: 4px solid white;
                            border-radius: 50%;
                            box-shadow: 0 3px 12px rgba(0,0,0,0.5);
                        "></div>`,
                        iconSize: [36, 36],
                        iconAnchor: [18, 18],
                    });

                    L.marker([lat, lng], {
                            icon: icon
                        }).addTo(map)
                        .bindPopup(`
                            <div style="font-family: system-ui;">
                                <b>{{ $asset->brand }} {{ $asset->model }}</b><br>
                                <span style="font-family: monospace; color: #6366f1;">{{ $asset->asset_code }}</span><br>
                                <small>{{ $asset->hostname }}</small>
                            </div>
                        `)
                        .openPopup();

                    setTimeout(() => map.invalidateSize(), 300);
                }
            }
        }
    </script>
@endpush
