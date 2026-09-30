@extends('layouts.app')

@section('title', 'Detail Aset — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="assetShowManager()">

        <div class="space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ route('siam.assets.index') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke daftar aset
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $asset->brand }} {{ $asset->model }}
                    </h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="text-sm text-gray-500 font-mono">SN: {{ $asset->serial_number }}</p>
                        <a href="{{ route('siam.asset-types.index', ['search' => $asset->model]) }}" target="_blank"
                            class="text-xs text-indigo-600 hover:underline">
                            Lihat di Master →
                        </a>
                    </div>
                </div>
                <div class="flex gap-2">
                    <span
                        class="px-3 py-1 text-sm rounded
                        {{ $asset->ownership_type === 'owned' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $asset->ownership_label }}
                    </span>
                    <span
                        class="px-3 py-1 text-sm rounded
                        {{ match ($asset->status) {
                            'available' => 'bg-green-100 text-green-700',
                            'in_use' => 'bg-blue-100 text-blue-700',
                            'loaned' => 'bg-yellow-100 text-yellow-700',
                            'maintenance' => 'bg-orange-100 text-orange-700',
                            'retired' => 'bg-gray-100 text-gray-700',
                            'lost' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        } }}">
                        {{ $asset->status_label }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Info Aset --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-4">Informasi Aset</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                            <div>
                                <dt class="text-gray-500">Kode Aset</dt>
                                <dd class="font-mono text-indigo-700">{{ $asset->asset_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Serial Number</dt>
                                <dd class="font-mono">{{ $asset->serial_number }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Brand</dt>
                                <dd class="font-medium">{{ $asset->brand ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Model</dt>
                                <dd class="font-medium">{{ $asset->model ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Hostname</dt>
                                <dd class="font-mono">{{ $asset->hostname ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Kategori</dt>
                                <dd>{{ $asset->category?->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">OS</dt>
                                <dd>{{ $asset->os ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Lisensi OS</dt>
                                <dd>{{ $asset->os_license ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Tanggal Beli</dt>
                                <dd>{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Umur Aset</dt>
                                <dd>
                                    @if ($asset->purchase_date)
                                        {{ $asset->purchase_date->diffForHumans(null, true) }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Harga</dt>
                                <dd>{{ $asset->purchase_price ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.') : '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Garansi Expire</dt>
                                <dd>{{ $asset->warranty_expire?->format('d M Y') ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Kondisi</dt>
                                <dd>
                                    @if ($asset->condition_percent !== null)
                                        <span class="font-medium">{{ $asset->condition_percent }}%</span>
                                        <span class="text-gray-500 text-xs">—
                                            {{ $asset->condition_notes ?? 'tanpa catatan' }}</span>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if ($asset->specification)
                            <div class="mt-6 pt-4 border-t">
                                <h3 class="font-medium text-gray-700 mb-2 text-sm">Spesifikasi</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    @foreach ($asset->specification as $key => $val)
                                        <div>
                                            <dt class="text-gray-500 capitalize">{{ $key }}</dt>
                                            <dd>{{ $val }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif

                        @if ($asset->notes)
                            <div class="mt-6 pt-4 border-t">
                                <h3 class="font-medium text-gray-700 mb-2 text-sm">Catatan</h3>
                                <p class="text-sm text-gray-600">{{ $asset->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- History Pemakai --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800">
                                History Pemakai
                                <span class="text-xs text-gray-500 font-normal">
                                    ({{ $asset->assignments->count() }} record)
                                </span>
                            </h2>
                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                    + Assign ke User
                                </a>
                            @endif
                        </div>

                        @if ($asset->assignments->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum pernah di-assign ke user.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($asset->assignments as $a)
                                    <div
                                        class="flex items-start gap-3 p-3 rounded-xl border
                                        {{ $a->is_active ? 'border-blue-300 bg-blue-50' : 'border-gray-200' }}">
                                        <div class="flex-shrink-0">
                                            @if ($a->user)
                                                <a href="{{ route('users.show', $a->user) }}">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold hover:bg-indigo-700 transition">
                                                        {{ strtoupper(substr($a->user->name, 0, 1)) }}
                                                    </div>
                                                </a>
                                            @else
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-bold">
                                                    ?</div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if ($a->user)
                                                    <a href="{{ route('users.show', $a->user) }}"
                                                        class="font-medium text-indigo-600 hover:underline hover:text-indigo-800">
                                                        {{ $a->user->name }}
                                                    </a>
                                                @else
                                                    <span class="font-medium">-</span>
                                                @endif
                                                @if ($a->is_active)
                                                    <span class="px-2 py-0.5 text-xs rounded bg-blue-600 text-white">
                                                        Sedang Dipakai
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $a->user?->position ?? '-' }}
                                                @if ($a->department)
                                                    — {{ $a->department->name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                📅 {{ $a->assigned_at?->format('d M Y') ?? '-' }}
                                                →
                                                {{ $a->returned_at?->format('d M Y') ?? 'Sekarang' }}
                                                @if ($a->duration_days !== null)
                                                    <span class="text-gray-400">({{ $a->duration_days }} hari)</span>
                                                @endif
                                            </div>
                                            @if ($a->location)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    📍 {{ $a->location->full_name }}
                                                </div>
                                            @endif
                                            @if ($a->condition_on_assign !== null || $a->condition_on_return !== null)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Kondisi: serah {{ $a->condition_on_assign ?? '-' }}%
                                                    @if ($a->condition_on_return !== null)
                                                        → kembali {{ $a->condition_on_return }}%
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($a->notes)
                                                <div class="text-xs text-gray-500 mt-1 italic">{{ $a->notes }}</div>
                                            @endif

                                            @if ($a->is_active)
                                                <form method="POST" action="{{ route('siam.assignments.return', $a) }}"
                                                    class="mt-2"
                                                    @submit.prevent="confirmReturn($event, '{{ $a->user?->name ?? 'user ini' }}')">
                                                    @csrf
                                                    <input type="hidden" name="returned_at"
                                                        value="{{ now()->format('Y-m-d H:i:s') }}">
                                                    <input type="hidden" name="condition_on_return"
                                                        value="{{ $asset->condition_percent ?? 100 }}">
                                                    <button type="submit"
                                                        class="text-xs px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                                                        Kembalikan Aset
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- History Peminjaman --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-4">
                            History Peminjaman
                            <span class="text-xs text-gray-500 font-normal">({{ $asset->loans->count() }} record)</span>
                        </h2>

                        @if ($asset->loans->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum ada peminjaman.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($asset->loans as $loan)
                                    <li
                                        class="border-l-2 {{ $loan->is_overdue ? 'border-red-500' : 'border-blue-400' }} pl-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($loan->user)
                                                <a href="{{ route('users.show', $loan->user) }}"
                                                    class="font-medium text-sm text-indigo-600 hover:underline hover:text-indigo-800">
                                                    {{ $loan->user->name }}
                                                </a>
                                            @else
                                                <span class="font-medium text-sm">-</span>
                                            @endif
                                            <span
                                                class="px-2 py-0.5 text-xs rounded
                                                {{ $loan->status === 'returned'
                                                    ? 'bg-green-100 text-green-700'
                                                    : ($loan->is_overdue
                                                        ? 'bg-red-100 text-red-700'
                                                        : 'bg-blue-100 text-blue-700') }}">
                                                {{ $loan->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            📅 {{ $loan->loan_date?->format('d M Y') }}
                                            → {{ $loan->due_date?->format('d M Y') }}
                                            @if ($loan->returned_at)
                                                <span class="text-green-600">(kembali
                                                    {{ $loan->returned_at->format('d M Y') }})</span>
                                            @endif
                                        </div>
                                        @if ($loan->purpose)
                                            <div class="text-xs text-gray-600 mt-1">Tujuan: {{ $loan->purpose }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- History Perbaikan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800">
                                History Perbaikan
                                <span class="text-xs text-gray-500 font-normal">({{ $asset->maintenances->count() }}
                                    record)</span>
                            </h2>
                            <a href="{{ route('siam.maintenances.create', ['asset_id' => $asset->id]) }}"
                                class="text-xs px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                                + Catat Perbaikan
                            </a>
                        </div>

                        @if ($asset->maintenances->isEmpty())
                            <p class="text-sm text-gray-400 italic">Belum ada perbaikan.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($asset->maintenances as $m)
                                    <li class="border-l-2 border-orange-400 pl-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium">{{ $m->issue }}</span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded
                                                {{ $m->status === 'done'
                                                    ? 'bg-green-100 text-green-700'
                                                    : ($m->status === 'in_progress'
                                                        ? 'bg-blue-100 text-blue-700'
                                                        : ($m->status === 'cancelled'
                                                            ? 'bg-gray-100 text-gray-700'
                                                            : 'bg-yellow-100 text-yellow-700')) }}">
                                                {{ $m->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $m->start_date?->format('d M Y') }}
                                            @if ($m->end_date)
                                                → {{ $m->end_date->format('d M Y') }}
                                            @endif
                                            • {{ $m->type_label }}
                                            @if ($m->vendor)
                                                • {{ $m->vendor->name }}
                                            @endif
                                        </div>
                                        @if ($m->action)
                                            <div class="text-xs text-gray-600 mt-1">Tindakan: {{ $m->action }}</div>
                                        @endif
                                        @if ($m->cost)
                                            <div class="text-xs text-gray-600 mt-1">Biaya: Rp
                                                {{ number_format($m->cost, 0, ',', '.') }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>

                {{-- KOLOM KANAN (1/3) --}}
                <div class="space-y-6">

                    {{-- Pemakai Saat Ini --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Pemakai Saat Ini</h2>
                        @if ($asset->currentUser)
                            <div class="flex items-center gap-3">
                                <a href="{{ route('users.show', $asset->currentUser) }}" class="block shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center text-lg font-bold hover:bg-indigo-700 transition">
                                        {{ $asset->currentUser->initial ?? strtoupper(substr($asset->currentUser->name, 0, 1)) }}
                                    </div>
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('users.show', $asset->currentUser) }}"
                                        class="font-medium truncate block text-indigo-600 hover:underline hover:text-indigo-800">
                                        {{ $asset->currentUser->name }}
                                    </a>
                                    <div class="text-xs text-gray-500 truncate">{{ $asset->currentUser->position }}</div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $asset->currentUser->department?->name }}</div>
                                </div>
                            </div>
                            @if ($asset->currentLocation)
                                <div class="mt-3 pt-3 border-t text-xs text-gray-600">
                                    📍 {{ $asset->currentLocation->full_name }}
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                                    Pindah / Assign Ulang
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic mb-3">Belum dipakai siapa pun.</p>
                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.assignments.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-xs px-3 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg font-medium">
                                    + Assign ke User
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Kepemilikan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Kepemilikan</h2>
                        @if ($asset->ownership)
                            <dl class="text-sm space-y-3">
                                <div>
                                    <dt class="text-gray-500 text-xs">Tipe</dt>
                                    <dd class="font-medium">{{ $asset->ownership_label }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 text-xs">Vendor</dt>
                                    <dd>{{ $asset->ownership->vendor?->name ?? '-' }}</dd>
                                </div>

                                @if ($asset->ownership->contract_number)
                                    <div class="pt-2 border-t">
                                        <dt class="text-gray-500 text-xs">No. Kontrak</dt>
                                        <dd class="font-mono text-xs">{{ $asset->ownership->contract_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 text-xs">Periode Sewa</dt>
                                        <dd class="text-xs">
                                            {{ $asset->ownership->contract_start?->format('d M Y') }}
                                            → {{ $asset->ownership->contract_end?->format('d M Y') }}
                                        </dd>
                                    </div>
                                    @if ($asset->ownership->monthly_cost)
                                        <div>
                                            <dt class="text-gray-500 text-xs">Biaya Sewa / Bulan</dt>
                                            <dd class="text-xs">Rp
                                                {{ number_format($asset->ownership->monthly_cost, 0, ',', '.') }}</dd>
                                        </div>
                                    @endif
                                    @if ($asset->ownership->days_until_expiry !== null)
                                        <div>
                                            <dt class="text-gray-500 text-xs">Sisa Waktu</dt>
                                            <dd
                                                class="{{ $asset->ownership->days_until_expiry < 30 ? 'text-red-600 font-medium' : '' }}">
                                                {{ $asset->ownership->days_until_expiry }} hari
                                            </dd>
                                        </div>
                                    @endif
                                @endif

                                @if ($asset->ownership->invoice_number)
                                    <div class="pt-2 border-t">
                                        <dt class="text-gray-500 text-xs">No. Invoice</dt>
                                        <dd class="font-mono text-xs">{{ $asset->ownership->invoice_number }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada data kepemilikan.</p>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-800 mb-3">Aksi</h2>
                        <div class="space-y-2">
                            <a href="{{ route('siam.assets.edit', $asset) }}"
                                class="block text-center text-sm px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                                ✏️ Edit Aset
                            </a>

                            @if ($asset->status === 'available')
                                <a href="{{ route('siam.loans.create', ['asset_id' => $asset->id]) }}"
                                    class="block text-center text-sm px-3 py-2 bg-yellow-100 text-yellow-800 hover:bg-yellow-200 rounded-lg font-medium">
                                    📤 Pinjamkan Sementara
                                </a>
                            @endif

                            <form method="POST" action="{{ route('siam.assets.destroy', $asset) }}"
                                @submit.prevent="confirmDelete('{{ $asset->serial_number }}', $event)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full text-center text-sm px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg font-medium">
                                    🗑️ Hapus Aset
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- MODAL KONFIRMASI (GENERIC) --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeConfirm()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-96"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center"
                        :class="{
                            'bg-red-100': confirmType === 'delete',
                            'bg-amber-100': confirmType === 'return',
                        }">
                        <svg x-show="confirmType === 'delete'" xmlns="http://www.w3.org/2000/svg"
                            class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <svg x-show="confirmType === 'return'" xmlns="http://www.w3.org/2000/svg"
                            class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1" x-text="confirmTitle"></h3>
                <p class="text-sm text-gray-500 text-center mb-6" x-html="confirmMessage"></p>

                <div class="flex gap-2">
                    <button type="button" @click="closeConfirm()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="flex-1 px-4 py-2.5 rounded-xl font-semibold transition text-white shadow-lg"
                        :class="{
                            'bg-red-600 hover:bg-red-700 shadow-red-500/30': confirmType === 'delete',
                            'bg-amber-600 hover:bg-amber-700 shadow-amber-500/30': confirmType === 'return',
                        }"
                        x-text="confirmButton"></button>
                </div>
            </div>
        </div>

        {{-- TOAST --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[130] flex items-center gap-3
                   min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">

            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function assetShowManager() {
            return {
                showConfirmModal: false,
                confirmType: '',
                confirmTitle: '',
                confirmMessage: '',
                confirmButton: '',
                pendingForm: null,
                pendingAction: null,

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                confirmReturn(event, userName) {
                    this.confirmType = 'return';
                    this.confirmTitle = 'Kembalikan Aset?';
                    this.confirmMessage =
                        `Aset ini akan dikembalikan dari <strong>${userName}</strong>. Status aset akan kembali menjadi <strong>Tersedia</strong>.`;
                    this.confirmButton = 'Ya, Kembalikan';
                    this.pendingForm = event.target.closest('form');
                    this.pendingAction = 'return';
                    this.showConfirmModal = true;
                },

                confirmDelete(serialNumber, event) {
                    this.confirmType = 'delete';
                    this.confirmTitle = 'Hapus Aset?';
                    this.confirmMessage =
                        `Aset dengan SN <strong>${serialNumber}</strong> akan dihapus permanen. Data yang dihapus tidak bisa dikembalikan.`;
                    this.confirmButton = 'Ya, Hapus';
                    this.pendingForm = event.target.closest('form');
                    this.pendingAction = 'delete';
                    this.showConfirmModal = true;
                },

                executeConfirm() {
                    if (this.pendingForm) {
                        this.pendingForm.submit();
                    }
                    this.closeConfirm();
                },

                closeConfirm() {
                    this.showConfirmModal = false;
                    this.pendingForm = null;
                    this.pendingAction = null;
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                init() {
                    @if (session('success'))
                        this.showToast(@json(session('success')), 'success');
                    @endif
                    @if (session('error'))
                        this.showToast(@json(session('error')), 'error');
                    @endif

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showConfirmModal) {
                            this.closeConfirm();
                        }
                    });
                }
            }
        }
    </script>
@endpush
