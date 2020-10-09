<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Mail\NewUser;
use App\Mail\WelcomeSignup;
use App\Mail\EmailVerification;
use App\Model\SocialAccount;
use App\Model\UserInfo;
use App\Model\Pc_Review;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mail;
use Socialite;
use Storage;
use Validator;
use DB;

class OauthController extends Controller
{
    use ResetsPasswords;

    public function generateOauth(Request $request)
    { 
        $http = new \GuzzleHttp\Client;
        try {
            // $http = new GuzzleHttp\Client;

            $response = $http->post(env('URL_PATHSHALA_USER') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => 'the-refresh-token',
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                    'scope' => '',
                ],
            ]);

            return json_decode((string) $response->getBody(), true);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            if ($e->getCode() === 400) {
                return $this->simpleReturn('error', 'Invalid request. Please enter username or password', 400);
            } else if ($e->getCode() === 401) {
                return $this->simpleReturn('error', 'Your credentials are incorrect. Please try again.', 401);
            } else {
                return $this->simpleReturn('error', 'Something went wrong on the server', $e->getCode());
            }
        }
    }
}
