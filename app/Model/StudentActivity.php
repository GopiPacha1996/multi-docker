<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentActivity extends Model
{
    public $table = 'student_activities';

    protected $fillable = [
        'user_id','title','type','type_id'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $appends=['published'];

    public function getPublishedAttribute()
    {
        
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }

}
