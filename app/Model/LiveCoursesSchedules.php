<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class LiveCoursesSchedules extends Model
{
    protected $connection = 'mysql_course';
    public $table = 'live_course_schedules';

    public $fillable = [
        'live_course_id',
        'title',
        'description',
        'video_id',
        'youtube_url',
        'sort_order',
        'schedule_date',
        'schedule_time',
        'end_time',
        'end_date',
        'quiz_id',
        'attachment',
        'status',
        'go_live',
    ];

}