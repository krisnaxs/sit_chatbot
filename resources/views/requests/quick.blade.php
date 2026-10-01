<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengajuan Cepat — SIAM</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-100 font-sans">

    {{-- HEADER SIMPLE --}}
    <div class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/plnip.png') }}" class="h-8" alt="Logo">
                <div>
                    <p class="font-bold text-gray-800 text-sm">SIAM</p>
                    <p class="text-[10px] text-gray-500">Pengajuan Cepat</p>
                </div>
            </a>

            @auth
                <div class="flex items-center gap-2">
                    <div class="text-right hidden sm:block">
                        <p class="font-semibold text-gray-800 text-xs">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-500">{{ auth()->user()->role_label }}</p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center text-white font-bold text-sm">
                        {{ auth()->user()->initial }}
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <div class="max-w-2xl mx-auto p-4 space-y-4">

        {{-- WELCOME BANNER --}}
        <div
            class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/30">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-lg">Pengajuan Cepat</h1>
                    <p class="text-sm text-white/80 mt-1">
                        @auth
                            Halo <strong>{{ auth()->user()->name }}</strong>! Ajukan peminjaman atau permintaan konsumable.
                        @else
                            Login untuk mulai mengajukan peminjaman atau permintaan konsumable.
                        @endauth
                    </p>
                </div>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="p-4 rounded-xl bg-green-100 text-green-800 border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-100 text-red-800 border border-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-100 text-red-800 border border-red-200">
                <p class="font-semibold mb-1 text-sm">Ada kesalahan:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM (hanya muncul kalau sudah login) --}}
        @auth
            <form method="POST" action="{{ route('requests.quick.store') }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5">
                @csrf

                {{-- DATA PEMOHON (auto-fill, readonly) --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Data Pemohon
                    </h2>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Nama</span>
                            <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>
                        </div>
                        @if (auth()->user()->nip)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">NIP</span>
                                <span class="font-mono text-gray-800">{{ auth()->user()->nip }}</span>
                            </div>
                        @endif
                        @if (auth()->user()->department)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Departemen</span>
                                <span class="text-gray-800">{{ auth()->user()->department->name }}</span>
                            </div>
                        @endif
                        @if (auth()->user()->position)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Jabatan</span>
                                <span class="text-gray-800">{{ auth()->user()->position }}</span>
                            </div>
                        @endif
                        @if (auth()->user()->phone)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">No. HP</span>
                                <span class="text-gray-800">{{ auth()->user()->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- JENIS PENGAJUAN --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Jenis Pengajuan
                    </h2>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="loan" class="peer sr-only"
                                @checked(old('type', $type) === 'loan' || !old('type'))>
                            <div
                                class="p-4 rounded-lg border-2 text-center peer-checked:border-amber-500
                                        peer-checked:bg-amber-50 transition">
                                <div class="text-3xl mb-1">📅</div>
                                <div class="text-sm font-semibold text-gray-700">Peminjaman</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="consumable" class="peer sr-only"
                                @checked(old('type', $type) === 'consumable')>
                            <div
                                class="p-4 rounded-lg border-2 text-center peer-checked:border-green-500
                                        peer-checked:bg-green-50 transition">
                                <div class="text-3xl mb-1">📦</div>
                                <div class="text-sm font-semibold text-gray-700">Konsumable</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- DETAIL ITEM --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Detail Item
                    </h2>

                    {{-- ============ PILIH ASET (SEARCHABLE) ============ --}}
                    @php
                        $assetItems = $assets
                            ->map(
                                fn($a) => [
                                    'id' => $a->id,
                                    'label' => '[' . $a->serial_number . '] ' . $a->brand . ' ' . $a->model,
                                    'sub' =>
                                        ($a->category?->name ?? '-') .
                                        ' • ' .
                                        ($a->status === 'available' ? 'Tersedia' : 'Dipakai'),
                                ],
                            )
                            ->values();
                    @endphp

                    <div data-type-show="loan" class="mb-3">
                        <div x-data="searchableSelect({
                            items: {{ Js::from($assetItems) }},
                            selectedId: '{{ old('asset_id') }}'
                        })" class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Pilih Aset <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="asset_id" :value="selectedId">

                            <button type="button" @click="open = !open"
                                class="w-full border rounded-lg px-3 py-2.5 text-sm text-left
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                       flex items-center justify-between gap-2">
                                <span x-show="!selectedItem" class="text-gray-400">-- Pilih Aset --</span>
                                <span x-show="selectedItem" class="text-gray-800 truncate"
                                    x-text="selectedItem?.label"></span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl
                                       max-h-72 flex flex-col">

                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-ref="searchInput"
                                        @keydown.escape="open = false" placeholder="Cari aset..."
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                               focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <div class="overflow-y-auto flex-1">
                                    <template x-if="filteredItems.length === 0">
                                        <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada aset
                                            ditemukan</p>
                                    </template>

                                    <template x-for="item in filteredItems" :key="item.id">
                                        <button type="button" @click="selectItem(item)"
                                            class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition
                                                   flex items-start gap-2">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-800 truncate"
                                                    x-text="item.label"></div>
                                                <div class="text-[10px] text-gray-500 truncate" x-text="item.sub || ''">
                                                </div>
                                            </div>
                                            <svg x-show="selectedId == item.id" xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-indigo-600 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============ KONSUMABLE (SEARCHABLE) ============ --}}
                    @php
                        $consumableItems = $consumables
                            ->map(
                                fn($c) => [
                                    'id' => $c->id,
                                    'label' => $c->name,
                                    'sub' => 'Stok: ' . $c->stock_available . ' ' . $c->unit,
                                ],
                            )
                            ->values();
                    @endphp

                    <div data-type-show="consumable" class="grid grid-cols-3 gap-3 mb-3">
                        <div class="col-span-2">
                            <div x-data="searchableSelect({
                                items: {{ Js::from($consumableItems) }},
                                selectedId: '{{ old('consumable_id') }}'
                            })" class="relative">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Pilih Konsumable <span class="text-red-500">*</span>
                                </label>

                                <input type="hidden" name="consumable_id" :value="selectedId">

                                <button type="button" @click="open = !open"
                                    class="w-full border rounded-lg px-3 py-2.5 text-sm text-left
                                           focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                           flex items-center justify-between gap-2">
                                    <span x-show="!selectedItem" class="text-gray-400">-- Pilih --</span>
                                    <span x-show="selectedItem" class="text-gray-800 truncate"
                                        x-text="selectedItem?.label"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                                        :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl
                                           max-h-72 flex flex-col">

                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" x-model="search" x-ref="searchInput"
                                            @keydown.escape="open = false" placeholder="Cari konsumable..."
                                            class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>

                                    <div class="overflow-y-auto flex-1">
                                        <template x-if="filteredItems.length === 0">
                                            <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada
                                                konsumable ditemukan</p>
                                        </template>

                                        <template x-for="item in filteredItems" :key="item.id">
                                            <button type="button" @click="selectItem(item)"
                                                class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition
                                                       flex items-start gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-gray-800 truncate"
                                                        x-text="item.label"></div>
                                                    <div class="text-[10px] text-gray-500 truncate"
                                                        x-text="item.sub || ''"></div>
                                                </div>
                                                <svg x-show="selectedId == item.id" xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 text-indigo-600 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah</label>
                            <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                                class="w-full border rounded-lg px-3 py-2.5 text-sm">
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 pt-4 border-t border-dashed">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Dibutuhkan</label>
                            <input type="date" name="needed_date" value="{{ old('needed_date') }}"
                                min="{{ now()->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2.5 text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Kosongkan kalau tidak mendesak.
                            </p>
                        </div>

                        <div data-type-show="loan">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Kembali <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}"
                                min="{{ now()->addDay()->format('Y-m-d') }}"
                                class="w-full border rounded-lg px-3 py-2.5 text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Minimal besok.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- LOKASI & DEPARTEMEN --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Lokasi Peminjam
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                            <select name="location_id" class="w-full border rounded-lg px-3 py-2.5 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($locations as $l)
                                    <option value="{{ $l->id }}" @selected(old('location_id', auth()->user()->location_id) == $l->id)>
                                        {{ $l->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                            <select name="department_id" class="w-full border rounded-lg px-3 py-2.5 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}" @selected(old('department_id', auth()->user()->department_id) == $d->id)>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- KETERANGAN --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Keterangan
                    </h2>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Alasan / Keperluan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="purpose" rows="3" required placeholder="Jelaskan kenapa butuh item ini..."
                            class="w-full border rounded-lg px-3 py-2.5 text-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">{{ old('purpose') }}</textarea>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="pt-2">
                    <button type="submit"
                        class="w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold
                               shadow-lg shadow-indigo-500/30 transition active:scale-[0.98]">
                        Kirim Pengajuan
                    </button>
                    <p class="text-[10px] text-gray-400 text-center mt-2">
                        Pengajuan akan diverifikasi oleh admin.
                    </p>
                </div>
            </form>
        @endauth
    </div>

    {{-- MODAL LOGIN --}}
    @guest
        <div id="loginModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Login Dulu</h2>
                        <p class="text-xs text-gray-500">Untuk melanjutkan pengajuan</p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mb-4">
                    <p class="text-xs text-blue-800">
                        <strong>Kenapa harus login?</strong> Supaya data pengajuan otomatis tercatat atas nama Anda.
                    </p>
                </div>

                <div id="loginError"
                    class="hidden mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                </div>

                <form id="quickLoginForm">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Email / Username / NIP
                        </label>
                        <input type="text" name="email" required autocomplete="username"
                            placeholder="Nip atau Username Email"
                            class="w-full border rounded-xl px-3.5 py-2.5 text-sm
                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required autocomplete="current-password"
                            class="w-full border rounded-xl px-3.5 py-2.5 text-sm
                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-3 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                               hover:from-blue-600 hover:to-violet-700
                               text-white font-bold shadow-lg shadow-blue-500/30 transition text-sm">
                        Login & Lanjutkan
                    </button>
                </form>

                <p class="text-[10px] text-gray-400 text-center mt-4">
                    Belum punya akun? Hubungi admin untuk pendaftaran.
                </p>
            </div>
        </div>
    @endguest

    <script>
        function searchableSelect(options) {
            return {
                open: false,
                search: '',
                items: options.items || [],
                selectedId: options.selectedId || '',

                get selectedItem() {
                    if (!this.selectedId) return null;
                    return this.items.find(i => i.id == this.selectedId);
                },

                get filteredItems() {
                    if (!this.search.trim()) return this.items;

                    const q = this.search.toLowerCase();
                    return this.items.filter(i =>
                        i.label.toLowerCase().includes(q) ||
                        (i.sub && i.sub.toLowerCase().includes(q))
                    );
                },

                selectItem(item) {
                    this.selectedId = item.id;
                    this.open = false;
                    this.search = '';
                },

                init() {
                    this.$watch('open', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                this.$refs.searchInput?.focus();
                            });
                        }
                    });
                }
            }
        }

        // Toggle field sesuai tipe
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="type"]');
            if (radios.length === 0) return;

            function updateFields() {
                const selected = document.querySelector('input[name="type"]:checked')?.value || 'loan';
                document.querySelectorAll('[data-type-show]').forEach(el => {
                    const types = el.dataset.typeShow.split(',');
                    el.style.display = types.includes(selected) ? '' : 'none';
                });
            }

            radios.forEach(r => r.addEventListener('change', updateFields));
            updateFields();
        });

        // Handle login form (guest)
        const loginForm = document.getElementById('quickLoginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(loginForm);
                const errBox = document.getElementById('loginError');

                try {
                    const response = await fetch('{{ route('admin.login') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': formData.get('_token'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.reload();
                    } else {
                        errBox.textContent = result.message;
                        errBox.classList.remove('hidden');
                    }
                } catch (err) {
                    errBox.textContent = 'Terjadi kesalahan. Coba lagi.';
                    errBox.classList.remove('hidden');
                }
            });
        }
    </script>

</body>

</html>
