<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\RazorPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api as RApi;
use Razorpay\Api\Errors\BadRequestError;

class RazorPlanController extends Controller
{
    /**
     * @var RApi
     */
    private $api;
    /**
     * RazorPlanController constructor.
     * @param RApi $api
     */
    public function __construct(RApi $api)
    {
        $this->api = $api;
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $rplans = RazorPlan::orderBy('amount','ASC');
        if($request->active){
            $rplans->where('active','1');
        }
        $rplans =$rplans->get();
        if ($rplans->count()) {
            return $this->simpleReturn('success', $rplans);
        }
        return $this->simpleReturn('error', 'No data found...', 404);
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
            'type' => 'required',
            'trail' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        try{
            $plan_id = Input::get('plan_id');
            $plan = $this->api->plan->fetch(Input::get('plan_id'));
            if ($plan){
                $exists = RazorPlan::where('plan_id', $plan_id)->count();
                if($exists){
                    return $this->simpleReturn('error', 'Plan id already exists', 400);
                }
                $rplan = RazorPlan::create([
                    'plan_id' => $plan_id,
                    'plan_desc' => $plan->item->description,
                    'amount' => $plan->item->amount / 100,
                    'interval' => $plan->interval,
                    'period' => $plan->period,
                    'type' => Input::get('type'),
                    'type_id' => Input::get('type_id'),
                    'active' => Input::get('active', true),
                    'trail' => Input::get('trail', 0),
                ]);
                return $this->simpleReturn('success', "Plan is created successfully", 201);
            } else {
                return $this->simpleReturn('error', 'Could not find plan id', 400);
            }
        } catch (BadRequestError $e){
            Log::error($e);
            return $this->simpleReturn('error', $e->getMessage(), 400);
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }
        return  $this->simpleReturn('success', 'Plan is created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param RazorPlan $razorPlan
     * @return void
     */
    public function show(RazorPlan $razorPlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param RazorPlan $razorPlan
     * @return void
     */
    public function edit(RazorPlan $razorPlan)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        try{
            Log::info(Input::get('type'));
            $plan = RazorPlan::findOrFail($id);
            if($plan){
                if(Input::get('plan_desc')){
                    $plan->plan_desc = Input::get('plan_desc');
                }
                if(Input::get('type')){
                    $plan->type = Input::get('type');
                }
                if(Input::get('type_id')){
                    $plan->type_id = Input::get('type_id');
                }
                if(Input::get('active')){
                    $plan->active = Input::get('active');
                }
                if(Input::get('trail')){
                    $plan->trail = Input::get('trail');
                }
                if ($plan->save()){
                    return  $this->simpleReturn('success', 'Plan is updated successfully', 201);
                }
            }
        } catch (BadRequestError $e){
            Log::error($e);
            return $this->simpleReturn('error', $e->getMessage(), 400);
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }
        return  $this->simpleReturn('success', 'Plan is updated successfully', 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param RazorPlan $razorPlan
     * @return void
     */
    public function destroy(RazorPlan $razorPlan)
    {
        //
    }
}
