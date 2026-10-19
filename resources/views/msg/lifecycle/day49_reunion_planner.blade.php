@extends ('email')
@section('title', 'Plan Your Next Family Gathering')

@section('subject', 'When is the Family Gathering Next? Shared Celebrations Calendar')

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
    Family life moves quickly, and organizing get-togethers across different households, cities, and time zones can easily turn into messy group chat messages.
</p>

<div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-left: 4px solid #ef4444; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #b91c1c; font-size: 16px; font-weight: 700;">
        📅 Shared Events & Celebration Hub
    </h4>
    <p style="margin: 0; color: #991b1b; font-size: 14px; line-height: 1.5;">
        With FamilyPlatform's <strong>Events Hub</strong>, every family milestone — birthdays, anniversaries, holiday lunches, and virtual reunions — sits on one elegant, synchronised calendar with automated 1-click RSVPs.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Organise Your Next Gathering:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Check upcoming birthdays and wedding anniversaries for the next 60 days.</li>
    <li>Create a family gathering event with venue details or a video link.</li>
    <li>Track attendance and send gentle automated reminders to all relatives.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/event" target="_blank" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);">
                View Family Events Calendar &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Never let an important family milestone pass unnoticed.
</p>

@endsection
