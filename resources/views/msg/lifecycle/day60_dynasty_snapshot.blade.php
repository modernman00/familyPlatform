@extends ('email')
@section('title', '60 Days of Heritage: Your Dynasty Milestone')

@section('subject', '🎉 60 Days of Heritage: Look at What You Have Built!')

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
    Dear <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 16px; color: #334155; line-height: 1.6;">
    Two months ago, you took the initiative to set up your family sanctuary on <strong>FamilyPlatform</strong>. Because of your dedication, your lineage, stories, and connections are now preserved for generations to come.
</p>

<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-left: 4px solid #6366f1; padding: 20px 24px; border-radius: 8px; margin-bottom: 24px;">
    <h4 style="margin: 0 0 8px 0; color: #4338ca; font-size: 16px; font-weight: 700;">
        🌟 Your 60-Day Dynasty Milestone
    </h4>
    <p style="margin: 0; color: #3730a3; font-size: 14px; line-height: 1.5;">
        You have turned forgotten memories into a permanent, interactive heirloom. Relatives across branches are now connected, photos and voice notes are secure, and future generations will inherit a living record of their roots.
    </p>
</div>

<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
    Next Steps for Your Family Dynasty:
</h3>

<ul style="margin: 0 0 24px 0; padding-left: 20px; color: #334155; font-size: 15px; line-height: 1.8;">
    <li>Open your organogram and review your high-resolution tree.</li>
    <li>Export or print your family tree diagram for your living room or family reunion.</li>
    <li>Continue sharing moments on the private family stream.</li>
</ul>

<!-- CTA Button -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $baseUrl }}/organogram" target="_blank" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);">
                View Your Family Sanctuary &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
    Thank you for being the guardian of your family's heritage.
</p>

@endsection
