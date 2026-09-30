<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SIT') — Sistem Informasi Terpadu</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{-- 🆕 WAJIB: sembunyikan elemen x-cloak sebelum Alpine init --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    {{-- SIDEBAR --}}
    <x-sidebar />

    {{-- HEADER — DI LUAR WRAPPER --}}
    <x-header title="@yield('title', 'SIT')" />

    {{-- MAIN WRAPPER --}}
    <div x-data :class="$store.sidebar.collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="transition-[margin] duration-300 ease-out">
        <main class="pt-[72px] px-4 pb-4 lg:px-6 lg:pb-6">
            @if (session('success'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- 🆕 WAJIB: render section modals (untuk requests/my & approval) --}}
    @yield('modals')

    <script>
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    @stack('scripts')
</body>

</html>
