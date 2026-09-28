@php
    // ═══════════════════════════════════════════════════════
    // HELPER FUNCTIONS — Activity Log Display
    // ═══════════════════════════════════════════════════════

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
                'updated_at' => 'Terakhir Diubah',
                'created_at' => 'Dibuat',
                'deleted_at' => 'Dihapus',
            ];
            return $map[$field] ?? ucwords(str_replace('_', ' ', $field));
        }
    }

    if (!function_exists('activityResolveValue')) {
        function activityResolveValue(string $field, $value): string
        {
            // Skip fields yang bukan relasi ID
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
            ];
            if (in_array($field, $skipFields)) {
                return activityFormatValue($value);
            }

            // Mapping relasi ID → model
            $idResolvers = [
                // Departments
                'department_id' => \App\Models\Department::class,

                // Locations
                'location_id' => \App\Models\Location::class,
                'current_location_id' => \App\Models\Location::class,
                'from_location_id' => \App\Models\Location::class,
                'to_location_id' => \App\Models\Location::class,

                // Users (berbagai role)
                'user_id' => \App\Models\User::class,
                'current_user_id' => \App\Models\User::class,
                'assigned_by' => \App\Models\User::class,
                'received_by' => \App\Models\User::class,
                'approved_by' => \App\Models\User::class,
                'requested_by' => \App\Models\User::class,
                'moved_by' => \App\Models\User::class,
                'uploaded_by' => \App\Models\User::class,

                // Assets & categories
                'asset_id' => \App\Models\Asset::class,
                'category_id' => \App\Models\AssetCategory::class,

                // Vendors
                'vendor_id' => \App\Models\Vendor::class,

                // Consumables
                'consumable_id' => \App\Models\Consumable::class,
            ];

            if (!isset($idResolvers[$field])) {
                return activityFormatValue($value);
            }

            if (is_null($value) || $value === '' || $value === 0 || $value === '0') {
                return '(kosong)';
            }

            $modelClass = $idResolvers[$field];
            $record = $modelClass::find($value);

            if (!$record) {
                return activityFormatValue($value);
            }

            // Resolve nama dengan prioritas
            $name = null;

            // 1. Cek field name
            if (isset($record->name) && !empty($record->name)) {
                $name = $record->name;
            }
            // 2. Cek full_name (locations)
            elseif (isset($record->full_name) && !empty($record->full_name)) {
                $name = $record->full_name;
            }
            // 3. Asset: brand + model + serial
            elseif (isset($record->brand) && isset($record->serial_number)) {
                $parts = array_filter([
                    $record->brand,
                    $record->model ?? null,
                    $record->serial_number ? "({$record->serial_number})" : null,
                ]);
                $name = implode(' ', $parts);
            }
            // 4. Cek asset_code
            elseif (isset($record->asset_code) && !empty($record->asset_code)) {
                $name = $record->asset_code;
            }
            // 5. Cek code (categories, departments)
            elseif (isset($record->code) && !empty($record->code)) {
                $name = $record->code;
            }
            // 6. Fallback: ID
            else {
                $name = "ID: {$value}";
            }

            return trim((string) $name);
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
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Activity Log - Admin</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans">

    <x-header title="Detail Activity Log" placeholder="Cari log..." />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-3xl mx-auto p-6 mt-4 lg:mr-auto transition-all duration-300">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('activity.index') }}" class="hover:text-blue-600 transition">Activity Log</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-semibold">Log #{{ $activity->id }}</span>
        </nav>

        <!-- HEADER CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">

            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600
                                flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Detail Log</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ID: <span class="font-mono font-semibold">#{{ $activity->id }}</span>
                        </p>
                    </div>
                </div>

                {{-- Badge Jenis Log --}}
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-lg
                             text-xs font-bold uppercase tracking-wider
                             @switch($activity->log_name)
                                 @case('auth') bg-blue-100 text-blue-700 @break
                                 @case('app') bg-emerald-100 text-emerald-700 @break
                                 @case('app_click') bg-violet-100 text-violet-700 @break
                                 @case('knowledge') bg-amber-100 text-amber-700 @break
                                 @case('siam') bg-cyan-100 text-cyan-700 @break
                                 @case('user') bg-indigo-100 text-indigo-700 @break
                                 @case('activity') bg-rose-100 text-rose-700 @break
                                 @default bg-gray-100 text-gray-700
                             @endswitch">
                    {{ $activity->log_name ?? 'general' }}
                </span>
            </div>

            <!-- DESKRIPSI -->
            <div
                class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-violet-50
                        border border-blue-100 mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                    Deskripsi Aktivitas
                </p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $activity->description }}
                </p>
            </div>

            <!-- INFO GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Waktu --}}
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Waktu</p>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $activity->created_at->format('d M Y, H:i:s') }}
                        </p>
                        <p class="text-[11px] text-gray-400">
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                {{-- User --}}
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-violet-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">User</p>
                        @if ($activity->causer)
                            <p class="text-sm font-bold text-gray-900 truncate">
                                {{ $activity->causer->name ?? 'User' }}
                            </p>
                            <p class="text-[11px] text-gray-400 truncate">
                                {{ $activity->causer->email ?? '' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500 italic">Guest / System</p>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- PERUBAHAN DATA — HIGHLIGHT OLD → NEW --}}
        {{-- ============================================================ --}}
        @php
            $allProps = $activity->properties ?? collect();
            $old = $allProps['old'] ?? null;
            $new = $allProps['new'] ?? null;
            $event = $activity->event;
        @endphp

        @if (($event === 'updated' && $old && $new) || ($event === 'created' && $new) || ($event === 'deleted' && $old))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div
                        class="w-10 h-10 rounded-lg
                        @if ($event === 'created') bg-emerald-100
                        @elseif ($event === 'updated') bg-amber-100
                        @elseif ($event === 'deleted') bg-red-100 @endif
                        flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5
                            @if ($event === 'created') text-emerald-600
                            @elseif ($event === 'updated') text-amber-600
                            @elseif ($event === 'deleted') text-red-600 @endif"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Perubahan Data</h2>
                        <p class="text-xs text-gray-500">
                            @if ($event === 'created')
                                Data baru dibuat
                            @elseif ($event === 'updated')
                                Highlight field yang berubah
                            @elseif ($event === 'deleted')
                                Data dihapus
                            @endif
                        </p>
                    </div>
                </div>

                {{-- CREATED: tampilkan semua field baru --}}
                @if ($event === 'created')
                    <div class="space-y-2">
                        @foreach ($new as $key => $value)
                            @if (!in_array($key, ['id', 'created_at', 'updated_at']))
                                @php
                                    $fieldLabel = activityFieldLabel($key);
                                    $display = activityResolveValue($key, $value);
                                @endphp
                                <div class="flex gap-3 p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                    <span class="text-xs font-bold text-emerald-700 shrink-0 min-w-[140px] pt-0.5">
                                        {{ $fieldLabel }}
                                    </span>
                                    <span class="text-xs text-emerald-800 flex-1 break-all">
                                        {{ $display }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- UPDATED: diff old → new dengan highlight --}}
                @if ($event === 'updated')
                    <div class="space-y-2">
                        @foreach ($new as $key => $newVal)
                            @php
                                $oldVal = $old[$key] ?? null;

                                // Handle array/object aman
                                $oldStr =
                                    is_array($oldVal) || is_object($oldVal)
                                        ? json_encode($oldVal, JSON_UNESCAPED_SLASHES)
                                        : (string) ($oldVal ?? '');
                                $newStr =
                                    is_array($newVal) || is_object($newVal)
                                        ? json_encode($newVal, JSON_UNESCAPED_SLASHES)
                                        : (string) ($newVal ?? '');
                                $isChanged = $oldStr !== $newStr;

                                // Resolve tampilan
                                $fieldLabel = activityFieldLabel($key);
                                $oldDisplay = activityResolveValue($key, $oldVal);
                                $newDisplay = activityResolveValue($key, $newVal);
                            @endphp
                            @if ($isChanged && !in_array($key, ['updated_at']))
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                                    <span class="text-xs font-bold text-amber-800 shrink-0 min-w-[140px] pt-0.5">
                                        {{ $fieldLabel }}
                                    </span>
                                    <div class="flex-1 flex flex-wrap items-center gap-2 text-xs">
                                        {{-- Nilai lama (merah) --}}
                                        <span class="px-2 py-1 rounded bg-red-100 text-red-700 line-through">
                                            {{ $oldDisplay }}
                                        </span>

                                        {{-- Arrow --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>

                                        {{-- Nilai baru (hijau) --}}
                                        <span class="px-2 py-1 rounded bg-green-100 text-green-700 font-bold">
                                            {{ $newDisplay }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- DELETED: tampilkan semua field yang dihapus --}}
                @if ($event === 'deleted')
                    <div class="space-y-2">
                        @foreach ($old as $key => $value)
                            @if (!in_array($key, ['id', 'created_at', 'updated_at']))
                                @php
                                    $fieldLabel = activityFieldLabel($key);
                                    $display = activityResolveValue($key, $value);
                                @endphp
                                <div class="flex gap-3 p-3 rounded-lg bg-red-50 border border-red-100">
                                    <span class="text-xs font-bold text-red-700 shrink-0 min-w-[140px] pt-0.5">
                                        {{ $fieldLabel }}
                                    </span>
                                    <span class="text-xs text-red-800 flex-1 break-all line-through">
                                        {{ $display }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- PROPERTIES (skip old & new karena sudah ditampilkan di diff) -->
        @php
            $props = $allProps->except(['old', 'new']);
        @endphp

        @if ($props->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Properties</h2>
                        <p class="text-xs text-gray-500">Data tambahan terkait log ini</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($props as $key => $value)
                        <div
                            class="flex gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100 hover:bg-gray-100 transition">
                            <span class="text-xs font-bold text-gray-500 shrink-0 min-w-[110px] font-mono pt-0.5">
                                {{ $key }}
                            </span>
                            <span class="text-xs text-gray-800 font-mono flex-1 break-all leading-relaxed">
                                @if (is_array($value) || is_object($value))
                                    <pre class="whitespace-pre-wrap">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @elseif (is_null($value))
                                    <span class="text-gray-400 italic">null</span>
                                @elseif (is_bool($value))
                                    {{ $value ? 'true' : 'false' }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- SUBJECT -->
        @if ($activity->subject)
            @php
                $subject = $activity->subject;
                $subjectType = class_basename($activity->subject_type);
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900">Subject</h2>
                        <p class="text-xs text-gray-500">Objek yang dikenai aksi</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span
                            class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider
                                     bg-emerald-100 text-emerald-700 border border-emerald-200">
                            {{ $subjectType }}
                        </span>
                        <span class="font-mono text-xs text-gray-500">
                            #{{ $activity->subject_id }}
                        </span>
                    </div>

                    {{-- Info subject human-readable --}}
                    @if (isset($subject->name))
                        <p class="text-sm font-bold text-gray-900 mb-1">{{ $subject->name }}</p>
                    @endif
                    @if (isset($subject->email))
                        <p class="text-xs text-gray-500 mb-2">{{ $subject->email }}</p>
                    @endif
                    @if (isset($subject->serial_number) && !isset($subject->name))
                        <p class="text-sm font-bold text-gray-900 mb-1 font-mono">{{ $subject->serial_number }}</p>
                    @endif
                    @if (isset($subject->brand) && !isset($subject->name))
                        <p class="text-sm font-bold text-gray-900 mb-1">
                            {{ $subject->brand }} {{ $subject->model ?? '' }}
                        </p>
                    @endif

                    @if (method_exists($subject, 'toArray'))
                        <details class="group mt-2">
                            <summary
                                class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 select-none inline-flex items-center gap-1 font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3 transition group-open:rotate-90" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                Lihat detail model
                            </summary>
                            <div
                                class="mt-2 p-3 bg-white rounded-lg border border-gray-200
                                        text-[11px] text-gray-700 font-mono overflow-x-auto">
                                <pre class="whitespace-pre-wrap break-all">@php
                                    try {
                                        echo json_encode(
                                            $subject->toArray(),
                                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR,
                                        );
                                    } catch (\Throwable $e) {
                                        echo '(tidak bisa ditampilkan)';
                                    }
                                @endphp</pre>
                            </div>
                        </details>
                    @endif
                </div>
            </div>
        @endif

        <!-- NAVIGASI PREV / NEXT -->
        <div class="flex items-center justify-between gap-3 mb-4">
            @if ($prev)
                <a href="{{ route('activity.show', $prev) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-white border border-gray-200 hover:bg-gray-50
                           hover:border-gray-300
                           text-gray-700 font-semibold text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Sebelumnya
                    <span class="text-[10px] text-gray-400 font-mono">#{{ $prev->id }}</span>
                </a>
            @else
                <div></div>
            @endif

            @if ($next)
                <a href="{{ route('activity.show', $next) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-white border border-gray-200 hover:bg-gray-50
                           hover:border-gray-300
                           text-gray-700 font-semibold text-sm transition shadow-sm">
                    <span class="text-[10px] text-gray-400 font-mono">#{{ $next->id }}</span>
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">

            <a href="{{ route('activity.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                       border border-gray-300 text-gray-700 font-semibold text-sm
                       hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar
            </a>

            @if (auth()->user()->isAdmin())
                <button type="button"
                    onclick="if(confirm('Yakin ingin menghapus log ini?')) document.getElementById('deleteShowForm').submit();"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           bg-red-600 hover:bg-red-700 text-white font-semibold text-sm
                           shadow-lg shadow-red-500/30 transition
                           hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Log Ini
                </button>

                <form id="deleteShowForm" method="POST" action="{{ route('activity.destroy', $activity) }}"
                    class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

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
