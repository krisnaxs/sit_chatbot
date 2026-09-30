@extends('layouts.app')

@section('title', 'Pengajuan Saya — SIAM')

@section('content')
    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pengajuan Saya</h1>
                <p class="text-sm text-gray-500">Riwayat & status pengajuan Anda</p>
            </div>
            <a href="{{ route('requests.create') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-lg shadow-indigo-500/30">
                + Buat Pengajuan
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 font-medium">Menunggu</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 font-medium">Disetujui</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $summary['approved'] }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 font-medium">Ditolak</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $summary['rejected'] }}</p>
            </div>
        </div>

        {{-- FILTER --}}
        <form method="GET"
            class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>
            <select name="type" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                <option value="loan" @selected(request('type') === 'loan')>Peminjaman</option>
                <option value="consumable" @selected(request('type') === 'consumable')>Konsumable</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pengajuan..."
                class="border rounded-lg px-3 py-2 text-sm">
            <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">
                Filter
            </button>
        </form>

        {{-- LIST --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. Pengajuan</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe</th>
                        <th class="px-4 py-3 text-left font-semibold">Item</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $r)
                        <tr onclick="openDetail({{ $r->id }})" class="hover:bg-indigo-50 cursor-pointer transition">

                            <td class="px-4 py-3 font-mono text-xs">{{ $r->request_number }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                bg-{{ $r->type_badge }}-100 text-{{ $r->type_badge }}-700">
                                    <span>{{ $r->type_icon }}</span>
                                    {{ $r->type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($r->asset)
                                    {{ $r->asset->brand }} {{ $r->asset->model }}
                                @elseif($r->consumable)
                                    {{ $r->consumable->name }} ({{ $r->quantity }})
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'pending' => [
                                            'bg' => 'bg-amber-100',
                                            'text' => 'text-amber-800',
                                            'dot' => 'bg-amber-500',
                                            'label' => 'Menunggu',
                                        ],
                                        'approved' => [
                                            'bg' => 'bg-green-100',
                                            'text' => 'text-green-800',
                                            'dot' => 'bg-green-500',
                                            'label' => 'Disetujui',
                                        ],
                                        'rejected' => [
                                            'bg' => 'bg-red-100',
                                            'text' => 'text-red-800',
                                            'dot' => 'bg-red-500',
                                            'label' => 'Ditolak',
                                        ],
                                        'cancelled' => [
                                            'bg' => 'bg-gray-100',
                                            'text' => 'text-gray-700',
                                            'dot' => 'bg-gray-400',
                                            'label' => 'Dibatalkan',
                                        ],
                                    ];
                                    $style = $statusStyles[$r->status] ?? $statusStyles['cancelled'];
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $style['bg'] }} {{ $style['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                    {{ $style['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $r->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                                Belum ada pengajuan. <a href="{{ route('requests.create') }}"
                                    class="text-indigo-600 underline">Buat sekarang</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $requests->links() }}</div>
    </div>
@endsection

@section('modals')
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MODAL DETAIL                                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div id="detailModal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetail()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

            {{-- HEADER --}}
            <div id="detailHeader" class="px-6 py-5 text-white shrink-0">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold" id="detailTitle">Detail Pengajuan</h3>
                            <p class="text-sm text-white/80" id="detailSubtitle">No. Pengajuan</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDetail()"
                        class="p-1.5 rounded-lg hover:bg-white/20 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4">

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Tipe Pengajuan</p>
                        <p class="font-medium" id="detailType">—</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Status</p>
                        <p class="font-medium" id="detailStatus">—</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-400 text-xs">Item</p>
                        <p class="font-medium" id="detailItem">—</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Lokasi</p>
                        <p class="font-medium" id="detailLocation">—</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Departemen</p>
                        <p class="font-medium" id="detailDepartment">—</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Tanggal Diajukan</p>
                        <p class="font-medium" id="detailCreatedAt">—</p>
                    </div>
                    <div id="detailDueDateWrapper" class="hidden">
                        <p class="text-gray-400 text-xs">Tanggal Kembali</p>
                        <p class="font-medium" id="detailDueDate">—</p>
                    </div>
                </div>

                {{-- Alasan --}}
                <div>
                    <p class="text-gray-400 text-xs mb-1">Alasan / Keperluan</p>
                    <p class="text-sm bg-gray-50 rounded-lg p-3" id="detailPurpose">—</p>
                </div>

                {{-- Admin notes --}}
                <div id="detailAdminNotesWrapper" class="hidden">
                    <p class="text-gray-400 text-xs mb-1">Catatan Admin</p>
                    <p class="text-sm bg-blue-50 rounded-lg p-3 text-blue-900" id="detailAdminNotes">—</p>
                </div>

                {{-- Rejection reason --}}
                <div id="detailRejectionWrapper" class="hidden">
                    <p class="text-gray-400 text-xs mb-1">Alasan Penolakan</p>
                    <p class="text-sm bg-red-50 rounded-lg p-3 text-red-900" id="detailRejection">—</p>
                </div>

                {{-- Processed info --}}
                <div id="detailProcessedWrapper" class="hidden text-xs text-gray-500 pt-3 border-t">
                    <span id="detailProcessedText"></span>
                </div>

                {{-- AKSI: Batalkan (kalau pending) --}}
                <div id="detailCancelWrapper" class="hidden pt-4 border-t space-y-3">
                    <div
                        class="flex items-center gap-2 text-amber-700 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs font-medium">Pengajuan ini masih menunggu. Anda bisa membatalkannya.</p>
                    </div>

                    <button type="button" onclick="openCancel()"
                        class="w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium
                           shadow-md shadow-red-500/30 transition inline-flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batalkan Pengajuan
                    </button>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeDetail()"
                    class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-100
                       text-gray-700 rounded-lg text-sm font-medium transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MODAL KONFIRMASI BATALKAN                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div id="cancelModal" class="hidden fixed inset-0 z-[110] items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCancel()"></div>

        <form id="cancelForm" method="POST"
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

            @csrf

            <div class="p-6 text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">Batalkan Pengajuan?</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Pengajuan <strong class="font-mono" id="cancelRequestNumber">—</strong> akan dibatalkan.
                    Tindakan ini tidak bisa di-undo.
                </p>

                <div class="flex gap-2">
                    <button type="button" onclick="closeCancel()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700
                           rounded-lg text-sm font-medium transition">
                        Tidak
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white
                           rounded-lg text-sm font-medium shadow-md shadow-red-500/30 transition">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // ═══════════════════════════════════════════════════════════
        //  DATA PENGAJUAN — di-inject dari Blade
        // ═══════════════════════════════════════════════════════════
        const REQUESTS = {
            @foreach ($requests as $r)
                {{ $r->id }}: {
                    id: {{ $r->id }},
                    request_number: @json($r->request_number),
                    type: @json($r->type),
                    type_label: @json($r->type_label),
                    type_icon: @json($r->type_icon),
                    item_detail: @json($r->item_detail),
                    location: @json($r->location?->full_name ?? '-'),
                    department: @json($r->department?->name ?? '-'),
                    purpose: @json($r->purpose),
                    admin_notes: @json($r->admin_notes),
                    rejection_reason: @json($r->rejection_reason),
                    status: @json($r->status),
                    status_label: @json($r->status_label),
                    due_date: @json($r->due_date?->format('d/m/Y') ?? null),
                    created_at: @json($r->created_at->format('d/m/Y H:i')),
                    approved_at: @json($r->approved_at?->format('d/m/Y H:i') ?? null),
                    approved_by: @json($r->approvedBy?->name ?? null),
                    is_pending: {{ $r->isPending() ? 'true' : 'false' }},
                    cancel_url: @json(route('requests.cancel', $r)),
                },
            @endforeach
        };

        // ═══════════════════════════════════════════════════════════
        //  DETAIL MODAL
        // ═══════════════════════════════════════════════════════════
        let currentRequestId = null;

        function openDetail(id) {
            const r = REQUESTS[id];
            if (!r) return;

            currentRequestId = id;

            // Header color sesuai status
            const header = document.getElementById('detailHeader');
            const headerGradients = {
                'pending': 'bg-gradient-to-br from-amber-500 to-orange-600',
                'approved': 'bg-gradient-to-br from-green-500 to-emerald-600',
                'rejected': 'bg-gradient-to-br from-red-500 to-rose-600',
                'cancelled': 'bg-gradient-to-br from-gray-500 to-slate-600',
            };
            header.className = headerGradients[r.status] + ' px-6 py-5 text-white shrink-0';

            // Header info
            document.getElementById('detailTitle').textContent = r.type_label;
            document.getElementById('detailSubtitle').textContent = r.request_number;

            // Body info
            document.getElementById('detailType').textContent = r.type_icon + ' ' + r.type_label;
            document.getElementById('detailStatus').textContent = r.status_label;
            document.getElementById('detailItem').textContent = r.item_detail;
            document.getElementById('detailLocation').textContent = r.location;
            document.getElementById('detailDepartment').textContent = r.department;
            document.getElementById('detailCreatedAt').textContent = r.created_at;
            document.getElementById('detailPurpose').textContent = r.purpose;

            // Due date (hanya loan)
            const dueWrapper = document.getElementById('detailDueDateWrapper');
            if (r.due_date) {
                dueWrapper.classList.remove('hidden');
                document.getElementById('detailDueDate').textContent = r.due_date;
            } else {
                dueWrapper.classList.add('hidden');
            }

            // Admin notes
            const adminWrapper = document.getElementById('detailAdminNotesWrapper');
            if (r.admin_notes) {
                adminWrapper.classList.remove('hidden');
                document.getElementById('detailAdminNotes').textContent = r.admin_notes;
            } else {
                adminWrapper.classList.add('hidden');
            }

            // Rejection reason
            const rejectWrapper = document.getElementById('detailRejectionWrapper');
            if (r.rejection_reason) {
                rejectWrapper.classList.remove('hidden');
                document.getElementById('detailRejection').textContent = r.rejection_reason;
            } else {
                rejectWrapper.classList.add('hidden');
            }

            // Processed info
            const processedWrapper = document.getElementById('detailProcessedWrapper');
            if (r.approved_at) {
                processedWrapper.classList.remove('hidden');
                document.getElementById('detailProcessedText').innerHTML =
                    `Diproses oleh <strong>${r.approved_by ?? '-'}</strong> pada ${r.approved_at}`;
            } else {
                processedWrapper.classList.add('hidden');
            }

            // Aksi batalkan (hanya kalau pending)
            const cancelWrapper = document.getElementById('detailCancelWrapper');
            if (r.is_pending) {
                cancelWrapper.classList.remove('hidden');
            } else {
                cancelWrapper.classList.add('hidden');
            }

            // Show modal
            const modal = document.getElementById('detailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeDetail() {
            const modal = document.getElementById('detailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            currentRequestId = null;
        }

        // ═══════════════════════════════════════════════════════════
        //  CANCEL MODAL
        // ═══════════════════════════════════════════════════════════
        function openCancel() {
            const id = currentRequestId;
            const r = REQUESTS[id];
            if (!r) return;

            const modal = document.getElementById('cancelModal');
            const form = document.getElementById('cancelForm');

            form.action = r.cancel_url;
            document.getElementById('cancelRequestNumber').textContent = r.request_number;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCancel() {
            const modal = document.getElementById('cancelModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('cancelForm').reset();

            // Kembali ke detail
            if (!document.getElementById('detailModal').classList.contains('hidden')) {
                closeDetail();
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  ESC HANDLER
        // ═══════════════════════════════════════════════════════════
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!document.getElementById('cancelModal').classList.contains('hidden')) {
                    closeCancel();
                } else {
                    closeDetail();
                }
            }
        });
    </script>
@endpush
