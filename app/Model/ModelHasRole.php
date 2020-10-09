<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    public $table = 'model_has_roles';
    public $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    public function rolename(){
        return $this->hasMany(Role::class, 'id', 'role_id');
    }
}
