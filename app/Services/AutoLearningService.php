<?php

namespace App\Services;

use App\Models\LearnedFaq;
use Illuminate\Support\Facades\Log;

class AutoLearningService
{

    private const AUTO_APPROVE_FREQUENCY = 5;


    private const MIN_ANSWER_LENGTH = 50;


    private const MIN_QUESTION_LENGTH = 10;


    public function process(string $question, string $answer, string $source = 'ai'): void
    {
        if (mb_strlen(trim($answer)) < self::MIN_ANSWER_LENGTH) {
            return;
        }
        if (mb_strlen(trim($question)) < self::MIN_QUESTION_LENGTH) {
            return;
        }
        if (preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}/i', $question)) {
            return;
        }
        if (preg_match('/\b\d{5,}\b/', $question)) {
            return;
        }
        $isQuestion = preg_match(
            '/\?|berapa|apa|bagaimana|gimana|kenapa|mengapa|kapan|dimana|siapa|cara|berapa|jumlah/i',
            $question
        );
        if (!$isQuestion) {
            return;
        }
        $answerLower = mb_strtolower($answer);
        $badPhrases = [
            'maaf, layanan ai',
            'maaf, saya belum bisa',
            'maaf, saya tidak bisa membantu',
            'tidak ada data yang cocok',
            'error',
            'exception',
            'undefined',
            'stack trace',
            'sqlstate',
        ];
        foreach ($badPhrases as $phrase) {
            if (str_contains($answerLower, $phrase)) {
                Log::info('AutoLearning: skip jawaban mengandung frasa negatif', [
                    'phrase' => $phrase,
                    'question' => mb_substr($question, 0, 100),
                ]);
                return;
            }
        }
        $normalized = LearnedFaq::normalize($question);

        if (empty($normalized)) {
            return;
        }
        $existing = LearnedFaq::where('question_normalized', $normalized)->first();

        if ($existing) {
            $this->updateExistingFaq($existing, $answer, $source);
        } else {
            $this->createNewFaq($question, $normalized, $answer, $source);
        }
    }


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
        return $bestScore >= 0.75 ? $best : null;
    }


    private function updateExistingFaq(LearnedFaq $existing, string $answer, string $source): void
    {
        $existing->increment('frequency');
        if ($existing->frequency >= self::AUTO_APPROVE_FREQUENCY && !$existing->is_approved) {
            $existing->update([
                'is_approved' => true,
                'confidence' => min(0.95, 0.5 + ($existing->frequency * 0.08)),
                'approved_at' => now(),
            ]);

            Log::info('AutoLearning: FAQ auto-approved', [
                'question' => $existing->question,
                'frequency' => $existing->frequency,
            ]);
        }
        if (
            $source === 'ai'
            && mb_strlen($answer) > mb_strlen($existing->answer)
            && mb_strlen($answer) > self::MIN_ANSWER_LENGTH
        ) {
            $existing->update(['answer' => $answer]);
        }

        $existing->save();
    }


    private function createNewFaq(string $question, string $normalized, string $answer, string $source): void
    {
        $initialConfidence = match ($source) {
            'admin' => 0.8,      // Admin input → high confidence
            'database' => 0.7,   // Dari DB → medium-high
            'ai' => 0.3,         // AI → low (perlu verifikasi)
            'learned' => 0.5,    // Sudah pernah dipelajari
            default => 0.3,
        };
        $isApproved = in_array($source, ['admin'], true);

        LearnedFaq::create([
            'question' => $question,
            'question_normalized' => $normalized,
            'answer' => $answer,
            'source' => $source,
            'frequency' => 1,
            'confidence' => $initialConfidence,
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? now() : null,
            'related_keywords' => $this->extractKeywords($question),
        ]);

        Log::info('AutoLearning: FAQ baru dibuat', [
            'question' => mb_substr($question, 0, 100),
            'source' => $source,
            'confidence' => $initialConfidence,
        ]);
    }


    private function similarity(string $a, string $b): float
    {
        $a = $this->cleanForSimilarity($a);
        $b = $this->cleanForSimilarity($b);

        $wordsA = array_filter(explode(' ', $a), fn($w) => mb_strlen($w) >= 3);
        $wordsB = array_filter(explode(' ', $b), fn($w) => mb_strlen($w) >= 3);

        if (empty($wordsA) || empty($wordsB)) {
            return 0;
        }

        $matched = 0;
        foreach ($wordsA as $wa) {
            foreach ($wordsB as $wb) {
                if ($wa === $wb) {
                    $matched++;
                    break;
                }
                if (mb_strlen($wa) >= 5 && mb_strlen($wb) >= 5) {
                    if (levenshtein($wa, $wb) <= 1) {
                        $matched++;
                        break;
                    }
                }
                if (
                    mb_strlen($wa) >= 4
                    && mb_strlen($wb) >= 4
                    && (str_contains($wb, $wa) || str_contains($wa, $wb))
                ) {
                    $matched++;
                    break;
                }
            }
        }

        $baseScore = $matched / max(count($wordsA), count($wordsB));
        $lengthRatio = min(count($wordsA), count($wordsB)) /
            max(count($wordsA), count($wordsB));

        $finalScore = $baseScore * (0.7 + 0.3 * $lengthRatio);
        $yearsA = $this->extractYears($a);
        $yearsB = $this->extractYears($b);
        if (!empty($yearsA) && !empty($yearsB) && $yearsA !== $yearsB) {
            $finalScore *= 0.5;  // Potong 50% skornya
        }

        return $finalScore;
    }


    private function cleanForSimilarity(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }


    private function extractYears(string $text): array
    {
        preg_match_all('/\b(20\d{2})\b/', $text, $matches);
        return array_unique($matches[1] ?? []);
    }


    private function extractKeywords(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

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
            'ada',
            'adalah',
            'dengan',
            'pada',
            'saya',
            'kamu',
            'anda',
            'juga',
            'bisa',
            'akan',
            'sudah',
            'telah',
            'masih',
            'saja',
            'aja',
            'dong',
            'sih',
            'ya',
        ];
        $words = array_filter(
            explode(' ', $text),
            fn($w) => mb_strlen($w) >= 3 && !in_array($w, $stopwords)
        );

        return array_values(array_unique($words));
    }
}
