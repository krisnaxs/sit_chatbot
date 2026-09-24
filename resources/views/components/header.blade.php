@props(['title' => 'Portal Aplikasi', 'placeholder' => 'Cari aplikasi...'])

<div class="sticky top-0 z-50 bg-white shadow-md px-6 py-3 flex items-center relative" x-data="adminLogin()">

    <!-- LEFT: Hamburger (kalau sudah login) + Logo -->
    <div class="flex items-center gap-3 shrink-0">
        @auth
            {{-- 🍔 Hamburger untuk toggle sidebar — DESKTOP + MOBILE --}}
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

    <!-- CENTER: Search (hanya di portal) -->
    @if (request()->routeIs('portal'))
        <div class="absolute left-1/2 -translate-x-1/2 w-auto text-center px-4">
            <input type="text" id="searchInput" placeholder="{{ $placeholder }}"
                class="w-72 sm:w-96 px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       focus:bg-white transition">
        </div>
    @endif

    <!-- RIGHT: Login Button (guest only) -->
    <div class="flex-1 flex justify-end items-center gap-2">

        @guest
            {{-- ⚙️ Tombol Login Admin --}}
            <button @click="showLogin = true"
                class="inline-flex items-center gap-2
                       px-4 py-2 rounded-xl
                       text-sm font-semibold
                       bg-gradient-to-br from-blue-500 to-violet-600
                       hover:from-blue-600 hover:to-violet-700
                       text-white
                       shadow-md shadow-blue-500/30
                       hover:scale-105 active:scale-95
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

    <!-- Modal login (hanya jika belum login) -->
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
                    x-transition:leave-start="opacity-100 translate-y-0 max-h-20 mb-3"
                    x-transition:leave-end="opacity-0 -translate-y-2 max-h-0 mb-0"
                    class="flex items-center gap-2.5
                           px-3.5 py-2.5 rounded-lg
                           bg-red-50 border border-red-200
                           text-red-700 text-sm font-medium
                           overflow-hidden"
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
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
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
                            class="flex-1 px-4 py-2.5 rounded-xl
                                   bg-gradient-to-br from-blue-500 to-violet-600
                                   hover:from-blue-600 hover:to-violet-700
                                   text-white font-semibold
                                   shadow-lg shadow-blue-500/30 transition-all duration-300">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endguest

</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 🎈 CHAT WIDGET (FAB + Modal Mini) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<x-chat-widget />

<script>
    function adminLogin() {
        return {
            showLogin: false,
            toast: {
                show: false,
                message: '',
            },

            showToast(message) {
                this.toast.message = message;
                this.toast.show = true;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            },

            init() {
                const form = document.getElementById('adminLoginForm');
                if (!form) return;

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const formData = new FormData(form);
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
                });
            }
        }
    }
</script>
