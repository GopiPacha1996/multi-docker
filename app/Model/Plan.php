<?php

namespace App\model;

use App\Model\PlanFeatures;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plan';
    protected $fillable = [
        'name',
        'amount',
        'description',
        'salary_per_plan',
        'duration',
        'status',
        'add_by',
        'icon',
    ];

    public function options()
    {
        return $this->hasMany(PlanFeatures::class, 'plan_id', 'id')->with('options');
    }
}
