@extends ('email')
@section('title', 'Your Sunday Family Sanctuary Digest')

@section('subject', 'Weekly Sunday Digest & Family Trivia Challenge')

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
    $famCode = (string)($data['famCode'] ?? '');
    $trivia = $data['trivia'] ?? null;
    $milestones = $data['milestones'] ?? [];
    $quest = $data['quest'] ?? null;
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 24px; font-size: 16px; color: #334155; line-height: 1.6;">
    Here is your Sunday evening wrap-up from your private <strong>{{ $famCode }}</strong> family sanctuary on FamilyPlatform.
</p>

<!-- 1. Sunday Family Trivia Section -->
@if(!empty($trivia))
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 5px solid #f59e0b; padding: 20px 24px; border-radius: 12px; margin-bottom: 26px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
        <span style="font-size: 20px;">🧠</span>
        <h4 style="margin: 0; color: #92400e; font-size: 17px; font-weight: 700;">
            This Week's Sunday Family Trivia
        </h4>
    </div>
    <p style="margin: 0 0 16px 0; color: #78350f; font-size: 15px; font-weight: 600; line-height: 1.5;">
        {{ $trivia['question'] ?? 'Test how well you know our family roots!' }}
    </p>

    <!-- Vote Button -->
    <table border="0" cellpadding="0" cellspacing="0" style="margin: 0;">
        <tr>
            <td align="center" style="border-radius: 8px; background: #d97706;">
                <a href="{{ $baseUrl }}/profilePage" 
                   target="_blank" 
                   style="font-size: 14px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 10px 20px; border: 1px solid #d97706; display: inline-block; font-weight: 700;">
                    👉 Vote in Feed &amp; See Results
                </a>
            </td>
        </tr>
    </table>
</div>
@endif

<!-- 2. Upcoming Birthdays & Celebrations -->
@if(!empty($milestones) && count($milestones) > 0)
<div style="background: #f0fdf4; border-left: 5px solid #10b981; padding: 20px 24px; border-radius: 12px; margin-bottom: 26px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <span style="font-size: 20px;">🎂</span>
        <h4 style="margin: 0; color: #065f46; font-size: 17px; font-weight: 700;">
            Celebrations in the Next 7 Days
        </h4>
    </div>
    <ul style="margin: 0; padding-left: 20px; color: #047857; font-size: 14px; line-height: 1.8;">
        @foreach($milestones as $m)
            <li>
                <strong>{{ $m['title'] ?? '' }}</strong> — {{ $m['date_badge'] ?? 'This Week' }}
            </li>
        @endforeach
    </ul>
</div>
@endif

<!-- 3. Missing Link Lineage Quest -->
@if(!empty($quest))
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 5px solid #a31621; padding: 20px 24px; border-radius: 12px; margin-bottom: 26px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
        <span style="font-size: 20px;">🌳</span>
        <h4 style="margin: 0; color: #1e3a8a; font-size: 17px; font-weight: 700;">
            Weekly Lineage Quest: {{ $quest['title'] ?? 'Help Complete the Tree' }}
        </h4>
    </div>
    <p style="margin: 0 0 12px 0; color: #475569; font-size: 14px; line-height: 1.5;">
        {{ $quest['subtitle'] ?? 'We are building our complete family tree. Can you or an elder help fill in this missing branch?' }}
    </p>
    <a href="{{ $baseUrl }}/organogram" style="color: #a31621; font-weight: 700; text-decoration: underline; font-size: 14px;">
        Open Organogram Canvas &amp; Quests &rarr;
    </a>
</div>
@endif

<!-- Primary CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="border-radius: 8px; background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%);">
                        <a href="{{ $baseUrl }}/profilePage" 
                           target="_blank" 
                           style="font-size: 16px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 14px 32px; border: 1px solid #a31621; display: inline-block; font-weight: 700; letter-spacing: 0.5px;">
                            Open Family Sanctuary
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 24px 0 0 0; font-size: 14px; color: #64748b; line-height: 1.5; text-align: center;">
    Your private family sanctuary • Protecting memories, stories &amp; heritage for generations.
</p>

@endsection
