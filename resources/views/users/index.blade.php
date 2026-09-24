<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="userManager()">

    <x-header title="Manajemen User" placeholder="Cari user..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- TITLE + ACTION -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola akun admin, support, dan user
                </p>
            </div>
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

        <!-- STATS BAR -->
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

        <!-- SEARCH -->
        <div class="mb-4 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" id="searchInput" placeholder="Cari nama atau email..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       bg-white shadow-sm transition">
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="usersTable" class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                #</th>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
                            <th
                                class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-44">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $index => $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-500 font-medium">
                                    {{ $users->firstItem() + $index }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                                    flex items-center justify-center
                                                    text-white font-bold text-sm shrink-0">
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
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $user->email }}
                                            </p>
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
                                        class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-lg
                                                 border {{ $roleColor }}
                                                 text-xs font-bold uppercase">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($user->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5
                                                     px-2.5 py-1 rounded-lg
                                                     bg-emerald-50 text-emerald-700
                                                     border border-emerald-100
                                                     text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5
                                                     px-2.5 py-1 rounded-lg
                                                     bg-gray-100 text-gray-500
                                                     border border-gray-200
                                                     text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $user->created_at->format('d M Y') }}</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $user->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('users.show', $user) }}"
                                            class="inline-flex items-center gap-1.5
                                                   px-3 py-1.5 rounded-lg
                                                   bg-blue-50 text-blue-700
                                                   border border-blue-200
                                                   hover:bg-blue-100
                                                   text-xs font-semibold
                                                   transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}"
                                            class="inline-flex items-center gap-1.5
                                                   px-3 py-1.5 rounded-lg
                                                   bg-amber-50 text-amber-700
                                                   border border-amber-200
                                                   hover:bg-amber-100
                                                   text-xs font-semibold
                                                   transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                @click="openDeleteModal(
                                                    {{ $user->id }},
                                                    '{{ addslashes($user->name) }}',
                                                    '{{ route('users.destroy', $user) }}'
                                                )"
                                                class="inline-flex items-center gap-1.5
                                                       px-3 py-1.5 rounded-lg
                                                       bg-red-50 text-red-700
                                                       border border-red-200
                                                       hover:bg-red-100
                                                       text-xs font-semibold
                                                       transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">Belum ada user</p>
                                            <p class="text-sm text-gray-500 mt-1">Tambahkan user pertama</p>
                                        </div>
                                        <a href="{{ route('users.create') }}"
                                            class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                            + Tambah user
                                        </a>
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

    </div>

    <!-- 🗑️ MODAL KONFIRMASI HAPUS -->
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
                <button type="button" @click="confirmDelete()"
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

    <!-- 🔔 TOAST -->
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
        function userManager() {
            return {
                showDeleteModal: false,
                deleteTarget: {
                    id: null,
                    name: '',
                    action: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openDeleteModal(id, name, action) {
                    this.deleteTarget = {
                        id,
                        name,
                        action
                    };
                    this.showDeleteModal = true;
                },

                confirmDelete() {
                    const form = document.getElementById('deleteForm');
                    form.action = this.deleteTarget.action;
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

                    const searchInput = document.getElementById('searchInput');
                    const table = document.getElementById('usersTable');
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                    searchInput.addEventListener('keyup', function() {
                        const filter = searchInput.value.toLowerCase();
                        let visibleCount = 0;

                        Array.from(rows).forEach(row => {
                            const cells = row.getElementsByTagName('td');
                            let match = false;
                            for (let i = 1; i < cells.length - 2; i++) {
                                if (cells[i].textContent.toLowerCase().includes(filter)) {
                                    match = true;
                                    break;
                                }
                            }
                            row.style.display = match ? '' : 'none';
                            if (match) visibleCount++;
                        });

                        if (filter.length > 0 && visibleCount === 0) {
                            clearTimeout(window._searchToastTimer);
                            window._searchToastTimer = setTimeout(() => {
                                this.showToast(`Tidak ada hasil untuk "${filter}"`, 'info');
                            }, 500);
                        }
                    }.bind(this));
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
