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

<body class="bg-slate-100 font-sans">

    <x-header title="Konsumable" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Stok Konsumable</h1>
                    <p class="text-sm text-gray-500">Mouse, keyboard, HDD, dan barang habis pakai lainnya</p>
                </div>
                <a href="{{ route('siam.consumables.create') }}"
                    class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700 text-sm font-medium">
                    + Tambah Item
                </a>
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
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-left">Brand</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-right">Tersedia</th>
                            <th class="px-3 py-2 text-right">Min</th>
                            <th class="px-3 py-2 text-left">Harga</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($consumables as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium">{{ $c->name }}</td>
                                <td class="px-3 py-2 text-xs">{{ $c->category?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-xs">{{ $c->brand ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $c->stock_total }} {{ $c->unit }}</td>
                                <td
                                    class="px-3 py-2 text-right font-medium
                                    {{ $c->stock_available <= $c->stock_minimum ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $c->stock_available }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs">{{ $c->stock_minimum }}</td>
                                <td class="px-3 py-2 text-xs">
                                    {{ $c->last_price ? 'Rp ' . number_format($c->last_price, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    @if ($c->stock_available == 0)
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Habis</span>
                                    @elseif ($c->is_low_stock)
                                        <span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700">Low
                                            Stock</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Tersedia</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <a href="{{ route('siam.consumables.show', $c) }}"
                                        class="text-xs text-indigo-600 hover:underline">Detail</a>
                                    <a href="{{ route('siam.consumables.edit', $c) }}"
                                        class="ml-2 text-xs text-violet-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-8 text-center text-gray-400">
                                    Belum ada item konsumable.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t">
                    {{ $consumables->links() }}
                </div>
            </div>

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
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

</body>

</html>
