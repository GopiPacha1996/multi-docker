<?php

namespace App\model;

use App\Model\Pc_BaseSettings;
use Illuminate\Database\Eloquent\Model;

class PlanFeatures extends Model
{
    protected $table = 'plan_features';
    protected $fillable = [
        'plan_id',
        'feature_id',
        'status',
    ];

    public function options()
    {
        // return $this->hasMany(Pc_BaseSettings::class, 'id', 'feature_id');
        return $this->belongsTo(Pc_BaseSettings::class, 'feature_id', 'id');
    }
}
