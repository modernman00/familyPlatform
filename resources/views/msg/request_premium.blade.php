@extends('email')

@section('title', 'New Friend Request')
@section('subject', 'Someone wants to connect with you')

@section('content')
<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['approverFirstName'] }}</strong>,
</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background-color: #f8fafc; padding: 20px; border-radius: 8px;">
    <tr>
        <td width="90" valign="top">
            <img src="{{ getenv('MIX_ROUTE_PICS') }}{{ $data['profileImg'] }}" alt="{{ $data['firstName'] }}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; display: block;" />
        </td>
        <td valign="top">
            <h2 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #1e293b;">{{ $data['firstName'] }} {{ $data['lastName'] }}</h2>
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #64748b;">Wants to join your family network</p>
            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #334155;">
                {{ $data['bio'] ?? 'Hello! I would like to connect with you on the family platform and share updates with each other.' }}
            </p>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/50/{{ $data['famCode'] }}/email" style="background-color: #0a66c2; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; margin-right: 10px;">Accept Request</a>
    <a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/10/request/email" style="background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Decline</a>
</div>

<p style="text-align: center; font-size: 14px; color: #64748b; margin: 0;">
    Or <a href="{{ getenv('MIX_APP_URL2') }}member/seeProfile/{{ $data['id'] }}" style="color: #0a66c2; text-decoration: underline; font-weight: 500;">view their profile</a> to learn more.
</p>
@endsection
