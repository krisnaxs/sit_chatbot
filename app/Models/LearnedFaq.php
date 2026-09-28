<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearnedFaq extends Model
{
    protected $fillable = [
        'question',
        'question_normalized',
        'answer',
        'source',
        'frequency',
        'confidence',
        'is_approved',
        'related_keywords',
        'category',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'related_keywords' => 'array',
        'confidence' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Normalisasi teks pertanyaan.
     */
    public static function normalize(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        $stopwords = [
            'yang',
            'dan',
            'atau',
            'di',
            'ke',
            'dari',
            'untuk',
            'apa',
            'itu',
            'ini',
            'saya',
            'kamu',
            'anda',
            'dong',
            'sih',
            'ya',
            'kah',
            'lah',
        ];

        $words = array_filter(
            explode(' ', $text),
            fn($w) => strlen($w) >= 3 && !in_array($w, $stopwords)
        );

        return implode(' ', $words);
    }

    /**
     * Scope: hanya FAQ yang approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: urut by frequency tertinggi.
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('frequency');
    }
}
