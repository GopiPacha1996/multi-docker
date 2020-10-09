<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\ActionLog;
use App\Model\Plan;
use App\Model\PlanFeatures;
use Illuminate\Http\Request;
use Validator;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Plan::with('options')->get();
        if ($plans) {
            return $this->simpleReturn('success', $plans);
        }
        return $this->simpleReturn('error', 'No data found', 404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;
        $user_id = $request->user()->id;

        $rules = array(
            'name' => 'required',
            'amount' => 'required|numeric',
            'description' => 'required',
            'salary_per_plan' => 'required|numeric',
            'duration' => 'required|numeric',
            'icon' => 'required',
            'feature' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $exists = Plan::where('name', $request->name)->count();
        if ($exists) {
            return $this->simpleReturn('error', 'The plan name has already been taken.', 409);
        }

        $plan = Plan::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'salary_per_plan' => $request->salary_per_plan,
            'duration' => $request->duration,
            'icon' => $request->icon,
            'status' => 'active',
            'add_by' => $user_id,
        ]);

        if ($plan) {
            foreach ($request->feature as $value) {
                $planfeature = PlanFeatures::create([
                    'plan_id' => $plan->id,
                    'feature_id' => $value,
                    'status' => 'active',
                ]);
            }

            return $this->simpleReturn('success', 'Inserted Successfully');
        }
        return $this->simpleReturn('error', 'Error in insertion', 400);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plans = Plan::where('id', $id)->with('options')->get()->first();
        if ($plans) {
            return $this->simpleReturn('success', $plans);
        }
        return $this->simpleReturn('error', 'No data found', 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_id = $request->user()->id;

        $rules = array(
            'name' => 'required',
            'amount' => 'required|numeric',
            'description' => 'required',
            'salary_per_plan' => 'required|numeric',
            'duration' => 'required|numeric',
            'icon' => 'required',
            'feature' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $exists = Plan::where('name', $request->name)->whereNotIn('id', [$id])->count();
        if ($exists) {
            return $this->simpleReturn('error', 'The plan name has already been taken.', 409);
        }

        $plans = Plan::where('id', $id)->update($request->only(['name', 'amount', 'description', 'salary_per_plan', 'duration', 'status', 'add_by', 'icon']));
        if ($plans) {

            foreach ($request->feature as $value) {
                $planfeature = PlanFeatures::where('plan_id', $id)->where('feature_id', $value)->get()->first();
                if ($planfeature === null) {
                    $planfeatureadd = PlanFeatures::create([
                        'plan_id' => $id,
                        'feature_id' => $value,
                        'status' => 'active',
                    ]);
                }
            }
            $planfeaturedelete = PlanFeatures::whereNotIn('feature_id', $request->feature)->delete();

            $action_log = ActionLog::create([
                'type' => 'base_settings',
                'action_id' => $id,
                'add_by' => $user_id,
            ]);

            return $this->simpleReturn('success', 'Updated Successfully');
        }
        return $this->simpleReturn('error', 'Error in updation', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
