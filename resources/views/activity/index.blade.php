@php
    if (!function_exists('activityFieldLabel')) {
        function activityFieldLabel(string $field): string
        {
            $map = [
                'name' => 'Nama',
                'email' => 'Email',
                'username' => 'Username',
                'nip' => 'NIP',
                'phone' => 'Telepon',
                'position' => 'Jabatan',
                'role' => 'Role',
                'is_active' => 'Status Aktif',
                'location_id' => 'Lokasi',
                'department_id' => 'Departemen',
                'user_id' => 'User',
                'asset_id' => 'Aset',
                'category_id' => 'Kategori',
                'vendor_id' => 'Vendor',
                'consumable_id' => 'Konsumable',
                'current_user_id' => 'Pemakai',
                'current_location_id' => 'Lokasi Saat Ini',
                'from_location_id' => 'Lokasi Asal',
                'to_location_id' => 'Lokasi Tujuan',
                'status' => 'Status',
                'condition_percent' => 'Kondisi (%)',
                'condition_notes' => 'Catatan Kondisi',
                'condition_before' => 'Kondisi Sebelum (%)',
                'condition_after' => 'Kondisi Sesudah (%)',
                'condition_on_assign' => 'Kondisi Saat Diserahkan (%)',
                'condition_on_return' => 'Kondisi Saat Dikembalikan (%)',
                'condition_on_loan' => 'Kondisi Saat Dipinjam (%)',
                'notes' => 'Catatan',
                'hostname' => 'Hostname',
                'serial_number' => 'Serial Number',
                'asset_code' => 'Kode Aset',
                'brand' => 'Brand',
                'model' => 'Model',
                'os' => 'OS',
                'os_license' => 'Lisensi OS',
                'specification' => 'Spesifikasi',
                'purchase_date' => 'Tanggal Beli',
                'purchase_price' => 'Harga Beli',
                'warranty_expire' => 'Garansi Berakhir',
                'ownership_type' => 'Tipe Kepemilikan',
                'invoice_number' => 'No. Invoice',
                'monthly_cost' => 'Biaya Sewa/Bulan',
                'contract_end' => 'Kontrak Berakhir',
                'stock_total' => 'Stok Total',
                'stock_available' => 'Stok Tersedia',
                'stock_minimum' => 'Stok Minimum',
                'unit' => 'Satuan',
                'quantity' => 'Jumlah',
                'type' => 'Tipe',
                'assigned_at' => 'Tanggal Diserahkan',
                'returned_at' => 'Tanggal Dikembalikan',
                'loan_date' => 'Tanggal Pinjam',
                'due_date' => 'Jatuh Tempo',
                'purpose' => 'Keperluan',
                'issue' => 'Masalah',
                'action' => 'Tindakan',
                'technician' => 'Teknisi',
                'cost' => 'Biaya',
                'start_date' => 'Tanggal Mulai',
                'end_date' => 'Tanggal Selesai',
                'transaction_date' => 'Tanggal Transaksi',
                'assigned_by' => 'Diserahkan Oleh',
                'received_by' => 'Diterima Oleh',
                'approved_by' => 'Disetujui Oleh',
                'requested_by' => 'Diminta Oleh',
                'moved_by' => 'Dipindahkan Oleh',
                'uploaded_by' => 'Diunggah Oleh',
                'request_number' => 'No. Pengajuan',
                'requested_at' => 'Tanggal Pengajuan',
                'needed_date' => 'Tanggal Dibutuhkan',
                'admin_notes' => 'Catatan Admin',
                'rejection_reason' => 'Alasan Penolakan',
                'loan_id' => 'ID Peminjaman',
                'transaction_id' => 'ID Transaksi',
                'source' => 'Sumber',
                'old_status' => 'Status Lama',
                'new_status' => 'Status Baru',
                'cancelled_by' => 'Dibatalkan Oleh',
                'cancelled_by_name' => 'Nama Pembatal',
                'user_name' => 'Nama User',
                'asset_serial' => 'Serial Aset',
                'consumable_name' => 'Nama Konsumable',
            ];
            return $map[$field] ?? ucwords(str_replace('_', ' ', $field));
        }
    }

    if (!function_exists('activityResolveValue')) {
        function activityResolveValue(string $field, $value): string
        {
            $skipFields = [
                'status',
                'type',
                'ownership_type',
                'role',
                'os_license',
                'unit',
                'is_active',
                'condition_percent',
                'condition_before',
                'condition_after',
                'condition_on_assign',
                'condition_on_return',
                'condition_on_loan',
                'quantity',
                'stock_total',
                'stock_available',
                'stock_minimum',
                'cost',
                'monthly_cost',
                'purchase_price',
                'last_price',
                'old_status',
                'new_status',
                'request_number',
                'source',
                'cancelled_by_name',
                'user_name',
                'asset_serial',
                'consumable_name',
                'admin_notes',
                'rejection_reason',
            ];
            if (in_array($field, $skipFields)) {
                return activityFormatValue($value);
            }

            $idResolvers = [
                'department_id' => \App\Models\Department::class,
                'location_id' => \App\Models\Location::class,
                'current_location_id' => \App\Models\Location::class,
                'from_location_id' => \App\Models\Location::class,
                'to_location_id' => \App\Models\Location::class,
                'user_id' => \App\Models\User::class,
                'current_user_id' => \App\Models\User::class,
                'assigned_by' => \App\Models\User::class,
                'received_by' => \App\Models\User::class,
                'approved_by' => \App\Models\User::class,
                'requested_by' => \App\Models\User::class,
                'moved_by' => \App\Models\User::class,
                'uploaded_by' => \App\Models\User::class,
                'asset_id' => \App\Models\Asset::class,
                'category_id' => \App\Models\AssetCategory::class,
                'vendor_id' => \App\Models\Vendor::class,
                'consumable_id' => \App\Models\Consumable::class,
                'loan_id' => \App\Models\AssetLoan::class,
                'transaction_id' => \App\Models\ConsumableTransaction::class,
            ];

            if (!isset($idResolvers[$field])) {
                return activityFormatValue($value);
            }
            if (is_null($value) || $value === '' || $value === 0 || $value === '0') {
                return '(kosong)';
            }
            $record = $idResolvers[$field]::find($value);
            if (!$record) {
                return activityFormatValue($value);
            }
            if (isset($record->name) && !empty($record->name)) {
                return trim((string) $record->name);
            }
            if (isset($record->full_name) && !empty($record->full_name)) {
                return trim((string) $record->full_name);
            }
            if (isset($record->brand) && isset($record->serial_number)) {
                $parts = array_filter([
                    $record->brand,
                    $record->model ?? null,
                    $record->serial_number ? "({$record->serial_number})" : null,
                ]);
                return trim(implode(' ', $parts));
            }
            if (isset($record->asset_code) && !empty($record->asset_code)) {
                return trim((string) $record->asset_code);
            }
            if (isset($record->code) && !empty($record->code)) {
                return trim((string) $record->code);
            }
            if (isset($record->loan_date) && isset($record->asset_id)) {
                $asset = \App\Models\Asset::find($record->asset_id);
                return "Peminjaman {$asset?->serial_number} (" . ($record->loan_date?->format('d M Y') ?? '-') . ')';
            }
            if (isset($record->transaction_date) && isset($record->consumable_id)) {
                $cons = \App\Models\Consumable::find($record->consumable_id);
                return "Transaksi {$cons?->name} x{$record->quantity}";
            }
            return "ID: {$value}";
        }
    }

    if (!function_exists('activityFormatValue')) {
        function activityFormatValue($value): string
        {
            if (is_null($value)) {
                return '(kosong)';
            }
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            if (is_array($value) || is_object($value)) {
                $json = json_encode($value, JSON_UNESCAPED_SLASHES);
                return $json === false ? '(tidak bisa ditampilkan)' : $json;
            }
            if ($value === '') {
                return '(kosong)';
            }
            return (string) $value;
        }
    }
@endphp

@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="max-w-6xl mx-auto" x-data="activityManager()">

        <!-- TITLE + ACTION -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Riwayat aktivitas admin &amp; pengguna sistem
                    @if (!auth()->user()->isAdmin())
                        <span
                            class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                     bg-gray-100 text-gray-600 border border-gray-200">
                            View Only
                        </span>
                    @endif
                </p>
            </div>

            @if (auth()->user()->isAdmin())
                <button type="button" @click="showClearModal = true"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-xl
                           font-semibold text-sm shadow-lg shadow-red-500/30 transition-all duration-300
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bersihkan Semua
                </button>
            @endif
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Log</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalAll) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari Ini</p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($totalToday) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Minggu Ini</p>
                        <p class="text-3xl font-bold text-violet-600 mt-1">{{ number_format($totalWeek) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cari Deskripsi</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="cth: login"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Log</label>
                    <select name="log_name"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Semua Jenis</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                                {{ strtoupper($name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('activity.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-14">
                                #</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                                Jenis</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Deskripsi</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">
                                User</th>
                            <th
                                class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">
                                Waktu</th>
                            @if (auth()->user()->isAdmin())
                                <th
                                    class="px-4 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">
                                    Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($activities as $index => $act)
                            <tr onclick="window.location='{{ route('activity.show', $act) }}'"
                                class="hover:bg-gray-50 transition-colors cursor-pointer">
                                <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                                    {{ $activities->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                                 @switch($act->log_name)
                                                     @case('auth') bg-blue-100 text-blue-700 @break
                                                     @case('app') bg-emerald-100 text-emerald-700 @break
                                                     @case('app_click') bg-violet-100 text-violet-700 @break
                                                     @case('knowledge') bg-amber-100 text-amber-700 @break
                                                     @case('siam') bg-cyan-100 text-cyan-700 @break
                                                     @case('user') bg-indigo-100 text-indigo-700 @break
                                                     @case('activity') bg-rose-100 text-rose-700 @break
                                                     @case('request') bg-purple-100 text-purple-700 @break
                                                     @default bg-gray-100 text-gray-700
                                                 @endswitch">
                                        {{ $act->log_name ?? 'general' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-800 font-medium">
                                        {{ $act->description }}
                                    </div>

                                    @php
                                        $props = $act->properties ?? collect();
                                        $old = $props['old'] ?? null;
                                        $new = $props['new'] ?? null;
                                        $eventRow = $act->event;
                                        $changedCount = 0;

                                        if ($eventRow === 'updated' && $old && $new) {
                                            foreach ($new as $k => $v) {
                                                $oldStr =
                                                    is_array($old[$k] ?? null) || is_object($old[$k] ?? null)
                                                        ? json_encode($old[$k] ?? null)
                                                        : (string) ($old[$k] ?? '');
                                                $newStr =
                                                    is_array($v) || is_object($v)
                                                        ? json_encode($v)
                                                        : (string) ($v ?? '');
                                                if ($oldStr !== $newStr && $k !== 'updated_at') {
                                                    $changedCount++;
                                                }
                                            }
                                        }
                                    @endphp

                                    @if ($changedCount > 0)
                                        <div class="mt-1.5">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                                                         bg-amber-100 text-amber-700 border border-amber-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                {{ $changedCount }} field diubah
                                            </span>
                                        </div>
                                    @endif

                                    @php
                                        $hasDiff =
                                            ($eventRow === 'updated' && $old && $new) ||
                                            ($eventRow === 'created' && $new) ||
                                            ($eventRow === 'deleted' && $old);
                                    @endphp

                                    @if ($hasDiff)
                                        <details class="mt-1.5 group" onclick="event.stopPropagation()">
                                            <summary
                                                class="text-[11px] text-amber-600 cursor-pointer hover:text-amber-700 select-none inline-flex items-center gap-1 font-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3 transition group-open:rotate-90" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                                Lihat perubahan
                                            </summary>
                                            <div
                                                class="mt-2 p-2.5 bg-amber-50 rounded-lg border border-amber-200 space-y-1.5">
                                                @if ($eventRow === 'updated')
                                                    @foreach ($new as $key => $newVal)
                                                        @php
                                                            $oldVal = $old[$key] ?? null;
                                                            $oldStr =
                                                                is_array($oldVal) || is_object($oldVal)
                                                                    ? json_encode($oldVal)
                                                                    : (string) ($oldVal ?? '');
                                                            $newStr =
                                                                is_array($newVal) || is_object($newVal)
                                                                    ? json_encode($newVal)
                                                                    : (string) ($newVal ?? '');
                                                            $isChanged = $oldStr !== $newStr;
                                                        @endphp
                                                        @if ($isChanged && !in_array($key, ['updated_at']))
                                                            <div class="flex items-center gap-2 text-[10px]">
                                                                <span
                                                                    class="font-bold text-amber-800 shrink-0 min-w-[90px]">
                                                                    {{ activityFieldLabel($key) }}
                                                                </span>
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 line-through truncate max-w-[150px]">
                                                                    {{ activityResolveValue($key, $oldVal) }}
                                                                </span>
                                                                <span class="text-gray-400">→</span>
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-semibold truncate max-w-[150px]">
                                                                    {{ activityResolveValue($key, $newVal) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif

                                                @if ($eventRow === 'created')
                                                    @foreach (collect($new)->take(5) as $key => $val)
                                                        @if (!in_array($key, ['id', 'created_at', 'updated_at']))
                                                            <div class="flex items-center gap-2 text-[10px]">
                                                                <span
                                                                    class="font-bold text-emerald-800 shrink-0 min-w-[90px]">
                                                                    {{ activityFieldLabel($key) }}
                                                                </span>
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-semibold truncate max-w-[200px]">
                                                                    {{ activityResolveValue($key, $val) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif

                                                @if ($eventRow === 'deleted')
                                                    @foreach (collect($old)->take(5) as $key => $val)
                                                        @if (!in_array($key, ['id', 'created_at', 'updated_at']))
                                                            <div class="flex items-center gap-2 text-[10px]">
                                                                <span class="font-bold text-red-800 shrink-0 min-w-[90px]">
                                                                    {{ activityFieldLabel($key) }}
                                                                </span>
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 line-through truncate max-w-[200px]">
                                                                    {{ activityResolveValue($key, $val) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($act->causer)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600
                                                        flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                                                {{ strtoupper(substr($act->causer->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-800 font-medium truncate">
                                                    {{ $act->causer->name ?? 'User' }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 truncate">
                                                    {{ $act->causer->email ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400 italic">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Guest / System
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $act->created_at->format('d M Y') }}</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $act->created_at->format('H:i') }}
                                            · {{ $act->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                @if (auth()->user()->isAdmin())
                                    <td class="px-4 py-4 text-right" onclick="event.stopPropagation()">
                                        <button type="button"
                                            @click.stop="openDeleteModal(
                                                {{ $act->id }},
                                                '{{ addslashes($act->description) }}',
                                                '{{ route('activity.destroy', $act) }}'
                                            )"
                                            class="p-1.5 rounded-lg hover:bg-red-50 transition" title="Hapus log">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">Belum ada aktivitas</p>
                                            <p class="text-sm text-gray-500 mt-1">Log akan muncul setelah ada aktivitas</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL & FORM — hanya untuk admin --}}
        @if (auth()->user()->isAdmin())
            <!-- MODAL KONFIRMASI HAPUS LOG -->
            <div x-show="showDeleteModal"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showDeleteModal = false"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex justify-center mb-4">
                        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Hapus Log Ini?</h3>
                    <p class="text-sm text-gray-500 text-center mb-6">
                        Yakin ingin menghapus log
                        <strong class="text-gray-800" x-text="deleteTarget.description"></strong>?
                    </p>
                    <div class="flex gap-2">
                        <button type="button" @click="showDeleteModal = false"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                            Batal
                        </button>
                        <button type="button" @click="confirmDelete()"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition shadow-lg shadow-red-500/30">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL KONFIRMASI BERSIHKAN SEMUA -->
            <div x-show="showClearModal"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showClearModal = false"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex justify-center mb-4">
                        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Bersihkan Semua Log?</h3>
                    <p class="text-sm text-gray-500 text-center mb-6">
                        Semua <strong class="text-gray-800">{{ number_format($totalAll) }}</strong> log akan dihapus
                        permanen.
                        Tindakan ini tidak bisa dibatalkan!
                    </p>
                    <div class="flex gap-2">
                        <button type="button" @click="showClearModal = false"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                            Batal
                        </button>
                        <button type="button" @click="confirmClear()"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition shadow-lg shadow-red-500/30">
                            Ya, Bersihkan
                        </button>
                    </div>
                </div>
            </div>

            <form id="deleteForm" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            <form id="clearForm" method="POST" action="{{ route('activity.clear') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        <!-- TOAST -->
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
            }"
            class="fixed top-24 right-6 z-[100] flex items-center gap-3 min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">
            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>
            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function activityManager() {
            return {
                showDeleteModal: false,
                showClearModal: false,
                deleteTarget: {
                    id: null,
                    description: '',
                    action: ''
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                openDeleteModal(id, description, action) {
                    this.deleteTarget = {
                        id,
                        description,
                        action
                    };
                    this.showDeleteModal = true;
                },
                confirmDelete() {
                    const form = document.getElementById('deleteForm');
                    if (!form) return;
                    form.action = this.deleteTarget.action;
                    form.submit();
                },
                confirmClear() {
                    const form = document.getElementById('clearForm');
                    if (form) form.submit();
                },
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },
                init() {
                    @if (session('success'))
                        this.showToast(@json(session('success')), 'success');
                    @endif
                    @if (session('error'))
                        this.showToast(@json(session('error')), 'error');
                    @endif
                }
            }
        }
    </script>
@endpush
