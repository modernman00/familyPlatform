@extends ('email')
@section('title', 'Welcome to Your Family Dynasty')

@section('subject', 'Welcome to Your Family Sanctuary')

@if (!empty($data['id']))
@section('reference') {{ $data['id'] }} @endsection
@endif

@section('name')
{{ $data['firstName'] }} {{ $data['lastName'] }}
@endsection

@section('content')

@php
    $rawBaseUrl = (string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com');
    $baseUrl = rtrim($rawBaseUrl, '/');
    $famCode = (string)($data['famCode'] ?? '');
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Dear <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 24px; font-size: 16px; color: #334155; line-height: 1.6;">
    Welcome to <strong>FamilyPlatform</strong> — your private digital sanctuary for preserving your lineage, celebrating living milestones, and connecting generations across the globe.
</p>

@if(!empty($famCode))
<!-- Family Code Heirloom Card -->
<div style="background: linear-gradient(135deg, #fdf5f5 0%, #fdf5f5 100%); border: 1px solid #f3d9db; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; text-align: center;">
    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a31621;">Your Private Family Code</span>
    <div style="font-size: 26px; font-weight: 800; color: #8a1019; letter-spacing: 2px; margin: 8px 0; font-family: monospace;">
        {{ $famCode }}
    </div>
    <p style="margin: 0; font-size: 13px; color: #2a0a0d;">
        Share this code with your siblings, parents, and children so they can connect directly to your family tree.
    </p>
</div>
@endif

<h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: #0f172a;">
    3 Simple Steps to Build Your Legacy:
</h3>

<!-- Step 1 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px;">
    <tr>
        <td width="44" valign="top" style="padding-right: 14px;">
            <div style="width: 36px; height: 36px; background-color: #f3d9db; color: #a31621; border-radius: 50%; font-size: 18px; line-height: 36px; text-align: center; font-weight: 700;">
                1
            </div>
        </td>
        <td valign="top">
            <strong style="color: #0f172a; font-size: 15px;">Build Your Family Tree in 60 Seconds</strong>
            <p style="margin: 4px 0 0 0; font-size: 14px; color: #475569; line-height: 1.5;">
                Add your parents and siblings. Our dynamic genealogical engine will map up to 7 generations upwards and downwards automatically.
            </p>
        </td>
    </tr>
</table>

<!-- Step 2 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px;">
    <tr>
        <td width="44" valign="top" style="padding-right: 14px;">
            <div style="width: 36px; height: 36px; background-color: #f5dede; color: #a31621; border-radius: 50%; font-size: 18px; line-height: 36px; text-align: center; font-weight: 700;">
                2
            </div>
        </td>
        <td valign="top">
            <strong style="color: #0f172a; font-size: 15px;">Preserve Living Voice Capsules</strong>
            <p style="margin: 4px 0 0 0; font-size: 14px; color: #475569; line-height: 1.5;">
                Record authentic oral stories and laughter from your parents and elders. Preserve their voices as timeless family heirlooms.
            </p>
        </td>
    </tr>
</table>

<!-- Step 3 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
    <tr>
        <td width="44" valign="top" style="padding-right: 14px;">
            <div style="width: 36px; height: 36px; background-color: #fef3c7; color: #d97706; border-radius: 50%; font-size: 18px; line-height: 36px; text-align: center; font-weight: 700;">
                3
            </div>
        </td>
        <td valign="top">
            <strong style="color: #0f172a; font-size: 15px;">Invite Your Kin & Relatives</strong>
            <p style="margin: 4px 0 0 0; font-size: 14px; color: #475569; line-height: 1.5;">
                Share your family link on WhatsApp or SMS. When relatives join, our smart auto-matching will position them right beside you on the tree.
            </p>
        </td>
    </tr>
</table>

<!-- Action CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/login" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Enter Your Family Sanctuary &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 24px 0 0 0; font-size: 14px; color: #64748b; line-height: 1.6; text-align: center;">
    May your family heritage be celebrated today and preserved for generations to come.
</p>

@endsection