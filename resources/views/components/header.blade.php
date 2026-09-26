@props(['title' => 'Portal Aplikasi', 'placeholder' => 'Cari aplikasi...'])

<div class="sticky top-0 z-50 bg-white shadow-md px-6 py-3 flex items-center relative" x-data="headerApp()">

    {{-- LEFT: Hamburger + Logo --}}
    <div class="flex items-center gap-3 shrink-0">
        @auth
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                class="p-2 rounded-xl hover:bg-gray-100 transition" title="Toggle Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        @endauth

        <a href="/" class="flex items-center gap-2">
            <img src="{{ asset('images/plnip.png') }}" class="h-10" alt="Logo">
        </a>
    </div>

    {{-- CENTER: Tab Navigasi --}}
    @auth
        @php
            $currentTab = match (true) {
                request()->routeIs('users.*') => 'master',
                request()->routeIs('siam.departments.*') => 'master',
                request()->routeIs('siam.locations.*') => 'master',
                request()->routeIs('siam.vendors.*') => 'master',
                request()->routeIs('activity.*') => 'master',
                request()->routeIs('siam.*') => 'siam',
                request()->routeIs('dashboard') => 'chatbot', // 🆕 khusus dashboard chatbot
                request()->routeIs('knowledge.*', 'chat.*') => 'chatbot',
                default => 'portal',
            };

            $tabs = [
                'portal' => [
                    'label' => 'Portal',
                    'route' => route('portal'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                    'active' => 'bg-blue-50 text-blue-700 shadow-sm shadow-blue-500/10',
                ],
                'chatbot' => [
                    'label' => 'Chatbot',
                    'route' => route('dashboard'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                    'active' => 'bg-amber-50 text-amber-700 shadow-sm shadow-amber-500/10',
                ],
                'siam' => [
                    'label' => 'SIAM',
                    'route' => route('siam.dashboard'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                    'active' => 'bg-cyan-50 text-cyan-700 shadow-sm shadow-cyan-500/10',
                    'role' => ['admin', 'support'],
                ],
                'master' => [
                    'label' => 'Master',
                    'route' => route('users.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>',
                    'active' => 'bg-violet-50 text-violet-700 shadow-sm shadow-violet-500/10',
                    'role' => ['admin', 'support'],
                ],
            ];
        @endphp

        <nav class="hidden lg:flex items-center gap-1 ml-6">
            @foreach ($tabs as $key => $tab)
                @if (!isset($tab['role']) || auth()->user()->hasAnyRole($tab['role']))
                    <a href="{{ $tab['route'] }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl
                              text-sm font-semibold transition-all duration-200
                              {{ $currentTab === $key ? $tab['active'] : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            {!! $tab['icon'] !!}
                        </svg>
                        {{ $tab['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    @endauth

    {{-- Search (guest only, portal) --}}
    @guest
        @if (request()->routeIs('portal'))
            <div class="absolute left-1/2 -translate-x-1/2 w-auto text-center px-4">
                <input type="text" id="searchInput" placeholder="{{ $placeholder }}"
                    class="w-72 sm:w-96 px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           focus:bg-white transition">
            </div>
        @endif
    @endguest

    {{-- RIGHT: Search + User Menu / Login --}}
    <div class="flex-1 flex justify-end items-center gap-3">

        {{-- Search (auth, portal) --}}
        @auth
            @if (request()->routeIs('portal'))
                <div class="hidden sm:block">
                    <input type="text" id="searchInputAuth" placeholder="{{ $placeholder }}"
                        class="w-56 lg:w-72 px-4 py-2 rounded-xl border border-gray-200 bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               focus:bg-white transition text-sm">
                </div>
            @endif
        @endauth

        {{-- USER DROPDOWN (auth only) --}}
        @auth
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button type="button" @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-gray-100 transition">
                    <div class="text-right hidden lg:block">
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-gray-500 leading-tight">{{ auth()->user()->role_label }}</p>
                    </div>
                    <div
                        class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center text-white font-bold text-sm">
                        {{ auth()->user()->initial }}
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="hidden lg:block h-3 w-3 text-gray-400 transition-transform duration-200"
                        :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="userMenuOpen" x-cloak @click.away="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute right-0 top-full mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-[80]">

                    {{-- User Info (klik untuk buka profil) --}}
                    <a href="{{ route('users.show', auth()->user()) }}"
                        class="block px-4 py-4 bg-gradient-to-br from-blue-50 to-violet-50 border-b border-gray-100
                               hover:from-blue-100 hover:to-violet-100 transition group">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                        flex items-center justify-center text-white font-bold text-lg shrink-0">
                                {{ auth()->user()->initial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-gray-800 truncate group-hover:text-blue-700 transition">
                                    {{ auth()->user()->name }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                        @if (auth()->user()->isAdmin()) bg-violet-100 text-violet-700 border border-violet-200
                                        @elseif(auth()->user()->isSupport()) bg-blue-100 text-blue-700 border border-blue-200
                                        @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                        {{ auth()->user()->role }}
                                    </span>
                                    @if (auth()->user()->nip)
                                        <span class="text-[10px] text-gray-400">NIP: {{ auth()->user()->nip }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 group-hover:text-blue-600 transition shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    {{-- Menu --}}
                    <div class="p-2">
                        {{-- Info Detail --}}
                        @if (auth()->user()->department || auth()->user()->position)
                            <div class="px-3 py-2 text-xs text-gray-500 border-b border-gray-100 mb-1">
                                @if (auth()->user()->position)
                                    <div class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        {{ auth()->user()->position }}
                                    </div>
                                @endif
                                @if (auth()->user()->department)
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ auth()->user()->department->name }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- 🆕 Profil Saya (semua user) --}}
                        <a href="{{ route('users.show', auth()->user()) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                                  text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span>Profil Saya</span>
                        </a>

                        {{-- Kelola User (admin only) --}}
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('users.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                                      text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <span>Kelola User</span>
                            </a>
                        @endif

                        {{-- Logout --}}
                        <button type="button" @click="userMenuOpen = false; showLogoutConfirm = true"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                                   text-red-600 hover:bg-red-50 transition">
                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        @endauth

        {{-- Login Button (guest only) --}}
        @guest
            <button @click="showLogin = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-gradient-to-br from-blue-500 to-violet-600
                       hover:from-blue-600 hover:to-violet-700 text-white
                       shadow-md shadow-blue-500/30 hover:scale-105 active:scale-95
                       transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login Admin
            </button>
        @endguest
    </div>

    {{-- Modal Login --}}
    @guest
        <div x-show="showLogin" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showLogin = false">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Login Admin</h2>
                        <p class="text-xs text-gray-500">Masukkan kredensial Anda</p>
                    </div>
                </div>

                <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 max-h-0 mb-0"
                    x-transition:enter-end="opacity-100 translate-y-0 max-h-20 mb-3"
                    x-transition:leave="transition ease-in duration-200"
                    class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg
                           bg-red-50 border border-red-200 text-red-700 text-sm font-medium overflow-hidden"
                    style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="toast.message" class="flex-1"></span>
                </div>

                <form id="adminLoginForm" method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Email / Username / NIP
                        </label>
                        <input type="text" name="email" required
                            placeholder="admin atau admin@admin.com atau 10000001" autocomplete="username"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <p class="text-[11px] text-gray-400 mt-1">
                            Bisa pakai email, username, atau NIP.
                        </p>
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="showLogin = false"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                                   text-gray-700 font-semibold transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                                   hover:from-blue-600 hover:to-violet-700
                                   text-white font-semibold shadow-lg shadow-blue-500/30 transition-all duration-300">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endguest

    {{-- MODAL KONFIRMASI LOGOUT --}}
    @auth
        <div x-show="showLogoutConfirm"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showLogoutConfirm = false">
                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-500 text-center mb-6">Yakin ingin keluar dari akun ini?</p>
                <div class="flex gap-2">
                    <button type="button" @click="showLogoutConfirm = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmLogout()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Logout
                    </button>
                </div>
            </div>
        </div>
    @endauth

</div>

{{-- Chat Widget --}}
<x-chat-widget />

{{-- Form Logout (hidden) --}}
@auth
    <form id="headerLogoutForm" method="POST" action="{{ route('admin.logout') }}" class="hidden">
        @csrf
    </form>
@endauth

<script>
    function headerApp() {
        return {
            // Login modal (guest)
            showLogin: false,
            toast: {
                show: false,
                message: ''
            },

            // Logout confirm modal (auth)
            showLogoutConfirm: false,

            showToast(message) {
                this.toast.message = message;
                this.toast.show = true;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            },

            confirmLogout() {
                this.showLogoutConfirm = false;
                document.getElementById('headerLogoutForm').submit();
            },

            init() {
                const form = document.getElementById('adminLoginForm');
                if (!form) return;

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);

                    try {
                        const response = await fetch("{{ route('admin.login') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showLogin = false;
                            window.location.href = result.redirect;
                        } else {
                            this.showToast(result.message);
                        }
                    } catch (err) {
                        this.showToast('Terjadi kesalahan. Coba lagi.');
                    }
                });
            }
        }
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
