@extends ('email')
@section('title', 'Preserve Elder Wisdom: 60-Second Video Reels')

@section('subject', 'A 60-Second Video That Will Be Priceless in 30 Years')

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
    Think about the wisdom and experiences your parents, aunts, uncles, and grandparents hold. In thirty or fifty years, what would future generations give to hear them speak directly to the camera?
</p>

<div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border-left: 4px solid #14b8a6; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #0f766e; font-size: 16px; font-weight: 700;">
        🎬 Living Heritage Reels
    </h4>
    <p style="margin: 0; color: #115e59; font-size: 14px; line-height: 1.5;">
        You do not need a studio or complicated equipment. Using your mobile phone, capture a simple 60-second video of an elder sharing their favourite piece of advice or their most memorable life lesson.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    3 Simple Prompts for Your 60-Second Reel:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li><em>"What is the greatest blessing our family has experienced?"</em></li>
    <li><em>"What advice would you give to the younger generation starting their careers?"</em></li>
    <li><em>"What made you fall in love with your spouse?"</em></li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/reels" target="_blank" style="background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(20, 184, 166, 0.35);">
                Record a Family Reel &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    All video memories are stored safely within your private family archive.
</p>

@endsection
