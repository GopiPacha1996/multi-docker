<?php

namespace App\model;

use App\Model\Pc_StudentsCourse;
use Illuminate\Database\Eloquent\Model;

class Pc_Course extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'course';

    public function student_courses()
    {
        return $this->hasMany(Pc_StudentsCourse::class, 'course_id', 'id');
    }

    public function student_coursesCount()
    {
        return $this->student_courses()
            ->selectRaw('course_id, count(*) as student_count')
            ->groupBy('course_id');
    }


}
