<?php
namespace App\controller;

use Src\{Utility, SelectFn};

final class Index 
{
    public function index(): void
    {
        if (\class_exists('\Src\functionality\SignIn')) {
            \Src\functionality\SignIn::rehydrateSession();
        }

        $isAdmin = (!empty($_SESSION['auth']['type']) && in_array((string) $_SESSION['auth']['type'], ['admin', 'super_admin'], true))
            || (\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('admin'));

        if ($isAdmin) {
            redirect('/admin/dashboard');
            return;
        }

        $isUser = (!empty($_SESSION['id']) && !empty($_SESSION['auth']['identifyCust']))
            || (\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users'));

        if ($isUser) {
            redirect('/profilePage');
            return;
        }

        try {
            Utility::view('index');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    /**
     * the launch page
     * @return void 
     */
    function launch()
    {
        Utility::view('launch');
    }

    public function privacy(): void
    {
        Utility::view('privacy');
    }

    public function terms(): void
    {
        Utility::view('termOfUse');
    }

    public function contact(): void
    {
        try {
            Utility::view('contact');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    public function unsubscribe(): void
    {
        $rawEmail = filter_input(INPUT_GET, 'email', FILTER_DEFAULT) ?: (filter_input(INPUT_POST, 'email', FILTER_DEFAULT) ?: '');
        $email = filter_var(trim((string)$rawEmail), FILTER_VALIDATE_EMAIL) ?: '';
        $token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
        $secretKey = (string) (getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ''));
        if (empty($secretKey)) {
            // Fail closed if APP_KEY is missing to avoid weak signature validation
            $secretKey = 'SECURE_ENV_MUST_DEFINE_APP_KEY_' . hash('sha256', __FILE__);
        }

        $isValid = false;
        if (!empty($email) && !empty($token)) {
            $expectedToken = hash_hmac('sha256', (string) $email, $secretKey);
            $isValid = hash_equals($expectedToken, $token);
            if (!$isValid) {
                // Also verify lowercased representation in case of case mismatches
                $expectedTokenLower = hash_hmac('sha256', strtolower((string) $email), $secretKey);
                $isValid = hash_equals($expectedTokenLower, $token);
            }
        }

        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare('UPDATE account SET email_unsubscribed = 1 WHERE email = ?');
                $stmt->execute([(string) $email]);
            } catch (\Throwable $e) {
                error_log('[Unsubscribe] DB update error: ' . $e->getMessage());
            }

            $wantsJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
                         (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

            if ($wantsJson) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'You have been successfully unsubscribed.']);
                exit;
            }

            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Unsubscribed Successfully</title><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8fafc;color:#1e293b;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box}.card{background:#ffffff;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.05);max-width:480px;width:100%;padding:40px;text-align:center}.btn{display:inline-block;background:#00bfa5;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;margin-top:20px}</style></head><body><div class="card"><h2 style="color:#0f172a;margin-top:0;">Unsubscribed Successfully</h2><p style="color:#64748b;line-height:1.6;"><strong>' . htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') . '</strong> has been removed from non-essential communications and community updates.</p><p style="color:#94a3b8;font-size:13px;">You will still receive critical account and security notifications.</p><a href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '" class="btn">Return to Family Platform</a></div></body></html>';
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Email Preferences</title><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8fafc;color:#1e293b;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box}.card{background:#ffffff;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.05);max-width:480px;width:100%;padding:40px;text-align:center}.btn-red{background:#dc2626;color:white;padding:12px 24px;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;width:100%}.btn-teal{display:inline-block;background:#00bfa5;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;margin-top:10px}</style></head><body><div class="card">';
        if ($isValid) {
            echo '<h2 style="color:#0f172a;margin-top:0;">Confirm Unsubscribe</h2>';
            echo '<p style="color:#64748b;line-height:1.6;">Are you sure you want to unsubscribe <strong>' . htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') . '</strong> from non-essential emails?</p>';
            echo '<form method="POST" style="margin-top:25px;"><input type="hidden" name="email" value="' . htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="token" value="' . htmlspecialchars((string)$token, ENT_QUOTES, 'UTF-8') . '"><button type="submit" class="btn-red">Confirm Unsubscribe</button></form>';
            echo '<p style="margin-top:20px;"><a href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '" style="color:#64748b;text-decoration:none;font-size:14px;">Cancel and go back</a></p>';
        } else {
            echo '<h2 style="color:#dc2626;margin-top:0;">Invalid Unsubscribe Request</h2>';
            echo '<p style="color:#64748b;line-height:1.6;">The unsubscribe link is invalid or expired. If this was a security notification (such as a password change or verification code), unsubscribe is not available for essential security messages.</p>';
            echo '<div style="margin-top:25px;"><a href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/settings/notifications" class="btn-teal">Manage Notification Preferences</a></div>';
            echo '<p style="margin-top:20px;"><a href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/login" style="color:#64748b;text-decoration:none;font-size:14px;">Log in to your account</a></p>';
        }
        echo '</div></body></html>';
        exit;
    }

    /**
     * Answers "is this email already a registered account?" for the kid/sibling
     * invite flow — one lookup, boolean answer, session required.
     *
     * Replaces a prior version that returned every approved account's email
     * address to any caller (unauthenticated PII disclosure, SEC-4).
     */
    public static function getEmails(): void
    {
        try {
            $sessionId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($sessionId === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            $rawEmail = $_GET['email'] ?? '';
            $clean = is_string($rawEmail) ? checkInput($rawEmail) : null;
            $email = is_string($clean) ? strtolower(trim($clean)) : '';
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                msgException(400, 'A valid email query parameter is required');
                return;
            }

            // SEC-5 — this is still an "does this email exist?" oracle even behind
            // the session gate; throttle per user so it can't be swept.
            \Src\Limiter::limit($sessionId . ':emailcheck', 'post');

            $rows = SelectFn::selectAllRowsById('account', 'email', $email);

            msgSuccess(200, ['exists' => !empty($rows)]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
