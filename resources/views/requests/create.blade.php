@extends('layouts.app')

@section('title', 'Buat Pengajuan — SIAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <a href="{{ route('requests.my') }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali ke pengajuan saya
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Buat Pengajuan Baru</h1>
            <p class="text-sm text-gray-500">Ajukan peminjaman aset atau permintaan konsumable</p>
        </div>

        {{-- ERROR --}}
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif
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

        {{-- FORM --}}
        <form method="POST" action="{{ route('requests.store') }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            {{-- TIPE PENGAJUAN --}}
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                    Jenis Pengajuan
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="loan" class="peer sr-only"
                            @checked(old('type', $type) === 'loan' || !old('type'))>
                        <div
                            class="p-4 rounded-lg border-2 text-center peer-checked:border-amber-500
                                peer-checked:bg-amber-50 transition">
                            <div class="text-2xl mb-1">📅</div>
                            <div class="text-sm font-semibold text-gray-700">Peminjaman</div>
                            <div class="text-xs text-gray-400 mt-1">Pinjam aset sementara</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="consumable" class="peer sr-only"
                            @checked(old('type', $type) === 'consumable')>
                        <div
                            class="p-4 rounded-lg border-2 text-center peer-checked:border-green-500
                                peer-checked:bg-green-50 transition">
                            <div class="text-2xl mb-1">📦</div>
                            <div class="text-sm font-semibold text-gray-700">Konsumable</div>
                            <div class="text-xs text-gray-400 mt-1">Tinta, kabel, dll</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- DETAIL ITEM --}}
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                    Detail Item
                </h2>

                {{-- ============ PILIH ASET (SEARCHABLE, hanya loan) ============ --}}
                @php
                    $assetItems = $assets
                        ->map(
                            fn($a) => [
                                'id' => $a->id,
                                'label' => '[' . $a->serial_number . '] ' . $a->brand . ' ' . $a->model,
                                'sub' =>
                                    ($a->category?->name ?? '-') .
                                    ' • ' .
                                    ($a->status === 'available' ? 'Tersedia' : 'Dipakai'),
                            ],
                        )
                        ->values();
                @endphp

                <div data-type-show="loan" class="mb-4">
                    <div x-data="searchableSelect({
                        items: {{ Js::from($assetItems) }},
                        selectedId: '{{ old('asset_id') }}'
                    })" class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Aset <span class="text-red-500">*</span>
                        </label>

                        <input type="hidden" name="asset_id" :value="selectedId">

                        <button type="button" @click="open = !open"
                            class="w-full border rounded-lg px-3 py-2 text-sm text-left
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   flex items-center justify-between gap-2">
                            <span x-show="!selectedItem" class="text-gray-400">-- Pilih Aset --</span>
                            <span x-show="selectedItem" class="text-gray-800 truncate" x-text="selectedItem?.label"></span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                                :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.away="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
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
                                            <div class="text-sm font-medium text-gray-800 truncate" x-text="item.label">
                                            </div>
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
                </div>

                {{-- ============ KONSUMABLE (SEARCHABLE + QTY) ============ --}}
                <div data-type-show="consumable" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    @php
                        $consumableItems = $consumables
                            ->map(
                                fn($c) => [
                                    'id' => $c->id,
                                    'label' => $c->name,
                                    'sub' => 'Stok: ' . $c->stock_available . ' ' . $c->unit,
                                ],
                            )
                            ->values();
                    @endphp

                    <div class="sm:col-span-2">
                        <div x-data="searchableSelect({
                            items: {{ Js::from($consumableItems) }},
                            selectedId: '{{ old('consumable_id') }}'
                        })" class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Pilih Konsumable <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="consumable_id" :value="selectedId">

                            <button type="button" @click="open = !open"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-left
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                       flex items-center justify-between gap-2">
                                <span x-show="!selectedItem" class="text-gray-400">-- Pilih --</span>
                                <span x-show="selectedItem" class="text-gray-800 truncate"
                                    x-text="selectedItem?.label"></span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl
                                       max-h-72 flex flex-col">

                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-ref="searchInput"
                                        @keydown.escape="open = false" placeholder="Cari konsumable..."
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                               focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <div class="overflow-y-auto flex-1">
                                    <template x-if="filteredItems.length === 0">
                                        <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada konsumable
                                            ditemukan</p>
                                    </template>

                                    <template x-for="item in filteredItems" :key="item.id">
                                        <button type="button" @click="selectItem(item)"
                                            class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition
                                                   flex items-start gap-2">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-800 truncate"
                                                    x-text="item.label"></div>
                                                <div class="text-[10px] text-gray-500 truncate" x-text="item.sub || ''">
                                                </div>
                                            </div>
                                            <svg x-show="selectedId == item.id" xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-indigo-600 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah</label>
                        <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                {{-- TANGGAL --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-dashed">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Dibutuhkan
                        </label>
                        <input type="date" name="needed_date" value="{{ old('needed_date') }}"
                            min="{{ now()->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-400 mt-1">
                            Kapan Anda butuh item ini? Kosongkan kalau tidak mendesak.
                        </p>
                    </div>

                    <div data-type-show="loan">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Kembali <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-400 mt-1">
                            Kapan aset akan dikembalikan? Minimal besok.
                        </p>
                    </div>
                </div>
            </div>

            {{-- LOKASI & DEPARTEMEN --}}
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                    Lokasi Peminjam
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                        <select name="location_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>
                                    {{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                        <select name="department_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- KETERANGAN --}}
            <div>
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                    Keterangan
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Alasan / Keperluan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="purpose" rows="3" required placeholder="Jelaskan kenapa butuh aset/konsumable ini..."
                            class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('purpose') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- TOMBOL --}}
            <div class="flex gap-2 pt-4 border-t">
                <a href="{{ route('requests.my') }}"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium
                       shadow-lg shadow-indigo-500/30 transition">
                    Kirim Pengajuan
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
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="type"]');

            function updateFields() {
                const selected = document.querySelector('input[name="type"]:checked')?.value || 'loan';
                document.querySelectorAll('[data-type-show]').forEach(el => {
                    const types = el.dataset.typeShow.split(',');
                    el.style.display = types.includes(selected) ? '' : 'none';
                });
            }

            radios.forEach(r => r.addEventListener('change', updateFields));
            updateFields();
        });
    </script>
@endpush
