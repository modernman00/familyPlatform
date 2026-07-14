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
                        <td align="center" style="background: linear-gradient(135deg, #0a66c2 0%, #004182 100%); padding: 40px 20px;">
                            <img src="{{ $_ENV['APP_LOGO'] ?? '' }}" alt="Family Platform Logo" style="display: block; height: 50px; width: auto; filter: brightness(0) invert(1);" />
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
                                        <div style="height: 3px; width: 40px; background-color: #0a66c2; border-radius: 2px;"></div>
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
                                <span style="color: #0a66c2; font-weight: 600;">The Membership Team</span>
                            </p>
                        </td>
                    </tr>
                    
                </table>

                <!-- Outside Footer -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="email-container" style="margin-top: 30px;">
                    <tr>
                        <td align="center" style="padding: 0 20px;">
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #64748b; line-height: 1.5; text-align: center;">
                                If you have any questions regarding your account, please contact our Customer Services Team at <strong style="color: #475569;">{{ getenv('BIZ_NO') }}</strong>.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5; text-align: center;">
                                This message is confidential and intended solely for the addressee. We will never ask you to supply sensitive security details via email. 
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>