<?php

namespace App\Services\WhatsApp\Drivers;

use App\Services\WhatsApp\WhatsAppDriverInterface;
use Illuminate\Support\Facades\Log;

class LogDriver implements WhatsAppDriverInterface
{
    public function send(string $target, string $message): bool
    {
        Log::info("[WA LOG DRIVER] → {$target}: {$message}");
        return true;
    }

    public function testConnection(): array
    {
        return ['success' => true, 'message' => 'Log driver aktif (tidak kirim WA asli).'];
    }
}
