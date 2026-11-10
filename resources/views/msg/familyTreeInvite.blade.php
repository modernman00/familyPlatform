@extends('email')

@section('title', 'Family Tree Invitation')
@section('subject', $data['subject'] ?? 'You have been invited to connect on FamilyPlatform')

@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://myfamilyplatform.com')), '/');
    $inviteUrl = $data['inviteUrl'] ?? ($baseUrl . '/register');
    $recipientName = trim((string)($data['recipientName'] ?? '')) ?: 'Family Member';
    $inviterName = trim((string)($data['inviterName'] ?? '')) ?: 'A family member';
    $familyCode = (string)($data['familyCode'] ?? '');
    $treeLabel = trim((string)($data['treeLabel'] ?? '')) ?: 'family';
    // "Olaogun Family" -> "the Olaogun Family tree"; empty -> "the family tree"
    $treePhrase = 'the ' . $treeLabel . ' tree';

    // Friendly greeting: drop honorifics ("Master", "Mrs", "Chief"...) and use the first given name.
    $honorifics = ['master', 'mr', 'mrs', 'ms', 'miss', 'mx', 'dr', 'prof', 'chief', 'sir', 'dame', 'lady', 'lord', 'rev', 'pastor', 'alhaji', 'alhaja', 'engr', 'barr', 'hon', 'aunty', 'auntie', 'uncle', 'grandma', 'grandpa'];
    $nameParts = preg_split('/\s+/', $recipientName) ?: [];
    while (count($nameParts) > 1 && in_array(strtolower(rtrim($nameParts[0], '.')), $honorifics, true)) {
        array_shift($nameParts);
    }
    $greetName = $nameParts[0] ?? $recipientName;

    // Inviter initials for the avatar chip
    $inviterParts = preg_split('/\s+/', $inviterName) ?: [];
    $inviterInitials = strtoupper(mb_substr($inviterParts[0] ?? 'F', 0, 1) . (count($inviterParts) > 1 ? mb_substr(end($inviterParts), 0, 1) : ''));
@endphp

@section('preheader', $inviterName . ' saved you a place on ' . $treePhrase . '. Claim it in under a minute.')
@section('eyebrow', 'You\'re invited')
@section('headline', 'Your place on ' . htmlspecialchars($treePhrase, ENT_QUOTES, 'UTF-8') . ' is waiting')

@section('content')

<p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.65; color: #292524;">
    Hi {{ $greetName }},
</p>

<!-- Inviter card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate !important; margin: 0 0 24px 0; background-color: #fbf7f2; border: 1px solid #efe4d8; border-radius: 14px;">
    <tr>
        <td width="56" valign="top" style="padding: 18px 0 18px 18px;">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" valign="middle" width="44" height="44" bgcolor="#a31621" style="width: 44px; height: 44px; border-radius: 22px; background-color: #a31621; color: #ffffff; font-size: 16px; font-weight: 700; font-family: 'Inter', Helvetica, Arial, sans-serif;">
                        {{ $inviterInitials }}
                    </td>
                </tr>
            </table>
        </td>
        <td valign="middle" style="padding: 18px 18px 18px 14px; font-size: 15px; line-height: 1.55; color: #44403c;">
            <strong style="color: #1c1917;">{{ $inviterName }}</strong> added you to <strong style="color: #1c1917;">{{ $treePhrase }}</strong> and reserved your branch. All that's left is for you to claim it.
        </td>
    </tr>
</table>

<!-- Primary CTA -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 28px 0;">
    <tr>
        <td align="center" class="btn-block">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $inviteUrl }}" style="height:52px;v-text-anchor:middle;width:300px;" arcsize="50%" stroke="f" fillcolor="#a31621">
                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">Claim my spot</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-- -->
            <a href="{{ $inviteUrl }}" target="_blank" style="background-color: #a31621; color: #ffffff; text-decoration: none; padding: 16px 44px; border-radius: 999px; font-weight: 700; font-size: 16px; display: inline-block; letter-spacing: 0.1px;">
                Claim my spot &rarr;
            </a>
            <!--<![endif]-->
        </td>
    </tr>
</table>

<!-- Family code ticket -->
@if ($familyCode !== '')
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate !important; margin: 0 0 30px 0;">
    <tr>
        <td align="center" style="padding: 16px; border-collapse: separate; border: 1.5px dashed #d6c7b6; border-radius: 12px;">
            <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #a8a29e;">Your family code</p>
            <p style="margin: 0; font-family: 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 24px; font-weight: 700; letter-spacing: 4px; color: #a31621;">{{ $familyCode }}</p>
        </td>
    </tr>
</table>
@endif

<!-- What's inside -->
<p style="margin: 0 0 14px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #1c1917;">
    What's waiting for you
</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 28px 0;">
    <tr>
        <td width="40" valign="top" style="padding: 0 0 16px 0; font-size: 22px; line-height: 1;">🌳</td>
        <td valign="top" style="padding: 0 0 16px 0; font-size: 15px; line-height: 1.55; color: #44403c;">
            <strong style="color: #1c1917;">See exactly where you fit.</strong> Explore the living tree and find parents, cousins and ancestors in one place.
        </td>
    </tr>
    <tr>
        <td width="40" valign="top" style="padding: 0 0 16px 0; font-size: 22px; line-height: 1;">📸</td>
        <td valign="top" style="padding: 0 0 16px 0; font-size: 15px; line-height: 1.55; color: #44403c;">
            <strong style="color: #1c1917;">Share stories and photos.</strong> Keep the family's memories together instead of scattered across group chats.
        </td>
    </tr>
    <tr>
        <td width="40" valign="top" style="padding: 0; font-size: 22px; line-height: 1;">🔒</td>
        <td valign="top" style="padding: 0; font-size: 15px; line-height: 1.55; color: #44403c;">
            <strong style="color: #1c1917;">Private to your family.</strong> Only approved relatives can see what's shared on {{ $treePhrase }}.
        </td>
    </tr>
</table>

<p style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.5; color: #78716c;">
    Button not working? Paste this link into your browser:
</p>
<p style="margin: 0 0 20px 0; font-size: 12px; line-height: 1.5; word-break: break-all;">
    <a href="{{ $inviteUrl }}" style="color: #a31621; text-decoration: underline;">{{ $inviteUrl }}</a>
</p>

<p style="margin: 0 0 8px 0; font-size: 13px; line-height: 1.5; color: #a8a29e;">
    Not expecting this? You can safely ignore this email. Nothing is shared until you accept.
</p>

@endsection
