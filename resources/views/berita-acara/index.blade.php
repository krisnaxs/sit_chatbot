@extends('layouts.app')

@section('title', 'Berita Acara — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('siam.assignments.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke Serah Terima
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Berita Acara</h1>
                <p class="text-sm text-gray-500">
                    Daftar BAST & BAP yang pernah dibuat sistem
                </p>
            </div>
        </div>

        {{-- STATISTIK RINGKAS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total BA</div>
                <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
                <div class="text-[10px] text-gray-400">dokumen</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Serah Terima</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['serah_terima'] }}</div>
                <div class="text-[10px] text-gray-400">BAST</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Pengembalian</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['pengembalian'] }}</div>
                <div class="text-[10px] text-gray-400">BAP</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Bulan Ini</div>
                <div class="text-2xl font-bold text-violet-600">{{ $stats['bulan_ini'] }}</div>
                <div class="text-[10px] text-gray-400">{{ now()->translatedFormat('F Y') }}</div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('siam.berita-acara.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">

                <div class="lg:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">
                        Cari (Nomor / SN / Merk / Nama)
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Contoh: BAST-2026-0001 atau T14-SN-0041"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jenis</label>
                    <select name="jenis" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="serah_terima" @selected(request('jenis') === 'serah_terima')>
                            Serah Terima
                        </option>
                        <option value="pengembalian" @selected(request('jenis') === 'pengembalian')>
                            Pengembalian
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('siam.berita-acara.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- TABEL --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Nomor BA</th>
                            <th class="px-3 py-2 text-left">Jenis</th>
                            <th class="px-3 py-2 text-left">Aset</th>
                            <th class="px-3 py-2 text-left">Pihak Pertama</th>
                            <th class="px-3 py-2 text-left">Pihak Kedua</th>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $ba)
                            @php
                                $jenisColor =
                                    $ba->jenis === 'serah_terima'
                                        ? 'bg-blue-100 text-blue-700 border border-blue-200'
                                        : 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                $jenisLabel = $ba->jenis === 'serah_terima' ? 'Serah Terima' : 'Pengembalian';
                                $jenisIcon = $ba->jenis === 'serah_terima' ? '📤' : '📥';
                            @endphp
                            <tr class="hover:bg-indigo-50 transition-colors">

                                {{-- Nomor BA --}}
                                <td class="px-3 py-3">
                                    <a href="{{ route('siam.berita-acara.show', $ba) }}"
                                        class="font-mono text-xs font-bold text-indigo-700 hover:underline">
                                        {{ $ba->nomor_ba }}
                                    </a>
                                </td>

                                {{-- Jenis --}}
                                <td class="px-3 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded font-semibold {{ $jenisColor }}">
                                        {{ $jenisIcon }} {{ $jenisLabel }}
                                    </span>
                                </td>

                                {{-- Aset --}}
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->merk }} {{ $ba->model }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $ba->serial_number }}
                                    </div>
                                    @if ($ba->kategori_aset)
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $ba->kategori_aset }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Pihak Pertama --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->pihakPertama?->name ?? '-' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $ba->pihak_pertama_jabatan ?? '-' }}
                                    </div>
                                </td>

                                {{-- Pihak Kedua --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-800">
                                        {{ $ba->pihakKedua?->name ?? '-' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $ba->pihak_kedua_jabatan ?? '-' }}
                                    </div>
                                </td>

                                {{-- Tanggal --}}
                                <td class="px-3 py-3 text-xs">
                                    <div class="font-medium text-gray-700">
                                        {{ $ba->tanggal_ba?->format('d M Y') ?? '-' }}
                                    </div>
                                    <div class="text-gray-400">
                                        {{ $ba->tempat_ba ?? '-' }}
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-3 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        {{-- Detail --}}
                                        <a href="{{ route('siam.berita-acara.show', $ba) }}" title="Lihat detail"
                                            class="p-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        {{-- Preview PDF --}}
                                        <a href="{{ route('siam.berita-acara.preview', $ba) }}" target="_blank"
                                            title="Preview PDF"
                                            class="p-1.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>

                                        {{-- Download PDF --}}
                                        <a href="{{ route('siam.berita-acara.download', $ba) }}" title="Download PDF"
                                            class="p-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 font-medium">Belum ada berita acara</p>
                                        <p class="text-xs text-gray-400">
                                            Berita acara akan otomatis dibuat saat Anda melakukan
                                            <strong>serah terima</strong> atau <strong>pengembalian</strong> aset.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>
@endpush
