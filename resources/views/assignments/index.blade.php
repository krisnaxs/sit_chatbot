<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Serah Terima Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="assignmentManager()">

    <x-header title="Serah Terima Aset" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Serah Terima Aset</h1>
                    <p class="text-sm text-gray-500">History penyerahan aset ke pegawai</p>
                </div>
                <a href="{{ route('siam.assignments.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium
                           inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Assign Aset
                </a>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.assignments.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">

                    {{-- SEARCH --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">
                            Cari (SN / Brand / Model / User)
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Contoh: T14-SN-0001 atau Budi"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- ASET --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Aset (SN)</label>
                        <select name="asset_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Aset</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(request('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BRAND --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Brand</label>
                        <select name="brand" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- MODEL --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1 flex items-center gap-1">
                            Model
                            @if (isset($models) && $models->count() > 0)
                                <span class="text-gray-400">({{ $models->count() }} tipe)</span>
                            @endif
                        </label>
                        <select name="model" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Model</option>
                            @foreach ($models as $m)
                                <option value="{{ $m->model }}" @selected(request('model') === $m->model)>
                                    {{ $m->model }}
                                    @if ($m->brand)
                                        — {{ $m->brand }}
                                    @endif
                                    ({{ $m->total }} unit)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- USER --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Pemakai</label>
                        <select name="user_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua User</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- LOKASI --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lokasi</label>
                        <select name="location_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Lokasi</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>
                                    {{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="active" @selected(request('status') === 'active')>Sedang Dipakai</option>
                            <option value="returned" @selected(request('status') === 'returned')>Sudah Kembali</option>
                        </select>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.assignments.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- 🆕 SUMMARY STATISTIK (muncul kalau ada filter aktif) --}}
            @if ($hasFilter && $summary['total'] > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div>
                            <h2 class="font-bold text-gray-800">Summary Hasil Filter</h2>
                            <p class="text-xs text-gray-500">
                                @if (request('model'))
                                    Model: <span class="font-semibold text-gray-700">{{ request('model') }}</span>
                                @endif
                                @if (request('brand'))
                                    • Brand: <span class="font-semibold text-gray-700">{{ request('brand') }}</span>
                                @endif
                                @if (request('search'))
                                    • Pencarian: "<span
                                        class="font-semibold text-gray-700">{{ request('search') }}</span>"
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
                        {{-- Total --}}
                        <div class="rounded-xl p-3 bg-gray-50 border border-gray-200">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Total Aset
                            </div>
                            <div class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</div>
                            <div class="text-[10px] text-gray-400">unit</div>
                        </div>

                        {{-- Tersedia --}}
                        <div class="rounded-xl p-3 bg-emerald-50 border border-emerald-200">
                            <div class="text-[10px] text-emerald-600 uppercase font-semibold tracking-wide">Tersedia
                            </div>
                            <div class="text-2xl font-bold text-emerald-700">{{ $summary['available'] }}</div>
                            <div class="text-[10px] text-emerald-500">siap dipakai</div>
                        </div>

                        {{-- Dipakai --}}
                        <div class="rounded-xl p-3 bg-blue-50 border border-blue-200">
                            <div class="text-[10px] text-blue-600 uppercase font-semibold tracking-wide">Dipakai</div>
                            <div class="text-2xl font-bold text-blue-700">{{ $summary['in_use'] }}</div>
                            <div class="text-[10px] text-blue-500">user pegang</div>
                        </div>

                        {{-- Dipinjam --}}
                        <div class="rounded-xl p-3 bg-amber-50 border border-amber-200">
                            <div class="text-[10px] text-amber-600 uppercase font-semibold tracking-wide">Dipinjam
                            </div>
                            <div class="text-2xl font-bold text-amber-700">{{ $summary['loaned'] }}</div>
                            <div class="text-[10px] text-amber-500">sementara</div>
                        </div>

                        {{-- Perbaikan --}}
                        <div class="rounded-xl p-3 bg-orange-50 border border-orange-200">
                            <div class="text-[10px] text-orange-600 uppercase font-semibold tracking-wide">Perbaikan
                            </div>
                            <div class="text-2xl font-bold text-orange-700">{{ $summary['maintenance'] }}</div>
                            <div class="text-[10px] text-orange-500">maintenance</div>
                        </div>

                        {{-- Pensiun --}}
                        <div class="rounded-xl p-3 bg-gray-100 border border-gray-300">
                            <div class="text-[10px] text-gray-600 uppercase font-semibold tracking-wide">Pensiun</div>
                            <div class="text-2xl font-bold text-gray-700">{{ $summary['retired'] }}</div>
                            <div class="text-[10px] text-gray-500">tidak dipakai</div>
                        </div>

                        {{-- Hilang --}}
                        <div class="rounded-xl p-3 bg-red-50 border border-red-200">
                            <div class="text-[10px] text-red-600 uppercase font-semibold tracking-wide">Hilang</div>
                            <div class="text-2xl font-bold text-red-700">{{ $summary['lost'] }}</div>
                            <div class="text-[10px] text-red-500">lost</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TABEL --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Aset</th>
                                <th class="px-3 py-2 text-left">Pemakai</th>
                                <th class="px-3 py-2 text-left">Lokasi</th>
                                <th class="px-3 py-2 text-left">Diserahkan</th>
                                <th class="px-3 py-2 text-left">Dikembalikan</th>
                                <th class="px-3 py-2 text-left">Durasi</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assignments as $a)
                                @php
                                    $assignData = [
                                        'id' => $a->id,
                                        'asset_brand' => $a->asset?->brand,
                                        'asset_model' => $a->asset?->model,
                                        'asset_sn' => $a->asset?->serial_number,
                                        'user_name' => $a->user?->name,
                                        'user_position' => $a->user?->position,
                                        'location_name' => $a->location?->full_name,
                                        'assigned_at' => $a->assigned_at?->format('d M Y'),
                                        'returned_at' => $a->returned_at?->format('d M Y'),
                                        'duration_days' => $a->duration_days,
                                        'is_active' => $a->is_active,
                                        'condition_on_assign' => $a->condition_on_assign,
                                        'condition_on_return' => $a->condition_on_return,
                                        'notes' => $a->notes,
                                        'routes' => [
                                            'asset' => route('siam.assets.show', $a->asset_id),
                                            'return' => route('siam.assignments.return', $a),
                                            'delete' => route('siam.assignments.destroy', $a),
                                        ],
                                    ];
                                @endphp
                                <tr class="hover:bg-indigo-50 cursor-pointer transition-colors"
                                    @click='openModal({{ json_encode($assignData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                    {{-- ASET --}}
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">
                                            {{ $a->asset?->brand }} {{ $a->asset?->model }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ $a->asset?->serial_number }}
                                        </div>
                                    </td>

                                    {{-- PEMAKAI --}}
                                    <td class="px-3 py-2">
                                        <div class="text-gray-800">{{ $a->user?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $a->user?->position }}</div>
                                    </td>

                                    {{-- LOKASI --}}
                                    <td class="px-3 py-2 text-xs">
                                        @if ($a->location)
                                            <div>{{ $a->location->building }}</div>
                                            <div class="text-gray-500">
                                                {{ $a->location->room }}
                                                @if ($a->location->division)
                                                    - {{ $a->location->division }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="px-3 py-2 text-xs">
                                        {{ $a->assigned_at?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $a->returned_at?->format('d M Y') ?? '-' }}
                                    </td>

                                    {{-- DURASI --}}
                                    <td class="px-3 py-2 text-xs">
                                        @if ($a->duration_days !== null)
                                            {{ $a->duration_days }} hari
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-3 py-2">
                                        @if ($a->is_active)
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700 font-semibold">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">
                                                Sudah Kembali
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-8 text-center text-gray-400">
                                        Belum ada serah terima aset.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $assignments->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL AKSI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.is_active ? 'AKTIF' : 'SUDAH KEMBALI'"></span>
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.duration_days !== null ? selected.duration_days + ' hari' : '-'"></span>
                        </div>
                        <h3 class="text-xl font-bold truncate"
                            x-text="selected ? selected.asset_brand + ' ' + selected.asset_model : ''"></h3>
                        <p class="text-sm text-white/80 font-mono mt-1">
                            SN: <span x-text="selected?.asset_sn"></span>
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
                        <div class="text-xs text-gray-500">Pemakai</div>
                        <div class="text-xs font-medium" x-text="selected?.user_name ?? '-'"></div>
                        <div class="text-[10px] text-gray-400" x-text="selected?.user_position ?? ''"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Lokasi</div>
                        <div class="text-xs" x-text="selected?.location_name ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Diserahkan</div>
                        <div class="text-xs" x-text="selected?.assigned_at ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Dikembalikan</div>
                        <div class="text-xs" x-text="selected?.returned_at ?? '-'"></div>
                    </div>
                    <div x-show="selected?.condition_on_assign !== null">
                        <div class="text-xs text-gray-500">Kondisi Serah</div>
                        <div class="text-xs font-medium" x-text="selected?.condition_on_assign + '%'"></div>
                    </div>
                    <div x-show="selected?.condition_on_return !== null">
                        <div class="text-xs text-gray-500">Kondisi Kembali</div>
                        <div class="text-xs font-medium" x-text="selected?.condition_on_return + '%'"></div>
                    </div>
                    <div class="col-span-2" x-show="selected?.notes">
                        <div class="text-xs text-gray-500">Catatan</div>
                        <div class="text-xs italic" x-text="selected?.notes"></div>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>
                <div class="space-y-2">

                    {{-- LIHAT ASET --}}
                    <a :href="selected?.routes.asset"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-indigo-300 hover:bg-indigo-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-gray-800">Lihat Aset</div>
                            <div class="text-xs text-gray-500">Buka detail aset</div>
                        </div>
                    </a>

                    {{-- KEMBALIKAN --}}
                    <template x-if="selected?.is_active">
                        <form :action="selected?.routes.return" method="POST"
                            onsubmit="return confirm('Kembalikan aset ini?')" class="contents">
                            @csrf
                            <input type="hidden" name="returned_at" value="{{ now()->format('Y-m-d H:i:s') }}">
                            <input type="hidden" name="condition_on_return" value="100">
                            <button type="submit"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                       hover:border-emerald-300 hover:bg-emerald-50 transition text-left">
                                <div
                                    class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800">Kembalikan Aset</div>
                                    <div class="text-xs text-gray-500">Set aset jadi tersedia</div>
                                </div>
                            </button>
                        </form>
                    </template>

                    {{-- HAPUS --}}
                    @if (auth()->user()->isAdmin())
                        <template x-if="true">
                            <form :action="selected?.routes.delete" method="POST"
                                onsubmit="return confirm('Hapus record assignment ini?')" class="contents">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full flex items-center gap-3 p-3 rounded-xl border border-red-200
                                           bg-red-50 hover:bg-red-100 hover:border-red-300 transition text-left">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm text-red-700">Hapus Record</div>
                                        <div class="text-xs text-red-500">Khusus admin — tidak bisa dibatalkan</div>
                                    </div>
                                </button>
                            </form>
                        </template>
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

    <script>
        function assignmentManager() {
            return {
                showModal: false,
                selected: null,

                openModal(data) {
                    this.selected = data;
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.selected = null;
                },

                init() {
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showModal) {
                            this.closeModal();
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
