<?php

namespace App\Services;

use App\Models\ChatContext;

class ChatMemoryService
{
    private string $sessionId;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Simpan konteks.
     */
    public function remember(string $type, array $data, int $ttlMinutes = 30, ?string $label = null): ChatContext
    {
        $data['type'] = $data['type'] ?? $type;
        $data['_type'] = $data['_type'] ?? $type;

        ChatContext::where('session_id', $this->sessionId)
            ->where('type', $type)
            ->delete();

        return ChatContext::create([
            'session_id' => $this->sessionId,
            'type' => $type,
            'data' => $data,
            'expires_at' => now()->addMinutes($ttlMinutes),
            'label' => $label,
        ]);
    }

    /**
     * Ambil konteks.
     *
     * - Kalau $type diisi → return context dengan type tersebut, atau null
     * - Kalau $type kosong → return context TERBARU
     */
    public function recall(?string $type = null): ?array
    {
        $query = ChatContext::where('session_id', $this->sessionId)
            ->active()
            ->latest();

        if ($type) {
            $query->where('type', $type);
        }

        $ctx = $query->first();

        if (!$ctx) {
            return null;
        }

        $ctx->increment('hit_count');

        return array_merge(['_type' => $ctx->type], $ctx->data ?? []);
    }

    /**
     *  Cari context pertama yang cocok dari daftar type.
     *
     * Contoh: recallFirstOf(['asset_by_status', 'list_asset_by_status'])
     */
    public function recallFirstOf(array $types): ?array
    {
        foreach ($types as $type) {
            $ctx = $this->recall($type);
            if ($ctx !== null) {
                return $ctx;
            }
        }
        return null;
    }

    /**
     * Ambil semua konteks aktif.
     */
    public function recallAll(): array
    {
        return ChatContext::where('session_id', $this->sessionId)
            ->active()
            ->get()
            ->mapWithKeys(fn($c) => [$c->type => $c->data])
            ->toArray();
    }

    /**
     * Hapus konteks.
     */
    public function forget(?string $type = null): void
    {
        $query = ChatContext::where('session_id', $this->sessionId);

        if ($type) {
            $query->where('type', $type);
        }

        $query->delete();
    }

    /**
     * Cleanup expired contexts.
     */
    public static function cleanup(): int
    {
        return ChatContext::where('expires_at', '<', now())->delete();
    }
}
