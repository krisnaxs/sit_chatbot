<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="activityManager()">

    <x-header title="Activity Log" placeholder="Cari log..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-6xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- TITLE + ACTION -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Riwayat aktivitas admin &amp; pengguna sistem
                    @if (!auth()->user()->isAdmin())
                        <span
                            class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                     bg-gray-100 text-gray-600 border border-gray-200">
                            View Only
                        </span>
                    @endif
                </p>
            </div>

            {{-- Tombol Bersihkan — HANYA ADMIN --}}
            @if (auth()->user()->isAdmin())
                <button type="button" @click="showClearModal = true"
                    class="inline-flex items-center gap-2
                           bg-red-600 hover:bg-red-700
                           text-white px-4 py-2.5 rounded-xl
                           font-semibold text-sm
                           shadow-lg shadow-red-500/30
                           transition-all duration-300
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bersihkan Semua
                </button>
            @endif
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Log</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalAll) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari Ini</p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($totalToday) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Minggu Ini</p>
                        <p class="text-3xl font-bold text-violet-600 mt-1">{{ number_format($totalWeek) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cari Deskripsi</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="cth: login"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Log</label>
                    <select name="log_name"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Semua Jenis</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                                {{ strtoupper($name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                           bg-blue-600 text-white text-sm font-semibold
                           hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('activity.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                           border border-gray-300 text-gray-700 text-sm font-semibold
                           hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-14">
                                #</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Jenis</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Deskripsi</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">
                                User</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">
                                Waktu</th>

                            @if (auth()->user()->isAdmin())
                                <th
                                    class="px-4 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                    Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($activities as $index => $act)
                            <tr onclick="window.location='{{ route('activity.show', $act) }}'"
                                class="hover:bg-gray-50 transition-colors cursor-pointer">

                                <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                                    {{ $activities->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg
                                                 text-[10px] font-bold uppercase tracking-wider
                                                 @switch($act->log_name)
                                                     @case('auth') bg-blue-100 text-blue-700 @break
                                                     @case('app') bg-emerald-100 text-emerald-700 @break
                                                     @case('app_click') bg-violet-100 text-violet-700 @break
                                                     @case('knowledge') bg-amber-100 text-amber-700 @break
                                                     @case('siam') bg-cyan-100 text-cyan-700 @break
                                                     @case('user') bg-indigo-100 text-indigo-700 @break
                                                     @case('activity') bg-rose-100 text-rose-700 @break
                                                     @default bg-gray-100 text-gray-700
                                                 @endswitch">
                                        {{ $act->log_name ?? 'general' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-800 font-medium">
                                        {{ $act->description }}
                                    </div>

                                    {{-- 🆕 Badge "X field diubah" untuk event updated --}}
                                    @php
                                        $props = $act->properties ?? collect();
                                        $old = $props['old'] ?? null;
                                        $new = $props['new'] ?? null;
                                        $changedCount = 0;
                                        if ($act->event === 'updated' && $old && $new) {
                                            foreach ($new as $k => $v) {
                                                if (($old[$k] ?? null) != $v && $k !== 'updated_at') {
                                                    $changedCount++;
                                                }
                                            }
                                        }
                                    @endphp

                                    @if ($changedCount > 0)
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                                                         bg-amber-100 text-amber-700 border border-amber-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                {{ $changedCount }} field diubah
                                            </span>
                                        </div>
                                    @endif

                                    @if ($props->count() > 0)
                                        <details class="mt-1.5 group" onclick="event.stopPropagation()">
                                            <summary
                                                class="text-[11px] text-gray-400 cursor-pointer hover:text-gray-600 select-none inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3 transition group-open:rotate-90" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                                Detail
                                            </summary>
                                            <div
                                                class="mt-2 p-2.5 bg-gray-50 rounded-lg text-[11px] text-gray-600 font-mono whitespace-pre-wrap break-all leading-relaxed">
                                                @foreach ($props as $key => $value)
                                                    <div class="flex gap-2">
                                                        <span
                                                            class="text-gray-400 shrink-0">{{ $key }}:</span>
                                                        <span
                                                            class="text-gray-700">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($act->causer)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                                        flex items-center justify-center
                                                        text-white text-[11px] font-bold shrink-0">
                                                {{ strtoupper(substr($act->causer->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-800 font-medium truncate">
                                                    {{ $act->causer->name ?? 'User' }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 truncate">
                                                    {{ $act->causer->email ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400 italic">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Guest / System
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $act->created_at->format('d M Y') }}</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $act->created_at->format('H:i') }}
                                            · {{ $act->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                @if (auth()->user()->isAdmin())
                                    <td class="px-4 py-4 text-right" onclick="event.stopPropagation()">
                                        <button type="button"
                                            @click.stop="openDeleteModal(
                                                {{ $act->id }},
                                                '{{ addslashes($act->description) }}',
                                                '{{ route('activity.destroy', $act) }}'
                                            )"
                                            class="p-1.5 rounded-lg hover:bg-red-50 transition" title="Hapus log">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">Belum ada aktivitas</p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Log akan muncul setelah ada aktivitas
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- MODAL & FORM — hanya di-render untuk admin --}}
    @if (auth()->user()->isAdmin())
        <!-- MODAL KONFIRMASI HAPUS LOG -->
        <div x-show="showDeleteModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative transform transition-all duration-300"
                @click.away="showDeleteModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Log Ini?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Yakin ingin menghapus log
                    <strong class="text-gray-800" x-text="deleteTarget.description"></strong>?
                </p>

                <div class="flex gap-2">
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmDelete()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL KONFIRMASI BERSIHKAN SEMUA -->
        <div x-show="showClearModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative transform transition-all duration-300"
                @click.away="showClearModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Bersihkan Semua Log?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Semua <strong class="text-gray-800">{{ number_format($totalAll) }}</strong> log akan dihapus
                    permanen.
                    Tindakan ini tidak bisa dibatalkan!
                </p>

                <div class="flex gap-2">
                    <button type="button" @click="showClearModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmClear()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Bersihkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden forms -->
        <form id="deleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form id="clearForm" method="POST" action="{{ route('activity.clear') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <!-- TOAST -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        :class="{
            'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
            'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
            'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
        }"
        class="fixed top-24 right-6 z-[100] flex items-center gap-3
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

    <script>
        function activityManager() {
            return {
                showDeleteModal: false,
                showClearModal: false,
                deleteTarget: {
                    id: null,
                    description: '',
                    action: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openDeleteModal(id, description, action) {
                    this.deleteTarget = {
                        id,
                        description,
                        action
                    };
                    this.showDeleteModal = true;
                },

                confirmDelete() {
                    const form = document.getElementById('deleteForm');
                    if (!form) return;
                    form.action = this.deleteTarget.action;
                    form.submit();
                },

                confirmClear() {
                    const form = document.getElementById('clearForm');
                    if (form) form.submit();
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
    </script>

</body>

</html>
