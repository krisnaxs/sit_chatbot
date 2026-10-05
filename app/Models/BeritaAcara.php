<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaAcara extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_ba',
        'jenis',
        'asset_id',
        'asset_assignment_id',
        'asset_loan_id',
        'pihak_pertama_id',
        'pihak_pertama_jabatan',
        'pihak_pertama_nip',
        'pihak_kedua_id',
        'pihak_kedua_jabatan',
        'pihak_kedua_nip',
        'kategori_aset',
        'merk',
        'model',
        'serial_number',
        'hostname',
        'detail_tambahan',
        'jumlah',
        'condition_percent',
        'notes',
        'tanggal_ba',
        'tempat_ba',
        'pdf_path',
        'created_by',
    ];

    protected $casts = [
        'tanggal_ba' => 'date',
        'detail_tambahan' => 'array', // 🆕 auto-cast JSON
    ];

    // Relations
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    public function assignment()
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }
    public function loan()
    {
        return $this->belongsTo(AssetLoan::class, 'asset_loan_id');
    }
    public function pihakPertama()
    {
        return $this->belongsTo(User::class, 'pihak_pertama_id');
    }
    public function pihakKedua()
    {
        return $this->belongsTo(User::class, 'pihak_kedua_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 🆕 Label judul BA (dinamis sesuai kategori)
    public function getJudulAttribute(): string
    {
        $jenisLabel = $this->jenis === 'serah_terima' ? 'Serah Terima' : 'Pengembalian';
        return "Berita Acara {$jenisLabel} {$this->kategori_aset}";
    }

    // 🆕 Detail tambahan sebagai array siap render
    public function getDetailListAttribute(): array
    {
        return $this->detail_tambahan ?? [];
    }

    // Generate nomor BA
    public static function generateNomor(string $jenis): string
    {
        $prefix = $jenis === 'serah_terima' ? 'SIS-BAST' : 'SIS-BAP';
        $year = now()->format('Y');

        // 🆕 Ambil nomor tertinggi — termasuk yang soft-deleted
        // Pakai withTrashed() biar tidak duplicate
        $last = static::withTrashed()
            ->where('nomor_ba', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw('CAST(SUBSTRING(nomor_ba, -4) AS UNSIGNED) DESC')
            ->first();

        $number = $last ? ((int) substr($last->nomor_ba, -4)) + 1 : 1;

        // Loop sampai dapat nomor unik (safety)
        while (static::withTrashed()->where('nomor_ba', sprintf('%s-%s-%04d', $prefix, $year, $number))->exists()) {
            $number++;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $number);
    }
}
