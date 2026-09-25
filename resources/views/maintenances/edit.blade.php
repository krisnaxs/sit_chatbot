<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Perbaikan — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Edit Perbaikan" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div>
                <a href="{{ route('siam.maintenances.show', $maintenance) }}"
                    class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke detail perbaikan
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Perbaikan</h1>
                <p class="text-sm text-gray-500">
                    Aset: <strong>{{ $maintenance->asset?->brand }} {{ $maintenance->asset?->model }}</strong>
                    — SN: <span class="font-mono">{{ $maintenance->asset?->serial_number }}</span>
                </p>
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

            <form method="POST" action="{{ route('siam.maintenances.update', $maintenance) }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf
                @method('PUT')

                {{-- Info Aset (read-only) --}}
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="text-xs text-gray-500">Aset</div>
                    <div class="font-semibold text-gray-800">
                        {{ $maintenance->asset?->brand }} {{ $maintenance->asset?->model }}
                    </div>
                    <div class="text-xs text-gray-500 font-mono">SN: {{ $maintenance->asset?->serial_number }}</div>
                </div>

                {{-- Tipe (read-only) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Perbaikan</label>
                    <input type="text" value="{{ $maintenance->type_label ?? ucfirst($maintenance->type) }}"
                        disabled
                        class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Tipe tidak bisa diubah setelah dibuat</p>
                </div>

                {{-- Issue (read-only) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Masalah / Kerusakan</label>
                    <input type="text" value="{{ $maintenance->issue }}" disabled
                        class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Masalah awal tidak bisa diubah</p>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                        <option value="open" @selected(old('status', $maintenance->status) === 'open')>Dibuka</option>
                        <option value="in_progress" @selected(old('status', $maintenance->status) === 'in_progress')>Proses</option>
                        <option value="done" @selected(old('status', $maintenance->status) === 'done')>Selesai</option>
                        <option value="cancelled" @selected(old('status', $maintenance->status) === 'cancelled')>Batal</option>
                    </select>
                </div>

                {{-- Action --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan</label>
                    <textarea name="action" rows="3" placeholder="Contoh: Ganti keyboard baru"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">{{ old('action', $maintenance->action) }}</textarea>
                </div>

                {{-- Vendor & Teknisi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor Service</label>
                        <select name="vendor_id"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                            <option value="">-- Internal (IT) --</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}" @selected(old('vendor_id', $maintenance->vendor_id) == $v->id)>
                                    {{ $v->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Bisa diganti kalau vendor berubah</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Teknisi</label>
                        <input type="text" name="technician" placeholder="Nama teknisi"
                            value="{{ old('technician', $maintenance->technician) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                {{-- Tanggal & Biaya --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date"
                            value="{{ old('end_date', $maintenance->end_date?->format('Y-m-d')) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Biaya (Rp)</label>
                        <input type="number" name="cost" min="0"
                            value="{{ old('cost', $maintenance->cost) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                {{-- Kondisi Setelah --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Setelah (%)</label>
                    <input type="number" name="condition_after" min="0" max="100"
                        value="{{ old('condition_after', $maintenance->condition_after) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    <p class="text-xs text-gray-400 mt-1">
                        Diisi kalau perbaikan sudah selesai. Akan update kondisi aset.
                    </p>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">{{ old('notes', $maintenance->notes) }}</textarea>
                </div>

                {{-- TOMBOL --}}
                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('siam.maintenances.show', $maintenance) }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
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
