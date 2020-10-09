<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\UserMobileSubscriptionController;
use App\Http\Controllers\Controller;
use App\Model\InstitutePayment;
use App\Model\RazorSubscription;
use App\Model\OauthClients;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Model\Pc_AppSettings;
use App\model\SubAdmin;
use App\Model\SubAdminMenu;
use App\Model\MenuSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use Razorpay\Api\Api as RApi;
use Storage;
use Validator;
use Illuminate\Support\Facades\Log;

class MobilePayController extends Controller
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

        $rules = array(
            'amount' => 'required',
            'new_plan_id' => 'required',
            'tax' => 'required',
            'address_id' => 'required',
            'action' => 'required',
            'plan_amount' => 'required',
            'applicable_amount' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $txn_id = strtoupper(str_random(10));
        $user = $request->user();
        $add = new InstitutePayment();
        $add->user_id = $user->id;
        $add->txnid = $txn_id;
        $add->amount = $request->amount;
        $add->mode = 'NA';
        $add->status = 'pending';
        $add->approval_status = 'pending';
        $add->new_plan_id= $request->new_plan_id;
        $add->address_id= $request->address_id;
        $add->tax= $request->tax;
        $add->subscription_id= $request->subscription_id;
        $add->old_plan_id= $request->old_plan_id;
        $add->applicable_amount= $request->applicable_amount;
        $add->plan_amount= $request->plan_amount;
        $add->action= $request->action;
        $add->payment_method= $request->payment_mode;
        if ($add->save()) {
            if($request->payment_mode == 'razorpay'){
                return $this->simpleReturn('success', $add);
            }else if($request->payment_mode == 'pay_u'){
                $txnid = $txn_id;
                $key = env('PAYU_KEY');
                $institute_pay_id = $add->id;
                $productinfo = 'Mobile Payment';
                $firstname = $user->name;
                $email = $user->email;
                $phone = $user->phone;
                $udf1 = $add->id;
                $udf2 = $request->action;
                $salt = env('PAYU_SALT');
                $hash_sequence = $key . '|' . $txnid . '|' . $request->amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|||||||||' . $salt;
                $hash = hash('sha512', $hash_sequence);

                $payudata['key'] = $key;
                $payudata['txnid'] = $txnid;
                $payudata['amount'] = round($request->amount, 2);
                $payudata['hash'] = $hash;
                $payudata['productinfo'] = $productinfo;
                $payudata['institute_pay_id'] = $add->id;
                $payudata['firstname'] = $firstname;
                $payudata['email'] = $email;
                $payudata['phone'] = $phone;
                $payudata['udf1'] = $udf1;
                $payudata['udf2'] = $udf2;
                $payudata['surl'] = env('URL_PATHSHALA_USER') . '/institute/mobile/payment';
                $payudata['furl'] = env('URL_PATHSHALA_USER') . '/institute/mobile/payment';
                $payudata['mode'] = 'dropout';

                return $this->simpleReturn('success', $payudata);
            }

        }
        return $this->simpleReturn('error', 'Error in insertion', 400);

    }

    public function update(Request $request, $id){

        $ins_pay = InstitutePayment::where('id', $id)->get()->first();
        if (!$ins_pay){
            return $this->simpleReturn('error ', 'No Payment Id found found', 404);
        }

        if($request->payment_id){
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
            if($payment->status == "authorized"){
                $ins_pay->amount = ($payment->amount / 100);
                $ins_pay->txnid = $request->payment_id;
                $ins_pay->mode = $payment->method;
                $ins_pay->status = 'success';
                $ins_pay->save();

                return $this->simpleReturn('success', $ins_pay);
            }else{
                // save payment status
                $ins_pay->amount = ($payment->amount / 100);
                $ins_pay->txnid = $request->payment_id;
                $ins_pay->mode = $payment->method;
                $ins_pay->status = 'failed';
                $ins_pay->save();

                return $this->simpleReturn('error', $ins_pay);
            }
        }else{
            $txnid = $request->txnid;

            if ($request->txnStatus == 'SUCCESS') {
                $ins_pay->txnid = $request->txnid;
                $ins_pay->status = 'success';
                $ins_pay->save();

                return $this->simpleReturn('success', $ins_pay);
            }else{
                $ins_pay->txnid = $request->txnid;
                $ins_pay->status = 'failed';
                $ins_pay->save();

                return $this->simpleReturn('error', $ins_pay);
            }
        }



    }

    public function payUupdate(Request $request){
        // return $this->simpleReturn('success', $request->udf2);
        $ins_pay = InstitutePayment::where('id', $request->udf1)->get()->first();
        if (!$ins_pay){
            return $this->simpleReturn('error ', 'No Payment Id found found', 404);
        }

        $txnid = $request->txnid;

        $mode = $request->mode;

        if ($request->txnStatus == 'SUCCESS') {

            $this->processSubscription($request, $ins_pay);

            return redirect(env('WEB_URL') . 'teacher/payment/success/'.$request->udf1);
        }else{
            $ins_pay->status = 'failed';
            $ins_pay->save();

            return redirect(env('WEB_URL') . 'teacher/payment/failed/'.$request->udf1);
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

    public function getHistory(Request $request) {
        $user = $request->user();
        $payment_history = InstitutePayment::where('user_id', $user->id)->where('status', 'success')->where('mode','!=', 'free')->orderBy('id','desc')->get();
        if ($payment_history) {
            return $this->simpleReturn('success', $payment_history);
        }return $this->simpleReturn('error', 'No history found', 404);
    }

    /**
     * @param $user_id
     * @param $user_subscriptions
     */
    public function updateOauthClientValidity($user_id, $user_subscriptions): void
    {
        Log::info('New subscription has been created for ends at=' . $user_subscriptions);
        $client = OauthClients::where('user_id',$user_id)->orderBy('id','desc')->first();
        $client->validity = $user_subscriptions->ends_at;
        $client->save();
    }

    /**
     * @param Request $request
     * @param $ins_pay
     */
    public function processSubscription(Request $request, $ins_pay): void
    {
        $plan = app('rinvex.subscriptions.plan')->find($ins_pay->new_plan_id);
        $user = User::where('id', $ins_pay->user_id)->first();

        if ($request->udf2 == 'SUBSCRIBE') {
            $user_subscriptions = $user->getSubscription('mobile');
            if (!$user_subscriptions) {
                $user_subscriptions = $user->newSubscription('mobile', $plan);
                $user_subscriptions->starts_at = now(env('APP_TIMEZONE', 'Asia/Kolkata'));
                $user_subscriptions->type = 'mobile';
                $user_subscriptions->save();
                $this->updateOauthClientValidity($ins_pay->user_id, $user_subscriptions);
                $this->syncSubadminsMenu($ins_pay->user_id, $plan->id);
            }
        } else if ($request->udf2 == 'UPGRADE') {
            $user_subscriptions = $user->getSubscription('mobile');
            if ($user_subscriptions) {
                $user_subscriptions->changePlan($plan);
                $this->updateOauthClientValidity($ins_pay->user_id, $user_subscriptions);
                $this->syncSubadminsMenu($ins_pay->user_id, $plan->id);
            }
        } else if ($request->udf2 == 'RENEW') {
            $user_subscriptions = $user->getSubscription('mobile');
            if ($user_subscriptions) {
                DB::transaction(function () use ($ins_pay, $user_subscriptions) {
                    $plan = app('rinvex.subscriptions.plan')->find($user_subscriptions->plan_id);
                    if ($plan){
                        $end = new Carbon($user_subscriptions->ends_at->isPast()? now() : $user_subscriptions->ends_at);
                        $method = 'add'.ucfirst($plan->invoice_interval).'s';
                        $user_subscriptions->ends_at = $end->{$method}($plan->invoice_period);
                        $user_subscriptions->canceled_at = null;
                        $user_subscriptions->save();

                        Log::info('New End date for userId=' . $ins_pay->user_id . ' while renewing is endDate='. $user_subscriptions->ends_at);
                        $this->updateOauthClientValidity($ins_pay->user_id, $user_subscriptions);
                    } else {
                        throw  new EntryNotFoundException('Could not find plan '. $user_subscriptions->plan_id);
                    }
                });
            }
        }

        $ins_pay->status = 'success';
        $ins_pay->mode = $request->mode;
        $ins_pay->save();
    }

    /**
     * @param Request $request
     * @param $invoice_id
     * @return JsonResponse
     */
    public function getInvoice(Request $request, $invoice_id)
    {
        $user = $request->user();
        $payment_invoice = InstitutePayment::where('user_id', $user->id)->where('id', $invoice_id)->with('user_info')->with('client_info')->with('address_info')->with('plan_info')->first();
        if ($payment_invoice) {
            $payment_history['invoice'] = $payment_invoice;
            $payment_history['office_address'] = ['name'=>'Aueducation And Solution PVT. LTD.', 'address1'=>'I-2/52, Awas Vikash, Keshavpuram','address2'=>'Kalyanpur,','city'=>'Kanpur', 'state'=>'Uttar Pradesh','country'=>'India', 'pincode'=>'208017','contact'=>'09369880746','gst'=>'GSTIN : 09AARCA5094L1ZW','sac_code'=>'SAC Code: 999294'];
            return $this->simpleReturn('success', $payment_history);
        }
        return $this->simpleReturn('error', 'No history found', 404);
    }


    public function mannualSubscription(Request $request)
    {

        $rules = array(
            'plan_id' => 'required',
            'label' => 'required',
            'given_name' => 'required',
            'street' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'amount' => 'required',
            'institute_id' => 'required',
            'action' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = User::find($request->institute_id);

        // Create a new address
        $address = $user->addresses()->create([
            'label' => $request->label,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'organization' => $request->organization,
            'country_code' => 'IN',
            'street' => $request->street,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'is_primary' => ($request->is_primary == 'true' ) ? 1 : 0,
            'is_billing' => ($request->is_billing == 'true')? 1 : 0,
            'is_shipping' => ($request->is_shipping == 'true') ? 1 : 0 ,
        ]);

        $plan = app('rinvex.subscriptions.plan')->find($request->plan_id);

        // $add = new InstitutePayment();
        // $add->user_id = $request->institute_id;
        // $add->txnid = $request->txn_id;
        // $add->amount = $request->amount;
        // $add->mode = 'cash';
        // $add->status = 'success';
        // $add->approval_status = 'pending';
        // $add->new_plan_id= $request->plan_id;
        // $add->address_id= $address->id;
        // $add->tax= '18';
        // $add->subscription_id= 0;
        // $add->old_plan_id= 0;
        // $add->applicable_amount= $request->amount;
        // $add->plan_amount= $plan->price;
        // $add->action= $request->action;
        // $add->payment_method= 'cash';
        // $add->comment= $request->comment;
        // $add->save();
        if ($plan) {

            if ($request->action == 'SUBSCRIBE') {
                $user_subscriptions = $user->getSubscription('mobile');
                if (!$user_subscriptions) {
                    $user_subscriptions = $user->newSubscription('mobile', $plan);
                    $user_subscriptions->starts_at = now(env('APP_TIMEZONE', 'Asia/Kolkata'));
                    $user_subscriptions->type = 'mobile';
                    $user_subscriptions->save();
                    $this->updateOauthClientValidity($request->institute_id, $user_subscriptions);
                    $this->syncSubadminsMenu($request->institute_id, $plan->id);
                }
            } else if ($request->action == 'UPGRADE') {
                $user_subscriptions = $user->getSubscription('mobile');
                if ($user_subscriptions) {
                    $user_subscriptions->changePlan($plan);
                    $this->updateOauthClientValidity($request->institute_id, $user_subscriptions);
                    $this->syncSubadminsMenu($request->institute_id, $plan->id);
                }
            } else if ($request->action == 'RENEW') {
                $user_subscriptions = $user->getSubscription('mobile');
                if ($user_subscriptions) {
                    DB::transaction(function () use ($request, $user_subscriptions) {
                        $plan = app('rinvex.subscriptions.plan')->find($user_subscriptions->plan_id);
                        if ($plan){
                            $end = new Carbon($user_subscriptions->ends_at->isPast()? now() : $user_subscriptions->ends_at);
                            $method = 'add'.ucfirst($plan->invoice_interval).'s';
                            $user_subscriptions->ends_at = $end->{$method}($plan->invoice_period);
                            $user_subscriptions->canceled_at = null;
                            $user_subscriptions->save();

                            Log::info('New End date for userId=' . $request->institute_id . ' while renewing is endDate='. $user_subscriptions->ends_at);
                            $this->updateOauthClientValidity($request->institute_id, $user_subscriptions);
                        } else {
                            throw  new EntryNotFoundException('Could not find plan '. $user_subscriptions->plan_id);
                        }
                    });
                }
            }
            return $this->simpleReturn('success', "Subcription added successfully");
        }

    }

    /**
     * @param Request $request
     * @param $invoice_id
     * @return JsonResponse
     */
    public function getClientList(Request $request)
    {
        $clients = OauthClients::where('revoked', '0');
        if($request->keyword) {
            $clients->where('id', 'like', '%' .$request->keyword. '%');
            $clients->orWhere('name', 'like', '%' .$request->keyword. '%');
        }
        $clients=$clients->get();
        if ($clients) {
            return $this->simpleReturn('success', $clients);
        }
        return $this->simpleReturn('error', 'No clients found', 404);
    }

    public function getAdminInvoice(Request $request) {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $payment_invoice = InstitutePayment::where('status', 'success')->with('user_info')->with('client_info')->with('address_info')->with('plan_info')->where('mode','!=', 'free')->orderBy('id','desc');

        if($request->plan_id){
			$payment_invoice->where('new_plan_id', $request->plan_id);
        }

        if(($request->startDate && $request->startDate != '')
            && ($request->endDate && $request->endDate !='')) {
            $payment_invoice->where('created_at', '>=', $request->startDate." 00:00:00")
                ->where('created_at' , '<=', $request->endDate." 23:59:59");
		}
        if($request->type == 'all'){
            $payment_invoice = $payment_invoice->get();
        }else{
            $payment_invoice = $payment_invoice->paginate($perPageCount);
        }

        if ($payment_invoice) {
            $payment_history['invoice'] = $payment_invoice;
            $payment_history['office_address'] = ['name'=>'Aueducation And Solution PVT. LTD.', 'address1'=>'I-2/52, Awas Vikash, Keshavpuram','address2'=>'Kalyanpur,','city'=>'Kanpur', 'state'=>'Uttar Pradesh','country'=>'India', 'pincode'=>'208017','contact'=>'09369880746','gst'=>'GSTIN : 09AARCA5094L1ZW','sac_code'=>'SAC Code: 999294'];
            return $this->simpleReturn('success', $payment_history);
        }
        return $this->simpleReturn('error', 'No history found', 404);
    }

    /**
     * @param Request $request
     * @param $invoice_id
     * @return JsonResponse
     */
    public function syncSubadminsMenu($user_id, $plan_id)
    {

        Log::info('subadmin_user_id=' . $user_id);
        Log::info('Purchased Plan=' . $plan_id);
        $clients = OauthClients::where('user_id',$user_id)->orderBy('id','desc')->first();
        if ($clients) {
            $subadmins = SubAdmin::where('oauth_client_id', $clients->id)->whereDate('expires_at', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))->get();
            if($subadmins){     
                foreach($subadmins as $subadmin){
                    $subadmins_menu = SubAdminMenu::where('active',true)
                            ->where('sub_admin_id',$subadmin->id)
                            ->with('menu')
                            ->get();
                            
                    SubAdminMenu::where('sub_admin_id', $subadmin->id)->update([
                        'active' => false
                    ]);

                    foreach($subadmins_menu as $value){                 
                        $menu = MenuSettings::select('id')->where('title', $value->menu->title)->where('plan_id', $plan_id)->where('user_type','teacher')->where('status','active')->first();
                        
                        SubAdminMenu::create([
                            'sub_admin_id' => $subadmin->id,
                            'menu_setting_id' => $menu->id,
                        ]);
                    }   
                }
                return true;
            }else{
                return false;
            }
        }
        return $this->simpleReturn('error', 'No clients found', 404);
    }

}
