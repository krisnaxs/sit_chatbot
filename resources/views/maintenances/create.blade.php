<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Catat Perbaikan — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Catat Perbaikan" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

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

                {{-- ============ PILIH ASET ============ --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Aset <span class="text-red-500">*</span>
                    </label>

                    {{-- 🆕 SEARCH ASET --}}
                    <div class="flex gap-2 mb-2">
                        <div class="relative flex-1">
                            <input type="text" name="search" id="assetSearch" value="{{ request('search') }}"
                                placeholder="Cari SN / brand / model / nama pemakai..."
                                class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm
                                       focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button type="button" onclick="filterAset()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                            Cari
                        </button>
                        @if (request('search'))
                            <a href="{{ route('siam.maintenances.create', request()->only(['asset_id'])) }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                                Reset
                            </a>
                        @endif
                    </div>

                    {{-- Info hasil filter --}}
                    @if (request('search'))
                        <p class="text-xs text-gray-500 mb-2">
                            Menampilkan <strong>{{ $assets->count() }}</strong> aset untuk pencarian
                            "<strong>{{ request('search') }}</strong>"
                        </p>
                    @endif

                    {{-- DROPDOWN ASET --}}
                    <select name="asset_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Aset --</option>
                        @forelse ($assets as $a)
                            <option value="{{ $a->id }}" @selected(old('asset_id', $asset?->id) == $a->id)>
                                {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                @if ($a->currentUser)
                                    (Pemakai: {{ $a->currentUser->name }})
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada aset ditemukan</option>
                        @endforelse
                    </select>

                    @if (request('search') && $assets->isEmpty())
                        <p class="text-xs text-amber-600 mt-2">
                            ⚠️ Tidak ada aset yang cocok. Coba kata kunci lain atau reset filter.
                        </p>
                    @endif
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
                        <input type="text" name="technician" value="{{ old('technician') }}"
                            placeholder="Nama teknisi"
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

        // 🆕 Filter aset — reload halaman dengan query search
        function filterAset() {
            const search = document.getElementById('assetSearch').value;
            const url = new URL(window.location.href);

            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }

            window.location.href = url.toString();
        }

        // Enter di input search → trigger filter
        document.getElementById('assetSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterAset();
            }
        });
    </script>

</body>

</html>
