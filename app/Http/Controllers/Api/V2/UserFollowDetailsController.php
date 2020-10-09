<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Pu_OauthClients;
use App\Model\UserInfo;
use App\Model\UserFollower;
use App\Model\ModelHasRole;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserFollowDetailsController extends Controller
{
    public function getFollowers(Request $request){
    	$user_id = Auth::id();
        $users_list = array();
    	$perPageCount = $request->get('perPageCount') ? $request->get('perPageCount') : 15 ;

    	if($request->sort!=null){

            $followed_users = UserFollower::where('tutor_id',$user_id)
                    ->where('follow',1);
    		if($request->sort == 'institute'){
    			//3
    			$users_list = ModelHasRole::where('role_id',3)->pluck('model_id');
                $followed_users = $followed_users->whereIn('user_id',$users_list);
    		}
    		if($request->sort == 'educator'){
    			//2
    			$users_list = ModelHasRole::where('role_id',2)->pluck('model_id');
                $followed_users = $followed_users->whereIn('user_id',$users_list);
    		}
    		if($request->sort == 'quiz'){
    			//6
    			$users_list = ModelHasRole::where('role_id',6)->pluck('model_id');
                $followed_users = $followed_users->whereIn('user_id',$users_list);
    		}

            $followed_users = $followed_users->pluck('user_id');

    		// $followed_users = UserFollower::where('tutor_id',$user_id)
    		// 		->whereIn('user_id',$users_list)
    		// 		->where('follow',1)
    		// 		->pluck('user_id');
    	}
    	else{
    		$followed_users = UserFollower::where('tutor_id',$user_id)
    				->where('follow',1)
    				->pluck('user_id');
    	}

    	$data = User::whereIn('id',$followed_users)
    			->with('user_info')
    			->withCount('followers')
                ->withCount('following')
                ->withCount('is_following');

    	if($request->search!=null){
    		$data = $data->where('name', 'like', '%' . $request->search . '%');
    	}

    	if($request->sort!=null){
    		if($request->sort == 'oldest'){
    			$data = $data->orderBy('created_at');
    		}
    		if($request->sort == 'newest'){
    			$data = $data->orderBy('created_at','desc');
    		}
            if($request->sort == 'institute'){
                //3
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',3);
                }]);
            }
            if($request->sort == 'educator'){
                //2
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',2);
                }]);
            }
            if($request->sort == 'quiz_creator'){
                //6
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',6);
                }]);
            }
    	}else
        {
            $data = $data->with('role_name_user');
        }

    	$data = $data->paginate($perPageCount);

        return $this->simpleReturn('success',$data);
    }

    public function getFollowing(Request $request){
    	$user_id = Auth::id();
        $users_list = array();
    	$perPageCount = $request->get('perPageCount') ? $request->get('perPageCount') : 15 ;

        if($request->sort!=null){
            $following_users = UserFollower::where('user_id',$user_id)
                    ->where('follow',1);
            if($request->sort == 'institute'){
                //3
                $users_list = ModelHasRole::where('role_id',3)->pluck('model_id');
                $following_users = $following_users->whereIn('tutor_id',$users_list);
            }
            if($request->sort == 'educator'){
                //2
                $users_list = ModelHasRole::where('role_id',2)->pluck('model_id');
                $following_users = $following_users->whereIn('tutor_id',$users_list);
            }
            if($request->sort == 'quiz_creator'){
                //6
                $users_list = ModelHasRole::where('role_id',6)->pluck('model_id');
                $following_users = $following_users->whereIn('tutor_id',$users_list);
            }

            $following_users = $following_users->pluck('tutor_id');

            // $following_users = UserFollower::where('user_id',$user_id)
            //         ->whereIn('user_id',$users_list)
            //         ->where('follow',1)
            //         ->pluck('tutor_id');
            
        }
        else{
            $following_users = UserFollower::where('user_id',$user_id)
                    ->where('follow',1)
                    ->pluck('tutor_id');
        }


		// $users = UserFollower::where('user_id',$user_id)
      //   				->where('follow',1)
      //   				->pluck('tutor_id');
        //print_r($following_users);die();
		$data = User::whereIn('id',$following_users)
    			->with('user_info')
    			->with('role_name_user')
    			->withCount('followers');

    	if($request->search!=null){
    		$data = $data->where('name', 'like', '%' . $request->search . '%');
    	}

        if($request->sort!=null){
            if($request->sort == 'oldest'){
                $data = $data->with('role_name_user');
                $data = $data->orderBy('created_at');
            }
            if($request->sort == 'newest'){
                $data = $data->with('role_name_user');
                $data = $data->orderBy('created_at','desc');
            }
            if($request->sort == 'institute'){
                //3
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',3);
                }]);
            }
            if($request->sort == 'educator'){
                //2
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',2);
                }]);
            }
            if($request->sort == 'quiz_creator'){
                //6
                $data = $data->with(['role_name_user'=>function($query){
                    $query->where('role_id',6);
                }]);
            }
        }else
        {
            $data = $data->with('role_name_user');
        }

    	$data = $data->paginate($perPageCount);
        return $this->simpleReturn('success',$data);
    }
}
