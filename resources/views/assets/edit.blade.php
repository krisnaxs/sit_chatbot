<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Edit Aset" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-4xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div>
                <a href="{{ route('siam.assets.show', $asset) }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke detail aset
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Aset</h1>
                <p class="text-sm text-gray-500 font-mono">SN: {{ $asset->serial_number }}</p>
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

            <form method="POST" action="{{ route('siam.assets.update', $asset) }}" enctype="multipart/form-data"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- ============ IDENTITAS ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Identitas Aset
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Serial Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="serial_number" required
                                value="{{ old('serial_number', $asset->serial_number) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Aset</label>
                            <input type="text" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                            <select name="category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $asset->category_id) == $c->id)>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Hostname</label>
                            <input type="text" name="hostname" value="{{ old('hostname', $asset->hostname) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                    </div>

                    {{-- ============ BRAND & MODEL (DEPENDENT DROPDOWN) ============ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" x-data="assetTypeSelector()"
                        x-init="init()">

                        {{-- BRAND --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Brand <span class="text-red-500">*</span>
                            </label>
                            <select name="brand" required x-model="selectedBrand" @change="onBrandChange()"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Brand --</option>
                                @foreach ($brands as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                            @error('brand')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MODEL --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Model / Type <span class="text-red-500">*</span>
                            </label>
                            <select name="model" required x-model="selectedModel" :disabled="!selectedBrand"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500
                                           disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">-- Pilih Brand dulu --</option>
                                <template x-for="m in filteredModels" :key="m.id">
                                    <option :value="m.model" x-text="m.model"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">
                                <span x-show="!selectedBrand">Pilih brand dulu untuk melihat model</span>
                                <span x-show="selectedBrand && filteredModels.length > 0">
                                    <span x-text="filteredModels.length"></span> model tersedia untuk
                                    <span class="font-semibold" x-text="selectedBrand"></span>
                                </span>
                                <span x-show="selectedBrand && filteredModels.length === 0" class="text-amber-600">
                                    ⚠️ Belum ada model untuk brand ini.
                                </span>
                            </p>
                            <a href="{{ route('siam.asset-types.index') }}" target="_blank"
                                class="text-xs text-indigo-600 hover:underline inline-flex items-center gap-1 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Kelola Brand & Model
                            </a>
                            @error('model')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ============ SPESIFIKASI ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Spesifikasi Teknis
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php $spec = old('specification', $asset->specification ?? []); @endphp

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">CPU</label>
                            <input type="text" name="specification[cpu]" value="{{ $spec['cpu'] ?? '' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">RAM</label>
                            <input type="text" name="specification[ram]" value="{{ $spec['ram'] ?? '' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Storage</label>
                            <input type="text" name="specification[storage]" value="{{ $spec['storage'] ?? '' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">GPU</label>
                            <input type="text" name="specification[gpu]" value="{{ $spec['gpu'] ?? '' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">OS</label>
                            <input type="text" name="os" value="{{ old('os', $asset->os) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lisensi OS</label>
                            <select name="os_license" class="w-full border rounded-lg px-3 py-2 text-sm">
                                @foreach (['OEM', 'Retail', 'Volume', 'Tidak Ada'] as $lic)
                                    <option value="{{ $lic }}" @selected(old('os_license', $asset->os_license) === $lic)>
                                        {{ $lic }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ============ KEPEMILIKAN ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Kepemilikan
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Kepemilikan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="ownership_type" value="owned" class="peer sr-only"
                                        @checked(old('ownership_type', $asset->ownership_type) === 'owned')>
                                    <div
                                        class="p-3 rounded-lg border-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                                        <div class="text-sm font-semibold text-green-700">🏢 Hak Milik</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="ownership_type" value="leased" class="peer sr-only"
                                        @checked(old('ownership_type', $asset->ownership_type) === 'leased')>
                                    <div
                                        class="p-3 rounded-lg border-2 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                                        <div class="text-sm font-semibold text-orange-700">📄 Sewa</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                            <select name="vendor_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($vendors ?? [] as $v)
                                    <option value="{{ $v->id }}" @selected(old('vendor_id', $asset->ownership?->vendor_id) == $v->id)>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Invoice / Kontrak</label>
                            <input type="text" name="invoice_number"
                                value="{{ old('invoice_number', $asset->ownership?->invoice_number) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Beli</label>
                            <input type="date" name="purchase_date"
                                value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Beli (Rp)</label>
                            <input type="number" name="purchase_price" min="0"
                                value="{{ old('purchase_price', $asset->purchase_price) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya Sewa/Bulan (Rp)</label>
                            <input type="number" name="monthly_cost" min="0"
                                value="{{ old('monthly_cost', $asset->ownership?->monthly_cost) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kontrak Berakhir</label>
                            <input type="date" name="contract_end"
                                value="{{ old('contract_end', $asset->ownership?->contract_end?->format('Y-m-d')) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Garansi Berakhir</label>
                            <input type="date" name="warranty_expire"
                                value="{{ old('warranty_expire', $asset->warranty_expire?->format('Y-m-d')) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                    </div>
                </div>

                {{-- ============ STATUS ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Status & Kondisi
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="available" @selected(old('status', $asset->status) === 'available')>Tersedia</option>
                                <option value="in_use" @selected(old('status', $asset->status) === 'in_use')>Dipakai</option>
                                <option value="loaned" @selected(old('status', $asset->status) === 'loaned')>Dipinjam</option>
                                <option value="maintenance" @selected(old('status', $asset->status) === 'maintenance')>Perbaikan</option>
                                <option value="retired" @selected(old('status', $asset->status) === 'retired')>Pensiun</option>
                                <option value="lost" @selected(old('status', $asset->status) === 'lost')>Hilang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                            <input type="number" name="condition_percent" min="0" max="100"
                                value="{{ old('condition_percent', $asset->condition_percent) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Kondisi</label>
                            <input type="text" name="condition_notes"
                                value="{{ old('condition_notes', $asset->condition_notes) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $asset->notes) }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- ============ FOTO ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Foto Aset
                    </h2>
                    @if ($asset->photo_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $asset->photo_path) }}"
                                alt="{{ $asset->serial_number }}" class="w-40 h-40 object-cover rounded-lg border">
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Biarkan kosong kalau tidak mau ganti.</p>
                </div>

                {{-- ============ TOMBOL ============ --}}
                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('siam.assets.show', $asset) }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        Update Aset
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
        function assetTypeSelector() {
            return {
                selectedBrand: '{{ old('brand', $asset->brand) }}',
                selectedModel: '{{ old('model', $asset->model) }}',
                allTypes: @json($assetTypes->map(fn($t) => ['id' => $t->id, 'brand' => $t->brand, 'model' => $t->model])),

                get filteredModels() {
                    if (!this.selectedBrand) return [];
                    return this.allTypes.filter(t => t.brand === this.selectedBrand);
                },

                onBrandChange() {
                    const stillValid = this.filteredModels.some(m => m.model === this.selectedModel);
                    if (!stillValid) this.selectedModel = '';
                },

                init() {
                    // Pastikan model yang tersimpan masih valid untuk brand
                    if (this.selectedBrand) {
                        this.onBrandChange();
                    }
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
