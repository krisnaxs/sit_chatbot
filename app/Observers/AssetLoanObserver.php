<?php

namespace App\Observers;

class AssetLoanObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Peminjaman';
    }
}
