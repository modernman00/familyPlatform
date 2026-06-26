@extends('layouts.email_premium')

@section('title', 'New Post')
@section('subtitle', 'See what your family is sharing')
@section('greeting', "Hi " . ($data['firstName'] ?? 'there') . ",")

@section('content')
<div style="text-align: center; padding: 10px 0;">
    <p style="font-size: 18px; line-height: 1.6; color: #1c1e21; margin: 0;">
        <strong>{{ $data['authorName'] }}</strong> just posted something new in the family feed!
    </p>
    <div style="margin-top: 25px; padding: 20px; background-color: #ffffff; border: 1px solid #eee; border-radius: 8px; font-style: italic; color: #4b4b4b;">
        "{{ !empty($data['postContent']) ? (mb_strlen($data['postContent']) > 100 ? mb_substr($data['postContent'], 0, 100) . '...' : $data['postContent']) : 'View the post to see more details...' }}"
    </div>
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}profilePage" class="btn btn-primary">View Post</a>
@endsection
