<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class OllamaResponseFormatter
{
    /**
     * Format jawaban. Kalau $rawAnswer sudah bagus (dari QueryService),
     * biasanya tidak perlu Ollama lagi. Tapi kalau mau dirapikan, panggil ini.
     */
    public function format(string $userMessage, string $rawAnswer): string
    {
        // Kalau jawaban sudah cukup jelas (ada **markdown**), langsung return
        if (str_contains($rawAnswer, '**') || strlen($rawAnswer) > 200) {
            return $rawAnswer;
        }

        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = config('services.ollama.model', 'llama3.2');

        $prompt = "Pertanyaan user: {$userMessage}\n\n"
            . "Data dari sistem:\n{$rawAnswer}\n\n"
            . "Susun jawaban SINGKAT dalam Bahasa Indonesia (1-2 kalimat). "
            . "Jangan menambah data di luar yang diberikan.";

        try {
            $response = Http::timeout(30)->post("{$url}/api/chat", [
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
            // silent fail
        }

        return $rawAnswer;
    }
}
