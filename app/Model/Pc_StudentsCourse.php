<?php

namespace App\model;
use App\Model\Pc_Course;

use Illuminate\Database\Eloquent\Model;

class Pc_StudentsCourse extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'students_course';

    public function course()
    {
        return $this->hasOne(Pc_Course::class, 'id', 'course_id');
    }
    
}
