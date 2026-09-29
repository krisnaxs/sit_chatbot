<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaIntentParser
{
    public function parse(string $pesan): ?array
    {
        $url = rtrim(config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
        $model = config('services.ollama.model', 'llama3.2');
        $timeout = (int) config('services.ollama.intent_timeout', 8);

        if (empty($model)) {
            Log::warning('OllamaIntentParser: model kosong');
            return null;
        }

        $system = $this->buildSystemPrompt();

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(3)
                ->post("{$url}/api/chat", [
                    'model' => $model,
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'temperature' => 0.1,
                        'num_predict' => 256,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $pesan],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('OllamaIntentParser: HTTP fail', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $content = $response->json('message.content', '');

            // Sanitasi: buang markdown code fence kalau ada
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($content));

            $parsed = json_decode($content, true);

            if (!is_array($parsed) || empty($parsed['intent'])) {
                Log::warning('OllamaIntentParser: JSON invalid', [
                    'raw' => mb_substr($content, 0, 200),
                ]);
                return null;
            }

            // Normalisasi params
            if (!isset($parsed['params']) || !is_array($parsed['params'])) {
                $parsed['params'] = [];
            }

            if (($parsed['confidence'] ?? 1) < 0.5) {
                Log::info('OllamaIntentParser: low confidence', [
                    'intent' => $parsed['intent'],
                    'confidence' => $parsed['confidence'] ?? null,
                ]);
                return null;
            }

            return $parsed;

        } catch (\Throwable $e) {
            Log::error('OllamaIntentParser exception: ' . $e->getMessage());
            return null;
        }
    }

    protected function buildSystemPrompt(): string
    {
        $categories = \App\Models\AssetCategory::where('is_consumable', false)
            ->pluck('name')->take(30)->implode(', ') ?: '(kosong)';

        $locations = \App\Models\Location::where('is_active', true)
            ->pluck('room')->take(30)->implode(', ') ?: '(kosong)';

        return <<<PROMPT
Kamu adalah parser intent untuk sistem Asset Management (SIAM).

Tugas: ubah pertanyaan user Bahasa Indonesia menjadi JSON.
Output HANYA JSON valid, tanpa penjelasan, tanpa markdown.

DAFTAR INTENT & PARAMETER:

1. count_asset_by_status
   params: status (wajib), category?, brand?, location?
2. list_asset_by_status
   params: status (wajib), category?, brand?, location?
3. count_asset_by_ownership
   params: ownership_type ("owned"|"leased"), category?
4. list_asset_by_category
   params: category (wajib)
5. list_asset_by_brand
   params: brand (wajib)
6. list_asset_by_user
   params: user_name (wajib), field ("sn"|"hostname"|"asset_code"|"all")
   - Contoh: "Dewi Lestari sn nya berapa" → field: "sn"
   - Contoh: "aset yang dipegang Budi" → field: "all"
   - Contoh: "hostname laptop Siti" → field: "hostname"
7. who_holds_asset
   params: identifier (SN/hostname/asset_code, wajib)
8. find_asset
   params: identifier (SN/hostname/asset_code, wajib)
9. count_maintenance
10. list_overdue_loans
11. list_active_loans
12. low_stock_consumable
13. top_users_with_assets
14. top_locations
15. total_status_summary
16. count_users
17. count_vendors
18. other  ← kalau tidak ada yang cocok

STATUS VALID (harus salah satu): available, in_use, loaned, maintenance, retired, lost
Mapping bahasa Indonesia → status:
- "tersedia", "ready", "kosong" → available
- "dipakai", "digunakan", "aktif" → in_use
- "dipinjam", "pinjam" → loaned
- "rusak", "perbaikan", "servis", "maintenance" → maintenance
- "pensiun", "tua" → retired
- "hilang" → lost

OWNERSHIP:
- "milik", "beli", "owned", "hak milik" → owned
- "sewa", "rental", "leased" → leased

KATEGORI yang ADA: {$categories}
LOKASI yang ADA: {$locations}

FORMAT OUTPUT:
{"intent":"nama_intent","params":{...},"confidence":0.0-1.0}

CONTOH:

User: "berapa laptop rusak?"
{"intent":"count_asset_by_status","params":{"status":"maintenance","category":"laptop"},"confidence":0.95}

User: "berapa laptop rusak di jakarta?"
{"intent":"count_asset_by_status","params":{"status":"maintenance","category":"laptop","location":"jakarta"},"confidence":0.95}

User: "siapa yang pegang NB-T14-005?"
{"intent":"who_holds_asset","params":{"identifier":"NB-T14-005"},"confidence":0.98}

User: "info laptop NB-T14-005"
{"intent":"find_asset","params":{"identifier":"NB-T14-005"},"confidence":0.95}

User: "berapa aset hak milik?"
{"intent":"count_asset_by_ownership","params":{"ownership_type":"owned"},"confidence":0.9}

User: "Dewi Lestari sn nya berapa"
{"intent":"list_asset_by_user","params":{"user_name":"Dewi Lestari","field":"sn"},"confidence":0.95}

User: "hostname laptop Budi Santoso"
{"intent":"list_asset_by_user","params":{"user_name":"Budi Santoso","field":"hostname"},"confidence":0.92}

User: "aset yang dipegang Siti Aminah"
{"intent":"list_asset_by_user","params":{"user_name":"Siti Aminah","field":"all"},"confidence":0.9}

User: "berapa user aktif?"
{"intent":"count_users","params":{},"confidence":0.9}

User: "cara reset password?"
{"intent":"other","params":{},"confidence":0.9}
PROMPT;
    }
}
