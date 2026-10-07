@extends('layouts.app')

@section('title', 'Buat Berita Acara — ' . $asset->serial_number)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <a href="{{ route('siam.assets.show', $asset) }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali ke detail aset
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Buat Berita Acara Manual</h1>
            <p class="text-sm text-gray-500">
                Buat ulang / generate BA dari data assignment yang sudah ada
            </p>
        </div>

        {{-- ALERT DARI SESSION --}}
        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-sm text-blue-800">
                <strong>Info:</strong> {{ session('info') }}
            </div>
        @endif

        {{-- INFO ASET --}}
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
            <div class="font-bold text-gray-800">{{ $asset->brand }} {{ $asset->model }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ $asset->serial_number }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $asset->category?->name ?? '-' }}</div>
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('siam.berita-acara.store-from-asset', $asset) }}"
            class="bg-white rounded-lg shadow p-6 space-y-5">
            @csrf

            {{-- JENIS BA --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Berita Acara</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="serah_terima" class="peer sr-only"
                            {{ $jenis === 'serah_terima' ? 'checked' : '' }}
                            onchange="window.location.href='{{ route('siam.berita-acara.create-from-asset', [$asset, 'jenis' => 'serah_terima']) }}'">
                        <div
                            class="border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50
                                    rounded-lg p-4 transition text-center">
                            <div class="text-2xl mb-1">📤</div>
                            <div class="font-semibold text-sm">Serah Terima (BAST)</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="pengembalian" class="peer sr-only"
                            {{ $jenis === 'pengembalian' ? 'checked' : '' }}
                            onchange="window.location.href='{{ route('siam.berita-acara.create-from-asset', [$asset, 'jenis' => 'pengembalian']) }}'">
                        <div
                            class="border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50
                                    rounded-lg p-4 transition text-center">
                            <div class="text-2xl mb-1">📥</div>
                            <div class="font-semibold text-sm">Pengembalian (BAP)</div>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    @if ($jenis === 'serah_terima')
                        BAST bisa dibuat untuk semua record assignment.
                    @else
                        BAP hanya bisa dibuat untuk assignment yang sudah dikembalikan.
                    @endif
                </p>
            </div>

            {{-- PILIH ASSIGNMENT --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Record Assignment</label>

                @if ($eligibleAssignments->isEmpty())
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                        ⚠️ Tidak ada assignment yang eligible untuk jenis BA ini.
                        @if ($jenis === 'pengembalian')
                            Aset harus sudah pernah dikembalikan (punya <code>returned_at</code>).
                        @endif
                    </div>
                @else
                    <select name="assignment_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach ($eligibleAssignments as $a)
                            @php
                                $baSt = $existingBa[$a->id]['serah_terima'] ?? null;
                                $baBap = $existingBa[$a->id]['pengembalian'] ?? null;
                                $currentBa = $jenis === 'serah_terima' ? $baSt : $baBap;
                            @endphp
                            <option value="{{ $a->id }}"
                                {{ (string) $selectedAssignmentId === (string) $a->id ? 'selected' : '' }}>
                                #{{ $a->id }} —
                                {{ $a->user?->name ?? '-' }}
                                | Diserahkan: {{ $a->assigned_at?->format('d M Y') ?? '-' }}
                                @if ($a->returned_at)
                                    | Dikembalikan: {{ $a->returned_at->format('d M Y') }}
                                @endif
                                @if ($currentBa)
                                    ⚠️ Sudah ada BA: {{ $currentBa->nomor_ba }}
                                @else
                                    ✅ Belum ada BA
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('assignment_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- PIHAK PERTAMA --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Pihak Pertama <span class="text-red-500">*</span>
                    </label>
                    <select name="pihak_pertama_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Pejabat --</option>
                        @foreach ($penandatangan as $p)
                            <option value="{{ $p->id }}" {{ old('pihak_pertama_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} — {{ $p->position ?? ucfirst($p->role) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hanya admin/support yang bisa jadi pihak pertama.</p>
                    @error('pihak_pertama_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jabatan Pihak Pertama</label>
                    <input type="text" name="pihak_pertama_jabatan" value="{{ old('pihak_pertama_jabatan') }}"
                        placeholder="Kosongkan = ambil dari profil user"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP Pihak Pertama</label>
                    <input type="text" name="pihak_pertama_nip" value="{{ old('pihak_pertama_nip') }}"
                        placeholder="Kosongkan = ambil dari profil user"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tempat BA</label>
                    <input type="text" name="tempat_ba" value="{{ old('tempat_ba', 'Suralaya') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- NOTES --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" placeholder="Opsional..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>

            {{-- SUBMIT --}}
            <div class="flex justify-end gap-2 pt-3 border-t">
                <a href="{{ route('siam.assets.show', $asset) }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                    Batal
                </a>
                <button type="submit" @disabled($eligibleAssignments->isEmpty())
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700
                           text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Buat Berita Acara
                </button>
            </div>
        </form>

    </div>
@endsection
