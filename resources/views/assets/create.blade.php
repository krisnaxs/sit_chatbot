<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tambah Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Tambah Aset" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-4xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div>
                <a href="{{ route('siam.assets.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke daftar aset
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Tambah Aset Baru</h1>
                <p class="text-sm text-gray-500">Registrasi aset IT: laptop, PC, printer, dll</p>
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

            {{-- FORM --}}
            <form method="POST" action="{{ route('siam.assets.store') }}" enctype="multipart/form-data"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                @csrf

                {{-- ============ IDENTITAS ASET ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Identitas Aset
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Serial Number (SN) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="serial_number" required value="{{ old('serial_number') }}"
                                placeholder="Contoh: T14-SN-0041"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">SN harus unik, tidak boleh sama.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Aset</label>
                            <input type="text" name="asset_code" value="{{ old('asset_code') }}"
                                placeholder="Kosongkan untuk auto-generate"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Format: AST-2026-0001 (otomatis kalau kosong).</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Hostname</label>
                            <input type="text" name="hostname" value="{{ old('hostname') }}"
                                placeholder="Contoh: NB-T14-041"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
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

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">CPU</label>
                            <input type="text" name="specification[cpu]" value="{{ old('specification.cpu') }}"
                                placeholder="Contoh: Intel Core i5-1335U"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">RAM</label>
                            <input type="text" name="specification[ram]" value="{{ old('specification.ram') }}"
                                placeholder="Contoh: 16 GB DDR4"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Storage</label>
                            <input type="text" name="specification[storage]"
                                value="{{ old('specification.storage') }}" placeholder="Contoh: 512 GB NVMe SSD"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">GPU</label>
                            <input type="text" name="specification[gpu]" value="{{ old('specification.gpu') }}"
                                placeholder="Contoh: Intel Iris Xe"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">OS</label>
                            <input type="text" name="os" value="{{ old('os') }}"
                                placeholder="Contoh: Windows 11 Pro"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lisensi OS</label>
                            <select name="os_license"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih --</option>
                                <option value="OEM" @selected(old('os_license') === 'OEM')>OEM</option>
                                <option value="Retail" @selected(old('os_license') === 'Retail')>Retail</option>
                                <option value="Volume" @selected(old('os_license') === 'Volume')>Volume</option>
                                <option value="Tidak Ada" @selected(old('os_license') === 'Tidak Ada')>Tidak Ada</option>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipe Kepemilikan <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="ownership_type" value="owned" class="peer sr-only"
                                        @checked(old('ownership_type', 'owned') === 'owned')>
                                    <div
                                        class="p-3 rounded-lg border-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                                        <div class="text-sm font-semibold text-green-700">🏢 Hak Milik</div>
                                        <div class="text-xs text-gray-500">Dibeli kantor</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="ownership_type" value="leased" class="peer sr-only"
                                        @checked(old('ownership_type') === 'leased')>
                                    <div
                                        class="p-3 rounded-lg border-2 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                                        <div class="text-sm font-semibold text-orange-700">📄 Sewa</div>
                                        <div class="text-xs text-gray-500">Kontrak sewa vendor</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                            <select name="vendor_id"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($vendors ?? [] as $v)
                                    <option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>
                                        {{ $v->name }} ({{ $v->type_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Invoice / Kontrak</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                placeholder="INV-2026-0001 / SEWA-2026-0001"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Beli / Mulai
                                Sewa</label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Beli (Rp)</label>
                            <input type="number" name="purchase_price" min="0"
                                value="{{ old('purchase_price') }}" placeholder="0"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya Sewa/Bulan (Rp)</label>
                            <input type="number" name="monthly_cost" min="0"
                                value="{{ old('monthly_cost') }}" placeholder="0"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kontrak Berakhir</label>
                            <input type="date" name="contract_end" value="{{ old('contract_end') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Garansi Berakhir</label>
                            <input type="date" name="warranty_expire" value="{{ old('warranty_expire') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                    </div>
                </div>

                {{-- ============ STATUS & KONDISI ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Status & Kondisi
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="available" @selected(old('status', 'available') === 'available')>Tersedia</option>
                                <option value="in_use" @selected(old('status') === 'in_use')>Dipakai</option>
                                <option value="loaned" @selected(old('status') === 'loaned')>Dipinjam</option>
                                <option value="maintenance" @selected(old('status') === 'maintenance')>Perbaikan</option>
                                <option value="retired" @selected(old('status') === 'retired')>Pensiun</option>
                                <option value="lost" @selected(old('status') === 'lost')>Hilang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                            <input type="number" name="condition_percent" min="0" max="100"
                                value="{{ old('condition_percent', 100) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Kondisi</label>
                            <input type="text" name="condition_notes" value="{{ old('condition_notes') }}"
                                placeholder="Contoh: Ada gores di casing"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"
                                placeholder="Catatan lain-lain...">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- ============ FOTO ============ --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Foto Aset (Opsional)
                    </h2>
                    <input type="file" name="photo" accept="image/*"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG. Maks: 2MB.</p>
                </div>

                {{-- ============ TOMBOL ============ --}}
                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('siam.assets.index') }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        Simpan Aset
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
        function assetTypeSelector() {
            return {
                selectedBrand: '{{ old('brand', '') }}',
                selectedModel: '{{ old('model', '') }}',
                allTypes: @json($assetTypes->map(fn($t) => ['id' => $t->id, 'brand' => $t->brand, 'model' => $t->model])),

                get filteredModels() {
                    if (!this.selectedBrand) return [];
                    return this.allTypes.filter(t => t.brand === this.selectedBrand);
                },

                onBrandChange() {
                    // Reset model kalau tidak ada di brand baru
                    const stillValid = this.filteredModels.some(m => m.model === this.selectedModel);
                    if (!stillValid) this.selectedModel = '';
                },

                init() {
                    // Jaga-jaga kalau ada old input setelah validasi gagal
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
