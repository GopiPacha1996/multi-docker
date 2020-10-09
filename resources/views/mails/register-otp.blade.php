@extends('mails.template')

@section('subject')
    {{ $_subject }}
@endsection

@section('content')
    <p>Hi {{ $name }},</p>
    <p>Your OTP for registration is <strong>{{ $otp }}</strong>.</p>
@endsection
