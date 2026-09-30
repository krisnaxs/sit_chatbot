@extends('layouts.app')

@section('title', 'Daftar User & Aset — SIAM')

@section('content')
    <div x-data="{
        expandedRows: [],
        toggleRow(id) {
            if (this.expandedRows.includes(id)) {
                this.expandedRows = this.expandedRows.filter(r => r !== id);
            } else {
                this.expandedRows.push(id);
            }
        },
        isExpanded(id) {
            return this.expandedRows.includes(id);
        }
    }" class="space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Daftar User & Aset</h1>
                <p class="text-sm text-gray-500">Klik baris user untuk lihat detail aset/konsumabel</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('siam.user-assets.export.excel', request()->query()) }}"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Excel
                </a>
                <a href="{{ route('siam.user-assets.export.pdf', request()->query()) }}" target="_blank"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h4" />
                    </svg>
                    PDF
                </a>
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-400 px-3 py-2.5">
                <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Total User</div>
                <div class="text-xl font-bold text-gray-800 leading-tight">{{ $summary['total_users'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-indigo-500 px-3 py-2.5">
                <div class="text-[10px] text-indigo-500 uppercase font-semibold tracking-wide">Pegang Aset</div>
                <div class="text-xl font-bold text-indigo-600 leading-tight">{{ $summary['users_with_assets'] }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-300 px-3 py-2.5">
                <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wide">Tidak Pegang</div>
                <div class="text-xl font-bold text-gray-500 leading-tight">{{ $summary['users_without_assets'] }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 px-3 py-2.5">
                <div class="text-[10px] text-blue-500 uppercase font-semibold tracking-wide">Aset Aktif</div>
                <div class="text-xl font-bold text-blue-600 leading-tight">{{ $summary['total_assets_held'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-amber-500 px-3 py-2.5">
                <div class="text-[10px] text-amber-500 uppercase font-semibold tracking-wide">Pinjaman</div>
                <div class="text-xl font-bold text-amber-600 leading-tight">{{ $summary['total_loans_active'] }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-pink-500 px-3 py-2.5">
                <div class="text-[10px] text-pink-500 uppercase font-semibold tracking-wide">Konsumabel</div>
                <div class="text-xl font-bold text-pink-600 leading-tight">{{ $summary['total_consumables_out'] }}
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('siam.user-assets.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cari (Nama / NIP / Email)</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Contoh: Budi atau 10000001"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                    <select name="department_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pegang Aset?</label>
                    <select name="has_asset" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="yes" @selected(request('has_asset') === 'yes')>Ya, pegang aset</option>
                        <option value="no" @selected(request('has_asset') === 'no')>Tidak pegang</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 md:col-span-4">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                        Filter
                    </button>
                    <a href="{{ route('siam.user-assets.index') }}"
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
                            <th class="px-3 py-2 text-left w-12">No</th>
                            <th class="px-3 py-2 text-left">User</th>
                            <th class="px-3 py-2 text-left">Departemen</th>
                            <th class="px-3 py-2 text-left">Jabatan</th>
                            <th class="px-3 py-2 text-center w-20">Aset</th>
                            <th class="px-3 py-2 text-center w-24">Pinjaman</th>
                            <th class="px-3 py-2 text-center w-24">Konsumabel</th>
                            <th class="px-3 py-2 text-center w-16">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $index => $user)
                            <tr @click="toggleRow({{ $user->id }})"
                                class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                <td class="px-3 py-2 text-xs text-gray-500 font-medium">
                                    {{ $users->firstItem() + $index }}
                                </td>

                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                                flex items-center justify-center text-white font-bold text-xs shrink-0">
                                            {{ $user->initial }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-800 truncate">{{ $user->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono truncate">
                                                {{ $user->nip ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-xs text-gray-600">
                                    {{ $user->department?->name ?? '-' }}
                                </td>

                                <td class="px-3 py-2 text-xs text-gray-600">
                                    {{ $user->position ?? '-' }}
                                </td>

                                <td class="px-3 py-2 text-center">
                                    @if ($user->total_assets > 0)
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-700 font-semibold">
                                            {{ $user->total_assets }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">0</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-center">
                                    @if ($user->total_loans > 0)
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700 font-semibold">
                                            {{ $user->total_loans }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">0</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-center">
                                    @if ($user->total_consumables > 0)
                                        <span class="px-2 py-0.5 text-xs rounded bg-pink-100 text-pink-700 font-semibold">
                                            {{ $user->total_consumables }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">0</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200 inline-block"
                                        :class="isExpanded({{ $user->id }}) ? 'rotate-180 text-indigo-600' : ''"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </td>
                            </tr>

                            {{-- EXPANDED DETAIL ROW --}}
                            <tr x-show="isExpanded({{ $user->id }})" x-cloak
                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100">
                                <td colspan="8" class="bg-gray-50 p-0">
                                    <div class="p-4 space-y-4 border-t-2 border-indigo-200">

                                        {{-- Info singkat user --}}
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                                            @if ($user->email)
                                                <span>📧 {{ $user->email }}</span>
                                            @endif
                                            @if ($user->phone)
                                                <span>📞 {{ $user->phone }}</span>
                                            @endif
                                            @if ($user->location)
                                                <span>📍 {{ $user->location->full_name }}</span>
                                            @endif
                                            <a href="{{ route('users.show', $user) }}"
                                                class="ml-auto text-indigo-600 hover:underline font-semibold">
                                                Lihat Profil User →
                                            </a>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                                            {{-- ASET --}}
                                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                                <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 text-indigo-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                    Aset
                                                    <span
                                                        class="text-xs text-gray-500 font-normal">({{ $user->currentAssets->count() }})</span>
                                                </h4>
                                                @if ($user->currentAssets->isEmpty())
                                                    <p class="text-xs text-gray-400 italic">Tidak pegang aset.</p>
                                                @else
                                                    <div class="space-y-2">
                                                        @foreach ($user->currentAssets as $a)
                                                            @php
                                                                $statusText = $a->status_label;
                                                                if ($a->status === 'in_use') {
                                                                    $asg = $a->assignments->first();
                                                                    if ($asg && $asg->assigned_at) {
                                                                        $statusText =
                                                                            'Dipakai (' .
                                                                            $asg->assigned_at->format('d M Y') .
                                                                            ')';
                                                                    }
                                                                } elseif ($a->status === 'loaned') {
                                                                    $ln =
                                                                        $a->loans->first() ?? $a->assignments->first();
                                                                    $dt = $ln?->loan_date ?? $ln?->assigned_at;
                                                                    if ($dt) {
                                                                        $statusText =
                                                                            'Dipinjam (' . $dt->format('d M Y') . ')';
                                                                    }
                                                                } elseif ($a->status === 'maintenance') {
                                                                    $mt = $a->maintenances->first();
                                                                    if ($mt && $mt->start_date) {
                                                                        $statusText =
                                                                            'Perbaikan (' .
                                                                            $mt->start_date->format('d M Y') .
                                                                            ')';
                                                                    }
                                                                }
                                                            @endphp
                                                            <a href="{{ route('siam.assets.show', $a) }}"
                                                                class="block p-2 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition">
                                                                <div class="font-semibold text-xs text-gray-800">
                                                                    {{ $a->brand }} {{ $a->model }}
                                                                </div>
                                                                <div class="text-[10px] text-gray-500 font-mono">
                                                                    SN: {{ $a->serial_number }}
                                                                </div>
                                                                <div class="flex items-center gap-1 mt-1 flex-wrap">
                                                                    <span
                                                                        class="px-1.5 py-0.5 text-[9px] rounded bg-indigo-100 text-indigo-700 font-semibold">
                                                                        {{ $statusText }}
                                                                    </span>
                                                                    @if ($a->currentLocation)
                                                                        <span class="text-[9px] text-gray-500">📍
                                                                            {{ $a->currentLocation->full_name }}</span>
                                                                    @endif
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- PINJAMAN --}}
                                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                                <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Pinjaman Aktif
                                                    <span
                                                        class="text-xs text-gray-500 font-normal">({{ $user->activeLoans->count() }})</span>
                                                </h4>
                                                @if ($user->activeLoans->isEmpty())
                                                    <p class="text-xs text-gray-400 italic">Tidak ada pinjaman.</p>
                                                @else
                                                    <div class="space-y-2">
                                                        @foreach ($user->activeLoans as $ln)
                                                            <div
                                                                class="p-2 rounded-lg border
                                                            {{ $ln->is_overdue ? 'border-red-300 bg-red-50' : 'border-amber-200 bg-amber-50/40' }}">
                                                                <div class="font-semibold text-xs text-gray-800">
                                                                    {{ $ln->asset?->brand }}
                                                                    {{ $ln->asset?->model }}
                                                                </div>
                                                                <div class="text-[10px] text-gray-500 font-mono">
                                                                    SN: {{ $ln->asset?->serial_number }}
                                                                </div>
                                                                <div class="text-[10px] mt-1">
                                                                    📅 {{ $ln->loan_date?->format('d M Y') }}
                                                                    → {{ $ln->due_date?->format('d M Y') }}
                                                                </div>
                                                                @if ($ln->is_overdue)
                                                                    <div class="text-[10px] text-red-600 font-bold mt-0.5">
                                                                        ⚠️ Terlambat</div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- KONSUMABEL --}}
                                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                                @php $outTrx = $user->consumableTransactions->where('type', 'out'); @endphp
                                                <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-600"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                    Konsumabel
                                                    <span
                                                        class="text-xs text-gray-500 font-normal">({{ $outTrx->count() }})</span>
                                                </h4>
                                                @if ($outTrx->isEmpty())
                                                    <p class="text-xs text-gray-400 italic">Tidak ada konsumabel.
                                                    </p>
                                                @else
                                                    <div class="space-y-2 max-h-64 overflow-y-auto">
                                                        @foreach ($outTrx as $t)
                                                            <div
                                                                class="p-2 rounded-lg border border-gray-200 hover:bg-pink-50 transition">
                                                                <div class="flex items-start justify-between gap-2">
                                                                    <div class="min-w-0">
                                                                        <div
                                                                            class="font-semibold text-xs text-gray-800 truncate">
                                                                            {{ $t->consumable?->name ?? '-' }}
                                                                        </div>
                                                                        <div class="text-[10px] text-gray-500">
                                                                            {{ $t->consumable?->category?->name ?? '-' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right shrink-0">
                                                                        <div class="text-xs font-bold text-pink-600">
                                                                            {{ $t->quantity }}
                                                                            {{ $t->consumable?->unit }}
                                                                        </div>
                                                                        <div class="text-[9px] text-gray-500">
                                                                            {{ $t->transaction_date?->format('d M Y') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if ($t->location || $t->asset)
                                                                    <div class="text-[9px] text-gray-500 mt-1">
                                                                        📍
                                                                        {{ $t->location?->full_name ?? ($t->asset?->serial_number ?? '-') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                    Tidak ada user ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t">
                {{ $users->links() }}
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush
