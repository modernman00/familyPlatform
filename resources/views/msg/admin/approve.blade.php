@extends('email')

@section('title', 'Membership Approved')
@section('subject', 'Membership Application Approved')

@if (!empty($data['id']))
@section('reference') {{ $data['id'] }} @endsection
@endif

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] }} {{ $data['lastName'] }}</strong>,
</p>

<div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 20px; border-radius: 4px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #166534; font-size: 16px;">
        Congratulations! Your application to join the <strong>{{ $data['lastName'] }}</strong> family platform has been officially approved.
    </p>
</div>

<p style="margin-bottom: 25px;">
    You can now log in to the network to complete your profile, upload your photos, and connect with your family.
</p>

<div style="text-align: center;">
    <a href="{{ getenv('APP_URL') }}login" style="background-color: #0a66c2; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Log in to Your Account</a>
</div>
@endsection