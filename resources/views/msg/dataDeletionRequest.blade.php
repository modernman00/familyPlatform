@extends('email')

@section('title', 'Account deletion request')
@section('subject', 'GDPR — account deletion request')

@section('content')
<p style="margin-bottom: 20px;">A member has requested erasure of their account under GDPR Article 17.</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #00bfa5; padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0 0 6px; color: #334155; font-size: 15px;"><strong>Account ID:</strong> {{ $data['account_id'] ?? '—' }}</p>
    <p style="margin: 0 0 6px; color: #334155; font-size: 15px;"><strong>Email:</strong> {{ $data['member_email'] ?? '—' }}</p>
    <p style="margin: 0; color: #334155; font-size: 15px;"><strong>Requested at:</strong> {{ $data['requested_at'] ?? '—' }}</p>
</div>

<p style="margin-bottom: 8px; color: #334155;">Action required within 30 days: verify the request, apply the retention carve-outs
(content other family members rely on, anything under legal hold), then erase or anonymise the account.</p>
@endsection
