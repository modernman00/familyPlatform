@extends ('email')
@section('title', 'Family Reels Sanctuary')

@section('subject', 'Capture Life in Motion with Family Reels')

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
    Some moments are too lively for a still photo. That is why we built the <strong>Family Reels Sanctuary</strong> — a private, vertical video stream designed exclusively for your family circle, free from random social media noise and ads.
</p>

<div style="background: linear-gradient(135deg, #fdf5f5 0%, #f3d9db 100%); border-left: 4px solid #a31621; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #8a1019; font-size: 16px; font-weight: 700;">
        📽️ Ambient Soundtracks &amp; 1-Level Threaded Replies
    </h4>
    <p style="margin: 0; color: #2a0a0d; font-size: 14px; line-height: 1.5;">
        Upload 15 to 60-second video clips of birthdays, children's milestones, or elder wisdom. Pair your reel with ambient acoustic tracks from our music studio, and reply in clean, 1-level threaded conversations.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Reels Highlights:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Vertical 9:16 full-screen immersive video player with zero buffering.</li>
    <li>Author Viewers Roster: tap the view counter to see exactly which relatives tuned in.</li>
    <li>Automated Weekly Activity Video Montage: a weekly kinetic digest of your family's best moments.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/reels" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Watch &amp; Share Family Reels &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Preserving family smiles in motion.
</p>

@endsection
