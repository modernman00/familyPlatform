@extends ('email')
@section('title', 'Family Roundtable & Hub')

@section('subject', 'Spark Intergenerational Talks at the Family Roundtable')

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
    Every family has healthy debates, funny arguments, and big questions to resolve. The <strong>Family Roundtable</strong> is our dedicated space for thoughtful, intergenerational conversations.
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🎙️ Respectful Intergenerational Dialogue
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        Unlike fast-scrolling group chats where voices get buried, the Roundtable provides focused discussion cards. Post a question of the week, hold a consensus vote, or ask elders for advice in an organised, respectful environment.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Sample Roundtable Topics:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Where should we host next year's summer family reunion?</li>
    <li>What is the single best piece of advice passed down in our family?</li>
    <li>Who was the most adventurous ancestor on our family tree?</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/profilePage?tab=roundtable" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Join the Roundtable &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Family Code: <strong>{{ $famCode }}</strong> • Every family voice matters.
</p>

@endsection
