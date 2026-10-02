@extends('layouts.app')

@section('title', 'Agent Registry — SIAM')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('siam.dashboard') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke dashboard
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Agent Registry</h1>
                <p class="text-sm text-gray-500">Kelola registrasi & token agent aset IT</p>
            </div>
        </div>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">
                ✗ {{ session('error') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
                <div class="text-xs text-gray-500 uppercase">Pending</div>
                <div class="text-2xl font-bold text-amber-600">{{ $stats['pending_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-xs text-gray-500 uppercase">Rejected</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['rejected_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase">Token Aktif</div>
                <div class="text-2xl font-bold text-indigo-600">{{ $stats['active_tokens'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-400">
                <div class="text-xs text-gray-500 uppercase">Revoked</div>
                <div class="text-2xl font-bold text-gray-600">{{ $stats['revoked_tokens'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                <div class="text-xs text-gray-500 uppercase">Online</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['online'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-xs text-gray-500 uppercase">Idle</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['idle'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-500">
                <div class="text-xs text-gray-500 uppercase">Offline</div>
                <div class="text-2xl font-bold text-gray-600">{{ $stats['offline'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-slate-500">
                <div class="text-xs text-gray-500 uppercase">Belum Install</div>
                <div class="text-2xl font-bold text-slate-600">{{ $stats['never_seen'] }}</div>
            </div>
        </div>

        {{-- PENDING AGENTS --}}
        @if ($pending->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 overflow-hidden">
                <div
                    class="px-5 py-4 bg-amber-50 border-b border-amber-200 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="font-bold text-amber-900">
                            ⚠️ {{ $pending->total() }} Agent Menunggu Review
                        </h2>
                        <p class="text-xs text-amber-700">
                            Device ini mencoba register tapi belum ada aset yang cocok di SIAM.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('siam.agent-registry.clear') }}"
                        onsubmit="return confirm('Hapus semua pending?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-3 py-1.5 bg-white border border-amber-300 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-100">
                            Bersihkan Semua
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Hostname</th>
                                <th class="px-4 py-2 text-left">Serial BIOS</th>
                                <th class="px-4 py-2 text-left">IP / WiFi</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Attempts</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($pending as $p)
                                <tr class="hover:bg-amber-50">
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-xs font-semibold text-gray-800">
                                            {{ $p->hostname }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs">
                                        {{ $p->serial_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <div>{{ $p->ip ?? '-' }}</div>
                                        <div class="text-gray-500">{{ $p->wifi_ssid ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $p->logged_user ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">
                                            {{ $p->attempt_count }}x
                                        </span>
                                        <div class="text-gray-500 text-[10px] mt-1">
                                            {{ $p->attempted_at?->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($p->rejected_at)
                                            <span class="text-xs text-red-600 font-semibold">Sudah di-reject</span>
                                        @elseif ($p->approved_at)
                                            <span class="text-xs text-emerald-600 font-semibold">Sudah di-approve</span>
                                        @else
                                            <div class="inline-flex gap-2">
                                                <button type="button"
                                                    onclick="openApproveModal({{ $p->id }}, '{{ $p->hostname }}', '{{ $p->serial_number }}')"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium">
                                                    Approve
                                                </button>

                                                <button type="button"
                                                    onclick="openRejectModal({{ $p->id }}, '{{ $p->hostname }}')"
                                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-medium">
                                                    Reject
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $pending->links() }}
                </div>
            </div>
        @endif

        {{-- REJECTED AGENTS --}}
        @if ($rejected->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
                <div class="px-5 py-4 bg-red-50 border-b border-red-200 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="font-bold text-red-900">
                            🚫 {{ $rejected->total() }} Device Di-reject
                        </h2>
                        <p class="text-xs text-red-700">
                            Device ini tidak akan bisa register lagi sampai dihapus permanen.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('siam.agent-registry.clear-rejected') }}"
                        onsubmit="return confirm('Hapus permanen semua device yang di-reject?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-3 py-1.5 bg-white border border-red-300 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">
                            Hapus Semua
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Hostname</th>
                                <th class="px-4 py-2 text-left">Serial BIOS</th>
                                <th class="px-4 py-2 text-left">Alasan</th>
                                <th class="px-4 py-2 text-left">Kapan</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rejected as $r)
                                <tr class="hover:bg-red-50">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                        {{ $r->hostname }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        {{ $r->serial_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-red-700">
                                        {{ $r->rejected_reason ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $r->rejected_at?->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('siam.agent-registry.force-delete', $r) }}"
                                            onsubmit="return confirm('Hapus permanen device {{ $r->hostname }} dari blacklist?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-[10px] font-medium">
                                                Hapus Permanen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $rejected->links() }}
                </div>
            </div>
        @endif

        {{-- TOKEN AKTIF --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="font-bold text-gray-800">
                        🔑 Token Aktif ({{ $tokens->total() }})
                    </h2>
                    <p class="text-xs text-gray-500">Token yang sedang aktif digunakan agent</p>
                </div>

                <form method="GET" action="{{ route('siam.agent-registry.index') }}" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari hostname / asset..." class="border rounded-lg px-3 py-1.5 text-xs w-64">
                    <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs font-medium">
                        Cari
                    </button>
                </form>
            </div>

            @if ($tokens->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Asset</th>
                                <th class="px-4 py-2 text-left">Device Hostname</th>
                                <th class="px-4 py-2 text-left">Serial</th>
                                <th class="px-4 py-2 text-left">Terakhir Dipakai</th>
                                <th class="px-4 py-2 text-left">IP</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tokens as $token)
                                <tr class="hover:bg-indigo-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800 text-xs">
                                            {{ $token->asset?->asset_code ?? '-' }}
                                        </div>
                                        <div class="text-gray-500 text-[10px]">
                                            {{ $token->asset?->brand }} {{ $token->asset?->model }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs">
                                        {{ $token->device_hostname ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        {{ $token->device_serial ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if ($token->last_used_at)
                                            <div class="text-gray-800">
                                                {{ $token->last_used_at->format('d M Y H:i') }}
                                            </div>
                                            <div class="text-gray-500 text-[10px]">
                                                {{ $token->last_used_at->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Belum pernah</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        {{ $token->last_ip ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex gap-2">
                                            <form method="POST"
                                                action="{{ route('siam.agent-registry.tokens.revoke', $token) }}"
                                                onsubmit="return confirm('Nonaktifkan token ini?')">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded text-[10px] font-medium">
                                                    Revoke
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('siam.agent-registry.tokens.regenerate', $token) }}"
                                                onsubmit="return confirm('Regenerate token? Token lama tidak akan valid lagi.')">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-[10px] font-medium">
                                                    Reset
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t">
                    {{ $tokens->links() }}
                </div>
            @else
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    Belum ada token aktif.
                </div>
            @endif
        </div>

    </div>

    {{-- MODAL APPROVE --}}
    <div id="approveModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeApproveModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 px-6 py-5 text-white">
                <h3 class="text-lg font-bold">Approve Agent</h3>
                <p class="text-sm text-white/80">Link device ke asset di SIAM</p>
            </div>

            <form id="approveForm" method="POST">
                @csrf

                <div class="p-6 space-y-4">
                    {{-- Info device --}}
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <div class="text-xs text-gray-500">Hostname</div>
                        <div class="font-mono text-sm font-semibold" id="modalHostname">-</div>
                        <div class="text-xs text-gray-500 mt-2">Serial BIOS</div>
                        <div class="font-mono text-xs" id="modalSerial">-</div>
                    </div>

                    {{-- Pilih asset --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Aset <span class="text-red-500">*</span>
                        </label>
                        <select name="asset_id" required
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}">
                                    {{ $asset->asset_code }} — {{ $asset->brand }} {{ $asset->model }}
                                    ({{ $asset->category?->name }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Hanya menampilkan aset yang belum punya token agent.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end gap-2">
                    <button type="button" onclick="closeApproveModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <div id="rejectModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRejectModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-gradient-to-br from-red-500 to-rose-600 px-6 py-5 text-white">
                <h3 class="text-lg font-bold">Reject Agent</h3>
                <p class="text-sm text-white/80">Device tidak akan bisa register lagi</p>
            </div>

            <form id="rejectForm" method="POST">
                @csrf

                <div class="p-6 space-y-4">
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 space-y-1">
                        <div class="text-xs text-red-600 font-semibold">Reject device:</div>
                        <div class="font-mono text-sm font-semibold text-red-900" id="rejectHostname">-</div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Alasan Reject (opsional)
                        </label>
                        <textarea name="reason" rows="3" placeholder="Contoh: Bukan aset perusahaan / Device tidak dikenal"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Device akan masuk daftar "Rejected" dan tidak bisa register lagi.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 border-t flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        function openApproveModal(pendingId, hostname, serial) {
            document.getElementById('modalHostname').textContent = hostname;
            document.getElementById('modalSerial').textContent = serial || '-';
            document.getElementById('approveForm').action = `/siam/agent-registry/${pendingId}/approve`;
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }

        function openRejectModal(pendingId, hostname) {
            document.getElementById('rejectHostname').textContent = hostname;
            document.getElementById('rejectForm').action = `/siam/agent-registry/${pendingId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
@endsection
