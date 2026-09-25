<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Peminjaman Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Peminjaman Aset" />
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
                    <h1 class="text-2xl font-bold text-gray-800">Peminjaman Aset</h1>
                    <p class="text-sm text-gray-500">Aset yang dipinjam sementara oleh pegawai</p>
                </div>
                <a href="{{ route('siam.loans.create') }}"
                    class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium">
                    + Pinjam Aset
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.loans.index') }}"
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
                        <label class="block text-xs text-gray-500 mb-1">Peminjam</label>
                        <select name="user_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                            <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                            <option value="borrowed" @selected(request('status') === 'borrowed')>Dipinjam</option>
                            <option value="returned" @selected(request('status') === 'returned')>Dikembalikan</option>
                            <option value="overdue" @selected(request('status') === 'overdue')>Terlambat</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.loans.index') }}"
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
                                <th class="px-3 py-2 text-left">Peminjam</th>
                                <th class="px-3 py-2 text-left">Tujuan</th>
                                <th class="px-3 py-2 text-left">Tgl Pinjam</th>
                                <th class="px-3 py-2 text-left">Jatuh Tempo</th>
                                <th class="px-3 py-2 text-left">Dikembalikan</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($loans as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">
                                            {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ $loan->asset?->serial_number }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-gray-800">{{ $loan->user?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $loan->user?->position }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->purpose ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->loan_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->due_date?->format('d M Y') ?? '-' }}
                                        @if ($loan->is_overdue)
                                            <span
                                                class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-red-100 text-red-700 font-bold">
                                                TERLAMBAT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->returned_at?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColor = match ($loan->status) {
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'approved' => 'bg-blue-100 text-blue-700',
                                                'borrowed' => 'bg-indigo-100 text-indigo-700',
                                                'returned' => 'bg-green-100 text-green-700',
                                                'overdue' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $statusColor }}">
                                            {{ $loan->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($loan->returned_at === null)
                                            <form method="POST" action="{{ route('siam.loans.return', $loan) }}"
                                                class="inline" onsubmit="return confirm('Kembalikan aset ini?')">
                                                @csrf
                                                <input type="hidden" name="returned_at"
                                                    value="{{ now()->format('Y-m-d H:i:s') }}">
                                                <input type="hidden" name="condition_on_return"
                                                    value="{{ $loan->asset?->condition_percent ?? 100 }}">
                                                <button type="submit"
                                                    class="text-xs px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                                                    Kembalikan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                        Belum ada peminjaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t">
                    {{ $loans->links() }}
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
