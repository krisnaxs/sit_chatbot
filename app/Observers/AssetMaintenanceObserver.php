<?php

namespace App\Observers;

class AssetMaintenanceObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Perbaikan';
    }
}
