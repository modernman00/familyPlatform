@extends('email')

@section('title', 'Login Verification')
@section('subject', 'Your Secure Authentication Token')

@section('content')
<div style="text-align: center; padding: 10px 0;">
    @if ($data)
        <p style="font-size: 16px; color: #475569; margin-bottom: 25px;">Please use the following verification code to complete your login. This code is valid for 10 minutes.</p>
        
        <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 25px; display: inline-block; margin-bottom: 30px;">
            <span style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #0f172a;">{{ $data['code'] }}</span>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ getenv('MIX_APP_URL2') }}login" style="background-color: #0a66c2; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Go to Login Page</a>
        </div>
    @else 
        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 4px;">
            <p style="color: #991b1b; margin: 0; font-weight: 500;">There was a problem generating your authentication token. Please try again.</p>
        </div>
    @endif
</div>
@endsection
