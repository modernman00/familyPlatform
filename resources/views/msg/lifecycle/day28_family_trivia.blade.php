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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Who in your family has the sharpest memory? Who knows which uncle was born during a historic storm, or what grandmother's very first job was?
</p>

<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🧠 Weekly Family Trivia
    </h4>
    <p style="margin: 0; color: #44403c; font-size: 14px; line-height: 1.6;">
        Every Sunday, FamilyPlatform generates interactive, lighthearted trivia questions based on your family's milestones, traditions, and kinship tree. Vote on polls and see who claims the title of <em>Family Historian</em>!
    </p>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    How It Works:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Cast your vote on this week's private family poll in under 10 seconds.</li>
    <li>Tag your siblings, cousins, and parents to join the friendly debate.</li>
    <li>Results and correct answers are revealed right inside your family sanctuary.</li>
</ul>

<!-- CTA Button -->
@php
    $triviaPostNo = !empty($data['post_no']) ? (int)$data['post_no'] : null;
    $triviaUrl = $triviaPostNo 
        ? "{$baseUrl}/profilePage?post_no={$triviaPostNo}#post{$triviaPostNo}" 
        : "{$baseUrl}/profilePage";
@endphp
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $triviaUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Play Weekly Trivia &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Keep your family connected through shared joy and weekly rituals.
</p>

@endsection
