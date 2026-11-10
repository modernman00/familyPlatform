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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Dear <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    What message, piece of wisdom, or heartfelt photograph would you want your children, younger siblings, or partner to open years from today?
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        ⏳ Digital Family Time Capsules
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        With FamilyPlatform's <strong>Time Capsules</strong>, you can seal private letters, photos, or video messages into an encrypted vault and choose an exact future unlock date — an 18th birthday, graduation day, or 25th wedding anniversary.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Inspiring Time Capsule Ideas:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>A letter to your newborn or young child to be opened on their 18th birthday.</li>
    <li>A secret anniversary message and throwback photo for your spouse.</li>
    <li>A family prediction letter to be unsealed on New Year's Day 2030.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/member/time-capsule" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Create a Time Capsule &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Your time capsule remains securely locked and encrypted until its designated reveal date.
</p>

@endsection
