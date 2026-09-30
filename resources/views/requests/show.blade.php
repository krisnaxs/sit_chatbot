@extends('layouts.app')

@section('title', 'Detail Pengajuan — SIAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <a href="{{ auth()->user()->isSupport() ? route('admin.requests.index') : route('requests.my') }}"
            class="text-sm text-indigo-600 hover:underline">← Kembali</a>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-mono">{{ $assetRequest->request_number }}</p>
                    <h1 class="text-xl font-bold text-gray-800 mt-1">{{ $assetRequest->type_label }}</h1>
                </div>
                <span
                    class="inline-flex px-3 py-1 rounded-md text-xs font-semibold
                @if ($assetRequest->status === 'pending') bg-amber-100 text-amber-700
                @elseif($assetRequest->status === 'approved') bg-green-100 text-green-700
                @elseif($assetRequest->status === 'rejected') bg-red-100 text-red-700
                @else bg-gray-100 text-gray-600 @endif">
                    {{ $assetRequest->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs">Pemohon</p>
                    <p class="font-medium">{{ $assetRequest->user->name }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs">Departemen</p>
                    <p class="font-medium">{{ $assetRequest->user->department?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs">Item</p>
                    <p class="font-medium">
                        @if ($assetRequest->asset)
                            {{ $assetRequest->asset->brand }} {{ $assetRequest->asset->model }}
                            <span class="text-gray-400 font-mono text-xs">({{ $assetRequest->asset->serial_number }})</span>
                        @elseif($assetRequest->consumable)
                            {{ $assetRequest->consumable->name }} × {{ $assetRequest->quantity }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs">Lokasi</p>
                    <p class="font-medium">{{ $assetRequest->location?->full_name ?? '-' }}</p>
                </div>
                @if ($assetRequest->due_date)
                    <div>
                        <p class="text-gray-400 text-xs">Tanggal Kembali</p>
                        <p class="font-medium">{{ $assetRequest->due_date->format('d/m/Y') }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-gray-400 text-xs">Tanggal Diajukan</p>
                    <p class="font-medium">{{ $assetRequest->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div>
                <p class="text-gray-400 text-xs mb-1">Alasan / Keperluan</p>
                <p class="text-sm bg-gray-50 rounded-lg p-3">{{ $assetRequest->purpose }}</p>
            </div>

            @if ($assetRequest->admin_notes)
                <div>
                    <p class="text-gray-400 text-xs mb-1">Catatan Admin</p>
                    <p class="text-sm bg-blue-50 rounded-lg p-3">{{ $assetRequest->admin_notes }}</p>
                </div>
            @endif

            @if ($assetRequest->status === 'rejected')
                <div>
                    <p class="text-gray-400 text-xs mb-1">Alasan Penolakan</p>
                    <p class="text-sm bg-red-50 text-red-800 rounded-lg p-3">{{ $assetRequest->rejection_reason }}
                    </p>
                </div>
            @endif

            @if ($assetRequest->approved_at)
                <div class="text-xs text-gray-500 pt-3 border-t">
                    Diproses oleh <strong>{{ $assetRequest->approvedBy?->name }}</strong>
                    pada {{ $assetRequest->approved_at->format('d/m/Y H:i') }}
                </div>
            @endif

            {{-- Aksi admin --}}
            @if (auth()->user()->isSupport() && $assetRequest->status === 'pending')
                <div class="flex gap-2 pt-3 border-t">
                    <form method="POST" action="{{ route('admin.requests.approve', $assetRequest) }}">
                        @csrf
                        <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                            Setujui
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.requests.reject', $assetRequest) }}"
                        onsubmit="return confirm('Tolak pengajuan ini?')">
                        @csrf
                        <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..."
                            class="border rounded-lg px-3 py-2 text-sm mr-2 w-64">
                        <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                            Tolak
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
