<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="knowledgeManager()">

    <!-- HEADER -->
    <x-header title="Knowledge Base" placeholder="Cari knowledge..." />

    <!-- SIDEBAR -->
    <x-sidebar />

    <!-- CONTENT -->
    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-6xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- TITLE + ACTION -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Knowledge Base</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Daftar pertanyaan & jawaban yang dipakai chatbot
                    @if (auth()->user()->hasRole('user'))
                        <span
                            class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                     bg-gray-100 text-gray-600 border border-gray-200">
                            View Only
                        </span>
                    @endif
                </p>
            </div>

            {{-- 🆕 Tombol Tambah — admin & support --}}
            @if (auth()->user()->hasAnyRole(['admin', 'support']))
                <a href="{{ route('knowledge.create') }}"
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
                    Tambah Knowledge
                </a>
            @endif
        </div>

        <!-- STATS BAR -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Knowledge</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $items->total() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Halaman Ini</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $items->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Halaman</p>
                <p class="text-3xl font-bold text-violet-600 mt-1">{{ $items->lastPage() }}</p>
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
            <input type="text" id="searchInput" placeholder="Cari kata kunci atau jawaban..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       bg-white shadow-sm transition">
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="knowledgeTable" class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                #</th>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-56">
                                Kata Kunci</th>
                            <th
                                class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Jawaban</th>
                            <th
                                class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">
                                Lampiran</th>
                            <th
                                class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-44">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $index => $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-500 font-medium">
                                    {{ $items->firstItem() + $index }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-lg
                                                 bg-blue-50 text-blue-700
                                                 border border-blue-100
                                                 text-xs font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ $item->kata_kunci }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit($item->jawaban, 120) }}
                                </td>

                                {{-- LAMPIRAN --}}
                                <td class="px-5 py-4 text-center">
                                    @if ($item->file_path)
                                        @php
                                            $fileCategory = match (true) {
                                                str_starts_with($item->file_type ?? '', 'image/') => 'image',
                                                $item->file_type === 'application/pdf' => 'pdf',
                                                str_contains($item->file_type ?? '', 'word') => 'word',
                                                str_contains($item->file_type ?? '', 'excel') ||
                                                    str_contains($item->file_type ?? '', 'spreadsheet')
                                                    => 'excel',
                                                str_contains($item->file_type ?? '', 'powerpoint') ||
                                                    str_contains($item->file_type ?? '', 'presentation')
                                                    => 'powerpoint',
                                                str_contains($item->file_type ?? '', 'zip') ||
                                                    str_contains($item->file_type ?? '', 'rar')
                                                    => 'archive',
                                                default => 'other',
                                            };
                                            $fileIcon = match ($fileCategory) {
                                                'image' => '🖼️',
                                                'pdf' => '📄',
                                                'word' => '📝',
                                                'excel' => '📊',
                                                'powerpoint' => '📽️',
                                                'archive' => '📦',
                                                default => '📎',
                                            };
                                        @endphp
                                        <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank"
                                            class="inline-flex items-center gap-2
                                                   px-2.5 py-1.5 rounded-lg
                                                   bg-gray-50 hover:bg-gray-100
                                                   border border-gray-200
                                                   text-xs font-semibold text-gray-700
                                                   transition max-w-full"
                                            title="{{ $item->file_name }}">
                                            <span class="shrink-0">{{ $fileIcon }}</span>
                                            <span class="truncate max-w-[120px]">{{ $item->file_name }}</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>

                                {{-- 🆕 AKSI — sesuai role --}}
                                <td class="px-5 py-4 text-right">
                                    @if (auth()->user()->hasAnyRole(['admin', 'support']))
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Edit — admin & support --}}
                                            <a href="{{ route('knowledge.edit', $item) }}"
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

                                            {{-- Hapus — admin saja --}}
                                            @if (auth()->user()->isAdmin())
                                                <button type="button"
                                                    @click="openDeleteModal(
                                                        {{ $item->id }},
                                                        '{{ addslashes($item->kata_kunci) }}',
                                                        '{{ route('knowledge.destroy', $item) }}'
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
                                    @else
                                        <span class="text-xs text-gray-400 italic">View only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">Belum ada knowledge</p>
                                            <p class="text-sm text-gray-500 mt-1">Tambahkan knowledge untuk chatbot</p>
                                        </div>
                                        @if (auth()->user()->hasAnyRole(['admin', 'support']))
                                            <a href="{{ route('knowledge.create') }}"
                                                class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                                + Tambah sekarang
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if ($items->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- 🆕 MODAL & FORM HAPUS — hanya di-render untuk admin --}}
    @if (auth()->user()->isAdmin())
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

                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">
                    Hapus Knowledge?
                </h3>

                <p class="text-sm text-gray-500 text-center mb-6">
                    Yakin ingin menghapus
                    <strong class="text-gray-800" x-text="deleteTarget.kata_kunci"></strong>?
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

        <!-- Hidden delete form -->
        <form id="deleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

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
               px-4 py-3 rounded-xl
               text-white shadow-2xl border backdrop-blur-md"
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
        function knowledgeManager() {
            return {
                showDeleteModal: false,
                deleteTarget: {
                    id: null,
                    kata_kunci: '',
                    action: '',
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success',
                },

                openDeleteModal(id, kata_kunci, action) {
                    this.deleteTarget = {
                        id,
                        kata_kunci,
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

                    // SEARCH
                    const searchInput = document.getElementById('searchInput');
                    const table = document.getElementById('knowledgeTable');
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                    searchInput.addEventListener('keyup', function() {
                        const filter = searchInput.value.toLowerCase();
                        let visibleCount = 0;

                        Array.from(rows).forEach(row => {
                            const cells = row.getElementsByTagName('td');
                            if (cells.length < 2) return;
                            let match = false;
                            for (let i = 1; i <= 2; i++) {
                                if (cells[i] && cells[i].textContent.toLowerCase().includes(filter)) {
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
