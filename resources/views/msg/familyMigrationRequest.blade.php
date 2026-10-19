@extends('email')

@section('title', 'Family Migration Request')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
    $profileUrl = $data['profileUrl'] ?? ($baseUrl . '/profilePage');
@endphp

<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] ?? 'Family Member' }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    <strong>{{ $data['requesterName'] ?? 'A relative' }}</strong> has discovered your family in the directory and requested to migrate their account into your <strong>#{{ $data['familyCode'] ?? '' }}</strong> family network!
</p>

@if(!empty($data['note']))
<div style="margin-bottom: 25px; background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px 20px; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; font-style: italic; color: #1e40af;">
        "{{ htmlspecialchars($data['note']) }}"
    </p>
</div>
@endif

<p style="margin-bottom: 25px; font-size: 15px; color: #334155; line-height: 1.6;">
    If you recognise this person as a member of your family, you can approve their migration request to connect them directly to your family tree.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $profileUrl }}"
       style="background-color: #00bfa5; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 700; display: inline-block; font-size: 15px; box-shadow: 0 4px 12px rgba(0, 191, 165, 0.25);">
       Review & Approve Request
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #94a3b8; margin: 0;">
    Manage all pending approvals directly on your <a href="{{ $profileUrl }}" style="color: #00bfa5; text-decoration: underline;">profile dashboard</a>.
    This request expires automatically in 7 days.
</p>
@endsection
