<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\Model\Plan;
use App\Model\PlanFeatures;
class PlanController extends Controller
{
    public function addPlan(Request $request)
    {
        $rules = array(
            'name'=>'required',
            'amount'=>'required|numeric',
            'description'=>'required',
            'salary_per_plan'=>'required|numeric',
            'duration'=>'required|numeric',
            'status'=>'required|string',
            'icon'=>'required',
            'feature'=>'required'
        );
        $validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
			return $this->simpleReturn('error', $validator->errors());
		}else{
            $plan=new Plan;
            $plan->name=$request->name;
            $plan->description=$request->description;
            $plan->amount=$request->amount;
            $plan->salary_per_plan=$request->salary_per_plan;
            $plan->duration=$request->duration;
            $plan->icon=$request->icon;
            $plan->status=$request->status;
            if($plan->save())
            {
                foreach($request->feature as $value){
                    $planfeature = new PlanFeatures;
                    $planfeature->plan_id = $plan->id;
                    $planfeature->feature = $value;
                    $planfeature->status = 'active';
                    $planfeature->save();
                }
                return $this->simpleReturn('success', 'Added');
            }else{
                return $this->simpleReturn('error', 'Plan Insertion');
            }
        }
    }

    public function getPlans(){
        // $data = Plan::join('plan_features', 'plan.id', '=', 'plan_features.plan_id')->distinct('plan.id')->get();
        $result = array();
        $data = Plan::get();
        if(count($data)>0){
            foreach($data as $value){
                $data = $value;
                $data->feature = $this->getPlanFeatures($value['id']);                
                $result[] = $data;
            }
            return $this->simpleReturn('success', $result);
        }else{
            return $this->simpleReturn('error', $validator->errors());    

        }
    }

   
}
