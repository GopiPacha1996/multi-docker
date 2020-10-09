<?php


namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Model\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $client_id = $request->header('ClientId');
        $edu_store = $request->header('EduStore');
        $uuid = $request->query('deviceUUID');

        $isInstitute = true;
        if ($client_id && $edu_store && $edu_store === 'true'){
            $isInstitute = false;
        }

        $device = Device::where('user_id', Auth::id())
            ->where('is_institute', $isInstitute)
            ->where('institute_id', $client_id)
            ->where('deviceUUID', $uuid)
            ->where('active', 1)
            ->first();

        if($device){
            return $this->simpleReturn('success', $device);
        }

        return $this->simpleReturn('error', 'Could not find any token.', 404);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        $rules = [
            'deviceToken' => 'required',
            'deviceUUID' => 'required',
            'platform' => 'required',
        ];

        $input = $request->all();

        $validator = validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $client_id = $request->header('ClientId');
        $edu_store = $request->header('EduStore');
        $uuid = $request->get('deviceUUID');
        $token = $request->get('deviceToken');

        $isInstitute = true;
        if ($client_id && $edu_store && $edu_store === 'true'){
            $isInstitute = false;
        }

        $devices = Device::where('user_id', Auth::id())
            ->where('is_institute', $isInstitute)
            ->where('institute_id', $client_id)
            ->where('deviceUUID', $uuid)
            ->where('active', 1)
            ->first();


        if($devices && $devices->count() > 0) {
            $device = Device::where('user_id', Auth::id())
                ->where('is_institute', $isInstitute)
                ->where('institute_id', $client_id)
                ->where('deviceUUID', $uuid)
                ->where('active', 1)
                ->first();

            if($device){
                Log::info("Device Id already registered for app_client_id=". $client_id
                    . '. Hence updating the token=' . $token . ' for device='. $uuid);
                $device->deviceToken = $token;
                $device->save();
                return $this->simpleReturn('success', 'Device updated Successfully');
            }

            return $this->simpleReturn('success', 'No need of any modification');
        } else {
            Log::info("Device not registered for app_client_id=". $client_id
                . '. Hence creating the token=' . $token . ' for device='. $uuid);
            $device = Device::create($input);
            $device->user_id = Auth::id();
            $device->is_institute = $isInstitute;
            $device->institute_id = $client_id;
            $device->platform = $input['platform'];
            $device->save();
            return $this->simpleReturn('success', 'Device Created successfully');
        }
    }
}
