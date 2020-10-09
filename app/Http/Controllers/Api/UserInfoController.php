<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserInfo;
use Illuminate\Http\Request;
use Storage;
use App\User;
use Validator;

class UserInfoController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $user_id = $request->user()->id;
        $info = UserInfo::where('user_id', $user_id)->get()->first();
        if ($info) {
            return $this->update($request, $info->id);
        }

        $rules = array(
            'mode' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        if ($request->mode == 'profile_pic') {
            // $pic = $request->profile_pic;
            // $file = $this->createFileName($pic, $user_id);
            // $pic->storeAs('/', $file, 'profile');
            $extension = $request->file('profile_pic')->extension();
            $file = mt_rand(100, 999) . time() . '.' . $extension;
            Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('profile_pic'), $file, 'public');

            $add = new UserInfo();
            $add->user_id = $user_id;
            $add->profile_pic = $file;
            $add->address1 = '';
            $add->address2 = '';
            $add->address3 = '';
            $add->city = '';
            $add->state = '';
            $add->country = '';
            $add->zipcode = '';
            $add->status = 'incomplete';
            if ($add->save()) {
                $user_info = UserInfo::where('user_id',$user_id)->get()->first();
                $res['pic'] = $user_info->profile_pic ? $user_info->profile_pic : null;
                // $res['pic'] = $this->userProfileDp($user_id);
                $res['msg'] = 'Successfully updated';
                return $this->simpleReturn('success', $res);
            }
            return $this->simpleReturn('error', 'Error in insertion', 500);
        }else if ($request->mode == 'address') {
           
            $add = new UserInfo();
            $add->user_id = $user_id;
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

                $user_profile = User::where('id', $user_id)->get()->first();
                $user_profile->profile_complete = $user_profile->profile_complete + 25;
                $user_profile->save();

                $res['msg'] = 'Successfully updated';
                return $this->simpleReturn('success', $res);
            }
            return $this->simpleReturn('error', 'Error in insertion', 500);
        }
        return $this->simpleReturn('error', 'Invalid mode', 400);
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
        $rules = array(
            'mode' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user_id = $request->user()->id;
        if ($request->mode == 'profile_pic') {
            $pic = $request->profile_pic;
            if ($pic) {
                // $file = $this->createFileName($pic, $user_id);
                // $pic->storeAs('/', $file, 'profile');
                $extension = $request->file('profile_pic')->extension();
                $file = mt_rand(100, 999) . time() . '.' . $extension;
                Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/images/profiles', $request->file('profile_pic'), $file, 'public');

                $updated = UserInfo::where('user_id', $user_id)->update(['profile_pic' => $file]);
                if ($updated) {
                    $res['pic'] = $this->userProfileDp($user_id);
                    $res['msg'] = 'Successfully updated';

                    return $this->simpleReturn('success', $res);
                }
                return $this->simpleReturn('error', 'error in updation', 500);
            }
            return $this->simpleReturn('error', 'Invalid file.', 400);
        } elseif ($request->mode == 'address') {
            $updated = UserInfo::where('user_id', $user_id)->update([
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
            return $this->simpleReturn('error', 'error in updation', 500);
        }
        return $this->simpleReturn('error', 'Invalid mode', 400);
    }

    public function destroy($id)
    {
        //
    }
}
