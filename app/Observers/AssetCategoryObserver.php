<?php

namespace App\Observers;

class AssetCategoryObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Kategori';
    }
}
