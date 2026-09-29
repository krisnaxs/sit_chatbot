<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaResponseFormatter
{
    /**
     * Format jawaban. HANYA dipakai untuk merapikan jawaban pendek.
     * Jangan dipakai di jalur utama — boros latency.
     */
    public function format(string $userMessage, string $rawAnswer): string
    {
        // Kalau jawaban sudah cukup jelas (ada **markdown** atau panjang), return as-is
        if (str_contains($rawAnswer, '**') || strlen($rawAnswer) > 200) {
            return $rawAnswer;
        }

        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = config('services.ollama.model', 'llama3.2');
        $timeout = (int) config('services.ollama.timeout', 30);

        $prompt = "Pertanyaan user: {$userMessage}\n\n"
            . "Data dari sistem:\n{$rawAnswer}\n\n"
            . "Susun jawaban SINGKAT dalam Bahasa Indonesia (1-2 kalimat). "
            . "Jangan menambah data di luar yang diberikan.";

        try {
            $response = Http::timeout($timeout)->post("{$url}/api/chat", [
                'model' => $model,
                'stream' => false,
                'options' => ['temperature' => 0.3],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($response->successful()) {
                return trim($response->json('message.content', $rawAnswer));
            }
        } catch (\Throwable $e) {
            Log::warning('OllamaResponseFormatter silent fail: ' . $e->getMessage());
        }

        return $rawAnswer;
    }
}
