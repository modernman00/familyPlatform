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
    $isPending = isset($data['familyStatus']) && $data['familyStatus'] === 'pending';
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Dear <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 24px; font-size: 16px; color: #334155; line-height: 1.6;">
    Welcome to <strong>FamilyPlatform</strong> — your private digital sanctuary for celebrating living milestones, capturing timeless oral memories, and connecting generations across the globe.
</p>

@if(!empty($famCode))
<!-- Family Code Heirloom Card -->
<div style="background: linear-gradient(135deg, #fdf5f5 0%, #fdf5f5 100%); border: 1px solid #f3d9db; border-radius: 12px; padding: 22px 24px; margin-bottom: 28px; text-align: center;">
    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #a31621;">Your Private Family Code</span>
    <div style="font-size: 28px; font-weight: 800; color: #8a1019; letter-spacing: 3px; margin: 10px 0; font-family: 'Courier New', Courier, monospace;">
        {{ $famCode }}
    </div>
    @if($isPending)
    <p style="margin: 0; font-size: 13px; color: #7f1d1d; line-height: 1.5;">
        Your request to join this family has been sent to your family organiser for approval. Once confirmed, you will instantly unlock full access to explore the tree and all shared memories.
    </p>
    @else
    <p style="margin: 0; font-size: 13px; color: #2a0a0d; line-height: 1.5;">
        Share this code with your parents, siblings, and cousins so they can join your family branch and contribute to your shared heritage.
    </p>
    @endif
</div>
@endif

<!-- Exciting Feature Showcase -->
<div style="margin-bottom: 30px;">
    <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: #0f172a; text-align: left;">
        What Awaits You Inside Your Family Sanctuary:
    </h3>

    <!-- Feature 1: Interactive Tree -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
        <tr>
            <td width="48" valign="top" style="padding-right: 14px;">
                <div style="width: 40px; height: 40px; background-color: #f3d9db; color: #a31621; border-radius: 10px; font-size: 20px; line-height: 40px; text-align: center; font-weight: 700;">
                    🌳
                </div>
            </td>
            <td valign="top">
                <strong style="color: #0f172a; font-size: 15px;">Interactive 7-Generation Family Tree</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">
                    Add your immediate kin in under 60 seconds. Our smart kinship engine maps up to 7 generations upwards and downwards dynamically.
                </p>
            </td>
        </tr>
    </table>

    <!-- Feature 2: Voice Capsules -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
        <tr>
            <td width="48" valign="top" style="padding-right: 14px;">
                <div style="width: 40px; height: 40px; background-color: #fdf2e9; color: #c2410c; border-radius: 10px; font-size: 20px; line-height: 40px; text-align: center; font-weight: 700;">
                    🎙️
                </div>
            </td>
            <td valign="top">
                <strong style="color: #0f172a; font-size: 15px;">Living Voice Capsules & Oral Stories</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">
                    Record laughter, bedtime stories, and wisdom from your parents and elders. Preserve their voices as everlasting heirlooms before memories fade.
                </p>
            </td>
        </tr>
    </table>

    <!-- Feature 3: Family Vault & Recipes -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
        <tr>
            <td width="48" valign="top" style="padding-right: 14px;">
                <div style="width: 40px; height: 40px; background-color: #ecfdf5; color: #047857; border-radius: 10px; font-size: 20px; line-height: 40px; text-align: center; font-weight: 700;">
                    🔐
                </div>
            </td>
            <td valign="top">
                <strong style="color: #0f172a; font-size: 15px;">Private Family Vault & Generational Recipes</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">
                    Safely archive secret family recipes, historical birth records, and vintage photographs in your encrypted, private vault.
                </p>
            </td>
        </tr>
    </table>

    <!-- Feature 4: Sunday Trivia & Time Capsules -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
        <tr>
            <td width="48" valign="top" style="padding-right: 14px;">
                <div style="width: 40px; height: 40px; background-color: #fef3c7; color: #d97706; border-radius: 10px; font-size: 20px; line-height: 40px; text-align: center; font-weight: 700;">
                    🏆
                </div>
            </td>
            <td valign="top">
                <strong style="color: #0f172a; font-size: 15px;">Sunday Heritage Trivia & Milestone Time Capsules</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">
                    Engage cousins and youth with interactive weekly trivia contests, and lock digital time capsules to be unveiled on future milestone birthdays.
                </p>
            </td>
        </tr>
    </table>
</div>

<!-- 60-Second Legacy Starter Checklist -->
<div style="background-color: #fdf8f6; border-left: 4px solid #a31621; border-radius: 8px; padding: 18px 20px; margin-bottom: 28px;">
    <h4 style="margin: 0 0 10px 0; color: #8a1019; font-size: 15px; font-weight: 700;">
        ⚡ Your 60-Second Legacy Starter:
    </h4>
    <ol style="margin: 0; padding-left: 20px; font-size: 14px; color: #334155; line-height: 1.7;">
        <li>Log in and open your <strong>Interactive Family Tree</strong>.</li>
        <li>Click <strong>"Add Parents"</strong> to honour your mother and father.</li>
        <li>Share your Family Code on <strong>WhatsApp</strong> so your kin can link to your branch!</li>
    </ol>
</div>

<!-- Action CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/login" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 14px rgba(163,22,33, 0.35); min-height: 44px; line-height: 44px; white-space: nowrap; flex-shrink: 0;">
                Enter Your Family Sanctuary &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 24px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.6; text-align: center;">
    May your family heritage be celebrated today and preserved for generations to come.
</p>

@endsection