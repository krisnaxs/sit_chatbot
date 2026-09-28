<?php

namespace App\Observers;

class DepartmentObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'Departemen';
    }
}
