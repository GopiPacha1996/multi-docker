<?php

namespace App\Model;
use App\User;
use App\Model\OauthClients;
use App\Model\SubAdmin;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Subscriptions\Models\Plan;

class SubAdminPayment extends Model
{
    protected $table = 'subadmin_payment';
    
    public function client_info() {
		return $this->hasOne(OauthClients::class, 'user_id', 'user_id');
    }
    
    public function user_info()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->with('user_info');
    }

    public function plan_info()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
    public function assigned_user() {
        return $this->hasMany(SubAdmin::class, 'subadmin_payment_id', 'id')->where('active', true);
    }
}
