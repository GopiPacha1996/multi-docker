<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OauthClients extends Model
{
    public $table = 'oauth_clients';
    public $fillable = [
        'user_id',
        'name',
        'secret',
        'client_id',
    ];
}
