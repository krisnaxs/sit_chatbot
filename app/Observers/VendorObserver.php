<?php

namespace App\Observers;

class VendorObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Vendor';
    }
}
