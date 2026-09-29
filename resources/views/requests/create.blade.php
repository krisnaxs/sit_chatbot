<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Pengajuan — SIAM</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Buat Pengajuan" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

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
                        {{-- Peminjaman --}}
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

                        {{-- Konsumable --}}
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

                    {{-- Pilih Aset (loan) --}}
                    <div data-type-show="loan" class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Aset <span class="text-red-500">*</span>
                        </label>
                        <select name="asset_id"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(old('asset_id') == $a->id)>
                                    [{{ $a->serial_number }}] {{ $a->brand }} {{ $a->model }}
                                    — {{ $a->status === 'available' ? 'Tersedia' : 'Dipakai' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Konsumable --}}
                    <div data-type-show="consumable" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Pilih Konsumable <span class="text-red-500">*</span>
                            </label>
                            <select name="consumable_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($consumables as $c)
                                    <option value="{{ $c->id }}" @selected(old('consumable_id') == $c->id)>
                                        {{ $c->name }} (Stok: {{ $c->stock_available }} {{ $c->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah</label>
                            <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- TANGGAL --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-dashed">
                        {{-- Tanggal Dibutuhkan (semua tipe) --}}
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

                        {{-- Tanggal Kembali (hanya loan) --}}
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
    </div>

    <script>
        function pageLayout() {
            return {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                init() {
                    window.addEventListener('sidebar-toggled', e => this.collapsed = e.detail.collapsed);
                }
            }
        }

        // Toggle field sesuai tipe pengajuan
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

</body>

</html>
