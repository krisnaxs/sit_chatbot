<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;

class ActivityDisplay
{
    /**
     * Mapping nama field → label Indonesia.
     */
    public static array $fieldLabels = [
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
        'status' => 'Status',
        'condition_percent' => 'Kondisi (%)',
        'condition_notes' => 'Catatan Kondisi',
        'notes' => 'Catatan',
        'hostname' => 'Hostname',
        'serial_number' => 'Serial Number',
        'asset_code' => 'Kode Aset',
        'brand' => 'Brand',
        'model' => 'Model',
        'os' => 'OS',
        'os_license' => 'Lisensi OS',
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
        'condition_before' => 'Kondisi Sebelum (%)',
        'condition_after' => 'Kondisi Sesudah (%)',
        'transaction_date' => 'Tanggal Transaksi',
        'assigned_by' => 'Diserahkan Oleh',
        'received_by' => 'Diterima Oleh',
        'approved_by' => 'Disetujui Oleh',
        'requested_by' => 'Diminta Oleh',
        'moved_by' => 'Dipindahkan Oleh',
        'uploaded_by' => 'Diunggah Oleh',
    ];

    /**
     * Field yang di-skip dari resolve ID (karena enum / bukan relasi).
     */
    public static array $skipResolve = [
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

    /**
     * Mapping relasi ID → model class.
     */
    public static array $idResolvers = [
        'department_id' => Department::class,
        'location_id' => Location::class,
        'current_location_id' => Location::class,
        'from_location_id' => Location::class,
        'to_location_id' => Location::class,
        'user_id' => User::class,
        'current_user_id' => User::class,
        'assigned_by' => User::class,
        'received_by' => User::class,
        'approved_by' => User::class,
        'requested_by' => User::class,
        'moved_by' => User::class,
        'uploaded_by' => User::class,
        'asset_id' => Asset::class,
        'category_id' => AssetCategory::class,
        'vendor_id' => Vendor::class,
        'consumable_id' => Consumable::class,
    ];

    /**
     * Mapping enum → label Indonesia.
     */
    public static array $enumLabels = [
        'status' => [
            'available' => 'Tersedia',
            'in_use' => 'Dipakai',
            'loaned' => 'Dipinjam',
            'maintenance' => 'Perbaikan',
            'retired' => 'Pensiun',
            'lost' => 'Hilang',
            'open' => 'Terbuka',
            'in_progress' => 'Sedang Dikerjakan',
            'done' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'borrowed' => 'Dipinjam',
            'returned' => 'Dikembalikan',
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'overdue' => 'Terlambat',
        ],
        'ownership_type' => [
            'owned' => 'Hak Milik',
            'leased' => 'Sewa',
        ],
        'role' => [
            'admin' => 'Admin',
            'support' => 'Support',
            'user' => 'User',
        ],
        'type' => [
            'in' => 'Masuk',
            'out' => 'Keluar',
            'return' => 'Kembali',
            'preventive' => 'Preventif',
            'corrective' => 'Korektif',
            'upgrade' => 'Upgrade',
        ],
    ];

    /**
     * Label field.
     */
    public static function label(string $field): string
    {
        return static::$fieldLabels[$field]
            ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Resolve nilai field: ID → nama, enum → label Indonesia.
     */
    public static function resolve(string $field, $value): string
    {
        if (isset(static::$enumLabels[$field][$value])) {
            return static::$enumLabels[$field][$value];
        }
        if (in_array($field, static::$skipResolve)) {
            return static::formatValue($value);
        }
        if (!isset(static::$idResolvers[$field])) {
            return static::formatValue($value);
        }
        if (is_null($value) || $value === '' || $value === 0 || $value === '0') {
            return '(kosong)';
        }
        $modelClass = static::$idResolvers[$field];
        $record = $modelClass::find($value);

        if (!$record) {
            return static::formatValue($value);
        }
        $name = null;

        if (isset($record->name) && !empty($record->name)) {
            $name = $record->name;
        } elseif (isset($record->full_name) && !empty($record->full_name)) {
            $name = $record->full_name;
        } elseif (isset($record->brand) && isset($record->serial_number)) {
            $parts = array_filter([
                $record->brand,
                $record->model ?? null,
                $record->serial_number ? "({$record->serial_number})" : null,
            ]);
            $name = implode(' ', $parts);
        } elseif (isset($record->asset_code) && !empty($record->asset_code)) {
            $name = $record->asset_code;
        } elseif (isset($record->code) && !empty($record->code)) {
            $name = $record->code;
        } else {
            $name = "ID: {$value}";
        }

        return trim((string) $name);
    }

    /**
     * Format nilai generic (array, null, bool, string).
     */
    public static function formatValue($value): string
    {
        if (is_null($value))
            return '(kosong)';
        if (is_bool($value))
            return $value ? 'Ya' : 'Tidak';
        if (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_UNESCAPED_SLASHES);
            return $json === false ? '(tidak bisa ditampilkan)' : $json;
        }
        if ($value === '')
            return '(kosong)';
        return (string) $value;
    }

    /**
     * Bandingkan 2 nilai (untuk cek perubahan), aman untuk array/object.
     */
    public static function isChanged($old, $new): bool
    {
        $oldStr = is_array($old) || is_object($old)
            ? json_encode($old, JSON_UNESCAPED_SLASHES)
            : (string) ($old ?? '');
        $newStr = is_array($new) || is_object($new)
            ? json_encode($new, JSON_UNESCAPED_SLASHES)
            : (string) ($new ?? '');
        return $oldStr !== $newStr;
    }
}
