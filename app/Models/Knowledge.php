<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $casts = [
        'file_size' => 'integer',
    ];


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


    public function attachments(): HasMany
    {
        return $this->hasMany(KnowledgeAttachment::class, 'knowledge_id', 'id');
    }


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


    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }


    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }


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


    public function getFileCategoryAttribute(): string
    {
        return self::detectCategory($this->file_type);
    }


    public function getFileIconAttribute(): string
    {
        return self::iconForCategory($this->file_category);
    }


    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }


    public function getAttachmentCountAttribute(): int
    {
        return $this->attachments()->count();
    }


    public function getFirstAttachmentAttribute(): ?KnowledgeAttachment
    {
        return $this->attachments()->first();
    }
}
