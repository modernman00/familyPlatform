@extends ('email')
@section('title', '3-Gear Event Studio & Announcements')

@section('subject', 'Plan Reunions with 3-Gear PartyPlatform Event Studio')

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
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    Whether it is an 80th birthday, a graduation feast, or a nationwide clan reunion, organising family celebrations shouldn't cause stress. Our integrated <strong>3-Gear Event Studio</strong> makes planning effortless.
</p>

<div style="background: linear-gradient(135deg, #fdf5f5 0%, #f3d9db 100%); border-left: 4px solid #a31621; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #8a1019; font-size: 16px; font-weight: 700;">
        📅 Automatic Family Tree Guest Sync (PartyPlatform)
    </h4>
    <p style="margin: 0; color: #2a0a0d; font-size: 14px; line-height: 1.5;">
        With single sign-on into PartyPlatform Event Studio, your family tree is pre-loaded into Guest Builder tiered by kinship closeness (Close Family, Extended Family, and Guests). Track dietary needs, RSVPs, and gifts in real-time.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    3-Gear Planning Engine:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li><strong>Gear 1:</strong> Fast in-app reminders and 1-click Google Calendar / .ics exports.</li>
    <li><strong>Gear 2:</strong> Interactive RSVP countdown widgets on the family feed sidebar.</li>
    <li><strong>Gear 3:</strong> Full PartyPlatform workspace with seating plans, tickets, and check-in QR codes.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/event" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Plan a Family Event &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Celebrating every milestone together.
</p>

@endsection
