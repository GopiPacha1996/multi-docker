<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $table = 'roles';
    public $fillable = [
        'name',
        'gaurd_name',
    ];

}
