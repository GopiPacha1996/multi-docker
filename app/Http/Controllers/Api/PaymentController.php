<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Model\Plan;
use App\Model\Payments;
use App\Model\Pc_BaseSettings;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $rules = array(
            'mode' => 'required',
            'plan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        if ($request->mode == 'hash') {
            $user = $request->user();   
            $plan = Plan::where('id', $request->plan)->where('status', 'active')->get()->first(); 
            if ($plan) {  

                $price = $plan->amount;
                $txnid = strtoupper(str_random(10));
                $taxData = Pc_BaseSettings::where('type', 'tax')->where('value', 'membership')->where('status', 'active')->first();
                $tax_per = $taxData ? ($taxData->others ? $taxData->others: 0) : 0;

                if ($price) {
                    $tax = ($price * $tax_per) / 100;
                    $amount = $price + $tax;

                    $add = new Payments();
                    $add->user_id = $user->id;
                    $add->plan_id = $plan->id;
                    $add->txnid = $txnid;
                    $add->price = $amount;
                    $add->tax = $tax;
                    $add->total = $amount + $tax;
                    $add->status = 'pending';
                    $add->save();

                    $key = env('PAYU_KEY');

                    $productinfo = 'Course Cart';
                    $firstname = $user->name;
                    $email = $user->email;
                    $phone = $user->phone;
                    $udf1 = '';
                    $salt = env('PAYU_SALT');

                    $hash_sequence = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '||||||||||' . $salt;
                    $hash = hash('sha512', $hash_sequence);

                    $payudata['key'] = $key;
                    $payudata['txnid'] = $txnid;
                    $payudata['amount'] = $amount;
                    $payudata['hash'] = $hash;
                    $payudata['productinfo'] = $productinfo;
                    $payudata['firstname'] = $firstname;
                    $payudata['email'] = $email;
                    $payudata['phone'] = $phone;
                    $payudata['udf1'] = $udf1;
                    $payudata['surl'] = env('URL_PATHSHALA_USER') . '/plan/payment';
                    $payudata['furl'] = env('URL_PATHSHALA_USER') . '/plan/payment';
                    $payudata['mode'] = 'dropout';

                    return $this->simpleReturn('success', $payudata);
                }
                return $this->simpleReturn('error', 'Invalid plan amount', 400);
            }
            return $this->simpleReturn('error', 'Invalid Plan', 400);
        }
        return $this->simpleReturn('error', 'Invalid mode.', 400);
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
        //
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
        //
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
