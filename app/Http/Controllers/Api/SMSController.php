<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Log;
use Mail;
use Socialite;
use Storage;
use Validator;

class SMSController extends Controller
{
    public function send(){
        $message = "yout otp for mypathshala is 123456";
        $phone= "7259516891";
        $this->sendSMS($message, $phone);
    }

    public static function sendSMS($message, $phone){
        $url="https://www.sms4india.com/api/v1/sendCampaign";
        $message = urlencode($message);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, "apikey=QRWLJDTNQOG3N1GCOWCNEMDKJ8EZJYGN&secret=B0K9LP7JJ6Z8WBSV&usetype=prod&phone=$phone&senderid=MYPATH&message=$message");

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if ($result['status'] == "success") {
            Log::info( $result['message'] . " phone=" . $phone);
        } else{
            Log::error("Error in sending message. " . $result['message'] . " phone=" . $phone);
        }
    }
}
