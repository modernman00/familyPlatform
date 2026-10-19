@extends ('email')
@section('title', 'Send a Message Into the Future')

@section('subject', 'Lock a Time Capsule: A Gift for Future Milestones')

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
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Dear <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    What message, piece of wisdom, or heartfelt photograph would you want your children, younger siblings, or partner to open years from today?
</p>

<div style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); border-left: 4px solid #ec4899; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #be185d; font-size: 16px; font-weight: 700;">
        ⏳ Digital Family Time Capsules
    </h4>
    <p style="margin: 0; color: #9d174d; font-size: 14px; line-height: 1.5;">
        With FamilyPlatform's <strong>Time Capsules</strong>, you can seal private letters, photos, or video messages into an encrypted vault and choose an exact future unlock date — an 18th birthday, graduation day, or 25th wedding anniversary.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Inspiring Time Capsule Ideas:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>A letter to your newborn or young child to be opened on their 18th birthday.</li>
    <li>A secret anniversary message and throwback photo for your spouse.</li>
    <li>A family prediction letter to be unsealed on New Year's Day 2030.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/time-capsule" target="_blank" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(236, 72, 153, 0.35);">
                Create a Time Capsule &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Your time capsule remains securely locked and encrypted until its designated reveal date.
</p>

@endsection
