@extends('layouts.app')

@section('title', $beritaAcara->nomor_ba . ' — SIAM')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6" x-data="beritaAcaraShowManager()">

        {{-- HEADER --}}
        <div>
            <a href="{{ route('siam.berita-acara.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali ke daftar berita acara
            </a>
        </div>

        {{-- ALERT INFO --}}
        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200 flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-indigo-800">
                <div class="font-bold mb-1">Dokumen Resmi</div>
                <div class="text-xs">
                    Berita Acara ini dibuat otomatis oleh sistem SIAM dan merupakan dokumen resmi
                    yang sah. Untuk perubahan data, silakan hubungi admin.
                </div>
            </div>
        </div>

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- HEADER BA --}}
            @php
                $isSerahTerima = $beritaAcara->jenis === 'serah_terima';
                $headerGradient = $isSerahTerima ? 'from-blue-500 to-indigo-600' : 'from-emerald-500 to-teal-600';
                $jenisLabel = $isSerahTerima ? 'Berita Acara Serah Terima' : 'Berita Acara Pengembalian';
                $jenisIcon = $isSerahTerima ? '📤' : '📥';
            @endphp

            <div class="bg-gradient-to-br {{ $headerGradient }} px-6 py-6 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs uppercase tracking-wider text-white/70 font-semibold mb-1">
                            {{ $jenisLabel }}
                        </div>
                        <h1 class="text-2xl font-bold font-mono">{{ $beritaAcara->nomor_ba }}</h1>
                        <p class="text-sm text-white/80 mt-1">
                            {{ $beritaAcara->tanggal_ba?->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-3xl mb-1">{{ $jenisIcon }}</div>
                        <div class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold">
                            {{ strtoupper($beritaAcara->jenis) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- AKSI BUTTONS --}}
            <div class="px-6 py-4 bg-gray-50 border-b flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Dibuat {{ $beritaAcara->created_at?->diffForHumans() }}
                    @if ($beritaAcara->createdBy)
                        oleh <strong>{{ $beritaAcara->createdBy->name }}</strong>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    {{-- Preview --}}
                    <a href="{{ route('siam.berita-acara.preview', $beritaAcara) }}" target="_blank"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700
                               text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview PDF
                    </a>

                    {{-- Download --}}
                    <a href="{{ route('siam.berita-acara.download', $beritaAcara) }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700
                               text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>

                    @if (auth()->user()->isAdmin())
                        {{-- Regenerate --}}
                        <form method="POST" action="{{ route('siam.berita-acara.regenerate', $beritaAcara) }}"
                            @submit.prevent="confirmAction(
                                'Regenerate PDF?',
                                'File PDF lama akan ditimpa dengan versi terbaru.',
                                'Ya, Regenerate',
                                'warning',
                                $event
                            )">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600
                                       text-sm font-medium inline-flex items-center gap-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Regenerate
                            </button>
                        </form>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('siam.berita-acara.destroy', $beritaAcara) }}"
                            @submit.prevent="confirmAction(
                                'Hapus Berita Acara?',
                                'Dokumen ini akan dihapus permanen dan tidak bisa dikembalikan.',
                                'Ya, Hapus',
                                'danger',
                                $event
                            )">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200
                                       text-sm font-medium inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- INFO PIHAK --}}
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">

                {{-- PIHAK PERTAMA --}}
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Pihak Pertama</h2>
                        <span class="text-xs text-gray-400">
                            ({{ $isSerahTerima ? 'Yang Menyerahkan' : 'Yang Menerima Kembali' }})
                        </span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600
                                    text-white flex items-center justify-center text-lg font-bold shrink-0">
                            {{ strtoupper(substr($beritaAcara->pihakPertama?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-800">
                                {{ $beritaAcara->pihakPertama?->name ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $beritaAcara->pihak_pertama_jabatan ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-400 font-mono mt-1">
                                NIP: {{ $beritaAcara->pihak_pertama_nip ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PIHAK KEDUA --}}
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Pihak Kedua</h2>
                        <span class="text-xs text-gray-400">
                            ({{ $isSerahTerima ? 'Yang Menerima' : 'Yang Menyerahkan Kembali' }})
                        </span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600
                                    text-white flex items-center justify-center text-lg font-bold shrink-0">
                            {{ strtoupper(substr($beritaAcara->pihakKedua?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-800">
                                {{ $beritaAcara->pihakKedua?->name ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $beritaAcara->pihak_kedua_jabatan ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-400 font-mono mt-1">
                                NIP: {{ $beritaAcara->pihak_kedua_nip ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- INFO ASET --}}
            <div class="border-t border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                    <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Informasi Aset</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">

                    <div>
                        <div class="text-xs text-gray-500">Kategori</div>
                        <div class="font-semibold text-gray-800">{{ $beritaAcara->kategori_aset ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Merk</div>
                        <div class="font-semibold text-gray-800">{{ $beritaAcara->merk ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Model</div>
                        <div class="font-semibold text-gray-800">{{ $beritaAcara->model ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Serial Number</div>
                        <div class="font-mono text-sm text-indigo-700 font-semibold">
                            {{ $beritaAcara->serial_number ?? '-' }}
                        </div>
                    </div>

                    @if ($beritaAcara->hostname)
                        <div>
                            <div class="text-xs text-gray-500">Hostname</div>
                            <div class="font-mono text-sm text-gray-800">{{ $beritaAcara->hostname }}</div>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs text-gray-500">Jumlah</div>
                        <div class="font-semibold text-gray-800">{{ $beritaAcara->jumlah ?? 1 }} Unit</div>
                    </div>

                    @if ($beritaAcara->condition_percent !== null)
                        <div>
                            <div class="text-xs text-gray-500">
                                Kondisi {{ $isSerahTerima ? 'Saat Diserahkan' : 'Saat Dikembalikan' }}
                            </div>
                            @php
                                $c = $beritaAcara->condition_percent;
                                $cColor =
                                    $c >= 80
                                        ? 'text-emerald-600'
                                        : ($c >= 60
                                            ? 'text-yellow-600'
                                            : ($c >= 40
                                                ? 'text-orange-600'
                                                : 'text-red-600'));
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-current {{ $cColor }}"
                                        style="width: {{ $c }}%"></div>
                                </div>
                                <span class="text-sm font-semibold {{ $cColor }}">{{ $c }}%</span>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Detail Tambahan --}}
                @if (!empty($beritaAcara->detail_tambahan))
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-3">
                            Detail Spesifikasi
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            @foreach ($beritaAcara->detail_tambahan as $key => $val)
                                @if ($val)
                                    <div class="flex items-start gap-2">
                                        <dt class="text-gray-500 capitalize min-w-[100px]">
                                            {{ str_replace('_', ' ', $key) }}
                                        </dt>
                                        <dd class="text-gray-800 font-medium flex-1">
                                            {{ is_array($val) ? implode(', ', $val) : $val }}
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif

                {{-- Catatan --}}
                @if ($beritaAcara->notes)
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">
                            Catatan
                        </div>
                        <p
                            class="text-sm text-gray-700 italic bg-amber-50 border-l-2 border-amber-400
                                  py-2 px-3 rounded-r">
                            "{{ $beritaAcara->notes }}"
                        </p>
                    </div>
                @endif
            </div>

            {{-- LINK KE ASET & ASSIGNMENT --}}
            <div class="border-t border-gray-100 p-6 bg-gray-50">
                <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-3">
                    Referensi Terkait
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    @if ($beritaAcara->asset)
                        <a href="{{ route('siam.assets.show', $beritaAcara->asset) }}"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                   bg-white hover:border-indigo-300 hover:bg-indigo-50 transition group">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-gray-500">Detail Aset</div>
                                <div class="font-semibold text-sm text-gray-800 truncate">
                                    {{ $beritaAcara->asset->brand }} {{ $beritaAcara->asset->model }}
                                </div>
                                <div class="text-xs text-gray-500 font-mono truncate">
                                    {{ $beritaAcara->asset->serial_number }}
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 group-hover:text-indigo-600 transition shrink-0"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif

                    @if ($beritaAcara->assignment)
                        <a href="{{ route('siam.assignments.index', ['asset_id' => $beritaAcara->asset_id]) }}"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                   bg-white hover:border-emerald-300 hover:bg-emerald-50 transition group">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-gray-500">Record Serah Terima</div>
                                <div class="font-semibold text-sm text-gray-800">
                                    Assignment #{{ $beritaAcara->asset_assignment_id }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    oleh {{ $beritaAcara->assignment->user?->name ?? '-' }}
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 group-hover:text-emerald-600 transition shrink-0"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif

                </div>
            </div>

        </div>

        {{-- FOOTER INFO --}}
        <div class="text-center text-xs text-gray-400 py-4">
            Dokumen ini di-generate otomatis oleh Sistem SIAM
            @if ($beritaAcara->created_by)
                • Dibuat oleh User ID: {{ $beritaAcara->created_by }}
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL KONFIRMASI --}}
        {{-- ============================================================ --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeConfirm()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" @click.away="closeConfirm()">

                {{-- HEADER --}}
                <div class="px-6 py-5 text-white"
                    :class="{
                        'bg-gradient-to-br from-amber-500 to-orange-600': confirmType === 'warning',
                        'bg-gradient-to-br from-red-500 to-rose-600': confirmType === 'danger',
                        'bg-gradient-to-br from-indigo-500 to-violet-600': confirmType === 'info',
                    }">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                {{-- Warning --}}
                                <svg x-show="confirmType === 'warning'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>

                                {{-- Danger --}}
                                <svg x-show="confirmType === 'danger'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>

                                {{-- Info --}}
                                <svg x-show="confirmType === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold" x-text="confirmTitle"></h3>
                                <p class="text-sm text-white/80">Konfirmasi diperlukan</p>
                            </div>
                        </div>
                        <button type="button" @click="closeConfirm()"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="p-6">
                    <p class="text-sm text-gray-600 text-center" x-text="confirmMessage"></p>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                    <button type="button" @click="closeConfirm()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirm()"
                        class="px-4 py-2 text-white rounded-lg text-sm font-medium transition shadow-lg"
                        :class="{
                            'bg-amber-600 hover:bg-amber-700 shadow-amber-500/30': confirmType === 'warning',
                            'bg-red-600 hover:bg-red-700 shadow-red-500/30': confirmType === 'danger',
                            'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/30': confirmType === 'info',
                        }"
                        x-text="confirmButton"></button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TOAST --}}
        {{-- ============================================================ --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-amber-600 border-amber-400/40 shadow-amber-500/50': toast.type === 'warning',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[130] flex items-center gap-3
                   min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">

            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'error' || toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function beritaAcaraShowManager() {
            return {
                // ============================================================
                // STATE
                // ============================================================
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmButton: '',
                confirmType: 'info',
                pendingForm: null,
                pendingFlashMessage: '',

                // ============================================================
                // CONFIRM ACTION
                // ============================================================
                confirmAction(title, message, buttonText, type, event) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmButton = buttonText;
                    this.confirmType = type;
                    this.pendingForm = event.target.closest('form');

                    // Simpan pesan untuk flash
                    if (type === 'warning') {
                        this.pendingFlashMessage = 'PDF berhasil di-regenerate.';
                    } else if (type === 'danger') {
                        this.pendingFlashMessage = 'Berita acara berhasil dihapus.';
                    } else {
                        this.pendingFlashMessage = 'Berhasil diproses.';
                    }

                    this.showConfirmModal = true;
                },

                executeConfirm() {
                    if (!this.pendingForm) return;

                    // Set flash message SEBELUM submit
                    localStorage.setItem('flash_message', this.pendingFlashMessage);
                    localStorage.setItem('flash_type', 'success');

                    const form = this.pendingForm;
                    this.closeConfirm();
                    form.submit();
                },

                closeConfirm() {
                    this.showConfirmModal = false;
                    this.pendingForm = null;
                    this.pendingFlashMessage = '';
                },

                // ============================================================
                // TOAST
                // ============================================================
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;

                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                // ============================================================
                // INIT
                // ============================================================
                init() {
                    this.$nextTick(() => {
                        // Cek localStorage flash (dari halaman redirect)
                        const flashMsg = localStorage.getItem('flash_message');
                        const flashType = localStorage.getItem('flash_type') || 'success';

                        if (flashMsg) {
                            this.showToast(flashMsg, flashType);
                            localStorage.removeItem('flash_message');
                            localStorage.removeItem('flash_type');
                        }

                        // Cek session Laravel
                        @if (session('success'))
                            this.showToast(@json(session('success')), 'success');
                        @endif
                        @if (session('error'))
                            this.showToast(@json(session('error')), 'error');
                        @endif
                        @if (session('warning'))
                            this.showToast(@json(session('warning')), 'warning');
                        @endif
                        @if (session('info'))
                            this.showToast(@json(session('info')), 'info');
                        @endif
                    });

                    // ESC untuk close modal
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showConfirmModal) {
                            this.closeConfirm();
                        }
                    });
                }
            }
        }
    </script>
@endpush
