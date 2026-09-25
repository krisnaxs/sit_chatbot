<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Assign Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Assign Aset" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div>
                <a href="{{ route('siam.assignments.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Assign Aset ke User</h1>
                <p class="text-sm text-gray-500">Serah terima aset ke pegawai</p>
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

            {{-- FORM --}}
            <form method="POST" action="{{ route('siam.assignments.store') }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf

                {{-- ASET --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Aset <span class="text-red-500">*</span>
                    </label>
                    <select name="asset_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Aset --</option>
                        @foreach ($assets as $a)
                            <option value="{{ $a->id }}" @selected(old('asset_id', $asset?->id) == $a->id)>
                                {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                ({{ $a->category?->name }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hanya aset dengan status <b>Tersedia</b> yang muncul.</p>
                </div>

                {{-- USER --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Pegawai Penerima <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                                {{ $u->name }} — {{ $u->position ?? '-' }}
                                ({{ $u->department?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- LOKASI & DEPARTEMEN --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                        <select name="location_id"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>
                                    {{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen</label>
                        <select name="department_id"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- TANGGAL --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tanggal Diserahkan <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="assigned_at" required
                        value="{{ old('assigned_at', now()->format('Y-m-d\TH:i')) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- KONDISI --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Kondisi Saat Diserahkan (%)
                    </label>
                    <input type="number" name="condition_on_assign" min="0" max="100"
                        value="{{ old('condition_on_assign', 100) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- NOTES --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: BAST-2026-0001...">{{ old('notes') }}</textarea>
                </div>

                {{-- TOMBOL --}}
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.assignments.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        Assign Aset
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
