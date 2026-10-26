@extends ('email')
@section('title', 'Where Did Your Story Begin?')

@section('subject', 'Where Did the Story Begin? Discover Your Roots')

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
    Every family dynasty has an origin point — a hometown, village, crossing, or neighbourhood where your ancestors laid the foundation for everything you are today.
</p>

<div style="background: linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%); border-left: 4px solid #a31621; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #8a1019; font-size: 16px; font-weight: 700;">
        🗺️ Lineage Quests: The Origin Story
    </h4>
    <p style="margin: 0; color: #8a1019; font-size: 14px; line-height: 1.5;">
        Take two minutes to document where your parents or grandparents grew up. Record the name of their ancestral town or parish, and watch your family heritage map light up with generations of migration and history.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Questions to Spark Conversation Tonight:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li><em>"What is the oldest family surname or ancestral town you remember hearing about?"</em></li>
    <li><em>"What year did our family first settle in our current city or home?"</em></li>
    <li><em>"What did our ancestors do for their trade, farming, or profession?"</em></li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/organogram" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Explore Your Lineage &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Your family archive remains private, encrypted, and accessible solely to your invited relatives.
</p>

@endsection
