<?php

namespace App\Model;

use App\User;

use Illuminate\Database\Eloquent\Model;

class EducatorInsight extends Model
{
    protected $connection = 'mysql_course';
    public $table = 'educator_insights';

    public function User()
	{
	return $this->belongsTo(User::class, 'id', 'educator_id');
	}
}
