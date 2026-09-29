<?php

namespace App\Observers;

class AssetRequestObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Pengajuan';
    }

    protected function logName(): string
    {
        return 'request';
    }
}
