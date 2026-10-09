<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f7;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #1c1e21;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 40px;
        }
        .header {
            padding: 40px 20px;
            text-align: center;
        }
        .logo-circle {
            width: 60px;
            height: 60px;
            background-color: #1c1e21;
            border-radius: 15px;
            display: inline-block;
            margin-bottom: 20px;
            line-height: 60px;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #1c1e21;
        }
        .subtitle {
            font-size: 16px;
            color: #6c757d;
            margin-top: 8px;
        }
        .body {
            padding: 20px 40px 40px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 25px;
        }
        .content-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .actions {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            margin: 0 8px;
        }
        .btn-primary {
            background-color: #1c1e21;
            color: #ffffff !important;
        }
        .btn-outline {
            background-color: #ffffff;
            color: #1c1e21 !important;
            border: 1px solid #dee2e6;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 13px;
        }
        @media screen and (max-width: 600px) {
            .body {
                padding: 20px;
            }
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    @php
                        $rawBaseUrl = (string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com');
                        $baseUrl = rtrim($rawBaseUrl, '/');

                        // For email assets: email clients fetch images via public proxies.
                        // If the app is running on a local testing domain (.test, localhost, 127.0.0.1), those proxies cannot reach local URLs.
                        $configuredAssetBase = (string)($_ENV['APP_ASSET_URL'] ?? getenv('APP_ASSET_URL') ?: '');
                        if (!empty($configuredAssetBase)) {
                            $assetBase = rtrim($configuredAssetBase, '/');
                        } elseif (preg_match('/(\.test|\.local|localhost|127\.0\.0\.1)/i', $baseUrl)) {
                            $assetBase = 'https://myfamilyplatform.com';
                        } else {
                            $assetBase = $baseUrl;
                        }

                        $rawLogo = (string)($_ENV['APP_LOGO_COLOR'] ?? getenv('APP_LOGO_COLOR') ?: ($_ENV['APP_LOGO'] ?? getenv('APP_LOGO') ?: '/public/assets/images/logo.png'));
                        $rawLogo = trim($rawLogo, "'\"");

                        if (empty($rawLogo) || str_contains($rawLogo, 'favicon')) {
                            $rawLogo = '/public/assets/images/logo.png';
                        }

                        if (!str_starts_with($rawLogo, 'http://') && !str_starts_with($rawLogo, 'https://')) {
                            $logoUrl = $assetBase . '/' . ltrim($rawLogo, '/');
                        } else {
                            $logoUrl = $rawLogo;
                        }

                        $appName = (string)($_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Family Platform');
                    @endphp
                    <a href="{{ $baseUrl }}" target="_blank" style="text-decoration: none; display: inline-block;">
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="200" style="display: block; margin: 0 auto 15px auto; width: 200px; max-width: 220px; height: auto; border: 0; outline: none; text-decoration: none; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 22px; font-weight: 700; color: #1c1e21;" />
                    </a>
                    <h1 class="title">@yield('title', 'Notification')</h1>
                    <p class="subtitle">@yield('subtitle', 'Updates from your family network')</p>
                </td>
            </tr>
            <tr>
                <td class="body">
                    <p class="greeting">@yield('greeting')</p>
                    
                    <div class="content-card">
                        @yield('content')
                    </div>

                    <div class="actions">
                        @yield('actions')
                    </div>

                    @yield('extra_links')
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p style="margin: 0 0 8px 0; font-size: 13px; color: #6c757d;">
                        Questions? Contact Customer Support at <a href="mailto:{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}" style="color: #00bfa5; text-decoration: none;">{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}</a>.
                    </p>
                    @php
                        $recipientEmail = (string)($email ?? ($data['email'] ?? ($data['mail'] ?? '')));

                        $checkFunctional = !empty($isFunctional) || !empty($data['isFunctional']);
                        if (!$checkFunctional) {
                            $pageTitle = (string)($this->yieldContent('title') ?: ($title ?? ($data['title'] ?? '')));
                            $pageSub = (string)($this->yieldContent('subtitle') ?: ($subtitle ?? ($data['subtitle'] ?? '')));
                            $combined = strtoupper($pageTitle . ' ' . $pageSub);
                            if (str_contains($combined, 'PASSWORD') ||
                                str_contains($combined, 'SECURITY') ||
                                str_contains($combined, 'TOKEN') ||
                                str_contains($combined, 'VERIF') ||
                                str_contains($combined, '2FA') ||
                                str_contains($combined, 'ALERT')) {
                                $checkFunctional = true;
                            }
                        }

                        $secretKey = (string)($_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: '');
                        if (empty($secretKey)) {
                            $secretKey = 'SECURE_ENV_MUST_DEFINE_APP_KEY_' . hash('sha256', __FILE__);
                        }
                        $activeToken = (string)($unsubscribeToken ?? ($data['unsubscribeToken'] ?? ''));
                        if (empty($activeToken) && !empty($recipientEmail)) {
                            $activeToken = hash_hmac('sha256', $recipientEmail, $secretKey);
                        }
                        $unsubscribeUrl = $baseUrl . '/email/unsubscribe?email=' . urlencode($recipientEmail) . '&token=' . urlencode($activeToken);
                        $preferencesUrl = $baseUrl . '/settings/notifications';
                    @endphp

                    @if ($checkFunctional)
                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #6c757d;">
                            <strong>Mandatory Service Notification:</strong> This email is essential to fulfill your account requests or security operations. Unsubscribe is not available for transactional security notifications.
                        </p>
                    @else
                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #6c757d;">
                            You received this email because you have an active account with {{ getenv('APP_NAME') ?: 'Family Platform' }}.<br/>
                            <a href="{{ $unsubscribeUrl }}" style="color: #6c757d; text-decoration: underline;">Unsubscribe</a> | <a href="{{ $preferencesUrl }}" style="color: #6c757d; text-decoration: underline;">Manage Notification Preferences</a>
                        </p>
                    @endif
                    <p style="margin: 10px 0 0 0; font-size: 11px; color: #94a3b8; line-height: 1.6; text-align: center;">
                        Internet communications are not secure, and therefore we do not accept legal responsibility for the contents of this message. This message is confidential and intended for the addressee only.<br/>
                        &copy; {{ date('Y') }} {{ getenv('APP_NAME') ?: 'Family Platform' }}. All rights reserved.<br/>
                        <a href="{{ $baseUrl }}/privacy" style="color: #94a3b8; text-decoration: underline;">Privacy Policy</a> | <a href="{{ $baseUrl }}/terms" style="color: #94a3b8; text-decoration: underline;">Terms of Service</a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
