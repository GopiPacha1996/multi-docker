<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Country;
use App\Model\SocialAccount;

class UserInfo extends Model
{
    protected $table="user_info";

    public function country()
    {
        return $this->hasOne(Country::class, 'code', 'country');
    }

    public function social_account()
    {
        return $this->hasOne(SocialAccount::class, 'user_id', 'user_id');
    }
}
