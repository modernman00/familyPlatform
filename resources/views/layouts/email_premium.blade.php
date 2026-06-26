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
                    <div class="logo-circle">C</div>
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
                    <p>You received this email because you have an account with Family Platform.</p>
                    <p>If you didn't request this, you can safely ignore it.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
