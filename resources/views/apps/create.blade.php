<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $app->exists ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="appForm()">

    <!-- SIDEBAR -->
    <x-sidebar />

    <!-- FORM CONTAINER -->
    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-20' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <h2 class="text-2xl font-bold mb-4">{{ $app->exists ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}</h2>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            @csrf
            @if ($method === 'PUT')
                @method('PUT')
            @endif

            <div class="mb-4">
                <label class="block font-medium mb-1">Nama Aplikasi</label>
                <input type="text" name="nama" class="w-full border rounded px-3 py-2"
                    value="{{ old('nama', $app->nama) }}" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">URL / Link</label>
                <input type="url" name="url" class="w-full border rounded px-3 py-2"
                    value="{{ old('url', $app->url) }}" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Upload Gambar</label>
                <input type="file" name="gambar" accept="image/*" class="w-full" onchange="previewImage(event)">
                @if ($app->gambar)
                    <img id="imagePreview" class="mt-2 max-h-40" src="{{ asset('storage/' . $app->gambar) }}"
                        alt="Preview Gambar">
                @else
                    <img id="imagePreview" class="mt-2 max-h-40" src="#" alt="Preview Gambar"
                        style="display: none;">
                @endif
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Slide</label>
                <input type="text" name="slide" class="w-full border rounded px-3 py-2"
                    value="{{ old('slide', $app->slide ?? 'Index') }}">
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Urutan</label>
                <input type="number" name="urutan" class="w-full border rounded px-3 py-2"
                    value="{{ old('urutan', $app->urutan ?? 1) }}">
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="form-checkbox"
                        {{ old('is_active', $app->is_active ?? true) ? 'checked' : '' }}>
                    <span class="ml-2">Aktif</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    {{ $app->exists ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('apps.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- 🔔 TOAST CONTAINER -->
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

        // 🆕 pageLayout — handle collapse sidebar
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
