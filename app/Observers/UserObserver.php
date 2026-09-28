<?php

namespace App\Observers;

class UserObserver extends BaseActivityObserver
{
    protected function label(): string
    {
        return 'User';
    }

    protected function logName(): string
    {
        return 'user';   // beda grup log
    }

    protected function except(): array
    {
        return ['created_at', 'updated_at', 'password', 'remember_token'];
    }
}
