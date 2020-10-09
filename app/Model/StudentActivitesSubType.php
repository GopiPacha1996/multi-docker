<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentActivitesSubType extends Model
{
    public $table = 'student_activities_subtype';

    protected $appends=['published'];

    public function getPublishedAttribute()
    {
        
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }

}
