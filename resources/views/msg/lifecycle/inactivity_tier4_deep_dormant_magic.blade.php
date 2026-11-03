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

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    Every generation loses irreplaceable stories, cherished photographs, and oral histories when they remain locked in paper boxes or fading memories.
</p>

<!-- Reserved Sanctuary Card -->
<div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-left: 4px solid #ea580c; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #c2410c; font-size: 16px; font-weight: 700;">
        🏛️ Your Family Space Remains Securely Reserved
    </h4>
    <p style="margin: 0; color: #9a3412; font-size: 14px; line-height: 1.6;">
        Even though you haven't visited in a while, your family sanctuary and your family code (<strong>{{ $famCode }}</strong>) are safely waiting for you. We have simplified the experience so you can preserve your heritage in moments.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    What You Can Do Today in Under 2 Minutes:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Enter the names of your parents to anchor your tree roots.</li>
    <li>Upload one vintage family photograph to your private feed.</li>
    <li>Record a 30-second living voice capsule for your descendants.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $magicUrl }}" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Access Your Sanctuary (No Password Needed) &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b; text-align: center;">
    This 1-click link is cryptographically protected and valid for 72 hours • Preserving your dynasty's legacy
</p>

@endsection
