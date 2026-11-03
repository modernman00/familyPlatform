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

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    Every family has that one debate that surfaces at every holiday dinner: <em>Who is the true culinary master of the house?</em> And who is notorious for burning the roast potatoes?
</p>

<!-- Banter Card -->
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #d97706; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #92400e; font-size: 16px; font-weight: 700;">
        🍗 This Week's Family Banter Poll
    </h4>
    <p style="margin: 0 0 16px 0; color: #78350f; font-size: 14px; line-height: 1.6;">
        Cast your confidential vote in 10 seconds, or forward the challenge to your family WhatsApp chat to settle the score once and for all!
    </p>
    <div style="text-align: center;">
        <a href="https://api.whatsapp.com/send?text={{ $banterShareMsg }}" target="_blank" style="background-color: #25D366; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block; box-shadow: 0 2px 8px rgba(37,211,102, 0.3);">
            Share Banter Poll on WhatsApp &rarr;
        </a>
    </div>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Cast Your Vote:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Vote for the family's best cook (or worst timekeeper).</li>
    <li>See live results from your siblings, parents, and cousins.</li>
    <li>No password needed — 1-click magic access below.</li>
</ul>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $magicUrl }}" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                Vote in Family Poll (1-Click) &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b; text-align: center;">
    Friendly family banter • Private to your family sanctuary • Zero public exposure
</p>

@endsection
