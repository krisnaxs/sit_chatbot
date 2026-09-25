<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ auth()->id() === $user->id ? 'Profil Saya' : 'Detail User — ' . $user->name }}
    </title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header :title="auth()->id() === $user->id ? 'Profil Saya' : 'Detail User'" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="space-y-6">

            {{-- HEADER (hanya admin yang bisa kembali ke daftar user) --}}
            @if (auth()->user()->isAdmin())
                <div>
                    <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke daftar user
                    </a>
                </div>
            @endif

            {{-- KARTU PROFIL --}}
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

                        {{-- Tombol Edit — hanya admin --}}
                        @if (auth()->user()->isAdmin())
                            <div class="flex gap-2 pb-1">
                                <a href="{{ route('users.edit', $user) }}"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                                    ✏️ Edit
                                </a>
                            </div>
                        @endif
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

            {{-- STATISTIK RINGKAS --}}
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
                                    <div class="font-medium text-gray-800 truncate">
                                        {{ $asset->brand }} {{ $asset->model }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        SN: {{ $asset->serial_number }}
                                    </div>

                                    {{-- 🆕 Hostname --}}
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
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-blue-600 text-white font-semibold">
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

                                    {{-- 🆕 Hostname snapshot (dari assignment) --}}
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
                                <div
                                    class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
