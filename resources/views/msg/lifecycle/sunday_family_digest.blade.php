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
    $recentPosts = $data['recent_posts'] ?? [];
@endphp

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 24px; font-size: 16px; color: #334155; line-height: 1.6;">
    Here is your Sunday evening wrap-up from your private <strong>{{ $famCode }}</strong> family sanctuary on FamilyPlatform.
</p>

<!-- 1. Sunday Family Trivia & Banter Section -->
@if(!empty($trivia))
@php
    $triviaPostNo = !empty($trivia['post_no']) ? (int)$trivia['post_no'] : null;
    $triviaUrl = $triviaPostNo 
        ? "{$baseUrl}/profilePage?post_no={$triviaPostNo}#post{$triviaPostNo}" 
        : "{$baseUrl}/profilePage";
@endphp
<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; color: #a31621; font-size: 16px; font-weight: 700;"><span style="font-size: 18px; margin-right: 6px;">🏆</span><a href="{{ $triviaUrl }}" target="_blank" style="color: #a31621; text-decoration: none;">This Week's Sunday Family Ritual &amp; Poll</a>
        </h4>
    </div>
    <p style="margin: 0 0 18px 0; color: #1c1917; font-family: Georgia, 'Times New Roman', serif; font-size: 18px; font-weight: 700; line-height: 1.4;">
        <a href="{{ $triviaUrl }}" target="_blank" style="color: #1c1917; text-decoration: none;">{{ $trivia['question'] ?? 'Vote in this week\'s family poll and join the debate!' }}</a>
    </p>

    <!-- Vote Button -->
    <table border="0" cellpadding="0" cellspacing="0" style="margin: 0;">
        <tr>
            <td align="center" bgcolor="#1c1917" style="border-radius: 999px; background-color: #1c1917;">
                <a href="{{ $triviaUrl }}" 
                   target="_blank" 
                   style="font-size: 14px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 999px; padding: 12px 24px; border: 1px solid #1c1917; display: inline-block; font-weight: 700; min-height: 44px; box-sizing: border-box; line-height: 20px;">
                    👉 Vote in Feed &amp; See Results
                </a>
            </td>
        </tr>
    </table>
</div>
@endif

<!-- 2. Upcoming Birthdays & Celebrations -->
@if(!empty($milestones) && count($milestones) > 0)
<div style="background-color: #ffffff; border: 1px solid #efe4d8; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <div style="margin-bottom: 12px;">
        <h4 style="margin: 0; color: #1c1917; font-size: 16px; font-weight: 700;"><span style="font-size: 18px; margin-right: 6px;">🎂</span>Celebrations in the Next 7 Days
        </h4>
    </div>
    <ul style="margin: 0; padding-left: 20px; color: #44403c; font-size: 14px; line-height: 1.8;">
        @foreach($milestones as $m)
            <li style="margin-bottom: 6px;">
                <strong>{{ $m['title'] ?? '' }}</strong> — {{ $m['date_badge'] ?? 'This Week' }}
                @if(($m['type'] ?? '') === 'anniversary')
                    @php
                        $annivRecipient = trim((string)preg_replace("/'s\s+Anniversary$/i", '', (string)($m['title'] ?? '')));
                        $annivCardUrl = $baseUrl . '/member/festive-cards?' . http_build_query(array_filter([
                            'occasion' => 'wedding',
                            'headline' => 'Happy Wedding Anniversary',
                            'recipient' => ($annivRecipient !== '' && strcasecmp($annivRecipient, 'Wedding Anniversary') !== 0) ? $annivRecipient : null,
                        ]));
                    @endphp
                    <br>
                    <a href="{{ $annivCardUrl }}" target="_blank" style="display: inline-block; margin-top: 6px; padding: 8px 14px; border-radius: 999px; background-color: #a31621; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px;">
                        💍 Create Celebratory Card &rarr;
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endif

<!-- 3. Missing Link Lineage Quest -->
@if(!empty($quest))
<div style="background-color: #ffffff; border: 1px solid #efe4d8; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <div style="margin-bottom: 8px;">
        <h4 style="margin: 0; color: #1c1917; font-size: 16px; font-weight: 700;"><span style="font-size: 18px; margin-right: 6px;">🌳</span>Weekly Lineage Quest: {{ $quest['title'] ?? 'Help Complete the Tree' }}
        </h4>
    </div>
    <p style="margin: 0 0 12px 0; color: #57534e; font-size: 14px; line-height: 1.6;">
        {{ $quest['subtitle'] ?? 'We are building our complete family tree. Can you or an elder help fill in this missing branch?' }}
    </p>
    <a href="{{ $baseUrl }}/organogram" style="color: #a31621; font-weight: 700; text-decoration: underline; font-size: 14px;">
        Open Family Tree Canvas &amp; Quests &rarr;
    </a>
</div>
@endif

<!-- 4. Recent Family Updates (Weekly Post Digest) -->
@if(!empty($recentPosts) && count($recentPosts) > 0)
<div style="background-color: #ffffff; border: 1px solid #efe4d8; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <div style="margin-bottom: 12px;">
        <h4 style="margin: 0; color: #1c1917; font-size: 16px; font-weight: 700;"><span style="font-size: 18px; margin-right: 6px;">📰</span>Family Updates You May Have Missed
        </h4>
    </div>
    <ul style="margin: 0; padding-left: 20px; color: #44403c; font-size: 14px; line-height: 1.7;">
        @foreach($recentPosts as $rp)
            <li style="margin-bottom: 8px;">
                <strong>{{ $rp['author'] ?? 'Family Member' }}:</strong>
                {{ $rp['excerpt'] ?? 'Shared a new update' }}
                @if(!empty($rp['post_no']))
                    <a href="{{ $baseUrl }}/profilePage?post_no={{ (int)$rp['post_no'] }}#post{{ (int)$rp['post_no'] }}" target="_blank" style="color: #a31621; font-weight: 600; text-decoration: underline;">View post</a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endif

<!-- Primary CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" bgcolor="#a31621" style="border-radius: 999px; background-color: #a31621;">
                        <a href="{{ $baseUrl }}/profilePage" 
                           target="_blank" 
                           style="font-size: 16px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 999px; padding: 16px 40px; border: 1px solid #a31621; display: inline-block; font-weight: 700;">
                            Open Family Sanctuary
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; line-height: 1.5; text-align: center;">
    Your private family sanctuary • Protecting memories, stories &amp; heritage for generations.
</p>

@endsection
