<?php

namespace App\Services\WhatsApp;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected WhatsAppDriverInterface $driver;

    public function __construct()
    {
        $provider = SettingsService::get('whatsapp', 'provider', 'fonnte');
        $this->driver = $this->resolveDriver($provider);
    }

    protected function resolveDriver(string $provider): WhatsAppDriverInterface
    {
        return match ($provider) {
            'fonnte' => new Drivers\FonnteDriver(),
            'wablas' => new Drivers\WablasDriver(),
            'meta' => new Drivers\MetaCloudDriver(),   // siap nanti
            'log' => new Drivers\LogDriver(),         // untuk testing lokal
            default => new Drivers\LogDriver(),
        };
    }

    public function send(?string $target, string $message): bool
    {
        $target = $this->normalize($target);

        if (!$target) {
            Log::warning('[WA] Nomor target kosong/invalid');
            return false;
        }

        if (!SettingsService::get('whatsapp', 'enabled', false)) {
            Log::info("[WA DISABLED] → {$target}: {$message}");
            return false;
        }
        $whitelist = array_filter(explode(',', SettingsService::get('whatsapp', 'whitelist', '') ?? ''));
        if (!empty($whitelist) && !in_array($target, $whitelist)) {
            Log::info("[WA SKIP - not whitelisted] {$target}");
            return false;
        }

        try {
            return $this->driver->send($target, $message);
        } catch (\Throwable $e) {
            Log::error('[WA] ' . $e->getMessage());
            return false;
        }
    }

    public function testConnection(): array
    {
        return $this->driver->testConnection();
    }

    protected function normalize(?string $phone): ?string
    {
        if (!$phone)
            return null;

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
