@extends('layouts.app')

@section('title', 'Sampah User — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="userTrashManager()">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke daftar user
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">🗑️ Sampah User</h1>
                <p class="text-sm text-gray-500">
                    Daftar user yang dihapus (soft delete). Bisa direstore kapan saja.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($stats['total'] > 0)
                    {{-- RESTORE ALL --}}
                    <form method="POST" action="{{ route('users.restore-all') }}"
                        @submit.prevent="confirmAction(
                            'Restore Semua User?',
                            '{{ $stats['total'] }} user akan direstore ke daftar aktif (yang tidak konflik email/NIP).',
                            'Ya, Restore Semua',
                            'info',
                            $event
                        )">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700
                                   text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Restore Semua
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ALERT ERROR --}}
        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-red-800">
                    <strong>Gagal:</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- ALERT SUCCESS --}}
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- 🆕 INFO BANNER --}}
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-800">
                <strong>Info:</strong> User yang dihapus <strong>tidak bisa dihapus permanen</strong>
                karena data historisnya (berita acara, serah terima aset, dll) dibutuhkan untuk audit.
                Gunakan tombol <strong>Restore</strong> untuk mengaktifkan kembali user.
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Sampah</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['total'] }}</div>
                <div class="text-[10px] text-gray-400">user terhapus</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Dihapus Bulan Ini</div>
                <div class="text-2xl font-bold text-amber-600">{{ $stats['bulan_ini'] }}</div>
                <div class="text-[10px] text-gray-400">{{ now()->translatedFormat('F Y') }}</div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('users.trash') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama / Username / Email / NIP"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dari Tanggal Hapus</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('users.trash') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- TABEL --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">User</th>
                            <th class="px-3 py-2 text-left">Kontak</th>
                            <th class="px-3 py-2 text-left">Role</th>
                            <th class="px-3 py-2 text-left">Departemen</th>
                            <th class="px-3 py-2 text-left">Dihapus</th>
                            <th class="px-3 py-2 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-red-50 transition-colors">
                                {{-- USER --}}
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gray-400 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-800 truncate">
                                                {{ $user->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 truncate">
                                                {{ $user->username }}
                                                @if ($user->nip)
                                                    • {{ $user->nip }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- KONTAK --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="truncate">{{ $user->email }}</div>
                                    @if ($user->phone)
                                        <div class="text-gray-500">{{ $user->phone }}</div>
                                    @endif
                                </td>

                                {{-- ROLE --}}
                                <td class="px-3 py-3">
                                    @php
                                        $roleColor = match ($user->role) {
                                            'admin' => 'bg-violet-100 text-violet-700',
                                            'support' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded font-semibold {{ $roleColor }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>

                                {{-- DEPARTEMEN --}}
                                <td class="px-3 py-3 text-xs">
                                    {{ $user->department?->name ?? '-' }}
                                </td>

                                {{-- DIHAPUS --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-red-600">
                                        {{ $user->deleted_at?->format('d M Y H:i') }}
                                    </div>
                                    <div class="text-gray-400">
                                        {{ $user->deleted_at?->diffForHumans() }}
                                    </div>
                                </td>

                                {{-- AKSI - HANYA RESTORE --}}
                                <td class="px-3 py-3 text-center">
                                    <form method="POST" action="{{ route('users.restore', $user->id) }}"
                                        @submit.prevent="confirmAction(
                                            'Restore User?',
                                            'User {{ addslashes($user->name) }} akan dikembalikan ke daftar aktif.',
                                            'Ya, Restore',
                                            'info',
                                            $event
                                        )">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg
                                                   hover:bg-emerald-700 text-xs font-medium
                                                   inline-flex items-center gap-1 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 font-medium">Sampah user kosong</p>
                                        <p class="text-xs text-gray-400">
                                            Tidak ada user yang dihapus.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL KONFIRMASI --}}
        {{-- ============================================================ --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeConfirm()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" @click.away="closeConfirm()">

                {{-- HEADER --}}
                <div class="px-6 py-5 text-white bg-gradient-to-br from-emerald-500 to-teal-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold" x-text="confirmTitle"></h3>
                                <p class="text-xs text-white/80">Konfirmasi diperlukan</p>
                            </div>
                        </div>
                        <button type="button" @click="closeConfirm()"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="p-6">
                    <p class="text-sm text-gray-600 text-center" x-html="confirmMessage"></p>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                    <button type="button" @click="closeConfirm()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium
                               transition shadow-lg shadow-emerald-500/30"
                        x-text="confirmButton"></button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TOAST --}}
        {{-- ============================================================ --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[130] flex items-center gap-3
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

    </div>
@endsection

@push('scripts')
    <script>
        function userTrashManager() {
            return {
                // ============================================================
                // STATE
                // ============================================================
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmButton: '',
                confirmType: 'info',
                pendingForm: null,

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                // ============================================================
                // CONFIRM ACTION
                // ============================================================
                confirmAction(title, message, buttonText, type, event) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmButton = buttonText;
                    this.confirmType = type;
                    this.pendingForm = event.target.closest('form');
                    this.showConfirmModal = true;
                },

                executeConfirm() {
                    if (this.pendingForm) {
                        this.pendingForm.submit();
                    }
                    this.closeConfirm();
                },

                closeConfirm() {
                    this.showConfirmModal = false;
                    this.pendingForm = null;
                },

                // ============================================================
                // TOAST
                // ============================================================
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                // ============================================================
                // INIT
                // ============================================================
                init() {
                    @if (session('success'))
                        this.showToast(@json(session('success')), 'success');
                    @endif
                    @if (session('error'))
                        this.showToast(@json(session('error')), 'error');
                    @endif

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showConfirmModal) {
                            this.closeConfirm();
                        }
                    });
                }
            }
        }
    </script>
@endpush
