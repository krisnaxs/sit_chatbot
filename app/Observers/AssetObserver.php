<?php

namespace App\Observers;

class AssetObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Aset';
    }
}
