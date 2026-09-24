<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chat';
    public $timestamps = false;
    protected $fillable = [
        'session_id',
        'pesan',
        'jawaban',
        'sumber',
        'waktu',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * Deteksi kategori file dari MIME type
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
     * Icon emoji untuk kategori
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
}
