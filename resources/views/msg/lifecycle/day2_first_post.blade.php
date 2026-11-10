@extends ('email')
@section('title', 'Share Your First Family Story')

@section('subject', 'Break the Ice: Share Your First Family Update in 3D')

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
    A family sanctuary is only as warm as the voices inside it. Now that you are set up, take a moment to say hello to your circle with a first message or memory.
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        ✨ 3D Futuristic Text Stage Studio
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        Elevate simple text updates into striking visual moments. Choose from 6 curated 3D translucent glassmorphic archetypes including <strong>Dynasty Tree</strong>, <strong>3D Holo Glass</strong>, <strong>Aurora Prism</strong>, and <strong>Royal Velvet</strong>.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Quick Conversation Starters:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Share a favourite memory from a childhood family holiday.</li>
    <li>Post a quick life update or celebratory milestone.</li>
    <li>Ask a question to see who remembers an old family tradition.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/profilePage#openPostModalTrigger" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Post Your First Message &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Preserving our heritage together.
</p>

@endsection
