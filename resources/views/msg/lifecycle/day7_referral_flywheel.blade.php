@extends ('email')
@section('title', 'Bring Your Siblings and Cousins Home')

@section('subject', 'Connect Generations: Expand Your Family Circle')

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
    $whatsappText = urlencode("Join me on FamilyPlatform! Connect to our family tree using our Family Code: {$famCode} at {$baseUrl}/register?famCode={$famCode}");
@endphp

<p style="margin-bottom: 20px; font-size: 16px; color: #1e293b; line-height: 1.6;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    A family tree is not built by one person alone — it thrives when the whole family gathers together.
</p>

<!-- Family Code Card with WhatsApp Invite -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Your Family Code</span>
    <div style="font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: 2px; margin: 8px 0; font-family: monospace;">
        {{ $famCode }}
    </div>
    <p style="margin: 0 0 16px 0; font-size: 14px; color: #475569;">
        When your brothers, sisters, and cousins register with this code, our intelligent kinship engine will recognize them and connect them directly into your tree!
    </p>

    <a href="https://api.whatsapp.com/send?text={{ $whatsappText }}" target="_blank" style="background-color: #25D366; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block;">
        Share on WhatsApp &rarr;
    </a>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    What Happens When Relatives Join:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>They can claim their spot on the tree with one click.</li>
    <li>They can upload memories, photos, and life milestones to the private family feed.</li>
    <li>They can record living voice capsules of their own branch of the family.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/profilePage" target="_blank" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(163,22,33, 0.35);">
                View Your Family Sanctuary &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Connecting generations • Preserving legacies
</p>

@endsection
