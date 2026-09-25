<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Peminjaman Aset — SIAM</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans" x-data="loanManager()">

    <x-header title="Peminjaman Aset" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

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

        <div class="space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Peminjaman Aset</h1>
                    <p class="text-sm text-gray-500">Aset yang dipinjam sementara oleh pegawai</p>
                </div>
                <a href="{{ route('siam.loans.create') }}"
                    class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium
                           inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Pinjam Aset
                </a>
            </div>

            {{-- FILTER --}}
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" action="{{ route('siam.loans.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-3">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Aset</label>
                        <select name="asset_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}" @selected(request('asset_id') == $a->id)>
                                    {{ $a->serial_number }} — {{ $a->brand }} {{ $a->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Peminjam</label>
                        <select name="user_id" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" data-auto-submit class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                            <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                            <option value="borrowed" @selected(request('status') === 'borrowed')>Dipinjam</option>
                            <option value="returned" @selected(request('status') === 'returned')>Dikembalikan</option>
                            <option value="overdue" @selected(request('status') === 'overdue')>Terlambat</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('siam.loans.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Aset</th>
                                <th class="px-3 py-2 text-left">Peminjam</th>
                                <th class="px-3 py-2 text-left">Tujuan</th>
                                <th class="px-3 py-2 text-left">Tgl Pinjam</th>
                                <th class="px-3 py-2 text-left">Jatuh Tempo</th>
                                <th class="px-3 py-2 text-left">Dikembalikan</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($loans as $loan)
                                @php
                                    $loanData = [
                                        'id' => $loan->id,
                                        'asset_brand' => $loan->asset?->brand,
                                        'asset_model' => $loan->asset?->model,
                                        'asset_sn' => $loan->asset?->serial_number,
                                        'user_name' => $loan->user?->name,
                                        'user_position' => $loan->user?->position,
                                        'user_email' => $loan->user?->email,
                                        'purpose' => $loan->purpose,
                                        'loan_date' => $loan->loan_date?->format('d M Y'),
                                        'due_date' => $loan->due_date?->format('d M Y'),
                                        'returned_at' => $loan->returned_at?->format('d M Y'),
                                        'status' => $loan->status,
                                        'status_label' => $loan->status_label,
                                        'is_overdue' => $loan->is_overdue,
                                        'is_returned' => $loan->returned_at !== null,
                                        'notes' => $loan->notes,
                                        'condition_on_loan' => $loan->condition_on_loan,
                                        'condition_on_return' => $loan->condition_on_return,
                                        'routes' => [
                                            'show' => route('siam.loans.show', $loan),
                                            'return' => route('siam.loans.return', $loan),
                                            'delete' => route('siam.loans.destroy', $loan),
                                        ],
                                    ];
                                @endphp
                                <tr @click='openModal({{ json_encode($loanData, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    class="hover:bg-indigo-50 cursor-pointer transition-colors">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">
                                            {{ $loan->asset?->brand }} {{ $loan->asset?->model }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ $loan->asset?->serial_number }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-gray-800">{{ $loan->user?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $loan->user?->position }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->purpose ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->loan_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->due_date?->format('d M Y') ?? '-' }}
                                        @if ($loan->is_overdue)
                                            <span
                                                class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-red-100 text-red-700 font-bold">
                                                TERLAMBAT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $loan->returned_at?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColor = match ($loan->status) {
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'approved' => 'bg-blue-100 text-blue-700',
                                                'borrowed' => 'bg-indigo-100 text-indigo-700',
                                                'returned' => 'bg-green-100 text-green-700',
                                                'overdue' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs rounded {{ $statusColor }}">
                                            {{ $loan->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">Belum ada peminjaman</p>
                                                <p class="text-sm text-gray-500 mt-1">Catat peminjaman pertama</p>
                                            </div>
                                            <a href="{{ route('siam.loans.create') }}"
                                                class="mt-2 text-sm text-indigo-600 hover:underline font-semibold">
                                                + Pinjam aset
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($loans->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $loans->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODAL POPUP AKSI PEMINJAMAN --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="closeModal()">

            {{-- HEADER --}}
            <div class="bg-gradient-to-br from-amber-500 to-yellow-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold"
                                x-text="selected?.status_label"></span>
                            <span x-show="selected?.is_overdue"
                                class="text-xs px-2 py-0.5 rounded bg-white/20 backdrop-blur-sm font-semibold">
                                TERLAMBAT
                            </span>
                        </div>
                        <h3 class="text-lg font-bold truncate"
                            x-text="(selected?.asset_brand ?? '') + ' ' + (selected?.asset_model ?? '')"></h3>
                        <p class="text-sm text-white/80 font-mono" x-text="'SN: ' + (selected?.asset_sn ?? '-')"></p>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- INFO --}}
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Peminjam</div>
                        <div class="text-xs font-medium" x-text="selected?.user_name ?? '-'"></div>
                        <div class="text-xs text-gray-400" x-text="selected?.user_position ?? ''"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Tujuan</div>
                        <div class="text-xs" x-text="selected?.purpose ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Tgl Pinjam</div>
                        <div class="text-xs font-medium" x-text="selected?.loan_date ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Jatuh Tempo</div>
                        <div class="text-xs font-medium" x-text="selected?.due_date ?? '-'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Dikembalikan</div>
                        <div class="text-xs font-medium" x-text="selected?.returned_at ?? 'Belum'"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Status</div>
                        <div class="text-xs font-medium" x-text="selected?.status_label"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Catatan</div>
                        <div class="text-xs" x-text="selected?.notes ?? '-'"></div>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilih Aksi</h4>

                <div class="grid grid-cols-2 gap-3">

                    {{-- DETAIL --}}
                    <a :href="selected?.routes.show"
                        class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                              hover:border-indigo-300 hover:bg-indigo-50 transition group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800">Detail</div>
                            <div class="text-xs text-gray-500">Info lengkap</div>
                        </div>
                    </a>

                    {{-- KEMBALIKAN --}}
                    <template x-if="!selected?.is_returned">
                        <button type="button" @click="confirmReturn()"
                            class="flex items-center gap-3 p-3 rounded-xl border border-green-200
                                   bg-green-50 hover:bg-green-100 hover:border-green-300 transition group w-full text-left">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-green-700">Kembalikan</div>
                                <div class="text-xs text-green-500">Tandai sudah kembali</div>
                            </div>
                        </button>
                    </template>

                    {{-- HAPUS (admin only) --}}
                    @if (auth()->user()->isAdmin())
                        <button type="button" @click="confirmDelete()"
                            class="col-span-2 flex items-center gap-3 p-3 rounded-xl border border-red-200
                                   bg-red-50 hover:bg-red-100 hover:border-red-300 transition group w-full text-left">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-red-700">Hapus</div>
                                <div class="text-xs text-red-500">Khusus admin — tidak bisa dibatalkan</div>
                            </div>
                        </button>
                    @endif

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-end">
                <button @click="closeModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI KEMBALIKAN --}}
    <div x-show="showReturnModal" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showReturnModal = false">
            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 11l3 3L22 4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Kembalikan Aset?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Tandai aset
                <strong class="text-gray-800"
                    x-text="(selected?.asset_brand ?? '') + ' ' + (selected?.asset_model ?? '')"></strong>
                sudah dikembalikan?
            </p>
            <div class="flex gap-2">
                <button type="button" @click="showReturnModal = false"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                    Batal
                </button>
                <button type="button" @click="submitReturn()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 hover:bg-green-700
                               text-white font-semibold transition shadow-lg shadow-green-500/30">
                    Ya, Kembalikan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showDeleteModal = false">
            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Peminjaman?</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
                Yakin ingin menghapus data peminjaman
                <strong class="text-gray-800"
                    x-text="(selected?.asset_brand ?? '') + ' ' + (selected?.asset_model ?? '')"></strong>?
                <br>
                <span class="text-xs text-red-500">Tindakan ini tidak bisa dibatalkan.</span>
            </p>
            <div class="flex gap-2">
                <button type="button" @click="showDeleteModal = false"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200
                               text-gray-700 font-semibold transition">
                    Batal
                </button>
                <button type="button" @click="submitDelete()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700
                               text-white font-semibold transition shadow-lg shadow-red-500/30">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <form id="returnForm" method="POST" class="hidden">
        @csrf
    </form>

    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function loanManager() {
            return {
                showModal: false,
                showReturnModal: false,
                showDeleteModal: false,
                selected: null,

                openModal(data) {
                    this.selected = data;
                    this.showModal = true;
                },
                closeModal() {
                    this.showModal = false;
                    this.selected = null;
                },
                confirmReturn() {
                    this.showModal = false;
                    this.showReturnModal = true;
                },
                confirmDelete() {
                    this.showModal = false;
                    this.showDeleteModal = true;
                },

                submitReturn() {
                    const form = document.getElementById('returnForm');
                    form.action = this.selected.routes.return;

                    const returnedAt = document.createElement('input');
                    returnedAt.type = 'hidden';
                    returnedAt.name = 'returned_at';
                    returnedAt.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    form.appendChild(returnedAt);

                    form.submit();
                },

                submitDelete() {
                    const form = document.getElementById('deleteForm');
                    form.action = this.selected.routes.delete;
                    form.submit();
                },

                init() {
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.showDeleteModal) this.showDeleteModal = false;
                            else if (this.showReturnModal) this.showReturnModal = false;
                            else if (this.showModal) this.closeModal();
                        }
                    });
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

        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</body>

</html>
