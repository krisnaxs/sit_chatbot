@extends('layouts.app')

@section('title', 'Setting WhatsApp — SIAM')

@section('content')
    <div x-data="whatsappSetting()" class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Setting WhatsApp
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Konfigurasi pengiriman notifikasi WhatsApp untuk reminder pensiun
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
            {{-- FORM SETTING (2/3 lebar)                                     --}}
            {{-- ============================================================ --}}
            <div class="lg:col-span-2 space-y-6">
                <form action="{{ route('settings.whatsapp.update') }}" method="POST" id="form-setting">
                    @csrf
                    @method('PUT')

                    {{-- CARD: STATUS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Status Pengiriman
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    Kalau nonaktif, semua notifikasi WhatsApp akan dilewati
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

                    {{-- CARD: PROVIDER --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="mb-4">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                                Pilih Provider
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Pilih layanan pengiriman WhatsApp. Bisa diganti kapan saja.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($providers as $key => $p)
                                @php
                                    $isActive = $settings['provider'] === $key;
                                    $colorMap = [
                                        'secondary' => [
                                            'bg-gray-100',
                                            'text-gray-700',
                                            'border-gray-300',
                                            'bg-gray-50',
                                        ],
                                        'success' => [
                                            'bg-emerald-100',
                                            'text-emerald-700',
                                            'border-emerald-400',
                                            'bg-emerald-50',
                                        ],
                                        'warning' => [
                                            'bg-amber-100',
                                            'text-amber-700',
                                            'border-amber-400',
                                            'bg-amber-50',
                                        ],
                                        'primary' => [
                                            'bg-indigo-100',
                                            'text-indigo-700',
                                            'border-indigo-400',
                                            'bg-indigo-50',
                                        ],
                                    ];
                                    [$badgeBg, $badgeText, $borderActive, $bgActive] =
                                        $colorMap[$p['color']] ?? $colorMap['secondary'];
                                @endphp
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="provider" value="{{ $key }}" class="sr-only peer"
                                        @checked($isActive)>

                                    <div
                                        class="border-2 rounded-xl p-4 h-full transition-all duration-200
                                    {{ $isActive ? "$borderActive $bgActive shadow-md" : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">

                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <strong class="text-sm text-gray-800">{{ $p['label'] }}</strong>
                                            <span
                                                class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $badgeBg }} {{ $badgeText }} shrink-0">
                                                {{ $p['badge'] }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-500 leading-relaxed">{{ $p['desc'] }}</p>

                                        @if ($isActive)
                                            <div
                                                class="mt-3 flex items-center gap-1.5 text-xs font-semibold {{ $badgeText }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Sedang dipakai
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- CARD: KREDENSIAL --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Kredensial Provider
                        </h3>

                        <div class="space-y-5">

                            {{-- Token --}}
                            <div x-show="provider !== 'log'" x-cloak>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-gray-400"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Token / API Key
                                </label>
                                <div class="relative">
                                    <input :type="showToken ? 'text' : 'password'" name="token"
                                        value="{{ $settings['token'] }}" placeholder="Masukkan token dari provider"
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 pr-11 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <button type="button" @click="showToken = !showToken"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showToken" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showToken" x-cloak xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">
                                    <strong>Fonnte:</strong> Dashboard → Device → Token.
                                    <strong>Wablas:</strong> Menu API Key.
                                    <strong>Meta:</strong> System User Token.
                                </p>
                            </div>

                            {{-- Phone Number ID (khusus Meta) --}}
                            <div x-show="provider === 'meta'" x-cloak>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-gray-400"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Phone Number ID
                                </label>
                                <input type="text" name="phone_number_id" value="{{ $settings['phone_number_id'] }}"
                                    placeholder="Contoh: 123456789012345"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-2">Hanya untuk Meta Cloud API.</p>
                            </div>

                            {{-- Admin Fallback Numbers --}}
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-gray-400"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Nomor Admin (Fallback)
                                </label>
                                <input type="text" name="admin_numbers" value="{{ $settings['admin_numbers'] }}"
                                    placeholder="628123456789,628987654321"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-2">
                                    Pisahkan dengan koma. Dipakai kalau user admin belum punya nomor HP.
                                    Format otomatis jadi <code class="px-1 py-0.5 bg-gray-100 rounded">62xxx</code>.
                                </p>
                            </div>

                            {{-- Whitelist --}}
                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-amber-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Whitelist <span class="text-xs font-normal text-gray-400">(untuk testing)</span>
                                </label>
                                <input type="text" name="whitelist" value="{{ $settings['whitelist'] }}"
                                    placeholder="628123456789 (kosongkan untuk produksi)"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-2">
                                    Kalau diisi, <strong>hanya nomor ini</strong> yang akan dikirim WA.
                                    <span class="text-amber-600 font-semibold">Kosongkan saat production.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex gap-3">
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
            {{-- SIDEBAR (1/3 lebar)                                          --}}
            {{-- ============================================================ --}}
            <div class="space-y-6">

                {{-- INFO PROVIDER AKTIF --}}
                @php $activeProvider = $providers[$settings['provider']] ?? null; @endphp
                @if ($activeProvider)
                    <div
                        class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/30 p-6 text-white">
                        <div
                            class="flex items-center gap-2 mb-2 text-indigo-100 text-xs font-semibold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Provider Aktif
                        </div>
                        <h3 class="text-xl font-bold mb-1">{{ $activeProvider['label'] }}</h3>
                        <p class="text-sm text-indigo-100">{{ $activeProvider['desc'] }}</p>
                    </div>
                @endif

                {{-- TEST CONNECTION --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                        Test Koneksi
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Cek apakah token valid & device terkoneksi.
                    </p>
                    <form action="{{ route('settings.whatsapp.test') }}" method="POST">
                        @csrf
                        <button
                            class="w-full py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-semibold text-sm transition
                                   inline-flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Test Sekarang
                        </button>
                    </form>
                </div>

                {{-- TEST SEND --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Test Kirim Pesan
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Kirim pesan uji ke nomor Anda sendiri.
                    </p>
                    <form action="{{ route('settings.whatsapp.test-send') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nomor Tujuan</label>
                            <input type="text" name="test_number" placeholder="08123456789" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Isi Pesan</label>
                            <textarea name="test_message" rows="3" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                         focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                        </div>
                        <button
                            class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm transition
                                   inline-flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Kirim Test
                        </button>
                    </form>
                </div>

                {{-- TIPS --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 shrink-0 mt-0.5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <div class="text-xs text-amber-800">
                            <strong class="block mb-1">Tips Testing</strong>
                            Saat testing, isi <em>Whitelist</em> dengan nomor Anda sendiri.
                            Setelah yakin, kosongkan agar semua pegawai menerima notifikasi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function whatsappSetting() {
            return {
                showToken: false,
                provider: '{{ $settings['provider'] }}',

                init() {
                    document.querySelectorAll('input[name="provider"]').forEach(radio => {
                        radio.addEventListener('change', (e) => {
                            this.provider = e.target.value;
                        });
                    });
                }
            }
        }
    </script>
@endpush
