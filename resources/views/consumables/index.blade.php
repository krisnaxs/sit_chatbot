<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Konsumable — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="consumableManager()">

    <x-header title="Konsumable" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

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

        <div class="space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Stok Konsumable</h1>
                    <p class="text-sm text-gray-500">Mouse, keyboard, HDD, dan barang habis pakai lainnya</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    {{-- 🆕 EXPORT EXCEL --}}
                    <a href="{{ route('siam.consumables.export.excel', request()->query()) }}"
                        title="Export data yang tampil ke Excel"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium
                               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Excel
                    </a>

                    {{-- 🆕 EXPORT PDF --}}
                    <a href="{{ route('siam.consumables.export.pdf', request()->query()) }}" target="_blank"
                        title="Export data yang tampil ke PDF"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium
                               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>

                    {{-- Tambah Item --}}
                    <a href="{{ route('siam.consumables.create') }}"
                        class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700 text-sm font-medium
                               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item
                    </a>
                </div>
            </div>

            {{-- STATISTIK --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-400">
                    <div class="text-xs text-gray-500 uppercase">Total Item</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Low Stock</div>
                    <div class="text-2xl font-bold text-amber-600">{{ $stats['low_stock'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <div class="text-xs text-gray-500 uppercase">Habis</div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['out_stock'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Tersedia</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $stats['total'] - $stats['out_stock'] }}
                    </div>
                </div>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.consumables.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama / brand / model" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                        <select name="category_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))>
                            Hanya low stock
                        </label>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Brand</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-right">Tersedia</th>
                                <th class="px-4 py-3 text-right">Keluar</th> {{-- 🆕 --}}
                                <th class="px-4 py-3 text-right">Min</th>
                                <th class="px-4 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($consumables as $c)
                                @php
                                    // 🆕 Hitung net keluar dari transaksi (out - return)
                                    $keluar = ($c->total_out ?? 0) - ($c->total_return ?? 0);

                                    // Warna badge keluar
                                    $keluarColor = match (true) {
                                        $keluar <= 0 => 'text-gray-400',
                                        default => 'bg-amber-50 text-amber-700',
                                    };

                                    $consData = [
                                        'id' => $c->id,
                                        'name' => $c->name,
                                        'category' => $c->category?->name,
                                        'brand' => $c->brand,
                                        'model' => $c->model,
                                        'unit' => $c->unit,
                                        'stock_total' => $c->stock_total,
                                        'stock_available' => $c->stock_available,
                                        'stock_minimum' => $c->stock_minimum,
                                        'stock_out' => $keluar, // 🆕
                                        'last_price' => $c->last_price,
                                        'last_price_formatted' => $c->last_price
                                            ? 'Rp ' . number_format($c->last_price, 0, ',', '.')
                                            : null,
                                        'notes' => $c->notes,
                                        'is_low_stock' => $c->is_low_stock,
                                        'is_out_of_stock' => $c->stock_available == 0,
                                        'routes' => [
                                            'show' => route('siam.consumables.show', $c),
                                            'edit' => route('siam.consumables.edit', $c),
                                            'delete' => route('siam.consumables.destroy', $c),
                                        ],
                                    ];
                                @endphp
                                <tr @click='openModal({{ json_encode($consData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $c->name }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $c->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $c->brand ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">{{ $c->stock_total }} {{ $c->unit }}</td>
                                    <td
                                        class="px-4 py-3 text-right font-medium
                                        {{ $c->stock_available <= $c->stock_minimum ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $c->stock_available }}
                                    </td>

                                    {{-- 🆕 KOLOM KELUAR --}}
                                    <td class="px-4 py-3 text-right">
                                        @if ($keluar > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded {{ $keluarColor }} font-semibold text-xs">
                                                {{ $keluar }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">0</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-right text-xs">{{ $c->stock_minimum }}</td>
                                    <td class="px-4 py-3">
                                        @if ($c->stock_available == 0)
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Habis</span>
                                        @elseif ($c->is_low_stock)
                                            <span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700">Low
                                                Stock</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-16 text-center"> {{-- 🆕 dari 7 ke 8 --}}
                                        <div class="flex flex-col items-center gap-3">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Belum ada item konsumable</p>
                                                <p class="text-sm text-gray-500 mt-1">Tambahkan item pertama</p>
                                            </div>
                                            <a href="{{ route('siam.consumables.create') }}"
                                                class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                + Tambah item
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($consumables->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $consumables->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODAL POPUP AKSI KONSUMABLE --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-br from-lime-500 to-emerald-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.category ?? 'Tanpa Kategori'"></span>
                            <span x-show="selected?.is_out_of_stock"
                                class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold">
                                Habis
                            </span>
                            <span x-show="!selected?.is_out_of_stock && selected?.is_low_stock"
                                class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold">
                                Low Stock
                            </span>
                            <span x-show="!selected?.is_out_of_stock && !selected?.is_low_stock"
                                class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold">
                                Tersedia
                            </span>
                        </div>
                        <h3 class="text-lg font-bold truncate" x-text="selected?.name"></h3>
                        <p class="text-sm text-white/80" x-text="selected?.brand ?? ''"></p>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- INFO --}}
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Kategori</div>
                        <div class="text-xs font-medium" x-text="selected?.category ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Brand</div>
                        <div class="text-xs font-medium" x-text="selected?.brand ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Stok Total</div>
                        <div class="text-xs font-medium"
                            x-text="(selected?.stock_total ?? 0) + ' ' + (selected?.unit ?? '')"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Stok Tersedia</div>
                        <div class="text-xs font-medium"
                            x-text="(selected?.stock_available ?? 0) + ' ' + (selected?.unit ?? '')"></div>
                    </div>

                    {{-- 🆕 KELUAR --}}
                    <div>
                        <div class="text-xs text-gray-500">Jumlah Keluar</div>
                        <div class="text-xs font-semibold text-amber-700"
                            x-text="(selected?.stock_out ?? 0) + ' ' + (selected?.unit ?? '')"></div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Stok Minimum</div>
                        <div class="text-xs font-medium" x-text="selected?.stock_minimum ?? 0"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Harga Terakhir</div>
                        <div class="text-xs font-medium" x-text="selected?.last_price_formatted ?? '-'"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Catatan</div>
                        <div class="text-xs" x-text="selected?.notes ?? '-'"></div>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>

                <div class="grid grid-cols-2 gap-3">

                    {{-- DETAIL --}}
                    <a :href="selected?.routes.show"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-indigo-300 hover:bg-indigo-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

                    {{-- EDIT --}}
                    <a :href="selected?.routes.edit"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-violet-300 hover:bg-violet-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Edit</div>
                            <div class="text-xs text-gray-500">Ubah data</div>
                        </div>
                    </a>

                    {{-- HAPUS (admin only) --}}
                    @if (auth()->user()->isAdmin())
                        <button type="button" @click="confirmDelete()"
                            class="col-span-2 flex items-center gap-3 p-3 rounded-xl border border-red-200
                                   bg-red-50 hover:bg-red-100 hover:border-red-300 transition group w-full text-left">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-red-700">Hapus</div>
                                <div class="text-xs text-red-500">Khusus admin — tidak bisa dibatalkan</div>
                            </div>
                        </button>
                    @endif

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="closeModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showDeleteModal = false">
            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Konsumable?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus <strong class="text-gray-800" x-text="selected?.name"></strong>?
                <br>
                <span class="text-xs text-red-500">Tindakan ini tidak bisa dibatalkan.</span>
            </p>
            <div class="flex gap-2">
                <button type="button" @click="showDeleteModal = false"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                    Batal
                </button>
                <button type="button" @click="submitDelete()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function consumableManager() {
            return {
                showModal: false,
                showDeleteModal: false,
                selected: null,

                openModal(data) {
                    this.selected = data;
                    this.showModal = true;
                },
                closeModal() {
                    this.showModal = false;
                    this.selected = null;
                },
                confirmDelete() {
                    this.showModal = false;
                    this.showDeleteModal = true;
                },

                submitDelete() {
                    const form = document.getElementById('deleteForm');
                    form.action = this.selected.routes.delete;
                    form.submit();
                },

                init() {
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.showDeleteModal) this.showDeleteModal = false;
                            else if (this.showModal) this.closeModal();
                        }
                    });
                }
            }
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

        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
