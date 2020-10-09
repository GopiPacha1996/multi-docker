<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\InstitutePayment;
use App\Model\OauthClients;
use App\model\SubAdmin;
use App\Model\SubAdminMenu;
use App\Model\MenuSettings;
use Carbon\Carbon;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserMobileSubscriptionController extends Controller
{

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function mine(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $user_subscriptions = $user->getSubscription($request->type);
            $response = [];
            if($user_subscriptions) {
                Log::debug('Fetch plan details for plan='. $user_subscriptions->plan_id);
                $plan = app('rinvex.subscriptions.plan')->find($user_subscriptions->plan_id);
                $response = $this->buildSubscriptionResponse($request, $user_subscriptions, $plan);

            } else {
                $oauth = OauthClients::where('user_id', $user->id)->where('revoked', 0)->first();
                $trial_validity_expired = false;
                $trial_ends_at = null;
                if($oauth){
                    $validity = Carbon::parse($oauth->validity)->timezone('Asia/Kolkata');
                    $trial_validity_expired = $validity->isPast() ? true : false;
                    $trial_ends_at = $oauth->validity;
                }

                $response = [
                    'subscribed'                => false,
                    'oauth_exists'              => $oauth ? true : false,
                    'trial_validity_expired'    => $trial_validity_expired,
                    'trial_expired_message'     => 'Your trial has expired. Please upgrade to continue the service.',
                    'trail_ends_at'             => $trial_ends_at
                ];
            }

            return $this->simpleReturn('success', $response);
        } catch (\Exception $exception){
            Log::error('Exception occurred while fetching subscription');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }

    /**
     * @param Request $request
     * @param $user_subscriptions
     * @param $plan
     * @return array
     */
    public function buildSubscriptionResponse(Request $request, $user_subscriptions, $plan): array
    {
        $max_value_plan = app('rinvex.subscriptions.plan')
            ->where('type', $request->type)
            ->where('is_active', 1)
            ->orderBy('price', 'DESC')
            ->first();

        $days_remaining = Carbon::now('Asia/Kolkata')->diffInDays($user_subscriptions->ends_at);

        $message = null;
        $show_message = false;
        if ($days_remaining <= 7) {
            $show_message = true;
            $s = $days_remaining > 0 ? 's' : '';
            $message = 'Your subscription is about to expire in ' . $days_remaining
                . ' day' . $s
                . '. Please make a payment to have uninterrupted service';
        }

        $diff_amount = $max_value_plan->price - $plan->price;
        $response = [
            'subscription'          => [
                'id'                => $user_subscriptions->id,
                'name'              => $user_subscriptions->name,
                'trial_ends_at'     => $user_subscriptions->trial_ends_at->toDateTimeString(),
                'starts_at'         => $user_subscriptions->starts_at->toDateTimeString(),
                'ends_at'           => $user_subscriptions->ends_at->toDateTimeString(),
                'days_remaining'    => $days_remaining,
                'show_message'      => $show_message,
                'message'           => $message,
            ],
            'plan'                  => $plan->only(['id', 'type', 'name', 'description', 'price']),
            'subscribed'            => true,
            'should_upgrade'        => $plan->price < $max_value_plan->price ? true : false,
            'diff_amount_message'   => $diff_amount > 0 ?  'Pay only ' . $diff_amount . ' more per billing cycle to upgrade to ' . $max_value_plan->name : '',
            'max_value_plan'        => $max_value_plan->only(['id', 'type', 'name', 'description', 'price', 'invoice_period', 'invoice_interval']),
        ];
        return $response;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function upgrade(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
                'new_plan_id' => 'required',
                'subscription_id' => 'required',
                'client_id' => 'required',
                'payment_id' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $plan = app('rinvex.subscriptions.plan')->find($request->new_plan_id);
            if(!$plan){
                return $this->simpleReturn('error', 'Could not find plan', 404);
            }

            $pay_id = InstitutePayment::where('id',$request->payment_id)->first();
            if(!$pay_id){
                return $this->simpleReturn('error', 'Could not find payment id', 404);
            }

            $subscription = app('rinvex.subscriptions.plan_subscription')->find($request->subscription_id);
            if(!$subscription){
                return $this->simpleReturn('error', 'Could not find plan', 404);
            }

            Log::info('Changing the plan for userid=' . $user->id);

            $subscription->changePlan($plan);
            $this->updateOauthCleintValidity($request, $subscription);
            $response = $this->buildSubscriptionResponse($request, $subscription, $plan);
            $this->syncSubadminsMenu($request->client_id,$plan);

            $pay_id->subscription_id = $subscription->id;
            $pay_id->save();

            return $this->simpleReturn('success', $response);
        } catch (\Exception $exception){
            Log::error('Excpetion occured while fetching subscription');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribe(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
                'new_plan_id' => 'required',
                'client_id' => 'required',
                'payment_id' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $plan = app('rinvex.subscriptions.plan')->find($request->new_plan_id);
            if(!$plan){
                return $this->simpleReturn('error', 'Could not find plan', 404);
            }

            $pay_id = InstitutePayment::where('id',$request->payment_id)->first();
            if(!$pay_id){
                return $this->simpleReturn('error', 'Could not find payment id', 404);
            }

            $user_subscriptions = $user->newSubscription($request->type, $plan);
            $user_subscriptions->starts_at = now(env('APP_TIMEZONE', 'Asia/Kolkata'));
            $user_subscriptions->type = 'mobile';
            $user_subscriptions->save();
            Log::info('New subscription has been created for user_id=' . $user ->id);

            Log::info('Fetching the subscription details for user_id=' . $user ->id);
            $user_subscriptions = $user->getSubscription($request->type);

            $this->updateOauthCleintValidity($request, $user_subscriptions);

            $pay_id->subscription_id = $user_subscriptions->id;
            $pay_id->save();

            $response = $this->buildSubscriptionResponse($request, $user_subscriptions, $plan);

            return $this->simpleReturn('success', $response);
        } catch (\Exception $exception){
            Log::error('Exception occurred while assigning subscription');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
                'client_id' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $user->getSubscription($request->type)->cancel(true);

            $user_subscriptions = $user->getSubscription($request->type);
            $this->updateOauthCleintValidity($request, $user_subscriptions);

            return $this->simpleReturn('success', 'Your subscription has been cancelled');
        } catch (\Exception $exception){
            Log::error('Exception occurred while cancelling subscription');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function renew(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
                'client_id' => 'required',
                'payment_id' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $pay_id = InstitutePayment::where('id',$request->payment_id)->first();
            if(!$pay_id){
                return $this->simpleReturn('error', 'Could not find payment id', 404);
            }

            $user_subscriptions = $user->getSubscription($request->type);

            if ($user_subscriptions) {
                DB::transaction(function () use ($request, $user, $user_subscriptions) {
                    $plan = app('rinvex.subscriptions.plan')->find($user_subscriptions->plan_id);
                    if ($plan){
                        $end = new Carbon($user_subscriptions->ends_at->isPast()? now() : $user_subscriptions->ends_at);
                        $method = 'add'.ucfirst($plan->invoice_interval).'s';
                        $user_subscriptions->ends_at = $end->{$method}($plan->invoice_period);
                        $user_subscriptions->canceled_at = null;
                        $user_subscriptions->save();

                        Log::info('New End date for userId=' . $user->id . ' while renewing is endDate='. $user_subscriptions->ends_at);
                        $this->updateOauthCleintValidity($request, $user_subscriptions);
                    } else {
                        throw  new EntryNotFoundException('Could not find plan '. $user_subscriptions->plan_id);
                    }
                });
            }

            $pay_id->subscription_id = $user_subscriptions->id;
            $pay_id->save();

            return $this->simpleReturn('success', 'Your subscription has been renewed');
        } catch (\Exception $exception){
            Log::error('Exception occurred while renewing subscription');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function proRatedCalculation(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = array(
                'type' => 'required',
                'old_plan_id' => 'required',
                'new_plan_id' => 'required',
                'subscription_id' => 'required',
            );

            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors());
            }

            $plan = app('rinvex.subscriptions.plan')->find($request->new_plan_id);
            if(!$plan){
                return $this->simpleReturn('error', 'Could not new find plan', 404);
            }

            $old_plan = app('rinvex.subscriptions.plan')->find($request->old_plan_id);
            if(!$old_plan){
                return $this->simpleReturn('error', 'Could not find old plan', 404);
            }

            $subscription = app('rinvex.subscriptions.plan_subscription')
                ->where('id', $request->subscription_id)
                ->first();
            if(!$subscription){
                return $this->simpleReturn('error', 'Could not find subscription', 404);
            }


            if($plan->price < $old_plan->price){
                return $this->simpleReturn('error', 'You can not downgrade to lower plan', 404);
            }
            $response = [];

            $tz = 'Asia/Kolkata';

            $days_total = $subscription->ends_at->diffInDays($subscription->starts_at);
            $days_remaining = Carbon::now('Asia/Kolkata')->diffInDays($subscription->ends_at);

            if($subscription->active()) {
                $applicable_amount = 0;
                if($subscription->starts_at->isFuture()) {
                    $applicable_amount = $plan->price - $old_plan->price;
                } else if ($subscription->starts_at->isPast() && $subscription->ends_at->isFuture()){
                    $iterations = 1;
                    $lower_to_upper = false;
                    $method = 'add'.ucfirst($old_plan->invoice_interval).'s';
                    $ends_at = $subscription->starts_at->{$method}($old_plan->invoice_period);

                    if($old_plan->id != $plan->id){
                        if(($old_plan->invoice_interval == $plan->invoice_interval)
                            && ($old_plan->invoice_period == $plan->invoice_period)){
                            if ($subscription->ends_at->gte($ends_at)){
                                $iterations = ($subscription->ends_at->diffInDays($subscription->starts_at)) / ($ends_at->diffInDays($subscription->starts_at));
                            }
                        } else{
                            $iterations = ($subscription->ends_at->diffInDays($subscription->starts_at)) / ($ends_at->diffInDays($subscription->starts_at));
                            $lower_to_upper = true;
                        }

                        $remaining_amount = ceil((($iterations * $old_plan->price) * $days_remaining) / $days_total);
                        $new_plan_total_price = ceil($lower_to_upper ? $plan->price : $plan->price * $iterations);
                        $applicable_amount = ceil($new_plan_total_price - $remaining_amount);

                        Log::info('$iterations=' . $iterations . '$old_plan->price='. $old_plan->price);
                        Log::info('$days_remaining=' . $days_remaining . '$days_total='. $days_total);
                        Log::info('$lower_to_upper=' . $lower_to_upper . '$plan->price='. $plan->price);
                        Log::info('$applicable_amount=' . $applicable_amount);
                    } else {
                        $applicable_amount = $plan->price;
                    }

                } else {
                    $applicable_amount = $plan->price;
                }

                $response = [
                    'days_remaining'                => $days_remaining,
                    'days_total'                    => $days_total,
                    'applicable_amount'             => $applicable_amount,
                ];

            } else {
                $applicable_amount = $plan->price;
                $response = [
                    'days_remaining'                => $days_remaining,
                    'days_total'                    => $days_total,
                    'applicable_amount'             => $applicable_amount,
                ];

            }

            return $this->simpleReturn('success', $response);
        } catch (\Exception $exception){
            Log::error('Exception occurred while upgrade calculation');
            Log::error($exception);
            return $this->simpleReturn('error', 'Something went wrong, please contact support', 500);
        }
    }

    /**
     * @param Request $request
     * @param $user_subscriptions
     */
    public function updateOauthCleintValidity(Request $request, $user_subscriptions): void
    {
        $client = OauthClients::findOrFail($request->client_id);
        $client->validity = $user_subscriptions->ends_at;
        $client->save();
    }

    /**
     * @param Request $request
     * @param $invoice_id
     * @return JsonResponse
     */
    public function syncSubadminsMenu($client_id, $plan_id)
    {
        $subadmins = SubAdmin::where('oauth_client_id', $client_id)->whereDate('expires_at', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))->get();
        if($subadmins){          
            foreach($subadmins as $subadmin){
                $subadmins_menu = SubAdminMenu::where('active',true)
                        ->where('sub_admin_id',$subadmin->id)
                        ->with('menu')
                        ->get();  
                        Log::info('$subadmin->id=' . $subadmin->id. '$subadmins_menu=' . $subadmins_menu); 
                        
                SubAdminMenu::where('sub_admin_id', $subadmin->id)->update([
                    'active' => false
                ]);

                foreach($subadmins_menu as $value){                 
                    $menu = MenuSettings::select('id')->where('title', $value->menu->title)->where('plan_id', $plan_id)->where('user_type','teacher')->where('status','active')->first();
                    Log::info('$value=' . $value . '$menu='. $menu);   
                    SubAdminMenu::create([
                        'sub_admin_id' => $subadmin->id,
                        'menu_setting_id' => $menu->id,
                    ]);
                }   
            }
            return true; 
        }          
            
        return $this->simpleReturn('error', 'No clients found', 404);
    }
}
