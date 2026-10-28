@extends('email')

@section('title', 'Family Tree Invitation')
@section('subject', $data['subject'] ?? 'You have been invited to connect on FamilyPlatform')

@section('content')
@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://myfamilyplatform.com')), '/');
    $inviteUrl = $data['inviteUrl'] ?? ($baseUrl . '/register');
    $recipientName = $data['recipientName'] ?? 'Family Member';
    $inviterName = $data['inviterName'] ?? 'A family member';
    $familyCode = $data['familyCode'] ?? '';
    $treeLabel = $data['treeLabel'] ?? 'our Family Tree';
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b;">
    Hello <strong>{{ $recipientName }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 15px; line-height: 1.6; color: #334155;">
    <strong>{{ $inviterName }}</strong> has reserved your branch on <strong>{{ $treeLabel }}</strong> (#{{ $familyCode }}) on FamilyPlatform.
</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
    <tr>
        <td valign="top">
            <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #0f172a;">🌳 Join {{ $treeLabel }}</h3>
            <p style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.5; color: #475569;">
                Connect with your family members, explore our ancestry and roots, share stories, and preserve generations of memories together in our secure private family sanctuary.
            </p>
            <div style="font-size: 13px; font-weight: 600; color: #a31621;">
                Family Code: #{{ $familyCode }}
            </div>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="{{ $inviteUrl }}"
       style="background: linear-gradient(135deg, #a31621 0%, #b8202c 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 700; display: inline-block; font-size: 16px; box-shadow: 0 4px 12px rgba(163, 22, 33, 0.25);">
       Claim Your Spot on the Family Tree
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #64748b; margin: 0; line-height: 1.5;">
    If the button above does not work, copy and paste this link into your browser:<br>
    <a href="{{ $inviteUrl }}" style="color: #a31621; word-break: break-all;">{{ $inviteUrl }}</a>
</p>
@endsection
