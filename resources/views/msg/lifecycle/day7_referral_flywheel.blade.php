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

<p style="margin: 0 0 16px 0; font-size: 16px; color: #292524; line-height: 1.65;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin: 0 0 22px 0; font-size: 16px; color: #44403c; line-height: 1.65;">
    A family tree is not built by one person alone — it thrives when the whole family gathers together.
</p>

<!-- Family Code Card with WhatsApp Invite -->
<div style="background-color: #fbf7f2; border: 1.5px dashed #d6c7b6; border-radius: 14px; padding: 24px; margin: 0 0 26px 0; text-align: center;">
    <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.6px; color: #a8a29e;">Your Family Code</span>
    <div style="font-size: 28px; font-weight: 700; color: #a31621; letter-spacing: 4px; margin: 6px 0 10px 0; font-family: 'SFMono-Regular', Menlo, Consolas, monospace;">
        {{ $famCode }}
    </div>
    <p style="margin: 0 0 18px 0; font-size: 14px; line-height: 1.6; color: #57534e;">
        When your brothers, sisters, and cousins register with this code, our intelligent kinship engine will recognize them and connect them directly into your tree!
    </p>

    <a href="https://api.whatsapp.com/send?text={{ $whatsappText }}" target="_blank" style="background-color: #25D366; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 999px; font-weight: 700; font-size: 14px; display: inline-block;">
        Share on WhatsApp &rarr;
    </a>
</div>

<h3 style="margin: 0 0 12px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    What Happens When Relatives Join:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #44403c; font-size: 15px; line-height: 1.8;">
    <li>They can claim their spot on the tree with one click.</li>
    <li>They can upload memories, photos, and life milestones to the private family feed.</li>
    <li>They can record living voice capsules of their own branch of the family.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center" class="btn-block">
            <a href="{{ $baseUrl }}/profilePage" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block;">
                View Your Family Sanctuary &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 8px 0 0 0; font-size: 13px; color: #a8a29e; text-align: center;">
    Connecting generations • Preserving legacies
</p>

@endsection
