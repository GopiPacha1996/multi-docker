<?php

namespace App\model;

use App\Model\Plan;
use App\User;
use Illuminate\Database\Eloquent\Model;

class TeacherInfo extends Model {
	protected $table = "teacher_info";

	public function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function plan() {
		return $this->belongsTo(Plan::class, 'plan_id', 'id');
	}
}
