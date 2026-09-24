<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class App extends Model
{
    use LogsActivity;

    protected $table = 'apps';
    protected $fillable = [
        'nama',
        'gambar',
        'url',
        'clicks',
        'is_active',
        'slide',
        'urutan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'url', 'slide', 'urutan', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('app');
    }
}
