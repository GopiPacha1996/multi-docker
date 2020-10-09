<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_CourseCompletionLog extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'course_completion_log';
}
