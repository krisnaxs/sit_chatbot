<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail User" placeholder="Cari user..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-3xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600 transition">Manajemen User</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Detail</span>
        </nav>

        <!-- PROFILE CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <!-- HERO BANNER -->
            <div class="h-32 bg-gradient-to-br from-blue-500 to-violet-600"></div>

            <!-- PROFILE -->
            <div class="px-6 sm:px-8 pb-8">
                <!-- Avatar -->
                <div class="flex items-end justify-between -mt-16 mb-4">
                    <div
                        class="w-28 h-28 rounded-2xl bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center
                                text-white font-black text-4xl
                                ring-4 ring-white shadow-xl shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('users.edit', $user) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                                   bg-gradient-to-br from-blue-500 to-violet-600
                                   hover:from-blue-600 hover:to-violet-700
                                   text-white font-semibold text-sm
                                   shadow-lg shadow-blue-500/30
                                   transition-all duration-300
                                   hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </a>
                    </div>
                </div>

                <!-- Name & Email -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        @php
                            $roleColors = [
                                'admin' => 'bg-violet-50 text-violet-700 border-violet-200',
                                'support' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'user' => 'bg-gray-50 text-gray-700 border-gray-200',
                            ];
                        @endphp
                        <span
                            class="px-2.5 py-1 rounded-lg border {{ $roleColors[$user->role] ?? '' }}
                                     text-xs font-bold uppercase">
                            {{ $user->role }}
                        </span>
                        @if ($user->is_active)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                         bg-emerald-50 text-emerald-700 border border-emerald-200
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
                    </div>
                    <p class="text-gray-500">{{ $user->email }}</p>
                </div>

                <!-- INFO GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-gray-100">

                    <!-- ID -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">User ID</p>
                            <p class="text-sm font-bold text-gray-900">#{{ $user->id }}</p>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Role</p>
                            <p class="text-sm font-bold text-gray-900">{{ ucfirst($user->role) }}</p>
                        </div>
                    </div>

                    <!-- Dibuat -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Dibuat</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ $user->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Login terakhir -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Terakhir Update</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ $user->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- BACK BUTTON -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                               border border-gray-300 text-gray-700 font-semibold text-sm
                               hover:bg-gray-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <!-- DANGER ZONE (opsional) -->
        @if ($user->id !== auth()->id())
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-red-100 p-6">
                <h3 class="font-bold text-red-700 mb-2">Zona Berbahaya</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Menghapus user akan menghilangkan semua data terkait. Tindakan ini tidak bisa dibatalkan.
                </p>
                <button type="button"
                    onclick="if(confirm('Yakin ingin menghapus user ini?')) { document.getElementById('showDeleteForm').submit(); }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-red-600 hover:bg-red-700 text-white font-semibold text-sm
                           shadow-lg shadow-red-500/30 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus User Ini
                </button>

                <form id="showDeleteForm" method="POST" action="{{ route('users.destroy', $user) }}"
                    class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        @endif

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
