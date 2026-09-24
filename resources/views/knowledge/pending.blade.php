<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Knowledge - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="pendingManager()">

    <x-header title="Pending Knowledge" placeholder="Cari pending..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-6xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- TITLE -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pending Knowledge</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Jawaban AI yang menunggu review dari admin
                </p>
            </div>
            @if ($totalApproved > 0 || $totalRejected > 0)
                <button type="button" @click="showClearModal = true"
                    class="inline-flex items-center gap-2
                           bg-gray-600 hover:bg-gray-700
                           text-white px-4 py-2.5 rounded-xl
                           font-semibold text-sm
                           shadow-lg shadow-gray-500/30
                           transition-all duration-300
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bersihkan Lama
                </button>
            @endif
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $totalPending }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari Ini</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $totalToday }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $totalApproved }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $totalRejected }}</p>
            </div>
        </div>

        <!-- SEARCH -->
        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-4">
            <div class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pertanyaan atau jawaban..."
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                    Cari
                </button>
                @if (request('search'))
                    <a href="{{ route('knowledge.pending') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                               border border-gray-300 text-gray-700 text-sm font-semibold
                               hover:bg-gray-50 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- LIST -->
        <div class="space-y-3">
            @forelse ($items as $item)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">
                                    Pertanyaan User
                                </p>
                                <p class="text-sm font-bold text-gray-900 mt-0.5">
                                    {{ $item->pesan_user }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($item->frequency > 1)
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                                             bg-rose-50 text-rose-700 border border-rose-200
                                             text-xs font-bold">
                                    🔥 {{ $item->frequency }}x ditanya
                                </span>
                            @endif
                            <span class="text-xs text-gray-400">
                                {{ $item->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <!-- Jawaban AI -->
                    <div
                        class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-violet-50
                                border border-blue-100 mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            Jawaban AI
                        </p>
                        <p class="text-sm text-gray-800 leading-relaxed">
                            {{ $item->jawaban_ai }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-100">
                        <button type="button"
                            @click="openApproveModal(
                                {{ $item->id }},
                                @js($item->pesan_user),
                                @js($item->jawaban_ai),
                                @js(route('knowledge.pending.approve', $item))
                            )"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-emerald-600 hover:bg-emerald-700
                                   text-white font-semibold text-sm
                                   shadow-lg shadow-emerald-500/30
                                   transition hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui & Simpan ke Knowledge
                        </button>

                        <button type="button"
                            @click="openRejectModal(
                                {{ $item->id }},
                                @js($item->pesan_user),
                                @js(route('knowledge.pending.reject', $item))
                            )"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-red-50 hover:bg-red-100 text-red-700
                                   border border-red-200
                                   font-semibold text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tolak
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Tidak ada pending</p>
                            <p class="text-sm text-gray-500 mt-1">
                                Semua jawaban AI sudah di-review 🎉
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- PAGINATION -->
        @if ($items->hasPages())
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif

    </div>

    <!-- ✅ MODAL APPROVE -->
    <div x-show="showApproveModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="showApproveModal = false" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-[560px] max-w-[calc(100vw-2rem)] relative
                    max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <h3 class="text-lg font-bold text-gray-900 mb-1">Setujui & Simpan</h3>
            <p class="text-sm text-gray-500 mb-6">
                Review & edit jika perlu, lalu simpan ke knowledge base.
            </p>

            <form :action="approveTarget.action" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kata Kunci (pertanyaan user)
                    </label>
                    <input type="text" name="kata_kunci" :value="approveTarget.pesan"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jawaban
                    </label>
                    <textarea name="jawaban" rows="6" :value="approveTarget.jawaban"
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               resize-none transition"
                        required></textarea>
                </div>

                <div class="flex gap-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showApproveModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl
                               bg-emerald-600 hover:bg-emerald-700
                               text-white font-semibold transition
                               shadow-lg shadow-emerald-500/30">
                        Setujui & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ❌ MODAL REJECT -->
    <div x-show="showRejectModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="showRejectModal = false" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative transform transition-all duration-300"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Tolak Pending?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin menolak pertanyaan
                <strong class="text-gray-800" x-text="rejectTarget.pesan"></strong>?
            </p>

            <form :action="rejectTarget.action" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-2">
                    <button type="button" @click="showRejectModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🧹 MODAL CLEAR -->
    <div x-show="showClearModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="showClearModal = false" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative transform transition-all duration-300"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Bersihkan Pending Lama?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Semua pending yang sudah <strong>approved</strong> & <strong>rejected</strong>
                ({{ $totalApproved + $totalRejected }}) akan dihapus.
            </p>

            <form action="{{ route('knowledge.pending.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-2">
                    <button type="button" @click="showClearModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-600 hover:bg-gray-700
                               text-white font-semibold transition">
                        Ya, Bersihkan
                    </button>
                </div>
            </form>
        </div>
    </div>

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
        function pendingManager() {
            return {
                showApproveModal: false,
                showRejectModal: false,
                showClearModal: false,
                approveTarget: {
                    id: null,
                    pesan: '',
                    jawaban: '',
                    action: ''
                },
                rejectTarget: {
                    id: null,
                    pesan: '',
                    action: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openApproveModal(id, pesan, jawaban, action) {
                    this.approveTarget = {
                        id,
                        pesan: pesan ?? '',
                        jawaban: jawaban ?? '',
                        action
                    };
                    this.showApproveModal = true;
                },

                openRejectModal(id, pesan, action) {
                    this.rejectTarget = {
                        id,
                        pesan: pesan ?? '',
                        action
                    };
                    this.showRejectModal = true;
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
