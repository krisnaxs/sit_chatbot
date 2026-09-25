<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detail Konsumable - SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail Konsumable" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="space-y-6">

            {{-- HEADER --}}
            <div>
                <a href="{{ route('siam.consumables.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali ke daftar konsumable</a>
                <div class="flex flex-wrap items-center justify-between gap-3 mt-1">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $consumable->name }}</h1>
                        <p class="text-sm text-gray-500">
                            {{ $consumable->brand }} {{ $consumable->model }}
                            • {{ $consumable->category?->name ?? 'Tanpa kategori' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @if ($consumable->stock_available == 0)
                            <span class="px-3 py-1 text-sm rounded bg-red-100 text-red-700 font-semibold">Habis</span>
                        @elseif ($consumable->is_low_stock)
                            <span class="px-3 py-1 text-sm rounded bg-amber-100 text-amber-700 font-semibold">Low
                                Stock</span>
                        @else
                            <span
                                class="px-3 py-1 text-sm rounded bg-green-100 text-green-700 font-semibold">Tersedia</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- STATISTIK --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-cyan-500">
                    <div class="text-xs text-gray-500 uppercase">Total Stok</div>
                    <div class="text-2xl font-bold text-cyan-600">{{ $consumable->stock_total }} {{ $consumable->unit }}
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Tersedia</div>
                    <div class="text-2xl font-bold text-green-600">{{ $consumable->stock_available }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Min. Stok</div>
                    <div class="text-2xl font-bold text-amber-600">{{ $consumable->stock_minimum }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-violet-500">
                    <div class="text-xs text-gray-500 uppercase">Terpakai</div>
                    <div class="text-2xl font-bold text-violet-600">
                        {{ $consumable->stock_total - $consumable->stock_available }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI: Info + History Transaksi --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- INFO --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-4">Informasi Item</h2>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Nama</dt>
                                <dd class="font-medium">{{ $consumable->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Kategori</dt>
                                <dd>{{ $consumable->category?->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Brand</dt>
                                <dd>{{ $consumable->brand ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Model</dt>
                                <dd>{{ $consumable->model ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Satuan</dt>
                                <dd>{{ $consumable->unit }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Harga Terakhir</dt>
                                <dd>{{ $consumable->last_price ? 'Rp ' . number_format($consumable->last_price, 0, ',', '.') : '-' }}
                                </dd>
                            </div>
                            @if ($consumable->notes)
                                <div class="col-span-2">
                                    <dt class="text-gray-500">Catatan</dt>
                                    <dd>{{ $consumable->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- HISTORY TRANSAKSI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800">
                                History Transaksi
                                <span class="text-xs text-gray-500 font-normal">
                                    ({{ $consumable->transactions->count() }} record)
                                </span>
                            </h2>
                            <a href="{{ route('siam.consumable-transactions.create', ['consumable_id' => $consumable->id]) }}"
                                class="text-xs px-3 py-1.5 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-medium">
                                + Catat Transaksi
                            </a>
                        </div>

                        @if ($consumable->transactions->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum ada transaksi.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Tanggal</th>
                                            <th class="px-3 py-2 text-left">Tipe</th>
                                            <th class="px-3 py-2 text-right">Qty</th>
                                            <th class="px-3 py-2 text-left">User</th>
                                            <th class="px-3 py-2 text-left">Keperluan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($consumable->transactions->sortByDesc('transaction_date') as $t)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2 text-xs">
                                                    {{ $t->transaction_date?->format('d M Y H:i') ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    @php
                                                        $typeColor = match ($t->type) {
                                                            'in' => 'bg-green-100 text-green-700',
                                                            'out' => 'bg-red-100 text-red-700',
                                                            'return' => 'bg-blue-100 text-blue-700',
                                                            default => 'bg-gray-100 text-gray-700',
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-0.5 text-xs rounded {{ $typeColor }}">
                                                        {{ $t->type_label }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-right font-medium">{{ $t->quantity }}</td>
                                                <td class="px-3 py-2 text-xs">{{ $t->user?->name ?? '-' }}</td>
                                                <td class="px-3 py-2 text-xs">{{ $t->purpose ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- KOLOM KANAN --}}
                <div class="space-y-6">

                    {{-- STOK BAR --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Level Stok</h2>

                        @php
                            $percent =
                                $consumable->stock_total > 0
                                    ? round(($consumable->stock_available / $consumable->stock_total) * 100, 1)
                                    : 0;
                            $barColor =
                                $percent >= 60 ? 'bg-green-500' : ($percent >= 30 ? 'bg-amber-500' : 'bg-red-500');
                        @endphp

                        <div class="mb-4">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Tersedia</span>
                                <span class="font-semibold text-gray-700">{{ $consumable->stock_available }} /
                                    {{ $consumable->stock_total }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $barColor }} transition-all"
                                    style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="text-right text-xs text-gray-500 mt-1">{{ $percent }}%</div>
                        </div>

                        @if ($consumable->is_low_stock)
                            <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                                ⚠️ Stok di bawah minimum ({{ $consumable->stock_minimum }}). Segera restock.
                            </div>
                        @endif
                    </div>

                    {{-- AKSI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Aksi</h2>
                        <div class="space-y-2">
                            <a href="{{ route('siam.consumables.edit', $consumable) }}"
                                class="block text-center text-sm px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                                ✏️ Edit Item
                            </a>
                            <a href="{{ route('siam.consumable-transactions.create', ['consumable_id' => $consumable->id]) }}"
                                class="block text-center text-sm px-3 py-2 bg-pink-100 text-pink-800 hover:bg-pink-200 rounded-lg font-medium">
                                📥 Catat Transaksi
                            </a>
                            <form method="POST" action="{{ route('siam.consumables.destroy', $consumable) }}"
                                onsubmit="return confirm('Yakin hapus item ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full text-center text-sm px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg font-medium">
                                    🗑️ Hapus Item
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- STATISTIK TRANSAKSI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Statistik Transaksi</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Total Masuk</dt>
                                <dd class="font-medium text-green-600">
                                    {{ $consumable->transactions->where('type', 'in')->sum('quantity') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Total Keluar</dt>
                                <dd class="font-medium text-red-600">
                                    {{ $consumable->transactions->where('type', 'out')->sum('quantity') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Total Kembali</dt>
                                <dd class="font-medium text-blue-600">
                                    {{ $consumable->transactions->where('type', 'return')->sum('quantity') }}
                                </dd>
                            </div>
                        </dl>
                    </div>

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
    </script>

</body>

</html>
