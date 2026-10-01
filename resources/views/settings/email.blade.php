@extends('layouts.app')

@section('title', 'Setting Email — SIAM')

@section('content')
    <div x-data="emailSetting()" class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Setting Email
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Konfigurasi SMTP untuk pengiriman notifikasi email reminder pensiun
                </p>
            </div>
            <span
                class="px-3 py-1.5 rounded-xl text-sm font-semibold inline-flex items-center gap-2
            {{ $settings['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                <span
                    class="w-2 h-2 rounded-full {{ $settings['enabled'] ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                {{ $settings['enabled'] ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        {{-- NAV TAB --}}
        <div class="mb-6 flex gap-2 border-b border-gray-200">
            <a href="{{ route('settings.whatsapp') }}"
                class="px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    WhatsApp
                </span>
            </a>
            <a href="{{ route('settings.email') }}"
                class="px-4 py-2.5 text-sm font-semibold border-b-2 border-indigo-500 text-indigo-600 transition">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                </span>
            </a>
        </div>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="mb-5 p-3 rounded-xl bg-green-100 text-green-800 border border-green-200 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 p-3 rounded-xl bg-red-100 text-red-800 border border-red-200 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ============================================================ --}}
            {{-- FORM SETTING                                                 --}}
            {{-- ============================================================ --}}
            <div class="lg:col-span-2 space-y-6">
                <form action="{{ route('settings.email.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- CARD: STATUS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Status Pengiriman Email
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    Kalau nonaktif, semua notifikasi email akan dilewati
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enabled" value="1" class="sr-only peer"
                                    @checked($settings['enabled'])>
                                <div
                                    class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-indigo-500 peer-checked:to-violet-600">
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- CARD: TRANSPORT --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                            </svg>
                            Metode Pengiriman
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach ([
            'smtp' => ['SMTP', 'Kirim email via server SMTP', 'indigo'],
            'log' => ['Log Only', 'Tulis ke log, tidak kirim', 'secondary'],
            'array' => ['Array', 'Simpan di memory (testing)', 'secondary'],
        ] as $key => $info)
                                @php [$label, $desc, $color] = $info; @endphp
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="transport" value="{{ $key }}" class="sr-only peer"
                                        @checked($settings['transport'] === $key)>
                                    <div
                                        class="border-2 rounded-xl p-4 h-full transition-all
                                    {{ $settings['transport'] === $key
                                        ? ($color === 'indigo'
                                            ? 'border-indigo-400 bg-indigo-50'
                                            : 'border-gray-400 bg-gray-50')
                                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                        <div class="flex justify-between items-start gap-2 mb-2">
                                            <strong class="text-sm text-gray-800">{{ $label }}</strong>
                                            @if ($settings['transport'] === $key)
                                                <span class="text-indigo-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $desc }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- CARD: SMTP DETAIL --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6"
                        x-show="transport === 'smtp'" x-cloak>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Detail SMTP Server
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    SMTP Host <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="host" value="{{ $settings['host'] }}"
                                    placeholder="smtp.gmail.com"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-1.5">
                                    Contoh: smtp.gmail.com, smtp-mail.outlook.com, mail.perusahaan.com
                                </p>
                            </div>
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    Port <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="port" value="{{ $settings['port'] }}" placeholder="587"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-1.5">587 (TLS) / 465 (SSL)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" value="{{ $settings['username'] }}"
                                    placeholder="email@perusahaan.com" autocomplete="off"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    Password / App Password
                                    <span class="text-xs text-gray-400 font-normal">(kosongkan kalau tidak diubah)</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password"
                                        value="{{ $settings['password'] }}" placeholder="••••••••"
                                        autocomplete="new-password"
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 pr-11 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <button type="button" @click="showPass = !showPass"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showPass" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block font-semibold text-sm text-gray-700 mb-2">Encryption</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['tls' => 'TLS (Port 587)', 'ssl' => 'SSL (Port 465)', 'none' => 'None (Tidak disarankan)'] as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="encryption" value="{{ $key }}"
                                            class="sr-only peer" @checked($settings['encryption'] === $key)>
                                        <div
                                            class="px-4 py-2 rounded-xl border-2 transition text-sm
                                        {{ $settings['encryption'] === $key
                                            ? 'border-indigo-400 bg-indigo-50 text-indigo-700 font-semibold'
                                            : 'border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    From Address <span class="text-xs text-gray-400 font-normal">(pengirim)</span>
                                </label>
                                <input type="email" name="from_address" value="{{ $settings['from_address'] }}"
                                    placeholder="noreply@perusahaan.com"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    From Name <span class="text-xs text-gray-400 font-normal">(nama pengirim)</span>
                                </label>
                                <input type="text" name="from_name" value="{{ $settings['from_name'] }}"
                                    placeholder="SIMASET"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex flex-wrap gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2
                                   bg-gradient-to-br from-indigo-500 to-violet-600
                                   hover:from-indigo-600 hover:to-violet-700
                                   text-white px-5 py-2.5 rounded-xl
                                   font-semibold text-sm
                                   shadow-lg shadow-indigo-500/30
                                   transition-all duration-300
                                   hover:scale-105 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Setting
                        </button>
                        <a href="{{ route('siam.assets.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-gray-300
                              text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>

            {{-- ============================================================ --}}
            {{-- SIDEBAR                                                      --}}
            {{-- ============================================================ --}}
            <div class="space-y-6">

                {{-- CONFIG AKTIF --}}
                <div
                    class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/30 p-6 text-white">
                    <div
                        class="flex items-center gap-2 mb-3 text-indigo-100 text-xs font-semibold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        Config Aktif
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between gap-2 items-center">
                            <span class="text-indigo-100">Mailer</span>
                            <span class="font-semibold px-2 py-0.5 rounded bg-white/20">{{ $current['mailer'] }}</span>
                        </div>
                        <div class="flex justify-between gap-2 items-center">
                            <span class="text-indigo-100">Host</span>
                            <span class="font-mono text-xs truncate">{{ $current['host'] ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-2 items-center">
                            <span class="text-indigo-100">Port</span>
                            <span class="font-mono text-xs">{{ $current['port'] ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-2 items-center">
                            <span class="text-indigo-100">From</span>
                            <span class="font-mono text-xs truncate">{{ $current['from'] ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- TEST EMAIL --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Test Kirim Email
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Kirim email uji untuk memverifikasi konfigurasi SMTP.
                    </p>
                    <form action="{{ route('settings.email.test') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email Tujuan</label>
                            <input type="email" name="test_email" value="{{ auth()->user()->email }}"
                                placeholder="test@email.com" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <button
                            class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm transition
                                   inline-flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Kirim Test Email
                        </button>
                    </form>
                </div>

                {{-- TIPS GMAIL --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 shrink-0 mt-0.5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <div class="text-xs text-amber-800 space-y-2">
                            <strong class="block">Tips Setup Gmail:</strong>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Aktifkan <strong>2-Factor Authentication</strong> di akun Google</li>
                                <li>Buka <a href="https://myaccount.google.com/apppasswords" target="_blank"
                                        class="underline font-semibold">myaccount.google.com/apppasswords</a></li>
                                <li>Buat <strong>App Password</strong> baru</li>
                                <li>Copy 16 karakter → paste di field Password</li>
                            </ol>
                            <div class="pt-2 border-t border-amber-200">
                                <div><strong>Host:</strong> <code class="px-1 bg-white rounded">smtp.gmail.com</code></div>
                                <div><strong>Port:</strong> <code class="px-1 bg-white rounded">587</code> (TLS)</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TIPS SMTP LAIN --}}
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-blue-800 space-y-1.5">
                            <strong class="block">Contoh Provider Lain:</strong>
                            <div><strong>Outlook:</strong> smtp-mail.outlook.com:587 (TLS)</div>
                            <div><strong>Yahoo:</strong> smtp.mail.yahoo.com:587 (TLS)</div>
                            <div><strong>SendGrid:</strong> smtp.sendgrid.net:587 (TLS)</div>
                            <div><strong>Mailgun:</strong> smtp.mailgun.org:587 (TLS)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function emailSetting() {
            return {
                showPass: false,
                transport: '{{ $settings['transport'] }}',

                init() {
                    document.querySelectorAll('input[name="transport"]').forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            this.transport = e.target.value;
                        });
                    });
                }
            }
        }
    </script>
@endpush
