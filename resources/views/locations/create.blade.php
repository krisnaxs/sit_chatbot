<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tambah Lokasi — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Tambah Lokasi" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('siam.locations.index') }}" class="hover:text-indigo-600 transition">Lokasi</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Tambah</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600
                            flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Tambah Lokasi</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Gedung, ruang, atau divisi baru</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-red-100 text-red-800 border border-red-200 text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('siam.locations.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Gedung <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="building" value="{{ old('building') }}" required
                            placeholder="Contoh: Gedung A"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   @error('building') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('building')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">Lantai</label>
                        <input type="text" name="floor" value="{{ old('floor') }}" placeholder="Contoh: Lt. 2"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   border-gray-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Ruang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="room" value="{{ old('room') }}" required
                            placeholder="Contoh: Ruang IT"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   @error('room') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('room')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 🆕 Divisi: DROPDOWN dari master departments --}}
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Divisi
                            <a href="{{ route('siam.departments.index') }}" target="_blank"
                                class="text-indigo-600 hover:underline text-[10px] font-semibold ml-1">
                                + Kelola
                            </a>
                        </label>
                        <select name="division"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   @error('division') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->name }}" @selected(old('division') === $d->name)>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">
                            Divisi diambil dari master Departemen.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan..."
                        class="w-full border rounded-xl px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               border-gray-200">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600
                                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Aktifkan</span>
                            <p class="text-xs text-gray-500">Lokasi bisa dipilih di form user/aset</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2
                               bg-gradient-to-br from-indigo-500 to-violet-600
                               hover:from-indigo-600 hover:to-violet-700
                               text-white px-5 py-2.5 rounded-xl
                               font-semibold text-sm
                               shadow-lg shadow-indigo-500/30
                               transition-all duration-300
                               hover:scale-105 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan
                    </button>
                    <a href="{{ route('siam.locations.index') }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300
                               text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                        Kembali
                    </a>
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
