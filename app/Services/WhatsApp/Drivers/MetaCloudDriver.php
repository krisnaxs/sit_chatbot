<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\SettingsService;
use App\Services\WhatsApp\WhatsAppDriverInterface;
use Illuminate\Support\Facades\Http;

class MetaCloudDriver implements WhatsAppDriverInterface
{
    protected string $token;
    protected string $phoneId;
    protected string $endpoint;

    public function __construct()
    {
        $this->token = SettingsService::get('whatsapp', 'token', '');
        $this->phoneId = SettingsService::get('whatsapp', 'phone_number_id', '');
        $this->endpoint = "https://graph.facebook.com/v20.0/{$this->phoneId}/messages";
    }

    public function send(string $target, string $message): bool
    {
        $res = Http::withToken($this->token)->post($this->endpoint, [
            'messaging_product' => 'whatsapp',
            'to' => $target,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);

        return $res->successful();
    }

    public function testConnection(): array
    {
        if (empty($this->token) || empty($this->phoneId)) {
            return ['success' => false, 'message' => 'Token / Phone Number ID belum diisi.'];
        }

        try {
            $res = Http::withToken($this->token)
                ->get("https://graph.facebook.com/v20.0/{$this->phoneId}");

            return $res->successful()
                ? ['success' => true, 'message' => 'Koneksi Meta Cloud OK.']
                : ['success' => false, 'message' => 'Gagal: ' . $res->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
