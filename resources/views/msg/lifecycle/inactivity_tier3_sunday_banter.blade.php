@extends ('email')
@section('title', 'Weekly Family Banter Poll')

@section('subject', 'Quick Question: Who Actually Makes the Best Sunday Roast in Your Family?')

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
    $magicUrl = (string)($data['magicUrl'] ?? "{$baseUrl}/profilePage");
    $banterShareMsg = urlencode("Family debate time! Cast your vote on our private family poll using our Family Code: {$famCode} at {$baseUrl}/register?famCode={$famCode}");
@endphp

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Every family has that one debate that surfaces at every holiday dinner: <em>Who is the true culinary master of the house?</em> And who is notorious for burning the roast potatoes?
</p>

<!-- Banter Card -->
<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🍗 This Week's Family Banter Poll
    </h4>
    <p style="margin: 0 0 16px 0; color: #78350f; font-size: 14px; line-height: 1.6;">
        Cast your confidential vote in 10 seconds, or forward the challenge to your family WhatsApp chat to settle the score once and for all!
    </p>
    <div style="text-align: center;">
        <a href="https://api.whatsapp.com/send?text={{ $banterShareMsg }}" target="_blank" style="background-color: #25D366; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 999px; font-weight: 700; font-size: 14px; display: inline-block; box-shadow: 0 2px 8px rgba(37,211,102, 0.3);">
            Share Banter Poll on WhatsApp &rarr;
        </a>
    </div>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Cast Your Vote:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>Vote for the family's best cook (or worst timekeeper).</li>
    <li>See live results from your siblings, parents, and cousins.</li>
    <li>No password needed — 1-click magic access below.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $magicUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Vote in Family Poll (1-Click) &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Friendly family banter • Private to your family sanctuary • Zero public exposure
</p>

@endsection
