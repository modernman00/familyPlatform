@extends('email')

@section('title', 'Connect Your Family Branch on FamilyPlatform')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
    $treeUrl = $data['treeUrl'] ?? ($data['studioUrl'] ?? ($baseUrl . '/organogram?mode=canvas'));
    $requesterName = $data['requesterName'] ?? 'A family relative';
    $firstName = $data['firstName'] ?? 'Family Member';
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b;">
    Hello <strong>{{ $firstName }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 15px; color: #334155; line-height: 1.6;">
    <strong>{{ $requesterName }}</strong> invited you to connect your branch of our family tree on FamilyPlatform! You can easily link your relatives to preserve your shared heritage and grow our family circle together.
</p>

@if(!empty($data['note']))
<div style="margin-bottom: 25px; background-color: #f8fafc; border-left: 4px solid #a31621; padding: 16px 20px; border-radius: 8px;">
    <p style="margin: 0; font-size: 14px; font-style: italic; color: #8a1019; line-height: 1.5;">
        "{{ htmlspecialchars((string)$data['note']) }}"
    </p>
    <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748b; font-weight: 600;">
        — {{ $requesterName }}
    </p>
</div>
@endif

<p style="margin-bottom: 25px; font-size: 15px; color: #334155; line-height: 1.6;">
    Connecting your family branch takes just a few moments on the <strong>Family Tree</strong>. You can link your parents, spouse, siblings, and children to celebrate your shared roots and expand our family timeline.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $treeUrl }}"
       style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; display: inline-block; font-size: 15px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
        🌱 View Family Tree & Connect Branch
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #94a3b8; margin: 0; line-height: 1.5;">
    Ready to get started? Visit your <a href="{{ $treeUrl }}" style="color: #10b981; text-decoration: underline;">Family Tree</a> anytime from your dashboard.
</p>
@endsection
