<?php

namespace App\Observers;

class LocationObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Lokasi';
    }
}
