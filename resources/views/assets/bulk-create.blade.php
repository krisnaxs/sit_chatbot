@extends('layouts.app')

@section('title', 'Input Banyak Aset — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="bulkCreateManager()">

        <div class="max-w-5xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ route('siam.assets.index') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke daftar aset
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1">Input Banyak Aset</h1>
                    <p class="text-sm text-gray-500">
                        Registrasi banyak unit aset identik sekaligus (mis. 40 unit laptop dengan spek sama)
                    </p>
                </div>
                <div class="px-3 py-2 bg-purple-50 border border-purple-200 rounded-lg">
                    <span class="text-xs font-semibold text-purple-700">
                        💡 Hemat waktu: 1x input untuk banyak unit
                    </span>
                </div>
            </div>

            {{-- ERROR VALIDATION --}}
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

            {{-- ERROR CUSTOM (dari controller) --}}
            @if (session('error') || session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
                    class="fixed top-4 right-4 z-[200] max-w-sm" style="display:none">
                    @if (session('error'))
                        <div class="bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3">
                            <span class="text-lg">⚠️</span>
                            <div class="text-sm leading-snug">{{ session('error') }}</div>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="bg-emerald-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3">
                            <span class="text-lg">✅</span>
                            <div class="text-sm leading-snug">{{ session('success') }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- FORM --}}
            <form id="bulkCreateForm" method="POST" action="{{ route('siam.assets.bulk-store') }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                @csrf

                {{-- ============ 1. DATA UMUM ============ --}}
                <div>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">1</span>
                        Data Umum Aset
                        <span class="text-xs font-normal text-gray-400 normal-case">— berlaku untuk semua unit</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-data="assetTypeSelector()" x-init="init()" class="contents">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Brand <span class="text-red-500">*</span>
                                </label>
                                <select name="brand" required x-model="selectedBrand" @change="onBrandChange()"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <option value="">-- Pilih Brand --</option>
                                    @foreach ($brands as $b)
                                        <option value="{{ $b }}">{{ $b }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Model / Type <span class="text-red-500">*</span>
                                </label>
                                <select name="model" required x-model="selectedModel" :disabled="!selectedBrand"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500
                                           disabled:bg-gray-100 disabled:cursor-not-allowed">
                                    <option value="">-- Pilih Brand dulu --</option>
                                    <template x-for="m in filteredModels" :key="m.id">
                                        <option :value="m.model" x-text="m.model"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Status Awal <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required x-model="status"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                <option value="available">Tersedia</option>
                                <option value="in_use">Dipakai (assign ke pegawai)</option>
                                <option value="retired">Pensiun</option>
                                <option value="lost">Hilang</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">
                                Bulk hanya mendukung status ini. Untuk "Dipinjam"/"Perbaikan" gunakan input satuan.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">CPU</label>
                            <input type="text" name="specification[cpu]" value="{{ old('specification.cpu') }}"
                                placeholder="Contoh: Intel Core i5-1335U"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">RAM</label>
                            <input type="text" name="specification[ram]" value="{{ old('specification.ram') }}"
                                placeholder="Contoh: 16 GB DDR4" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Storage</label>
                            <input type="text" name="specification[storage]" value="{{ old('specification.storage') }}"
                                placeholder="Contoh: 512 GB NVMe SSD" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">GPU</label>
                            <input type="text" name="specification[gpu]" value="{{ old('specification.gpu') }}"
                                placeholder="Contoh: Intel Iris Xe" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">OS</label>
                            <input type="text" name="os" value="{{ old('os') }}"
                                placeholder="Contoh: Windows 11 Pro" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lisensi OS</label>
                            <select name="os_license" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                <option value="OEM" @selected(old('os_license') === 'OEM')>OEM</option>
                                <option value="Retail" @selected(old('os_license') === 'Retail')>Retail</option>
                                <option value="Volume" @selected(old('os_license') === 'Volume')>Volume</option>
                                <option value="Tidak Ada" @selected(old('os_license') === 'Tidak Ada')>Tidak Ada</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ============ 2. KEPEMILIKAN ============ --}}
                <div>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">2</span>
                        Kepemilikan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        <div class="sm:col-span-2 lg:col-span-3">
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
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="ownership_type" value="leased" class="peer sr-only"
                                        @checked(old('ownership_type') === 'leased')>
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
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Invoice / Kontrak</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Beli</label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Beli / Unit (Rp)</label>
                            <input type="number" name="purchase_price" min="0"
                                value="{{ old('purchase_price') }}" placeholder="0"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Harga per unit, bukan total.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya Sewa / Bulan (Rp)</label>
                            <input type="number" name="monthly_cost" min="0" value="{{ old('monthly_cost') }}"
                                placeholder="0" class="w-full border rounded-lg px-3 py-2 text-sm">
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

                {{-- ============ 3. JUMLAH & SERIAL NUMBER ============ --}}
                <div>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">3</span>
                        Jumlah & Serial Number
                    </h2>

                    {{-- Jumlah unit --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Jumlah Unit <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantity" min="1" max="500" required
                                value="{{ old('quantity', 40) }}" x-model.number="quantity"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-bold text-lg focus:ring-2 focus:ring-purple-500">
                            <p class="text-xs text-gray-400 mt-1">Maks 500 unit per submit.</p>
                        </div>
                    </div>

                    {{-- Toggle auto/manual serial --}}
                    <div class="p-4 rounded-lg bg-purple-50 border border-purple-200 mb-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" x-model="autoSerial" @change="syncCounts()"
                                class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5">
                            <div>
                                <div class="font-semibold text-sm text-purple-900">
                                    Auto-generate Serial Number
                                </div>
                                <div class="text-xs text-purple-700">
                                    Centang kalau SN berpola (mis. <code class="font-mono">SN-LAP-0001</code>).
                                    Hilangkan centang untuk input SN manual (mis. SN dari pabrik berbeda-beda).
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- MODE AUTO --}}
                    <div x-show="autoSerial" x-cloak x-transition class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Prefix Serial <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="serial_prefix" x-model="serialPrefix"
                                value="{{ old('serial_prefix', 'SN-LAP-') }}" :required="autoSerial"
                                placeholder="Contoh: SN-LAP-"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Mulai Nomor <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="serial_start" min="1" x-model.number="serialStart"
                                :required="autoSerial" value="{{ old('serial_start', 1) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Padding (0-6)</label>
                            <input type="number" name="serial_pad" min="0" max="6"
                                x-model.number="serialPad" :required="autoSerial" value="{{ old('serial_pad', 4) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <p class="text-xs text-gray-400 mt-1">4 → 0001, 0 → 1</p>
                        </div>
                    </div>

                    {{-- MODE MANUAL --}}
                    <div x-show="!autoSerial" x-cloak x-transition class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Daftar Serial Number <span class="text-red-500">*</span>
                        </label>
                        <textarea name="serial_list" x-model="serialList" :required="!autoSerial" rows="10" @input="syncCounts()"
                            placeholder="Tempel SN di sini, 1 baris = 1 unit&#10;Contoh:&#10;5CG1234ABC&#10;5CG9876XYZ&#10;5CG5555DEF"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-purple-500"></textarea>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">
                                Terdeteksi: <span class="font-bold text-purple-700" x-text="manualSerialCount"></span>
                                baris
                            </span>
                            <span
                                :class="manualSerialCount === quantity ? 'text-emerald-600 font-semibold' :
                                    'text-amber-600 font-semibold'">
                                Target: <span x-text="quantity"></span> unit
                                <template x-if="manualSerialCount !== quantity">
                                    <span> — ⚠️ belum cocok</span>
                                </template>
                                <template x-if="manualSerialCount === quantity">
                                    <span> — ✓ cocok</span>
                                </template>
                            </span>
                        </div>
                    </div>

                    {{-- Live preview serial range --}}
                    <div x-show="autoSerial" x-cloak class="mt-3 p-3 rounded-lg bg-purple-50 border border-purple-200">
                        <p class="text-xs text-purple-700">
                            <span class="font-semibold">Preview serial:</span>
                            <code class="font-mono text-purple-900" x-text="serialRangePreview()"></code>
                        </p>
                    </div>
                </div>

                {{-- ============ 4. ASSET CODE ============ --}}
                <div>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-xs font-bold flex items-center justify-center">4</span>
                        Asset Code
                        <span class="text-xs font-normal text-gray-400 normal-case">— opsional</span>
                    </h2>

                    {{-- Toggle auto code --}}
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 mb-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" x-model="autoCode" @change="syncCounts()"
                                class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5">
                            <div>
                                <div class="font-semibold text-sm text-gray-800">
                                    Auto-generate Asset Code
                                </div>
                                <div class="text-xs text-gray-600">
                                    Hilangkan centang kalau mau input kode manual / tidak pakai kode.
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- MODE AUTO --}}
                    <div x-show="autoCode" x-cloak x-transition class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Prefix Asset Code</label>
                            <input type="text" name="code_prefix" x-model="codePrefix"
                                value="{{ old('code_prefix') }}" placeholder="Contoh: LAP-2026-"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Mulai Nomor</label>
                            <input type="number" name="code_start" x-model.number="codeStart"
                                value="{{ old('code_start', 1) }}" min="1"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Padding (0-6)</label>
                            <input type="number" name="code_pad" x-model.number="codePad"
                                value="{{ old('code_pad', 4) }}" min="0" max="6"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- MODE MANUAL --}}
                    <div x-show="!autoCode" x-cloak x-transition class="space-y-2">
                        <textarea name="code_list" x-model="codeList" rows="8" @input="syncCounts()"
                            placeholder="Kosongkan kalau tidak pakai kode&#10;Atau tempel kode di sini, 1 baris = 1 unit"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono"></textarea>
                        <div class="text-xs text-gray-500">
                            Terdeteksi: <span class="font-bold text-gray-700" x-text="manualCodeCount"></span> baris
                            <span class="text-gray-400">(boleh kosong / boleh ≠ jumlah unit)</span>
                        </div>
                    </div>

                    {{-- Hostname prefix --}}
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Prefix Hostname <span class="text-xs font-normal text-gray-400">(opsional)</span>
                        </label>
                        <input type="text" name="hostname_prefix" value="{{ old('hostname_prefix') }}"
                            placeholder="Contoh: PC-HR-"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono sm:w-1/2">
                    </div>
                </div>

                {{-- ============ 5. ASSIGN LANGSUNG (OPSIONAL) ============ --}}
                <div x-show="status === 'in_use'" x-cloak x-transition>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">5</span>
                        Assign ke Pegawai
                        <span class="text-xs font-normal text-gray-400 normal-case">— semua unit ke pegawai yang
                            sama</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Pegawai Penerima <span class="text-red-500">*</span>
                            </label>
                            <select name="assign[user_id]"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" @selected(old('assign.user_id') == $u->id)>
                                        {{ $u->name }} — {{ $u->position ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                            <select name="assign[location_id]" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach ($locations as $l)
                                    <option value="{{ $l->id }}" @selected(old('assign.location_id') == $l->id)>
                                        {{ $l->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                            <select name="assign[department_id]" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}" @selected(old('assign.department_id') == $d->id)>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Serah Terima</label>
                            <input type="datetime-local" name="assign[assigned_at]"
                                value="{{ old('assign.assigned_at', now()->format('Y-m-d\TH:i')) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Serah (%)</label>
                            <input type="number" name="assign[condition_on_assign]" min="0" max="100"
                                value="{{ old('assign.condition_on_assign', 100) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                {{-- ============ 6. KONDISI & CATATAN ============ --}}
                <div>
                    <h2
                        class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">6</span>
                        Kondisi & Catatan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                            <input type="number" name="condition_percent" min="0" max="100"
                                value="{{ old('condition_percent', 100) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Kondisi</label>
                            <input type="text" name="condition_notes" value="{{ old('condition_notes') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ============ TOMBOL ============ --}}
                <div class="flex flex-wrap gap-2 pt-4 border-t items-center justify-between">
                    <div class="flex gap-2">
                        <a href="{{ route('siam.assets.index') }}"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium
                                   shadow-lg shadow-purple-500/30 transition inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Simpan <span x-text="quantity"></span> Aset Sekaligus
                        </button>
                    </div>
                    <button type="button" @click="openPreview()"
                        class="px-4 py-2.5 bg-white border border-purple-300 text-purple-700 rounded-lg text-sm font-medium hover:bg-purple-50 transition inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview Serial
                    </button>
                </div>

            </form>
        </div>

        {{-- ============ MODAL PREVIEW ============ --}}
        <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showPreviewModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="bg-gradient-to-br from-purple-500 to-violet-600 px-6 py-5 text-white shrink-0">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Preview Serial Number</h3>
                                <p class="text-sm text-white/80">
                                    <span x-text="previewRows.length"></span> unit akan dibuat
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showPreviewModal = false"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left w-12">#</th>
                                <th class="px-3 py-2 text-left">Serial Number</th>
                                <th class="px-3 py-2 text-left">Asset Code</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="row in previewRows" :key="row.no">
                                <tr :class="row.isDup ? 'bg-red-50' : ''">
                                    <td class="px-3 py-2 text-xs text-gray-500" x-text="row.no"></td>
                                    <td class="px-3 py-2 font-mono text-xs"
                                        :class="row.isDup ? 'text-red-700' : 'text-indigo-700'">
                                        <span x-text="row.serial"></span>
                                        <template x-if="row.isDup">
                                            <span
                                                class="ml-2 px-1.5 py-0.5 text-[10px] rounded bg-red-200 text-red-800 font-bold">
                                                DUPLIKAT
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600" x-text="row.code ?? '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center shrink-0">
                    <template x-if="hasDuplicates">
                        <span class="text-xs text-red-600 font-semibold">
                            ⚠️ Ada serial/asset code duplikat. Ganti prefix atau nomor mulai.
                        </span>
                    </template>
                    <template x-if="!hasDuplicates">
                        <span class="text-xs text-emerald-600 font-semibold">
                            ✓ Semua serial unik, siap disimpan.
                        </span>
                    </template>
                    <button type="button" @click="showPreviewModal = false"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function assetTypeSelector() {
            return {
                selectedBrand: @json(old('brand', '')),
                selectedModel: @json(old('model', '')),
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
                    if (this.selectedBrand) this.onBrandChange();
                }
            }
        }

        function bulkCreateManager() {
            return {
                // form state
                status: @json(old('status', 'available')),
                quantity: {{ (int) old('quantity', 40) }},

                // === AUTO/MANUAL TOGGLES ===
                autoSerial: true,
                autoCode: true,

                // auto fields
                serialPrefix: @json(old('serial_prefix', 'SN-LAP-')),
                serialStart: {{ (int) old('serial_start', 1) }},
                serialPad: {{ (int) old('serial_pad', 4) }},
                codePrefix: @json(old('code_prefix', '')),
                codeStart: {{ (int) old('code_start', 1) }},
                codePad: {{ (int) old('code_pad', 4) }},

                // manual fields
                serialList: '',
                codeList: '',
                manualSerialCount: 0,
                manualCodeCount: 0,

                // preview state
                showPreviewModal: false,
                previewRows: [],
                hasDuplicates: false,

                parseLines(text) {
                    if (!text) return [];
                    return text.split('\n')
                        .map(s => s.trim())
                        .filter(s => s.length > 0);
                },

                syncCounts() {
                    this.manualSerialCount = this.parseLines(this.serialList).length;
                    this.manualCodeCount = this.parseLines(this.codeList).length;
                },

                pad(num, size) {
                    if (size <= 0) return String(num);
                    return String(num).padStart(size, '0');
                },

                serialRangePreview() {
                    if (!this.serialPrefix || this.quantity < 1) return '—';
                    const first = this.serialPrefix + this.pad(this.serialStart, this.serialPad);
                    const last = this.serialPrefix + this.pad(this.serialStart + this.quantity - 1, this.serialPad);
                    return this.quantity === 1 ? first : `${first} … ${last}`;
                },

                buildRowsForPreview() {
                    // === MODE MANUAL SERIAL ===
                    if (!this.autoSerial) {
                        const serials = this.parseLines(this.serialList);
                        const codes = this.autoCode ? [] :
                            this.parseLines(this.codeList);
                        return serials.map((s, i) => ({
                            no: i + 1,
                            serial: s,
                            code: codes[i] ?? null,
                            isDup: false,
                        }));
                    }

                    // === MODE AUTO SERIAL ===
                    const rows = [];
                    for (let i = 0; i < this.quantity; i++) {
                        const serial = this.serialPrefix + this.pad(this.serialStart + i, this.serialPad);
                        let code = null;
                        if (this.autoCode && this.codePrefix) {
                            code = this.codePrefix + this.pad(this.codeStart + i, this.codePad);
                        } else if (!this.autoCode) {
                            const codes = this.parseLines(this.codeList);
                            code = codes[i] ?? null;
                        }
                        rows.push({
                            no: i + 1,
                            serial,
                            code,
                            isDup: false
                        });
                    }
                    return rows;
                },

                async openPreview() {
                    // Validasi client
                    if (this.autoSerial) {
                        if (!this.serialPrefix) {
                            alert('Isi prefix serial number dulu.');
                            return;
                        }
                    } else {
                        const list = this.parseLines(this.serialList);
                        if (list.length === 0) {
                            alert('Isi daftar Serial Number dulu.');
                            return;
                        }
                        if (list.length !== this.quantity) {
                            alert(`Jumlah SN (${list.length}) tidak sama dengan jumlah unit (${this.quantity}).`);
                            return;
                        }
                    }

                    if (this.quantity < 1) {
                        alert('Jumlah unit minimal 1.');
                        return;
                    }

                    this.previewRows = this.buildRowsForPreview();
                    this.hasDuplicates = false;
                    this.showPreviewModal = true;

                    // Cek duplikat ke server
                    try {
                        const res = await fetch('{{ route('siam.assets.bulk-preview') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                quantity: this.quantity,
                                auto_serial: this.autoSerial,
                                auto_code: this.autoCode,
                                serial_prefix: this.serialPrefix,
                                serial_start: this.serialStart,
                                serial_pad: this.serialPad,
                                serial_list: this.serialList,
                                code_prefix: this.codePrefix,
                                code_start: this.codeStart,
                                code_pad: this.codePad,
                                code_list: this.codeList,
                            })
                        });
                        const data = await res.json();
                        const dupSerials = new Set(data.dup_serial || []);
                        const dupCodes = new Set(data.dup_code || []);

                        this.previewRows = this.previewRows.map(r => ({
                            ...r,
                            isDup: dupSerials.has(r.serial) || (r.code && dupCodes.has(r.code))
                        }));
                        this.hasDuplicates = this.previewRows.some(r => r.isDup);
                    } catch (e) {
                        // silent — tetap tampilkan preview lokal
                    }
                },
            }
        }
    </script>
@endpush
