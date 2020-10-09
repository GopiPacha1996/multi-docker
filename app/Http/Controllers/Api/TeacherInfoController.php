<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InstructorRegistration;
use App\Mail\TeacherVerification;
use App\Model\TeacherInfo;
use App\Model\UserInfo;
use App\Model\UserBankData;
use App\Model\Plan;
use App\Model\Pc_BaseSettings;
use App\User;
use Illuminate\Http\Request;
use Mail;
use Storage;
use Validator;

class TeacherInfoController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $info = User::where('id', $user->id)->with('teacher_info')->with('user_info')->with('bank_info')->get()->first();
        return $this->simpleReturn('success', $info);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = array(
            'demo_video' => 'required',
            // 'cover_pic' => 'required',
            'pathshala_employee' => 'required',
            'about' => 'required',
            'instructor_type' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = $request->user();
        $info = TeacherInfo::where('user_id', $user->id)->get()->first();

        $extension = $request->file('demo_video')->extension();
        $file = mt_rand(100, 999) . time() . '.' . $extension;
        Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/videos/samples', $request->file('demo_video'), $file, 'public');

        $extension1 = $request->file('cover_pic')->extension();
        $file1 = mt_rand(100, 999) . time() . '.' . $extension1;
        Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('cover_pic'), $file1, 'public');

        // $video = $request->demo_video;
        // $file = $this->createFileName($video, $user->id);
        // $video->storeAs('/', $file, 'demoVideo');

        $employee = $request->pathshala_employee ? 1 : 0;

        if ($info) {
            // return $this->simpleReturn('error', 'Pass to update func', 400);
            $updated = TeacherInfo::where('id', $info->id)->update([
                'demo_video' => $file,
                'cover_pic' => $file1,
                'type' => $request->instructor_type,
                'pathshala_employee' => $employee,
                'about' => $request->about,
            ]);

            if ($updated) {
                return $this->simpleReturn('success', 'Successfully updated');
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }

        $add = new TeacherInfo();
        $add->user_id = $user->id;
        $add->type = $request->instructor_type;
        $add->pathshala_employee = $employee;
        $add->demo_video = $file;
        $add->cover_pic = $file1;
        $add->about = $request->about;
        $add->status = 'incomplete';
        if ($add->save()) {
            return $this->simpleReturn('success', 'Successfully inserted');
        }
        return $this->simpleReturn('error', 'Error in insertion', 500);
    }


    public function updateCoverPic(Request $request)
    {
        $rules = array(
            // 'about' => 'required',
            'instructor_type' =>'required'
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 401);
        }

        $user = $request->user();
        // update name
        $updateName = User::where('id', $user->id)->update([ 'name' => $request->name]);

        $info = TeacherInfo::where('user_id', $user->id)->get()->first();

        // $extension = $request->file('demo_video')->extension();
        // $file = mt_rand(100, 999) . time() . '.' . $extension;
        // Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/videos/samples', $request->file('demo_video'), $file, 'public');
        if($request->file('cover_pic')){
            $extension1 = $request->file('cover_pic')->extension();
            $file1 = mt_rand(100, 999) . time() . '.' . $extension1;
            Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('cover_pic'), $file1, 'public');
        }

        $employee = $request->pathshala_employee ? 1 : 0;

        if ($info) {
            // return $this->simpleReturn('error', 'Pass to update func', 400);
            $updated = TeacherInfo::where('id', $info->id)->update([
                'demo_video' => null,
                'cover_pic' => $request->file('cover_pic') ? $file1 : $info->cover_pic,
                'type' => $request->instructor_type,
                'pathshala_employee' => $employee,
                'about' => $request->about ? $request->about : null,
            ]);

            if ($updated) {
                return $this->simpleReturn('success', 'Successfully updated');
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }

        $add = new TeacherInfo();
        $add->user_id = $user->id;
        $add->type = $request->instructor_type;
        $add->pathshala_employee = $employee;
        $add->demo_video = null;
        $add->cover_pic = $request->file('cover_pic') ? $file1 : null;
        $add->about = $request->about ? $request->about : null;
        $add->status = 'incomplete';
        if ($add->save()) {
            if($request->about){
                $user_profile = User::where('id', $user->id)->get()->first();
                $user_profile->profile_complete = $user_profile->profile_complete + 5;
                $user_profile->save(); 
            }
            
            return $this->simpleReturn('success', 'Successfully inserted');
        }
        return $this->simpleReturn('error', 'Error in insertion', 500);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        // $rules = array(
        //     'plan' => 'required',
        // );

        // $validator = validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return $this->simpleReturn('error', $validator->errors(), 400);
        // }

        $user = $request->user();

        $updated = TeacherInfo::where('user_id', $user->id)
                    ->update(['status' => 'active', 'admin_status' => 'pending']);

        if ($updated) {
            $info = TeacherInfo::where('user_id', $user->id)->get()->first();
            $role = 'educator';
            if ($info) {
                $role = $info->type == 'institute' ? 'institute' : $role;
            }
            $user->assignRole($role);

            // Registration mail
            Mail::to($user->email)->queue(new InstructorRegistration($user));
            $this->notification($user->id, null, 'teacher_approval', 'Approval', 'Your profile is in approval state and will be approved with in 24 hours.');
            
            // Admin mail
            $admins = User::role(['super-admin', 'admin'])->active()->get();
            foreach ($admins as $key => $admin) {
                Mail::to($admin->email)->queue(new TeacherVerification($user));
                $this->notification($admin->id, $user->id, 'teacher_registration', 'Registration', ':name is waiting for teacher approval.');
            }

            $res['msg'] = 'Successfully updated..';
            $res['mode'] = 'free';

            return $this->simpleReturn('success', $res);
        }
        return $this->simpleReturn('error', 'Error in updating values', 500);
    }

    public function destroy($id)
    {
        //
    }

    public function updateRegistration(Request $request)
    {
        $rules = array(
            'name' => 'required',
            'about' => 'required',
            'pathshala_employee' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = $request->user();

        $user_update = User::where('id', $user->id)->update(['name' => $request->name]);
        $employee = $request->pathshala_employee ? 1 : 0;
        $teacher_update = TeacherInfo::where('user_id', $user->id)->update(['about' => $request->about, 'pathshala_employee' => $employee]);

        $upload = false;
        $res = array();
        if ($request->profile_pic) {
            $upload = true;
            $user_info = UserInfo::where('user_id', $user->id)->get()->first();

            // $pic = $request->profile_pic;
            // $file = $this->createFileName($pic, $user->id);
            // $pic->storeAs('/', $file, 'profile');

            $extension = $request->file('profile_pic')->extension();
            $file = mt_rand(100, 999) . time() . '.' . $extension;
            Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('profile_pic'), $file, 'public');

            if ($user_info) {
                $updated = UserInfo::where('user_id', $user->id)->update(['profile_pic' => $file]);
                if ($updated) {
                    $user_info = UserInfo::where('user_id', $user->id)->get()->first();
                    $res['pic'] = $user_info->profile_pic ? $user_info->profile_pic : null;
                    // $res['pic'] = $this->userProfileDp($user->id);`
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
            }
        }

        if ($user_update && $teacher_update) {
            $res['msg'] = 'Successfully updated..';
            return $this->simpleReturn('success', $res);
        } elseif ($user_update) {
            return $this->simpleReturn('success', 'fok');
        } elseif ($teacher_update) {
            return $this->simpleReturn('success', 'sok');
        }
        return $this->simpleReturn('error', 'Error in updating values', 500);
    }

    public function updateVideo(Request $request)
    {
        $rules = array(
            'demo_video' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = $request->user();
        $info = TeacherInfo::where('user_id', $user->id)->get()->first();

        if ($info) {
            // $video = $request->demo_video;
            // $file = $this->createFileName($video, $user->id);
            // $video->storeAs('/', $file, 'demoVideo');
            $extension = $request->file('demo_video')->extension();
            $file = mt_rand(100, 999) . time() . '.' . $extension;
            Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/videos/samples', $request->file('demo_video'), $file, 'public');

            $updated = TeacherInfo::where('user_id', $user->id)->update([
                'demo_video' => $file,
            ]);

            if ($updated) {
                return $this->simpleReturn('success', 'Successfully updated');
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }
        return $this->simpleReturn('error', 'Cannot find teacher info.', 404);
    }

    public function updateProfile(Request $request)
    {
        $rules = array(
            'type' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $user = $request->user();

        if($request->type == 'about'){            
            $info = TeacherInfo::where('user_id', $user->id)->get()->first();
            if ($info) {
                if(!$info->about){
                    $user_profile = User::where('id', $user->id)->get()->first();
                    $user_profile->profile_complete = $user_profile->profile_complete + 5;
                    $user_profile->save(); 
                }

                $updated = TeacherInfo::where('user_id', $user->id)->update([
                    'about' => $request->about,
                ]);
                if ($updated) {
                    return $this->simpleReturn('success', 'Successfully updated');
                }
                return $this->simpleReturn('error', 'error in updation', 500);
            }            

            if ($updated) {
                return $this->simpleReturn('success', 'Successfully updated');
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }
        if($request->type == "bank"){
            $info = UserBankData::where('user_id', $user->id)->get()->first();
            if ($info) {
                $updated = UserBankData::where('id', $info->id)->update([
                    'ac_name' => $request->account_name,
                    'ac_number' => $request->account_number,
                    'ifsc_code' => $request->ifsc,
                    'branch_name' => $request->branch_name,
                    'pancard' => $request->pancard,
                    'gst' => $request->has('gst') ? $request->gst : $info->gst,
                    'company_registration' => $request->has('registration_num') ? $request->registration_num : $info->company_registration,
                    // 'bank_statement' => $file,
                ]);
                if ($updated) {
                    return $this->simpleReturn('success', 'Successfully updated');
                }
                return $this->simpleReturn('error', 'error in updation', 500);
            }else{
                $add = new UserBankData();
                $add->user_id = $user->id;
                $add->ac_name = $request->account_name;
                $add->ac_number = $request->account_number;
                $add->ifsc_code = $request->ifsc;
                $add->branch_name = $request->branch_name;
                $add->pancard = $request->pancard;
                $add->gst = $request->has('gst') ? $request->gst : null;
                $add->company_registration = $request->has('registration_num') ? $request->registration_num : null;
                $add->bank_statement = null;
                $add->status = 'active';
                if ($add->save()) {

                    $user_profile = User::where('id', $user->id)->get()->first();
                    $user_profile->profile_complete = $user_profile->profile_complete + 25;
                    $user_profile->save();
                    
                    return $this->simpleReturn('success', 'Successfully updated');
                }
                return $this->simpleReturn('error', 'error in updation', 500);
            }
        }
        if($request->type == 'address'){
            $info = UserInfo::where('user_id', $user->id)->get()->first();
            if ($info) {
                if(!$info->address1){
                    $user_profile = User::where('id', $user->id)->get()->first();
                    $user_profile->profile_complete = $user_profile->profile_complete + 25;
                    $user_profile->save();
                }
                $updated = UserInfo::where('user_id', $user->id)->update([
                    'address1' => $request->address1,
                    'address2' => $request->address2,
                    'address3' => $request->address3,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'zipcode' => $request->zipcode,
                    'status' => 'active',
                ]);
    
                if ($updated) {
                    return $this->simpleReturn('success', 'Successfully updated');
                }
            }else{
                $add = new UserInfo();
                $add->user_id = $user->id;
                $add->profile_pic = null;
                $add->address1 = $request->address1;
                $add->address2 = $request->address2;
                $add->address3 = $request->address3;
                $add->city = $request->city;
                $add->state = $request->state;
                $add->country = $request->country;
                $add->zipcode = $request->zipcode;
                $add->status = 'active';
                if ($add->save()) {

                    $user_profile = User::where('id', $user->id)->get()->first();
                    $user_profile->profile_complete = $user_profile->profile_complete + 25;
                    $user_profile->save();

                    return $this->simpleReturn('success', 'Successfully updated');
                }
                return $this->simpleReturn('error', 'Error in insertion', 500);
            }
            
            return $this->simpleReturn('error', 'error in updation', 500);
        }
        return $this->simpleReturn('error', 'Cannot find teacher info.', 404);
    }
}
