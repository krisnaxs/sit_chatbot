@extends('layouts.app')

@section('title', 'Backup Data — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="backupManager()">

        <div class="max-w-5xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ route('siam.dashboard') }}" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke dashboard
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1">Backup Data</h1>
                    <p class="text-sm text-gray-500">
                        Backup database & file uploads. Simpan di lokasi aman (external drive / cloud).
                    </p>
                </div>
            </div>

            {{-- ALERT --}}
            @if (session('success'))
                <div class="p-4 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">
                    ✗ {{ session('error') }}
                </div>
            @endif

            {{-- DISK INFO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b">
                    Informasi Disk
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Total</div>
                        <div class="text-lg font-bold text-gray-800">{{ number_format($diskInfo['total'] / 1073741824, 2) }}
                            GB</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Terpakai</div>
                        <div class="text-lg font-bold text-amber-600">{{ number_format($diskInfo['used'] / 1073741824, 2) }}
                            GB</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Tersedia</div>
                        <div class="text-lg font-bold text-emerald-600">
                            {{ number_format($diskInfo['free'] / 1073741824, 2) }} GB</div>
                    </div>
                </div>
                <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $diskInfo['percent'] }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Terpakai {{ $diskInfo['percent'] }}%</p>
            </div>

            {{-- TOMBOL BACKUP --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Backup DB --}}
                <form method="POST" action="{{ route('siam.backup.create') }}">
                    @csrf
                    <input type="hidden" name="type" value="db">
                    <button type="submit"
                        class="w-full text-left p-5 rounded-2xl border-2 border-blue-200 bg-blue-50 hover:bg-blue-100 transition group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-blue-900">Backup Database Saja</div>
                                <div class="text-xs text-blue-700 mt-1">
                                    Hanya dump SQL (semua tabel). File kecil, cepat.
                                </div>
                                <div class="text-xs text-blue-500 mt-2 font-semibold">
                                    → Hasil: file .sql
                                </div>
                            </div>
                        </div>
                    </button>
                </form>

                {{-- Backup Full --}}
                <form method="POST" action="{{ route('siam.backup.create') }}" @submit="showFullConfirm = true">
                    @csrf
                    <input type="hidden" name="type" value="full">
                    <button type="submit"
                        class="w-full text-left p-5 rounded-2xl border-2 border-purple-200 bg-purple-50 hover:bg-purple-100 transition group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-600 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-purple-900">Backup Full (Rekomendasi)</div>
                                <div class="text-xs text-purple-700 mt-1">
                                    Database + semua file upload (foto aset) + .env + metadata.
                                </div>
                                <div class="text-xs text-purple-500 mt-2 font-semibold">
                                    → Hasil: file .zip
                                </div>
                            </div>
                        </div>
                    </button>
                </form>
            </div>

            {{-- RESTORE --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2
                    class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center">!</span>
                    Restore Database
                </h2>

                <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 mb-4">
                    <p class="text-xs text-amber-800">
                        <strong>⚠️ PERHATIAN:</strong> Restore akan menimpa SELURUH data yang ada di database.
                        Pastikan Anda sudah backup sebelum restore. Restore hanya mendukung file <code>.sql</code>
                        (dari backup DB saja). Untuk restore ZIP full, extract manual lalu import SQL.
                    </p>
                </div>

                <form method="POST" action="{{ route('siam.backup.restore') }}" enctype="multipart/form-data"
                    @submit="return confirm('Yakin restore? Semua data saat ini akan tertimpa!')">
                    @csrf
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[240px]">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                File SQL (.sql)
                            </label>
                            <input type="file" name="sql_file" accept=".sql,.txt" required
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-lg shadow-red-500/30">
                            Restore Sekarang
                        </button>
                    </div>
                </form>
            </div>

            {{-- LIST BACKUP --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">
                        Daftar Backup ({{ count($backups) }})
                    </h2>
                </div>

                @if (empty($backups))
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        Belum ada backup. Klik salah satu tombol di atas untuk memulai.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Nama File</th>
                                    <th class="px-4 py-2 text-left">Tipe</th>
                                    <th class="px-4 py-2 text-left">Ukuran</th>
                                    <th class="px-4 py-2 text-left">Dibuat</th>
                                    <th class="px-4 py-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($backups as $b)
                                    <tr class="hover:bg-indigo-50 transition">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-800">
                                            {{ $b['name'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($b['type'] === 'full')
                                                <span
                                                    class="px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-700 font-semibold">
                                                    FULL
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700 font-semibold">
                                                    DB
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">{{ $b['size_human'] }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-600">{{ $b['modified'] }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex gap-2">
                                                <a href="{{ $b['download_url'] }}"
                                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium inline-flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Download
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('siam.backup.destroy', $b['name']) }}"
                                                    onsubmit="return confirm('Hapus backup {{ $b['name'] }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-medium">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- INFO TIPS --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-blue-900 mb-2">💡 Tips Backup</h3>
                <ul class="text-xs text-blue-800 space-y-1 list-disc list-inside">
                    <li>Backup rutin minimal 1x seminggu, atau sebelum update besar.</li>
                    <li>Download file backup dan simpan di external drive / Google Drive.</li>
                    <li>File backup di server <strong>tidak aman</strong> jika server rusak — selalu simpan salinan di luar
                        server.</li>
                    <li>Untuk restore ZIP full: extract dulu, import <code>database.sql</code> ke MySQL, copy folder
                        <code>storage/app/public</code>.</li>
                    <li>Backup otomatis harian bisa dijadwalkan via cron + Laravel Scheduler (opsional).</li>
                </ul>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function backupManager() {
            return {
                // bisa ditambah progress indicator kalau perlu
            }
        }
    </script>
@endpush
