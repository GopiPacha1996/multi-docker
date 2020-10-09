@component('mail::message')
# Reset Password Notification

Hello!<br>
You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $url.$token])
Reset Password
@endcomponent

This password reset link will expire in 60 minutes.<br>
If you did not request a password reset, no further action is required.

@endcomponent
