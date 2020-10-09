<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class LiveCourses extends Model
{
    protected $connection = 'mysql_course';
    public $table = 'live_courses';

    public $fillable = [
        'id',
        'user_id',
        'category',
        'sub_category',
        'batch_name',
        'validity',
        'image',
        'featured_staus',
        'status',
        'progressive_course_id',
    ];
}
