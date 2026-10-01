@extends('layouts.app')

@section('title', 'Pinjam Aset — SIAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <div>
            <a href="{{ route('siam.loans.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Pinjam Aset</h1>
            <p class="text-sm text-gray-500">Peminjaman aset sementara</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                <p class="font-semibold mb-1">Ada kesalahan:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('siam.loans.store') }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            {{-- ============================================================ --}}
            {{-- ASET (SEARCHABLE) --}}
            {{-- ============================================================ --}}
            @php
                $assetItems = $assets
                    ->map(
                        fn($a) => [
                            'id' => $a->id,
                            'label' => $a->serial_number . ' — ' . $a->brand . ' ' . $a->model,
                            'sub' => $a->category?->name ?? '',
                        ],
                    )
                    ->values();
            @endphp

            <div x-data="searchableSelect({
                items: {{ Js::from($assetItems) }},
                selectedId: '{{ old('asset_id', $asset?->id) }}'
            })" class="relative">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Aset <span class="text-red-500">*</span>
                </label>

                <input type="hidden" name="asset_id" :value="selectedId" required>

                <button type="button" @click="open = !open"
                    class="w-full border rounded-lg px-3 py-2 text-sm text-left
                           focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                           flex items-center justify-between gap-2">
                    <span x-show="!selectedItem" class="text-gray-400">-- Pilih Aset --</span>
                    <span x-show="selectedItem" class="text-gray-800 truncate" x-text="selectedItem?.label"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                        :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl
                           max-h-72 flex flex-col">

                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="search" x-ref="searchInput" @keydown.escape="open = false"
                            placeholder="Cari aset..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div class="overflow-y-auto flex-1">
                        <template x-if="filteredItems.length === 0">
                            <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada aset ditemukan</p>
                        </template>

                        <template x-for="item in filteredItems" :key="item.id">
                            <button type="button" @click="selectItem(item)"
                                class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition
                                       flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-800 truncate" x-text="item.label"></div>
                                    <div class="text-[10px] text-gray-500 truncate" x-text="item.sub || ''"></div>
                                </div>
                                <svg x-show="selectedId == item.id" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-400 -mt-3">Hanya aset dengan status <b>Tersedia</b> yang muncul.</p>

            {{-- ============================================================ --}}
            {{-- PEMINJAM (SEARCHABLE) --}}
            {{-- ============================================================ --}}
            @php
                $userItems = $users
                    ->map(
                        fn($u) => [
                            'id' => $u->id,
                            'label' => $u->name,
                            'sub' => ($u->position ?? '-') . ' - ' . ($u->department?->name ?? '-'),
                        ],
                    )
                    ->values();
            @endphp

            <div x-data="searchableSelect({
                items: {{ Js::from($userItems) }},
                selectedId: '{{ old('user_id') }}'
            })" class="relative">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Peminjam <span class="text-red-500">*</span>
                </label>

                <input type="hidden" name="user_id" :value="selectedId" required>

                <button type="button" @click="open = !open"
                    class="w-full border rounded-lg px-3 py-2 text-sm text-left
                           focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                           flex items-center justify-between gap-2">
                    <span x-show="!selectedItem" class="text-gray-400">-- Pilih Pegawai --</span>
                    <span x-show="selectedItem" class="text-gray-800 truncate" x-text="selectedItem?.label"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                        :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl
                           max-h-72 flex flex-col">

                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="search" x-ref="searchInput" @keydown.escape="open = false"
                            placeholder="Cari pegawai..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div class="overflow-y-auto flex-1">
                        <template x-if="filteredItems.length === 0">
                            <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada pegawai ditemukan</p>
                        </template>

                        <template x-for="item in filteredItems" :key="item.id">
                            <button type="button" @click="selectItem(item)"
                                class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition
                                       flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-800 truncate" x-text="item.label"></div>
                                    <div class="text-[10px] text-gray-500 truncate" x-text="item.sub || ''"></div>
                                </div>
                                <svg x-show="selectedId == item.id" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- TANGGAL --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tanggal Pinjam <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="loan_date" required
                        value="{{ old('loan_date', now()->format('Y-m-d\TH:i')) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Jatuh Tempo <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="due_date" required
                        value="{{ old('due_date', now()->addDays(3)->format('Y-m-d\TH:i')) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- TUJUAN --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan Peminjaman</label>
                <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="Contoh: Presentasi klien"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- KONDISI --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Saat Pinjam (%)</label>
                <input type="number" name="condition_on_loan" min="0" max="100"
                    value="{{ old('condition_on_loan', 100) }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- NOTES --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
            </div>

            {{-- TOMBOL --}}
            <div class="flex gap-2 pt-2">
                <a href="{{ route('siam.loans.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium">
                    Pinjam Aset
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        function searchableSelect(options) {
            return {
                open: false,
                search: '',
                items: options.items || [],
                selectedId: options.selectedId || '',

                get selectedItem() {
                    if (!this.selectedId) return null;
                    return this.items.find(i => i.id == this.selectedId);
                },

                get filteredItems() {
                    if (!this.search.trim()) return this.items;

                    const q = this.search.toLowerCase();
                    return this.items.filter(i =>
                        i.label.toLowerCase().includes(q) ||
                        (i.sub && i.sub.toLowerCase().includes(q))
                    );
                },

                selectItem(item) {
                    this.selectedId = item.id;
                    this.open = false;
                    this.search = '';
                },

                init() {
                    this.$watch('open', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                this.$refs.searchInput?.focus();
                            });
                        }
                    });
                }
            }
        }
    </script>
@endpush
