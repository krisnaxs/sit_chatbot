<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $app->exists ? 'Edit Aplikasi' : 'Tambah Aplikasi' }} - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="appForm()">

    <!-- HEADER -->
    <x-header title="{{ $app->exists ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}" placeholder="Cari aplikasi..." />

    <!-- SIDEBAR -->
    <x-sidebar />

    <!-- CONTENT -->
    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-20' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('apps.index') }}" class="hover:text-blue-600 transition">Daftar Aplikasi</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">{{ $app->exists ? 'Edit' : 'Tambah' }}</span>
        </nav>

        <!-- CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            <!-- HEADER CARD -->
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div
                    class="w-12 h-12 rounded-xl
                            {{ $app->exists ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-amber-500/30' : 'bg-gradient-to-br from-blue-500 to-violet-600 shadow-blue-500/30' }}
                            flex items-center justify-center shadow-lg">
                    @if ($app->exists)
                        {{-- Ikon edit --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    @else
                        {{-- Ikon tambah --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ $app->exists ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $app->exists ? 'Perbarui informasi aplikasi' : 'Tambahkan aplikasi baru ke portal' }}
                    </p>
                </div>
            </div>

            <!-- FORM -->
            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Nama Aplikasi -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">
                        Nama Aplikasi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <input type="text" name="nama" value="{{ old('nama', $app->nama) }}"
                            placeholder="cth: ERP PROD"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('nama') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                   transition"
                            required>
                    </div>
                    @error('nama')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">
                        URL / Link <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <input type="url" name="url" value="{{ old('url', $app->url) }}"
                            placeholder="https://example.com"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('url') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                   transition"
                            required>
                    </div>
                    @error('url')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload Gambar -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">Upload Gambar</label>

                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- Tombol kustom --}}
                        <label
                            class="inline-flex items-center gap-2
                                   bg-gradient-to-br from-blue-500 to-violet-600
                                   hover:from-blue-600 hover:to-violet-700
                                   text-white px-4 py-2.5 rounded-xl
                                   cursor-pointer font-semibold text-sm
                                   shadow-lg shadow-blue-500/30
                                   transition-all duration-300
                                   hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v6m0 0l-3-3m3 3l3-3M12 6v6" />
                            </svg>
                            Pilih File
                            <input type="file" name="gambar" accept="image/*" class="hidden"
                                onchange="previewImage(event)">
                        </label>

                        {{-- Tombol hapus (kalau ada gambar) --}}
                        @if ($app->gambar)
                            <button type="button" onclick="removeImage()"
                                class="inline-flex items-center gap-2
                                       bg-red-50 hover:bg-red-100
                                       text-red-700 border border-red-200
                                       px-4 py-2.5 rounded-xl
                                       font-semibold text-sm
                                       transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mt-1.5">Format: JPG, PNG, WEBP · Maks: 2 MB</p>

                    <!-- Preview Gambar -->
                    <div class="mt-3">
                        <img id="imagePreview" class="max-h-48 rounded-xl border border-gray-200 shadow-sm"
                            src="{{ $app->gambar ? asset('storage/' . $app->gambar) : '#' }}"
                            style="{{ $app->gambar ? '' : 'display:none;' }}" alt="Preview Gambar">
                    </div>

                    @error('gambar')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slide -->
                <!-- Slide -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">
                        Slide <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <select name="slide"
                            class="w-full pl-10 pr-10 py-3 border rounded-xl appearance-none bg-white
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                   @error('slide') border-red-400 bg-red-50 @else border-gray-200 @enderror
                   transition cursor-pointer"
                            required>
                            <option value="" disabled {{ old('slide', $app->slide) ? '' : 'selected' }}>
                                -- Pilih Slide --
                            </option>
                            @php
                                $slideOptions = ['APP SURALAYA INFORMATION', 'PLN APP', 'SIS SURALAYA', 'UBP Suralaya'];
                                $selectedSlide = old('slide', $app->slide);
                            @endphp
                            @foreach ($slideOptions as $option)
                                <option value="{{ $option }}"
                                    {{ $selectedSlide === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Ikon panah dropdown --}}
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">
                        💡 Nama slide/kelompok tempat aplikasi ditampilkan di portal
                    </p>
                    @error('slide')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Urutan -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">Urutan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </div>
                        <input type="number" name="urutan" value="{{ old('urutan', $app->urutan ?? 1) }}"
                            min="1"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('urutan') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                   transition">
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">
                        💡 Urutan tampil aplikasi dalam satu slide (kecil = depan)
                    </p>
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-blue-600
                                   focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                   cursor-pointer transition"
                            {{ old('is_active', $app->is_active ?? true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Aktifkan aplikasi</span>
                            <p class="text-xs text-gray-500">Aplikasi akan tampil di portal publik</p>
                        </div>
                    </label>
                </div>

                <!-- BUTTONS -->
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2
                               bg-gradient-to-br from-blue-500 to-violet-600
                               hover:from-blue-600 hover:to-violet-700
                               text-white px-5 py-2.5 rounded-xl
                               font-semibold text-sm
                               shadow-lg shadow-blue-500/30
                               transition-all duration-300
                               hover:scale-105 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $app->exists ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ route('apps.index') }}"
                        class="px-5 py-2.5 rounded-xl
                               border border-gray-300
                               text-gray-700 font-semibold text-sm
                               hover:bg-gray-50
                               transition">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 🔔 TOAST ERROR VALIDASI -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="fixed top-24 right-6 z-[100] flex items-start gap-3
               min-w-[320px] max-w-md
               px-4 py-3 rounded-xl
               bg-red-600 text-white
               shadow-2xl shadow-red-500/50
               border border-red-400/40
               backdrop-blur-md"
        style="display: none;">

        <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mt-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div class="flex-1">
            <div class="text-sm font-bold mb-1">Validasi gagal</div>
            <ul class="text-xs space-y-0.5 list-disc list-inside opacity-90">
                <template x-for="(msg, i) in toast.messages" :key="i">
                    <li x-text="msg"></li>
                </template>
            </ul>
        </div>

        <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = 'block';
        }

        function removeImage() {
            const preview = document.getElementById('imagePreview');
            const fileInput = document.querySelector('input[name="gambar"]');

            fileInput.value = '';
            preview.src = '#';
            preview.style.display = 'none';
        }

        function appForm() {
            return {
                toast: {
                    show: false,
                    messages: [],
                },

                showToast(messages) {
                    this.toast.messages = Array.isArray(messages) ? messages : [messages];
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },

                init() {
                    @if ($errors->any())
                        this.showToast(@json($errors->all()));
                    @endif
                }
            }
        }

        // 🆕 pageLayout — handle sidebar collapse
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
