<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\InstitutePayment;
use App\Model\RazorSubscription;
use App\Model\OauthClients;
use App\Model\Pc_AppSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Razorpay\Api\Api as RApi;
use Storage;
use Validator;

class RazorpayController extends Controller
{
    private $api;
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * RazorpayController constructor.
     * @param ClientRepository $clientRepository
     * @param RApi $api
     */
    public function __construct(ClientRepository $clientRepository, RApi $api)
    {
//        $api = new RApi(env("RPAY_KEY","rzp_test_3Yal3Yt44eXwut"), env("RPAY_SECRET","2GcSQUVnHF4YlqQWyluIcjZ9"));
        $this->clientRepository = $clientRepository;
        $this->api = $api;
    }

    public function index(Request $request) {
        $user = $request->user();
		if ($user->hasRole(['educator', 'institute','quiz'])) {
			$tutor = User::where('id', $user->id)->with('user_info')->with('client_info')->first();
			if ($tutor) {
				return $this->simpleReturn('success', $tutor);
			}return $this->simpleReturn('error', 'No details found', 404);

		}
    }

    public function store(Request $request)
    {
        //Fetch payment information by razorpay_payment_id
        $payment = $this->api->payment->fetch($request->payment_id);
        $reponse = array(
            'amount' => $payment->amount,
            'status' => $payment->status,
            'email' => $payment->email,
            'method' => $payment->method,
            'notes' => $payment->notes,
            'created_at' => $payment->created_at,
        );
        if(($payment->status == "authorized") || ($payment->status == "refunded")){
            // save payment status
            $user = $request->user();
            $add = new InstitutePayment();
            $add->user_id = $user->id;
            $add->amount = ($payment->amount / 100);
            $add->mode = $payment->method;
            $add->approval_status = 'pending';
            $add->txnid = $request->payment_id;
            $add->status = 'success';
            $add->save();
            
            return $this->simpleReturn('success', "successful");
        }else{
            // save payment status
            $user = $request->user();
            $add = new InstitutePayment();
            $add->user_id = $user->id;
            $add->amount = ($payment->amount / 100);
            $add->mode = $payment->method;
            $add->approval_status = 'pending';
            $add->txnid = $request->payment_id;
            $add->status = 'failed';
            $add->save();
            return $this->simpleReturn('error', "failed");
        }
    }

    public function deactivate(Request $request)
    {
        $client = OauthClients::where('user_id', $request->userId)->get()->first();

        if($client){
            $client_delete = OauthClients::where('user_id',$request->userId)->update([
                'revoked' => 1,
                'issue_status' => '3',
            ]);
            if ($client_delete) {
                RazorSubscription::where('subscription_id',$request->subscription_id)->update([
                    'status' => 'cancelled'
                ]);
                return $this->simpleReturn('success', 'Successfully deactive');
            }
        }
        return $this->simpleReturn('error', 'deletion error', 400);
    }

    public function instituteAction(Request $request){
        $payment = InstitutePayment::where('id', $request->action_id)->get()->first();

        if($payment){
            $payment_find = InstitutePayment::where('id',$request->action_id)->update([
                'approval_status' => $request->action,
            ]);
            if ($payment_find) {
                // update issue status and validy in oauth client
                $client_update = OauthClients::where('user_id',$payment->user_id)->update([
                    'issue_status' => '2',
                    'validity' => $request->validity,
                    'gcp_credential' => $request->gcp_credential,
                    'credential_url' => $request->credential_url,
                    'gcp_account' => $request->gcp_account,
                    'gcp_project' => $request->gcp_project,
                    'package_id' => $request->package_id,
                    
                ]);
                $clients = OauthClients::where('user_id', $payment->user_id)->where('revoked','0')->first();
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
                return $this->simpleReturn('success', "Successful");
            }
        }
        return $this->simpleReturn('error', 'deletion error', 400);
    }

}
