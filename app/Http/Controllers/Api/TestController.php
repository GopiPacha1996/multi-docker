<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Mail\WelcomeSignup;
// use App\Mail\ForgotPassword;
// use App\Mail\NewUser;
// use App\Mail\TeacherVerification;
use App\Mail\RegistrationApproval;
use App\User;
use Mail;

class TestController extends Controller
{
    public function test()
    {
        // $when = now()->addMinutes(1);
        // Mail::to('sanju@appeonix.com')->later($when, new WelcomeSignup());

        $user = User::where('id', 2)->first();

        // Mail::to($user->email)->queue(new WelcomeSignup($user));
        // Mail::to($user->email)->queue(new InstructorRegistration($user));
        // Mail::to($user->email)->queue(new NewUser($user));
        // Mail::to($user->email)->queue(new TeacherVerification($user));
        Mail::to($user->email)->queue(new RegistrationApproval($user));

        return $this->simpleReturn('success', 'hii');
    }
}
