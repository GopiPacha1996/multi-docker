<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Rinvex\Subscriptions\Models\PlanFeature;

class RinvexPlanController extends Controller
{
    /**
     * RinvexPlanController constructor.
     */
    public function __construct()
    {
//        $this->middleware(['role:admin']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 10;

        $rules = array(
            'type' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        $sortDirection = "ASC";
        $plans = app('rinvex.subscriptions.plan')->with(['features' => function ($query) use ($sortDirection) {
            $query->orderBy('sort_order', $sortDirection);
        }]);

        if($request->id) {
            $plans = $plans->where('id', $request->id);
        }
        $plans = $plans->where('type',$request->type)->orderBy('sort_order')->orderBy('name')->paginate($perPageCount);

        return $this->simpleReturn('success', $plans);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        $rules = array(
            'type' => 'required',
            'name' => 'required',
            'price' => 'required',
            'invoice_period' => 'required',
            'trial_period' => 'required',
            'sort_order' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        $plan = app('rinvex.subscriptions.plan')->create([
            'slug' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'signup_fee' => 0,
            'invoice_period' => $request->invoice_period,
            'invoice_interval' => 'month',
            'trial_period' => $request->trial_period,
            'trial_interval' => 'day',
            'sort_order' => $request->sort_order,
            'currency' => 'INR',
        ]);


        if($plan) {

            $this->addFeatures($request, $plan);
        }


        return $this->simpleReturn('success', 'You plan has been created successfully');
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {

        $rules = array(
            'type' => 'required',
            'name' => 'required',
            'price' => 'required',
            'invoice_period' => 'required',
            'trial_period' => 'required',
            'sort_order' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }
        $plan = app('rinvex.subscriptions.plan')->find($id);

        if(!$plan){
            return $this->simpleReturn('error', 'Could not find plan', 404);
        }
        $plan ->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'signup_fee' => 0,
            'invoice_period' => $request->invoice_period,
            'invoice_interval' => 'month',
            'trial_period' => $request->trial_period,
            'trial_interval' => 'day',
            'sort_order' => $request->sort_order,
            'currency' => 'INR',
        ]);




        if($plan) {
            $plan->features()->delete();
            $this->addFeatures($request, $plan);
        }

        $plan['features'] = $plan->features()->orderBy('sort_order')->get();

        return $this->simpleReturn('success', 'You plan has been updated successfully');
    }

    /**
     * @param Request $request
     * @param $plan
     */
    public function addFeatures(Request $request, $plan): void
    {
        if ($request->features) {
            $features = array();
            foreach (json_decode($request->features) as $feature) {
                $features [] = new PlanFeature(['name' => $feature->name, 'value' => $feature->value, 'sort_order' => $feature->sort_order]);
            }

            if (count($features) > 0) {
                $plan->features()->saveMany($features);
            }
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $plan = app('rinvex.subscriptions.plan')->find($id);
        if(!$plan){
            return $this->simpleReturn('error', 'Could not find plan', 404);
        }
        $plan->delete();
        return $this->simpleReturn('success', 'Plan has been deleted successfully');
    }
}
