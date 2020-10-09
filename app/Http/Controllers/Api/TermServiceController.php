<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\TermService;
use Validator;
class TermServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $term_service = TermService::where('status','active')->get();
        if ($term_service) {
            return $this->simpleReturn('success', $term_service);
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
            'title' => 'required',
            'sort_order' => 'required|numeric',
            'details' => 'required',
            'type' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $service = TermService::create([
            'title' => $request->title,
            'details' => $request->details,
            'sub_details' => $request->sub_details,
            'sort_order' => $request->sort_order,
            'type' => $request->type,
            'status' => 'active',
        ]);

        if ($service) {

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
            'title' => 'required',
            'sort_order' => 'required|numeric',
            'details' => 'required',
            'type' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $service = TermService::where('id', $id)->first();
        if ($service) {
            $service->title = $request->title;
            $service->details = $request->details;
            $service->sub_details = $request->sub_details;
            $service->sort_order = $request->sort_order;
            $service->type = $request->type;
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
        $test_service = TermService::where('id', $id)->first();
		if($test_service){
            $test_service_delete = TermService::where('id', $id)->update([
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
