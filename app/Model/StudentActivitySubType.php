<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StudentActivitySubType extends Model
{
    protected $table="student_activities_subtype";
	
    protected $fillable = [
        'sub_type','sub_type_id','student_activity_id'
    ];

	protected $hidden = [
        'updated_at'
    ];
}
