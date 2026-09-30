@extends('layouts.app')

@section('title', 'Import User')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- TITLE --}}
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Import User dari Excel</h1>
                <p class="text-sm text-gray-500 mt-1">Upload file Excel untuk menambahkan user secara massal</p>
            </div>
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center gap-2
                       bg-gray-100 hover:bg-gray-200
                       text-gray-700 px-4 py-2.5 rounded-xl
                       font-semibold text-sm
                       transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="max-w-4xl space-y-4">

            {{-- Alert error --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Terjadi Kesalahan</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Step 1: Download template --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <span class="text-emerald-600 font-bold text-lg">1</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 mb-1">Download Template</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Template berisi 2 sheet: <strong>Petunjuk</strong> (kolom wajib/opsional)
                            dan <strong>Data User</strong> (contoh isian).
                        </p>
                        <a href="{{ route('users.import.template') }}"
                            class="inline-flex items-center gap-2 px-4 py-2
                                   bg-emerald-600 hover:bg-emerald-700
                                   text-white text-sm font-semibold
                                   rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                            </svg>
                            Download Template Excel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 2: Upload --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <span class="text-blue-600 font-bold text-lg">2</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 mb-1">Upload File</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Isi template, lalu upload di sini. Format: <strong>xlsx</strong>,
                            <strong>xls</strong>, atau <strong>csv</strong> (max 5MB).
                        </p>

                        <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data"
                            class="space-y-3">
                            @csrf

                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                class="block w-full text-sm text-gray-600
                                       file:mr-4 file:py-2 file:px-4
                                       file:rounded-lg file:border-0
                                       file:text-sm file:font-semibold
                                       file:bg-blue-50 file:text-blue-700
                                       hover:file:bg-blue-100
                                       border border-gray-300 rounded-lg p-1">

                            @error('file')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2
                                       bg-blue-600 hover:bg-blue-700
                                       text-white text-sm font-semibold
                                       rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                                </svg>
                                Upload & Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold mb-1">Tips Import:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Kolom <code class="bg-white px-1 rounded">name</code>, <code
                                    class="bg-white px-1 rounded">email</code>, <code
                                    class="bg-white px-1 rounded">password</code>, <code
                                    class="bg-white px-1 rounded">role</code> wajib diisi.</li>
                            <li>Email harus unik — yang duplikat akan dilewati.</li>
                            <li>Gunakan <code class="bg-white px-1 rounded">department_code</code> (contoh: IT, FIN)
                                bukan nama departemen.</li>
                            <li>Password akan otomatis di-hash.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
