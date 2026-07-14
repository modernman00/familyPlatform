@extends('email')

@section('title', 'New Post')
@section('subject', 'See what your family is sharing')

@section('content')
<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['firstName'] ?? 'there' }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 18px; color: #1e293b; text-align: center;">
    <strong>{{ $data['authorName'] }}</strong> just posted something new in the family feed!
</p>

<div style="background-color: #f8fafc; border-left: 4px solid #0a66c2; padding: 20px; border-radius: 4px; margin-bottom: 25px; font-style: italic; color: #475569;">
    "{{ !empty($data['postContent']) ? (mb_strlen($data['postContent']) > 100 ? mb_substr($data['postContent'], 0, 100) . '...' : $data['postContent']) : 'View the post to see more details...' }}"
</div>

<div style="text-align: center;">
    <a href="{{ getenv('MIX_APP_URL2') }}profilePage" style="background-color: #0a66c2; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">View Post</a>
</div>
@endsection
