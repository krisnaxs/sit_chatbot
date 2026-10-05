<?php

namespace App\Services;

use App\Models\LearnedFaq;
use Illuminate\Support\Facades\Log;

class AutoLearningService
{
    /**
     * Threshold frekuensi untuk auto-approve.
     * Naik dari 3 → 5 supaya FAQ lebih teruji.
     */
    private const AUTO_APPROVE_FREQUENCY = 5;

    /**
     * Threshold minimum panjang jawaban (karakter).
     */
    private const MIN_ANSWER_LENGTH = 50;

    /**
     * Threshold minimum panjang pertanyaan (karakter).
     */
    private const MIN_QUESTION_LENGTH = 10;

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * Proses jawaban → cek apakah perlu disimpan sebagai FAQ baru.
     */
    public function process(string $question, string $answer, string $source = 'ai'): void
    {
        // ============================================================
        // GUARD 1: Panjang minimum
        // ============================================================
        if (mb_strlen(trim($answer)) < self::MIN_ANSWER_LENGTH) {
            return;
        }
        if (mb_strlen(trim($question)) < self::MIN_QUESTION_LENGTH) {
            return;
        }

        // ============================================================
        // GUARD 2: Skip pertanyaan dengan SN pattern
        // Contoh: "NB-T14-005", "AST-2026-0001"
        // ============================================================
        if (preg_match('/\b[A-Z]{2,}[-_][A-Z0-9]{2,}/i', $question)) {
            return;
        }

        // ============================================================
        // GUARD 3: Skip angka panjang (SN, kode unik, dll)
        // ⭐ FIX: Ubah dari \d{4,} → \d{5,}
        // Karena tahun (2024) itu 4 digit dan VALID untuk FAQ
        // Yang di-skip hanya angka 5+ digit (SN, ID, no. HP)
        // ============================================================
        if (preg_match('/\b\d{5,}\b/', $question)) {
            return;
        }

        // ============================================================
        // GUARD 4: Harus berupa pertanyaan
        // ============================================================
        $isQuestion = preg_match(
            '/\?|berapa|apa|bagaimana|gimana|kenapa|mengapa|kapan|dimana|siapa|cara|berapa|jumlah/i',
            $question
        );
        if (!$isQuestion) {
            return;
        }

        // ============================================================
        // GUARD 5: Jangan simpan jawaban yang mengandung error/penolakan
        // ⭐ BARU: Cegah nyampah dari response error
        // ============================================================
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

        // ============================================================
        // PROSES NORMALISASI
        // ============================================================
        $normalized = LearnedFaq::normalize($question);

        if (empty($normalized)) {
            return;
        }

        // ============================================================
        // CEK FAQ EXISTING
        // ============================================================
        $existing = LearnedFaq::where('question_normalized', $normalized)->first();

        if ($existing) {
            $this->updateExistingFaq($existing, $answer, $source);
        } else {
            $this->createNewFaq($question, $normalized, $answer, $source);
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

        // 1. Exact match dulu (paling akurat)
        $exact = LearnedFaq::approved()
            ->where('question_normalized', $normalized)
            ->first();

        if ($exact) {
            return $exact;
        }

        // 2. Fuzzy match
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

        // ⭐ Threshold naik dari 0.6 → 0.75 supaya lebih akurat
        return $bestScore >= 0.75 ? $best : null;
    }

    // ============================================================
    // INTERNAL HANDLERS
    // ============================================================

    /**
     * Update FAQ yang sudah ada.
     */
    private function updateExistingFaq(LearnedFaq $existing, string $answer, string $source): void
    {
        $existing->increment('frequency');

        // Auto-approve kalau frekuensi cukup tinggi
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

        // Update jawaban kalau lebih panjang DAN dari AI
        // (Jawaban dari knowledge base biasanya lebih akurat, jangan ditimpa)
        if (
            $source === 'ai'
            && mb_strlen($answer) > mb_strlen($existing->answer)
            && mb_strlen($answer) > self::MIN_ANSWER_LENGTH
        ) {
            $existing->update(['answer' => $answer]);
        }

        $existing->save();
    }

    /**
     * Buat FAQ baru.
     */
    private function createNewFaq(string $question, string $normalized, string $answer, string $source): void
    {
        // Hitung confidence awal berdasarkan source
        $initialConfidence = match ($source) {
            'admin' => 0.8,      // Admin input → high confidence
            'database' => 0.7,   // Dari DB → medium-high
            'ai' => 0.3,         // AI → low (perlu verifikasi)
            'learned' => 0.5,    // Sudah pernah dipelajari
            default => 0.3,
        };

        // Auto-approve kalau dari admin
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

    // ============================================================
    // SIMILARITY
    // ============================================================

    /**
     * Similarity score antara 2 string.
     * Return nilai 0.0 - 1.0
     */
    private function similarity(string $a, string $b): float
    {
        // Normalisasi dulu
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
                // Exact match
                if ($wa === $wb) {
                    $matched++;
                    break;
                }

                // ⭐ FIX: Levenshtein hanya untuk kata yang panjangnya mirip
                // Biar "2024" vs "2023" tidak dianggap sama (beda 1 digit)
                if (mb_strlen($wa) >= 5 && mb_strlen($wb) >= 5) {
                    if (levenshtein($wa, $wb) <= 1) {
                        $matched++;
                        break;
                    }
                }

                // Substring match — hanya kalau panjang kata mirip
                // Biar "laptop" vs "laptops" dianggap sama, tapi "type" vs "types" juga
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

        // Length ratio → makin mirip panjangnya, makin tinggi skor
        $lengthRatio = min(count($wordsA), count($wordsB)) /
            max(count($wordsA), count($wordsB));

        $finalScore = $baseScore * (0.7 + 0.3 * $lengthRatio);

        // ⭐ FIX: Turunkan skor kalau ada angka tahun yang beda
        // "laptop tahun 2024" vs "laptop tahun 2023" → harus beda
        $yearsA = $this->extractYears($a);
        $yearsB = $this->extractYears($b);
        if (!empty($yearsA) && !empty($yearsB) && $yearsA !== $yearsB) {
            $finalScore *= 0.5;  // Potong 50% skornya
        }

        return $finalScore;
    }

    /**
     * Bersihkan string untuk perbandingan similarity.
     */
    private function cleanForSimilarity(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Extract tahun (4-digit yang masuk akal: 2000-2099).
     */
    private function extractYears(string $text): array
    {
        preg_match_all('/\b(20\d{2})\b/', $text, $matches);
        return array_unique($matches[1] ?? []);
    }

    // ============================================================
    // KEYWORD EXTRACTION
    // ============================================================

    /**
     * Extract keywords.
     */
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

        // ⭐ FIX: Turunkan minimum panjang dari 4 → 3
        // Biar keyword penting seperti "type", "pc", "sn", "os" ikut ke-capture
        $words = array_filter(
            explode(' ', $text),
            fn($w) => mb_strlen($w) >= 3 && !in_array($w, $stopwords)
        );

        return array_values(array_unique($words));
    }
}
