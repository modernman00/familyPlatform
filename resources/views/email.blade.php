<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f8fafc; font-family: 'Inter', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse !important; }

        /* Mobile styles */
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; padding: 10px !important; }
            .content-card { padding: 20px !important; border-radius: 12px !important; }
            .header-logo { max-width: 150px !important; }
            .h1 { font-size: 24px !important; }
            .p { font-size: 16px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #00bfa5 0%, #004182 100%); padding: 36px 20px;">
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
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="220" class="header-logo" style="display: block; width: 220px; max-width: 240px; height: auto; border: 0; outline: none; text-decoration: none; font-family: 'Inter', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; color: #ffffff;" />
                            </a>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; color: #1e293b;" class="content-card">
                            
                            <h1 class="h1" style="margin: 0 0 20px 0; font-size: 28px; font-weight: 700; color: #0f172a; text-align: center; letter-spacing: -0.5px;">
                                @yield('subject')
                            </h1>

                            <!-- Divider -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 30px;">
                                        <div style="height: 3px; width: 40px; background-color: #00bfa5; border-radius: 2px;"></div>
                                    </td>
                                </tr>
                            </table>

                            <div class="p" style="font-size: 16px; line-height: 1.6; color: #334155;">
                                @yield('content')
                            </div>

                        </td>
                    </tr>

                    <!-- Footer Content Inside Card -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <p style="margin: 30px 0 0 0; font-size: 16px; line-height: 1.6; color: #475569; font-weight: 500;">
                                Warm regards,<br/>
                                <span style="color: #00bfa5; font-weight: 600;">The Membership Team</span>
                            </p>
                        </td>
                    </tr>
                    
                </table>

                <!-- Outside Footer & Legal Small Print -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container" style="margin-top: 30px;">
                    <tr>
                        <td align="center" style="padding: 0 20px;">
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #64748b; line-height: 1.5; text-align: center;">
                                If you have any questions regarding your account, please contact Customer Services at <a href="mailto:{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}" style="color: #00bfa5; text-decoration: none;">{{ getenv('APP_EMAIL') ?: 'support@myfamilyplatform.com' }}</a>.
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
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #94a3b8; line-height: 1.5; text-align: center;">
                                    <strong>Mandatory Service Notice:</strong> This is a transactional notification regarding your account integrity or security. Because this email is necessary to deliver your requested service, you cannot opt out of critical security messages.
                                </p>
                            @else
                                <p style="margin: 0 0 10px 0; font-size: 12px; color: #94a3b8; line-height: 1.5; text-align: center;">
                                    You received this message because you opted in to activity and community updates from {{ getenv('APP_NAME') ?: 'Family Platform' }}.<br/>
                                    If you no longer wish to receive non-essential updates, you can <a href="{{ $unsubscribeUrl }}" style="color: #64748b; text-decoration: underline;">Unsubscribe from these emails</a> or <a href="{{ $preferencesUrl }}" style="color: #64748b; text-decoration: underline;">Manage Notification Preferences</a>.
                                </p>
                            @endif

                            @php
                                $companyName = getenv('COMPANY_NAME') ?: (getenv('APP_NAME') ?: 'Family Platform') . ' Ltd';
                            @endphp
                            <p style="margin: 10px 0 0 0; font-size: 11px; color: #cbd5e1; line-height: 1.4; text-align: center;">
                                &copy; {{ date('Y') }} {{ $companyName }}.<br/>
                                <a href="{{ $baseUrl }}/privacy" style="color: #cbd5e1; text-decoration: underline;">Privacy Policy</a> | <a href="{{ $baseUrl }}/terms" style="color: #cbd5e1; text-decoration: underline;">Terms of Service</a>
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>