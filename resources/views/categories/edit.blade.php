@extends('layouts.app')

@section('title', 'Edit Kategori — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto">

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <a href="{{ route('siam.categories.index') }}" class="text-sm text-indigo-600 hover:underline">←
                    Kembali</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Kategori</h1>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('siam.categories.update', $category) }}"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Kode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="code" required value="{{ old('code', $category->code) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $category->name) }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_consumable" value="1"
                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                            @checked(old('is_consumable', $category->is_consumable))>
                        <span class="font-semibold text-gray-700">Konsumable</span>
                    </label>

                    {{-- Monitor via Agent --}}
                    <label
                        class="flex items-start gap-3 cursor-pointer select-none p-4 rounded-xl bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition">
                        <input type="checkbox" name="is_agent_monitored" value="1"
                            class="mt-0.5 w-5 h-5 rounded border-gray-300 text-indigo-600
                                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer"
                            @checked(old('is_agent_monitored', $category->is_agent_monitored))>
                        <div>
                            <span class="text-sm font-semibold text-indigo-900">🖥 Monitor via Agent</span>
                            <p class="text-xs text-indigo-700 mt-0.5">
                                Centang jika kategori ini bisa di-install agent (laptop, PC, server).
                                Aset dalam kategori ini akan muncul di halaman <strong>Monitoring</strong>.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            @checked(old('is_active', $category->is_active))>
                        <span class="font-semibold text-gray-700">Aktif</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('siam.categories.index') }}"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
