<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeacherVerification extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $url;

    public function __construct($user)
    {
        $this->user = $user;
        $this->url = env('WEB_URL') . 'admin/approvals/';
    }

    public function build()
    {
        return $this->subject('New Teacher Registration')
            ->markdown('emails.registration.verification')
            ->with(['url' => $this->url, 'userId' => $this->user->id, 'userName' => $this->user->name, 'userEmail' => $this->user->email, 'userPhone' => $this->user->phone]);
    }
}
