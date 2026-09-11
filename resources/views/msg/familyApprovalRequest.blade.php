@extends('email')

@section('title', 'New Family Join Request')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://myfamilyplatform.com')), '/');
    $approveUrl = $data['approveUrl'] ?? ($baseUrl . '/profilePage');
    $denyUrl    = $data['denyUrl']    ?? ($baseUrl . '/profilePage');
@endphp

<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['firstName'] ?? 'Family Member' }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    A new member wants to join your <strong>{{ $data['familyCode'] ?? '' }}</strong> family network on FamilyPlatform.
</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background-color: #f8fafc; padding: 20px; border-radius: 8px;">
    <tr>
        <td valign="top">
            <h2 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #1e293b;">{{ $data['requesterName'] ?? 'New Member' }}</h2>
            <p style="margin: 0 0 6px 0; font-size: 14px; color: #64748b;">{{ $data['requesterEmail'] ?? '' }}</p>
            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #334155;">
                Is requesting to connect with your family on FamilyPlatform.
            </p>
        </td>
    </tr>
</table>

<p style="margin-bottom: 20px; font-size: 15px; color: #334155;">
    Do you know this person? If so, approve their request so they can access the family network.
    If you do not recognise them, simply deny the request.
</p>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $approveUrl }}"
       style="background-color: #00bfa5; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 700; display: inline-block; margin-right: 12px; font-size: 15px;">
       Approve Request
    </a>
    <a href="{{ $denyUrl }}"
       style="background-color: #ffffff; color: #dc2626; border: 1.5px solid #dc2626; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 700; display: inline-block; font-size: 15px;">
       Deny
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #94a3b8; margin: 0;">
    You can also manage join requests from your <a href="{{ $baseUrl }}/profilePage" style="color: #00bfa5; text-decoration: underline;">profile page</a>.
    This link expires in 7 days.
</p>
@endsection
