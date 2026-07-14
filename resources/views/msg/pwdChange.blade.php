@extends('email')

@section('title', 'Password Changed')
@section('subject', 'Security Alert: Password Changed')

@section('content')
<p style="margin-bottom: 20px;">
    Hello,
</p>

<p style="margin-bottom: 20px;">
    The password for your Family Platform account was recently changed. 
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #0a66c2; padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0; color: #334155; font-size: 15px;">
        <strong>Important:</strong> If you made this change, you can safely ignore this email. If you did not change your password, please contact our support team immediately to secure your account.
    </p>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="{{ getenv('MIX_APP_URL2') }}login" style="background-color: #0a66c2; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Login to Account</a>
</div>
@endsection