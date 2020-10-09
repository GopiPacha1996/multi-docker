<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Pc_PayuPayments extends Model
{
    protected $connection = 'mysql_course';
    protected $table = 'payu_payments';
}
