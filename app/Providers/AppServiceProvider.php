<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
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
     * Di-cache oleh SettingsService, jadi aman dipanggil setiap request.
     */
    protected function overrideMailConfig(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }

            $enabled = SettingsService::get('mail', 'enabled', false);

            if (!$enabled) {
                return;
            }

            $transport = SettingsService::get('mail', 'transport', 'smtp');
            if (in_array($transport, ['log', 'array'])) {
                Config::set('mail.default', $transport);
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
        } catch (\Throwable $e) {
        }
    }
}
