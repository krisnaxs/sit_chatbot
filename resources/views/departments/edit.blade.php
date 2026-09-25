<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Departemen - SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Edit Departemen" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-2xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('siam.departments.index') }}" class="hover:text-indigo-600 transition">Departemen</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Edit</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500
                            flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Edit Departemen</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Perbarui data <strong>{{ $department->name }}</strong>
                    </p>
                </div>
            </div>

            <form action="{{ route('siam.departments.update', $department) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Kode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', $department->code) }}" required
                            maxlength="20"
                            class="w-full border rounded-xl px-3 py-2.5 text-sm font-mono uppercase
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   @error('code') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('code')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-sm text-gray-700 mb-2">
                            Nama Departemen <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                            class="w-full border rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   @error('name') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        @error('name')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full border rounded-xl px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               @error('description') border-red-400 bg-red-50 @else border-gray-200 @enderror">{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600
                                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer"
                            {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Aktifkan</span>
                            <p class="text-xs text-gray-500">Departemen bisa dipilih di form user</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-100">
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
                        Update
                    </button>
                    <a href="{{ route('siam.departments.index') }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300
                               text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
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
