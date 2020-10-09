<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubAdminHistory extends Model
{
    protected $fillable = [
        'sub_admin_id',
        'type',
        'type_id',
        'action',
        'menu',
        'comment',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }
    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['updated_at'])->diffForHumans();
    }

    public function subadmin() {
        return $this->belongsTo(SubAdmin::class, 'sub_admin_id', 'id')->with('user');
    }
}
