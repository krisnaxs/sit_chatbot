<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Konsumable — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Edit Konsumable" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <a href="{{ route('siam.consumables.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Konsumable</h1>
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

            <form method="POST" action="{{ route('siam.consumables.update', $consumable) }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nama Item <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required value="{{ old('name', $consumable->name) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $consumable->category_id) == $c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $consumable->brand) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Model</label>
                        <input type="text" name="model" value="{{ old('model', $consumable->model) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Satuan</label>
                        <input type="text" name="unit" required value="{{ old('unit', $consumable->unit) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Total Stok</label>
                        <input type="number" name="stock_total" required min="0"
                            value="{{ old('stock_total', $consumable->stock_total) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tersedia</label>
                        <input type="number" name="stock_available" required min="0"
                            value="{{ old('stock_available', $consumable->stock_available) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Min Stok</label>
                        <input type="number" name="stock_minimum" required min="0"
                            value="{{ old('stock_minimum', $consumable->stock_minimum) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Terakhir (Rp)</label>
                    <input type="number" name="last_price" min="0"
                        value="{{ old('last_price', $consumable->last_price) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $consumable->notes) }}</textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.consumables.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-lime-600 hover:bg-lime-700 text-white rounded-lg text-sm font-medium">
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
