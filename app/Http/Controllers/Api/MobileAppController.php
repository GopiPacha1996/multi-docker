<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\InstitutePayment;
use App\Model\RazorSubscription;
use App\Model\OauthClients;
use App\Model\Pc_AppSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Razorpay\Api\Api as RApi;
use Storage;
use Validator;

class MobileAppController extends Controller
{
    // type=all&value=&status=all&start_date=2020-05-19&end_date=2020-05-19
    public function index(Request $request) {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;

        $list = InstitutePayment::select('institute_payment.*')
        ->where('institute_payment.status','success')
        ->join('oauth_clients', 'oauth_clients.user_id', '=', 'institute_payment.user_id')
        ->join('users', 'users.id', '=', 'institute_payment.user_id')
        ->with('client_info')
        ->with('user_info');
        
        if(($request->status) && ($request->status != 'all')){
            $list->where('oauth_clients.issue_status', $request->status);
        }
        if(($request->type) && ($request->type == 'name')){
            $list->where('oauth_clients.name', 'like', '%' . $request->value . '%');
        }
        if(($request->type) && ($request->type == 'client_id')){
            $list->where('oauth_clients.id', $request->value);
        }
        if(($request->type) && ($request->type == 'gcp_account')){
            $list->where('oauth_clients.gcp_account', 'like', '%' . $request->value . '%');
        }
        if(($request->type) && ($request->type == 'gcp_project')){
            $list->where('oauth_clients.gcp_project', 'like', '%' . $request->value . '%');
        }
        if(($request->type) && ($request->type == 'package_id')){
            $list->where('oauth_clients.package_id', 'like', '%' . $request->value . '%');
        }
    
        if(($request->type) && ($request->type == 'phone')){
            $list->where('users.phone', 'like', '%' . $request->value . '%');
        }
        if(($request->type) && ($request->type == 'email')){
            $list->where('users.email', 'like', '%' . $request->value . '%');
        }
        
        if(($request->start_date && $request->start_date != '')
            && ($request->end_date && $request->end_date !='')) {
            $list->where('oauth_clients.created_at', '>=', $request->start_date." 00:00:00")
                ->where('oauth_clients.created_at' , '<=', $request->end_date." 23:59:59");
        }
        
        $list=$list->paginate($perPageCount);;

        if ($list->count()) {            
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No data found.');
    }

    public function update(Request $request){
        $payment = InstitutePayment::where('id', $request->action_id)->get()->first();

        if($payment){
            $payment_find = InstitutePayment::where('id',$request->action_id)->update([
                'approval_status' => $request->action,
            ]);
            if ($payment_find) {
                // update issue status and validy in oauth client
                $clients = OauthClients::where('user_id', $payment->user_id)->where('revoked','0')->first();
                Log::info($request->hasFile('upload_file'));
                if($request->hasFile('upload_file')){
                    $getFile = file_get_contents($request->file('upload_file'));

                    $extension = $request->file('upload_file')->extension();
                    $firebase_file_name = $clients->id . '-firebase.' . $extension;
                    Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/clients/firebase/'.$clients->id, $request->file('upload_file'), $firebase_file_name);
                    $clients->gcp_credential = $getFile;

                    $clients->credential_url = env('DO_SPACES_DRIVE') . '/clients/firebase/'.$clients->id.'/'.$firebase_file_name;
                }
                // return $this->simpleReturn('success', $clients);
                if($request->gcp_account){
                    $clients->gcp_account = $request->gcp_account;
                }
                if($request->gcp_project){
                    $clients->gcp_project = $request->gcp_project;
                }
                if($request->package_id){
                    $clients->package_id = $request->package_id;
                }
                if($request->validity){
                    $clients->validity = $request->validity;
                }
                $clients->issue_status = '2';
                $clients->save();

                if($request->action == 'approved'){
                    // add home settings
                    $settings  = ['quiz', 'youtube_live', 'course', 'featured_courses', 'popular_course'];
                    foreach ($settings as $setting){
                        $each = Pc_AppSettings::where('type', $setting)->where('is_institute', 0)->first();
                        if ($each){
                            $newrecord = $each->replicate();
                            $newrecord->institute_id = $clients->id;
                            $newrecord->is_institute = '1';
                            $newrecord->user_id = $payment->user_id;
                            $newrecord->save();
                        }
                    }
                }
                
                return $this->simpleReturn('success', "Successful");
            }
        }
        return $this->simpleReturn('error', 'deletion error', 400);
    }
}
