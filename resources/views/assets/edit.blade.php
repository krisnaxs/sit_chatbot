@extends('layouts.app')

@section('title', 'Edit Aset — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="editAssetManager()">

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

            <form id="assetEditForm" method="POST" action="{{ route('siam.assets.update', $asset) }}"
                enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- IDENTITAS --}}
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

                    {{-- BRAND & MODEL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" x-data="assetTypeSelector()" x-init="init()">

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
                                @foreach ($assetTypes as $t)
                                    <option value="{{ $t->model }}" data-brand="{{ $t->brand }}"
                                        @selected(old('model', $asset->model) === $t->model)>
                                        {{ $t->model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                {{-- SPESIFIKASI --}}
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

                {{-- KEPEMILIKAN --}}
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

                {{-- STATUS --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Status & Kondisi
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status" required
                                @change="
                                    showMaintWarning = false;
                                    showLoanWarning = false;
                                    showAssignWarning = false;
                                    if ($event.target.value === 'maintenance' && oldStatus !== 'maintenance') {
                                        showMaintenanceModal = true;
                                        showMaintWarning = true;
                                    }
                                    if ($event.target.value === 'loaned' && oldStatus !== 'loaned') {
                                        showLoanModal = true;
                                        showLoanWarning = true;
                                    }
                                    if ($event.target.value === 'in_use' && oldStatus !== 'in_use') {
                                        showAssignModal = true;
                                        showAssignWarning = true;
                                    }
                                "
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="available" @selected(old('status', $asset->status) === 'available')>Tersedia</option>
                                <option value="in_use" @selected(old('status', $asset->status) === 'in_use')>Dipakai</option>
                                <option value="loaned" @selected(old('status', $asset->status) === 'loaned')>Dipinjam</option>
                                <option value="maintenance" @selected(old('status', $asset->status) === 'maintenance')>Perbaikan</option>
                                <option value="retired" @selected(old('status', $asset->status) === 'retired')>Pensiun</option>
                                <option value="lost" @selected(old('status', $asset->status) === 'lost')>Hilang</option>
                            </select>

                            <div x-show="showMaintWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="font-semibold text-amber-800 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Info
                                </p>
                                <p class="text-amber-700 text-xs mt-1 leading-relaxed">
                                    Status <strong>"Perbaikan"</strong> hanya menandai aset sedang diperbaiki.
                                    Isi detail perbaikan untuk mencatat issue, vendor, biaya, dll.
                                </p>
                                <button type="button" @click="showMaintenanceModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline hover:text-amber-900">
                                    + Isi Detail Perbaikan
                                </button>
                            </div>

                            <div x-show="showLoanWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="font-semibold text-amber-800 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Info
                                </p>
                                <p class="text-amber-700 text-xs mt-1 leading-relaxed">
                                    Status <strong>"Dipinjam"</strong> wajib isi data peminjaman.
                                </p>
                                <button type="button" @click="showLoanModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline hover:text-amber-900">
                                    + Isi Data Peminjaman
                                </button>
                            </div>

                            <div x-show="showAssignWarning" x-cloak x-transition
                                class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
                                <p class="font-semibold text-amber-800 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Info
                                </p>
                                <p class="text-amber-700 text-xs mt-1 leading-relaxed">
                                    Status <strong>"Dipakai"</strong> wajib isi data pegawai penerima.
                                </p>
                                <button type="button" @click="showAssignModal = true"
                                    class="mt-2 text-xs font-semibold text-amber-800 underline hover:text-amber-900">
                                    + Isi Data Pegawai
                                </button>
                            </div>
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

                {{-- FOTO --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                        Foto Aset
                    </h2>
                    @if ($asset->photo_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $asset->photo_path) }}" alt="{{ $asset->serial_number }}"
                                class="w-40 h-40 object-cover rounded-lg border">
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Biarkan kosong kalau tidak mau ganti.</p>
                </div>

                {{-- HIDDEN INPUTS --}}
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

                {{-- TOMBOL --}}
                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('siam.assets.show', $asset) }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="button" @click="confirmUpdate()"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium
                               shadow-lg shadow-indigo-500/30 transition">
                        Update Aset
                    </button>
                </div>

                {{-- MODAL PERBAIKAN --}}
                <div x-show="showMaintenanceModal" x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showMaintenanceModal = false">
                    </div>

                    <div
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-br from-orange-500 to-amber-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Catat Detail Perbaikan</h3>
                                        <p class="text-sm text-white/80">Isi detail untuk mencatat perbaikan aset ini</p>
                                    </div>
                                </div>
                                <button type="button" @click="showMaintenanceModal = false"
                                    class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="text-xs text-gray-500">Aset</div>
                                <div class="font-semibold text-gray-800">{{ $asset->brand }} {{ $asset->model }}</div>
                                <div class="text-xs text-gray-500 font-mono">SN: {{ $asset->serial_number }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Masalah / Kerusakan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="maintenance[issue]" x-bind:required="showMaintenanceModal"
                                    placeholder="Contoh: Keyboard tidak berfungsi"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan</label>
                                <textarea name="maintenance[action]" rows="2" placeholder="Contoh: Ganti keyboard baru"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor Service</label>
                                    <select name="maintenance[vendor_id]"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                        <option value="">-- Internal (IT) --</option>
                                        @foreach ($vendors ?? [] as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teknisi</label>
                                    <input type="text" name="maintenance[technician]" placeholder="Nama teknisi"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Tanggal Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="maintenance[start_date]"
                                        value="{{ now()->format('Y-m-d') }}" x-bind:required="showMaintenanceModal"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                                    <input type="date" name="maintenance[end_date]"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya (Rp)</label>
                                    <input type="number" name="maintenance[cost]" min="0" value="0"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Sebelum
                                        (%)</label>
                                    <input type="number" name="maintenance[condition_before]" min="0"
                                        max="100" value="{{ $asset->condition_percent ?? 100 }}"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Sesudah
                                        (%)</label>
                                    <input type="number" name="maintenance[condition_after]" min="0"
                                        max="100"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea name="maintenance[notes]" rows="2"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center shrink-0">
                            <p class="text-xs text-gray-500"><span class="text-red-500">*</span> Wajib diisi</p>
                            <div class="flex gap-2">
                                <button type="button" @click="showMaintenanceModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                                    Batal
                                </button>
                                <button type="button" @click="confirmMaintenance()"
                                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-orange-500/30">
                                    Simpan Perbaikan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL PINJAM --}}
                <div x-show="showLoanModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelLoan()"></div>

                    <div
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-br from-amber-500 to-orange-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                                <div class="text-xs text-amber-600">Aset yang akan dipinjam</div>
                                <div class="font-semibold text-gray-800">{{ $asset->brand }} {{ $asset->model }}</div>
                                <div class="text-xs text-gray-500 font-mono">SN: {{ $asset->serial_number }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Peminjam <span class="text-red-500">*</span>
                                </label>
                                <select x-model="loanForm.user_id" required
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
                                    <input type="datetime-local" x-model="loanForm.loan_date" required
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Jatuh Tempo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" x-model="loanForm.due_date" required
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan Peminjaman</label>
                                <input type="text" x-model="loanForm.purpose" placeholder="Contoh: Presentasi klien"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Saat Pinjam
                                    (%)</label>
                                <input type="number" x-model="loanForm.condition_on_loan" min="0" max="100"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea x-model="loanForm.notes" rows="2"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center shrink-0">
                            <p class="text-xs text-gray-500"><span class="text-red-500">*</span> Wajib diisi</p>
                            <div class="flex gap-2">
                                <button type="button" @click="cancelLoan()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                                    Batal
                                </button>
                                <button type="button" @click="confirmLoan()"
                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-amber-500/30">
                                    Simpan Peminjaman
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL ASSIGN --}}
                <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelAssign()"></div>

                    <div
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-br from-indigo-500 to-violet-600 px-6 py-5 text-white shrink-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Assign Aset ke User</h3>
                                        <p class="text-sm text-white/80">Isi data serah terima aset ke pegawai</p>
                                    </div>
                                </div>
                                <button type="button" @click="cancelAssign()"
                                    class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200">
                                <div class="text-xs text-indigo-600">Aset yang akan di-assign</div>
                                <div class="font-semibold text-gray-800">{{ $asset->brand }} {{ $asset->model }}</div>
                                <div class="text-xs text-gray-500 font-mono">SN: {{ $asset->serial_number }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Pegawai Penerima <span class="text-red-500">*</span>
                                </label>
                                <select x-model="assignForm.user_id" required
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
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Lokasi --</option>
                                        @foreach ($locations ?? [] as $l)
                                            <option value="{{ $l->id }}">{{ $l->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                                    <select x-model="assignForm.department_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach (\App\Models\Department::active()->orderBy('name')->get() as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Hostname Komputer</label>
                                <input type="text" x-model="assignForm.hostname" placeholder="Contoh: NB-IT-001"
                                    class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Tanggal Diserahkan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" x-model="assignForm.assigned_at" required
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Saat Diserahkan
                                        (%)</label>
                                    <input type="number" x-model="assignForm.condition_on_assign" min="0"
                                        max="100"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea x-model="assignForm.notes" rows="2" placeholder="Contoh: BAST-2026-0001"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center shrink-0">
                            <p class="text-xs text-gray-500"><span class="text-red-500">*</span> Wajib diisi</p>
                            <div class="flex gap-2">
                                <button type="button" @click="cancelAssign()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                                    Batal
                                </button>
                                <button type="button" @click="confirmAssign()"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-indigo-500/30">
                                    Simpan Assign
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        {{-- MODAL KONFIRMASI UPDATE --}}
        <div x-show="showConfirmUpdate" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showConfirmUpdate = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-96">
                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Update Aset?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Yakin ingin menyimpan perubahan pada aset
                    <strong class="text-gray-800">{{ $asset->serial_number }}</strong>?
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="showConfirmUpdate = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="submitUpdate()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition shadow-lg shadow-indigo-500/30">
                        Ya, Update
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI PERBAIKAN --}}
        <div x-show="showConfirmMaintenance" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showConfirmMaintenance = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-96">
                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Simpan Perbaikan?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Aset akan diupdate & perbaikan dicatat. Status berubah jadi
                    <strong class="text-orange-600">Perbaikan</strong>.
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="showConfirmMaintenance = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="submitMaintenance()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold transition shadow-lg shadow-orange-500/30">
                        Ya, Simpan
                    </button>
                </div>
            </div>
        </div>

        {{-- TOAST --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[130] flex items-center gap-3
                   min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">

            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function assetTypeSelector() {
            return {
                selectedBrand: @json(old('brand', $asset->brand)),
                selectedModel: @json(old('model', $asset->model)),
                allTypes: @json($assetTypes->map(fn($t) => ['id' => $t->id, 'brand' => $t->brand, 'model' => $t->model])),

                get filteredModels() {
                    if (!this.selectedBrand) return [];
                    return this.allTypes.filter(t => t.brand === this.selectedBrand);
                },

                onBrandChange() {
                    const stillValid = this.filteredModels.some(m => m.model === this.selectedModel);
                    if (!stillValid) this.selectedModel = '';
                    this.refreshModelOptions();
                },

                refreshModelOptions() {
                    const select = document.querySelector('select[name="model"]');
                    if (!select) return;
                    Array.from(select.options).forEach(opt => {
                        if (!opt.value) return;
                        const brand = opt.getAttribute('data-brand');
                        opt.hidden = this.selectedBrand && brand !== this.selectedBrand;
                    });
                },

                init() {
                    this.$nextTick(() => {
                        this.refreshModelOptions();
                    });
                }
            }
        }

        function editAssetManager() {
            return {
                showMaintenanceModal: false,
                showMaintWarning: false,
                showConfirmUpdate: false,
                showConfirmMaintenance: false,
                showLoanModal: false,
                showLoanWarning: false,
                showAssignModal: false,
                showAssignWarning: false,
                oldStatus: @json($asset->status),

                loanForm: {
                    user_id: '',
                    loan_date: '{{ now()->format('Y-m-d\TH:i') }}',
                    due_date: '{{ now()->addDays(3)->format('Y-m-d\TH:i') }}',
                    purpose: '',
                    condition_on_loan: {{ $asset->condition_percent ?? 100 }},
                    notes: '',
                },

                assignForm: {
                    user_id: '',
                    location_id: '',
                    department_id: '',
                    hostname: @json($asset->hostname ?? ''),
                    assigned_at: '{{ now()->format('Y-m-d\TH:i') }}',
                    condition_on_assign: {{ $asset->condition_percent ?? 100 }},
                    notes: '',
                },

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                confirmUpdate() {
                    const statusSelect = document.querySelector('select[name="status"]');
                    const newStatus = statusSelect.value;

                    if (newStatus === 'loaned' && this.oldStatus !== 'loaned' && !this.loanForm.user_id) {
                        this.showLoanWarning = true;
                        this.showLoanModal = true;
                        return;
                    }

                    if (newStatus === 'in_use' && this.oldStatus !== 'in_use' && !this.assignForm.user_id) {
                        this.showAssignWarning = true;
                        this.showAssignModal = true;
                        return;
                    }

                    this.showConfirmUpdate = true;
                },

                confirmAssign() {
                    if (!this.assignForm.user_id) {
                        this.showToast('Pegawai penerima wajib dipilih', 'error');
                        return;
                    }
                    if (!this.assignForm.assigned_at) {
                        this.showToast('Tanggal diserahkan wajib diisi', 'error');
                        return;
                    }
                    this.showAssignModal = false;
                    this.showAssignWarning = false;
                    this.showConfirmUpdate = true;
                },

                cancelAssign() {
                    this.showAssignModal = false;
                    this.showAssignWarning = false;
                    const statusSelect = document.querySelector('select[name="status"]');
                    if (statusSelect) statusSelect.value = this.oldStatus;
                    this.assignForm = {
                        user_id: '',
                        location_id: '',
                        department_id: '',
                        hostname: @json($asset->hostname ?? ''),
                        assigned_at: '{{ now()->format('Y-m-d\TH:i') }}',
                        condition_on_assign: {{ $asset->condition_percent ?? 100 }},
                        notes: '',
                    };
                },

                confirmLoan() {
                    if (!this.loanForm.user_id) {
                        this.showToast('Peminjam wajib dipilih', 'error');
                        return;
                    }
                    if (!this.loanForm.loan_date || !this.loanForm.due_date) {
                        this.showToast('Tanggal pinjam & jatuh tempo wajib diisi', 'error');
                        return;
                    }
                    this.showLoanModal = false;
                    this.showLoanWarning = false;
                    this.showConfirmUpdate = true;
                },

                cancelLoan() {
                    this.showLoanModal = false;
                    this.showLoanWarning = false;
                    const statusSelect = document.querySelector('select[name="status"]');
                    if (statusSelect) statusSelect.value = this.oldStatus;
                    this.loanForm = {
                        user_id: '',
                        loan_date: '{{ now()->format('Y-m-d\TH:i') }}',
                        due_date: '{{ now()->addDays(3)->format('Y-m-d\TH:i') }}',
                        purpose: '',
                        condition_on_loan: {{ $asset->condition_percent ?? 100 }},
                        notes: '',
                    };
                },

                confirmMaintenance() {
                    const form = document.getElementById('assetEditForm');
                    const issue = form.querySelector('input[name="maintenance[issue]"]');
                    const startDate = form.querySelector('input[name="maintenance[start_date]"]');

                    if (!issue.value.trim()) {
                        this.showToast('Masalah / kerusakan wajib diisi', 'error');
                        issue.focus();
                        return;
                    }
                    if (!startDate.value) {
                        this.showToast('Tanggal mulai wajib diisi', 'error');
                        startDate.focus();
                        return;
                    }
                    this.showMaintenanceModal = false;
                    this.showConfirmMaintenance = true;
                },

                submitUpdate() {
                    this.showConfirmUpdate = false;
                    document.getElementById('assetEditForm').submit();
                },

                submitMaintenance() {
                    this.showConfirmMaintenance = false;
                    document.getElementById('assetEditForm').submit();
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                init() {
                    @if (session('success'))
                        this.showToast(@json(session('success')), 'success');
                    @endif
                    @if (session('error'))
                        this.showToast(@json(session('error')), 'error');
                    @endif

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.showAssignModal) this.cancelAssign();
                            else if (this.showLoanModal) this.cancelLoan();
                            else if (this.showConfirmMaintenance) this.showConfirmMaintenance = false;
                            else if (this.showConfirmUpdate) this.showConfirmUpdate = false;
                            else if (this.showMaintenanceModal) this.showMaintenanceModal = false;
                        }
                    });
                }
            }
        }
    </script>
@endpush
