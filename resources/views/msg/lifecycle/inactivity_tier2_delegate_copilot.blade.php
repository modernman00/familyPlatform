@extends ('email')
@section('title', 'Delegate Your Family Chronicler Torch')

@section('subject', 'Too Busy to Build Your Family Tree Alone? Delegate the Torch')

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
    $magicUrl = (string)($data['magicUrl'] ?? "{$baseUrl}/organogram");
    $whatsappMsg = urlencode("Hello! I started our private family tree on FamilyPlatform. Jump in and help me add everyone using our Family Code: {$famCode} at {$baseUrl}/register?famCode={$famCode}");
@endphp

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    Life gets busy, and preserving generations of family heritage should never feel like a solo burden on your shoulders alone.
</p>

<!-- Co-Pilot Invitation Card -->
<div style="background-color: #fbf7f2; border: 1px solid #efe4d8; border-left: 3px solid #a31621; border-radius: 12px; padding: 20px 22px; margin: 0 0 26px 0;">
    <h4 style="margin: 0 0 6px 0; color: #a31621; font-size: 15px; font-weight: 700;">
        🤝 Invite a Family Co-Pilot via WhatsApp
    </h4>
    <p style="margin: 0 0 16px 0; color: #1e40af; font-size: 14px; line-height: 1.6;">
        Every family has a natural storyteller — a sister who collects old photographs, an uncle who remembers every birthday, or a cousin who loves family history. Pass them the key so they can help you co-build.
    </p>
    <div style="text-align: center;">
        <a href="https://api.whatsapp.com/send?text={{ $whatsappMsg }}" target="_blank" style="background-color: #25D366; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 999px; font-weight: 700; font-size: 14px; display: inline-block; box-shadow: 0 2px 8px rgba(37,211,102, 0.3);">
            Share Family Key on WhatsApp &rarr;
        </a>
    </div>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    Your Reserved Family Code:
</h3>

<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 24px;">
    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; font-weight: 700;">Private Family Code</span>
    <div style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: 2px; margin: 6px 0; font-family: monospace;">
        {{ $famCode }}
    </div>
</div>

<!-- 1-Click Magic CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $magicUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                Access Your Sanctuary (1-Click Login) &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Your relatives can join instantly • No technical setup required • Shared family history
</p>

@endsection
