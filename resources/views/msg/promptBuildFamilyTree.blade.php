@extends('email')

@section('title', 'Build Your Family Tree on FamilyPlatform')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
    $studioUrl = $data['studioUrl'] ?? ($baseUrl . '/familyStudio');
    $requesterName = $data['requesterName'] ?? 'A family relative';
    $firstName = $data['firstName'] ?? 'Family Member';
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b;">
    Hello <strong>{{ $firstName }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 15px; color: #334155; line-height: 1.6;">
    <strong>{{ $requesterName }}</strong> visited your family tree on FamilyPlatform and noticed you haven't connected your relatives yet. They would love to see your branch of the family tree!
</p>

@if(!empty($data['note']))
<div style="margin-bottom: 25px; background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px 20px; border-radius: 8px;">
    <p style="margin: 0; font-size: 14px; font-style: italic; color: #1e40af; line-height: 1.5;">
        "{{ htmlspecialchars((string)$data['note']) }}"
    </p>
    <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748b; font-weight: 600;">
        — {{ $requesterName }}
    </p>
</div>
@endif

<p style="margin-bottom: 25px; font-size: 15px; color: #334155; line-height: 1.6;">
    Building your family tree takes just a few minutes in the <strong>Family Studio</strong>. You can connect your parents, spouse, siblings, and children to preserve your heritage and unlock your shared family timeline.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $studioUrl }}"
       style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; display: inline-block; font-size: 15px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
        🌱 Build Your Family Tree Now
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #94a3b8; margin: 0; line-height: 1.5;">
    Need help getting started? Visit your <a href="{{ $studioUrl }}" style="color: #10b981; text-decoration: underline;">Family Studio</a> anytime from your dashboard.
</p>
@endsection
