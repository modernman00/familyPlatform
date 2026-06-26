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
        .request-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            float: left;
            margin-right: 20px;
        }
        .requester-info {
            overflow: hidden;
        }
        .requester-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #1c1e21;
        }
        .mutual-friends {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 12px;
        }
        .bio {
            font-size: 14px;
            line-height: 1.5;
            color: #4b4b4b;
            margin: 0;
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
        .profile-link {
            color: #1c1e21;
            text-decoration: underline;
            font-weight: 500;
        }
        @media screen and (max-width: 600px) {
            .body {
                padding: 20px;
            }
            .profile-img {
                float: none;
                margin: 0 auto 15px;
                display: block;
            }
            .requester-info {
                text-align: center;
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
                    <h1 class="title">New Friend Request</h1>
                    <p class="subtitle">Someone wants to connect with you</p>
                </td>
            </tr>
            <tr>
                <td class="body">
                    <p class="greeting">Hi {{ $data['approverFirstName'] }},</p>
                    
                    <div class="request-card">
                        <img src="{{ getenv('MIX_ROUTE_PICS') }}{{ $data['profileImg'] }}" alt="{{ $data['firstName'] }}" class="profile-img">
                        <div class="requester-info">
                            <h2 class="requester-name">{{ $data['firstName'] }} {{ $data['lastName'] }}</h2>
                            <p class="mutual-friends">Wants to join your family network</p>
                            <p class="bio">
                                {{ $data['bio'] ?? 'Hello! I would like to connect with you on the family platform.' }}
                            </p>
                        </div>
                    </div>

                    <div class="actions">
                        <a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/50/{{ $data['famCode'] }}/email" class="btn btn-primary">Accept Request</a>
                        <a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['id'] }}/{{ $data['approverId'] }}/10/request/email" class="btn btn-outline">Decline</a>
                    </div>

                    <p style="text-align: center; margin-top: 30px; font-size: 14px; color: #6c757d;">
                        Or <a href="{{ getenv('MIX_APP_URL2') }}member/seeProfile/{{ $data['id'] }}" class="profile-link">view their profile</a> to learn more
                    </p>
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
