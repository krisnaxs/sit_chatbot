@extends('layouts.app')

@section('title', 'Catat Transaksi — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto">

        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <a href="{{ route('siam.consumable-transactions.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Catat Transaksi Konsumable</h1>
            </div>

            @if (session('error'))
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('siam.consumable-transactions.store') }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf

                {{-- ============ ITEM (SEARCHABLE) ============ --}}
                @php
                    $consumableItems = $consumables
                        ->map(
                            fn($c) => [
                                'id' => $c->id,
                                'label' => $c->name,
                                'sub' =>
                                    'Tersedia: ' .
                                    $c->stock_available .
                                    ' ' .
                                    $c->unit .
                                    ' • Total: ' .
                                    $c->stock_total .
                                    ' ' .
                                    $c->unit,
                            ],
                        )
                        ->values();
                @endphp

                <div x-data="searchableSelect({
                    items: {{ Js::from($consumableItems) }},
                    selectedId: '{{ old('consumable_id', $consumable?->id) }}'
                })" class="relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Item <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" name="consumable_id" :value="selectedId" required>

                    <button type="button" @click="open = !open"
                        class="w-full border rounded-lg px-3 py-2 text-sm text-left
                               focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               flex items-center justify-between gap-2">
                        <span x-show="!selectedItem" class="text-gray-400">-- Pilih Item --</span>
                        <span x-show="selectedItem" class="text-gray-800 truncate" x-text="selectedItem?.label"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0 transition-transform"
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
                                placeholder="Cari item..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div class="overflow-y-auto flex-1">
                            <template x-if="filteredItems.length === 0">
                                <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada item ditemukan</p>
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

                @if ($consumable)
                    <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-xs space-y-1 -mt-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Stok Total:</span>
                            <span class="font-semibold">{{ $consumable->stock_total }} {{ $consumable->unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Stok Tersedia:</span>
                            <span class="font-semibold text-green-700">{{ $consumable->stock_available }}
                                {{ $consumable->unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sedang Dipakai User:</span>
                            <span class="font-semibold text-amber-700">
                                {{ ($consumable->total_out ?? 0) - ($consumable->total_return ?? 0) }}
                                {{ $consumable->unit }}
                            </span>
                        </div>
                    </div>
                @endif

                {{-- ============ TIPE ============ --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tipe Transaksi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="in" class="peer sr-only"
                                @checked(old('type', $defaultType ?? 'out') === 'in')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                                <div class="text-sm font-semibold text-green-700">Masuk</div>
                                <div class="text-xs text-gray-500">Restock</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="out" class="peer sr-only"
                                @checked(old('type', $defaultType ?? 'out') === 'out')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                                <div class="text-sm font-semibold text-red-700">Keluar</div>
                                <div class="text-xs text-gray-500">Kasih ke user</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="return" class="peer sr-only"
                                @checked(old('type', $defaultType ?? 'out') === 'return')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 transition">
                                <div class="text-sm font-semibold text-blue-700">Kembali</div>
                                <div class="text-xs text-gray-500">Dikembalikan</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ============ QTY + TANGGAL ============ --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" required min="1" value="{{ old('quantity', 1) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="transaction_date" required
                            value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                {{-- ============ USER (SEARCHABLE) ============ --}}
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1">User Penerima</label>

                    <input type="hidden" name="user_id" :value="selectedId">

                    <button type="button" @click="open = !open"
                        class="w-full border rounded-lg px-3 py-2 text-sm text-left
                               focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               flex items-center justify-between gap-2">
                        <span x-show="!selectedItem" class="text-gray-400">-- Pilih User --</span>
                        <span x-show="selectedItem" class="text-gray-800 truncate" x-text="selectedItem?.label"></span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                placeholder="Cari user..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div class="overflow-y-auto flex-1">
                            <template x-if="filteredItems.length === 0">
                                <p class="p-3 text-xs text-gray-400 italic text-center">Tidak ada user ditemukan</p>
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

                {{-- ============ LOKASI + ASET ============ --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                        <select name="location_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>{{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Aset (opsional)</label>
                        <select name="asset_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Tidak terkait aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(old('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ============ KEPERLUAN ============ --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan</label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}"
                        placeholder="Contoh: Mouse untuk laptop baru" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>

                {{-- ============ CATATAN ============ --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>

                {{-- ============ SUBMIT ============ --}}
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.consumable-transactions.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-sm font-medium">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
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
