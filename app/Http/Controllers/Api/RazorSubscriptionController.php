<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\RazorPlan;
use App\Model\RazorSubscription;
use App\Model\OauthClients;
use App\Model\InstitutePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Razorpay\Api\Api as RApi;
use Razorpay\Api\Errors\BadRequestError;

class RazorSubscriptionController extends Controller
{
    /**
     * @var RApi
     */
    private $api;
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * RazorPlanController constructor.
     * @param ClientRepository $clientRepository
     * @param RApi $api
     */
    public function __construct(ClientRepository $clientRepository, RApi $api)
    {
        $this->api = $api;
        $this->clientRepository = $clientRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $plan_id = $request->query('plan_id');
        $status = $request->query('status');

        $subs = RazorSubscription::where('user_id', Auth::id());
        if($type){
            $subs  = $subs->whereHas('plan', function ($q) use ($type){
                $q->where('type', '=', $type);
            });
        }

        if($plan_id){
            $subs = $subs->where('plan_id', $plan_id);
        }

        if($status){
            $subs = $subs->whereIn('status', $status);
        }

        $subs = $subs->orderBy('id', 'desc')->first();

        if ($subs && $subs->count()) {
            return $this->simpleReturn('success', $subs);
        }

        return $this->simpleReturn('error', 'No data found...', 404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = [
            'plan_id' => 'required',
            'stage' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        try{
            $plan = RazorPlan::findOrFail(Input::get('plan_id'));

            if(!$plan){
                return $this->simpleReturn('error', "Could not find plan_id in table", 400);
            }

            if('init' == Input::get('stage')){
                $subscription  = $this->api->subscription->create(array(
                        'plan_id' => $plan->plan_id,
                        'total_count' => env('RPAY_TOTAL_CYCLE', 60) / $plan->interval,
                        'start_at' => Carbon::now(env('APP_TIMEZONE', 'Asia/Kolkata'))->addDays($plan->trail)->timestamp,
                    )
                );
                if($subscription){
                    Log::info("Subscription for user has been created. userId=". Auth::id());
                    $subs = new RazorSubscription();
                    $subs = $this->createUpdateSubscription($subs, $subscription, $plan);
                    return $this->simpleReturn('success', $subs);
                }
            } else if ('done' == Input::get('stage')){
                $subs = RazorSubscription::where('subscription_id', Input::get('subscription_id'))->first();
                if(!$subs) {
                    return $this->simpleReturn('error', "Could not find subscription_id in table", 400);
                }
                $subscription = $this->api->subscription->fetch(Input::get('subscription_id'));
                if(!$subscription) {
                    return $this->simpleReturn('error', "Could not find subscription detail from  pay gateway", 400);
                }


                $subs = $this->createUpdateSubscription($subs, $subscription, $plan);

                $client = $this->clientRepository->create(Auth::id(), Auth::user()->name, env('URL_PATHSHALA_USER'), true, true);
                $client->issue_status ='1';
                $client->validity = $subs->end_at;
                $client->save();

                $accessClient = Passport::personalAccessClient();
                $accessClient->client_id = $client->id;
                $accessClient->save();

                return $this->simpleReturn('success', $subs);

            } else if ('failed' == Input::get('stage')){
                $subs = RazorSubscription::where('subscription_id', Input::get('subscription_id'))->first();
                if(!$subs) {
                    return $this->simpleReturn('error', "Could not find subscription_id in table", 400);
                }
                $subs->status = 'failed';
                $subs->save();
                return $this->simpleReturn('success', "Subscription has been updated successfully");
            } else {
                return $this->simpleReturn('error', 'Not a valid stage - Please contact support!', 500);
            }
        }catch (BadRequestError $e){
            Log::error($e);
            return $this->simpleReturn('error', $e->getMessage(), 400);
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }
        return  $this->simpleReturn('success', 'Subscription is created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param RazorSubscription $razorSubscription
     * @return void
     */
    public function show(RazorSubscription $razorSubscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param RazorSubscription $razorSubscription
     * @return void
     */
    public function edit(RazorSubscription $razorSubscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param RazorSubscription $razorSubscription
     * @return void
     */
    public function update(Request $request, RazorSubscription $razorSubscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param RazorSubscription $razorSubscription
     * @return void
     */
    public function destroy(RazorSubscription $razorSubscription)
    {
        //
    }

    /**
     * @param RazorSubscription $subs
     * @param $subscription
     * @param $plan
     * @return RazorSubscription
     */
    public function createUpdateSubscription(RazorSubscription $subs, $subscription, $plan)
    {
        $tz = env('APP_TIMEZONE', 'Asia/Kolkata');
        $ft = 'Y-m-d H:m:s';
        $subs->user_id = Auth::id();
        $subs->subscription_id = $subscription->id;
        $subs->plan_id = $plan->id;
        $subs->entity = $subscription->entity;
        $subs->customer_id = isset($subscription->customer_id) ? $subscription->customer_id : null;
        $subs->short_url = isset($subscription->short_url) ? $subscription->short_url : null;
        $subs->status = $subscription->status;
        $subs->current_start = isset($subscription->current_start) ? Carbon::parse($subscription->current_start)->timezone($tz)->format($ft) : null;
        $subs->current_end = isset($subscription->current_end) ? Carbon::parse($subscription->current_end )->timezone($tz)->format($ft) : null;
        $subs->ended_at = isset($subscription->ended_at) ? Carbon::parse($subscription->ended_at)->timezone($tz)->format($ft) : null;
        $subs->start_at = isset($subscription->start_at) ? Carbon::parse($subscription->start_at)->timezone($tz)->format($ft) : null;
        $subs->end_at = isset($subscription->end_at) ? Carbon::parse($subscription->end_at)->timezone($tz)->format($ft) : null;
        $subs->created_at = isset($subscription->created_at) ? Carbon::parse($subscription->created_at)->timezone($tz)->format($ft) : null;
        $subs->quantity = isset($subscription->quantity) ? $subscription->quantity : null;
        $subs->total_count = isset($subscription->total_count) ? $subscription->total_count : null;
        $subs->paid_count = isset($subscription->paid_count) ? $subscription->paid_count : null;
        $subs->remaining_count = isset($subscription->remaining_count) ? $subscription->remaining_count : null;
        $subs->customer_notify = isset($subscription->customer_notify) ? $subscription->customer_notify : null;

        $subs->save();

        return $subs;
    }

    public function appUser(Request $request)
	{
		$perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $users = RazorSubscription::whereNotIn('status',['failed','cancelled','created'])->with('plan')->with('client_info')
        ->with(['user' => function($query) use($request) {
            if(($request->keyword) && ($request->keyword != '')){
                $query->where('email', 'like', '%' . $request->keyword . '%');
                $query->orWhere('phone', 'like', '%' . $request->keyword . '%');
            }
        }]);
        if(($request->startDate && $request->startDate != '')
            && ($request->endDate && $request->endDate !='')) {

            $users->where('created_at', '>=', $request->startDate." 00:00:00")
                ->where('created_at' , '<=', $request->endDate." 23:59:59");

		}

		$users = $users->paginate($perPageCount);

		if($users->count()){
			return $this->simpleReturn('success', $users);
		}
		return $this->simpleReturn('error', 'No Users found', 404);
    }

    public function appUserAction(Request $request)
	{
		$client = OauthClients::where('user_id', $request->userId)->first();

        if($client){
            $client->revoked = $request->status;
            $client->save();
            if ($client) {
                return $this->simpleReturn('success', 'Successfully status changed ');
            }

        }
        return $this->simpleReturn('error', 'updation error', 400);
    }
    public function freeTrial(Request $request)
	{
        $user_id = Auth::id();

        $user = $request->user();
        $add = new InstitutePayment();
        $add->user_id = $user->id;
        $add->amount = 0;
        $add->mode = 'free';
        $add->approval_status = 'pending';
        $add->txnid = 'NA';
        $add->status = 'success';
        $add->save();

        if($add)
        {
            $client = $this->clientRepository->create(Auth::id(), Auth::user()->name, env('URL_PATHSHALA_USER'), true, true);
            $client->issue_status ='1';
            $client->validity = Carbon::now(env('APP_TIMEZONE', 'Asia/Kolkata'))->addDays(env('MOBILE_TRAIL_PERIOD', 3));
            $client->save();

            $accessClient = Passport::personalAccessClient();
            $accessClient->client_id = $client->id;
            $accessClient->save();

            return $this->simpleReturn('success', 'Client id generated Successfuly');
        }

        return $this->simpleReturn('error', 'updation error', 500);
    }

}
