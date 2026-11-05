<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Kunci cache untuk menandai konfigurasi mail sudah di-override.
     */
    private const MAIL_CONFIG_CACHE_KEY = 'mail_config_overridden';

    /**
     * Durasi cache (detik). Setelah habis, konfigurasi akan dibaca ulang dari DB.
     * Sesuaikan dengan kebutuhan; 600 detik (10 menit) cukup wajar.
     */
    private const MAIL_CONFIG_CACHE_TTL = 600;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \App\Models\AssetType::observe(\App\Observers\AssetTypeObserver::class);
        \App\Models\Asset::observe(\App\Observers\AssetObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Consumable::observe(\App\Observers\ConsumableObserver::class);
        \App\Models\AssetLoan::observe(\App\Observers\AssetLoanObserver::class);
        \App\Models\AssetAssignment::observe(\App\Observers\AssetAssignmentObserver::class);
        \App\Models\AssetMaintenance::observe(\App\Observers\AssetMaintenanceObserver::class);
        \App\Models\AssetCategory::observe(\App\Observers\AssetCategoryObserver::class);
        \App\Models\Vendor::observe(\App\Observers\VendorObserver::class);
        \App\Models\Department::observe(\App\Observers\DepartmentObserver::class);
        \App\Models\Location::observe(\App\Observers\LocationObserver::class);
        \App\Models\AssetRequest::observe(\App\Observers\AssetRequestObserver::class);

        $this->overrideMailConfig();
    }

    /**
     * Override konfigurasi mail dari tabel settings.
     *
     * PENTING: Method ini TIDAK boleh melakukan query DB di setiap request.
     * Karena itu hasilnya di-cache. Query hanya jalan saat cache miss
     * (pertama kali, atau setelah TTL habis, atau setelah cache di-flush).
     */
    protected function overrideMailConfig(): void
    {
        // Fast path: kalau cache masih ada, langsung keluar tanpa query DB.
        if (Cache::has(self::MAIL_CONFIG_CACHE_KEY)) {
            return;
        }

        try {
            // Cek tabel settings hanya sekali per siklus cache.
            if (!Schema::hasTable('settings')) {
                // Tabel belum ada (mis. saat migrate). Tandai sebentar
                // supaya tidak query berulang-ulang.
                Cache::put(self::MAIL_CONFIG_CACHE_KEY, true, 60);
                return;
            }

            $enabled = SettingsService::get('mail', 'enabled', false);

            if (!$enabled) {
                Cache::put(self::MAIL_CONFIG_CACHE_KEY, true, self::MAIL_CONFIG_CACHE_TTL);
                return;
            }

            $transport = SettingsService::get('mail', 'transport', 'smtp');

            if (in_array($transport, ['log', 'array'], true)) {
                Config::set('mail.default', $transport);
                Cache::put(self::MAIL_CONFIG_CACHE_KEY, true, self::MAIL_CONFIG_CACHE_TTL);
                return;
            }

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => SettingsService::get('mail', 'host', 'smtp.gmail.com'),
                'port' => (int) SettingsService::get('mail', 'port', 587),
                'username' => SettingsService::get('mail', 'username', ''),
                'password' => SettingsService::get('mail', 'password', ''),
                'encryption' => SettingsService::get('mail', 'encryption', 'tls'),
                'timeout' => null,
                'auth_mode' => null,
            ]);

            Config::set('mail.from', [
                'address' => SettingsService::get('mail', 'from_address', config('mail.from.address')),
                'name' => SettingsService::get('mail', 'from_name', config('mail.from.name')),
            ]);

            Cache::put(self::MAIL_CONFIG_CACHE_KEY, true, self::MAIL_CONFIG_CACHE_TTL);
        } catch (\Throwable $e) {
            // Jangan telan error tanpa jejak. Catat sebagai warning,
            // dan jangan cache hasil gagal supaya bisa dicoba lagi nanti.
            Log::warning('Mail config override failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public static function flushMailConfigCache(): void
    {
        Cache::forget(self::MAIL_CONFIG_CACHE_KEY);
    }
}
