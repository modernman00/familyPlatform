@extends ('email')
@section('title', 'Your Living Avatar Awaits')

@section('subject', 'Transform Your Photo into a Living Family Avatar')

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
    Put a face to your lineage! The <strong>Avatar Studio</strong> on FamilyPlatform allows you to create a high-definition living avatar that represents you across the family tree, reels, and comment threads.
</p>

<div style="background: linear-gradient(135deg, #fdf5f5 0%, #f3d9db 100%); border-left: 4px solid #a31621; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #8a1019; font-size: 16px; font-weight: 700;">
        🎨 Instant Photo-to-Avatar Magic
    </h4>
    <p style="margin: 0; color: #2a0a0d; font-size: 14px; line-height: 1.5;">
        Upload a selfie or portrait to automatically synthesise a bespoke 3D-styled avatar. Customise hairstyles, skin tones, eye colours, and generational attire with our real-time interactive rendering engine.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    What You Can Do:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Convert any clear portrait into a living family avatar in 1 click.</li>
    <li>Unlock personalised Me-Sticker reaction packs for comments and celebrations.</li>
    <li>Display your custom avatar proudly on the 7-generation dynasty organogram.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/emojiStudio" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Launch Avatar Studio &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Expressing our unique family identity.
</p>

@endsection
