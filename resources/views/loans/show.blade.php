@extends('layouts.app')

@section('title', 'Detail Peminjaman — SIAM')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <a href="{{ route('siam.loans.index') }}"
                class="text-sm text-indigo-600 hover:underline inline-flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke daftar peminjaman
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Detail Peminjaman</h1>
            <p class="text-sm text-gray-500">
                Aset: <strong>{{ $loan->asset?->brand }} {{ $loan->asset?->model }}</strong>
                — SN: <span class="font-mono">{{ $loan->asset?->serial_number }}</span>
            </p>
        </div>

        @if (session('success'))
            <div class="p-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 rounded-lg bg-red-100 text-red-800 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- INFO PENGAJUAN (kalau dari approval) --}}
        @if ($loan->assetRequest)
            <div class="bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100 rounded-2xl p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">
                                Peminjaman ini berasal dari pengajuan
                            </p>
                            <p class="font-mono font-bold text-indigo-700 text-lg mt-0.5">
                                {{ $loan->assetRequest->request_number }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Diajukan {{ $loan->assetRequest->created_at?->diffForHumans() }}
                                oleh {{ $loan->assetRequest->user?->name }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('requests.show', $loan->assetRequest) }}"
                        class="shrink-0 px-3 py-1.5 rounded-lg bg-white border border-indigo-200
                           text-xs font-semibold text-indigo-700 hover:bg-indigo-50 transition
                           inline-flex items-center gap-1">
                        Lihat Detail
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Peminjaman Manual</p>
                    <p class="text-sm text-gray-600">
                        Dicatat langsung tanpa pengajuan
                    </p>
                </div>
            </div>
        @endif

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- HEADER CARD --}}
            <div class="bg-gradient-to-br from-amber-500 to-yellow-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-white/70 uppercase tracking-wider font-semibold">Peminjaman Aset</p>
                            <h2 class="text-xl font-bold mt-0.5">
                                {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                            </h2>
                            <p class="text-sm text-white/80 font-mono mt-0.5">
                                SN: {{ $loan->asset?->serial_number }}
                            </p>
                        </div>
                    </div>

                    @php
                        $statusColor = match ($loan->status) {
                            'pending' => 'bg-white/20 text-white border-white/30',
                            'approved' => 'bg-white/20 text-white border-white/30',
                            'borrowed' => 'bg-white/20 text-white border-white/30',
                            'returned' => 'bg-white/20 text-white border-white/30',
                            'overdue' => 'bg-red-500/40 text-white border-red-300/50',
                            default => 'bg-white/20 text-white border-white/30',
                        };
                    @endphp
                    <span
                        class="shrink-0 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                             border {{ $statusColor }}">
                        {{ $loan->status_label }}
                    </span>
                </div>
            </div>

            {{-- BODY CARD --}}
            <div class="p-6 space-y-6">

                {{-- Grid Info Utama --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Peminjam</p>
                        <p class="font-semibold text-gray-800">{{ $loan->user?->name ?? '-' }}</p>
                        @if ($loan->user?->position)
                            <p class="text-xs text-gray-500">{{ $loan->user->position }}</p>
                        @endif
                        @if ($loan->user?->department)
                            <p class="text-xs text-gray-400">{{ $loan->user->department->name }}</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Aset</p>
                        <p class="font-semibold text-gray-800">
                            {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                        </p>
                        <p class="text-xs text-gray-500 font-mono">SN: {{ $loan->asset?->serial_number }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Tanggal Pinjam</p>
                        <p class="font-semibold text-gray-800">
                            {{ $loan->loan_date?->format('d M Y') ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $loan->loan_date?->format('H:i') }} WIB
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Jatuh Tempo</p>
                        <p class="font-semibold {{ $loan->is_overdue ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $loan->due_date?->format('d M Y') ?? '-' }}
                        </p>
                        @if ($loan->is_overdue && $loan->returned_at === null)
                            <p class="text-xs text-red-500 font-bold mt-0.5">
                                Terlambat {{ $loan->due_date?->diffForHumans() }}
                            </p>
                        @elseif ($loan->due_date && $loan->returned_at === null)
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $loan->due_date->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Dikembalikan</p>
                        @if ($loan->returned_at)
                            <p class="font-semibold text-green-700">
                                {{ $loan->returned_at->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $loan->returned_at->format('H:i') }} WIB
                            </p>
                        @else
                            <p class="text-sm text-gray-400 italic">Belum dikembalikan</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Disetujui Oleh</p>
                        <p class="font-semibold text-gray-800">{{ $loan->approvedBy?->name ?? '-' }}</p>
                    </div>

                </div>

                {{-- Kondisi --}}
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-3">
                        Kondisi Aset
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Saat Dipinjam</div>
                            <div class="flex items-center gap-2 mt-1">
                                @php $c1 = $loan->condition_on_loan ?? 0; @endphp
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full
                                    @if ($c1 >= 80) bg-green-500
                                    @elseif ($c1 >= 60) bg-amber-500
                                    @else bg-red-500 @endif"
                                        style="width: {{ $c1 }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700 w-12 text-right">{{ $c1 }}%</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Saat Dikembalikan</div>
                            @if ($loan->condition_on_return !== null)
                                @php $c2 = $loan->condition_on_return; @endphp
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full
                                        @if ($c2 >= 80) bg-green-500
                                        @elseif ($c2 >= 60) bg-amber-500
                                        @else bg-red-500 @endif"
                                            style="width: {{ $c2 }}%"></div>
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700 w-12 text-right">{{ $c2 }}%</span>
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic mt-1">Belum ada data</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tujuan --}}
                @if ($loan->purpose)
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Tujuan Peminjaman
                        </p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $loan->purpose }}</p>
                    </div>
                @endif

                {{-- Catatan --}}
                @if ($loan->notes)
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Catatan</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $loan->notes }}</p>
                    </div>
                @endif

            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('siam.loans.index') }}"
                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold text-sm
                   hover:bg-gray-50 transition inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>

            @if ($loan->asset)
                <a href="{{ route('siam.assets.show', $loan->asset) }}"
                    class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-sm
                       shadow-lg shadow-cyan-500/30 transition inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Lihat Detail Aset
                </a>
            @endif

            @if ($loan->returned_at === null)
                <button type="button"
                    onclick="document.getElementById('returnModal').classList.remove('hidden'); document.getElementById('returnModal').classList.add('flex')"
                    class="px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold text-sm
                       shadow-lg shadow-green-500/30 transition inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tandai Sudah Kembali
                </button>
            @endif

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('siam.loans.destroy', $loan) }}"
                    onsubmit="return confirm('Yakin ingin menghapus data peminjaman ini?')" class="ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm
                           shadow-lg shadow-red-500/30 transition inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus
                    </button>
                </form>
            @endif
        </div>

    </div>

    {{-- MODAL KONFIRMASI KEMBALIKAN --}}
    <div id="returnModal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            onclick="document.getElementById('returnModal').classList.add('hidden'); document.getElementById('returnModal').classList.remove('flex')">
        </div>

        <form method="POST" action="{{ route('siam.loans.return', $loan) }}"
            class="relative bg-white rounded-2xl shadow-2xl p-6 w-96">
            @csrf

            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Kembalikan Aset?</h3>
            <p class="text-sm text-gray-500 text-center mb-5">
                Tandai aset <strong class="text-gray-800">{{ $loan->asset?->brand }} {{ $loan->asset?->model }}</strong>
                sudah dikembalikan?
            </p>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    Kondisi Saat Dikembalikan (%)
                </label>
                <input type="number" name="condition_on_return" min="0" max="100"
                    value="{{ $loan->condition_on_loan ?? 100 }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1">Kondisi aset saat ini</p>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Contoh: ada lecet kecil di sudut..."
                    class="w-full border rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                       resize-none">{{ $loan->notes }}</textarea>
            </div>

            <input type="hidden" name="returned_at" value="{{ now()->format('Y-m-d H:i:s') }}">

            <div class="flex gap-2">
                <button type="button"
                    onclick="document.getElementById('returnModal').classList.add('hidden'); document.getElementById('returnModal').classList.remove('flex')"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                       text-gray-700 font-semibold transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 hover:bg-green-700
                       text-white font-semibold transition shadow-lg shadow-green-500/30">
                    Ya, Kembalikan
                </button>
            </div>
        </form>
    </div>
@endsection
