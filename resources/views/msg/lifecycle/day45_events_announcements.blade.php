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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Whether it is an 80th birthday, a graduation feast, or a nationwide clan reunion, organising family celebrations shouldn't cause stress. Our integrated <strong>3-Gear Event Studio</strong> makes planning effortless.
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        📅 Automatic Family Tree Guest Sync (PartyPlatform)
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        With single sign-on into PartyPlatform Event Studio, your family tree is pre-loaded into Guest Builder tiered by kinship closeness (Close Family, Extended Family, and Guests). Track dietary needs, RSVPs, and gifts in real-time.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    3-Gear Planning Engine:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li><strong>Gear 1:</strong> Fast in-app reminders and 1-click Google Calendar / .ics exports.</li>
    <li><strong>Gear 2:</strong> Interactive RSVP countdown widgets on the family feed sidebar.</li>
    <li><strong>Gear 3:</strong> Full PartyPlatform workspace with seating plans, tickets, and check-in QR codes.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/event" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Plan a Family Event &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Celebrating every milestone together.
</p>

@endsection
