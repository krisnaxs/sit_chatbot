<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Kategori — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Edit Kategori" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <a href="{{ route('siam.categories.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Kategori</h1>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('siam.categories.update', $category) }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Kode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="code" required value="{{ old('code', $category->code) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $category->name) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_consumable" value="1" @checked(old('is_consumable', $category->is_consumable))>
                        <span>Konsumable</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                        <span>Aktif</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.categories.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        Update
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
