<?php

namespace App\Observers;

class ConsumableObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Konsumable';
    }
}
