<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Perbaikan Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="maintenanceManager()">

    <x-header title="Perbaikan Aset" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Perbaikan Aset</h1>
                    <p class="text-sm text-gray-500">History maintenance & service aset IT</p>
                </div>
                <a href="{{ route('siam.maintenances.create') }}"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium
                           inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Perbaikan
                </a>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.maintenances.index') }}"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">

                    {{-- SEARCH --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Cari (SN / Brand / Model / Pemakai)</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Contoh: T14-SN-0040 atau Budi"
                                class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Aset</label>
                        <select name="asset_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(request('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                    @if ($a->currentUser)
                                        ({{ $a->currentUser->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                        <select name="type" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="preventive" @selected(request('type') === 'preventive')>Preventif</option>
                            <option value="corrective" @selected(request('type') === 'corrective')>Perbaikan</option>
                            <option value="upgrade" @selected(request('type') === 'upgrade')>Upgrade</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="open" @selected(request('status') === 'open')>Dibuka</option>
                            <option value="in_progress" @selected(request('status') === 'in_progress')>Proses</option>
                            <option value="done" @selected(request('status') === 'done')>Selesai</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Batal</option>
                        </select>
                    </div>

                    <div class="lg:col-span-5 flex gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.maintenances.index') }}"
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
                                <th class="px-3 py-2 text-left">Aset</th>
                                <th class="px-3 py-2 text-left">Pemakai</th>
                                <th class="px-3 py-2 text-left">Masalah</th>
                                <th class="px-3 py-2 text-left">Vendor</th>
                                <th class="px-3 py-2 text-left">Biaya</th>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($maintenances as $m)
                                @php
                                    $maintData = [
                                        'id' => $m->id,
                                        'asset_brand' => $m->asset?->brand,
                                        'asset_model' => $m->asset?->model,
                                        'asset_sn' => $m->asset?->serial_number,
                                        'current_user' => $m->asset?->currentUser?->name,
                                        'current_user_position' => $m->asset?->currentUser?->position,
                                        'type' => $m->type,
                                        'type_label' => $m->type_label ?? ucfirst($m->type),
                                        'issue' => $m->issue,
                                        'action' => $m->action,
                                        'vendor' => $m->vendor?->name,
                                        'technician' => $m->technician,
                                        'cost' => $m->cost,
                                        'cost_formatted' => $m->cost
                                            ? 'Rp ' . number_format($m->cost, 0, ',', '.')
                                            : null,
                                        'start_date' => $m->start_date?->format('d M Y'),
                                        'end_date' => $m->end_date?->format('d M Y'),
                                        'status' => $m->status,
                                        'status_label' => $m->status_label,
                                        'condition_before' => $m->condition_before,
                                        'condition_after' => $m->condition_after,
                                        'notes' => $m->notes,
                                        'can_complete' => in_array($m->status, ['open', 'in_progress']),
                                        'routes' => [
                                            'show' => route('siam.maintenances.show', $m),
                                            'edit' => route('siam.maintenances.edit', $m),
                                            'delete' => route('siam.maintenances.destroy', $m),
                                        ],
                                    ];
                                @endphp
                                <tr @click='openModal({{ json_encode($maintData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">
                                            {{ $m->asset?->brand }} {{ $m->asset?->model }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ $m->asset?->serial_number }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($m->asset?->currentUser)
                                            <div class="text-gray-800 text-xs font-medium">
                                                {{ $m->asset->currentUser->name }}
                                            </div>
                                            @if ($m->asset->currentUser->position)
                                                <div class="text-[10px] text-gray-500">
                                                    {{ $m->asset->currentUser->position }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $m->issue }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $m->vendor?->name ?? 'Internal' }}</td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $m->cost ? 'Rp ' . number_format($m->cost, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $m->start_date?->format('d M Y') }}
                                        @if ($m->end_date)
                                            → {{ $m->end_date->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColor = match ($m->status) {
                                                'open' => 'bg-yellow-100 text-yellow-700',
                                                'in_progress' => 'bg-blue-100 text-blue-700',
                                                'done' => 'bg-green-100 text-green-700',
                                                'cancelled' => 'bg-gray-100 text-gray-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $statusColor }}">
                                            {{ $m->status_label }}
                                        </span>
                                    </td>
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
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">
                                                    @if (request('search') || request('status') || request('type') || request('asset_id'))
                                                        Tidak ada perbaikan ditemukan
                                                    @else
                                                        Belum ada perbaikan
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    @if (request('search') || request('status') || request('type') || request('asset_id'))
                                                        Coba ubah filter atau reset
                                                    @else
                                                        Catat perbaikan pertama
                                                    @endif
                                                </p>
                                            </div>
                                            @if (request('search') || request('status') || request('type') || request('asset_id'))
                                                <a href="{{ route('siam.maintenances.index') }}"
                                                    class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                    Reset filter
                                                </a>
                                            @else
                                                <a href="{{ route('siam.maintenances.create') }}"
                                                    class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                    + Catat perbaikan
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($maintenances->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $maintenances->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODAL POPUP AKSI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="closeModal()">

            <div class="bg-gradient-to-br from-orange-500 to-amber-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.type_label"></span>
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.status_label"></span>
                        </div>
                        <h3 class="text-lg font-bold truncate"
                            x-text="(selected?.asset_brand ?? '') + ' ' + (selected?.asset_model ?? '')"></h3>
                        <p class="text-sm text-white/80 font-mono" x-text="'SN: ' + (selected?.asset_sn ?? '-')"></p>
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
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Pemakai Aset</div>
                        <div class="text-xs font-medium" x-text="selected?.current_user ?? 'Tidak ada pemakai'"></div>
                        <div class="text-xs text-gray-400" x-text="selected?.current_user_position ?? ''"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Masalah</div>
                        <div class="text-xs font-medium" x-text="selected?.issue ?? '-'"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Tindakan</div>
                        <div class="text-xs" x-text="selected?.action ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Vendor</div>
                        <div class="text-xs font-medium" x-text="selected?.vendor ?? 'Internal'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Teknisi</div>
                        <div class="text-xs font-medium" x-text="selected?.technician ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Biaya</div>
                        <div class="text-xs font-medium" x-text="selected?.cost_formatted ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tanggal</div>
                        <div class="text-xs font-medium">
                            <span x-text="selected?.start_date ?? '-'"></span>
                            <span x-show="selected?.end_date"> → <span x-text="selected?.end_date"></span></span>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Catatan</div>
                        <div class="text-xs" x-text="selected?.notes ?? '-'"></div>
                    </div>
                </div>
            </div>

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

                    {{-- TANDAI SELESAI --}}
                    <template x-if="selected?.can_complete">
                        <a :href="selected?.routes.show + '?complete=1'"
                            class="col-span-2 flex items-center gap-3 p-3 rounded-xl border border-green-200
                                   bg-green-50 hover:bg-green-100 hover:border-green-300 transition group">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-green-700">Tandai Selesai</div>
                                <div class="text-xs text-green-500">Perbaikan sudah selesai</div>
                            </div>
                        </a>
                    </template>

                    {{-- HAPUS --}}
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
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Perbaikan?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus data perbaikan untuk
                <strong class="text-gray-800"
                    x-text="(selected?.asset_brand ?? '') + ' ' + (selected?.asset_model ?? '')"></strong>?
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
        function maintenanceManager() {
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
