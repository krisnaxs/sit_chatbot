@extends('layouts.app')

@section('title', 'Aset Saya — SIAM')

@section('content')
    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Aset Saya</h1>
                <p class="text-sm text-gray-500">Daftar aset, peminjaman, dan konsumable yang Anda pegang</p>
            </div>
            <a href="{{ route('requests.create') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-lg shadow-indigo-500/30">
                + Buat Pengajuan
            </a>
        </div>

        {{--  INFO PENGAJUAN TERBARU --}}
        @if ($latestRequest)
            @php
                $statusConfig = [
                    'pending' => [
                        'bg' => 'bg-amber-50',
                        'border' => 'border-amber-200',
                        'icon_bg' => 'bg-amber-100',
                        'icon_color' => 'text-amber-600',
                        'title' => 'text-amber-800',
                        'text' => 'text-amber-700',
                        'label' => 'Menunggu Persetujuan',
                        'icon_svg' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    ],
                    'approved' => [
                        'bg' => 'bg-green-50',
                        'border' => 'border-green-200',
                        'icon_bg' => 'bg-green-100',
                        'icon_color' => 'text-green-600',
                        'title' => 'text-green-800',
                        'text' => 'text-green-700',
                        'label' => 'Pengajuan Disetujui',
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
                    ],
                    'rejected' => [
                        'bg' => 'bg-red-50',
                        'border' => 'border-red-200',
                        'icon_bg' => 'bg-red-100',
                        'icon_color' => 'text-red-600',
                        'title' => 'text-red-800',
                        'text' => 'text-red-700',
                        'label' => 'Pengajuan Ditolak',
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
                    ],
                    'cancelled' => [
                        'bg' => 'bg-gray-50',
                        'border' => 'border-gray-200',
                        'icon_bg' => 'bg-gray-100',
                        'icon_color' => 'text-gray-600',
                        'title' => 'text-gray-800',
                        'text' => 'text-gray-700',
                        'label' => 'Pengajuan Dibatalkan',
                        'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
                    ],
                ];
                $cfg = $statusConfig[$latestRequest->status] ?? $statusConfig['pending'];
            @endphp

            <div class="{{ $cfg['bg'] }} {{ $cfg['border'] }} border rounded-xl p-4 flex items-start gap-3">
                <div class="shrink-0 w-10 h-10 rounded-full {{ $cfg['icon_bg'] }} flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $cfg['icon_color'] }}" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        {!! $cfg['icon_svg'] !!}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-sm {{ $cfg['title'] }}">{{ $cfg['label'] }}</h3>
                        <span class="font-mono text-[11px] text-gray-500">{{ $latestRequest->request_number }}</span>
                    </div>
                    <p class="text-xs {{ $cfg['text'] }} leading-relaxed">
                        @if ($latestRequest->type === 'loan')
                            Peminjaman <strong>{{ $latestRequest->asset?->brand ?? '-' }}
                                {{ $latestRequest->asset?->model ?? '-' }}</strong>
                        @elseif ($latestRequest->type === 'consumable')
                            Permintaan <strong>{{ $latestRequest->consumable?->name ?? '-' }}</strong>
                            ({{ $latestRequest->quantity }} unit)
                        @endif
                        — {{ $latestRequest->updated_at->diffForHumans() }}

                        @if ($latestRequest->status === 'pending')
                            <br><span class="text-[11px]">Menunggu review dari admin.</span>
                        @elseif ($latestRequest->status === 'approved' && $latestRequest->admin_notes)
                            <br><span class="text-[11px]">Catatan: {{ $latestRequest->admin_notes }}</span>
                        @elseif ($latestRequest->status === 'rejected' && $latestRequest->rejection_reason)
                            <br><span class="text-[11px]">Alasan: {{ $latestRequest->rejection_reason }}</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('requests.my') }}"
                    class="shrink-0 text-xs font-semibold {{ $cfg['text'] }} hover:underline">
                    Lihat →
                </a>
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 font-medium">Aset Dipegang</p>
                <p class="text-2xl font-bold text-cyan-600 mt-1">{{ $summary['total_assets'] }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2">
                    <p class="text-xs text-gray-500 font-medium">Sedang Dipinjam</p>
                    @if ($summary['total_loans'] > 0)
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['total_loans'] }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 font-medium">Total Konsumable</p>
                <p class="text-2xl font-bold text-lime-600 mt-1">{{ $summary['total_consumables'] }}</p>
            </div>
        </div>

        {{-- ASET YANG DIPEGANG --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800">Aset yang Sedang Saya Pegang</h2>
                @if ($assets->isNotEmpty())
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-700 text-xs font-semibold">
                        {{ $assets->count() }} unit
                    </span>
                @endif
            </div>

            @if ($assets->isEmpty())
                <div class="p-10 text-center text-gray-400">
                    Anda belum memegang aset apapun.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Kode</th>
                                <th class="px-4 py-3 text-left font-semibold">Aset</th>
                                <th class="px-4 py-3 text-left font-semibold">Serial</th>
                                <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                                <th class="px-4 py-3 text-left font-semibold">Lokasi</th>
                                <th class="px-4 py-3 text-left font-semibold">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($assets as $a)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $a->asset_code }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $a->brand }} {{ $a->model }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $a->serial_number }}</td>
                                    <td class="px-4 py-3">{{ $a->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $a->currentLocation?->full_name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex px-2 py-1 rounded-md text-xs font-medium
                                        @if (($a->condition_percent ?? 0) >= 80) bg-green-100 text-green-700
                                        @elseif(($a->condition_percent ?? 0) >= 60) bg-amber-100 text-amber-700
                                        @else bg-red-100 text-red-700 @endif">
                                            {{ $a->condition_percent ?? '—' }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- PEMINJAMAN AKTIF --}}
        @if ($activeLoans->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="font-bold text-gray-800">Peminjaman Aktif</h2>
                        {{--  indikator nyala --}}
                        <span class="relative flex h-2.5 w-2.5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                        </span>
                    </div>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                        {{ $activeLoans->count() }} aktif
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Aset</th>
                                <th class="px-4 py-3 text-left font-semibold">Tgl Pinjam</th>
                                <th class="px-4 py-3 text-left font-semibold">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($activeLoans as $loan)
                                @php $isOverdue = $loan->due_date && $loan->due_date->isPast(); @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $loan->loan_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $loan->due_date?->format('d/m/Y') ?? '—' }}
                                        @if ($isOverdue)
                                            <span class="ml-1 text-red-600 font-bold">(Terlambat)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($isOverdue)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                Terlambat
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- KONSUMABLE YANG PERNAH DIAMBIL --}}
        @if ($consumables->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800">Riwayat Konsumable</h2>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-lime-100 text-lime-700 text-xs font-semibold">
                        {{ $consumables->count() }} transaksi
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Item</th>
                                <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                                <th class="px-4 py-3 text-left font-semibold">Lokasi</th>
                                <th class="px-4 py-3 text-left font-semibold">Tgl</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($consumables as $trx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $trx->consumable?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        {{ $trx->quantity }} {{ $trx->consumable?->unit ?? '' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $trx->location?->full_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $trx->transaction_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
