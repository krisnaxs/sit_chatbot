<?php

namespace App\Services\WhatsApp;

interface WhatsAppDriverInterface
{
    public function send(string $target, string $message): bool;

    /**
     * Test koneksi, return ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}
