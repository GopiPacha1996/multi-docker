<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('New Registration')
            ->markdown('emails.registration.newuser')
            ->with(['userName' => $this->user->name, 'userEmail' => $this->user->email, 'userPhone' => $this->user->phone]);
    }
}
