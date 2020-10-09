<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pc_Checkout extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'checkouts';
}
