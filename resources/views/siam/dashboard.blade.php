<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Dashboard SIAM" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="space-y-6">

            {{-- HEADER --}}
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard SIAM</h1>
                <p class="text-sm text-gray-500">Ringkasan & statistik aset IT</p>
            </div>

            {{-- ============================================================ --}}
            {{-- RINGKASAN ASET --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-800">Ringkasan Aset</h2>
                        <p class="text-xs text-gray-500">Total {{ $assetStats['total'] }} unit aset</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <a href="{{ route('siam.assets.index') }}"
                        class="rounded-xl p-3 bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                        <div class="text-[10px] text-gray-500 uppercase font-semibold">Total</div>
                        <div class="text-2xl font-bold text-gray-800">{{ $assetStats['total'] }}</div>
                        <div class="text-[10px] text-gray-400">unit</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'available']) }}"
                        class="rounded-xl p-3 bg-emerald-50 border border-emerald-200 hover:border-emerald-400 transition">
                        <div class="text-[10px] text-emerald-600 uppercase font-semibold">Tersedia</div>
                        <div class="text-2xl font-bold text-emerald-700">{{ $assetStats['available'] }}</div>
                        <div class="text-[10px] text-emerald-500">siap dipakai</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'in_use']) }}"
                        class="rounded-xl p-3 bg-blue-50 border border-blue-200 hover:border-blue-400 transition">
                        <div class="text-[10px] text-blue-600 uppercase font-semibold">Dipakai</div>
                        <div class="text-2xl font-bold text-blue-700">{{ $assetStats['in_use'] }}</div>
                        <div class="text-[10px] text-blue-500">user pegang</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'loaned']) }}"
                        class="rounded-xl p-3 bg-amber-50 border border-amber-200 hover:border-amber-400 transition">
                        <div class="text-[10px] text-amber-600 uppercase font-semibold">Dipinjam</div>
                        <div class="text-2xl font-bold text-amber-700">{{ $assetStats['loaned'] }}</div>
                        <div class="text-[10px] text-amber-500">sementara</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'maintenance']) }}"
                        class="rounded-xl p-3 bg-orange-50 border border-orange-200 hover:border-orange-400 transition">
                        <div class="text-[10px] text-orange-600 uppercase font-semibold">Perbaikan</div>
                        <div class="text-2xl font-bold text-orange-700">{{ $assetStats['maintenance'] }}</div>
                        <div class="text-[10px] text-orange-500">maintenance</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'retired']) }}"
                        class="rounded-xl p-3 bg-gray-100 border border-gray-300 hover:border-gray-500 transition">
                        <div class="text-[10px] text-gray-600 uppercase font-semibold">Pensiun</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $assetStats['retired'] }}</div>
                        <div class="text-[10px] text-gray-500">tidak dipakai</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['status' => 'lost']) }}"
                        class="rounded-xl p-3 bg-red-50 border border-red-200 hover:border-red-400 transition">
                        <div class="text-[10px] text-red-600 uppercase font-semibold">Hilang</div>
                        <div class="text-2xl font-bold text-red-700">{{ $assetStats['lost'] }}</div>
                        <div class="text-[10px] text-red-500">lost</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['ownership_type' => 'owned']) }}"
                        class="rounded-xl p-3 bg-green-50 border border-green-200 hover:border-green-400 transition">
                        <div class="text-[10px] text-green-600 uppercase font-semibold">Hak Milik</div>
                        <div class="text-2xl font-bold text-green-700">{{ $assetStats['owned'] }}</div>
                        <div class="text-[10px] text-green-500">owned</div>
                    </a>

                    <a href="{{ route('siam.assets.index', ['ownership_type' => 'leased']) }}"
                        class="rounded-xl p-3 bg-orange-50 border border-orange-200 hover:border-orange-400 transition">
                        <div class="text-[10px] text-orange-600 uppercase font-semibold">Sewa</div>
                        <div class="text-2xl font-bold text-orange-700">{{ $assetStats['leased'] }}</div>
                        <div class="text-[10px] text-orange-500">leased</div>
                    </a>

                    <div class="rounded-xl p-3 bg-indigo-50 border border-indigo-200">
                        <div class="text-[10px] text-indigo-600 uppercase font-semibold">Nilai Aset</div>
                        <div class="text-lg font-bold text-indigo-700">
                            Rp {{ number_format($assetValues['total_purchase'], 0, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-indigo-500">total pembelian</div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- ALERT / WARNING --}}
            {{-- ============================================================ --}}
            @php
                $hasAlerts =
                    $overdueLoans->count() > 0 ||
                    $lowStockConsumables->count() > 0 ||
                    $expiringContracts->count() > 0 ||
                    $expiringWarranties->count() > 0;
            @endphp

            @if ($hasAlerts)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">Peringatan</h2>
                            <p class="text-xs text-gray-500">Perlu tindakan segera</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                        @if ($overdueLoans->count() > 0)
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <p class="font-semibold text-sm text-red-800">
                                        {{ $overdueLoans->count() }} Peminjaman Terlambat
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    @foreach ($overdueLoans->take(3) as $loan)
                                        <div class="text-xs text-red-700">
                                            • {{ $loan->asset?->serial_number }}
                                            @if ($loan->asset?->hostname)
                                                <span
                                                    class="font-mono text-red-500">({{ $loan->asset->hostname }})</span>
                                            @endif
                                            — {{ $loan->user?->name }}
                                            <span class="text-red-500">
                                                ({{ $loan->due_date?->diffForHumans() }})
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($overdueLoans->count() > 3)
                                        <div class="text-xs text-red-500 italic">
                                            +{{ $overdueLoans->count() - 3 }} lainnya
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('siam.loans.index', ['status' => 'borrowed']) }}"
                                    class="inline-block mt-2 text-xs font-semibold text-red-700 hover:underline">
                                    Lihat semua →
                                </a>
                            </div>
                        @endif

                        @if ($lowStockConsumables->count() > 0)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <p class="font-semibold text-sm text-amber-800">
                                        {{ $lowStockConsumables->count() }} Konsumable Stok Rendah
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    @foreach ($lowStockConsumables->take(3) as $c)
                                        <div class="text-xs text-amber-700">
                                            • {{ $c->name }}
                                            <span class="text-amber-500">
                                                ({{ $c->stock_available }}/{{ $c->stock_minimum }}
                                                {{ $c->unit }})
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($lowStockConsumables->count() > 3)
                                        <div class="text-xs text-amber-500 italic">
                                            +{{ $lowStockConsumables->count() - 3 }} lainnya
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('siam.consumables.index', ['low_stock' => 1]) }}"
                                    class="inline-block mt-2 text-xs font-semibold text-amber-700 hover:underline">
                                    Lihat semua →
                                </a>
                            </div>
                        @endif

                        @if ($expiringContracts->count() > 0)
                            <div class="rounded-xl border border-orange-200 bg-orange-50 p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    <p class="font-semibold text-sm text-orange-800">
                                        {{ $expiringContracts->count() }} Kontrak Sewa Berakhir < 30 Hari </p>
                                </div>
                                <div class="space-y-1">
                                    @foreach ($expiringContracts->take(3) as $asset)
                                        <div class="text-xs text-orange-700">
                                            • {{ $asset->serial_number }}
                                            @if ($asset->hostname)
                                                <span
                                                    class="font-mono text-orange-500">({{ $asset->hostname }})</span>
                                            @endif
                                            — {{ $asset->brand }} {{ $asset->model }}
                                            <span class="text-orange-500">
                                                ({{ $asset->ownership?->contract_end?->diffForHumans() }})
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($expiringContracts->count() > 3)
                                        <div class="text-xs text-orange-500 italic">
                                            +{{ $expiringContracts->count() - 3 }} lainnya
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($expiringWarranties->count() > 0)
                            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <p class="font-semibold text-sm text-yellow-800">
                                        {{ $expiringWarranties->count() }} Garansi Berakhir < 60 Hari </p>
                                </div>
                                <div class="space-y-1">
                                    @foreach ($expiringWarranties->take(3) as $asset)
                                        <div class="text-xs text-yellow-700">
                                            • {{ $asset->serial_number }}
                                            @if ($asset->hostname)
                                                <span
                                                    class="font-mono text-yellow-500">({{ $asset->hostname }})</span>
                                            @endif
                                            — {{ $asset->brand }} {{ $asset->model }}
                                            <span class="text-yellow-500">
                                                ({{ $asset->warranty_expire?->diffForHumans() }})
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($expiringWarranties->count() > 3)
                                        <div class="text-xs text-yellow-500 italic">
                                            +{{ $expiringWarranties->count() - 3 }} lainnya
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-800">Semua Aman! ✨</p>
                    <p class="text-sm text-gray-500 mt-1">Tidak ada peringatan yang perlu ditindaklanjuti</p>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- GRAFIK --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">Aset per Kategori</h2>
                            <p class="text-xs text-gray-500">Distribusi aset</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="chartCategory"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">Tren Perbaikan</h2>
                            <p class="text-xs text-gray-500">6 bulan terakhir</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="chartMaintenance"></canvas>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- AKTIVITAS TERBARU --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            Perbaikan Terbaru
                        </h3>
                        <a href="{{ route('siam.maintenances.index') }}"
                            class="text-xs text-orange-600 hover:underline">Semua →</a>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentMaintenances as $m)
                            <a href="{{ route('siam.maintenances.show', $m) }}"
                                class="block p-2 rounded-lg hover:bg-orange-50 transition">
                                <div class="text-xs font-semibold text-gray-800 truncate">
                                    {{ $m->asset?->serial_number }}
                                    @if ($m->asset?->hostname)
                                        <span class="font-mono text-gray-500 font-normal">
                                            ({{ $m->asset->hostname }})
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-500 truncate">
                                    {{ Str::limit($m->issue, 40) }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ $m->created_at?->diffForHumans() }}
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada perbaikan</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Peminjaman Terbaru
                        </h3>
                        <a href="{{ route('siam.loans.index') }}"
                            class="text-xs text-amber-600 hover:underline">Semua →</a>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentLoans as $loan)
                            <a href="{{ route('siam.loans.show', $loan) }}"
                                class="block p-2 rounded-lg hover:bg-amber-50 transition">
                                <div class="text-xs font-semibold text-gray-800 truncate">
                                    {{ $loan->asset?->serial_number }}
                                    @if ($loan->asset?->hostname)
                                        <span class="font-mono text-gray-500 font-normal">
                                            ({{ $loan->asset->hostname }})
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-500 truncate">
                                    {{ $loan->user?->name ?? 'Tanpa peminjam' }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ $loan->created_at?->diffForHumans() }}
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada peminjaman</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Serah Terima Terbaru
                        </h3>
                        <a href="{{ route('siam.assignments.index') }}"
                            class="text-xs text-teal-600 hover:underline">Semua →</a>
                    </div>
                    <div class="space-y-2">
                        @forelse ($recentAssignments as $item)
                            @php
                                $isReturn = $item['type'] === 'kembali';
                                $a = $item['model'];
                            @endphp
                            <div class="p-2 rounded-lg hover:bg-teal-50 transition">
                                <div class="flex items-center gap-2 mb-0.5">
                                    {{-- 🆕 FLAG SERAH / KEMBALI --}}
                                    @if ($isReturn)
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-100 text-blue-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Kembali
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Serah
                                        </span>
                                    @endif

                                    <div class="text-xs font-semibold text-gray-800 truncate">
                                        {{ $a->asset?->serial_number }}
                                        @if ($a->hostname)
                                            <span class="font-mono text-gray-500 font-normal">
                                                ({{ $a->hostname }})
                                            </span>
                                        @elseif ($a->asset?->hostname)
                                            <span class="font-mono text-gray-400 font-normal">
                                                ({{ $a->asset->hostname }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-500 truncate">
                                    {{ $a->user?->name ?? '-' }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ $item['at']?->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada serah terima</p>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- KONSUMABLE + USER/VENDOR --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-lime-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">Konsumable</h2>
                            <p class="text-xs text-gray-500">Status stok</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('siam.consumables.index') }}"
                            class="rounded-xl p-3 bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Total Item</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $consumableStats['total'] }}</div>
                        </a>
                        <a href="{{ route('siam.consumables.index', ['low_stock' => 1]) }}"
                            class="rounded-xl p-3 bg-amber-50 border border-amber-200 hover:border-amber-400 transition">
                            <div class="text-[10px] text-amber-600 uppercase font-semibold">Low Stock</div>
                            <div class="text-2xl font-bold text-amber-700">{{ $consumableStats['low_stock'] }}</div>
                        </a>
                        <a href="{{ route('siam.consumables.index', ['low_stock' => 1]) }}"
                            class="rounded-xl p-3 bg-red-50 border border-red-200 hover:border-red-400 transition">
                            <div class="text-[10px] text-red-600 uppercase font-semibold">Habis</div>
                            <div class="text-2xl font-bold text-red-700">{{ $consumableStats['out_stock'] }}</div>
                        </a>
                        <a href="{{ route('siam.consumable-transactions.index') }}"
                            class="rounded-xl p-3 bg-pink-50 border border-pink-200 hover:border-pink-400 transition">
                            <div class="text-[10px] text-pink-600 uppercase font-semibold">Transaksi Bln Ini</div>
                            <div class="text-2xl font-bold text-pink-700">
                                {{ $consumableStats['transactions_this_month'] }}</div>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">User & Vendor</h2>
                            <p class="text-xs text-gray-500">Statistik pengguna</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl p-3 bg-indigo-50 border border-indigo-200">
                            <div class="text-[10px] text-indigo-600 uppercase font-semibold">Total User</div>
                            <div class="text-2xl font-bold text-indigo-700">{{ $userStats['total'] }}</div>
                        </div>
                        <div class="rounded-xl p-3 bg-emerald-50 border border-emerald-200">
                            <div class="text-[10px] text-emerald-600 uppercase font-semibold">User Aktif</div>
                            <div class="text-2xl font-bold text-emerald-700">{{ $userStats['active'] }}</div>
                        </div>
                        <div class="rounded-xl p-3 bg-blue-50 border border-blue-200">
                            <div class="text-[10px] text-blue-600 uppercase font-semibold">Pegang Aset</div>
                            <div class="text-2xl font-bold text-blue-700">{{ $userStats['with_assets'] }}</div>
                        </div>
                        <div class="rounded-xl p-3 bg-orange-50 border border-orange-200">
                            <div class="text-[10px] text-orange-600 uppercase font-semibold">Vendor Aktif</div>
                            <div class="text-2xl font-bold text-orange-700">{{ $vendorStats['active'] }}</div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TOP STATISTIK --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3
                        class="font-bold text-sm text-gray-800 mb-3 pb-3 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Top 5 Pemakai Aset
                    </h3>
                    <div class="space-y-2">
                        @forelse ($topUsers as $i => $u)
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-gray-800 truncate">{{ $u->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">{{ $u->position ?? '-' }}</div>
                                </div>
                                <span class="text-sm font-bold text-blue-700">{{ $u->current_assets_count }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada data</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3
                        class="font-bold text-sm text-gray-800 mb-3 pb-3 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        Top 5 Aset Sering Diperbaiki
                    </h3>
                    <div class="space-y-2">
                        @forelse ($topMaintenancedAssets as $i => $a)
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-gray-800 truncate">
                                        {{ $a->serial_number }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 truncate">
                                        {{ $a->brand }} {{ $a->model }}
                                        @if ($a->hostname)
                                            • <span class="font-mono">{{ $a->hostname }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-orange-700">{{ $a->maintenances_count }}x</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada data</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3
                        class="font-bold text-sm text-gray-800 mb-3 pb-3 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        Top 5 Brand
                    </h3>
                    <div class="space-y-2">
                        @forelse ($topBrands as $i => $b)
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-gray-800 truncate">{{ $b->brand }}
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-violet-700">{{ $b->total }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-4">Belum ada data</p>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- ACTIVITY LOG TERBARU --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                    <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                        Activity Log Terbaru
                    </h3>
                    <a href="{{ route('activity.index') }}" class="text-xs text-slate-600 hover:underline">Semua
                        →</a>
                </div>

                <div class="space-y-2">
                    @forelse ($recentActivities as $log)
                        @php
                            $eventColor = match ($log->event) {
                                'created' => 'bg-emerald-100 text-emerald-700',
                                'updated' => 'bg-blue-100 text-blue-700',
                                'deleted' => 'bg-red-100 text-red-700',
                                'returned' => 'bg-cyan-100 text-cyan-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                                'cleared' => 'bg-orange-100 text-orange-700',
                                'clicked' => 'bg-violet-100 text-violet-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0 text-xs font-bold text-slate-700">
                                {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $eventColor }}">
                                        {{ $log->event ?? 'log' }}
                                    </span>
                                    <span class="text-xs font-semibold text-gray-800 truncate">
                                        {{ $log->description }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-[10px] text-gray-400 flex-wrap">
                                    <span>{{ $log->causer?->name ?? 'System' }}</span>
                                    <span>•</span>
                                    <span>{{ $log->created_at?->diffForHumans() }}</span>
                                    @if ($log->properties && $log->properties->has('ip'))
                                        <span>•</span>
                                        <span class="font-mono">{{ $log->properties->get('ip') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-4">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        const categoryCtx = document.getElementById('chartCategory');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: @json($categoryLabels),
                    datasets: [{
                        label: 'Jumlah Aset',
                        data: @json($categoryData),
                        backgroundColor: 'rgba(6, 182, 212, 0.7)',
                        borderColor: 'rgb(6, 182, 212)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        const maintenanceCtx = document.getElementById('chartMaintenance');
        if (maintenanceCtx) {
            new Chart(maintenanceCtx, {
                type: 'line',
                data: {
                    labels: @json($maintenanceLabels),
                    datasets: [{
                        label: 'Jumlah Perbaikan',
                        data: @json($maintenanceData),
                        backgroundColor: 'rgba(249, 115, 22, 0.2)',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgb(249, 115, 22)',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        function pageLayout() {
            return {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                init() {
                    window.addEventListener('sidebar-toggled', (e) => {
                        this.collapsed = e.detail.collapsed;
                    });
                }
            }
        }
    </script>

</body>

</html>
