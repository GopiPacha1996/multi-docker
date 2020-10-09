<?php

namespace App\model;

use App\User;
use Carbon\Carbon;
use App\Model\OauthClients;
use App\Model\SubAdminMenu;
use Illuminate\Database\Eloquent\Model;

class SubAdmin extends Model
{
    protected $fillable = [
        'user_id',
        'oauth_client_id',
        'active',
        'subadmin_payment_id',
        'expires_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'expires_at'];

    public function getCreatedAtAttribute()
    {
        return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
    }
    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['updated_at'])->diffForHumans();
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function oauth() {
        return $this->belongsTo(OauthClients::class, 'oauth_client_id', 'id')
            ->where('revoked', false)->where('issue_status', 2);
    }

    public function menus() {
        return $this->hasMany(SubAdminMenu::class, 'sub_admin_id', 'id')
            ->where('active', true)->with('menu');
    }
}
