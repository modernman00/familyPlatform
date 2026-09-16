<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | FamilyPlatform Admin</title>
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
            margin-bottom: 28px;
            line-height: 1.6;
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
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            background: rgba(15,23,42,0.6);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: var(--accent);
        }
        .form-group input::placeholder {
            color: #475569;
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--accent);
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
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand">Admin Password Recovery</div>
        <p class="subtitle">Enter your admin email address and we'll send you a secure link to reset your password.</p>

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

        <form method="POST" action="{{ $adminPrefix }}/forgot-password" autocomplete="off">
            <input type="hidden" name="_token" value="{{ htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8') }}">

            <div class="form-group">
                <label for="email">Admin Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="admin@familyplatform.app"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn-primary">Send Reset Link</button>
        </form>

        <a href="{{ $adminPrefix }}/login" class="back-link">&larr; Back to Sign In</a>
    </div>
</body>
</html>
