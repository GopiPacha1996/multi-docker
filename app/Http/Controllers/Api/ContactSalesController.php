<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ContactSales;
use Validator;
class ContactSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contact_sales = ContactSales::with('user')->where('status', 'active')->get();
        if ($contact_sales) {
            return $this->simpleReturn('success', $contact_sales);
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
        $rules = array(
            'name' => 'required',
            // 'email' => 'required',
            'phone' => 'required',
            // 'post_query' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $add = new ContactSales();
        $add->name = $request->name;
        $add->phone = $request->phone;
        $add->query = $request->post_query ? $request->post_query :' ';
        $add->email = $request->email;
//        $add->subject = $request->subject;
        $add->status = 'active';
        if ($add->save()) {

            return $this->simpleReturn('success', 'Thank you for submitting your query. We will get back to you within 48 hours');
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
        $rules = array(
            'status' => 'required',
            'reply_query' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $service = ContactSales::where('id', $id)->first();
        if ($service) {
            $service->status = $request->status;
            $service->reply_query = $request->reply_query;
            $service->save();
            if($service){
                return $this->simpleReturn('success', 'Updated Successfully');
            }
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
        $test_service = ContactSales::where('id', $id)->first();
		if($test_service){
            $test_service_delete = ContactSales::where('id', $id)->update([
                'status' => 'deleted',
            ]);
            if($test_service_delete){
                return $this->simpleReturn('success', 'Successfully deleted');
            }
            return $this->simpleReturn('error', 'deletion error', 400);
		}
		return $this->simpleReturn('error', 'No data found', 404);
    }
}
