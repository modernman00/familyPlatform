@extends ('email')
@section('title', 'Start with Just One Name')

@section('subject', 'Start with Just One Name — Your Family Sanctuary Awaits')

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
    $magicUrl = (string)($data['magicUrl'] ?? "{$baseUrl}/organogram");
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    When you registered on FamilyPlatform, you took the first step to preserve your family's precious legacy. But building a full family tree can feel daunting if you try to do it all at once.
</p>

<!-- Micro-Action Focus Card -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #16a34a; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #15803d; font-size: 16px; font-weight: 700;">
        🌱 The 10-Second Micro-Start
    </h4>
    <p style="margin: 0; color: #166534; font-size: 14px; line-height: 1.6;">
        You do not need to map four generations today. Start with just <strong>one person</strong> you cherish — your mother, father, brother, or sister. Tell us their name, and your family tree will immediately bloom into life.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Why Add That First Person Today?
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>It takes fewer than 15 seconds to enter a name and relationship.</li>
    <li>Our kinship engine automatically configures generational connections for you.</li>
    <li>Click below for instant 1-tap access — no password required.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $magicUrl }}" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Add Your First Relative &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b; text-align: center;">
    Your private family sanctuary • 100% secure • Preserving memories for future generations
</p>

@endsection
