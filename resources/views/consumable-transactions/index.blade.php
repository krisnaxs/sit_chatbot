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

<body class="bg-slate-100 font-sans" x-data="transactionManager()">

    <x-header title="Transaksi Konsumable" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Transaksi Konsumable</h1>
                    <p class="text-sm text-gray-500">Riwayat barang masuk, keluar, dan kembali</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    {{-- EXPORT EXCEL --}}
                    <a href="{{ route('siam.consumable-transactions.export.excel', request()->query()) }}"
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

                    {{-- EXPORT PDF --}}
                    <a href="{{ route('siam.consumable-transactions.export.pdf', request()->query()) }}" target="_blank"
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

                    {{-- CATAT TRANSAKSI --}}
                    <a href="{{ route('siam.consumable-transactions.create') }}"
                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 text-sm font-medium
                   inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Catat Transaksi
                    </a>
                </div>
            </div>

            {{-- STATISTIK --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
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
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transactions as $t)
                                @php
                                    $trxData = [
                                        'id' => $t->id,
                                        'transaction_date' => $t->transaction_date?->format('d M Y H:i'),
                                        'consumable_name' => $t->consumable?->name,
                                        'consumable_unit' => $t->consumable?->unit,
                                        'type' => $t->type,
                                        'type_label' => $t->type_label,
                                        'quantity' => $t->quantity,
                                        'user_name' => $t->user?->name,
                                        'location_name' => $t->location?->full_name,
                                        'asset_sn' => $t->asset?->serial_number,
                                        'purpose' => $t->purpose,
                                        'notes' => $t->notes,
                                        'requested_by' => $t->requestedBy?->name,
                                        'approved_by' => $t->approvedBy?->name,
                                        'routes' => [
                                            'show' => route('siam.consumable-transactions.show', $t),
                                            'delete' => route('siam.consumable-transactions.destroy', $t),
                                        ],
                                    ];
                                @endphp
                                <tr @click='openModal({{ json_encode($trxData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Belum ada transaksi</p>
                                                <p class="text-sm text-gray-500 mt-1">Catat transaksi pertama</p>
                                            </div>
                                            <a href="{{ route('siam.consumable-transactions.create') }}"
                                                class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                + Catat transaksi
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODAL POPUP AKSI TRANSAKSI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.type_label"></span>
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.transaction_date"></span>
                        </div>
                        <h3 class="text-lg font-bold truncate" x-text="selected?.consumable_name"></h3>
                        <p class="text-sm text-white/80">
                            <span x-text="selected?.quantity"></span>
                            <span x-text="selected?.consumable_unit ?? 'unit'"></span>
                        </p>
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
                        <div class="text-xs text-gray-500">Item</div>
                        <div class="text-xs font-medium" x-text="selected?.consumable_name"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tipe</div>
                        <div class="text-xs font-medium" x-text="selected?.type_label"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Qty</div>
                        <div class="text-xs font-medium"
                            x-text="selected?.quantity + ' ' + (selected?.consumable_unit ?? '')"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tanggal</div>
                        <div class="text-xs font-medium" x-text="selected?.transaction_date"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">User</div>
                        <div class="text-xs font-medium" x-text="selected?.user_name ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Lokasi</div>
                        <div class="text-xs font-medium" x-text="selected?.location_name ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Aset</div>
                        <div class="text-xs font-medium" x-text="selected?.asset_sn ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Keperluan</div>
                        <div class="text-xs font-medium" x-text="selected?.purpose ?? '-'"></div>
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

                    {{-- KELUARKAN --}}
                    <a :href="'{{ route('siam.consumable-transactions.create') }}?consumable_id=' + selected?.id + '&type=out'"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                               hover:border-red-300 hover:bg-red-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Keluarkan</div>
                            <div class="text-xs text-gray-500">Kasih ke user</div>
                        </div>
                    </a>

                    {{-- KEMBALIKAN --}}
                    <a :href="'{{ route('siam.consumable-transactions.create') }}?consumable_id=' + selected?.id + '&type=return'"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                               hover:border-blue-300 hover:bg-blue-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Kembalikan</div>
                            <div class="text-xs text-gray-500">User return</div>
                        </div>
                    </a>

                    {{-- HAPUS (admin only) --}}
                    @if (auth()->user()->isAdmin())
                        <button type="button" @click="confirmDelete()"
                            class="flex items-center gap-3 p-3 rounded-xl border border-red-200
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
                                <div class="text-xs text-red-500">Khusus admin</div>
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
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Transaksi?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus transaksi
                <strong class="text-gray-800" x-text="selected?.consumable_name"></strong>
                (<span x-text="selected?.type_label"></span>)?
                <br>
                <span class="text-xs text-amber-600 font-semibold">⚠️ Stok akan dikembalikan otomatis.</span>
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
        function transactionManager() {
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
                    // Tutup modal detail dulu, baru buka modal konfirmasi hapus
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
