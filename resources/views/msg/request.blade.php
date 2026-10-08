@extends('email')

@section('title', 'New Friend Request')
@section('subject', 'Someone wants to connect with you')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://myfamilyplatform.com')), '/');

    // Email client proxy safety: if running on local domain, use public asset host so external email clients can display images
    $configuredAssetBase = (string)($_ENV['APP_ASSET_URL'] ?? getenv('APP_ASSET_URL') ?: '');
    if (!empty($configuredAssetBase)) {
        $assetBase = rtrim($configuredAssetBase, '/');
    } elseif (preg_match('/(\.test|\.local|localhost|127\.0\.0\.1)/i', $baseUrl)) {
        $assetBase = 'https://myfamilyplatform.com';
    } else {
        $assetBase = $baseUrl;
    }

    $rawImg = (string)($data['profileImg'] ?? ($data['profilePics'] ?? ($data['img'] ?? '')));
    $genderAvatar = ($data['gender'] ?? '') === 'Female' ? 'avatarF.png' : 'avatarM.png';

    // Domain whitelist to neutralize SSRF and web beacon tracking pixel exfiltration
    $allowedHosts = array_filter([
        parse_url($baseUrl, PHP_URL_HOST),
        parse_url($assetBase, PHP_URL_HOST),
        'myfamilyplatform.com',
        'www.myfamilyplatform.com',
    ]);

    if (!empty($rawImg)) {
        if (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
            $parsedHost = parse_url($rawImg, PHP_URL_HOST);
            if ($parsedHost && in_array(strtolower((string)$parsedHost), $allowedHosts, true)) {
                $avatarUrl = $rawImg;
            } else {
                // Reject untrusted external domain / tracker beacon -> safe platform fallback
                $avatarUrl = $assetBase . '/resources/images/profile/' . $genderAvatar;
            }
        } else {
            // Strip any directory traversal or path prefixes -> canonical resources/images/profile/ storage
            $cleanFile = basename($rawImg);
            $avatarUrl = $assetBase . '/resources/images/profile/' . ltrim($cleanFile, '/');
        }
    } else {
        $avatarUrl = $assetBase . '/resources/images/profile/' . $genderAvatar;
    }
@endphp

<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['approverFirstName'] }}</strong>,
</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background-color: #f8fafc; padding: 20px; border-radius: 8px;">
    <tr>
        <td width="90" valign="top">
            <img src="{{ $avatarUrl }}" alt="{{ $data['firstName'] ?? 'Profile' }}" width="80" height="80" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; display: block;" />
        </td>
        <td valign="top">
            <h2 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #1e293b;">{{ $data['firstName'] }} {{ $data['lastName'] }}</h2>
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #64748b;">Wants to join your family network</p>
            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #334155;">
                "Hello! I would like to connect with you on the family platform and share updates with each other."
            </p>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $baseUrl }}/member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/50/{{ $data['famCode'] }}/email" style="background-color: #00bfa5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; margin-right: 10px;">Accept Request</a>
    <a href="{{ $baseUrl }}/member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/10/request/email" style="background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Decline</a>
</div>

<p style="text-align: center; font-size: 14px; color: #64748b; margin: 0;">
    Or <a href="{{ $baseUrl }}/member/seeProfile/{{ $data['id'] }}" style="color: #00bfa5; text-decoration: underline; font-weight: 500;">view their profile</a> to learn more.
</p>
@endsection