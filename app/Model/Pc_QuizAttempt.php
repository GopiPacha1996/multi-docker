<?php

namespace App\model;

use App\Model\Pc_Quiz;
use Illuminate\Database\Eloquent\Model;

class Pc_QuizAttempt extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'quiz_attempts';

    public function quiz()
    {
        return $this->belongsTo(Pc_Quiz::class, 'quiz_id', 'id');
    }
}
