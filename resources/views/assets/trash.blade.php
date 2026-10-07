@extends('layouts.app')

@section('title', 'Sampah Aset — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="trashManager()">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('siam.assets.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke daftar aset
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">🗑️ Sampah Aset</h1>
                <p class="text-sm text-gray-500">
                    Daftar aset yang dihapus (soft delete). Bisa direstore atau dihapus permanen.
                </p>
            </div>

            <div class="flex gap-2">
                @if ($stats['total'] > 0)
                    <form method="POST" action="{{ route('siam.assets.restore-all') }}"
                        @submit.prevent="confirmAction(
                            'Restore Semua Aset?',
                            '{{ $stats['total'] }} aset akan direstore ke daftar aktif.',
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

                    <form method="POST" action="{{ route('siam.assets.empty-trash') }}"
                        @submit.prevent="confirmAction(
                            'Kosongkan Sampah?',
                            {{ $stats['total'] }} Aset dan Relasi Turunan akan terhapus,Tidak bisa dikembalikan!',
                            'Ya, Hapus Permanen',
                            'danger',
                            $event
                        )">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700
                                   text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Kosongkan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Sampah</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['total'] }}</div>
                <div class="text-[10px] text-gray-400">aset terhapus</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Dihapus Bulan Ini</div>
                <div class="text-2xl font-bold text-amber-600">{{ $stats['bulan_ini'] }}</div>
                <div class="text-[10px] text-gray-400">{{ now()->translatedFormat('F Y') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Bisa Direstore</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['bisa_direstore'] }}</div>
                <div class="text-[10px] text-gray-400">30 hari terakhir</div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('siam.assets.trash') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="SN / Kode / Brand / Model / Hostname"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dari Tanggal Hapus</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('siam.assets.trash') }}"
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
                            <th class="px-3 py-2 text-left">Aset</th>
                            <th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-left">Pemakai Terakhir</th>
                            <th class="px-3 py-2 text-left">Dihapus</th>
                            <th class="px-3 py-2 text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($assets as $asset)
                            <tr class="hover:bg-red-50 transition-colors">
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $asset->brand }} {{ $asset->model }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $asset->serial_number }}
                                    </div>
                                    @if ($asset->asset_code)
                                        <div class="text-[10px] text-indigo-600 font-mono">
                                            {{ $asset->asset_code }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs">
                                    {{ $asset->category?->name ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-xs">
                                    @if ($asset->currentUser)
                                        <div class="font-medium">{{ $asset->currentUser->name }}</div>
                                    @else
                                        <span class="text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-red-600">
                                        {{ $asset->deleted_at?->format('d M Y H:i') }}
                                    </div>
                                    <div class="text-gray-400">
                                        {{ $asset->deleted_at?->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- RESTORE --}}
                                        <form method="POST" action="{{ route('siam.assets.restore', $asset->id) }}"
                                            @submit.prevent="confirmAction(
                                                'Restore Aset?',
                                                'Aset {{ $asset->serial_number }} akan dikembalikan ke daftar aktif.',
                                                'Ya, Restore',
                                                'info',
                                                $event
                                            )">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg
                                                       hover:bg-emerald-700 text-xs font-medium
                                                       inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Restore
                                            </button>
                                        </form>

                                        {{-- FORCE DELETE --}}
                                        <form method="POST" action="{{ route('siam.assets.force-delete', $asset->id) }}"
                                            @submit.prevent="confirmAction(
                                                'Hapus Permanen?',
                                                'Aset {{ $asset->serial_number }} dan Relasi Turunan akan terhapus,Tidak bisa dikembalikan!',
                                                'Ya, Hapus Permanen',
                                                'danger',
                                                $event
                                            )">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg
                                                       hover:bg-red-200 text-xs font-medium
                                                       inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 font-medium">Sampah kosong</p>
                                        <p class="text-xs text-gray-400">Tidak ada aset yang dihapus.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assets->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL KONFIRMASI --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeConfirm()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-5 text-white"
                    :class="{
                        'bg-gradient-to-br from-red-500 to-rose-600': confirmType === 'danger',
                        'bg-gradient-to-br from-emerald-500 to-teal-600': confirmType === 'info',
                    }">
                    <h3 class="text-lg font-bold" x-text="confirmTitle"></h3>
                    <p class="text-xs text-white/80">Konfirmasi diperlukan</p>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 text-center" x-html="confirmMessage"></p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                    <button type="button" @click="closeConfirm()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="px-4 py-2 text-white rounded-lg text-sm font-medium shadow-lg"
                        :class="{
                            'bg-red-600 hover:bg-red-700': confirmType === 'danger',
                            'bg-emerald-600 hover:bg-emerald-700': confirmType === 'info',
                        }"
                        x-text="confirmButton"></button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function trashManager() {
            return {
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmButton: '',
                confirmType: 'info',
                pendingForm: null,

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

                init() {
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
