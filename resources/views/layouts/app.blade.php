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
</head>

<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ sidebarCollapsed: false }"
    @sidebar-toggled.window="sidebarCollapsed = $event.detail.collapsed">

    {{-- SIDEBAR SIT (dengan menu kondisional per tab) --}}
    <x-sidebar />

    {{-- MAIN CONTENT --}}
    <div class="transition-all duration-300" :class="sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'">

        {{-- HEADER SIT (dengan tab navigasi + login + chat widget) --}}
        <x-header title="@yield('title', 'SIT')" />

        {{-- PAGE CONTENT --}}
        <main class="p-4 lg:p-6">
            {{-- Flash Messages --}}
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

    <script>
        // Auto-submit filter saat dropdown berubah
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    @stack('scripts')
</body>

</html>
