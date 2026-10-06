@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="userManager()">

        {{-- TITLE + ACTION --}}
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola akun admin, support, dan user</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('users.import.form') }}"
                    class="inline-flex items-center gap-2
                           bg-emerald-600 hover:bg-emerald-700
                           text-white px-4 py-2.5 rounded-xl
                           font-semibold text-sm
                           shadow-lg shadow-emerald-500/30
                           transition-all duration-300
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Import Excel
                </a>

                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center gap-2
                           bg-gradient-to-br from-blue-500 to-violet-600
                           hover:from-blue-600 hover:to-violet-700
                           text-white px-5 py-2.5 rounded-xl
                           font-semibold text-sm
                           shadow-lg shadow-blue-500/30
                           transition-all duration-300
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah User
                </a>
            </div>
        </div>

        {{-- ALERT DETAIL GAGAL IMPORT --}}
        @if (session('import_failures') && count(session('import_failures')) > 0)
            <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-red-800">
                            {{ count(session('import_failures')) }} Baris Gagal Diimport
                        </h3>
                        <p class="text-xs text-red-600 mt-0.5">
                            Periksa detail di bawah, perbaiki file Excel, lalu coba import lagi.
                        </p>
                    </div>
                </div>

                <div class="max-h-60 overflow-y-auto space-y-1.5 bg-white rounded-lg p-2 border border-red-100">
                    @foreach (session('import_failures') as $failure)
                        <div class="text-xs text-red-700 p-2 rounded bg-red-50/50 border border-red-100">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-bold text-red-800">
                                    Baris #{{ $failure->row() }}
                                </span>
                                @if (isset($failure->values()['email']))
                                    <span class="text-red-500 font-mono text-[10px]">
                                        {{ $failure->values()['email'] }}
                                    </span>
                                @endif
                            </div>
                            <ul class="list-disc list-inside ml-2 text-[11px]">
                                @foreach ($failure->errors() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- STATS BAR --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $users->total() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</p>
                <p class="text-3xl font-bold text-violet-600 mt-1">
                    {{ \App\Models\User::where('role', 'admin')->count() }}
                </p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Support</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">
                    {{ \App\Models\User::where('role', 'support')->count() }}
                </p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktif</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">
                    {{ \App\Models\User::where('is_active', true)->count() }}
                </p>
            </div>
        </div>

        {{-- SEARCH (SERVER-SIDE) --}}
        <form method="GET" action="{{ route('users.index') }}" class="mb-4" id="searchForm">
            <div class="relative flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        placeholder="Cari nama, email, NIP, atau username... (tekan Enter)" autocomplete="off"
                        class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               bg-white shadow-sm transition">

                    {{-- Tombol clear kalau ada keyword --}}
                    @if (request('search'))
                        <a href="{{ route('users.index') }}"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
                <button type="submit"
                    class="px-5 py-3 bg-gradient-to-br from-blue-500 to-violet-600
                           hover:from-blue-600 hover:to-violet-700
                           text-white font-semibold text-sm rounded-xl
                           shadow-lg shadow-blue-500/30 transition-all
                           hover:scale-105 active:scale-95">
                    Cari
                </button>
            </div>

            {{-- Info hasil pencarian --}}
            @if (request('search'))
                <p class="text-xs text-gray-500 mt-2">
                    Menampilkan hasil untuk:
                    <strong class="text-gray-700">"{{ request('search') }}"</strong>
                    — {{ $users->total() }} hasil ditemukan
                </p>
            @endif
        </form>

        {{-- TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="usersTable" class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                #</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                User</th>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Role</th>
                            <th
                                class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                                Status</th>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-44">
                                Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $index => $user)
                            @php
                                $userData = [
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'username' => $user->username,
                                    'nip' => $user->nip,
                                    'role' => $user->role,
                                    'role_label' => $user->role_label,
                                    'position' => $user->position,
                                    'department' => $user->department?->name,
                                    'location' => $user->location?->full_name,
                                    'is_active' => $user->is_active,
                                    'is_self' => $user->id === auth()->id(),
                                    'created_at' => $user->created_at?->format('d M Y'),
                                    'initial' => strtoupper(substr($user->name, 0, 1)),
                                    'routes' => [
                                        'show' => route('users.show', $user),
                                        'edit' => route('users.edit', $user),
                                        'delete' => route('users.destroy', $user),
                                        'reset_password' => route('users.reset-password', $user),
                                    ],
                                ];
                            @endphp
                            <tr @click='openUserModal({{ json_encode($userData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-500 font-medium">
                                    {{ $users->firstItem() + $index }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                                    flex items-center justify-center text-white font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $user->name }}
                                                @if ($user->id === auth()->id())
                                                    <span
                                                        class="ml-1 text-[10px] px-1.5 py-0.5 rounded
                                                                 bg-amber-100 text-amber-700 border border-amber-200 font-bold">
                                                        ANDA
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-violet-50 text-violet-700 border-violet-100',
                                            'support' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'user' => 'bg-gray-50 text-gray-700 border-gray-100',
                                        ];
                                        $roleColor =
                                            $roleColors[$user->role] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                                 border {{ $roleColor }} text-xs font-bold uppercase">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($user->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                                     bg-emerald-50 text-emerald-700 border border-emerald-100
                                                     text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                                     bg-gray-100 text-gray-500 border border-gray-200
                                                     text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $user->created_at->format('d M Y') }}</span>
                                        <span
                                            class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            @if (request('search'))
                                                <p class="font-semibold text-gray-900">Tidak ada hasil</p>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    Tidak ditemukan user dengan keyword
                                                    "<strong>{{ request('search') }}</strong>"
                                                </p>
                                                <a href="{{ route('users.index') }}"
                                                    class="inline-block mt-3 text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                                    ← Kembali ke semua user
                                                </a>
                                            @else
                                                <p class="font-semibold text-gray-900">Belum ada user</p>
                                                <p class="text-sm text-gray-500 mt-1">Tambahkan user pertama atau import
                                                    dari Excel</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL POPUP AKSI USER --}}
        {{-- ============================================================ --}}
        <div x-show="showUserModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeUserModal()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" @click.away="closeUserModal()">

                <div class="bg-gradient-to-br from-blue-500 to-violet-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div
                                class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl font-bold shrink-0">
                                <span x-text="selectedUser?.initial"></span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold truncate" x-text="selectedUser?.name"></h3>
                                <p class="text-sm text-white/80 truncate" x-text="selectedUser?.email"></p>
                            </div>
                        </div>
                        <button @click="closeUserModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
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
                            <div class="text-xs text-gray-500">NIP</div>
                            <div class="font-mono text-xs" x-text="selectedUser?.nip ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Username</div>
                            <div class="font-mono text-xs text-indigo-700" x-text="selectedUser?.username"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Jabatan</div>
                            <div class="text-xs" x-text="selectedUser?.position ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Departemen</div>
                            <div class="text-xs" x-text="selectedUser?.department ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Lokasi</div>
                            <div class="text-xs" x-text="selectedUser?.location ?? '-'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Status</div>
                            <div class="text-xs font-medium">
                                <span x-show="selectedUser?.is_active" class="text-emerald-600">Aktif</span>
                                <span x-show="!selectedUser?.is_active" class="text-gray-500">Nonaktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>

                    <div class="space-y-2">
                        {{-- LIHAT DETAIL --}}
                        <a :href="selectedUser?.routes.show"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                  hover:border-blue-300 hover:bg-blue-50 transition group">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">Lihat Detail</div>
                                <div class="text-xs text-gray-500">Aset yang dipegang & history</div>
                            </div>
                        </a>

                        {{-- EDIT --}}
                        <a :href="selectedUser?.routes.edit"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                  hover:border-amber-300 hover:bg-amber-50 transition group">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-800">Edit User</div>
                                <div class="text-xs text-gray-500">Ubah data & role</div>
                            </div>
                        </a>

                        {{-- RESET PASSWORD (admin only, bukan diri sendiri) --}}
                        <template x-if="{{ auth()->user()->isAdmin() ? 'true' : 'false' }} && !selectedUser?.is_self">
                            <button type="button" @click="confirmResetPassword()"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border border-orange-200
                                       bg-orange-50 hover:bg-orange-100 hover:border-orange-300 transition text-left">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-orange-700">Reset Password</div>
                                    <div class="text-xs text-orange-500">Kembalikan ke password default</div>
                                </div>
                            </button>
                        </template>

                        {{-- HAPUS (admin only, bukan diri sendiri) --}}
                        <template x-if="{{ auth()->user()->isAdmin() ? 'true' : 'false' }} && !selectedUser?.is_self">
                            <button type="button" @click="confirmDelete()"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border border-red-200
                                       bg-red-50 hover:bg-red-100 hover:border-red-300 transition text-left">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-red-700">Hapus User</div>
                                    <div class="text-xs text-red-500">Tindakan tidak bisa dibatalkan</div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end">
                    <button @click="closeUserModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL KONFIRMASI HAPUS --}}
        {{-- ============================================================ --}}
        <div x-show="showDeleteModal" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center p-4"
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
                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus User?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Yakin ingin menghapus <strong class="text-gray-800" x-text="deleteTarget.name"></strong>?
                    Tindakan ini tidak bisa dibatalkan.
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmDeleteSubmit()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL KONFIRMASI RESET PASSWORD --}}
        {{-- ============================================================ --}}
        <div x-show="showResetModal" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showResetModal = false">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Reset Password?</h3>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Password <strong class="text-gray-800" x-text="resetTarget.name"></strong> akan direset ke:
                </p>

                <div class="p-3 rounded-xl bg-orange-50 border border-orange-200 text-center mb-4">
                    <code class="font-mono text-base font-bold text-orange-600">password123</code>
                </div>

                <p class="text-xs text-gray-500 text-center mb-6">
                    User harus login ulang dengan password baru setelah direset.
                </p>

                <div class="flex gap-2">
                    <button type="button" @click="showResetModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmResetPasswordSubmit()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700
                               text-white font-semibold transition shadow-lg shadow-orange-500/30">
                        Ya, Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- HIDDEN FORMS --}}
        <form id="deleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form id="resetPasswordForm" method="POST" class="hidden">
            @csrf
            @method('PUT')
        </form>

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
            class="fixed top-24 right-6 z-[120] flex items-center gap-3
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
        function userManager() {
            return {
                showUserModal: false,
                selectedUser: null,
                showDeleteModal: false,
                deleteTarget: {
                    id: null,
                    name: '',
                    action: ''
                },
                showResetModal: false,
                resetTarget: {
                    id: null,
                    name: '',
                    action: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openUserModal(user) {
                    this.selectedUser = user;
                    this.showUserModal = true;
                },

                closeUserModal() {
                    this.showUserModal = false;
                    this.selectedUser = null;
                },

                confirmDelete() {
                    this.deleteTarget = {
                        id: this.selectedUser.id,
                        name: this.selectedUser.name,
                        action: this.selectedUser.routes.delete
                    };
                    this.closeUserModal();
                    this.showDeleteModal = true;
                },

                confirmDeleteSubmit() {
                    const form = document.getElementById('deleteForm');
                    form.action = this.deleteTarget.action;
                    form.submit();
                },

                confirmResetPassword() {
                    this.resetTarget = {
                        id: this.selectedUser.id,
                        name: this.selectedUser.name,
                        action: this.selectedUser.routes.reset_password
                    };
                    this.closeUserModal();
                    this.showResetModal = true;
                },

                confirmResetPasswordSubmit() {
                    const form = document.getElementById('resetPasswordForm');
                    form.action = this.resetTarget.action;
                    form.submit();
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

                    // Auto-submit search dengan debounce (opsional: hapus kalau mau manual enter)
                    const searchInput = document.getElementById('searchInput');
                    const searchForm = document.getElementById('searchForm');
                    if (searchInput && searchForm) {
                        let debounceTimer;
                        searchInput.addEventListener('input', function() {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                searchForm.submit();
                            }, 600);
                        });
                    }

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.showResetModal) {
                                this.showResetModal = false;
                            } else if (this.showDeleteModal) {
                                this.showDeleteModal = false;
                            } else if (this.showUserModal) {
                                this.closeUserModal();
                            }
                        }
                    });
                }
            }
        }
    </script>
@endpush
