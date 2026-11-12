@extends('email')

@section('title', 'New Connected Kin Request')
@section('subject', 'Someone wants to connect with you as Connected Kin')

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

    $relCategoryLabel = $data['relationship_category_label'] ?? 'Connected Kin';
    $relRole = $data['relationship_role'] ?? '';
    $relNote = $data['relationship_note'] ?? '';
    $relFull = $data['relationship_full_label'] ?? (($relRole !== '' && $relRole !== $relCategoryLabel) ? "{$relCategoryLabel} ({$relRole})" : $relCategoryLabel);
    $approverFamCode = $data['approverCode'] ?? 'YOUR-CODE';
@endphp

<p style="margin-bottom: 20px; font-size: 15px; color: #1e293b;">
    Hi <strong>{{ $data['approverFirstName'] }}</strong>,
</p>

<!-- Member Identity Card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; background-color: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
    <tr>
        <td width="80" valign="top">
            <img src="{{ $avatarUrl }}" alt="{{ $data['firstName'] ?? 'Profile' }}" width="72" height="72" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; display: block; border: 2px solid #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.08);" />
        </td>
        <td valign="top" style="padding-left: 15px;">
            <h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #0f172a;">{{ $data['firstName'] }} {{ $data['lastName'] }}</h2>
            
            <div style="margin-bottom: 10px;">
                <span style="display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                    Specified as: {{ $relFull }}
                </span>
            </div>

            @if(!empty($relNote))
            <div style="background-color: #ffffff; border-left: 3px solid #0284c7; padding: 10px 12px; border-radius: 4px; margin-bottom: 8px;">
                <p style="margin: 0; font-size: 13px; font-style: italic; color: #334155; line-height: 1.45;">
                    &ldquo;{{ $relNote }}&rdquo;
                </p>
            </div>
            @endif

            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #475569;">
                {{ $data['bio'] ?? 'Would like to connect with you as Connected Kin on the family platform.' }}
            </p>
        </td>
    </tr>
</table>

<!-- Access & Privacy Disclosures -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background-color: #f1f5f9; border-radius: 12px; padding: 18px; border: 1px solid #cbd5e1;">
    <tr>
        <td>
            <h3 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em;">
                ℹ️ What Connected Kin Access Means
            </h3>
            <ul style="margin: 0 0 14px 0; padding-left: 20px; font-size: 13px; color: #334155; line-height: 1.55;">
                <li style="margin-bottom: 4px;">{{ $data['firstName'] }} can view your public profile and bio.</li>
                <li style="margin-bottom: 4px;">You can exchange direct kin messages with each other.</li>
                <li>{{ $data['firstName'] }} can view general updates you share with <em>All Family Circles &amp; Connected Kin</em>.</li>
            </ul>

            <h3 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.04em;">
                🛡️ Your Sovereign Privacy Safeguards (Your Control)
            </h3>
            <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #1e293b; line-height: 1.55;">
                <li style="margin-bottom: 4px;"><strong>Your Family Tree is 100% Private:</strong> Only relatives sharing your family code (#{{ $approverFamCode }}) can view your family tree. Connected Kin cannot access your tree.</li>
                <li style="margin-bottom: 4px;"><strong>Lineage Posts are Locked:</strong> Posts you mark as <em>Birth Family</em> or <em>Married Family</em> remain completely hidden from {{ $data['firstName'] }}.</li>
                <li style="margin-bottom: 4px;"><strong>Contact Details Protected:</strong> Your phone number and private contacts stay confidential.</li>
                <li><strong>Revocable Anytime:</strong> You can disconnect or remove this connection at any time in 1-click.</li>
            </ul>
        </td>
    </tr>
</table>

<!-- Action CTA Buttons -->
<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $baseUrl }}/member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/50/{{ !empty($data['famCode']) ? $data['famCode'] : 'kinship' }}/email" style="background-color: #059669; color: #ffffff; padding: 13px 26px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block; margin-right: 12px; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">Accept as Connected Kin</a>
    <a href="{{ $baseUrl }}/member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/10/request/email" style="background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 13px 22px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block;">Decline</a>
</div>

<p style="text-align: center; font-size: 13px; color: #64748b; margin: 0;">
    Or <a href="{{ $baseUrl }}/allMembers/seeProfile/{{ $data['id'] }}" style="color: #0284c7; text-decoration: underline; font-weight: 500;">view {{ $data['firstName'] }}'s profile</a> to learn more.
</p>
@endsection
