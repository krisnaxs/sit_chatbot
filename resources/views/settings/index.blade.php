@extends('layouts.app')

@section('title', 'Setting — SIAM')

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Pengaturan Sistem
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Konfigurasi channel notifikasi dan preferensi aplikasi
            </p>
        </div>

        {{-- GRID MENU --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- CARD: WhatsApp --}}
            <a href="{{ route('settings.whatsapp') }}"
                class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6
                  hover:shadow-lg hover:border-emerald-200 hover:-translate-y-1
                  transition-all duration-300">

                <div class="flex items-start justify-between mb-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600
                            flex items-center justify-center shadow-lg shadow-emerald-500/30
                            group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>

                    {{-- Status Badge --}}
                    @if ($status['whatsapp']['enabled'])
                        <span
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                 bg-emerald-100 text-emerald-700 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Aktif
                        </span>
                    @else
                        <span
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                 bg-gray-100 text-gray-500 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>

                <h3 class="font-bold text-gray-800 text-lg mb-1 group-hover:text-emerald-600 transition">
                    WhatsApp
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Pengiriman notifikasi via WhatsApp (Fonnte, Wablas, Meta Cloud API)
                </p>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs">
                        <span class="text-gray-400">Provider:</span>
                        <span class="font-semibold text-gray-700 uppercase">
                            {{ $status['whatsapp']['provider'] }}
                        </span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- CARD: Email --}}
            <a href="{{ route('settings.email') }}"
                class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6
                  hover:shadow-lg hover:border-indigo-200 hover:-translate-y-1
                  transition-all duration-300">

                <div class="flex items-start justify-between mb-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-400 to-violet-600
                            flex items-center justify-center shadow-lg shadow-indigo-500/30
                            group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    @if ($status['email']['enabled'])
                        <span
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                 bg-emerald-100 text-emerald-700 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Aktif
                        </span>
                    @else
                        <span
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                 bg-gray-100 text-gray-500 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>

                <h3 class="font-bold text-gray-800 text-lg mb-1 group-hover:text-indigo-600 transition">
                    Email
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Pengiriman email via SMTP (Gmail, Outlook, SendGrid, dll)
                </p>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs">
                        <span class="text-gray-400">Transport:</span>
                        <span class="font-semibold text-gray-700 uppercase">
                            {{ $status['email']['transport'] }}
                        </span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- CARD: Placeholder untuk setting berikutnya --}}
            <div
                class="bg-gradient-to-br from-gray-50 to-white rounded-2xl border-2 border-dashed border-gray-200 p-6
                    flex flex-col items-center justify-center text-center opacity-60">

                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>

                <h3 class="font-bold text-gray-400 text-lg mb-1">Segera Hadir</h3>
                <p class="text-xs text-gray-400">
                    Telegram, SMS Gateway, Push Notification
                </p>
            </div>
        </div>

        {{-- INFO CARD --}}
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-2xl p-5">
            <div class="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                    <strong class="block mb-1">Tentang Pengaturan Ini</strong>
                    <p class="text-blue-700">
                        Konfigurasi di halaman ini menggantikan setting <code class="px-1 bg-white rounded">.env</code>
                        secara otomatis. Perubahan langsung berlaku tanpa perlu restart server.
                        Notifikasi reminder pensiun akan dikirim melalui channel yang aktif.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
