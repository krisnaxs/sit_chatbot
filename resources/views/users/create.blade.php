<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tambah User - Admin</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="userForm()">

    <x-header title="Tambah User" placeholder="Cari user..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        {{-- BREADCRUMB --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600 transition">Manajemen User</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Tambah</span>
        </nav>

        {{-- CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            {{-- HEADER --}}
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                            flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Tambah User</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Tambahkan akun user baru ke sistem</p>
                </div>
            </div>

            {{-- INFO AUTO USERNAME --}}
            <div class="mb-6 p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-blue-800">
                    <span class="font-semibold">Username otomatis.</span>
                    Username akan dibuat dari email sebelum tanda <code class="font-mono">@</code>.
                    Contoh: <code class="font-mono">budi.santoso@perusahaan.com</code> → username <code
                        class="font-mono">budi.santoso</code>.
                </div>
            </div>

            {{-- FORM --}}
            <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- IDENTITAS --}}
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Identitas</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" placeholder="cth: 20000006"
                                class="w-full border rounded-xl px-3 py-2.5 text-sm font-mono
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       @error('nip') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            @error('nip')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">No. HP</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                placeholder="cth: 081234567890"
                                class="w-full border rounded-xl px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       @error('phone') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            @error('phone')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="cth: Budi Santoso"
                            required
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('name') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('name')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="cth: budi.santoso@perusahaan.com" required
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ORGANISASI --}}
                <div class="pt-4 border-t border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Organisasi</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">Departemen</label>
                            <select name="department_id"
                                class="w-full border rounded-xl px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       @error('department_id') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">Jabatan</label>
                            <input type="text" name="position" value="{{ old('position') }}"
                                placeholder="cth: IT Support"
                                class="w-full border rounded-xl px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       @error('position') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            @error('position')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block font-semibold text-sm text-gray-700 mb-2">Lokasi Kerja</label>
                        <select name="location_id"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('location_id') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>
                                    {{ $l->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- PASSWORD --}}
                <div class="pt-4 border-t border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Keamanan</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" placeholder="Min. 8 karakter" required
                                class="w-full border rounded-xl px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       @error('password') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                            @error('password')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-sm text-gray-700 mb-2">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" placeholder="Ulangi password"
                                required
                                class="w-full border rounded-xl px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                       border-gray-200">
                        </div>
                    </div>
                </div>

                {{-- ROLE --}}
                <div class="pt-4 border-t border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                        Role <span class="text-red-500">*</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ([
        'admin' => ['Administrator', 'violet', 'Akses penuh ke semua fitur'],
        'support' => ['Support', 'blue', 'Kelola knowledge & SIAM'],
        'user' => ['User', 'gray', 'Akses portal aplikasi'],
    ] as $value => $info)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="role" value="{{ $value }}"
                                    class="peer sr-only" {{ old('role', 'user') === $value ? 'checked' : '' }}>
                                <div
                                    class="border-2 rounded-xl p-4 transition-all duration-200
                                            peer-checked:border-{{ $info[1] }}-500
                                            peer-checked:bg-{{ $info[1] }}-50
                                            border-gray-200 hover:border-gray-300">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-{{ $info[1] }}-100
                                                    flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-{{ $info[1] }}-600" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800">{{ $info[0] }}</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5 leading-tight">
                                                {{ $info[2] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- STATUS --}}
                <div class="pt-4 border-t border-gray-100">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-blue-600
                                   focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer transition"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Aktifkan akun</span>
                            <p class="text-xs text-gray-500">User bisa login setelah akun diaktifkan</p>
                        </div>
                    </label>
                </div>

                {{-- BUTTONS --}}
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2
                               bg-gradient-to-br from-blue-500 to-violet-600
                               hover:from-blue-600 hover:to-violet-700
                               text-white px-5 py-2.5 rounded-xl
                               font-semibold text-sm
                               shadow-lg shadow-blue-500/30
                               transition-all duration-300
                               hover:scale-105 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan
                    </button>
                    <a href="{{ route('users.index') }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300
                               text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TOAST ERROR --}}
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="fixed top-24 right-6 z-[100] flex items-start gap-3
               min-w-[320px] max-w-md
               px-4 py-3 rounded-xl
               bg-red-600 text-white
               shadow-2xl shadow-red-500/50
               border border-red-400/40
               backdrop-blur-md"
        style="display: none;">
        <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mt-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="flex-1">
            <div class="text-sm font-bold mb-1">Validasi gagal</div>
            <ul class="text-xs space-y-0.5 list-disc list-inside opacity-90">
                <template x-for="(msg, i) in toast.messages" :key="i">
                    <li x-text="msg"></li>
                </template>
            </ul>
        </div>
        <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <script>
        function userForm() {
            return {
                toast: {
                    show: false,
                    messages: []
                },
                showToast(messages) {
                    this.toast.messages = Array.isArray(messages) ? messages : [messages];
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },
                init() {
                    @if ($errors->any())
                        this.showToast(@json($errors->all()));
                    @endif
                }
            }
        }

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
