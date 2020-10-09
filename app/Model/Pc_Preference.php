<?php

namespace App\model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Model\Pc_BaseSettings;


class Pc_Preference extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'preference';

    public function preference()
    {
        return $this->hasOne(Pc_BaseSettings::class, 'id', 'preference_id')->with('options');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function preference_name()
    {
        return $this->hasOne(Pc_BaseSettings::class, 'id', 'preference_id');
    }
}
