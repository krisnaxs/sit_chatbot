<?php

namespace App\Providers;

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
        // Existing
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
    }
}
