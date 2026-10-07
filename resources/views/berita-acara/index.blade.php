@extends('layouts.app')

@section('title', 'Berita Acara — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="beritaAcaraIndex()">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('siam.assignments.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke Serah Terima
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Berita Acara</h1>
                <p class="text-sm text-gray-500">
                    Daftar BAST & BAP yang pernah dibuat sistem
                </p>
            </div>

            {{-- 🆕 Tombol buat BA manual --}}
            <button type="button" @click="openAssetPicker()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700
                       text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat BA Manual
            </button>
        </div>

        {{-- STATISTIK RINGKAS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total BA</div>
                <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
                <div class="text-[10px] text-gray-400">dokumen</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Serah Terima</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['serah_terima'] }}</div>
                <div class="text-[10px] text-gray-400">BAST</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Pengembalian</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['pengembalian'] }}</div>
                <div class="text-[10px] text-gray-400">BAP</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Bulan Ini</div>
                <div class="text-2xl font-bold text-violet-600">{{ $stats['bulan_ini'] }}</div>
                <div class="text-[10px] text-gray-400">{{ now()->translatedFormat('F Y') }}</div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('siam.berita-acara.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">

                <div class="lg:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">
                        Cari (Nomor / SN / Merk / Nama)
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Contoh: BAST-2026-0001 atau T14-SN-0041"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jenis</label>
                    <select name="jenis" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="serah_terima" @selected(request('jenis') === 'serah_terima')>
                            Serah Terima
                        </option>
                        <option value="pengembalian" @selected(request('jenis') === 'pengembalian')>
                            Pengembalian
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('siam.berita-acara.index') }}"
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
                            <th class="px-3 py-2 text-left">Nomor BA</th>
                            <th class="px-3 py-2 text-left">Jenis</th>
                            <th class="px-3 py-2 text-left">Aset</th>
                            <th class="px-3 py-2 text-left">Pihak Pertama</th>
                            <th class="px-3 py-2 text-left">Pihak Kedua</th>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $ba)
                            @php
                                $jenisColor =
                                    $ba->jenis === 'serah_terima'
                                        ? 'bg-blue-100 text-blue-700 border border-blue-200'
                                        : 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                $jenisLabel = $ba->jenis === 'serah_terima' ? 'Serah Terima' : 'Pengembalian';
                                $jenisIcon = $ba->jenis === 'serah_terima' ? '📤' : '📥';
                            @endphp
                            <tr class="hover:bg-indigo-50 transition-colors">

                                {{-- Nomor BA --}}
                                <td class="px-3 py-3">
                                    <a href="{{ route('siam.berita-acara.show', $ba) }}"
                                        class="font-mono text-xs font-bold text-indigo-700 hover:underline">
                                        {{ $ba->nomor_ba }}
                                    </a>
                                </td>

                                {{-- Jenis --}}
                                <td class="px-3 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded font-semibold {{ $jenisColor }}">
                                        {{ $jenisIcon }} {{ $jenisLabel }}
                                    </span>
                                </td>

                                {{-- Aset --}}
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->merk }} {{ $ba->model }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $ba->serial_number }}
                                    </div>
                                    @if ($ba->kategori_aset)
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $ba->kategori_aset }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Pihak Pertama --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->pihakPertama?->name ?? '-' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $ba->pihak_pertama_jabatan ?? '-' }}
                                    </div>
                                </td>

                                {{-- Pihak Kedua --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->pihakKedua?->name ?? '-' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $ba->pihak_kedua_jabatan ?? '-' }}
                                    </div>
                                </td>

                                {{-- Tanggal --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-700">
                                        {{ $ba->tanggal_ba?->format('d M Y') ?? '-' }}
                                    </div>
                                    <div class="text-gray-400">
                                        {{ $ba->tempat_ba ?? '-' }}
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-3 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        {{-- Detail --}}
                                        <a href="{{ route('siam.berita-acara.show', $ba) }}" title="Lihat detail"
                                            class="p-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        {{-- Preview PDF --}}
                                        <a href="{{ route('siam.berita-acara.preview', $ba) }}" target="_blank"
                                            title="Preview PDF"
                                            class="p-1.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>

                                        {{-- Download PDF --}}
                                        <a href="{{ route('siam.berita-acara.download', $ba) }}" title="Download PDF"
                                            class="p-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 font-medium">Belum ada berita acara</p>
                                        <p class="text-xs text-gray-400">
                                            Berita acara akan otomatis dibuat saat Anda melakukan
                                            <strong>serah terima</strong> atau <strong>pengembalian</strong> aset.
                                        </p>
                                        <button type="button" @click="openAssetPicker()"
                                            class="mt-2 text-xs px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                            + Buat BA Manual
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- 🆕 MODAL ASSET PICKER --}}
        {{-- ============================================================ --}}
        <div x-show="showPicker" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closePicker()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden"
                @click.away="closePicker()">

                {{-- HEADER --}}
                <div class="px-6 py-4 bg-gradient-to-br from-indigo-500 to-violet-600 text-white shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Pilih Aset</h3>
                                <p class="text-xs text-white/80">Cari aset untuk buat berita acara manual</p>
                            </div>
                        </div>
                        <button type="button" @click="closePicker()"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- SEARCH --}}
                    <div class="mt-3">
                        <input type="text" x-model="search" x-ref="searchInput"
                            placeholder="Cari SN / kode / brand / model / hostname..."
                            class="w-full px-3 py-2 rounded-lg text-sm text-gray-800 placeholder-gray-400
                                   focus:ring-2 focus:ring-white/50 border-0 outline-none">
                    </div>
                </div>

                {{-- LIST ASSET --}}
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="text-xs text-gray-500 mb-2 flex items-center justify-between">
                        <span>
                            Menampilkan <strong x-text="filtered.length"></strong> dari
                            <strong x-text="assets.length"></strong> aset
                        </span>
                        <span x-show="search" class="text-indigo-600">
                            Filter: "<span x-text="search"></span>"
                        </span>
                    </div>

                    {{-- LOADING (kosong karena pakai data lokal, tapi siap kalau mau fetch) --}}
                    <div x-show="loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <p class="text-sm text-gray-400 mt-2">Memuat...</p>
                    </div>

                    <template x-for="a in filtered" :key="a.id">
                        <div @click="selectAsset(a)"
                            class="cursor-pointer p-3 rounded-lg border border-gray-200
                                   hover:border-indigo-400 hover:bg-indigo-50 transition mb-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-gray-800 truncate">
                                        <span x-text="a.brand"></span>
                                        <span x-text="a.model"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono truncate" x-text="a.serial_number"></div>
                                    <div class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span x-text="a.category"></span>
                                        <span class="text-gray-300">•</span>
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600"
                                            x-text="a.status_label"></span>
                                        <template x-if="a.hostname">
                                            <span class="text-gray-400">
                                                🖥️ <span x-text="a.hostname"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div x-show="a.assignment_count > 0"
                                        class="text-[10px] text-blue-600 mt-1 font-semibold">
                                        📋 <span x-text="a.assignment_count"></span> record assignment
                                    </div>
                                    <div x-show="a.assignment_count === 0"
                                        class="text-[10px] text-amber-600 mt-1 font-semibold">
                                        ⚠️ Belum ada assignment — BA tidak bisa dibuat
                                    </div>
                                </div>
                                <div class="text-xs text-indigo-600 font-semibold shrink-0"
                                    :class="a.assignment_count === 0 && 'opacity-30'">
                                    Pilih →
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="!loading && filtered.length === 0" class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-2"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-sm text-gray-400">Tidak ada aset yang cocok dengan pencarian.</p>
                        <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain.</p>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center shrink-0">
                    <p class="text-xs text-gray-500">
                        Pilih aset → akan diarahkan ke form buat BA.
                    </p>
                    <button type="button" @click="closePicker()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function beritaAcaraIndex() {
            return {
                // ============================================================
                // STATE
                // ============================================================
                showPicker: false,
                search: '',
                loading: false,

                // Data aset dari controller (di-render server-side)
                assets: @json($assetOptions),

                // ============================================================
                // COMPUTED: Filter aset berdasarkan search
                // ============================================================
                get filtered() {
                    const term = (this.search || '').toLowerCase().trim();

                    if (!term) {
                        // Tanpa search: tampilkan max 100 aset
                        return this.assets.slice(0, 100);
                    }

                    return this.assets.filter(a => {
                        return (a.brand || '').toLowerCase().includes(term) ||
                            (a.model || '').toLowerCase().includes(term) ||
                            (a.serial_number || '').toLowerCase().includes(term) ||
                            (a.asset_code || '').toLowerCase().includes(term) ||
                            (a.hostname || '').toLowerCase().includes(term) ||
                            (a.category || '').toLowerCase().includes(term);
                    }).slice(0, 100);
                },

                // ============================================================
                // ACTIONS
                // ============================================================
                openAssetPicker() {
                    this.search = '';
                    this.showPicker = true;

                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                },

                closePicker() {
                    this.showPicker = false;
                    this.search = '';
                },

                selectAsset(asset) {
                    if (asset.assignment_count === 0) {
                        // Tetap boleh masuk, tapi kasih warning
                        const proceed = confirm(
                            `Aset ${asset.brand} ${asset.model} (${asset.serial_number}) belum memiliki record assignment.\n\n` +
                            `BA tidak akan bisa dibuat karena tidak ada data serah terima.\n\n` +
                            `Tetap lanjutkan?`
                        );
                        if (!proceed) return;
                    }

                    // Redirect ke form create BA
                    window.location.href = asset.url;
                },

                // ============================================================
                // INIT
                // ============================================================
                init() {
                    // Auto-submit filter
                    document.querySelectorAll('[data-auto-submit]').forEach(el => {
                        el.addEventListener('change', () => el.closest('form').submit());
                    });

                    // ESC untuk close modal
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showPicker) {
                            this.closePicker();
                        }
                    });
                }
            }
        }
    </script>
@endpush
