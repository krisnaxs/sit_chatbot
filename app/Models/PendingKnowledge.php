<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingKnowledge extends Model
{
    protected $table = 'pending_knowledge';
    protected $fillable = ['pesan_user', 'jawaban_ai', 'frequency', 'status'];

    protected $casts = [
        'frequency' => 'integer',
    ];
}
