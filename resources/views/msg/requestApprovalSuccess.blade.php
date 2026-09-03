<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request {{ ucfirst($app['decision'] ?? 'Approved') }} — Family Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0f7fa 0%, #e8f5e9 40%, #f3e5f5 100%);
            padding: 24px;
            overflow: hidden;
        }

        /* Floating particles */
        .particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            opacity: 0;
            animation: floatUp 4s ease-out forwards;
        }

        @keyframes floatUp {
            0%   { opacity: 0; transform: translateY(100vh) scale(0); }
            20%  { opacity: 0.7; }
            100% { opacity: 0; transform: translateY(-20vh) scale(1); }
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.08),
                0 4px 16px rgba(0, 0, 0, 0.04);
            max-width: 480px;
            width: 100%;
            text-align: center;
            padding: 48px 40px;
            animation: cardSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes cardSlideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Success ring */
        .success-ring {
            width: 100px;
            height: 100px;
            margin: 0 auto 28px auto;
            position: relative;
        }

        .success-ring svg {
            width: 100%;
            height: 100%;
        }

        .success-ring .circle {
            fill: none;
            stroke: #00bfa5;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            animation: drawCircle 0.6s 0.3s ease-out forwards;
            transform-origin: center;
        }

        .success-ring .checkmark {
            fill: none;
            stroke: #00bfa5;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: drawCheck 0.4s 0.8s ease-out forwards;
        }

        @keyframes drawCircle {
            to { stroke-dashoffset: 0; }
        }

        @keyframes drawCheck {
            to { stroke-dashoffset: 0; }
        }

        .heading {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            animation: fadeIn 0.5s 0.9s ease forwards;
            opacity: 0;
        }

        .subheading {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 32px;
            animation: fadeIn 0.5s 1s ease forwards;
            opacity: 0;
        }

        .subheading strong {
            color: #00bfa5;
            font-weight: 600;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Connection visual */
        .connection-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 32px;
            animation: fadeIn 0.5s 1.1s ease forwards;
            opacity: 0;
        }

        .avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bfa5, #004182);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 191, 165, 0.3);
        }

        .connection-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bfa5, #26c6a9);
            color: #fff;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0, 191, 165, 0.25);
            animation: pulse 2s infinite 1.5s;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.15); }
        }

        /* CTA Button */
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 36px;
            background: linear-gradient(135deg, #00bfa5 0%, #004182 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 24px rgba(0, 65, 130, 0.25);
            animation: fadeIn 0.5s 1.3s ease forwards;
            opacity: 0;
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 65, 130, 0.35);
        }

        .cta-btn:active {
            transform: translateY(0);
        }

        .cta-btn i {
            font-size: 18px;
        }

        .footer-note {
            margin-top: 24px;
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.5;
            animation: fadeIn 0.5s 1.5s ease forwards;
            opacity: 0;
        }

        @media (max-width: 520px) {
            .card { padding: 36px 24px; }
            .heading { font-size: 22px; }
            .subheading { font-size: 14px; }
            .avatar-circle { width: 48px; height: 48px; font-size: 18px; }
        }
    </style>
</head>
<body>
    @php
        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://familyplatform.test'), '/');
        $firstName = $app['firstName'] ?? 'there';
        $requesterName = $app['requesterName'] ?? 'your family member';
        $decision = $app['decision'] ?? 'approved';
        $isApproved = $decision === 'approved';
        $initials = strtoupper(substr($firstName, 0, 1));
        $requesterInitials = strtoupper(substr(explode(' ', $requesterName)[0] ?? 'F', 0, 1));
    @endphp

    <div class="card">
        @if($isApproved)
            {{-- Animated success checkmark --}}
            <div class="success-ring">
                <svg viewBox="0 0 100 100">
                    <circle class="circle" cx="50" cy="50" r="45" />
                    <polyline class="checkmark" points="30,52 44,66 70,36" />
                </svg>
            </div>

            <h1 class="heading">You're Now Connected!</h1>
            <p class="subheading">
                You've successfully accepted the kinship request from <strong>{{ $requesterName }}</strong>. 
                Your family network just got stronger. 🎉
            </p>

            {{-- Connection visual --}}
            <div class="connection-visual">
                <div class="avatar-circle">{{ $initials }}</div>
                <div class="connection-icon">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <div class="avatar-circle" style="background: linear-gradient(135deg, #004182, #1a6fcc);">{{ $requesterInitials }}</div>
            </div>
        @else
            <div class="success-ring">
                <svg viewBox="0 0 100 100">
                    <circle class="circle" cx="50" cy="50" r="45" style="stroke: #94a3b8;" />
                    <line x1="35" y1="35" x2="65" y2="65" stroke="#94a3b8" stroke-width="4" stroke-linecap="round"
                          style="stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.4s 0.8s ease-out forwards;" />
                    <line x1="65" y1="35" x2="35" y2="65" stroke="#94a3b8" stroke-width="4" stroke-linecap="round"
                          style="stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.4s 0.9s ease-out forwards;" />
                </svg>
            </div>

            <h1 class="heading">Request Declined</h1>
            <p class="subheading">
                You've declined the kinship request from <strong>{{ $requesterName }}</strong>. 
                No worries — you can always connect later from the app.
            </p>
        @endif

        <a href="{{ $baseUrl }}/login" class="cta-btn">
            <i class="bi bi-box-arrow-in-right"></i>
            Sign In to Your Family Network
        </a>

        <p class="footer-note">
            See all your connections, share updates, and stay close to the ones who matter most.
        </p>
    </div>

    @if($isApproved)
    <script>
        // Celebratory confetti particles
        (function() {
            const colors = ['#00bfa5', '#004182', '#f59e0b', '#ec4899', '#8b5cf6', '#10b981'];
            const body = document.body;
            for (let i = 0; i < 30; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 10 + 5;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
                p.style.left = Math.random() * 100 + 'vw';
                p.style.background = colors[Math.floor(Math.random() * colors.length)];
                p.style.animationDelay = (Math.random() * 2) + 's';
                p.style.animationDuration = (Math.random() * 2 + 3) + 's';
                body.appendChild(p);
            }
        })();
    </script>
    @endif
</body>
</html>
