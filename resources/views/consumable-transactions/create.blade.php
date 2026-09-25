<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Catat Transaksi — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Catat Transaksi" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <a href="{{ route('siam.consumable-transactions.index') }}"
                    class="text-sm text-indigo-600 hover:underline">← Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Catat Transaksi Konsumable</h1>
            </div>

            @if (session('error'))
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('siam.consumable-transactions.store') }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Item <span class="text-red-500">*</span>
                    </label>
                    <select name="consumable_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Item --</option>
                        @foreach ($consumables as $c)
                            <option value="{{ $c->id }}" @selected(old('consumable_id', $consumable?->id) == $c->id)>
                                {{ $c->name }} — Tersedia: {{ $c->stock_available }} {{ $c->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tipe Transaksi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="in" class="peer sr-only"
                                @checked(old('type') === 'in')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                                <div class="text-sm font-semibold text-green-700">Masuk</div>
                                <div class="text-xs text-gray-500">Restock</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="out" class="peer sr-only"
                                @checked(old('type', 'out') === 'out')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                                <div class="text-sm font-semibold text-red-700">Keluar</div>
                                <div class="text-xs text-gray-500">Kasih ke user</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="return" class="peer sr-only"
                                @checked(old('type') === 'return')>
                            <div
                                class="p-3 rounded-lg border-2 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 transition">
                                <div class="text-sm font-semibold text-blue-700">Kembali</div>
                                <div class="text-xs text-gray-500">Dikembalikan</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" required min="1" value="{{ old('quantity', 1) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="transaction_date" required
                            value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">User Penerima</label>
                    <select name="user_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih User --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                        <select name="location_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>{{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Aset (opsional)</label>
                        <select name="asset_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Tidak terkait aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(old('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan</label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}"
                        placeholder="Contoh: Mouse untuk laptop baru"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.consumable-transactions.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-sm font-medium">
                        Simpan Transaksi
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
