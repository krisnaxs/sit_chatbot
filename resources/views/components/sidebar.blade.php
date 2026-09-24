@auth
    <div x-data="sidebarApp()" @toggle-sidebar.window="window.innerWidth >= 1024 ? toggleCollapse() : toggle()"
        class="fixed inset-0 z-[45] pointer-events-none">

        {{-- OVERLAY (mobile) --}}
        <div x-show="open" @click="close()"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 pointer-events-auto
                   lg:hidden transition-opacity duration-300"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
        </div>

        {{-- SIDEBAR --}}
        <aside
            :class="{
                'translate-x-0': open,
                '-translate-x-full lg:translate-x-0': !open,
                'lg:w-64': !collapsed,
                'lg:w-16': collapsed
            }"
            class="fixed top-0 left-0 bottom-0 w-64 z-50
                   bg-white border-r border-gray-200 shadow-xl
                   flex flex-col
                   transform transition-all duration-300 ease-out
                   pointer-events-auto">

            {{-- HEADER SIDEBAR --}}
            <div :class="collapsed ? 'lg:justify-center lg:px-0' : 'justify-between'"
                class="flex items-center px-4 py-3.5 border-b border-gray-100 gap-2 shrink-0">

                {{-- Logo (disembunyikan saat mini di desktop) --}}
                <a href="/" :class="collapsed ? 'lg:hidden' : 'flex items-center gap-2'">
                    <img src="{{ asset('images/plnip.png') }}" class="h-8 shrink-0" alt="Logo">
                    <span class="font-bold text-gray-800 text-sm whitespace-nowrap">SIT Admin</span>
                </a>

                {{-- Logo icon only saat mini (desktop) --}}
                <a href="/" :class="collapsed ? 'lg:block hidden' : 'hidden'">
                    <img src="{{ asset('images/plnip.png') }}" class="h-7" alt="Logo">
                </a>

                {{-- Collapse toggle (desktop) --}}
                <button type="button" @click="toggleCollapse()"
                    class="hidden lg:flex items-center justify-center
                           w-7 h-7 rounded-lg
                           hover:bg-gray-100 transition shrink-0"
                    :title="collapsed ? 'Perluas sidebar' : 'Perkecil sidebar'">
                    <svg xmlns="http://www.w3.org/2000/svg" :class="collapsed ? 'rotate-180' : ''"
                        class="h-4 w-4 text-gray-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                {{-- Close button (mobile) --}}
                <button type="button" @click="close()"
                    class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">

                {{-- SECTION: Utama --}}
                <p :class="collapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 pt-2 pb-1 whitespace-nowrap">
                    Utama
                </p>

                {{-- 🏠 Portal --}}
                <a href="{{ route('portal') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-sm font-semibold transition-all duration-200
                           {{ request()->routeIs('portal')
                               ? 'bg-blue-50 text-blue-700 shadow-sm shadow-blue-500/10'
                               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                 {{ request()->routeIs('portal') ? 'bg-blue-100' : 'bg-gray-100' }}
                                 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </span>
                    <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Portal</span>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            Portal
                        </span>
                    </template>
                </a>

                {{-- Aplikasi --}}
                <a href="{{ route('apps.index') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-sm font-semibold transition-all duration-200
                           {{ request()->routeIs('apps.*')
                               ? 'bg-violet-50 text-violet-700 shadow-sm shadow-violet-500/10'
                               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                 {{ request()->routeIs('apps.*') ? 'bg-violet-100' : 'bg-gray-100' }}
                                 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </span>
                    <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Aplikasi</span>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            Aplikasi
                        </span>
                    </template>
                </a>

                {{-- Knowledge --}}
                <a href="{{ route('knowledge.index') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-sm font-semibold transition-all duration-200
                           {{ request()->routeIs('knowledge.index') ||
                           request()->routeIs('knowledge.create') ||
                           request()->routeIs('knowledge.edit')
                               ? 'bg-amber-50 text-amber-700 shadow-sm shadow-amber-500/10'
                               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                 {{ request()->routeIs('knowledge.index') || request()->routeIs('knowledge.create') || request()->routeIs('knowledge.edit') ? 'bg-amber-100' : 'bg-gray-100' }}
                                 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Knowledge</span>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            Knowledge
                        </span>
                    </template>
                </a>

                {{-- 🆕 Pending AI — HANYA ADMIN & SUPPORT --}}
                @if (Route::has('knowledge.pending') &&
                        auth()->user()->hasAnyRole(['admin', 'support']))
                    @php
                        $pendingCount = \App\Models\PendingKnowledge::where('status', 'pending')->count();
                    @endphp

                    <a href="{{ route('knowledge.pending') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('knowledge.pending*')
                                   ? 'bg-orange-50 text-orange-700 shadow-sm shadow-orange-500/10'
                                   : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span
                            class="relative w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                     {{ request()->routeIs('knowledge.pending*') ? 'bg-orange-100' : 'bg-gray-100' }}
                                     transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>

                            {{-- 🆕 Badge counter --}}
                            @if ($pendingCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full
                                             bg-rose-500 text-white text-[9px] font-bold
                                             flex items-center justify-center
                                             ring-2 ring-white">
                                    {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                                </span>
                            @endif
                        </span>
                        <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">
                            Pending AI
                            @if ($pendingCount > 0)
                                <span class="ml-1 text-[10px] text-rose-500 font-bold">({{ $pendingCount }})</span>
                            @endif
                        </span>

                        <template x-if="collapsed">
                            <span
                                class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                         bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                         pointer-events-none">
                                Pending AI ({{ $pendingCount }})
                            </span>
                        </template>
                    </a>
                @endif

                {{-- Chatbot --}}
                <a href="{{ route('chat.index') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-sm font-semibold transition-all duration-200
                           {{ request()->routeIs('chat.*')
                               ? 'bg-cyan-50 text-cyan-700 shadow-sm shadow-cyan-500/10'
                               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                 {{ request()->routeIs('chat.*') ? 'bg-cyan-100' : 'bg-gray-100' }}
                                 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </span>
                    <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Chatbot</span>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            Chatbot
                        </span>
                    </template>
                </a>

                {{-- SECTION: Sistem --}}
                <p :class="collapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 pt-3 pb-1 whitespace-nowrap">
                    Sistem
                </p>

                {{-- Dashboard --}}
                @if (Route::has('dashboard'))
                    <a href="{{ route('dashboard') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('dashboard')
                                   ? 'bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-500/10'
                                   : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span
                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                     {{ request()->routeIs('dashboard') ? 'bg-emerald-100' : 'bg-gray-100' }}
                                     transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </span>
                        <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Dashboard</span>

                        <template x-if="collapsed">
                            <span
                                class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                         bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                         pointer-events-none">
                                Dashboard
                            </span>
                        </template>
                    </a>
                @endif

                {{-- Activity Log --}}
                @if (Route::has('activity.index'))
                    <a href="{{ route('activity.index') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('activity.*')
                                   ? 'bg-rose-50 text-rose-700 shadow-sm shadow-rose-500/10'
                                   : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span
                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                     {{ request()->routeIs('activity.*') ? 'bg-rose-100' : 'bg-gray-100' }}
                                     transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Activity Log</span>

                        <template x-if="collapsed">
                            <span
                                class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                         bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                         pointer-events-none">
                                Activity Log
                            </span>
                        </template>
                    </a>
                @endif

                {{-- 🆕 Kelola User — HANYA UNTUK ADMIN --}}
                @if (Route::has('users.index') && auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl
                               text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('users.*')
                                   ? 'bg-indigo-50 text-indigo-700 shadow-sm shadow-indigo-500/10'
                                   : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span
                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                     {{ request()->routeIs('users.*') ? 'bg-indigo-100' : 'bg-gray-100' }}
                                     transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Kelola User</span>

                        <template x-if="collapsed">
                            <span
                                class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                         bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                         pointer-events-none">
                                Kelola User
                            </span>
                        </template>
                    </a>
                @endif

            </nav>

            {{-- FOOTER SIDEBAR: User Info + Logout --}}
            <div class="border-t border-gray-100 p-3 shrink-0">

                {{-- User Info --}}
                <div :class="collapsed ? 'lg:justify-center lg:p-0' : ''"
                    class="group relative flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 mb-2">

                    <div
                        class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center
                                text-white font-bold text-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div :class="collapsed ? 'lg:hidden' : 'flex-1 min-w-0'">
                        <div class="flex items-center gap-1.5">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ auth()->user()->name ?? 'User' }}
                            </p>
                            @if (auth()->check())
                                <span
                                    class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                            @if (auth()->user()->isAdmin()) bg-violet-100 text-violet-700 border border-violet-200
                                            @elseif(auth()->user()->isSupport())
                                                bg-blue-100 text-blue-700 border border-blue-200
                                            @else
                                                bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                    {{ auth()->user()->role }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-500 truncate">
                            {{ auth()->user()->email ?? '' }}
                        </p>
                    </div>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            {{ auth()->user()->name ?? 'User' }} · {{ auth()->user()->role ?? '' }}
                        </span>
                    </template>
                </div>

                {{-- 🚪 Tombol Logout --}}
                <button type="button" @click="showLogoutConfirm = true"
                    :class="collapsed ? 'lg:justify-center lg:px-0 lg:py-2.5' : ''"
                    class="group relative w-full flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-sm font-semibold
                           bg-red-50 hover:bg-red-100
                           text-red-600 hover:text-red-700
                           border border-red-100
                           transition-all duration-200">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </span>
                    <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Logout</span>

                    <template x-if="collapsed">
                        <span
                            class="hidden lg:group-hover:block absolute left-full ml-2 px-2 py-1
                                     bg-gray-900 text-white text-xs rounded-md whitespace-nowrap z-50
                                     pointer-events-none">
                            Logout
                        </span>
                    </template>
                </button>

                {{-- Form logout (hidden) --}}
                <form id="sidebarLogoutForm" method="POST" action="{{ route('admin.logout') }}" class="hidden">
                    @csrf
                </form>
            </div>

        </aside>

        {{-- 🚪 MODAL KONFIRMASI LOGOUT --}}
        <div x-show="showLogoutConfirm"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center
                   pointer-events-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative transform transition-all duration-300"
                @click.away="showLogoutConfirm = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

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
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmLogout()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sidebarApp() {
            return {
                open: false,
                collapsed: false,
                showLogoutConfirm: false,

                toggle() {
                    this.open = !this.open;
                },

                close() {
                    this.open = false;
                },

                toggleCollapse() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('sidebar-collapsed', this.collapsed ? 'true' : 'false');

                    window.dispatchEvent(new CustomEvent('sidebar-toggled', {
                        detail: {
                            collapsed: this.collapsed
                        }
                    }));
                },

                confirmLogout() {
                    this.showLogoutConfirm = false;
                    document.getElementById('sidebarLogoutForm').submit();
                },

                init() {
                    const saved = localStorage.getItem('sidebar-collapsed');
                    if (saved === 'true') {
                        this.collapsed = true;
                    }

                    if (window.innerWidth >= 1024) {
                        this.open = true;
                    }

                    window.addEventListener('resize', () => {
                        this.open = window.innerWidth >= 1024;
                    });

                    this.$el.querySelectorAll('nav a').forEach(link => {
                        link.addEventListener('click', () => {
                            if (window.innerWidth < 1024) {
                                this.close();
                            }
                        });
                    });

                    this.$nextTick(() => {
                        window.dispatchEvent(new CustomEvent('sidebar-toggled', {
                            detail: {
                                collapsed: this.collapsed
                            }
                        }));
                    });
                }
            }
        }
    </script>
@endauth
