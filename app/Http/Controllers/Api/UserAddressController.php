<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if($request->id){
            $address = $user->addresses()->where('id', $request->id)->first()->delete();
            return $this->simpleReturn('success', $address);
        }
        return $this->simpleReturn('success', $user->addresses);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

         $user = Auth::user();
//        $user = User::find(5);


        $rules = array(
            'label' => 'required',
            'given_name' => 'required',
            'street' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        // Create a new address
        $address = $user->addresses()->create([
            'label' => $request->label,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'organization' => $request->organization,
            'country_code' => $request->country_code,
            'street' => $request->street,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'is_primary' => ($request->is_primary == 'true' ) ? 1 : 0,
            'is_billing' => ($request->is_billing == 'true')? 1 : 0,
            'is_shipping' => ($request->is_shipping == 'true') ? 1 : 0 ,
        ]);

        return $this->simpleReturn('success', "Address has been created successfully");
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
                $user = Auth::user();
//        $user = User::find(5);


        $rules = array(
            'label' => 'required',
            'given_name' => 'required',
            'street' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        $address = app('rinvex.addresses.address')->find($id);

        if(!$address){
            return $this->simpleReturn('error', 'Could not find address by id'. $id, 400);
        }

        // Update an existing address
        $address->update([
            'label' => $request->label,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'organization' => $request->organization,
            'country_code' => $request->country_code,
            'street' => $request->street,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'is_primary' => ($request->is_primary == 'true' ) ? 1 : 0,
            'is_billing' => ($request->is_billing == 'true')? 1 : 0,
            'is_shipping' => ($request->is_shipping == 'true') ? 1 : 0 ,
        ]);

        return $this->simpleReturn('success', "Address has been updated successfully");
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $address = app('rinvex.addresses.address')->find($id);

        if(!$address){
            return $this->simpleReturn('error', 'Could not find address by id'. $id, 400);
        }

        if($address->addressable_id == Auth::id()){
            // Delete address
            $address->delete();
        } else {
            return $this->simpleReturn('error', 'Not allowed to delete address', 403);
        }


        return $this->simpleReturn('success', 'Address has been deleted successfully');
    }
}
