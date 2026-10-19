@extends ('email')
@section('title', 'Sunday Heritage Trivia')

@section('subject', 'Who Knows the Family Best? Sunday Heritage Trivia')

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
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    Who in your family has the sharpest memory? Who knows which uncle was born during a historic storm, or what grandmother's very first job was?
</p>

<div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-left: 4px solid #a855f7; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #7e22ce; font-size: 16px; font-weight: 700;">
        🧠 Weekly Family Trivia
    </h4>
    <p style="margin: 0; color: #6b21a8; font-size: 14px; line-height: 1.5;">
        Every Sunday, FamilyPlatform generates interactive, lighthearted trivia questions based on your family's milestones, traditions, and kinship tree. Vote on polls and see who claims the title of <em>Family Historian</em>!
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    How It Works:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Cast your vote on this week's private family poll in under 10 seconds.</li>
    <li>Tag your siblings, cousins, and parents to join the friendly debate.</li>
    <li>Results and correct answers are revealed right inside your family sanctuary.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/member/trivia" target="_blank" style="background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.35);">
                Play Weekly Trivia &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Keep your family connected through shared joy and weekly rituals.
</p>

@endsection
