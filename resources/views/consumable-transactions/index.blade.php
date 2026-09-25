<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Transaksi Konsumable — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Transaksi Konsumable" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Transaksi Konsumable</h1>
                    <p class="text-sm text-gray-500">Riwayat barang masuk, keluar, dan kembali</p>
                </div>
                <a href="{{ route('siam.consumable-transactions.create') }}"
                    class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 text-sm font-medium">
                    + Catat Transaksi
                </a>
            </div>

            {{-- STATISTIK --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Total Masuk</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['total_in'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <div class="text-xs text-gray-500 uppercase">Total Keluar</div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['total_out'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <div class="text-xs text-gray-500 uppercase">Total Kembali</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_return'] }}</div>
                </div>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.consumable-transactions.index') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Item</label>
                        <select name="consumable_id" data-auto-submit
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($consumables as $c)
                                <option value="{{ $c->id }}" @selected(request('consumable_id') == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                        <select name="type" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="in" @selected(request('type') === 'in')>Masuk</option>
                            <option value="out" @selected(request('type') === 'out')>Keluar</option>
                            <option value="return" @selected(request('type') === 'return')>Kembali</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">User</label>
                        <select name="user_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.consumable-transactions.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Tipe</th>
                                <th class="px-3 py-2 text-right">Qty</th>
                                <th class="px-3 py-2 text-left">User</th>
                                <th class="px-3 py-2 text-left">Lokasi / Aset</th>
                                <th class="px-3 py-2 text-left">Keperluan</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transactions as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-xs">
                                        {{ $t->transaction_date?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs font-medium">{{ $t->consumable?->name }}</td>
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
                                    <td class="px-3 py-2 text-xs">
                                        @if ($t->asset)
                                            {{ $t->asset->serial_number }}
                                        @elseif ($t->location)
                                            {{ $t->location->full_name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $t->purpose ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST"
                                            action="{{ route('siam.consumable-transactions.destroy', $t) }}"
                                            onsubmit="return confirm('Hapus transaksi ini? Stok akan dikembalikan.')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                        Belum ada transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t">
                    {{ $transactions->links() }}
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
