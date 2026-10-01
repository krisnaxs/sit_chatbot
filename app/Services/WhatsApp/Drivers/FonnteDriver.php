<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\SettingsService;
use App\Services\WhatsApp\WhatsAppDriverInterface;
use Illuminate\Support\Facades\Http;

class FonnteDriver implements WhatsAppDriverInterface
{
    protected string $token;
    protected string $endpoint = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = SettingsService::get('whatsapp', 'token', '');
    }

    public function send(string $target, string $message): bool
    {
        $res = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post($this->endpoint, [
                    'target' => $target,
                    'message' => $message,
                ]);

        return $res->successful();
    }

    public function testConnection(): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Token Fonnte belum diisi.'];
        }

        try {
            $res = Http::withHeaders([
                'Authorization' => $this->token,
            ])->get('https://api.fonnte.com/device');

            if ($res->successful()) {
                return ['success' => true, 'message' => 'Token valid. Device info: ' . json_encode($res->json())];
            }

            return ['success' => false, 'message' => 'Gagal: ' . $res->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
