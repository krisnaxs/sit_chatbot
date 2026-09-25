<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detail Transaksi — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail Transaksi" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-3xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        {{-- BREADCRUMB --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('siam.consumable-transactions.index') }}" class="hover:text-pink-600 transition">
                Transaksi Konsumable
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">#{{ $transaction->id }}</span>
        </nav>

        {{-- HEADER CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600
                                flex items-center justify-center shadow-lg shadow-pink-500/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Detail Transaksi</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ID: <span class="font-mono font-semibold">#{{ $transaction->id }}</span>
                        </p>
                    </div>
                </div>

                @php
                    $typeColor = match ($transaction->type) {
                        'in' => 'bg-green-100 text-green-700',
                        'out' => 'bg-red-100 text-red-700',
                        'return' => 'bg-blue-100 text-blue-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider {{ $typeColor }}">
                    {{ $transaction->type_label }}
                </span>
            </div>

            {{-- DESKRIPSI --}}
            <div class="p-4 rounded-xl bg-gradient-to-br from-pink-50 to-rose-50 border border-pink-100 mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Transaksi</p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $transaction->consumable?->name }}
                    <span class="text-sm font-normal text-gray-500">
                        — {{ $transaction->quantity }} {{ $transaction->consumable?->unit }}
                    </span>
                </p>
            </div>

            {{-- INFO GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-pink-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Tanggal</p>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $transaction->transaction_date?->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">User</p>
                        <p class="text-sm font-bold text-gray-900 truncate">
                            {{ $transaction->user?->name ?? 'Guest / System' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL LAINNYA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
            <h2 class="font-bold text-gray-900 mb-4">Info Lengkap</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Item</div>
                    <div class="font-medium">{{ $transaction->consumable?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Quantity</div>
                    <div class="font-medium">{{ $transaction->quantity }} {{ $transaction->consumable?->unit }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Lokasi</div>
                    <div class="font-medium">{{ $transaction->location?->full_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Aset Terkait</div>
                    <div class="font-medium">{{ $transaction->asset?->serial_number ?? '-' }}</div>
                </div>
                <div class="sm:col-span-2">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Keperluan</div>
                    <div class="font-medium">{{ $transaction->purpose ?? '-' }}</div>
                </div>
                <div class="sm:col-span-2">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Catatan</div>
                    <div class="font-medium">{{ $transaction->notes ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Diminta Oleh</div>
                    <div class="font-medium">{{ $transaction->requestedBy?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Disetujui Oleh</div>
                    <div class="font-medium">{{ $transaction->approvedBy?->name ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('siam.consumable-transactions.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                       border border-gray-300 text-gray-700 font-semibold text-sm
                       hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>

    </div>

    <script>
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
