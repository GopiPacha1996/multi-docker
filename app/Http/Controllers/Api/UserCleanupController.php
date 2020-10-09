<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\Model\SocialAccount;
use App\Model\UserFollower;
use App\Model\Device;
use Illuminate\Http\Request;
use Validator;

class UserCleanupController extends Controller
{

    public function userCleanup(Request $request)
    {
        $rules = array(
            'user_id' => 'required|numeric',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        } else {

            $user = User::where('id', $request->user_id)->first();
            if(($user) &&($request->password  ==  env("USER_CLEANUP_PASSWORD","2GcSQUVnHF4YlqQWyluIcjZ9"))){

                SocialAccount::where('user_id', $request->user_id)->delete();
                UserFollower::where('user_id', $request->user_id)->delete();
//                Device::where('user_id', $request->user_id)->delete();
                $user->devices()->delete();
                $user_delete = User::where('id', $request->user_id)->delete();

                if ($user_delete) {
                    return $this->simpleReturn('success', 'Successfully deactive');
                }
            }
            return $this->simpleReturn('error', 'user not found');
        }
    }

}
