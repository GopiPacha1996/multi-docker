<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationApproval;
use App\Model\TeacherInfo;
use App\Model\InstitutePayment;
use App\User;
use Illuminate\Http\Request;
use Mail;
use Validator;

class TeacherApprovalController extends Controller
{

    public function index()
    {
        $list = TeacherInfo::where('admin_status', 'pending')
            ->where('status', 'active')
            ->has('user')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        if ($list->count()) {
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No pending requests found..', 404);
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
        $user = User::where('id', $id)->with('teacher_info')->with('user_info')->with('bank_info')->get()->first();
        if ($user) {
            $media['bank'] = $this->mediaUrl('bank-details');
            $media['demo'] = $this->mediaUrl('demo');
            $media['profile'] = $this->mediaUrl('profile');
            $user['media'] = $media;
            return $this->simpleReturn('success', $user);
        }
        return $this->simpleReturn('error', 'No user found..', 404);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $rules = array(
            'status' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        $update = array(
            'admin_status' => $request->status,
            'admin_comment' => $request->comment,
            'done_by' => $request->user()->id,
            'pathshala_employee' => $request->pathshala_employee ? 1 : 0,
        );
        if (TeacherInfo::where('user_id', $id)->update($update)) {
            if ($request->status == 'approve' && $request->pathshala_admin) {
                User::where('id', $id)->assignRole('admin');
            }

            if ($request->status == 'approve') {
                $user = User::where('id', $id)->get()->first();
                if ($user) {
                    Mail::to($user->email)->queue(new RegistrationApproval($user));

                    $this->notification($id, $request->user()->id, 'teacher_approved', 'Approved', 'Your Profile has been approved. Now you may publish your first course.');
                }

            }

            return $this->simpleReturn('success', 'Success');
        } else {
            return $this->simpleReturn('error', 'Error', 400);
        }
    }

    public function destroy($id)
    {
        //
    }
    public function allPendingInstituteList(Request $request)
    {
        $list = InstitutePayment::join('users', 'users.id', '=', 'institute_payment.user_id')
            ->join('oauth_clients', 'oauth_clients.user_id', '=', 'institute_payment.user_id')
            ->where('approval_status', 'pending')
            ->where('status', 'success')
            ->select(['users.id as user_id', 'users.name', 'users.email', 'users.phone', 'institute_payment.status', 'institute_payment.id', 'institute_payment.created_at', 'institute_payment.mode', 'institute_payment.amount', 'oauth_clients.validity'])
            ->get();

        if ($list->count()) {
            
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No data found.');
    }
}
