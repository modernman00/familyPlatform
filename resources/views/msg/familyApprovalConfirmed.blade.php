@extends('email')

@section('title', "You're now part of the family network!")

@section('content')
@php
    $baseUrl    = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://myfamilyplatform.com')), '/');
    $profileUrl = $data['profileUrl'] ?? ($baseUrl . '/profilePage');
@endphp

<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['firstName'] ?? 'Member' }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 15px; color: #1e293b;">
    🎉 Great news! Your request to join the <strong>{{ $data['familyCode'] ?? '' }}</strong> family network on FamilyPlatform has been <strong style="color: #00bfa5;">approved</strong>.
</p>

<p style="margin-bottom: 20px; color: #334155;">
    You now have full access to your family tree, member profiles, and all shared family updates.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $profileUrl }}"
       style="background-color: #00bfa5; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 700; display: inline-block; font-size: 16px;">
       Visit Your Family Network →
    </a>
</div>

<p style="font-size: 13px; color: #94a3b8; text-align: center; margin: 0;">
    If you have any questions, contact us at <a href="mailto:support@myfamilyplatform.com" style="color: #00bfa5;">support@myfamilyplatform.com</a>.
</p>
@endsection
