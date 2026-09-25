<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Perbaikan Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Perbaikan Aset" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Perbaikan Aset</h1>
                    <p class="text-sm text-gray-500">History maintenance & service aset IT</p>
                </div>
                <a href="{{ route('siam.maintenances.create') }}"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium">
                    + Catat Perbaikan
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.maintenances.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-3">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Aset</label>
                        <select name="asset_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(request('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                        <select name="type" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="preventive" @selected(request('type') === 'preventive')>Preventif</option>
                            <option value="corrective" @selected(request('type') === 'corrective')>Perbaikan</option>
                            <option value="upgrade" @selected(request('type') === 'upgrade')>Upgrade</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="open" @selected(request('status') === 'open')>Dibuka</option>
                            <option value="in_progress" @selected(request('status') === 'in_progress')>Proses</option>
                            <option value="done" @selected(request('status') === 'done')>Selesai</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Batal</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.maintenances.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Aset</th>
                                <th class="px-3 py-2 text-left">Masalah</th>
                                <th class="px-3 py-2 text-left">Tindakan</th>
                                <th class="px-3 py-2 text-left">Vendor</th>
                                <th class="px-3 py-2 text-left">Teknisi</th>
                                <th class="px-3 py-2 text-left">Biaya</th>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($maintenances as $m)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">
                                            {{ $m->asset?->brand }} {{ $m->asset?->model }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ $m->asset?->serial_number }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $m->issue }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $m->action ?? '-' }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $m->vendor?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $m->technician ?? '-' }}</td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $m->cost ? 'Rp ' . number_format($m->cost, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $m->start_date?->format('d M Y') }}
                                        @if ($m->end_date)
                                            → {{ $m->end_date->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColor = match ($m->status) {
                                                'open' => 'bg-yellow-100 text-yellow-700',
                                                'in_progress' => 'bg-blue-100 text-blue-700',
                                                'done' => 'bg-green-100 text-green-700',
                                                'cancelled' => 'bg-gray-100 text-gray-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $statusColor }}">
                                            {{ $m->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('siam.maintenances.show', $m) }}"
                                            class="text-xs text-indigo-600 hover:underline">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-8 text-center text-gray-400">
                                        Belum ada perbaikan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t">
                    {{ $maintenances->links() }}
                </div>
            </div>

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
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

</body>

</html>
