@extends('layouts.email_premium')

@section('title', 'New Friend Request')
@section('subtitle', 'Someone wants to connect with you')
@section('greeting', "Hi {$data['approverFirstName']},")

@section('content')
<style>
    .profile-img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        float: left;
        margin-right: 20px;
    }
    .requester-info {
        overflow: hidden;
    }
    .requester-name {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 4px;
        color: #1c1e21;
    }
    .mutual-friends {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 12px;
    }
    .bio {
        font-size: 14px;
        line-height: 1.5;
        color: #4b4b4b;
        margin: 0;
    }
    @media screen and (max-width: 600px) {
        .profile-img {
            float: none;
            margin: 0 auto 15px;
            display: block;
        }
        .requester-info {
            text-align: center;
        }
    }
</style>

<img src="{{ getenv('MIX_ROUTE_PICS') }}{{ $data['profileImg'] }}" alt="{{ $data['firstName'] }}" class="profile-img">
<div class="requester-info">
    <h2 class="requester-name">{{ $data['firstName'] }} {{ $data['lastName'] }}</h2>
    <p class="mutual-friends">Wants to join your family network</p>
    <p class="bio">
        Hello! I would like to connect with you on the family platform and share updates with each other.
    </p>
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/50/{{ $data['famCode'] }}/email" class="btn btn-primary">Accept Request</a>
<a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/10/request/email" class="btn btn-outline">Decline</a>
@endsection

@section('extra_links')
<p style="text-align: center; margin-top: 30px; font-size: 14px; color: #6c757d;">
    Or <a href="{{ getenv('MIX_APP_URL2') }}member/seeProfile/{{ $data['id'] }}" style="color: #1c1e21; text-decoration: underline; font-weight: 500;">view their profile</a> to learn more
</p>
@endsection