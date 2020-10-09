<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_Quiz extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'quizzes';
}
