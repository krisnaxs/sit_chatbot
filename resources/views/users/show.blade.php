@extends('layouts.app')

@section('title', auth()->id() === $user->id ? 'Profil Saya' : 'Detail User — ' . $user->name)

@section('content')
    @php
        $isSelf = auth()->id() === $user->id;
        $isAdmin = auth()->user()->isAdmin();
        $canManage = $isSelf || $isAdmin;
        $isResetMode = $isAdmin && !$isSelf;
    @endphp

    <div class="max-w-7xl mx-auto" x-data="{ showPasswordModal: false, showResetModal: false }">

        {{-- ============================================================ --}}
        {{-- FLASH MESSAGE --}}
        {{-- ============================================================ --}}
        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-50 text-green-800 border border-green-200
                        flex items-start gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200
                        flex items-start gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1 text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <div class="space-y-6">

            {{-- HEADER (hanya admin yang bisa kembali ke daftar user) --}}
            @if (auth()->user()->isAdmin())
                <div>
                    <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke daftar user
                    </a>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- KARTU PROFIL --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-br from-indigo-500 to-violet-600 h-24"></div>

                <div class="px-6 pb-6">
                    <div class="flex flex-wrap items-end gap-4 -mt-12">
                        <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg">
                            <div
                                class="w-full h-full rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-3xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 pb-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>

                                {{-- Badge role --}}
                                <span
                                    class="px-2.5 py-0.5 text-xs rounded-full font-bold uppercase
                                    @if ($user->role === 'admin') bg-violet-100 text-violet-700 border border-violet-200
                                    @elseif ($user->role === 'support') bg-blue-100 text-blue-700 border border-blue-200
                                    @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                    {{ $user->role }}
                                </span>

                                {{-- Badge status --}}
                                @if ($user->is_active)
                                    <span
                                        class="px-2.5 py-0.5 text-xs rounded-full font-bold bg-green-100 text-green-700 border border-green-200">
                                        AKTIF
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 text-xs rounded-full font-bold bg-red-100 text-red-700 border border-red-200">
                                        NONAKTIF
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $user->position ?? '-' }}
                                @if ($user->department)
                                    • {{ $user->department->name }}
                                @endif
                            </p>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="flex flex-wrap gap-2 pb-1">
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('users.edit', $user) }}"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                                    ✏️ Edit
                                </a>
                            @endif

                            @if ($canManage)
                                <button type="button" @click="showPasswordModal = true"
                                    class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm font-medium
                                           inline-flex items-center gap-1.5">
                                    🔑 Ganti Password
                                </button>
                            @endif

                            @if ($isResetMode)
                                <button type="button" @click="showResetModal = true"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium
                                           inline-flex items-center gap-1.5">
                                    🔄 Reset Password
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Info grid --}}
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t text-sm">
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">NIP</dt>
                            <dd class="font-mono font-medium">{{ $user->nip ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Username</dt>
                            <dd class="font-mono font-medium text-indigo-700">{{ $user->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Email</dt>
                            <dd class="font-medium break-all">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">No. HP</dt>
                            <dd class="font-medium">{{ $user->phone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Departemen</dt>
                            <dd class="font-medium">{{ $user->department?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Jabatan</dt>
                            <dd class="font-medium">{{ $user->position ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Lokasi Kerja</dt>
                            <dd class="font-medium">{{ $user->location?->full_name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs uppercase tracking-wider">Terdaftar</dt>
                            <dd class="font-medium">{{ $user->created_at?->format('d M Y') ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- STATISTIK RINGKAS --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-cyan-500">
                    <div class="text-xs text-gray-500 uppercase">Aset Dipegang</div>
                    <div class="text-2xl font-bold text-cyan-600">{{ $user->currentAssets->count() }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-blue-500">
                    <div class="text-xs text-gray-500 uppercase">History Pemakai</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $user->assetAssignments->count() }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Peminjaman Aktif</div>
                    <div class="text-2xl font-bold text-amber-600">{{ $user->activeLoans->count() }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 border-l-4 border-pink-500">
                    <div class="text-xs text-gray-500 uppercase">Konsumable</div>
                    <div class="text-2xl font-bold text-pink-600">{{ $user->consumableTransactions->count() }}</div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- ASET YANG SEDANG DIPEGANG --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                        Aset yang Sedang Dipegang
                        <span class="text-xs text-gray-500 font-normal">
                            ({{ $user->currentAssets->count() }})
                        </span>
                    </h2>
                </div>

                @if ($user->currentAssets->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Tidak ada aset yang sedang dipegang.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($user->currentAssets as $asset)
                            <a href="{{ route('siam.assets.show', $asset) }}"
                                class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 hover:border-cyan-300 hover:bg-cyan-50 transition group">

                                <div class="w-12 h-12 rounded-lg bg-cyan-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <div class="font-medium text-gray-800 truncate">
                                            {{ $asset->brand }} {{ $asset->model }}
                                        </div>

                                        @php
                                            $statusMap = [
                                                'available' => [
                                                    'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                    'Tersedia',
                                                ],
                                                'in_use' => ['bg-blue-100 text-blue-700 border-blue-200', 'Dipakai'],
                                                'loaned' => [
                                                    'bg-amber-100 text-amber-700 border-amber-200',
                                                    'Dipinjam',
                                                ],
                                                'maintenance' => [
                                                    'bg-orange-100 text-orange-700 border-orange-200',
                                                    'Perbaikan',
                                                ],
                                                'retired' => ['bg-gray-100 text-gray-700 border-gray-200', 'Pensiun'],
                                                'lost' => ['bg-red-100 text-red-700 border-red-200', 'Hilang'],
                                            ];
                                            [$statusCls, $statusLabel] = $statusMap[$asset->status] ?? [
                                                'bg-gray-100 text-gray-700 border-gray-200',
                                                $asset->status,
                                            ];
                                        @endphp
                                        <span
                                            class="px-2 py-0.5 text-[10px] rounded-full font-bold border {{ $statusCls }} shrink-0">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-gray-500 font-mono">
                                        SN: {{ $asset->serial_number }}
                                    </div>

                                    @if ($asset->hostname)
                                        <div class="text-xs text-gray-600 font-mono mt-0.5">
                                            🖥️ {{ $asset->hostname }}
                                        </div>
                                    @endif

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $asset->category?->name }}
                                        @if ($asset->currentLocation)
                                            • 📍 {{ $asset->currentLocation->full_name }}
                                        @endif
                                    </div>

                                    @if ($asset->condition_percent !== null)
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full
                                        {{ $asset->condition_percent >= 80
                                            ? 'bg-green-500'
                                            : ($asset->condition_percent >= 60
                                                ? 'bg-yellow-500'
                                                : ($asset->condition_percent >= 40
                                                    ? 'bg-orange-500'
                                                    : 'bg-red-500')) }}"
                                                    style="width: {{ $asset->condition_percent }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $asset->condition_percent }}%</span>
                                        </div>
                                    @endif
                                </div>

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 group-hover:text-cyan-600 transition shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- HISTORY PEMAKAI --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        History Pemakai
                        <span class="text-xs text-gray-500 font-normal">
                            ({{ $user->assetAssignments->count() }})
                        </span>
                    </h2>
                </div>

                @if ($user->assetAssignments->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Belum ada history pemakaian aset.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($user->assetAssignments->sortByDesc('assigned_at') as $a)
                            <div
                                class="flex items-start gap-3 p-3 rounded-xl border
                                {{ $a->is_active ? 'border-blue-300 bg-blue-50' : 'border-gray-200' }}">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('siam.assets.show', $a->asset) }}"
                                            class="font-medium text-gray-800 hover:text-blue-600 hover:underline">
                                            {{ $a->asset?->brand }} {{ $a->asset?->model }}
                                        </a>
                                        @if ($a->is_active)
                                            <span class="px-2 py-0.5 text-xs rounded bg-blue-600 text-white font-semibold">
                                                SEDANG DIPAKAI
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">
                                                Sudah Kembali
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono mt-0.5">
                                        SN: {{ $a->asset?->serial_number }}
                                    </div>

                                    @if ($a->hostname)
                                        <div class="text-xs text-gray-600 font-mono mt-0.5">
                                            🖥️ Hostname: <span class="font-medium">{{ $a->hostname }}</span>
                                        </div>
                                    @elseif ($a->asset?->hostname)
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">
                                            🖥️ Hostname (terkini): {{ $a->asset->hostname }}
                                        </div>
                                    @endif

                                    <div class="text-xs text-gray-600 mt-1">
                                        📅 {{ $a->assigned_at?->format('d M Y') ?? '-' }}
                                        →
                                        {{ $a->returned_at?->format('d M Y') ?? 'Sekarang' }}
                                        @if ($a->duration_days !== null)
                                            <span class="text-gray-400">({{ $a->duration_days }} hari)</span>
                                        @endif
                                    </div>
                                    @if ($a->condition_on_assign !== null || $a->condition_on_return !== null)
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Kondisi: serah {{ $a->condition_on_assign ?? '-' }}%
                                            @if ($a->condition_on_return !== null)
                                                → kembali {{ $a->condition_on_return }}%
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- PEMINJAMAN AKTIF --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Peminjaman Aktif
                        <span class="text-xs text-gray-500 font-normal">
                            ({{ $user->activeLoans->count() }})
                        </span>
                    </h2>
                </div>

                @if ($user->activeLoans->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Tidak ada peminjaman aktif.</p>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($user->activeLoans as $loan)
                            <li class="flex items-start gap-3 p-3 rounded-xl border border-amber-200 bg-amber-50">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('siam.assets.show', $loan->asset) }}"
                                        class="font-medium text-gray-800 hover:text-amber-600 hover:underline">
                                        {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                                    </a>
                                    <div class="text-xs text-gray-500 font-mono">
                                        SN: {{ $loan->asset?->serial_number }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        📅 Pinjam: {{ $loan->loan_date?->format('d M Y') }}
                                        → Jatuh tempo: {{ $loan->due_date?->format('d M Y') }}
                                        @if ($loan->is_overdue)
                                            <span
                                                class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-red-100 text-red-700 font-bold">
                                                TERLAMBAT
                                            </span>
                                        @endif
                                    </div>
                                    @if ($loan->purpose)
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Tujuan: {{ $loan->purpose }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- KONSUMABLE YANG PERNAH DIMINTA --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        Konsumable yang Pernah Diminta
                        <span class="text-xs text-gray-500 font-normal">
                            ({{ $user->consumableTransactions->count() }})
                        </span>
                    </h2>
                </div>

                @if ($user->consumableTransactions->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 italic">Belum pernah minta konsumable.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Tanggal</th>
                                    <th class="px-3 py-2 text-left">Item</th>
                                    <th class="px-3 py-2 text-left">Tipe</th>
                                    <th class="px-3 py-2 text-right">Qty</th>
                                    <th class="px-3 py-2 text-left">Keperluan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($user->consumableTransactions->sortByDesc('transaction_date') as $t)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-xs">
                                            {{ $t->transaction_date?->format('d M Y H:i') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-xs font-medium">
                                            {{ $t->consumable?->name ?? '-' }}
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
                                        <td class="px-3 py-2 text-xs">{{ $t->purpose ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
        {{-- ⬆️ TUTUP: space-y-6 --}}

        {{-- ============================================================ --}}
        {{-- MODAL GANTI PASSWORD --}}
        {{-- ============================================================ --}}
        @if ($canManage)
            <div x-show="showPasswordModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showPasswordModal = false"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                    @click.away="showPasswordModal = false">

                    {{-- HEADER --}}
                    <div class="bg-gradient-to-br from-amber-400 to-orange-500 px-6 py-5 text-white">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">Ganti Password</h3>
                                    <p class="text-sm text-white/80">
                                        {{ $isSelf ? 'Akun Anda sendiri' : $user->name }}
                                    </p>
                                </div>
                            </div>
                            <button @click="showPasswordModal = false"
                                class="p-1.5 rounded-lg hover:bg-white/20 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <form action="{{ route('users.update-password', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="px-6 py-5 space-y-4">

                            @if ($isSelf && !$isAdmin)
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password Lama</label>
                                    <input type="password" name="current_password" required
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    @error('current_password')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                                <input type="password" name="new_password" required minlength="8"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-amber-500">
                                @error('new_password')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                                <input type="password" name="new_password_confirmation" required minlength="8"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                            <button type="button" @click="showPasswordModal = false"
                                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500
                                       hover:from-amber-600 hover:to-orange-600 text-white text-sm font-semibold
                                       shadow-lg shadow-amber-500/30 transition">
                                Simpan Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL RESET PASSWORD (khusus admin, user lain) --}}
        {{-- ============================================================ --}}
        @if ($isResetMode)
            <div x-show="showResetModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showResetModal = false"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                    @click.away="showResetModal = false">

                    {{-- HEADER --}}
                    <div class="bg-gradient-to-br from-red-500 to-rose-600 px-6 py-5 text-white">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">Reset Password</h3>
                                    <p class="text-sm text-white/80">{{ $user->name }}</p>
                                </div>
                            </div>
                            <button @click="showResetModal = false" class="p-1.5 rounded-lg hover:bg-white/20 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="px-6 py-5">
                        <div class="flex justify-center mb-4">
                            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 text-center mb-4">
                            Password <strong>{{ $user->name }}</strong> akan direset ke default:
                        </p>

                        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-center mb-4">
                            <code class="font-mono text-base font-bold text-red-600">password123</code>
                        </div>

                        <p class="text-xs text-gray-500 text-center">
                            ⚠️ User harus login ulang dengan password baru setelah direset.
                        </p>
                    </div>

                    {{-- FOOTER --}}
                    <form action="{{ route('users.reset-password', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                            <button type="button" @click="showResetModal = false"
                                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-gradient-to-br from-red-500 to-rose-600
                                       hover:from-red-600 hover:to-rose-700 text-white text-sm font-semibold
                                       shadow-lg shadow-red-500/30 transition">
                                Ya, Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
    {{-- ⬆️ TUTUP: max-w-7xl --}}
@endsection
