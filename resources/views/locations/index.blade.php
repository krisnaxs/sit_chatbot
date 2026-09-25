<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Lokasi - SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="locationManager()">

    <x-header title="Lokasi" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Lokasi</h1>
                    <p class="text-sm text-gray-500">Kelola gedung, ruang, dan divisi</p>
                </div>
                <a href="{{ route('siam.locations.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium
                           inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Lokasi
                </a>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left w-16">#</th>
                                <th class="px-4 py-3 text-left">Gedung</th>
                                <th class="px-4 py-3 text-left">Lantai</th>
                                <th class="px-4 py-3 text-left">Ruang</th>
                                <th class="px-4 py-3 text-left">Divisi</th>
                                <th class="px-4 py-3 text-right">Jumlah User</th>
                                <th class="px-4 py-3 text-right">Aset</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($locations as $i => $l)
                                @php
                                    $locData = [
                                        'id' => $l->id,
                                        'building' => $l->building,
                                        'floor' => $l->floor,
                                        'room' => $l->room,
                                        'division' => $l->division,
                                        'full_name' => $l->full_name,
                                        'notes' => $l->notes,
                                        'is_active' => $l->is_active,
                                        'users_count' => $l->users_count,
                                        'assets_count' => $l->current_assets_count,
                                        'routes' => [
                                            'edit' => route('siam.locations.edit', $l),
                                            'delete' => route('siam.locations.destroy', $l),
                                        ],
                                    ];
                                @endphp
                                <tr @click='openModal({{ json_encode($locData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-medium">
                                        {{ $locations->firstItem() + $i }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $l->building ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $l->floor ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $l->floor ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $l->room ?? '-' }}</div>
                                        @if ($l->full_name)
                                            <div class="text-xs text-gray-500">{{ $l->full_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if ($l->division)
                                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold">
                                                {{ $l->division }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-cyan-50 text-cyan-700 font-semibold">
                                            {{ $l->users_count }} user
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-violet-50 text-violet-700 font-semibold">
                                            {{ $l->current_assets_count }} aset
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($l->is_active)
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Belum ada lokasi</p>
                                                <p class="text-sm text-gray-500 mt-1">Tambahkan lokasi pertama</p>
                                            </div>
                                            <a href="{{ route('siam.locations.create') }}"
                                                class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                + Tambah lokasi
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($locations->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $locations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL POPUP AKSI LOKASI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div
                            class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold truncate"
                                x-text="selected?.full_name || (selected?.building + ' - ' + selected?.room)"></h3>
                            <p class="text-sm text-white/80" x-text="'Divisi: ' + (selected?.division || '-')"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- INFO --}}
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Gedung</div>
                        <div class="text-xs font-medium" x-text="selected?.building ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Lantai</div>
                        <div class="text-xs font-medium" x-text="selected?.floor ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Ruang</div>
                        <div class="text-xs font-medium" x-text="selected?.room ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Divisi</div>
                        <div class="text-xs font-medium" x-text="selected?.division ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">User</div>
                        <div class="text-xs font-medium" x-text="selected?.users_count + ' orang'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Aset</div>
                        <div class="text-xs font-medium" x-text="selected?.assets_count + ' unit'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Status</div>
                        <div class="text-xs font-medium">
                            <span x-show="selected?.is_active" class="text-emerald-600">Aktif</span>
                            <span x-show="!selected?.is_active" class="text-gray-500">Nonaktif</span>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Catatan</div>
                        <div class="text-xs" x-text="selected?.notes ?? '-'"></div>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>

                <div class="grid grid-cols-2 gap-3">

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

                    {{-- HAPUS (admin only) --}}
                    @if (auth()->user()->isAdmin())
                        <button type="button" @click="confirmDelete()"
                            class="flex items-center gap-3 p-3 rounded-xl border border-red-200
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
                                <div class="text-xs text-red-500">Khusus admin</div>
                            </div>
                        </button>
                    @endif

                </div>
            </div>

            {{-- FOOTER --}}
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
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Lokasi?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus
                <strong class="text-gray-800"
                    x-text="selected?.full_name || (selected?.building + ' - ' + selected?.room)"></strong>?
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
        function locationManager() {
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
                    // Tutup modal detail dulu, baru buka modal konfirmasi hapus
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
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
