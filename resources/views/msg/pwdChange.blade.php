@extends('layouts.email_premium')

@section('title', 'Password Changed')
@section('subtitle', 'Security alert for your account')
@section('greeting', "Hi Member,")

@section('content')
<div style="text-align: center; padding: 10px 0;">
    <p style="font-size: 16px; line-height: 1.6; color: #1c1e21; margin: 0;">
        The password for your Family Platform account was recently changed.
    </p>
    <p style="margin-top: 20px; font-size: 14px; color: #6c757d; line-height: 1.5;">
        If you made this change, you can safely ignore this email. If you did not change your password, please contact our support team immediately to secure your account.
    </p>
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}login" class="btn btn-primary">Login to Account</a>
@endsection