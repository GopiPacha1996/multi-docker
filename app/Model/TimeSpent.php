<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Pu_User;

class TimeSpent extends Model
{
    protected $connection = 'mysql_course';
    public $table = 'time_spent';

    public $fillable = [
        'user_id',
        'is_institute',
        'institute_id',
        'platform',
        'type',
        'duration',
        'date'
    ];

    public function user()
    {
        return $this->belongsTo(Pu_User::class, 'user_id', 'id');
    }

}