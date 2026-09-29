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

    protected array $validFields = [
        'sn',
        'hostname',
        'asset_code',
        'all',
    ];

    public function __construct(protected QueryRouter $router)
    {
    }

    /**
     * Return assoc array:
     *   [
     *     'answer'  => string,
     *     'context' => array|null,
     *     'source'  => 'database',
     *   ]
     * atau null kalau tidak bisa dieksekusi.
     */
    public function execute(array $intent): ?array
    {
        $name = $intent['intent'] ?? null;
        $params = $intent['params'] ?? [];
        $confidence = (float) ($intent['confidence'] ?? 1.0);

        if (!$name || $name === 'other') {
            return null;
        }

        if ($confidence < 0.5) {
            Log::info('IntentExecutor: low confidence, skip', [
                'intent' => $name,
                'confidence' => $confidence,
            ]);
            return null;
        }

        if (!$this->validateParams($name, $params)) {
            Log::warning('IntentExecutor: params invalid', [
                'intent' => $name,
                'params' => $params,
            ]);
            return null;
        }

        // Handler khusus: list_asset_by_user punya format pesan sendiri
        if ($name === 'list_asset_by_user') {
            return $this->executeListAssetByUser($params);
        }

        $pesan = $this->intentToPesan($name, $params);
        if (!$pesan) {
            Log::info('IntentExecutor: intentToPesan null', ['intent' => $name]);
            return null;
        }

        try {
            $result = $this->router->tryAnswer($pesan);
        } catch (\Throwable $e) {
            Log::warning('IntentExecutor: QueryRouter exception', [
                'intent' => $name,
                'pesan' => $pesan,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (!$result || !isset($result[0])) {
            return null;
        }

        return [
            'answer' => $result[0],
            'context' => $result[2] ?? null,
            'source' => 'database',
        ];
    }

    /**
     * Khusus list_asset_by_user — langsung query DB, tidak lewat QueryRouter.
     */
    protected function executeListAssetByUser(array $params): ?array
    {
        $userName = trim($params['user_name'] ?? '');
        $field = $params['field'] ?? 'all';

        if ($userName === '') {
            return null;
        }

        if (!in_array($field, $this->validFields, true)) {
            $field = 'all';
        }

        $user = \App\Models\User::where('name', 'like', "%{$userName}%")->first();
        if (!$user) {
            return null;
        }

        $assets = $user->currentAssets()->with('category')->get();

        if ($assets->isEmpty()) {
            return [
                'answer' => "User **{$user->name}** sedang tidak memegang aset.",
                'context' => [
                    'type' => 'user_assets',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'time' => now()->toDateTimeString(),
                ],
                'source' => 'database',
            ];
        }

        if ($field === 'all') {
            $jawaban = "👤 **{$user->name}** memegang **{$assets->count()}** aset:\n\n";
            foreach ($assets as $a) {
                $jawaban .= "• **{$a->hostname}**";
                if ($a->serial_number && $a->serial_number !== $a->hostname) {
                    $jawaban .= " (SN: `{$a->serial_number}`)";
                }
                $jawaban .= "\n  {$a->brand} {$a->model}\n";
                if ($a->category) {
                    $jawaban .= "  Kategori: {$a->category->name}\n";
                }
                $jawaban .= "\n";
            }
        } else {
            $fieldLabels = [
                'sn' => 'Serial Number',
                'hostname' => 'Hostname',
                'asset_code' => 'Asset Code',
            ];
            $dbField = match ($field) {
                'sn' => 'serial_number',
                'hostname' => 'hostname',
                'asset_code' => 'asset_code',
                default => 'serial_number',
            };
            $label = $fieldLabels[$field] ?? $field;

            $jawaban = "👤 **{$user->name}** memegang **{$assets->count()}** aset:\n\n";
            foreach ($assets as $a) {
                $value = $a->{$dbField} ?? null;
                $jawaban .= "• **{$a->hostname}**";
                if ($a->serial_number && $dbField !== 'serial_number' && $a->serial_number !== $a->hostname) {
                    $jawaban .= " (SN: `{$a->serial_number}`)";
                }
                $jawaban .= "\n"
                    . "  {$label}: `" . ($value ?: '-') . "`\n"
                    . "  {$a->brand} {$a->model}\n\n";
            }
        }

        $first = $assets->first();

        return [
            'answer' => trim($jawaban),
            'context' => [
                'type' => 'user_assets',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'asset_id' => $first->id,
                'serial_number' => $first->serial_number,
                'hostname' => $first->hostname,
                'time' => now()->toDateTimeString(),
            ],
            'source' => 'database',
        ];
    }

    protected function validateParams(string $intent, array $params): bool
    {
        if (in_array($intent, ['count_asset_by_status', 'list_asset_by_status'], true)) {
            if (empty($params['status']) || !in_array($params['status'], $this->validStatus, true)) {
                return false;
            }
        }

        if ($intent === 'count_asset_by_ownership') {
            if (
                empty($params['ownership_type'])
                || !in_array($params['ownership_type'], ['owned', 'leased'], true)
            ) {
                return false;
            }
        }

        if (in_array($intent, ['who_holds_asset', 'find_asset'], true)) {
            if (empty($params['identifier'])) {
                return false;
            }
            if (!preg_match('/[A-Z0-9\-_]{3,}/i', $params['identifier'])) {
                return false;
            }
        }

        if ($intent === 'list_asset_by_user') {
            if (empty($params['user_name'])) {
                return false;
            }
        }

        if ($intent === 'list_asset_by_category' && empty($params['category'])) {
            return false;
        }

        if ($intent === 'list_asset_by_brand' && empty($params['brand'])) {
            return false;
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
