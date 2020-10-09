<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Model\Pc_Course;
use App\Model\TeacherInfo;
use App\Model\UserBankData;
use App\Model\UserInfo;
use App\Model\UserPlanLog;
use App\User;
use Illuminate\Http\Request;
use Validator;

class TeacherController extends Controller
{
    public function teacherDetails(Request $request)
    {
        $rules = array(
            'teacher_id' => 'required|numeric',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        } else {
            $user = $this->getUserBasic($request->teacher_id);
            if ($user) {
                $teacher = TeacherInfo::where('user_id', $request->teacher_id)->get()->first();
                $bank = UserBankData::where('user_id', $request->teacher_id)->get()->first();
                $user_info = UserInfo::where('user_id', $request->teacher_id)->get()->first();
                $result['basicdetails'] = $user;
                $result['teacherdetails'] = $teacher;
                $result['bankdetails'] = $bank;
                $result['userinfo'] = $user_info;
                $result['profile_pic'] = $this->userProfileDp($request->teacher_id);
                return $this->simpleReturn('success', $result);
            }
            return $this->simpleReturn('error', 'No user found');
        }
    }

    public function teacherApprovalList(Request $request)
    {
        $list = TeacherInfo::join('users', 'users.id', '=', 'teacher_info.user_id')
            ->where('admin_status', 'pending')
            ->where('status', 'active')
            ->select(['users.id', 'users.name', 'users.email', 'users.phone', 'teacher_info.admin_status', 'teacher_info.created_at'])
            ->get();

        if ($list->count()) {
            $result = array();

            foreach ($list as $key => $list_item) {
                $item['slnum'] = $key + 1;
                $item['user_id'] = $list_item->id;
                $item['name'] = $list_item->name;
                $item['email'] = $list_item->email;
                $item['phone'] = $list_item->phone;
                $item['admin_status'] = $list_item->admin_status;
                $item['reg_date'] = date('d-m-Y', strtotime($list_item->created_at));
                $result[] = $item;
            }
            return $this->simpleReturn('success', $result);
        }
        return $this->simpleReturn('error', 'No data found.');
    }

    // public function getTutorDetails($tutor_id)
    // {
    //     $tutor = TeacherInfo::where('id', $tutor_id)->select('user_id')->get()->first();
    //     if ($tutor) {
    //         $result = array();
    //         $user = User::where('id', $tutor['user_id'])->get()->first();
    //         $result['tutor_name'] = $user['name'];
    //         $result['profile_pic'] = $this->userProfileDp($tutor_id);

    //         return $this->simpleReturn('success', $result);
    //     }
    //     return $this->simpleReturn('error', 'No Tutor found');
    // }
    public function getTutorDetails($id)
    {
        $tutor = TeacherInfo::where('user_id', $id)->select('user_id')->get()->first();
        if ($tutor) {
            $user = User::where('id', $id)->get()->first();
            $result = array();

            $result['tutor_name'] = $user['name'];
            $result['profile_pic'] = $this->userProfileDp($id);
            $result['basic_details'] = $user;
            $result['teacher_info'] = TeacherInfo::where('user_id', $id)->get()->first();
            $result['bank_details'] = UserBankData::where('user_id', $id)->get()->first();
            $result['user_info'] = UserInfo::where('user_id', $id)->get()->first();

            return $this->simpleReturn('success', $result);
        }
        return $this->simpleReturn('error', 'No Tutor found.');
    }

    public function myProfile(request $request)
    {
        $id = $request->user()->id;
        $tutor = TeacherInfo::where('user_id', $id)->select('user_id')->get()->first();
        if ($tutor) {
            $user = User::where('id', $id)->get()->first();
            $result = array();

            $result['tutor_name'] = $user['name'];
            $result['profile_pic'] = $this->userProfileDp($id);
            $result['basic_details'] = $user;
            $result['teacher_info'] = TeacherInfo::where('user_id', $id)->get()->first();
            $result['bank_details'] = UserBankData::where('user_id', $id)->get()->first();
            $result['user_info'] = UserInfo::where('user_id', $id)->get()->first();
            $result['bank_statement'] = $this->getBankStatement($id);

            return $this->simpleReturn('success', $result);
        }
        return $this->simpleReturn('error', 'No Tutor found.', 404);
    }

    public function teacherList(Request $request)
    {
        $list = TeacherInfo::join('users', 'users.id', '=', 'teacher_info.user_id')
            ->where('admin_status', 'approve')
            ->select(['users.id', 'users.name', 'users.email', 'users.phone', 'teacher_info.status', 'teacher_info.created_at', 'teacher_info.type'])
            ->get();

        if ($list->count()) {
            $result = array();
            foreach ($list as $key => $list_item) {
                $item['slnum'] = $key + 1;
                $item['user_id'] = $list_item->id;
                $item['name'] = $list_item->name;
                $item['email'] = $list_item->email;
                $item['phone'] = $list_item->phone;
                $plan = UserPlanLog::where('user_id', $list_item->id)->where('status', 'active')->get()->first();
                // $item['membership'] = $this->getPlanName($plan['plan_id']);
                $item['membership'] = $this->getPlanName($plan->plan_id);
                $item['courses'] = Pc_Course::where('user_id', $list_item->id)->where('status', 'published')->count();
                $item['status'] = $list_item->status;
                $item['regdate'] = date('d-m-Y', strtotime($list_item->created_at));
                $item['type'] = $list_item->type;

                $result[] = $item;
            }
            return $this->simpleReturn('success', $result);
        }
        return $this->simpleReturn('error', 'No data found.');
    }


    public function allTeacherList(Request $request)
    {
        $list = TeacherInfo::join('users', 'users.id', '=', 'teacher_info.user_id')
            ->where('admin_status', 'approve')
            ->select(['users.id', 'users.name', 'users.email', 'users.phone', 'teacher_info.status', 'teacher_info.created_at', 'teacher_info.type'])
            ->get();

        if ($list->count()) {
            
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No data found.');
    }
}
