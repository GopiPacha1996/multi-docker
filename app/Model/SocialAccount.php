<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table="social_account";

    protected $fillable = [
        'provider', 'provider_user_id', 'user_id'
    ];
}
