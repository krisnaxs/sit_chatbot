<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Activity Log - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail Activity Log" placeholder="Cari log..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-3xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('activity.index') }}" class="hover:text-blue-600 transition">Activity Log</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Log #{{ $activity->id }}</span>
        </nav>

        <!-- HEADER CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">

            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Detail Log</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ID: <span class="font-mono font-semibold">#{{ $activity->id }}</span>
                        </p>
                    </div>
                </div>

                {{-- Badge Jenis Log --}}
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-lg
                             text-xs font-bold uppercase tracking-wider
                             @switch($activity->log_name)
                                 @case('auth') bg-blue-100 text-blue-700 @break
                                 @case('app') bg-emerald-100 text-emerald-700 @break
                                 @case('app_click') bg-violet-100 text-violet-700 @break
                                 @case('knowledge') bg-amber-100 text-amber-700 @break
                                 @default bg-gray-100 text-gray-700
                             @endswitch">
                    {{ $activity->log_name ?? 'general' }}
                </span>
            </div>

            <!-- DESKRIPSI -->
            <div
                class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-violet-50
                        border border-blue-100 mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                    Deskripsi Aktivitas
                </p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $activity->description }}
                </p>
            </div>

            <!-- INFO GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Waktu --}}
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Waktu</p>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $activity->created_at->format('d M Y, H:i:s') }}
                        </p>
                        <p class="text-[11px] text-gray-400">
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                {{-- User --}}
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">User</p>
                        @if ($activity->causer)
                            <p class="text-sm font-bold text-gray-900 truncate">
                                {{ $activity->causer->name ?? 'User' }}
                            </p>
                            <p class="text-[11px] text-gray-400 truncate">
                                {{ $activity->causer->email ?? '' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500 italic">Guest / System</p>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        <!-- PROPERTIES -->
        @php
            $props = $activity->properties ?? collect();
        @endphp

        @if ($props->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Properties</h2>
                        <p class="text-xs text-gray-500">Data tambahan terkait log ini</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($props as $key => $value)
                        <div
                            class="flex gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100
                                    hover:bg-gray-100 transition">
                            <span class="text-xs font-bold text-gray-500 shrink-0 min-w-[110px] font-mono pt-0.5">
                                {{ $key }}
                            </span>
                            <span class="text-xs text-gray-800 font-mono flex-1 break-all leading-relaxed">
                                @if (is_array($value) || is_object($value))
                                    <pre class="whitespace-pre-wrap">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- SUBJECT -->
        @if ($activity->subject)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Subject</h2>
                        <p class="text-xs text-gray-500">Objek yang dikenai aksi</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span
                            class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider
                                     bg-emerald-100 text-emerald-700 border border-emerald-200">
                            {{ class_basename($activity->subject_type) }}
                        </span>
                        <span class="font-mono text-xs text-gray-500">
                            #{{ $activity->subject_id }}
                        </span>
                    </div>

                    @if (method_exists($activity->subject, 'toArray'))
                        <details class="group">
                            <summary
                                class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 select-none inline-flex items-center gap-1 font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3 transition group-open:rotate-90" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                Lihat detail model
                            </summary>
                            <div
                                class="mt-2 p-3 bg-white rounded-lg border border-gray-200
                                        text-[11px] text-gray-700 font-mono overflow-x-auto">
                                <pre class="whitespace-pre-wrap break-all">{{ json_encode($activity->subject->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </details>
                    @endif
                </div>
            </div>
        @endif

        <!-- NAVIGASI PREV / NEXT -->
        <div class="flex items-center justify-between gap-3 mb-4">
            @if ($prev)
                <a href="{{ route('activity.show', $prev) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-white border border-gray-200 hover:bg-gray-50
                           hover:border-gray-300
                           text-gray-700 font-semibold text-sm transition
                           shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Sebelumnya
                    <span class="text-[10px] text-gray-400 font-mono">#{{ $prev->id }}</span>
                </a>
            @else
                <div></div>
            @endif

            @if ($next)
                <a href="{{ route('activity.show', $next) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-white border border-gray-200 hover:bg-gray-50
                           hover:border-gray-300
                           text-gray-700 font-semibold text-sm transition
                           shadow-sm">
                    <span class="text-[10px] text-gray-400 font-mono">#{{ $next->id }}</span>
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">

            <a href="{{ route('activity.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                       border border-gray-300 text-gray-700 font-semibold text-sm
                       hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar
            </a>

            @if (auth()->user()->isAdmin())
                <button type="button"
                    onclick="if(confirm('Yakin ingin menghapus log ini?')) document.getElementById('deleteShowForm').submit();"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-red-600 hover:bg-red-700 text-white font-semibold text-sm
                           shadow-lg shadow-red-500/30 transition
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Log Ini
                </button>

                <form id="deleteShowForm" method="POST" action="{{ route('activity.destroy', $activity) }}"
                    class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

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
