<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    public $table = 'action_log';
    public $fillable = [
        'type',
        'action_id',
        'add_by',
    ];
}
