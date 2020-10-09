<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserBankData;
use Illuminate\Http\Request;
use Storage;
use Validator;

class BankInfoController extends Controller
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
            'account_name' => 'required',
            'account_number' => 'required',
            'ifsc' => 'required',
            'branch_name' => 'required',
            'pancard' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user_id = $request->user()->id;
        $info = UserBankData::where('user_id', $user_id)->get()->first();
        if ($info) {

            $file = $info->bank_statement;
            if ($request->bank_statement) {

                $extension = $request->file('bank_statement')->extension();
                $file = mt_rand(100, 999) . time() . '.' . $extension;
                Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/docs/bank', $request->file('bank_statement'), $file, 'private');

                // $pic = $request->bank_statement;
                // $file = $this->createFileName($pic, $user_id);
                // $pic->storeAs('/', $file, 'bankData');
            }

            $updated = UserBankData::where('id', $info->id)->update([
                'ac_name' => $request->account_name,
                'ac_number' => $request->account_number,
                'ifsc_code' => $request->ifsc,
                'branch_name' => $request->branch_name,
                'pancard' => $request->pancard,
                'gst' => $request->has('gst') ? $request->gst : $info->gst,
                'company_registration' => $request->has('registration_num') ? $request->registration_num : $info->company_registration,
                'bank_statement' => $file,
            ]);

            if ($updated) {
                return $this->simpleReturn('success', 'Successfully updated');
            }
            return $this->simpleReturn('error', 'error in updation', 500);
        }

        $file = '';
        if ($request->bank_statement) {

            $extension = $request->file('bank_statement')->extension();
            $file = mt_rand(100, 999) . time() . '.' . $extension;
            Storage::disk('do_spaces')->putFileAs(env('DO_SPACES_DRIVE') . '/docs/bank', $request->file('bank_statement'), $file, 'private');

            // $pic = $request->bank_statement;
            // $file = $this->createFileName($pic, $user_id);
            // $pic->storeAs('/', $file, 'bankData');
        }

        $add = new UserBankData();
        $add->user_id = $user_id;
        $add->ac_name = $request->account_name;
        $add->ac_number = $request->account_number;
        $add->ifsc_code = $request->ifsc;
        $add->branch_name = $request->branch_name;
        $add->pancard = $request->pancard;
        $add->gst = $request->has('gst') ? $request->gst : null;
        $add->company_registration = $request->has('registration_num') ? $request->registration_num : null;
        $add->bank_statement = $file;
        $add->status = 'active';
        if ($add->save()) {
            return $this->simpleReturn('success', 'Successfully updated');
        }
        return $this->simpleReturn('error', 'Error in insertion', 500);
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
        // $rules = array(
        //     'account_name' => 'required',
        //     'account_number' => 'required',
        //     'ifsc' => 'required',
        //     'branch_name' => 'required',
        //     'pancard' => 'required',
        // );

        // $validator = validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return $this->simpleReturn('error', $validator->errors(), 400);
        // }
        // return $this->simpleReturn('success', 'it works', 400);
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
