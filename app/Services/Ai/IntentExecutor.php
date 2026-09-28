<?php

namespace App\Services\Ai;

use App\Services\Query\QueryRouter;
use Illuminate\Support\Facades\Log;

class IntentExecutor
{
    protected array $validStatus = [
        'available',
        'in_use',
        'loaned',
        'maintenance',
        'retired',
        'lost',
    ];

    public function __construct(protected QueryRouter $router)
    {
    }

    /**
     * Return: [jawaban, context] atau null kalau intent tidak valid.
     */
    public function execute(array $intent): ?array
    {
        $name = $intent['intent'] ?? null;
        $params = $intent['params'] ?? [];

        if (!$name || $name === 'other') {
            return null;
        }

        if (!$this->validateParams($name, $params)) {
            Log::warning('IntentExecutor: params invalid', compact('name', 'params'));
            return null;
        }

        $pesan = $this->intentToPesan($name, $params);
        if (!$pesan) {
            return null;
        }

        $result = $this->router->tryAnswer($pesan);
        if (!$result) {
            return null;
        }

        return [
            'answer' => $result[0],
            'context' => $result[2] ?? null,
            'pesan' => $pesan,
            'intent' => $intent,
        ];
    }

    protected function validateParams(string $intent, array $params): bool
    {
        if (in_array($intent, ['count_asset_by_status', 'list_asset_by_status'])) {
            if (empty($params['status']) || !in_array($params['status'], $this->validStatus)) {
                return false;
            }
        }

        if ($intent === 'count_asset_by_ownership') {
            if (
                empty($params['ownership_type'])
                || !in_array($params['ownership_type'], ['owned', 'leased'])
            ) {
                return false;
            }
        }

        if (in_array($intent, ['who_holds_asset', 'find_asset'])) {
            if (empty($params['identifier'])) {
                return false;
            }
            if (!preg_match('/[A-Z0-9\-_]{3,}/i', $params['identifier'])) {
                return false;
            }
        }

        // 🆕 Validasi list_asset_by_user
        if ($intent === 'list_asset_by_user') {
            if (empty($params['user_name'])) {
                return false;
            }
        }

        return true;
    }

    protected function intentToPesan(string $intent, array $params): ?string
    {
        return match ($intent) {
            'count_asset_by_status' => trim(sprintf(
                'berapa aset %s %s %s %s',
                $this->statusToKata($params['status']),
                $params['category'] ?? '',
                $params['brand'] ?? '',
                $params['location'] ?? ''
            )),

            'list_asset_by_status' => trim(sprintf(
                'daftar aset %s %s %s %s',
                $this->statusToKata($params['status']),
                $params['category'] ?? '',
                $params['brand'] ?? '',
                $params['location'] ?? ''
            )),

            'count_asset_by_ownership' => trim(sprintf(
                'berapa aset %s %s',
                $params['ownership_type'] === 'owned' ? 'hak milik' : 'sewa',
                $params['category'] ?? ''
            )),

            'list_asset_by_category' => 'daftar aset kategori ' . ($params['category'] ?? ''),
            'list_asset_by_brand' => 'daftar aset brand ' . ($params['brand'] ?? ''),

            // 🆕 list_asset_by_user dengan field spesifik
            'list_asset_by_user' => $this->buildUserAssetQuery($params),

            'who_holds_asset' => 'siapa yang pegang ' . ($params['identifier'] ?? ''),
            'find_asset' => 'info ' . ($params['identifier'] ?? ''),

            'count_maintenance' => 'berapa aset sedang perbaikan',
            'list_overdue_loans' => 'peminjaman terlambat',
            'list_active_loans' => 'peminjaman aktif',
            'low_stock_consumable' => 'konsumable stok rendah',
            'top_users_with_assets' => 'top user pemegang aset',
            'top_locations' => 'top lokasi aset',
            'total_status_summary' => 'summary status aset',
            'count_users' => 'berapa user',
            'count_vendors' => 'berapa vendor',

            default => null,
        };
    }

    /**
     * 🆕 Bangun pesan untuk user + field spesifik.
     * QueryRouter regex mengenali "aset yang dipegang X" untuk field "all".
     * Untuk field spesifik (sn/hostname), kita gunakan format khusus yang
     * akan ditangkap oleh ChatController::tryAnswerUserAssetField().
     */
    protected function buildUserAssetQuery(array $params): string
    {
        $name = trim($params['user_name'] ?? '');
        $field = $params['field'] ?? 'all';

        return match ($field) {
            'sn' => "sn {$name} nya berapa",
            'hostname' => "hostname {$name} nya berapa",
            'asset_code' => "asset code {$name} nya berapa",
            default => "aset yang dipegang {$name}",
        };
    }

    protected function statusToKata(string $status): string
    {
        return match ($status) {
            'available' => 'tersedia',
            'in_use' => 'dipakai',
            'loaned' => 'dipinjam',
            'maintenance' => 'rusak',
            'retired' => 'pensiun',
            'lost' => 'hilang',
            default => $status,
        };
    }
}
