@extends('email')

@section('title', 'Cloudflare Stream Alert')
@section('subject', '🚨 [CRITICAL ALERT] Cloudflare Stream API Token Authorisation Failure')

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>Wally</strong>,
</p>

<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 4px; margin-bottom: 25px;">
    <p style="margin: 0 0 10px; font-weight: 700; color: #991b1b; font-size: 16px;">
        🚨 Cloudflare Stream API Token Authorisation Failure
    </p>
    <p style="margin: 0; color: #7f1d1d; font-size: 14px;">
        The system attempted to generate a video direct-upload URL, but Cloudflare rejected the API credentials.
    </p>
</div>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 6px; margin-bottom: 25px; font-family: monospace; font-size: 13px;">
    <p style="margin: 0 0 6px;"><strong>Error:</strong> {{ $data['error'] ?? 'Authorisation Failure' }}</p>
    <p style="margin: 0 0 6px;"><strong>HTTP Code:</strong> {{ $data['httpCode'] ?? 403 }}</p>
    <p style="margin: 0 0 6px;"><strong>Account ID:</strong> {{ $data['accountId'] ?? 'N/A' }}</p>
    <p style="margin: 0;"><strong>Timestamp:</strong> {{ $data['time'] ?? date('Y-m-d H:i:s T') }}</p>
</div>

<div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 4px; margin-bottom: 25px;">
    <p style="margin: 0 0 8px; font-weight: 600; color: #166534; font-size: 14px;">
        🛡️ Automatic Fallback Active:
    </p>
    <p style="margin: 0; color: #15803d; font-size: 13px;">
        Zero user uploads were lost. The platform automatically engaged <strong>Sovereign Local Video Storage</strong>. However, videos are currently uploading to your local server rather than Cloudflare Edge.
    </p>
</div>

<p style="margin-bottom: 25px; font-size: 14px; color: #334155;">
    To restore high-speed Cloudflare Direct Uploads, please generate a new API token with <strong>Account &gt; Stream &gt; Edit</strong> permissions at Cloudflare and update <code>CLOUDFLARE_STREAM_API_TOKEN</code> in your <code>.env</code> file.
</p>

<div style="text-align: center;">
    <a href="https://dash.cloudflare.com/profile/api-tokens" style="background-color: #a31621; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Manage Cloudflare API Tokens</a>
</div>
@endsection
