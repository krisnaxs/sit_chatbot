<?php

namespace App\Services;

use App\Models\LearnedFaq;
use Illuminate\Support\Facades\Log;

class AutoLearningService
{
    /**
     * Proses jawaban → cek apakah perlu disimpan sebagai FAQ baru.
     */
    public function process(string $question, string $answer, string $source = 'ai'): void
    {
        if (strlen(trim($answer)) < 50) {
            return;
        }
        if (strlen(trim($question)) < 10) {
            return;
        }
        if (preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}/i', $question)) {
            return;
        }
        if (preg_match('/\b\d{4,}\b/', $question)) {
            return;
        }
        $isQuestion = preg_match('/\?|berapa|apa|bagaimana|gimana|kenapa|mengapa|kapan|dimana|siapa|cara/i', $question);
        if (!$isQuestion) {
            return;
        }

        $normalized = LearnedFaq::normalize($question);

        if (empty($normalized)) {
            return;
        }
        $existing = LearnedFaq::where('question_normalized', $normalized)->first();

        if ($existing) {
            $existing->increment('frequency');
            if ($existing->frequency >= 3 && !$existing->is_approved) {
                $existing->update([
                    'is_approved' => true,
                    'confidence' => min(0.95, 0.5 + ($existing->frequency * 0.1)),
                    'approved_at' => now(),
                ]);

                Log::info('FAQ auto-approved', [
                    'question' => $existing->question,
                    'frequency' => $existing->frequency,
                ]);
            }
            if ($source === 'ai' && strlen($answer) > strlen($existing->answer)) {
                $existing->update(['answer' => $answer]);
            }
        } else {
            LearnedFaq::create([
                'question' => $question,
                'question_normalized' => $normalized,
                'answer' => $answer,
                'source' => $source,
                'frequency' => 1,
                'confidence' => 0.3,
                'is_approved' => false,
                'related_keywords' => $this->extractKeywords($question),
            ]);
        }
    }

    /**
     * Cari FAQ yang cocok.
     */
    public function findAnswer(string $question): ?LearnedFaq
    {
        $normalized = LearnedFaq::normalize($question);

        if (empty($normalized)) {
            return null;
        }
        $exact = LearnedFaq::approved()
            ->where('question_normalized', $normalized)
            ->first();

        if ($exact) {
            return $exact;
        }
        $candidates = LearnedFaq::approved()
            ->popular()
            ->limit(30)
            ->get();

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $faq) {
            $score = $this->similarity($normalized, $faq->question_normalized);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return $bestScore >= 0.6 ? $best : null;
    }

    /**
     * Similarity score antara 2 string.
     */
    private function similarity(string $a, string $b): float
    {
        $wordsA = array_filter(explode(' ', $a), fn($w) => strlen($w) >= 3);
        $wordsB = array_filter(explode(' ', $b), fn($w) => strlen($w) >= 3);

        if (empty($wordsA) || empty($wordsB)) {
            return 0;
        }

        $matched = 0;
        foreach ($wordsA as $wa) {
            foreach ($wordsB as $wb) {
                if ($wa === $wb || levenshtein($wa, $wb) <= 1 || str_contains($wb, $wa) || str_contains($wa, $wb)) {
                    $matched++;
                    break;
                }
            }
        }

        $baseScore = $matched / max(count($wordsA), count($wordsB));
        $lengthRatio = min(count($wordsA), count($wordsB)) / max(count($wordsA), count($wordsB));
        return $baseScore * (0.7 + 0.3 * $lengthRatio);
    }

    /**
     * Extract keywords.
     */
    private function extractKeywords(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        $stopwords = ['yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'apa', 'itu', 'ini'];

        $words = array_filter(
            explode(' ', $text),
            fn($w) => strlen($w) >= 4 && !in_array($w, $stopwords)
        );

        return array_values(array_unique($words));
    }
}
