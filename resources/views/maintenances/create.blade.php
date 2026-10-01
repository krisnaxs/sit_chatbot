@extends('layouts.app')

@section('title', 'Catat Perbaikan — SIAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <a href="{{ route('siam.maintenances.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Catat Perbaikan</h1>
            <p class="text-sm text-gray-500">Catat maintenance aset IT</p>
        </div>

        {{-- ERROR --}}
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

        <form method="POST" action="{{ route('siam.maintenances.store') }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            {{-- ============ PILIH ASET (SEARCHABLE) ============ --}}
            @php
                $assetItems = $assets
                    ->map(
                        fn($a) => [
                            'id' => $a->id,
                            'label' => $a->serial_number . ' — ' . $a->brand . ' ' . $a->model,
                            'sub' =>
                                ($a->currentUser ? 'Pemakai: ' . $a->currentUser->name : 'Tidak ada pemakai') .
                                ' • ' .
                                ($a->category?->name ?? '-'),
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
                            placeholder="Cari SN / brand / model / pemakai..."
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

            {{-- TIPE & STATUS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="corrective" @selected(old('type') === 'corrective')>Perbaikan</option>
                        <option value="preventive" @selected(old('type') === 'preventive')>Preventif</option>
                        <option value="upgrade" @selected(old('type') === 'upgrade')>Upgrade</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="open" @selected(old('status') === 'open')>Dibuka</option>
                        <option value="in_progress" @selected(old('status') === 'in_progress')>Proses</option>
                        <option value="done" @selected(old('status') === 'done')>Selesai</option>
                    </select>
                </div>
            </div>

            {{-- ISSUE --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Masalah / Kerusakan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="issue" required value="{{ old('issue') }}"
                    placeholder="Contoh: Keyboard tidak berfungsi"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- ACTION --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan</label>
                <textarea name="action" rows="3"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: Ganti keyboard baru">{{ old('action') }}</textarea>
            </div>

            {{-- VENDOR & TEKNISI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor Service</label>
                    <select name="vendor_id"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Internal (IT) --</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teknisi</label>
                    <input type="text" name="technician" value="{{ old('technician') }}" placeholder="Nama teknisi"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- TANGGAL & BIAYA --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" required
                        value="{{ old('start_date', now()->format('Y-m-d')) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" min="0" value="{{ old('cost', 0) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- KONDISI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Sebelum (%)</label>
                    <input type="number" name="condition_before" min="0" max="100"
                        value="{{ old('condition_before', 100) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Sesudah (%)</label>
                    <input type="number" name="condition_after" min="0" max="100"
                        value="{{ old('condition_after') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- NOTES --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>

            {{-- TOMBOL --}}
            <div class="flex gap-2 pt-2">
                <a href="{{ route('siam.maintenances.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium">
                    Simpan Perbaikan
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
