<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeAttachment extends Model
{
    /**
     * Nama tabel.
     */
    protected $table = 'knowledge_attachments';

    protected $fillable = [
        'knowledge_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // ============================================================
    // RELASI
    // ============================================================

    /**
     * Induk knowledge.
     */
    public function knowledge(): BelongsTo
    {
        return $this->belongsTo(Knowledge::class, 'knowledge_id', 'id');
    }

    // ============================================================
    // ACCESSOR
    // ============================================================

    /**
     * URL publik file.
     * Contoh: http://localhost:8000/storage/knowledge/12/file_abc123.pdf
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Ukuran file human-readable.
     * Contoh: "2.45 MB"
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Cek apakah file adalah gambar.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/');
    }

    /**
     * Icon berdasarkan tipe file (untuk tampilan).
     */
    public function getIconAttribute(): string
    {
        $type = $this->file_type ?? '';

        if (str_starts_with($type, 'image/')) {
            return '🖼️';
        }

        return match (true) {
            $type === 'application/pdf' => '📄',
            str_contains($type, 'word') => '📝',
            str_contains($type, 'excel')
            || str_contains($type, 'spreadsheet') => '📊',
            str_contains($type, 'powerpoint')
            || str_contains($type, 'presentation') => '📽️',
            str_contains($type, 'zip')
            || str_contains($type, 'rar')
            || str_contains($type, '7z') => '🗜️',
            str_starts_with($type, 'text/') => '📃',
            default => '📎',
        };
    }
}
