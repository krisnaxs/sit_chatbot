<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Knowledge extends Model
{
    use LogsActivity;
    use Searchable;

    protected $table = 'knowledge';
    public $timestamps = false;

    protected $fillable = [
        'kata_kunci',
        'jawaban',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * Field yang di-index ke TNTSearch.
     * Wajib ada 'id' agar Scout bisa mapping hasil pencarian ke model.
     * 'kata_kunci' dan 'jawaban' agar pencarian bisa mencocokkan keduanya.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'kata_kunci' => $this->kata_kunci,
            'jawaban' => $this->jawaban,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kata_kunci', 'jawaban', 'file_path', 'file_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('knowledge');
    }

    /**
     * Deteksi kategori file dari MIME type
     * Return: 'image' | 'pdf' | 'word' | 'excel' | 'powerpoint' | 'archive' | 'other'
     */
    public static function detectCategory(?string $mimeType): string
    {
        if (!$mimeType)
            return 'other';

        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            $mimeType === 'application/pdf' => 'pdf',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'word',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') || $mimeType === 'text/csv' => 'excel',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'powerpoint',
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, '7z') => 'archive',
            default => 'other',
        };
    }

    /**
     * Emoji icon untuk kategori file
     */
    public static function iconForCategory(string $category): string
    {
        return match ($category) {
            'image' => '🖼️',
            'pdf' => '📄',
            'word' => '📝',
            'excel' => '📊',
            'powerpoint' => '📽️',
            'archive' => '📦',
            default => '📎',
        };
    }

    /**
     * Helper: apakah knowledge punya file?
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    /**
     * Helper: URL file publik
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Helper: ukuran file yang sudah diformat (1.2 MB, 245 KB, dll)
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size)
            return '-';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    /**
     * Helper: kategori file (from instance)
     */
    public function getFileCategoryAttribute(): string
    {
        return self::detectCategory($this->file_type);
    }

    /**
     * Helper: icon file (from instance)
     */
    public function getFileIconAttribute(): string
    {
        return self::iconForCategory($this->file_category);
    }
}
