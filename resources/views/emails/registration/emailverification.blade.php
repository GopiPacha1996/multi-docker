@component('mail::message')
# Verify {{ config('app.web_name') }} Email
### Hi, {{ $userName }}

Your phone verification code (otp) for {{ config('app.web_name') }} is **{{ $OTP }}**.

Thanks,<br>
Support Team
@endcomponent
