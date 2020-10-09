<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Device;
use App\User;
use App\Model\OauthClients;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use DB;
use Illuminate\Support\Facades\Cache;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $deviceUUID  = $request->query('deviceUUID');
        if (!$deviceUUID || is_null($deviceUUID)) {
            return $this->simpleReturn('error', "Bad Request", 400);
        }

        try{
            if (Device::deviceExists($deviceUUID)){
                $device  = Device::where('deviceUUID', $deviceUUID)->get();
                return $this->simpleReturn('success', $device);
            } else {
                return $this->simpleReturn('error', 'Could not find deviceUUID ' . $deviceUUID , 404);
            }
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'deviceToken' => 'required',
            'deviceUUID' => 'required',
            'platform' => 'required',
        );


        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        try{
            $input = $request->all();

            // assign the userid if present
            if ( null != $request->user() && null != $request->user()->id) {
                $input['userID'] = $request->user()->id;
            }

            if ($input['deviceUUID'] == "0") {
                return $this->simpleReturn('error', "Device id can't be zero", 400);
            }

            $device = Device::findByUUID($input['deviceUUID']);
            Log::info( $input['platform']);

            if ($device) {
                if ($device->platform == $input['platform']
                    &&  ( array_key_exists('userID', $input) && $input['userID'] ) ) {
                    $device->user_id = $input['userID'];
                    $device->save();
                    return $this->simpleReturn('success', 'Device got updated successfully', 201);
                }
            } else {
                if ( array_key_exists('userID', $input) && $input['userID'] && isset($device->platform)) {
                    $devices = Device::findByPlatformAndUser($input['userID'], $device->platform);

                    if ($devices->count > 0) {
                        foreach ($devices as $device) {
                            $device->update (['active' => false]);
                        }
                    }
                }
            }

            $device = Device::create($input);
            $device->platform = $input['platform'];
            $device->save();

        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }

        return  $this->simpleReturn('success', 'Device got registered successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $deviceUUID
     * @return Response
     */
    public function update(Request $request, $deviceUUID)
    {
        try {
            if (Device::deviceExists($deviceUUID)){
                $data = json_decode($request->getContent(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new BadRequestHttpException('invalid json body: ' . json_last_error_msg());
                }

                $data = is_array($data) ? $data : array();


                // Fetch the latest record for device id
                $device  = Device::where('deviceUUID', $deviceUUID)
                    ->where('active', true)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if (isset($data['deviceToken']) && Device::tokenExists($data['deviceToken'])){
                    $exists = Device::where('deviceToken', $data['deviceToken'])
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    if ($exists->id != $device->id){
                        return $this->simpleReturn('error', 'Token already attached', 409);
                    }
                }

                if (isset($data['user_id'])){
                    $user_id = $data['user_id'];
                    $exists = User::where('id', $user_id)->count();
                    if(!$exists){
                        return $this->simpleReturn('error', 'Could not find user_id ' . $user_id , 404);
                    }
                }

                // update the latest record
                $device->update($data);

                // Update all other tokens to false
                Device::where('deviceUUID', $deviceUUID)
                    ->where('id', '!=', $device->id)
                    ->update(['active' => false]);

            } else {
                return $this->simpleReturn('error', 'Could not find deviceUUID ' . $deviceUUID , 404);
            }
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }

        return  $this->simpleReturn('success', 'Device updated successfully', 202);
    }

    public function partnersList(Request $request)
    {
        $limit = 250;
        $result = array();
        try{
            $partners  = Cache::remember('partner-list',86400, function() use($limit){
                return DB::table('devices as pu_devices')
                ->select('pu_devices.institute_id', DB::raw('count(*) as count'))
                ->where('pu_devices.is_institute','1')
                ->where('pu_devices.institute_id','>','4')
                ->groupBy('pu_devices.institute_id')
                ->orderBy('count','desc')
                ->limit($limit)
                ->get();
            });
            foreach($partners as $key => $value){
                $clients = OauthClients::where('id', $value->institute_id)->where('revoked','0')->first();
                if($clients){
                    $users = User::where('id', $clients->user_id)->with('user_info')->first();
                    if($users){
                        $result[] = array(
                            "institute_id"=> $value->institute_id,
                            "name"=> $users->name,
                            "profile_pic"=> isset($users->user_info->profile_pic)?$users->user_info->profile_pic:'',
                            "city"=> isset($users->user_info->city)?$users->user_info->city:''
                        );
                    }
                }
            }
            if ($partners) {
                return $this->simpleReturn('success', $result);
            }
            return $this->simpleReturn('error', 'No history found', 404);
        } catch (\Exception $e){
            Log::error($e);
            return $this->simpleReturn('error', 'Error - Please contact support!', 500);
        }
    }

}
