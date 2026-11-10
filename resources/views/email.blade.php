<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <!--[if !mso]--><!-- -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!--<![endif]-->
    <title>Family Platform Communication</title>
    <style type="text/css">
        /* Client-specific resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }

        /* Reset styles */
        img { border: 0; outline: none; text-decoration: none; max-width: 100%; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f4efe9; font-family: 'Inter', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse !important; }
        a { color: #a31621; }

        /* Mobile styles */
        @media screen and (max-width: 600px) {
            .email-outer { padding: 16px 0 !important; }
            .email-container { width: 100% !important; }
            .email-card { border-radius: 0 !important; border-left: 0 !important; border-right: 0 !important; }
            .content-card { padding: 28px 20px 8px 20px !important; }
            .signoff-cell { padding: 0 20px 28px 20px !important; }
            .header-cell { padding: 22px 20px !important; }
            .header-logo { width: 150px !important; }
            .h1 { font-size: 24px !important; line-height: 1.25 !important; }
            .p { font-size: 16px !important; }
            .btn-block a { display: block !important; }
        }
    </style>
</head></head>
<body style="margin: 0; padding: 0; background-color: #f4efe9; font-family: 'Inter', Helvetica, Arial, sans-serif;">
    @php
        // Hidden inbox preview line (falls back to nothing when a template does not define one)
        $preheaderText = is_callable([$this, 'yieldContent']) ? trim((string)$this->yieldContent('preheader')) : '';
        // Optional on-card headline override; defaults to the subject line
        $headlineText = is_callable([$this, 'yieldContent']) ? trim((string)$this->yieldContent('headline')) : '';
        $eyebrowText = is_callable([$this, 'yieldContent']) ? trim((string)$this->yieldContent('eyebrow')) : '';
        $signoffText = is_callable([$this, 'yieldContent']) ? trim((string)$this->yieldContent('signoff')) : '';
    @endphp
    @if ($preheaderText !== '')
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #f4efe9;">{{ $preheaderText }}&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;</div>
    @endif
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-outer" style="background-color: #f4efe9; padding: 40px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container email-card" style="background-color: #ffffff; border: 1px solid #e9e0d5; border-radius: 18px; overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" class="header-cell" bgcolor="#a31621" style="background-color: #a31621; background-image: linear-gradient(135deg, #a31621 0%, #7a0e16 100%); padding: 26px 20px;">
                            @php
                                $rawBaseUrl = (string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com');
                                $baseUrl = rtrim($rawBaseUrl, '/');

                                // For email assets: email clients (Gmail, Apple Mail, Outlook) fetch images via public proxies.
                                // If the app is running on a local testing domain (.test, localhost, 127.0.0.1), those proxies cannot reach local URLs.
                                // Use APP_ASSET_URL if explicitly set, or fall back to public production domain if asset base is a local host.
                                $configuredAssetBase = (string)($_ENV['APP_ASSET_URL'] ?? getenv('APP_ASSET_URL') ?: '');
                                if (!empty($configuredAssetBase)) {
                                    $assetBase = rtrim($configuredAssetBase, '/');
                                } elseif (preg_match('/(\.test|\.local|localhost|127\.0\.0\.1)/i', $baseUrl)) {
                                    $assetBase = 'https://myfamilyplatform.com';
                                } else {
                                    $assetBase = $baseUrl;
                                }

                                $rawLogo = (string)($_ENV['APP_LOGO_EMAIL'] ?? getenv('APP_LOGO_EMAIL') ?: ($_ENV['APP_LOGO'] ?? getenv('APP_LOGO') ?: '/public/assets/images/logo-white.png'));
                                $rawLogo = trim($rawLogo, "'\"");

                                if (empty($rawLogo) || str_contains($rawLogo, 'favicon') || str_contains($rawLogo, '/img/logo/')) {
                                    $rawLogo = '/public/assets/images/logo-white.png';
                                }

                                if (!str_starts_with($rawLogo, 'http://') && !str_starts_with($rawLogo, 'https://')) {
                                    $logoUrl = $assetBase . '/' . ltrim($rawLogo, '/');
                                } else {
                                    $logoUrl = $rawLogo;
                                }

                                $appName = (string)($_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Family Platform');
                            @endphp
                            <a href="{{ $baseUrl }}" target="_blank" style="text-decoration: none; display: inline-block;">
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="170" class="header-logo" style="display: block; width: 170px; max-width: 170px; height: auto; border: 0; outline: none; text-decoration: none; font-family: 'Inter', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; color: #ffffff;" />
                            </a>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 44px 8px 44px; color: #292524;" class="content-card">

                            @if ($eyebrowText !== '')
                            <p style="margin: 0 0 10px 0; font-size: 12px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #a31621;">{{ $eyebrowText }}</p>
                            @endif

                            <h1 class="h1" style="margin: 0 0 24px 0; font-family: Georgia, 'Times New Roman', serif; font-size: 28px; line-height: 1.25; font-weight: 700; color: #1c1917; letter-spacing: -0.3px;">
                                @if ($headlineText !== '')
                                    {!! $headlineText !!}
                                @else
                                    @yield('subject')
                                @endif
                            </h1>

                            <div class="p" style="font-size: 16px; line-height: 1.65; color: #44403c;">
                                @yield('content')
                            </div>

                        </td>
                    </tr>

                    <!-- Sign-off Inside Card -->
                    <tr>
                        <td class="signoff-cell" style="padding: 0 44px 40px 44px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding-top: 24px; border-top: 1px solid #f0e9e1; font-size: 15px; line-height: 1.6; color: #57534e;">
                                        With warmth,<br/>
                                        <span style="color: #1c1917; font-weight: 600;">{{ $signoffText !== '' ? $signoffText : 'The Family Platform Team' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <!-- Outside Footer & Legal Small Print -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container" style="margin-top: 24px;">
                    <tr>
                        <td align="center" style="padding: 0 20px;">
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #78716c; line-height: 1.5; text-align: center;">
                                Questions? Reach us at <a href="mailto:{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}" style="color: #a31621; text-decoration: none;">{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}</a>.
                            </p>

                            @php
                                $recipientEmail = (string)($email ?? ($data['email'] ?? ($data['mail'] ?? '')));

                                // Auto-detect functional/transactional notifications (security alerts, passwords, verification codes)
                                $checkFunctional = !empty($isFunctional) || !empty($data['isFunctional']);
                                if (!$checkFunctional) {
                                    $yieldSub = is_callable([$this, 'yieldContent']) ? (string)$this->yieldContent('subject') : '';
                                    $yieldTitle = is_callable([$this, 'yieldContent']) ? (string)$this->yieldContent('title') : '';
                                    $pageSubject = $yieldSub ?: ($yieldTitle ?: (string)($subject ?? ($data['subject'] ?? '')));
                                    $subjectUpper = strtoupper($pageSubject);
                                    if (str_contains($subjectUpper, 'PASSWORD') ||
                                        str_contains($subjectUpper, 'SECURITY') ||
                                        str_contains($subjectUpper, 'TOKEN') ||
                                        str_contains($subjectUpper, 'VERIF') ||
                                        str_contains($subjectUpper, '2FA') ||
                                        str_contains($subjectUpper, 'ALERT')) {
                                        $checkFunctional = true;
                                    }
                                }

                                // Cryptographic HMAC-SHA256 signature generation for unsubscribe URL
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
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #a8a29e; line-height: 1.5; text-align: center;">
                                    <strong>Mandatory Service Notice:</strong> This is a transactional notification regarding your account integrity or security. Because this email is necessary to deliver your requested service, you cannot opt out of critical security messages.
                                </p>
                            @else
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #a8a29e; line-height: 1.5; text-align: center;">
                                    You received this message because you opted in to activity and community updates from {{ getenv('APP_NAME') ?: 'Family Platform' }}.<br/>
                                    If you no longer wish to receive non-essential updates, you can <a href="{{ $unsubscribeUrl }}" style="color: #78716c; text-decoration: underline;">Unsubscribe from these emails</a> or <a href="{{ $preferencesUrl }}" style="color: #78716c; text-decoration: underline;">Manage Notification Preferences</a>.
                                </p>
                            @endif

                            <p style="margin: 10px 0 0 0; font-size: 11px; color: #a8a29e; line-height: 1.6; text-align: center;">
                                Internet communications are not secure, and therefore we do not accept legal responsibility for the contents of this message. This message is confidential and intended for the addressee only.<br/>
                                &copy; {{ date('Y') }} {{ getenv('APP_NAME') ?: 'Family Platform' }}. All rights reserved.<br/>
                                <a href="{{ $baseUrl }}/privacy" style="color: #a8a29e; text-decoration: underline;">Privacy Policy</a> | <a href="{{ $baseUrl }}/terms" style="color: #a8a29e; text-decoration: underline;">Terms of Service</a>
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>