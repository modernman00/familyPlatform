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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Some moments are too lively for a still photo. That is why we built the <strong>Family Reels Sanctuary</strong> — a private, vertical video stream designed exclusively for your family circle, free from random social media noise and ads.
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        📽️ Ambient Soundtracks &amp; 1-Level Threaded Replies
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        Upload 15 to 60-second video clips of birthdays, children's milestones, or elder wisdom. Pair your reel with ambient acoustic tracks from our music studio, and reply in clean, 1-level threaded conversations.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Reels Highlights:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Vertical 9:16 full-screen immersive video player with zero buffering.</li>
    <li>Author Viewers Roster: tap the view counter to see exactly which relatives tuned in.</li>
    <li>Automated Weekly Activity Video Montage: a weekly kinetic digest of your family's best moments.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/reels" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Watch &amp; Share Family Reels &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Preserving family smiles in motion.
</p>

@endsection
