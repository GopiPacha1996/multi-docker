<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_AppSettings extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'app_settings';

}
