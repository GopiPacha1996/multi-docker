<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InstructorRegistration;
use App\Mail\TeacherVerification;
use App\Model\TeacherInfo;
use App\Model\UserInfo;
use App\Model\Plan;
use App\Model\Pc_BaseSettings;
use App\User;
use Illuminate\Http\Request;
use Mail;
use Storage;
use Validator;

class QuizCreatorController extends Controller
{

    public function index(Request $request)
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
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
        
        $user = $request->user();
        $updated = TeacherInfo::where('user_id', $user->id)
                    ->update(['status' => 'active', 'admin_status' => 'pending']);

        if ($updated) {
            $info = TeacherInfo::where('user_id', $user->id)->get()->first();
            $role = $request->register_type;
            if ($info) {
                $role = $role;
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
}
