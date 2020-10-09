<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MenuSettings extends Model
{
    public $table = 'menu_settings';

    public $fillable = [
        'user_type',
        'title',
        'routes',
        'icons',
        'sort_order',
        'parent_id',
        'permission',
        'status',
        'is_parent',
        'type',
        'plan_id',
        'is_locked',
        'is_new',        
        'created_at',
        'updated_at',
    ];

    public function parent() {
		return $this->belongsTo(MenuSettings::class, 'parent_id', 'id');
	}
}
