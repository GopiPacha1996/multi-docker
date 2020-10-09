<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\OauthClients;
use App\Model\UserInfo;
use App\Model\Pc_Preference;
use App\User;
use Storage;
use Illuminate\Support\Facades\Log;
use App\Model\SocialAccount;

class UserProfileController extends Controller
{
	public function profile(Request $request){
        $user = $request->user();
		$user_id = $user->id;
		
		$exists = User::where('id',$user_id)->first();
		if($exists){

		
			if($request->get('name') != null){
				$exists->name = $request->get('name');
				$exists->save();
			
				$oauth_client = OAuthClients::where('user_id',$user_id)->first();
				if($oauth_client){
					$oauth_client->name = $request->get('name');
					$oauth_client->save();
				}
			}

			if($request->get('about') != null){
				$user_info = UserInfo::where('user_id',$user_id)->first();
				if($user_info){
					$user_info->about = $request->get('about');
					$user_info->save();
				}else{
					$user_info = new UserInfo();
					$user_info->user_id = $user_id;
					$user_info->about = $request->get('about');
					$user_info->save();
				}
			}

			if($request->file('profile_pic')){
				Log::info('file found');
				$extension = $request->file('profile_pic')->extension();
            	$file = mt_rand(100, 999) . time() . '.' . $extension;
            	Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('profile_pic'), $file, 'public');
            	$user_info = UserInfo::where('user_id',$user_id)->first();
				if($user_info){
					$user_info->profile_pic = $file;
					$user_info->save();
					Log::info($user_info);	
				}else{
					$user_info_new = new UserInfo();
					$user_info_new->user_id = $user_id;
					$user_info_new->profile_pic = $file;
					$user_info_new->save();
					Log::info($user_info_new);
				}
			}

			if($request->get('categories')!=null){

				$selectedPreferenceId=[];
        		$selectedPreferenceId = $request->get('categories');
        		Log::info(sizeof($selectedPreferenceId));
        		for($i = 0; $i < sizeof($selectedPreferenceId); $i++){
        			$entry = Pc_Preference::where('user_id',$user_id)
        					->where('preference_id',$selectedPreferenceId[$i])
        					->first();
        			if($entry){
        				$entry->status = 1;
        				$entry->save();
        			}else{
        				$new_entry = new Pc_Preference();
        				$new_entry->user_id = $user_id;
        				$new_entry->preference_id = $selectedPreferenceId[$i];
        				$new_entry->status = 1;
        				$new_entry->save();
        			}
        		}

        $preference_upadte = Pc_Preference::where('user_id',$user_id)
                  ->whereNotIn('preference_id',$selectedPreferenceId)
                  ->update(array(
                    'status' => 0
                  ));


			}


    $user = User::select('id','name','phone','email')
          ->where('id',$user_id)
          ->with('user_info')
          ->first();
                
      if($user){
        $user->profile_pic = $this->getUserProfile($user_id);
      }

      return $this->simpleReturn('success',$user);
  		
      }else{
  	        return $this->simpleReturn('error', 'User not found.', 404);
  		}
   	}

   	public function show($id){
   		$password_set = User::select('id')
   						->whereNotNull('password')
   						->where('id',$id)
   						->get();
   		
   		$flag = 0;

   		if(empty($password_set)){
   			$flag = 0;
   		}else{
   			$flag = 1;
   		}

   		$user = User::select('id','name','phone','email')
   				->where('id',$id)
   				->with('user_info')
   				->first();
   				   		
   		if($user){
   			$user->password_flag = $flag;
   			//$user->profile_pic = $this->getUserProfile($id);

        	return $this->simpleReturn('success',$user);
   		}else{
	        return $this->simpleReturn('error', 'User not found.', 404);
   		}
    }


    public function getUserProfile($user_id)
    {
        $url='';
        $social_account = SocialAccount::select('provider_user_avatar')
                                    ->where('user_id',$user_id)
                                    ->first();
        if($social_account){
            $url = $social_account->provider_user_avatar;
        } else {
           $user_info = UserInfo::select('profile_pic')
                                    ->where('user_id',$user_id)
                                    ->first();
            if($user_info){
                $url = env('DO_SPACES_CDN').'/'.env('DO_SPACES_APP_ENV','e2e').'/images/profiles/'.$user_info->profile_pic;
            }
        }
        return $url;
    }
}
