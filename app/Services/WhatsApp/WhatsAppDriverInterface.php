<?php

namespace App\Services\WhatsApp;

interface WhatsAppDriverInterface
{
    public function send(string $target, string $message): bool;


    public function testConnection(): array;
}
