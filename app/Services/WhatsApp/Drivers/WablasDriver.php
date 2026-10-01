<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\SettingsService;
use App\Services\WhatsApp\WhatsAppDriverInterface;
use Illuminate\Support\Facades\Http;

class WablasDriver implements WhatsAppDriverInterface
{
    protected string $token;
    protected string $endpoint = 'https://console.wablas.com/api/send-message';

    public function __construct()
    {
        $this->token = SettingsService::get('whatsapp', 'token', '');
    }

    public function send(string $target, string $message): bool
    {
        $res = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post($this->endpoint, [
                    'phone' => $target,
                    'message' => $message,
                ]);

        return $res->successful();
    }

    public function testConnection(): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Token Wablas belum diisi.'];
        }

        try {
            $res = Http::withHeaders([
                'Authorization' => $this->token,
            ])->get('https://console.wablas.com/api/device/info');

            return $res->successful()
                ? ['success' => true, 'message' => 'Token valid.']
                : ['success' => false, 'message' => 'Gagal: ' . $res->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
