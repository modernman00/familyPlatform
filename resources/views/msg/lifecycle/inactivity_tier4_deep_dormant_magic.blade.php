@extends ('email')
@section('title', 'Your Family Sanctuary is Still Reserved')

@section('subject', 'Your Family Sanctuary is Still Reserved — 1-Click Access (No Password Needed)')

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
    $magicUrl = (string)($data['magicUrl'] ?? "{$baseUrl}/organogram");
@endphp

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Every generation loses irreplaceable stories, cherished photographs, and oral histories when they remain locked in paper boxes or fading memories.
</p>

<!-- Reserved Sanctuary Card -->
<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🏛️ Your Family Space Remains Securely Reserved
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        Even though you haven't visited in a while, your family sanctuary and your family code (<strong>{{ $famCode }}</strong>) are safely waiting for you. We have simplified the experience so you can preserve your heritage in moments.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    What You Can Do Today in Under 2 Minutes:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Enter the names of your parents to anchor your tree roots.</li>
    <li>Upload one vintage family photograph to your private feed.</li>
    <li>Record a 30-second living voice capsule for your descendants.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $magicUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Access Your Sanctuary (No Password Needed) &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    This 1-click link is cryptographically protected and valid for 72 hours • Preserving your dynasty's legacy
</p>

@endsection
