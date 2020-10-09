@extends('mails.template')

@section('subject')
    {{ $_subject }}
@endsection

@section('content')
    <p>Hello!</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <br>
    <p><a href="{{$url.$token}}">Reset Password </a></p>
    <br>
    <p>This password reset link will expire in 60 minutes.</p>
    <br>
    <p>If you did not request a password reset, no further action is required.</p>
@endsection
