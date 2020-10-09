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
use App\Model\OauthClients;
use App\Model\SubAdmin;
use Carbon\Carbon;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Mail;
use Socialite;
use Storage;
use Validator;
use DB;

class LoginController extends Controller
{
    use ResetsPasswords;

    /**
     * @param Request $request
     * @return JsonResponse|mixed
     */
    public function signUp(Request $request)
    {
        $rules = array(
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => 'required|string|min:6',
            're_password' => 'required|same:password',
            'client_id' => 'required',
            'client_secret' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        } else {

            $oauth_clients = DB::table('oauth_clients')
                ->where('id', $request->client_id)
                ->where('secret', $request->client_secret)
                ->first();

            if(!$oauth_clients){
                return $this->simpleReturn('error', 'Your credentials are incorrect. Please try again. lll', 401);
            }

            $add = new User();
            $add->name = $request->name;
            $add->email = $request->email;
            $add->phone = $request->phone;
            $add->password = Hash::make($request->password);
            $add->email_verify = 1;
            $add->is_active = 1;
            $add->phone_otp = mt_rand(100000,999999);

            if ($add->save()) {
                $add->assignRole('student');

                Mail::to($request->email)->queue(new EmailVerification($add));
                // SMS Verification

                $message="Hi ".$add->name.", Phone verification code for ".urlencode(config('app.web_name'))." is ".$add->phone_otp;

                SMSController::sendSMS($message, $add->phone).

                // Welcome mail
                Mail::to($request->email)->queue(new WelcomeSignup($add));
                // Admin mail & Notification
//                $admins = User::role(['super-admin', 'admin'])->active()->get();
//                foreach ($admins as $key => $admin) {
//                    Mail::to($admin->email)->queue(new NewUser($add));
//
//                    $this->notification($admin->id, $add->id, 'user_registration', 'Registration', 'New User :name has joined.');
//                }

                return $this->signIn($request);
            } else {
                return $this->simpleReturn('error', 'Error in insertion', 500);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function signUpVerify(Request $request)
    {
        $rules = array(
            'user_id' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        } else {

            $phone_updated = User::where('id', Auth::id())->update(['phone_verify' => 1]);
            Log::info('Phone verify is =' . $phone_updated ? true : false);
            if($phone_updated){
                $user_profile = User::where('id', Auth::id())->get()->first();
                $user_profile->profile_complete = $user_profile->profile_complete + 10;
                $user_profile->save();
                return $this->simpleReturn('success', 'Phone verified successfully');
            } else {
                return $this->simpleReturn('error', 'Error in insertion', 500);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|mixed
     */
    public function signIn(Request $request)
    {
        $http = new \GuzzleHttp\Client;
        try {
            $username = $request->username ? $request->username : $request->email;
            $user = User::where('email', $username)->orWhere('phone', $username)->first();

            if($user && Hash::check($request->password, $user->password)) {
                Log::info('Removing all access tokens for the userId=' . $user->id);
                $user->revokeAccessTokens();
            }

            $response = $http->post(env('URL_PATHSHALA_USER') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                    'username' => $request->username ? $request->username : $request->email,
                    'password' => $request->password,
                ],
            ]);
            return json_decode($response->getbody(), true);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            if ($e->getCode() === 400) {
                return $this->simpleReturn('error', 'Invalid request. Please enter username or password', 400);
            } else if ($e->getCode() === 401) {
                return $this->simpleReturn('error', 'Your credentials are incorrect. Please try again.', 401);
            } else {
                return $this->simpleReturn('error', 'Something went wrong on the server', $e->getCode());
            }
        } catch (\Exception $e){
            return $this->simpleReturn('error', 'Something went wrong on the server', $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request)
    {
        $user = $request->user();
        $rules = array(
            'phone_otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $phone_updated= false;
        $phone_otp = $request->phone_otp ? $request->phone_otp : null;

        if($user->phone_otp == $phone_otp){

            if($user->phone_otp == $phone_otp){
                $phone_updated = User::where('id', $user->id)->update(['phone_verify' => 1]);
            }

            if($phone_updated){
                $user_profile = User::where('id', $user->id)->get()->first();
                $user_profile->profile_complete = $user_profile->profile_complete + 10;
                $user_profile->save();
                return $this->simpleReturn('success', 'Email and Phone verified successfully');
            }

            return $this->simpleReturn('error', 'Error in updation', 500);

        }
        return $this->simpleReturn('error', 'Invalid OTP', 400);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function authUser(Request $request)
    {
        $uc = $request->user();
        $user = $this->authUserDetails($uc);

        return $this->simpleReturn('success', $user);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function signOut(Request $request)
    {
        $request->user()->token()->delete();
        return $this->simpleReturn('success', 'Logged out successfully');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function ssoValidate(Request $request)
    {

        $res = $request->user();
        $res['roles'] = $request->user()->getRoleNames();
        $clientId = $request->header('ClientId');

        Log::debug('Requesting clientId=' . $clientId);

        $res->app_client_user_id = json_encode(0);
        if($clientId){
            $client = OauthClients::where('id', $clientId)
                ->where('revoked','0')
                ->where('issue_status','2')
//                ->where('validity','>=', Carbon::now())
                ->first();
            if($client){
                $res->app_client_user_id = $client->user_id;
            }
        }
        return json_decode($res, true);
    }

    /**
     * @param $provider
     * @param $provider_id
     * @param $user_id
     * @param $provider_img
     * @return bool
     */
    public function addSocialAccount($provider, $provider_id, $user_id, $provider_img)
    {
        $add = new SocialAccount();
        $add->provider = $provider;
        $add->provider_user_id = $provider_id;
        $add->provider_user_avatar = $provider_img;
        $add->user_id = $user_id;
        $add->save();

        return true;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loginFacebook(Request $request)
    {
        try {
            $facebook = Socialite::driver('facebook')->userFromToken($request->accessToken);
            $socialExist = SocialAccount::where('provider', 'facebook')->where('provider_user_id', $facebook->getId())->first();

            $token['avatar'] = false;
            $token['user_img'] = $facebook->getAvatar();
            $token['user_name'] = $facebook->getName();

            if (!$socialExist) {
                $userExist = User::where('email', $facebook->getEmail())->with('user_info')->first();
                if ($userExist) {
                    $userExist->revokeAccessTokens();
                    $token['access_token'] = $userExist->createToken('')->accessToken;
                    $token['user_type'] = $userExist->getRoleNames();
                    $token['user_name'] = $userExist->name;
                    if($userExist->user_info){
                        if ($userExist->user_info->profile_pic) {
                            $token['user_img'] = $userExist->user_info->profile_pic;
                            $token['avatar'] = true;
                        }
                    }
                    $this->addSocialAccount('facebook', $facebook->getId(), $userExist->id, $facebook->getAvatar());

                    return $this->simpleReturn('success', $token);
                } else {
                    $add = new User();
                    $add->name = $facebook->getName();
                    $add->email = $facebook->getEmail();
                    $add->is_active = 1;
                    $add->email_verify = 1;
                    $add->save();
                    $lid = $add->id;
                    $add->assignRole('student');

                    // Welcome mail
                    Mail::to($facebook->getEmail())->queue(new WelcomeSignup($add));
                    // Admin mail
                    $admins = User::role(['super-admin', 'admin'])->active()->get();
                    foreach ($admins as $key => $admin) {
                        Mail::to($admin->email)->queue(new NewUser($add));
                    }
                    $add->revokeAccessTokens();
                    $token['access_token'] = $add->createToken('')->accessToken;
                    $this->addSocialAccount('facebook', $facebook->getId(), $lid, $facebook->getAvatar());
                    $token['user_type'] = $add->getRoleNames();

                    return $this->simpleReturn('success', $token);
                }
            }

            $existUser = User::where('id', $socialExist->user_id)->with('user')->first();
            $existUser->revokeAccessTokens();
            $token['access_token'] = $existUser->createToken('')->accessToken;
            $token['user_type'] = $existUser->getRoleNames();
            $token['user_name'] = $existUser->name;

            if ($existUser->user_info) {
                if ($existUser->user_info->profile_pic) {
                    $token['user_img'] = $existUser->user_info->profile_pic;
                    $token['avatar'] = true;
                }
            }

            return $this->simpleReturn('success', $token);
        } catch (\Exception $e) {
            return $this->simpleReturn('error', $e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loginGoogle(Request $request)
    {
        try {
            $google = Socialite::driver('google')->userFromToken($request->accessToken);
            // Log::debug('provider_id=' . $google->getId());
            $socialExist = SocialAccount::where('provider', 'google')->where('provider_user_id', $google->getId())->first();

            $token['avatar'] = false;
            $token['user_img'] = $google->getAvatar();
            $token['user_name'] = $google->getName();

            if (!$socialExist) {
                $userExist = User::where('email', $google->getEmail())->with('user_info')->first();
                if ($userExist) {
                    $userExist->revokeAccessTokens();
                    $token['access_token'] = $userExist->createToken('')->accessToken;
                    $token['user_type'] = $userExist->getRoleNames();
                    $token['user_name'] = $userExist->name;

                    if($userExist->user_info){
                        if ($userExist->user_info->profile_pic) {
                            $token['user_img'] = $userExist->user_info->profile_pic;
                            $token['avatar'] = true;
                        }
                    }
                    $this->addSocialAccount('google', $google->getId(), $userExist->id, $google->getAvatar());

                    return $this->simpleReturn('success', $token);
                } else {
                    $add = new User();
                    $add->name = $google->getName();
                    $add->email = $google->getEmail();
                    $add->is_active = 1;
                    $add->email_verify = 1;
                    $add->save();
                    $lid = $add->id;
                    $add->assignRole('student');

                    // Welcome mail
                    Mail::to($google->getEmail())->queue(new WelcomeSignup($add));
                    // Admin mail
                    $admins = User::role(['super-admin', 'admin'])->active()->get();
                    foreach ($admins as $key => $admin) {
                        Mail::to($admin->email)->queue(new NewUser($add));
                    }
                    $add->revokeAccessTokens();
                    $token['access_token'] = $add->createToken('')->accessToken;
                    $this->addSocialAccount('google', $google->getId(), $lid, $google->getAvatar());
                    $token['user_type'] = $add->getRoleNames();

                    return $this->simpleReturn('success', $token);
                }
            }

            $existUser = User::where('id', $socialExist->user_id)->with('user_info')->first();
            if($existUser){
                $existUser->revokeAccessTokens();
                $token['access_token'] = $existUser->createToken('')->accessToken;
                $token['user_type'] = $existUser->getRoleNames();
                $token['user_name'] = $existUser->name;
            }


            if ($existUser->user_info) {
                if ($existUser->user_info->profile_pic) {
                    $token['user_img'] = $existUser->user_info->profile_pic;
                    $token['avatar'] = true;
                }
            }

            return $this->simpleReturn('success', $token);
        } catch (\Exception $e) {
            return $this->simpleReturn('error', $e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->get()->first();
        if ($user) {

            $params['url'] = env('WEB_URL') . 'sign-in/forgot-password/reset/';
            $params['token'] = app('auth.password.broker')->createToken($user);
            Mail::to($request->email)->queue(new ForgotPassword($params));

            return $this->simpleReturn('success', 'Sent reset password.');
        } else {
            return $this->simpleReturn('error', 'No user found with this email.', 404);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($response == Password::PASSWORD_RESET) {
            return $this->simpleReturn('success', 'Password changed successfully');
        } else {
            if ($response == 'passwords.user') {
                return $this->simpleReturn('error', 'Invalid User.', 400);
            } elseif ($response == 'passwords.token') {
                return $this->simpleReturn('error', 'Invalid Token.', 400);
            } elseif ($response == 'passwords.password') {
                return $this->simpleReturn('error', 'Invalid Password.', 400);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function contact(Request $request)
    {
        $rules = array(
            'phone' => 'required|numeric',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = $request->user();
        $extUser = User::where('phone', $request->phone)->where('is_active', 1)->where('id', '!=', $user->id)->first();

        if ($extUser) {
            $res['phone'][] = 'The Phone number has already been taken.';
            return $this->simpleReturn('error', $res, 400);
        }

        $otp = mt_rand(100000, 999999);
        if($request->otp_enabled == true){
            $update = User::where('id', $user->id)->update(['phone' => $request->phone, 'phone_otp' => $otp]);
        }else{
            $update = User::where('id', $user->id)->update(['phone' => $request->phone, 'phone_otp' => $otp, 'phone_verify' => 1]);
        }
        if ($update) {
            if($request->otp_enabled == true){
                $message = "Hi " . $user->name . ", Your OTP for verifying phone number is " . $otp;
                SMSController::sendSMS($message, $request->phone);
            }
            $user_profile = User::where('id', $user->id)->get()->first();
            $user_profile->profile_complete = $user_profile->profile_complete + 10;
            $user_profile->save();

            return $this->simpleReturn('success', 'Successfully');
        }
        return $this->simpleReturn('error', 'Error in updating phone number', 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function contactVerify(Request $request)
    {
        $rules = array(
            'otp' => 'required|numeric',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $user = $request->user();
        if ($user->phone_otp == $request->otp) {
            $update = User::where('id', $user->id)->update(['phone_verify' => 1]);

            $user_profile = User::where('id', $user->id)->get()->first();
            $user_profile->profile_complete = $user_profile->profile_complete + 10;
            $user_profile->save();

            return $this->simpleReturn('success', 'Successfully Verified.');
        }
        return $this->simpleReturn('error', 'Invalid OTP', 400);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function contactResend(Request $request)
    {
        $user = $request->user();
        $otp = mt_rand(100000, 999999);
        $update = User::where('id', $user->id)->update(['phone_otp' => $otp]);

        $message = "Hi " . $user->name . ", Your OTP for verifying phone number is " . $otp;
        SMSController::sendSMS($message, $user->phone);

        return $this->simpleReturn('success', 'OTP sent to the phone number');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function authViaOtp(Request $request)
    {
        $action = $request->get('action');
        $rules = array();
        if ('signin' == $action) {
            $rules = array(
                'phone' => 'required|numeric',
                'action' => 'required|string',
                'client_id' => 'required|numeric',
                'client_secret' => 'required|string',
            );
        } else {
            $rules = array(
                'phone' => 'required|numeric',
                'action' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'otp' => 'required',
                'client_id' => 'required|numeric',
                'client_secret' => 'required|string',
            );
        }

        $oauth_clients = DB::table('oauth_clients')
            ->where('id', $request->client_id)
            ->where('secret', $request->client_secret)
            ->where('revoked', 0)
            ->first();

        if(!$oauth_clients){
            return $this->simpleReturn('error', 'Not a valid app', 401);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        if('signin' == $action){
            $user = User::where('phone', $request->get('phone'))->first();
            if($user && $user->is_active == 1){
                $response  =  $this->authUserDetails($user);
                $user->revokeAccessTokens();
                $response['access_token'] = $user->createToken('')->accessToken;
                return $this->simpleReturn('success', $response);
            } else if ($user && $user->is_active == 0){
                return $this->simpleReturn('error', 'Your access is barred. Please contact support',  403);
            } else {
                return $this->simpleReturn('error', 'User not found. Sign up needed',  404);
            }
        } else if ('signup' == $action) {
            $user = User::where('email', $request->get('email'))->first();
            if($user){
                return $this->simpleReturn('error', 'This email is associated with another user. Please try another email', 400);
            }

            $user = User::where('phone', $request->get('phone'))->first();
            if($user){
                return $this->simpleReturn('error', 'This phone number is associated with another user. Please try another phone number', 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->phone_otp = $request->otp;
            $user->phone_verify = 1;
            $user->email_verify = 1;
            $user->is_active = 1;
            $user->profile_complete = 35;
            if ($user->save()) {
                $user->assignRole('student');
            }

            $response  =  $this->authUserDetails($user);
            $user->revokeAccessTokens();
            $response['access_token'] = $user->createToken('')->accessToken;
            return $this->simpleReturn('success', $response);
        } else {
            return $this->simpleReturn('error', 'Not a valid action', 400);
        }
    }

    /**
     * @param $uc
     * @return mixed
     */
    public function authUserDetails($uc)
    {
        $user['id'] = $uc->id;
        $user['name'] = $uc->name;
        $user['email'] = $uc->email;
        $user['phone'] = $uc->phone;
        $user['phone_verify'] = $uc->phone_verify;
        $user['social_avatar'] = false;
        $user['joining_date'] = $uc->created_at->format('Y-m-d H:i:s');
        $user_info = UserInfo::where('user_id', $uc->id)->with('social_account')->get()->first();
        if ($user_info) {
            $user['about'] = $user_info->about;
            if ($user_info->profile_pic) {
                $user['avatar'] = Storage::disk('do_spaces')->exists(env('DO_SPACES_DRIVE') . '/images/profiles/' . $user_info->profile_pic) ? $user_info->profile_pic : null;
            } else {
                if ($user_info->social_account) {
                    if ($user_info->social_account->provider_user_avatar) {
                        $user['avatar'] = $user_info->social_account->provider_user_avatar;
                        $user['social_avatar'] = true;
                    } else {
                        $user['avatar'] = null;
                    }
                } else {
                    $user['avatar'] = null;
                }
            }
        } else {
            $user['avatar'] = null;
            $user['about'] = null;
        }

        $user['roles'] = $uc->getRoleNames();
        $user['is_sub_admin'] = false;
        if($uc->hasRole('sub-admin')){
            $subadmins = SubAdmin::where('user_id', $uc->id)->where('active',true)
            ->with(['oauth' => function($query) {
                $query->select('id','user_id', 'name', 'issue_status', 'revoked', 'validity');
            }])
            ->get();
            if($subadmins){
                $user['is_sub_admin'] = true;
                $user['sub_admin_details'] = $subadmins;    
            }
        }

        $user_review = Pc_Review::where('author_id', $uc->id)->get();
        if ($user_review) {
            $user['rating'] = $user_review->avg('rating');
        } else {
            $user['rating'] = 0;
        }
        return $user;
    }
}
