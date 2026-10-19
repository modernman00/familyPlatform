@extends ('email')
@section('title', 'The Flavours of Family')

@section('subject', 'What Dish Defines Sunday Dinner in Your Home?')

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
    Think about the dishes that bring your family together around the dinner table — Grandmother's secret stew, Sunday roast, spiced festive pastries, or traditional holiday puddings.
</p>

<div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 4px solid #f59e0b; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #b45309; font-size: 16px; font-weight: 700;">
        🍲 The Private Family Journal
    </h4>
    <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
        Too many culinary traditions are lost across generations simply because they were never written down. With FamilyPlatform's <strong>Family Journal</strong>, you can preserve step-by-step recipes, culinary secrets, and the heartwarming stories behind them.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Quick Heritage Challenge:
</h3>

<ol style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Open your private Family Journal.</li>
    <li>Post one signature family recipe or special holiday tradition.</li>
    <li>Upload a photograph of the dish or of your family dining together.</li>
</ol>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/journal" target="_blank" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);">
                Preserve a Family Recipe &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Your recipes and journals are safely stored in your private family vault.
</p>

@endsection
