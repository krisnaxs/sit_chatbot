<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ $asset->asset_code ?? $asset->serial_number }} — SIAM</title>

    <link rel="icon" href="{{ asset('images/plnip.png') }}" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .pulse-dot {
            animation: pulse-dot 1.5s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen antialiased">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- HEADER --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 h-14 flex items-center gap-3">
            <img src="{{ asset('images/plnip.png') }}" alt="Logo" class="h-8">

            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate leading-tight">SIAM</p>
                <p class="text-[10px] text-gray-500 truncate leading-tight">
                    Sistem Informasi Aset Manajemen
                </p>
            </div>

            <span
                class="inline-flex items-center gap-1 px-2 py-1 rounded-full
                         bg-emerald-100 text-emerald-700 text-[10px] font-bold shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Verified
            </span>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- CONTENT --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <main class="max-w-2xl mx-auto p-4 pb-12">

        {{-- HEADER ASET --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl font-bold text-gray-800 truncate">
                        {{ $asset->brand }} {{ $asset->model }}
                    </h1>
                    <p class="text-sm text-gray-500 font-mono truncate mt-0.5">
                        {{ $asset->asset_code ?? '-' }}
                    </p>
                </div>

                @php
                    $statusMap = [
                        'available' => ['bg-emerald-100 text-emerald-700 border-emerald-200', 'Tersedia'],
                        'in_use' => ['bg-blue-100 text-blue-700 border-blue-200', 'Dipakai'],
                        'loaned' => ['bg-amber-100 text-amber-700 border-amber-200', 'Dipinjam'],
                        'maintenance' => ['bg-orange-100 text-orange-700 border-orange-200', 'Perbaikan'],
                        'retired' => ['bg-gray-100 text-gray-700 border-gray-200', 'Pensiun'],
                        'lost' => ['bg-red-100 text-red-700 border-red-200', 'Hilang'],
                    ];
                    [$cls, $label] = $statusMap[$asset->status] ?? [
                        'bg-gray-100 text-gray-700 border-gray-200',
                        $asset->status,
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0 border {{ $cls }}">
                    {{ $label }}
                </span>
            </div>

            @if ($asset->condition_percent !== null)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-gray-500 font-semibold">Kondisi Aset</span>
                        <span class="font-bold text-gray-800">{{ $asset->condition_percent }}%</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        @php
                            $cond = $asset->condition_percent;
                            $condColor = $cond >= 80 ? 'bg-emerald-500' : ($cond >= 50 ? 'bg-amber-500' : 'bg-red-500');
                        @endphp
                        <div class="h-full {{ $condColor }} transition-all" style="width: {{ $cond }}%">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- FOTO ASET --}}
        @if ($asset->photo_path)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 mb-4">
                <img src="{{ Storage::disk('public')->url($asset->photo_path) }}" alt="Foto Aset"
                    class="w-full rounded-xl object-cover max-h-64">
            </div>
        @endif

        {{-- DETAIL ASET --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <h2 class="font-bold text-gray-800 mb-3 pb-2 border-b border-gray-100 flex items-center gap-2 text-sm">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                Detail Aset
            </h2>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500 shrink-0">Serial Number</dt>
                    <dd class="font-mono font-semibold text-gray-800 text-right break-all">
                        {{ $asset->serial_number }}
                    </dd>
                </div>

                @if ($asset->hostname)
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 shrink-0">Hostname</dt>
                        <dd class="font-mono font-semibold text-gray-800 text-right">{{ $asset->hostname }}</dd>
                    </div>
                @endif

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500 shrink-0">Kategori</dt>
                    <dd class="font-semibold text-gray-800 text-right">{{ $asset->category?->name ?? '-' }}</dd>
                </div>

                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500 shrink-0">Kepemilikan</dt>
                    <dd class="font-semibold text-gray-800 text-right">
                        {{ $asset->ownership_type === 'owned' ? 'Hak Milik' : 'Sewa' }}
                    </dd>
                </div>

                @if ($asset->purchase_date)
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 shrink-0">Tgl. Perolehan</dt>
                        <dd class="font-semibold text-gray-800 text-right">
                            {{ $asset->purchase_date->format('d M Y') }}
                        </dd>
                    </div>
                @endif

                @if ($asset->os)
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 shrink-0">OS</dt>
                        <dd class="font-semibold text-gray-800 text-right">{{ $asset->os }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- PEMAKAI SAAT INI --}}
        @if ($asset->currentUser || $asset->currentLocation)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-3 pb-2 border-b border-gray-100 flex items-center gap-2 text-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Pemakai Saat Ini
                </h2>

                <div class="space-y-3">
                    @if ($asset->currentUser)
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                        flex items-center justify-center text-white font-bold text-base shrink-0">
                                {{ strtoupper(substr($asset->currentUser->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-gray-800 truncate">
                                    {{ $asset->currentUser->name }}
                                </div>
                                @if ($asset->currentUser->position)
                                    <div class="text-xs text-gray-500 truncate">{{ $asset->currentUser->position }}
                                    </div>
                                @endif
                                @if ($asset->currentUser->department)
                                    <div class="text-xs text-gray-400 truncate">
                                        {{ $asset->currentUser->department->name }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($asset->currentLocation)
                        <div
                            class="flex items-start gap-3 {{ $asset->currentUser ? 'pt-3 border-t border-gray-100' : '' }}">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs text-gray-500 font-semibold">Lokasi</div>
                                <div class="text-sm text-gray-800 truncate">{{ $asset->currentLocation->full_name }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- RIWAYAT PERBAIKAN --}}
        @if ($asset->maintenances->count())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-3 pb-2 border-b border-gray-100 flex items-center gap-2 text-sm">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    Perbaikan Terakhir
                </h2>
                <div class="space-y-3">
                    @foreach ($asset->maintenances as $m)
                        <div class="text-xs border-l-2 border-orange-300 pl-3">
                            <div class="font-semibold text-gray-800">{{ $m->issue }}</div>
                            <div class="text-gray-500 mt-0.5">
                                {{ $m->start_date?->format('d M Y') }}
                                @if ($m->vendor)
                                    — {{ $m->vendor->name }}
                                @endif
                                @if ($m->status === 'open')
                                    <span
                                        class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-bold uppercase">
                                        Open
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TIMESTAMP --}}
        <div class="text-center mt-6">
            <div
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot"></span>
                <span class="text-[10px] text-gray-500 font-medium">
                    Data per {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </span>
            </div>
        </div>

    </main>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- FOOTER --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <footer class="text-center pb-6">
        <p class="text-[10px] text-gray-400">
            © {{ now()->year }} SIAM — Sistem Informasi Aset Manajemen
        </p>
    </footer>

</body>

</html>
