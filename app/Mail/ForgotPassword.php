<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function build()
    {
        return $this->subject('Reset Password Notification')
            ->markdown('emails.registration.forgotpassword')
            ->with(['url' => $this->params['url'], 'token' => $this->params['token']]);
    }
}
