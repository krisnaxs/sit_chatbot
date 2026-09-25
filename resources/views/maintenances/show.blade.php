<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detail Perbaikan — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="{
    showCompleteModal: {{ request('complete') ? 'true' : 'false' }}
}">

    <x-header title="Detail Perbaikan" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-4xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        {{-- BREADCRUMB --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('siam.maintenances.index') }}" class="hover:text-orange-600 transition">
                Perbaikan Aset
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">#{{ $maintenance->id }}</span>
        </nav>

        {{-- SUCCESS / ERROR --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">

            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600
                                flex items-center justify-center shadow-lg shadow-orange-500/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Detail Perbaikan</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ID: <span class="font-mono font-semibold">#{{ $maintenance->id }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    @php
                        $typeColor = match ($maintenance->type) {
                            'preventive' => 'bg-blue-100 text-blue-700',
                            'corrective' => 'bg-orange-100 text-orange-700',
                            'upgrade' => 'bg-violet-100 text-violet-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                        $statusColor = match ($maintenance->status) {
                            'open' => 'bg-yellow-100 text-yellow-700',
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'done' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-gray-100 text-gray-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider {{ $typeColor }}">
                        {{ $maintenance->type_label ?? ucfirst($maintenance->type) }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider {{ $statusColor }}">
                        {{ $maintenance->status_label ?? ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                    </span>
                </div>
            </div>

            {{-- ASSET INFO + PEMAKAI --}}
            <div class="p-4 rounded-xl bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-100 mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Aset</p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $maintenance->asset?->brand }} {{ $maintenance->asset?->model }}
                </p>
                <p class="text-sm text-gray-500 font-mono">
                    SN: {{ $maintenance->asset?->serial_number }}
                </p>

                {{-- PEMAKAI ASET --}}
                @if ($maintenance->asset?->currentUser)
                    <div class="mt-3 pt-3 border-t border-orange-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Sedang Dipakai
                            Oleh</p>
                        <p class="font-semibold text-gray-900">
                            👤 {{ $maintenance->asset->currentUser->name }}
                        </p>
                        @if ($maintenance->asset->currentUser->position)
                            <p class="text-xs text-gray-500">
                                {{ $maintenance->asset->currentUser->position }}
                                @if ($maintenance->asset->currentUser->department)
                                    • {{ $maintenance->asset->currentUser->department->name }}
                                @endif
                            </p>
                        @endif
                    </div>
                @else
                    <div class="mt-3 pt-3 border-t border-orange-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Sedang Dipakai
                            Oleh</p>
                        <p class="text-sm text-gray-500 italic">Tidak ada pemakai</p>
                    </div>
                @endif

                @if ($maintenance->asset)
                    <a href="{{ route('siam.assets.show', $maintenance->asset) }}"
                        class="inline-flex items-center gap-1 mt-3 text-xs text-indigo-600 hover:underline font-semibold">
                        Lihat detail aset →
                    </a>
                @endif
            </div>

            {{-- INFO GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Masalah</div>
                    <div class="font-medium">{{ $maintenance->issue ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Tindakan</div>
                    <div class="font-medium">{{ $maintenance->action ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Vendor</div>
                    <div class="font-medium">
                        {{ $maintenance->vendor?->name ?? 'Internal (IT)' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Teknisi</div>
                    <div class="font-medium">{{ $maintenance->technician ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Biaya</div>
                    <div class="font-medium">
                        {{ $maintenance->cost ? 'Rp ' . number_format($maintenance->cost, 0, ',', '.') : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Tanggal</div>
                    <div class="font-medium">
                        {{ $maintenance->start_date?->format('d M Y') ?? '-' }}
                        @if ($maintenance->end_date)
                            → {{ $maintenance->end_date->format('d M Y') }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Kondisi Sebelum</div>
                    <div class="font-medium">
                        {{ $maintenance->condition_before !== null ? $maintenance->condition_before . '%' : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Kondisi Setelah</div>
                    <div class="font-medium">
                        {{ $maintenance->condition_after !== null ? $maintenance->condition_after . '%' : '-' }}</div>
                </div>
                @if ($maintenance->notes)
                    <div class="sm:col-span-2">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">Catatan</div>
                        <div class="font-medium">{{ $maintenance->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <a href="{{ route('siam.maintenances.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                       border border-gray-300 text-gray-700 font-semibold text-sm
                       hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>

            @if (in_array($maintenance->status, ['open', 'in_progress']))
                <a href="{{ route('siam.maintenances.edit', $maintenance) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-violet-600 hover:bg-violet-700 text-white font-semibold text-sm
                           shadow-lg shadow-violet-500/30 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>

                <button type="button" @click="showCompleteModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-green-600 hover:bg-green-700 text-white font-semibold text-sm
                           shadow-lg shadow-green-500/30 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tandai Selesai
                </button>
            @endif

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('siam.maintenances.destroy', $maintenance) }}"
                    onsubmit="return confirm('Yakin ingin menghapus data perbaikan ini?')" class="ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                               bg-red-600 hover:bg-red-700 text-white font-semibold text-sm
                               shadow-lg shadow-red-500/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus
                    </button>
                </form>
            @endif
        </div>

    </div>

    {{-- MODAL TANDAI SELESAI --}}
    <div x-show="showCompleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCompleteModal = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            @click.away="showCompleteModal = false">

            <div class="bg-gradient-to-br from-green-500 to-emerald-600 px-6 py-5 text-white shrink-0">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Tandai Perbaikan Selesai</h3>
                            <p class="text-sm text-white/80">Isi hasil perbaikan untuk menutup tiket ini</p>
                        </div>
                    </div>
                    <button type="button" @click="showCompleteModal = false"
                        class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('siam.maintenances.complete', $maintenance) }}"
                class="flex-1 overflow-y-auto">
                @csrf

                <div class="p-6 space-y-4">

                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Aset</div>
                        <div class="font-semibold text-gray-800">
                            {{ $maintenance->asset?->brand }} {{ $maintenance->asset?->model }}
                        </div>
                        <div class="text-xs text-gray-500 font-mono">SN: {{ $maintenance->asset?->serial_number }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Kondisi Setelah Perbaikan (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="condition_after" required min="0" max="100"
                            value="{{ old('condition_after', $maintenance->condition_after ?? ($maintenance->condition_before ?? 100)) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-400 mt-1">Kondisi aset setelah diperbaiki</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan Perbaikan</label>
                        <textarea name="action" rows="2" placeholder="Contoh: Ganti keyboard baru"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">{{ old('action', $maintenance->action) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Teknisi</label>
                            <input type="text" name="technician" placeholder="Nama teknisi"
                                value="{{ old('technician', $maintenance->technician) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya Akhir (Rp)</label>
                            <input type="number" name="cost" min="0"
                                value="{{ old('cost', $maintenance->cost ?? 0) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" required
                            value="{{ old('end_date', now()->format('Y-m-d')) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">{{ old('notes', $maintenance->notes) }}</textarea>
                    </div>

                </div>

                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end gap-2 shrink-0">
                    <button type="button" @click="showCompleteModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition
                               shadow-lg shadow-green-500/30">
                        ✓ Tandai Selesai
                    </button>
                </div>
            </form>
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

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
