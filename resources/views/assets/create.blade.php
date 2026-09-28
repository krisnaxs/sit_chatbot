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

<body class="bg-slate-100 font-sans" x-data="createAssetManager()">

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
            <form id="assetCreateForm" method="POST" action="{{ route('siam.assets.store') }}"
                enctype="multipart/form-data"
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

                    {{-- BRAND & MODEL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" x-data="assetTypeSelector()"
                        x-init="init()">

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
                        </div>

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
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">RAM</label>
                            <input type="text" name="specification[ram]" value="{{ old('specification.ram') }}"
                                placeholder="Contoh: 16 GB DDR4" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Storage</label>
                            <input type="text" name="specification[storage]"
                                value="{{ old('specification.storage') }}" placeholder="Contoh: 512 GB NVMe SSD"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">GPU</label>
                            <input type="text" name="specification[gpu]" value="{{ old('specification.gpu') }}"
                                placeholder="Contoh: Intel Iris Xe" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">OS</label>
                            <input type="text" name="os" value="{{ old('os') }}"
                                placeholder="Contoh: Windows 11 Pro"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
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
                                @foreach ($vendors ?? [] as $v)
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
                                @change="
                                    showMaintWarning = false;
                                    showLoanWarning = false;
                                    showAssignWarning = false;
                                    if ($event.target.value === 'maintenance') {
                                        showMaintenanceModal = true;
                                        showMaintWarning = true;
                                    }
                                    if ($event.target.value === 'loaned') {
                                        showLoanModal = true;
                                        showLoanWarning = true;
                                    }
                                    if ($event.target.value === 'in_use') {
                                        showAssignModal = true;
                                        showAssignWarning = true;
                                    }
                                "
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="available" @selected(old('status', 'available') === 'available')>Tersedia</option>
                                <option value="in_use" @selected(old('status') === 'in_use')>Dipakai</option>
                                <option value="loaned" @selected(old('status') === 'loaned')>Dipinjam</option>
                                <option value="maintenance" @selected(old('status') === 'maintenance')>Perbaikan</option>
                                <option value="retired" @selected(old('status') === 'retired')>Pensiun</option>
                                <option value="lost" @selected(old('status') === 'lost')>Hilang</option>
                            </select>

                            {{-- Warning Maintenance --}}
                            <div x-show="showMaintWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="text-amber-700 text-xs">
                                    Status "Perbaikan" dipilih. Isi detail perbaikan di modal yang muncul.
                                </p>
                                <button type="button" @click="showMaintenanceModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline">
                                    + Buka Modal Perbaikan
                                </button>
                            </div>

                            {{-- Warning Loan --}}
                            <div x-show="showLoanWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="text-amber-700 text-xs">
                                    Status "Dipinjam" dipilih. Isi data peminjaman di modal yang muncul.
                                </p>
                                <button type="button" @click="showLoanModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline">
                                    + Buka Modal Pinjam
                                </button>
                            </div>

                            {{-- Warning Assign --}}
                            <div x-show="showAssignWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="text-amber-700 text-xs">
                                    Status "Dipakai" dipilih. Isi data pegawai penerima di modal yang muncul.
                                </p>
                                <button type="button" @click="showAssignModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline">
                                    + Buka Modal Assign
                                </button>
                            </div>
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
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
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

                {{-- ============ HIDDEN INPUTS ============ --}}
                <input type="hidden" name="loan[user_id]" :value="loanForm.user_id">
                <input type="hidden" name="loan[loan_date]" :value="loanForm.loan_date">
                <input type="hidden" name="loan[due_date]" :value="loanForm.due_date">
                <input type="hidden" name="loan[purpose]" :value="loanForm.purpose">
                <input type="hidden" name="loan[condition_on_loan]" :value="loanForm.condition_on_loan">
                <input type="hidden" name="loan[notes]" :value="loanForm.notes">

                <input type="hidden" name="assign[user_id]" :value="assignForm.user_id">
                <input type="hidden" name="assign[location_id]" :value="assignForm.location_id">
                <input type="hidden" name="assign[department_id]" :value="assignForm.department_id">
                <input type="hidden" name="assign[hostname]" :value="assignForm.hostname">
                <input type="hidden" name="assign[assigned_at]" :value="assignForm.assigned_at">
                <input type="hidden" name="assign[condition_on_assign]" :value="assignForm.condition_on_assign">
                <input type="hidden" name="assign[notes]" :value="assignForm.notes">

                {{-- ============ TOMBOL ============ --}}
                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('siam.assets.index') }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium
                               shadow-lg shadow-indigo-500/30 transition">
                        Simpan Aset
                    </button>
                </div>

                {{-- ============ MODAL PERBAIKAN (DI DALAM FORM) ============ --}}
                <div x-show="showMaintenanceModal" x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showMaintenanceModal = false">
                    </div>

                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                        <div class="bg-gradient-to-br from-orange-500 to-amber-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Catat Detail Perbaikan</h3>
                                        <p class="text-sm text-white/80">Isi detail untuk mencatat perbaikan aset ini
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="showMaintenanceModal = false"
                                    class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Masalah / Kerusakan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="maintenance[issue]"
                                    placeholder="Contoh: Keyboard tidak berfungsi"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan</label>
                                <textarea name="maintenance[action]" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor
                                        Service</label>
                                    <select name="maintenance[vendor_id]"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                        <option value="">-- Internal (IT) --</option>
                                        @foreach ($vendors ?? [] as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teknisi</label>
                                    <input type="text" name="maintenance[technician]"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                                    <input type="date" name="maintenance[start_date]"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal
                                        Selesai</label>
                                    <input type="date" name="maintenance[end_date]"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya (Rp)</label>
                                    <input type="number" name="maintenance[cost]" min="0" value="0"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea name="maintenance[notes]" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-end items-center gap-2 shrink-0">
                            <button type="button" @click="showMaintenanceModal = false"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                                Batal
                            </button>
                            <button type="button" @click="showMaintenanceModal = false"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium">
                                OK, Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============ MODAL PINJAM (DI DALAM FORM) ============ --}}
                <div x-show="showLoanModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelLoan()"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                        <div class="bg-gradient-to-br from-amber-500 to-orange-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Pinjam Aset</h3>
                                        <p class="text-sm text-white/80">Isi data peminjaman aset ini</p>
                                    </div>
                                </div>
                                <button type="button" @click="cancelLoan()"
                                    class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Peminjam <span class="text-red-500">*</span>
                                </label>
                                <select x-model="loanForm.user_id"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach ($users ?? [] as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} —
                                            {{ $u->position ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Tanggal Pinjam <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" x-model="loanForm.loan_date"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Jatuh Tempo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" x-model="loanForm.due_date"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan</label>
                                <input type="text" x-model="loanForm.purpose"
                                    class="w-full border rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                                <input type="number" x-model="loanForm.condition_on_loan" min="0"
                                    max="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea x-model="loanForm.notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-end items-center gap-2 shrink-0">
                            <button type="button" @click="cancelLoan()"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                                Batal
                            </button>
                            <button type="button" @click="confirmLoan()"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============ MODAL ASSIGN (DI DALAM FORM) ============ --}}
                <div x-show="showAssignModal" x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelAssign()"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                        <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Assign Aset ke User</h3>
                                        <p class="text-sm text-white/80">Isi data serah terima aset</p>
                                    </div>
                                </div>
                                <button type="button" @click="cancelAssign()"
                                    class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Pegawai Penerima <span class="text-red-500">*</span>
                                </label>
                                <select x-model="assignForm.user_id"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach ($users ?? [] as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} —
                                            {{ $u->position ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                                    <select x-model="assignForm.location_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                        <option value="">-- Pilih Lokasi --</option>
                                        @foreach ($locations ?? [] as $l)
                                            <option value="{{ $l->id }}">{{ $l->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                                    <select x-model="assignForm.department_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach (\App\Models\Department::active()->orderBy('name')->get() as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Hostname</label>
                                <input type="text" x-model="assignForm.hostname"
                                    class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Tanggal Diserahkan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" x-model="assignForm.assigned_at"
                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi (%)</label>
                                    <input type="number" x-model="assignForm.condition_on_assign" min="0"
                                        max="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea x-model="assignForm.notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-end items-center gap-2 shrink-0">
                            <button type="button" @click="cancelAssign()"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                                Batal
                            </button>
                            <button type="button" @click="confirmAssign()"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                                Simpan
                            </button>
                        </div>
                    </div>
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
                    const stillValid = this.filteredModels.some(m => m.model === this.selectedModel);
                    if (!stillValid) this.selectedModel = '';
                },

                init() {
                    if (this.selectedBrand) this.onBrandChange();
                }
            }
        }

        function createAssetManager() {
            return {
                showMaintenanceModal: false,
                showMaintWarning: false,
                showLoanModal: false,
                showLoanWarning: false,
                showAssignModal: false,
                showAssignWarning: false,

                loanForm: {
                    user_id: '',
                    loan_date: '{{ now()->format('Y-m-d\TH:i') }}',
                    due_date: '{{ now()->addDays(3)->format('Y-m-d\TH:i') }}',
                    purpose: '',
                    condition_on_loan: 100,
                    notes: '',
                },

                assignForm: {
                    user_id: '',
                    location_id: '',
                    department_id: '',
                    hostname: '',
                    assigned_at: '{{ now()->format('Y-m-d\TH:i') }}',
                    condition_on_assign: 100,
                    notes: '',
                },

                confirmLoan() {
                    if (!this.loanForm.user_id) {
                        alert('Peminjam wajib dipilih');
                        return;
                    }
                    this.showLoanModal = false;
                    // 🆕 Trigger submit form
                    document.getElementById('assetCreateForm').submit();
                },

                confirmAssign() {
                    if (!this.assignForm.user_id) {
                        alert('Pegawai penerima wajib dipilih');
                        return;
                    }
                    this.showAssignModal = false;
                    document.getElementById('assetCreateForm').submit();
                },

                cancelLoan() {
                    this.showLoanModal = false;
                    this.showLoanWarning = false;
                    const s = document.querySelector('select[name="status"]');
                    if (s) s.value = 'available';
                },

                cancelAssign() {
                    this.showAssignModal = false;
                    this.showAssignWarning = false;
                    const s = document.querySelector('select[name="status"]');
                    if (s) s.value = 'available';
                },
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

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
