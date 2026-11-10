@extends('email')

@section('title', 'Connect Your Family Branch on FamilyPlatform')

@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
    $treeUrl = $data['treeUrl'] ?? ($data['studioUrl'] ?? ($baseUrl . '/organogram?mode=canvas'));
    $requesterName = $data['requesterName'] ?? 'A family relative';
    $firstName = $data['firstName'] ?? 'Family Member';
@endphp

@section('preheader', $requesterName . ' would love you to add your branch to the family tree.')
@section('eyebrow', 'Grow the family tree')
@section('headline', htmlspecialchars((string)$requesterName, ENT_QUOTES, 'UTF-8') . ' asked you to add your branch')

@section('content')

<p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.65; color: #292524;">
    Hi {{ $firstName }},
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; line-height: 1.65; color: #44403c;">
    <strong style="color: #1c1917;">{{ $requesterName }}</strong> is filling in the family tree and would love your side of the story. Linking your parents, spouse, siblings and children takes just a few minutes.
</p>

@if(!empty($data['note']))
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 24px 0;">
    <tr>
        <td style="background-color: #fbf7f2; border-left: 3px solid #a31621; padding: 16px 20px; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 16px; font-style: italic; color: #44403c; line-height: 1.55;">
                &ldquo;{{ (string)$data['note'] }}&rdquo;
            </p>
            <p style="margin: 8px 0 0 0; font-size: 13px; color: #78716c; font-weight: 600;">
                &mdash; {{ $requesterName }}
            </p>
        </td>
    </tr>
</table>
@endif

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $treeUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Add my branch &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0 0 8px 0; font-size: 13px; line-height: 1.5; color: #a8a29e; text-align: center;">
    You can also open the Family Tree any time from your dashboard.
</p>
@endsection
