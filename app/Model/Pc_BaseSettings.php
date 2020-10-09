<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_BaseSettings extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'base_settings';

    public function options()
    {
        return $this->hasMany(Pc_BaseSettings::class, 'parent_id', 'id');
    }
}
