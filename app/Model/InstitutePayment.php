<?php

namespace App\Model;
use App\User;
use App\Model\OauthClients;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Addresses\Models\Address;
use Rinvex\Subscriptions\Models\Plan;

class InstitutePayment extends Model
{
    protected $table = 'institute_payment';
    
    public function client_info() {
		return $this->hasOne(OauthClients::class, 'user_id', 'user_id');
    }
    
    public function user_info()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function address_info()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }


    public function plan_info()
    {
        return $this->belongsTo(Plan::class, 'new_plan_id', 'id');
    }
}
