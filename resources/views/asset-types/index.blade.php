<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Brand & Model - SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="typeManager()">

    <x-header title="Brand & Model" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Brand & Model</h1>
                    <p class="text-sm text-gray-500">Master data brand dan type aset IT</p>
                </div>
                <a href="{{ route('siam.asset-types.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium
                           inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Brand & Model
                </a>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.asset-types.index') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cari (Brand / Model)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Contoh: Lenovo, T14" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Brand</label>
                        <select name="brand" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Brand</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.asset-types.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left w-16">#</th>
                                <th class="px-4 py-3 text-left">Brand</th>
                                <th class="px-4 py-3 text-left">Model / Type</th>
                                <th class="px-4 py-3 text-left">Deskripsi</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assetTypes as $i => $t)
                                @php
                                    $typeData = [
                                        'id' => $t->id,
                                        'brand' => $t->brand,
                                        'model' => $t->model,
                                        'description' => $t->description,
                                        'is_active' => $t->is_active,
                                        'routes' => [
                                            'edit' => route('siam.asset-types.edit', $t),
                                            'delete' => route('siam.asset-types.destroy', $t),
                                        ],
                                    ];
                                @endphp
                                <tr class="hover:bg-indigo-50 cursor-pointer transition-colors"
                                    @click='openModal({{ json_encode($typeData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'>
                                    <td class="px-4 py-3 text-gray-500 font-medium">
                                        {{ $assetTypes->firstItem() + $i }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-indigo-50 text-indigo-700 font-semibold">
                                            {{ $t->brand }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $t->model }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ \Illuminate\Support\Str::limit($t->description, 60) ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($t->is_active)
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('siam.asset-types.edit', $t) }}"
                                            class="text-xs text-violet-600 hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-16 text-center text-gray-400">
                                        Belum ada brand & model.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($assetTypes->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $assetTypes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL AKSI --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="closeModal()">

            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                            x-text="selected?.brand"></span>
                        <h3 class="text-lg font-bold truncate mt-1" x-text="selected?.model"></h3>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
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
                        <div class="text-xs text-gray-500">Brand</div>
                        <div class="text-xs font-medium" x-text="selected?.brand"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Model</div>
                        <div class="text-xs font-medium" x-text="selected?.model"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Deskripsi</div>
                        <div class="text-xs" x-text="selected?.description ?? '-'"></div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>
                <div class="space-y-2">
                    <a :href="selected?.routes.edit"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-violet-300 hover:bg-violet-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-gray-800">Edit</div>
                            <div class="text-xs text-gray-500">Ubah brand / model</div>
                        </div>
                    </a>

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
                        <div>
                            <div class="font-semibold text-sm text-red-700">Hapus</div>
                            <div class="text-xs text-red-500">Tindakan tidak bisa dibatalkan</div>
                        </div>
                    </button>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="closeModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS --}}
    <div x-show="showDeleteModal"
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
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Brand & Model?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus <strong x-text="selected?.brand"></strong> — <strong
                    x-text="selected?.model"></strong>?
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
        function typeManager() {
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

        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
