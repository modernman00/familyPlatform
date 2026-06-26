@extends('layouts.email_premium')

@section('title', 'Login Verification')
@section('subtitle', 'Your secure authentication token')
@section('greeting', 'Hello,')

@section('content')
<div style="text-align: center; padding: 20px 0;">
    @if ($data)
        <p style="font-size: 16px; color: #4b4b4b; margin-bottom: 25px;">Please use the following verification code to complete your login. This code is valid for 10 minutes.</p>
        <div style="background-color: #ffffff; border: 2px dashed #dee2e6; border-radius: 12px; padding: 20px; display: inline-block;">
            <span style="font-size: 32px; font-weight: 800; letter-spacing: 5px; color: #1c1e21;">{{ $data['code'] }}</span>
        </div>
    @else 
        <p style="color: #d9534f;">There was a problem generating your authentication token. Please try again.</p>
    @endif
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}login" class="btn btn-primary">Go to Login Page</a>
@endsection
