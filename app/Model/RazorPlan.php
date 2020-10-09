<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RazorPlan extends Model
{

    protected $table = 'plans';

    protected $fillable = [
        'plan_id',
        'plan_desc',
        'type',
        'type_id',
        'active',
        'amount',
        'trail',
        'interval',
        'period'
    ];
}
