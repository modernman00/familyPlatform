<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pair Google Authenticator | FamilyPlatform Admin</title>
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --danger: #ef4444;
            --success: #10b981;
            --border: #334155;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: rgba(30,41,59,0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
        }
        .brand {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .qr-box {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .qr-box img,
        .qr-box svg {
            width: 180px;
            height: 180px;
            display: block;
        }
        .secret-box {
            background: rgba(15,23,42,0.6);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }
        .secret-box .label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        .secret-key {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: 700;
            color: var(--accent);
            word-break: break-all;
            letter-spacing: 2px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .otp-input {
            width: 100%;
            padding: 14px;
            background: rgba(15,23,42,0.6);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 8px;
            text-align: center;
            outline: none;
            transition: border-color 0.2s;
        }
        .otp-input:focus {
            border-color: var(--accent);
        }
        .otp-input::placeholder {
            color: #334155;
            letter-spacing: 4px;
            font-size: 18px;
        }
        .btn-primary {
            width: 100%;
            padding: 13px;
            background: var(--accent);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 6px;
        }
        .btn-primary:hover {
            background: var(--accent-hover);
        }
        .btn-primary:active {
            transform: scale(0.99);
        }
        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid;
        }
        .alert-danger {
            background: rgba(239,68,68,0.12);
            border-color: rgba(239,68,68,0.35);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16,185,129,0.12);
            border-color: rgba(16,185,129,0.35);
            color: #6ee7b7;
        }
        .step-list {
            list-style: none;
            margin-bottom: 20px;
            counter-reset: steps;
        }
        .step-list li {
            counter-increment: steps;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .step-list li::before {
            content: counter(steps);
            min-width: 20px;
            height: 20px;
            background: rgba(59,130,246,0.2);
            border: 1px solid rgba(59,130,246,0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--accent);
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand">Set Up Google Authenticator</div>
        <p class="subtitle">Scan this QR Code using Google Authenticator, 1Password, or Authy on your mobile device.</p>

        <?php if (!empty($formError)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($formSuccess)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($formSuccess, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <ol class="step-list">
            <li>Download Google Authenticator, 1Password, or Authy on your phone.</li>
            <li>Tap the <strong>+</strong> icon and choose <strong>Scan a QR code</strong>.</li>
            <li>Scan the QR code below, then enter the 6-digit code to confirm.</li>
        </ol>

        <div class="qr-box">
            @if(str_starts_with(trim($qrCodeSvg), '<svg') || str_starts_with(trim($qrCodeSvg), '<?xml'))
                {!! $qrCodeSvg !!}
            @else
                <img src="{{ $qrCodeSvg }}" alt="Google Authenticator QR Code" style="width:180px; height:180px; display:block; margin:0 auto;">
            @endif
        </div>

        <div class="secret-box">
            <div class="label">Manual Entry Key</div>
            <div class="secret-key">{{ $secretKey ?? $secret ?? '' }}</div>
        </div>

        <form method="POST" action="{{ $adminPrefix }}/2fa/setup" autocomplete="off">
            <input type="hidden" name="_token" value="{{ htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8') }}">

            <div class="form-group">
                <label for="otp">Confirm 6-Digit Code</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    class="otp-input"
                    placeholder="000000"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    required
                    autofocus
                    autocomplete="one-time-code"
                >
            </div>

            <button type="submit" class="btn-primary">Confirm &amp; Enable 2FA</button>
        </form>
    </div>
</body>
</html>
