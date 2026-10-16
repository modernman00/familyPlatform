@extends ('email')
@section('title', 'Preserve the Voices of Your Elders')

@section('subject', 'The Sound of Family: Living Voice Capsules')

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
    Photographs capture a moment in time, but <strong>the sound of a loved one's voice</strong> brings memories back to life like nothing else.
</p>

<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #16a34a; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #15803d; font-size: 16px; font-weight: 700;">
        🎙️ Living Voice Capsules
    </h4>
    <p style="margin: 0; color: #166534; font-size: 14px; line-height: 1.5;">
        You can attach high-fidelity audio recordings directly to any node on your family tree. Record an elder recounting where the family originated, a grandmother singing a lullaby, or parents sharing advice with their grandchildren.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    3 Meaningful Questions to Ask Your Elders:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li><em>"What is the earliest memory you have of your own grandparents?"</em></li>
    <li><em>"What was our ancestral hometown like when you were growing up?"</em></li>
    <li><em>"What life wisdom do you want future generations to always carry?"</em></li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/organogram" target="_blank" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);">
                Record a Voice Capsule &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Your audio records are encrypted and preserved privately for verified kin only.
</p>

@endsection
