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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    When you registered on FamilyPlatform, you took the first step to preserve your family's precious legacy. But building a full family tree can feel daunting if you try to do it all at once.
</p>

<!-- Micro-Action Focus Card -->
<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🌱 The 10-Second Micro-Start
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        You do not need to map four generations today. Start with just <strong>one person</strong> you cherish — your mother, father, brother, or sister. Tell us their name, and your family tree will immediately bloom into life.
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Why Add That First Person Today?
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>It takes fewer than 15 seconds to enter a name and relationship.</li>
    <li>Our kinship engine automatically configures generational connections for you.</li>
    <li>Click below for instant 1-tap access — no password required.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $magicUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Add Your First Relative &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Your private family sanctuary • 100% secure • Preserving memories for future generations
</p>

@endsection
