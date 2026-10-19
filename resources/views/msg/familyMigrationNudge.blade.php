@extends('email')

@section('title', 'Invitation to Join Family Network')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
    $acceptUrl = $data['acceptUrl'] ?? ($baseUrl . '/profilePage');
    $profileUrl = $data['profileUrl'] ?? ($baseUrl . '/profilePage');
@endphp

<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] ?? 'Family Member' }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    <strong>{{ $data['requesterName'] ?? 'A family member' }}</strong> noticed that you are registered with a different family code and has sent you an invitation to migrate to the <strong>#{{ $data['newFamilyCode'] ?? '' }}</strong> family tree!
</p>

@if(!empty($data['note']))
<div style="margin-bottom: 25px; background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; font-style: italic; color: #166534;">
        "{{ htmlspecialchars($data['note']) }}"
    </p>
</div>
@endif

<p style="margin-bottom: 25px; font-size: 15px; color: #334155; line-height: 1.6;">
    Migrating to <strong>#{{ $data['newFamilyCode'] ?? '' }}</strong> will link you directly to your relatives on the interactive family tree, connect your shared memories, and unite your household timeline.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $profileUrl }}"
       style="background-color: #00bfa5; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 700; display: inline-block; font-size: 15px; box-shadow: 0 4px 12px rgba(0, 191, 165, 0.25);">
       Review & Accept Migration
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #94a3b8; margin: 0;">
    You can also manage migration invitations from your <a href="{{ $profileUrl }}" style="color: #00bfa5; text-decoration: underline;">profile dashboard</a>.
    This invitation remains valid for 7 days.
</p>
@endsection
