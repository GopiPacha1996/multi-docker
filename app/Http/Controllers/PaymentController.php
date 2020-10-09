<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\InstructorRegistration;
use App\Mail\TeacherVerification;
use App\Model\Payments;
use App\Model\TeacherInfo;
use App\User;
use Mail;

class PaymentController extends Controller
{
    public function paymentResponse(Request $request)
    {
        if ($request->txnStatus == 'SUCCESS') {
            $txnid = $request->txnid;

            $checkout = Payments::where('txnid', $txnid)->get()->first();
            if ($checkout) {
                $update = Payments::where('txnid', $txnid)->update(['status' => 'success']);
                $infoUpdate = TeacherInfo::where('user_id', $checkout->user_id)->update([
                    'plan_log_id' => $checkout->id,
                    'plan_id' => $checkout->plan_id,
                    'status' => 'active',
                    'admin_status' => 'pending'
                ]);

                if ($infoUpdate) {
                    $info = TeacherInfo::where('user_id', $checkout->user_id)->get()->first();
                    $role = 'educator';
                    if ($info) {
                        $role = $info->type == 'institute' ? 'institute' : $role;
                    }
                    $user = User::where('id', $checkout->user_id)->get()->first();
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
                }
            }

            // payu payments
            // $add_pp = new PayuPayments();
            // $add_pp->account = 'test';
            // $add_pp->txnid = $txnid;
            // $add_pp->mihpayid = $request->mihpayid;
            // $add_pp->firstname = $request->firstname;
            // $add_pp->email = $request->email;
            // $add_pp->phone = $request->phone;
            // $add_pp->amount = $request->amount;
            // $add_pp->discount = $request->discount;
            // $add_pp->net_amount_debit = $request->net_amount_debit;
            // $add_pp->data = '';
            // $add_pp->status = $request->status;
            // $add_pp->unmappedstatus = $request->unmappedstatus;
            // $add_pp->save();

            return redirect(env('WEB_URL') . 'teacher/dashboard/');

        } else {
            $status = $request->txnStatus == 'FAILED' ? 'failed' : 'cancelled';
            if ($request->txnid) {
                $update = Payments::where('txnid', $txnid)->update(['status' => $status]);
            }

            return redirect(env('WEB_URL'));
        }
    }
}
