<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Model\TeacherInfo;
use Illuminate\Http\Request;
use Validator;

class AdminController extends Controller
{
    public function teacherVerification(Request $request)
    {
        $rules = array(
            'user_id' => 'required|numeric',
            'status' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        } else {
            $update = array(
                'admin_status' => $request->status,
                'admin_comment' => $request->comment,
                'done_by' => $request->user()->id,
                'pathshala_employee' => $request->pathshala_employee ? 1 : 0,
            );
            if (TeacherInfo::where('user_id', $request->user_id)->update($update)) {
                User::where('id', $request->user_id)->assignRole('educator');
                return $this->simpleReturn('success', 'Success');
            } else {
                return $this->simpleReturn('error', 'Error');
            }
        }
    }

}
