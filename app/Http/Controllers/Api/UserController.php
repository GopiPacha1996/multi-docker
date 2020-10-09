<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\model\TeacherInfo;
use App\Model\UserInfo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Storage;
use Validator;

class UserController extends Controller {

	public function index(Request $request) {
		$user = $request->user();

		if ($user->hasRole(['super-admin', 'admin'])) {
			$admin = User::where('id', $user->id)->with('user_info')->first();

			if ($admin) {
				return $this->simpleReturn('success', $admin);
			}return $this->simpleReturn('error', 'No details found', 404);

		} else if (($user->hasRole(['educator', 'institute'])) || ($user->hasRole(['quiz'])) || ($user->hasRole(['mocktest'])) || ($user->hasRole(['sub-admin']))) {
			$tutor = User::where('id', $user->id)->with('teacher_info')->with('user_info')->with('bank_info')->first();

			if ($tutor) {
				return $this->simpleReturn('success', $tutor);
			}return $this->simpleReturn('error', 'No details found', 404);

		} else {
			$student = User::where('id', $user->id)->with('user_info')->first();

			if ($student) {
				return $this->simpleReturn('success', $student);
			}return $this->simpleReturn('error', 'No details found', 404);
		}
	}

	public function create() {
		//
	}

	public function store(Request $request) {
		$rules = array(
			'mode' => 'required',
			'name' => 'required',
			'email' => 'required',
			'phone' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return $this->simpleReturn('error', $validator->errors(), 400);
		}

		$user_id = $request->user()->id;
		$update = User::where('id', $user_id)->update($request->only(['name', 'phone']));
		if ($update) {
			$user_info = UserInfo::where('user_id', $user_id)->get()->first();
			$res['pic'] = null;
			if ($user_info) {
				$res['pic'] = $user_info->profile_pic ? $user_info->profile_pic : null;
			}
			return $this->simpleReturn('success', $res);
		}
		return $this->simpleReturn('error', 'Error in updating values', 500);
	}

	public function show($id) {
		//
	}

	public function edit($id) {
		//
	}

	public function update(Request $request, $id) {

	}

	public function destroy($id) {
		//
	}

	public function profileUpdate(Request $request) {
		$rules = array(
			'type' => 'required',
		);

		$validator = validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return $this->simpleReturn('error', $validator->errors(), 400);
		}
		$user = $request->user();

		if ($request->type == 'profile_pic') {
			if ($request->profile_pic) {
				$extension = $request->file('profile_pic')->extension();
				$file = mt_rand(100, 999) . time() . '.' . $extension;
				Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('profile_pic'), $file, 'public');
			}

			$user_info = UserInfo::where('user_id', $user->id)->get()->first();
			if ($user_info) {
				if ($user_info->profile_pic) {
					$extImg = env('DO_SPACES_DRIVE') . '/images/profiles/'.$user_info->profile_pic;
					if (Storage::disk('do_spaces')->exists($extImg)) {
						Storage::disk('do_spaces')->delete($extImg);
					}
				}
				if(!$user_info->profile_pic){
                    $user_profile = User::where('id', $user->id)->get()->first();
                    $user_profile->profile_complete = $user_profile->profile_complete + 5;
                    $user_profile->save(); 
                }
				$updated = UserInfo::where('user_id', $user->id)->update(['profile_pic' => $file]);

				if ($updated) {
					$user_info_set = UserInfo::where('user_id', $user->id)->get()->first();
					$res['pic'] = null;
					if ($user_info_set) {
						$res['pic'] = $user_info_set->profile_pic ? $user_info_set->profile_pic : null;
					}
					// $res['pic'] = $this->userProfileDp($user->id);
					$res['msg'] = 'Successfully updated';
					return $this->simpleReturn('success', $res);
				}
			} else {
					$add = new UserInfo();
					$add->user_id = $user->id;
					$add->profile_pic = $file;
					$add->address1 = '';
					$add->address2 = '';
					$add->address3 = '';
					$add->city = '';
					$add->state = '';
					$add->country = '';
					$add->zipcode = '';
					$add->status = 'incomplete';
					$add->save();

					if ($add) {
						$user_info_set = UserInfo::where('user_id', $user->id)->get()->first();
						$res['pic'] = null;
						if ($user_info_set) {
							$res['pic'] = $user_info_set->profile_pic ? $user_info_set->profile_pic : null;
						}

						$user_profile = User::where('id', $user->id)->get()->first();
						$user_profile->profile_complete = $user_profile->profile_complete + 5;
						$user_profile->save();
					
						// $res['pic'] = $this->userProfileDp($user->id);
						$res['msg'] = 'Successfully updated';
						return $this->simpleReturn('success', $res);
					}
			}

			return $this->simpleReturn('error', 'error in updation', 500);
		} else if ($request->type == 'profile') {
			$updated = User::where('id', $user->id)->update(['name' => $request->name, 'phone' => $request->phone]);
			if ($updated) {

				$user_info = UserInfo::where('user_id', $user->id)->get()->first();
				if($user_info){
					UserInfo::where('user_id', $user->id)->update(['address1' => $request->address1, 'address2' => $request->address2, 'city' => $request->city, 'state' => $request->state, 'country' => $request->country, 'zipcode' => $request->zipcode]);

					// update teacher about
					if($request->about){
						TeacherInfo::where('user_id', $user->id)->update(['about' => $request->about]);
					}
				// }

			}
				else{

					$add = new UserInfo();
					$add->user_id = $user->id;
					$add->address1 = $request->address1;
					$add->address2 = $request->address2;
					$add->address3 = '';
					$add->city = $request->city;
					$add->state = $request->state;
					$add->country = $request->country;
					$add->zipcode = $request->zipcode;
					$add->status = 'incomplete';
					$add->save();
				}
				return $this->simpleReturn('success', 'Successfully updated.');
			}
			return $this->simpleReturn('error', 'error in updation', 500);
		} else if ($request->type == 'password') {
			if ($user->password) {
				if (Hash::check($request->current_password, $user->password)) {
					$request->user()->fill([
						'password' => Hash::make($request->new_password),
					])->save();
					return $this->simpleReturn('success', 'Successfully Updated');
				}
				$res['type'] = 'incorrect';
				$res['msg'] = 'Incorrect Password';
				return $this->simpleReturn('error', $res, 400);
			} else {
				$request->user()->fill([
					'password' => Hash::make($request->new_password),
				])->save();
				return $this->simpleReturn('success', 'Successfully Updated');
			}
		}
		return $this->simpleReturn('error', 'Invalid request', 400);
	}

	public function usersList(Request $request)
	{
		$users = User::where('is_active', true);

		if($request->type){
			$users->role($request->type);
		}

		$users = $users->get();

		if($users->count()){
			return $this->simpleReturn('success', $users);
		}
		return $this->simpleReturn('error', 'No Users found', 404);
	}
	public function usersStudentList(Request $request)
	{
		$users = User::where('is_active', true);

		if($request->type){
			$users->role($request->type);
		}

		if($request->keyword){
			$users->where('email', 'like', '%' . $request->keyword . '%');
			$users->orWhere('phone', 'like', '%' . $request->keyword . '%');
		}

		$users = $users->get();

		if($users->count()){
			return $this->simpleReturn('success', $users);
		}
		return $this->simpleReturn('error', 'No Users found', 404);
	}

	public function usersAllList(Request $request)
	{
		$perPageCount = $request->perPageCount ? $request->perPageCount : 15;
		$users = User::where('id','!=', $request->user()->id)->where('is_active', true)->with('user_info')->with('client_info');
		// ->join('user_info', 'user_info.user_id', 'users.id');

		if($request->user_type){
			$users->role($request->user_type);
		}

		// if (($request->startDate) && ($request->endDate)) {
		// 	$startDate = $request->startDate." 00:00:00";
		// 	$endDate = $request->endDate." 23:59:59";
		// 	$users->whereBetween('created_at', [$request->startDate,$request->endDate]);
		// }

		if(($request->keyword) && ($request->keyword != '')){
			$users->where('email', 'like', '%' . $request->keyword . '%');
			$users->orWhere('phone', 'like', '%' . $request->keyword . '%');
		}

		if(($request->startDate && $request->startDate != '')
            && ($request->endDate && $request->endDate !='')) {

            $users->where('created_at', '>=', $request->startDate." 00:00:00")
                ->where('created_at' , '<=', $request->endDate." 23:59:59");

		}
		$users->orderBy('id', 'DESC');
		$users = $users->paginate($perPageCount);

		if($users->count()){
			return $this->simpleReturn('success', $users);
		}
		return $this->simpleReturn('error', 'No Users found', 404);
	}

	public function usersDataUpDate(Request $request)
	{
		$rules = array(
			'type' => 'required',
		);

		$validator = validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return $this->simpleReturn('error', $validator->errors(), 400);
		}
		if($request->type == 'password'){
			$updated = User::where('id', $request->user_id)->update(['password' => Hash::make($request->password)]);
			return $this->simpleReturn('success', 'Successfully Updated');
		}else if($request->type == 'profile'){
			$updated = User::where('id', $request->user_id)->update(['phone_verify' => '1']);
			return $this->simpleReturn('success', 'Successfully Updated');
		}else if($request->type == 'phone_varify'){
			$user_profile = User::where('id',  $request->user()->id)->get()->first();
			$user_profile->profile_complete = $user_profile->profile_complete + 10;
			$user_profile->save();
			$updated = User::where('id', $request->user()->id)->update(['phone_verify' => '1']);
			return $this->simpleReturn('success', 'Successfully Updated');
		}

		return $this->simpleReturn('error', 'Invalid request', 400);
	}


    public function coverUpdate(Request $request) {
        $rules = array(
            'type' => 'required',
        );
        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $user = $request->user();
        if ($request->type == 'cover_pic') {
            if ($request->cover_pic) {
                $extension = $request->file('cover_pic')->extension();
                $file = mt_rand(100, 999) . time() . '.' . $extension;
                Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('cover_pic'), $file, 'public');
			}
			
            $teacher_info = TeacherInfo::where('user_id', $user->id)->get()->first();
            if ($teacher_info) {
                if ($teacher_info->cover_pic) {
                    $extImg = env('DO_SPACES_DRIVE') . '/images/profiles/'.$teacher_info->cover_pic;
                    if (Storage::disk('do_spaces')->exists($extImg)) {
                        Storage::disk('do_spaces')->delete($extImg);
                    }
				}else{
					$user_profile = User::where('id', $user->id)->get()->first();
					$user_profile->profile_complete = $user_profile->profile_complete + 5;
					$user_profile->save(); 
				}
                $updated = TeacherInfo::where('user_id', $user->id)->update(['cover_pic' => $file]);
            } else {
                $add = new TeacherInfo();
                $add->user_id = $user->id;
                $add->cover_pic = $file;
                $add->demo_video = '';
                $add->type = '';
                $add->pathshala_employee = '';
                $add->about = $request->about ? $request->about : '';
                $add->status = 'incomplete';
				$add->save();

				if($request->about != ''){
					$user_profile = User::where('id', $user->id)->get()->first();
					$user_profile->profile_complete = $user_profile->profile_complete + 5;
					$user_profile->save();
				}
				
				 
            }
            if ($updated) {
                $user_info_set = TeacherInfo::where('user_id', $user->id)->get()->first();
                $res['pic'] = null;
                if ($user_info_set) {
                    $res['pic'] = $user_info_set->cover_pic ? $user_info_set->cover_pic : null;
				}
				
				
                
                // $res['pic'] = $this->userProfileDp($user->id);
                $res['msg'] = 'Successfully updated';
                return $this->simpleReturn('success', $res);
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }
        return $this->simpleReturn('error', 'Invalid request', 400);
    }
}
