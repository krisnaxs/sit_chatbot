<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Dashboard" placeholder="Cari..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- TITLE -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan aktivitas chatbot & knowledge base
                </p>
            </div>
            <div class="text-sm text-gray-500">
                <span class="font-semibold">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 1. STATISTIK CARDS -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            {{-- Total Chat --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Chat</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalChat) }}</p>

                        @if (isset($trendHariIni))
                            <div class="flex items-center gap-1 mt-2">
                                @if ($trendHariIni >= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="text-xs font-semibold text-emerald-600">
                                        +{{ $trendHariIni }}% vs kemarin
                                    </span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-red-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="text-xs font-semibold text-red-600">
                                        {{ $trendHariIni }}% vs kemarin
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Knowledge --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Knowledge</p>
                        <p class="text-3xl font-bold text-violet-600 mt-1">{{ number_format($totalKnowledge) }}</p>

                        @if (isset($knowledgeWithFile))
                            <div class="flex items-center gap-1 mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="text-xs text-gray-500">
                                    {{ $knowledgeWithFile }} punya lampiran
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Chat Hari Ini --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Chat Hari Ini</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($chatHariIni) }}</p>

                        @if (isset($chatKemarin))
                            <div class="flex items-center gap-1 mt-2">
                                <span class="text-xs text-gray-500">
                                    Kemarin: <span class="font-semibold">{{ $chatKemarin }}</span>
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Chat Minggu Ini --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Chat Minggu Ini</p>
                        <p class="text-3xl font-bold text-amber-600 mt-1">{{ number_format($chatMingguIni) }}</p>

                        @if (isset($chatPerHari))
                            <div class="flex items-center gap-1 mt-2">
                                <span class="text-xs text-gray-500">
                                    Rata-rata: <span class="font-semibold">{{ round($chatPerHari, 1) }}/hari</span>
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- 2. GRAFIK 7 HARI -->
        <!-- ============================================================ -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Grafik Chat</h2>
                    <p class="text-xs text-gray-500 mt-0.5">7 hari terakhir</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    Jumlah Chat
                </div>
            </div>

            <div style="height: 280px;">
                <canvas id="chatChart"></canvas>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 3. GRID 2 KOLOM: TOP PERTANYAAN + CHAT TERBARU -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Top Pertanyaan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Pertanyaan Terpopuler</h2>
                        <p class="text-xs text-gray-500">10 pertanyaan paling sering ditanya</p>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($topPertanyaan ?? [] as $i => $item)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition">
                            <div
                                class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center
                                        text-xs font-bold text-gray-600 shrink-0">
                                {{ $i + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate font-medium">
                                    {{ $item->pesan }}
                                </p>
                            </div>
                            <div
                                class="shrink-0 px-2 py-0.5 rounded-lg bg-orange-50 text-orange-700
                                        border border-orange-100 text-[11px] font-bold">
                                {{ $item->total }}×
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-400 italic">Belum ada data</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Chat Terbaru --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Chat Terbaru</h2>
                        <p class="text-xs text-gray-500">8 aktivitas terakhir</p>
                    </div>
                </div>

                <div class="p-4 space-y-2 max-h-[420px] overflow-y-auto">
                    @forelse ($chatTerbaru ?? [] as $chat)
                        <div class="p-3 rounded-xl hover:bg-gray-50 transition border border-gray-100">
                            {{-- User Question --}}
                            <div class="flex items-start gap-2 mb-2">
                                <div
                                    class="shrink-0 w-5 h-5 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                            flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-700 leading-snug flex-1 line-clamp-2">
                                    {{ $chat->pesan }}
                                </p>
                            </div>

                            {{-- Bot Answer --}}
                            <div class="flex items-start gap-2 pl-7">
                                <p class="text-xs text-gray-500 leading-snug flex-1 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit($chat->jawaban, 100) }}
                                </p>
                            </div>

                            {{-- Meta --}}
                            <div class="flex items-center justify-between mt-2 pl-7">
                                <span class="text-[10px] text-gray-400">
                                    {{ \Carbon\Carbon::parse($chat->waktu)->diffForHumans() }}
                                </span>
                                @if ($chat->file_path)
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700
                                                border border-blue-100 font-semibold">
                                        📎 File
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-400 italic">Belum ada chat</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- 4. GRID 2 KOLOM: JAM SIBUK + KNOWLEDGE UNUSED -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Jam Sibuk --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Jam Sibuk</h2>
                        <p class="text-xs text-gray-500">5 jam paling aktif (30 hari terakhir)</p>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($jamSibuk ?? [] as $jam)
                        @php
                            $maxTotal = $jamSibuk->max('total') ?: 1;
                            $width = ($jam->total / $maxTotal) * 100;
                        @endphp
                        <div class="flex items-center gap-3 py-2">
                            <div class="w-14 text-sm font-bold text-gray-700 shrink-0">
                                {{ str_pad($jam->jam, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-400 to-rose-600 rounded-full"
                                    style="width: {{ $width }}%"></div>
                            </div>
                            <div class="w-12 text-right text-sm font-semibold text-gray-700 shrink-0">
                                {{ $jam->total }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-400 italic">Belum ada data</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Knowledge Tidak Terpakai --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Knowledge Tidak Terpakai</h2>
                        <p class="text-xs text-gray-500">Belum pernah muncul di chat</p>
                    </div>
                </div>

                <div class="p-4">
                    @forelse ($knowledgeUnused ?? [] as $kb)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition">
                            <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-700 truncate flex-1">
                                {{ $kb->kata_kunci }}
                            </p>
                            <a href="{{ route('knowledge.edit', $kb) }}"
                                class="shrink-0 text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                Edit
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-sm text-emerald-600 font-semibold">🎉 Semua knowledge sudah terpakai!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- 5. PEMBELAJARAN AI DARI PENDING (BARU)                       -->
        <!-- ============================================================ -->
        <div class="mb-6">

            {{-- Header Section --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600
                                flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            🧠 Pembelajaran AI dari Pending
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Seberapa banyak jawaban AI yang sudah di-review & disimpan ke knowledge
                        </p>
                    </div>
                </div>
                <a href="{{ route('knowledge.pending') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                           bg-white border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50
                           text-sm font-semibold text-gray-700 hover:text-emerald-700
                           transition shadow-sm">
                    Kelola Pending
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Progress Bar Tingkat Pembelajaran --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Tingkat Pembelajaran
                        </p>
                        <p class="text-4xl font-bold text-emerald-600 mt-1">
                            {{ $persenApproved ?? 0 }}%
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            dari {{ $pendingTotalAll ?? 0 }} total data pending
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-right">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Dipelajari</p>
                            <p class="text-xl font-bold text-emerald-600 mt-0.5">{{ $pendingApproved ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Pending</p>
                            <p class="text-xl font-bold text-amber-600 mt-0.5">{{ $pendingTotal ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Ditolak</p>
                            <p class="text-xl font-bold text-red-600 mt-0.5">{{ $pendingRejected ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                {{-- Stacked Progress Bar --}}
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden flex">
                    <div class="h-full bg-emerald-500 transition-all duration-500"
                        style="width: {{ $persenApproved ?? 0 }}%"></div>
                    <div class="h-full bg-amber-400 transition-all duration-500"
                        style="width: {{ $persenPending ?? 0 }}%"></div>
                    <div class="h-full bg-red-400 transition-all duration-500"
                        style="width: {{ $persenRejected ?? 0 }}%"></div>
                </div>

                {{-- Legend --}}
                <div class="flex flex-wrap gap-4 mt-3 text-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-600">Dipelajari ({{ $persenApproved ?? 0 }}%)</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="text-gray-600">Pending ({{ $persenPending ?? 0 }}%)</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="text-gray-600">Ditolak ({{ $persenRejected ?? 0 }}%)</span>
                    </span>
                </div>
            </div>

            {{-- 4 Kartu Statistik Pending --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

                {{-- Dipelajari --}}
                <div
                    class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                            hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Dipelajari
                        </p>
                    </div>
                    <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $pendingApproved ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total approved</p>
                </div>

                {{-- Pending --}}
                <div
                    class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                            hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Pending
                        </p>
                    </div>
                    <p class="text-3xl font-bold text-amber-600 mt-1">{{ $pendingTotal ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Menunggu review</p>
                </div>

                {{-- Dipelajari Hari Ini --}}
                <div
                    class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                            hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Hari Ini
                        </p>
                    </div>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $approvedHariIni ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Dipelajari hari ini</p>
                </div>

                {{-- Pending Baru Hari Ini --}}
                <div
                    class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                            hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Pending Baru
                        </p>
                    </div>
                    <p class="text-3xl font-bold text-rose-600 mt-1">{{ $pendingHariIni ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Masuk hari ini</p>
                </div>

            </div>

            {{-- Grid 2 Kolom: Top Pending + Chart --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Top Pending --}}
                @if (($topPending ?? collect())->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Pending Paling Sering Ditanya</h3>
                                <p class="text-xs text-gray-500">Belum dipelajari — prioritas untuk di-review</p>
                            </div>
                        </div>

                        <div class="p-4">
                            @foreach ($topPending as $p)
                                <div
                                    class="flex items-center justify-between gap-3 p-2.5
                                            rounded-xl hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-800 truncate font-medium">
                                            {{ $p->pesan_user }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            {{ $p->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-lg
                                                 bg-rose-50 text-rose-700 border border-rose-200">
                                        🔥 {{ $p->frequency }}×
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8
                                flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-900">Tidak ada pending</p>
                        <p class="text-sm text-gray-500 mt-1">Semua jawaban AI sudah di-review 🎉</p>
                    </div>
                @endif

                {{-- Chart Approval 7 Hari --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Approval 7 Hari Terakhir</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Jawaban AI yang disimpan ke knowledge</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            Dipelajari
                        </div>
                    </div>
                    <div style="height: 240px;">
                        <canvas id="chartPendingApproved"></canvas>
                    </div>
                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <p class="text-center text-gray-400 text-xs mt-8">
            SIS Assistant Dashboard · Last updated {{ now()->format('H:i') }}
        </p>

    </div>

    <script>
        // ===== Chart.js — Grafik Chat =====
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('chatChart');
            if (!ctx) return;

            const labels = @json($labels);
            const data = @json($data);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Chat',
                        data: data,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' chat';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                                stepSize: 1,
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                            }
                        }
                    }
                }
            });
        });

        // ===== Chart.js — Grafik Pending Approval =====
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('chartPendingApproved');
            if (!ctx) return;

            const labels = @json($pendingLabels ?? []);
            const data = @json($pendingData ?? []);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'AI Dipelajari',
                        data: data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' jawaban dipelajari';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                                stepSize: 1,
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                            }
                        }
                    }
                }
            });
        });

        // ===== Sidebar collapse =====
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
