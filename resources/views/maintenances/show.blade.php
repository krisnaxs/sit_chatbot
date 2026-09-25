<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detail Perbaikan — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail Perbaikan" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">

            <div>
                <a href="{{ route('siam.maintenances.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Detail Perbaikan</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2">
                        <dt class="text-gray-500">Aset</dt>
                        <dd class="font-medium">{{ $maintenance->asset?->brand }} {{ $maintenance->asset?->model }}</dd>
                        <dd class="text-xs text-gray-500 font-mono">{{ $maintenance->asset?->serial_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tipe</dt>
                        <dd>{{ $maintenance->type_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>{{ $maintenance->status_label }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500">Masalah</dt>
                        <dd>{{ $maintenance->issue }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500">Tindakan</dt>
                        <dd>{{ $maintenance->action ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vendor</dt>
                        <dd>{{ $maintenance->vendor?->name ?? 'Internal' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Teknisi</dt>
                        <dd>{{ $maintenance->technician ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tanggal Mulai</dt>
                        <dd>{{ $maintenance->start_date?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tanggal Selesai</dt>
                        <dd>{{ $maintenance->end_date?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Biaya</dt>
                        <dd>{{ $maintenance->cost ? 'Rp ' . number_format($maintenance->cost, 0, ',', '.') : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Kondisi</dt>
                        <dd>{{ $maintenance->condition_before ?? '-' }}% → {{ $maintenance->condition_after ?? '-' }}%
                        </dd>
                    </div>
                    @if ($maintenance->notes)
                        <div class="col-span-2">
                            <dt class="text-gray-500">Catatan</dt>
                            <dd>{{ $maintenance->notes }}</dd>
                        </div>
                    @endif
                </dl>
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
    </script>

</body>

</html>
