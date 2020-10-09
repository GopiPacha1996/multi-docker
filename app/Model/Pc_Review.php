<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_Review extends Model
{
    protected $connection = 'mysql_course';
    public $table = 'reviews';

    public $fillable = [
        'user_id',
        'type',
        'type_id',
        'author_id',
        'review',
        'rating',
        'status',
    ];
}