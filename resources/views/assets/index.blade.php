<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="{
    selectedAsset: null,
    showModal: false,
    openModal(asset) {
        this.selectedAsset = asset;
        this.showModal = true;
    },
    closeModal() {
        this.showModal = false;
        this.selectedAsset = null;
    }
}">

    <x-header title="Daftar Aset" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Aset IT</h1>
                    <p class="text-sm text-gray-500">Kelola aset laptop, PC, printer, dan konsumable</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    {{-- 🆕 EXPORT EXCEL --}}
                    <a href="{{ route('siam.assets.export.excel', request()->query()) }}"
                        title="Export data yang tampil ke Excel"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium
               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Excel
                    </a>

                    {{-- 🆕 EXPORT PDF --}}
                    <a href="{{ route('siam.assets.export.pdf', request()->query()) }}" target="_blank"
                        title="Export data yang tampil ke PDF"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium
               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h4" />
                        </svg>
                        PDF
                    </a>

                    {{-- Tombol lama (Brand & Model, Tambah Aset) --}}
                    <a href="{{ route('siam.asset-types.index') }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium
               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Brand & Model
                    </a>
                    <a href="{{ route('siam.assets.create') }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium
               inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Aset
                    </a>
                </div>
            </div>

            {{-- STATISTIK GLOBAL --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-400">
                    <div class="text-xs text-gray-500 uppercase">Total</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Hak Milik</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['owned'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                    <div class="text-xs text-gray-500 uppercase">Sewa</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['leased'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                    <div class="text-xs text-gray-500 uppercase">Tersedia</div>
                    <div class="text-2xl font-bold text-emerald-600">{{ $stats['available'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <div class="text-xs text-gray-500 uppercase">Dipakai</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['in_use'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Perbaikan</div>
                    <div class="text-2xl font-bold text-amber-600">{{ $stats['maintenance'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-500">
                    <div class="text-xs text-gray-500 uppercase">Pensiun</div>
                    <div class="text-2xl font-bold text-gray-600">{{ $stats['retired'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <div class="text-xs text-gray-500 uppercase">Hilang</div>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['lost'] }}</div>
                </div>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.assets.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">

                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Cari (SN / Kode / Hostname / Pemakai)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Contoh: T14-SN-0001 atau Budi"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                        <select name="category_id" data-auto-submit
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kepemilikan</label>
                        <select name="ownership_type" data-auto-submit
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="owned" @selected(request('ownership_type') === 'owned')>Hak Milik</option>
                            <option value="leased" @selected(request('ownership_type') === 'leased')>Sewa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="available" @selected(request('status') === 'available')>Tersedia</option>
                            <option value="in_use" @selected(request('status') === 'in_use')>Dipakai</option>
                            <option value="loaned" @selected(request('status') === 'loaned')>Dipinjam</option>
                            <option value="maintenance" @selected(request('status') === 'maintenance')>Perbaikan</option>
                            <option value="retired" @selected(request('status') === 'retired')>Pensiun</option>
                            <option value="lost" @selected(request('status') === 'lost')>Hilang</option>
                        </select>
                    </div>

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

                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1 flex items-center gap-1">
                            Model
                            @if (isset($models) && $models->count() > 0)
                                <span class="text-gray-400">({{ $models->count() }} tipe)</span>
                            @endif
                            <a href="{{ route('siam.asset-types.index') }}" target="_blank"
                                class="text-indigo-600 hover:underline text-[10px] font-semibold">
                                + Kelola
                            </a>
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

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lokasi</label>
                        <select name="location_id" data-auto-submit
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>
                                    {{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Pemakai</label>
                        <select name="user_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 🆕 TAHUN PEMBELIAN --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tahun Pembelian</label>
                        <select name="year" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($years as $y)
                                <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.assets.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- SUMMARY HASIL FILTER --}}
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
                                @if (request('category_id'))
                                    • Kategori:
                                    <span class="font-semibold text-gray-700">
                                        {{ $categories->firstWhere('id', request('category_id'))?->name }}
                                    </span>
                                @endif
                                @if (request('year'))
                                    • Tahun: <span class="font-semibold text-gray-700">{{ request('year') }}</span>
                                @endif
                                @if (request('search'))
                                    • Pencarian: "<span
                                        class="font-semibold text-gray-700">{{ request('search') }}</span>"
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        <div class="rounded-xl p-3 bg-gray-50 border border-gray-200">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Total</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</div>
                            <div class="text-[10px] text-gray-400">unit</div>
                        </div>
                        <div class="rounded-xl p-3 bg-emerald-50 border border-emerald-200">
                            <div class="text-[10px] text-emerald-600 uppercase font-semibold tracking-wide">Tersedia
                            </div>
                            <div class="text-2xl font-bold text-emerald-700">{{ $summary['available'] }}</div>
                            <div class="text-[10px] text-emerald-500">siap dipakai</div>
                        </div>
                        <div class="rounded-xl p-3 bg-blue-50 border border-blue-200">
                            <div class="text-[10px] text-blue-600 uppercase font-semibold tracking-wide">Dipakai</div>
                            <div class="text-2xl font-bold text-blue-700">{{ $summary['in_use'] }}</div>
                            <div class="text-[10px] text-blue-500">user pegang</div>
                        </div>
                        <div class="rounded-xl p-3 bg-amber-50 border border-amber-200">
                            <div class="text-[10px] text-amber-600 uppercase font-semibold tracking-wide">Dipinjam
                            </div>
                            <div class="text-2xl font-bold text-amber-700">{{ $summary['loaned'] }}</div>
                            <div class="text-[10px] text-amber-500">sementara</div>
                        </div>
                        <div class="rounded-xl p-3 bg-orange-50 border border-orange-200">
                            <div class="text-[10px] text-orange-600 uppercase font-semibold tracking-wide">Perbaikan
                            </div>
                            <div class="text-2xl font-bold text-orange-700">{{ $summary['maintenance'] }}</div>
                            <div class="text-[10px] text-orange-500">maintenance</div>
                        </div>
                        <div class="rounded-xl p-3 bg-gray-100 border border-gray-300">
                            <div class="text-[10px] text-gray-600 uppercase font-semibold tracking-wide">Pensiun</div>
                            <div class="text-2xl font-bold text-gray-700">{{ $summary['retired'] }}</div>
                            <div class="text-[10px] text-gray-500">tidak dipakai</div>
                        </div>
                        <div class="rounded-xl p-3 bg-red-50 border border-red-200">
                            <div class="text-[10px] text-red-600 uppercase font-semibold tracking-wide">Hilang</div>
                            <div class="text-2xl font-bold text-red-700">{{ $summary['lost'] }}</div>
                            <div class="text-[10px] text-red-500">lost</div>
                        </div>
                        <div class="rounded-xl p-3 bg-green-50 border border-green-200">
                            <div class="text-[10px] text-green-600 uppercase font-semibold tracking-wide">Hak Milik
                            </div>
                            <div class="text-2xl font-bold text-green-700">{{ $summary['owned'] }}</div>
                            <div class="text-[10px] text-green-500">owned</div>
                        </div>
                        <div class="rounded-xl p-3 bg-orange-50 border border-orange-200">
                            <div class="text-[10px] text-orange-600 uppercase font-semibold tracking-wide">Sewa</div>
                            <div class="text-2xl font-bold text-orange-700">{{ $summary['leased'] }}</div>
                            <div class="text-[10px] text-orange-500">leased</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TABEL ASET --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left w-12">No</th>
                                <th class="px-3 py-2 text-left">SN</th>
                                <th class="px-3 py-2 text-left">Brand & Model</th>
                                <th class="px-3 py-2 text-left">Kategori</th>
                                <th class="px-3 py-2 text-left">Kepemilikan</th>
                                <th class="px-3 py-2 text-left">Pemakai</th>
                                <th class="px-3 py-2 text-left">Lokasi</th>
                                <th class="px-3 py-2 text-left">Tahun</th>
                                <th class="px-3 py-2 text-left">Hostname</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assets as $index => $asset)
                                @php
                                    $assetData = [
                                        'id' => $asset->id,
                                        'asset_code' => $asset->asset_code,
                                        'serial_number' => $asset->serial_number,
                                        'hostname' => $asset->hostname,
                                        'brand' => $asset->brand,
                                        'model' => $asset->model,
                                        'category' => $asset->category?->name,
                                        'ownership_type' => $asset->ownership_type,
                                        'ownership_label' => $asset->ownership_label,
                                        'status' => $asset->status,
                                        'status_label' => $asset->status_label,
                                        'condition_percent' => $asset->condition_percent,
                                        'current_user' => $asset->currentUser?->name,
                                        'current_location' => $asset->currentLocation?->full_name,
                                        'purchase_year' => $asset->purchase_date?->format('Y'),
                                        'routes' => [
                                            'show' => route('siam.assets.show', $asset),
                                            'edit' => route('siam.assets.edit', $asset),
                                            'assign' => route('siam.assignments.create', ['asset_id' => $asset->id]),
                                            'loan' => route('siam.loans.create', ['asset_id' => $asset->id]),
                                            'maintenance' => route('siam.maintenances.create', [
                                                'asset_id' => $asset->id,
                                            ]),
                                            'delete' => route('siam.assets.destroy', $asset),
                                        ],
                                    ];
                                @endphp

                                <tr @click='openModal({{ json_encode($assetData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">

                                    {{-- 🆕 No --}}
                                    <td class="px-3 py-2 text-xs text-gray-500 font-medium">
                                        {{ $assets->firstItem() + $index }}
                                    </td>

                                    <td class="px-3 py-2 font-mono text-xs text-indigo-700">
                                        {{ $asset->serial_number }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-gray-800">
                                            {{ $asset->brand }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ $asset->model }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-500">
                                        {{ $asset->category?->name }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $ownColor =
                                                $asset->ownership_type === 'owned'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-orange-100 text-orange-700';
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $ownColor }}">
                                            {{ $asset->ownership_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($asset->currentUser)
                                            <div class="text-gray-800">{{ $asset->currentUser->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $asset->currentUser->position }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        @if ($asset->currentLocation)
                                            <div>{{ $asset->currentLocation->building }}</div>
                                            <div class="text-gray-500">
                                                {{ $asset->currentLocation->room }}
                                                @if ($asset->currentLocation->division)
                                                    - {{ $asset->currentLocation->division }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- 🆕 TAHUN PEMBELIAN --}}
                                    <td class="px-3 py-2 text-xs">
                                        @if ($asset->purchase_date)
                                            <span class="font-medium">{{ $asset->purchase_date->format('Y') }}</span>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 text-xs font-mono">{{ $asset->hostname ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColor = match ($asset->status) {
                                                'available' => 'bg-green-100 text-green-700',
                                                'in_use' => 'bg-blue-100 text-blue-700',
                                                'loaned' => 'bg-yellow-100 text-yellow-700',
                                                'maintenance' => 'bg-orange-100 text-orange-700',
                                                'retired' => 'bg-gray-100 text-gray-700',
                                                'lost' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $statusColor }}">
                                            {{ $asset->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($asset->condition_percent !== null)
                                            @php
                                                $c = $asset->condition_percent;
                                                $cColor = match (true) {
                                                    $c >= 80 => 'text-green-600',
                                                    $c >= 60 => 'text-yellow-600',
                                                    $c >= 40 => 'text-orange-600',
                                                    default => 'text-red-600',
                                                };
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                    <div class="h-1.5 rounded-full bg-current {{ $cColor }}"
                                                        style="width: {{ $c }}%"></div>
                                                </div>
                                                <span
                                                    class="text-xs {{ $cColor }} font-medium">{{ $c }}%</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-3 py-8 text-center text-gray-400">
                                        Tidak ada aset ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $assets->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL POPUP AKSI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" @click.away="closeModal()">

            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selectedAsset?.category"></span>
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selectedAsset?.ownership_label"></span>
                        </div>
                        <h3 class="text-xl font-bold truncate"
                            x-text="selectedAsset ? selectedAsset.brand + ' ' + selectedAsset.model : ''"></h3>
                        <p class="text-sm text-white/80 font-mono mt-1">
                            SN: <span x-text="selectedAsset?.serial_number"></span>
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

            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Kode Aset</div>
                        <div class="font-mono text-xs text-indigo-700" x-text="selectedAsset?.asset_code"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Hostname</div>
                        <div class="font-mono text-xs" x-text="selectedAsset?.hostname ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Pemakai</div>
                        <div class="text-xs" x-text="selectedAsset?.current_user ?? 'Belum dipakai'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Lokasi</div>
                        <div class="text-xs" x-text="selectedAsset?.current_location ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tahun Pembelian</div>
                        <div class="text-xs" x-text="selectedAsset?.purchase_year ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Kondisi</div>
                        <div class="text-xs font-medium"
                            x-text="selectedAsset?.condition_percent !== null ? selectedAsset.condition_percent + '%' : '-'">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>
                <div class="grid grid-cols-2 gap-3">

                    <a :href="selectedAsset?.routes.show"
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
                            <div class="text-xs text-gray-500">Lihat info lengkap</div>
                        </div>
                    </a>

                    @if (auth()->user()->hasAnyRole(['admin', 'support']))
                        <a :href="selectedAsset?.routes.edit"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                  hover:border-violet-300 hover:bg-violet-50 transition group">
                            <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">Edit</div>
                                <div class="text-xs text-gray-500">Ubah data aset</div>
                            </div>
                        </a>
                    @endif

                    <a :href="selectedAsset?.routes.assign"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-teal-300 hover:bg-teal-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Assign</div>
                            <div class="text-xs text-gray-500">Serah terima ke user</div>
                        </div>
                    </a>

                    <a :href="selectedAsset?.routes.loan"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-amber-300 hover:bg-amber-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Pinjam</div>
                            <div class="text-xs text-gray-500">Peminjaman sementara</div>
                        </div>
                    </a>

                    <a :href="selectedAsset?.routes.maintenance"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-orange-300 hover:bg-orange-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Perbaikan</div>
                            <div class="text-xs text-gray-500">Catat maintenance</div>
                        </div>
                    </a>

                    @if (auth()->user()->isAdmin())
                        <form :action="selectedAsset?.routes.delete" method="POST"
                            onsubmit="return confirm('Yakin hapus aset ini? Data yang dihapus tidak bisa dikembalikan.')"
                            class="contents">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex items-center gap-3 p-3 rounded-xl border border-red-200
                                       bg-red-50 hover:bg-red-100 hover:border-red-300 transition group w-full text-left">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-red-700">Hapus</div>
                                    <div class="text-xs text-red-500">Khusus admin</div>
                                </div>
                            </button>
                        </form>
                    @endif

                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="closeModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Batal
                </button>
            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });

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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const body = document.body;
                if (body._x_dataStack) {
                    const data = body._x_dataStack[0];
                    if (data && data.showModal) {
                        data.showModal = false;
                        data.selectedAsset = null;
                    }
                }
            }
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
