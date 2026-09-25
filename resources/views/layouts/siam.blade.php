<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIAM') — Sistem Informasi Aset Manajemen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800" x-data="{ sidebarOpen: true }">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside x-show="sidebarOpen" x-transition
            class="w-64 bg-slate-800 text-white flex-shrink-0 fixed h-full overflow-y-auto z-40">

            {{-- Logo --}}
            <div class="p-4 border-b border-slate-700">
                <a href="{{ route('apps.index') }}" class="flex items-center gap-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center font-bold">
                        S
                    </div>
                    <div>
                        <div class="font-bold">SIAM</div>
                        <div class="text-xs text-slate-400">Aset Manajemen</div>
                    </div>
                </a>
            </div>

            {{-- Menu --}}
            <nav class="p-3 space-y-1 text-sm">

                {{-- Dashboard --}}
                <a href="{{ route('apps.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    SIT Dashboard
                </a>

                <div class="pt-3 pb-1 text-xs uppercase text-slate-400 font-semibold px-3">Aset</div>

                {{-- Aset --}}
                <a href="{{ route('siam.assets.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.assets.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Daftar Aset
                </a>

                {{-- Assignment --}}
                <a href="{{ route('siam.assignments.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.assignments.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Serah Terima
                </a>

                {{-- Loan --}}
                <a href="{{ route('siam.loans.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.loans.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Peminjaman
                </a>

                {{-- Maintenance --}}
                <a href="{{ route('siam.maintenances.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.maintenances.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Perbaikan
                </a>

                {{-- Consumable --}}
                <a href="{{ route('siam.consumables.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.consumables.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Konsumable
                </a>

                {{-- Consumable Transactions --}}
                <a href="{{ route('siam.consumable-transactions.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.consumable-transactions.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transaksi Konsumable
                </a>

                <div class="pt-3 pb-1 text-xs uppercase text-slate-400 font-semibold px-3">Master Data</div>

                {{-- Categories --}}
                <a href="{{ route('siam.categories.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.categories.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Kategori
                </a>

                {{-- Vendors --}}
                <a href="{{ route('siam.vendors.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.vendors.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Vendor
                </a>

                {{-- Departments --}}
                <a href="{{ route('siam.departments.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.departments.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Departemen
                </a>

                {{-- Locations --}}
                <a href="{{ route('siam.locations.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition
                      {{ request()->routeIs('siam.locations.*') ? 'bg-slate-700 text-white' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lokasi
                </a>

            </nav>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-0'">

            {{-- TOP BAR --}}
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'SIAM')</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">
                            {{ auth()->user()->name }}
                            <span class="ml-1 px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-700">
                                {{ auth()->user()->role }}
                            </span>
                        </span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="text-sm text-red-600 hover:underline">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- PAGE CONTENT --}}
            <main class="p-6 flex-1">
                @if (session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-3 rounded bg-red-100 text-red-800 border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    <script>
        // Auto-submit filter saat berubah
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    @stack('scripts')
</body>

</html>
