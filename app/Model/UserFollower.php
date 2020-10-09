<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model
{
    public $table = 'user_followers';

    public $fillable = [
        'user_id',
        'tutor_id',
        'follow',
        'notification',
        // 'follow_at',
        // 'notified_at',
    ];
}