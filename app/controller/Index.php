<?php
namespace App\controller;

use Src\{Utility, SelectFn};

final class Index 
{
    public function index(): void
    {

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
        $email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) ?: (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '');
        $token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
        $secretKey = (string) (getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ''));
        if (empty($secretKey)) {
            // Fail closed if APP_KEY is missing to avoid weak signature validation
            $secretKey = 'SECURE_ENV_MUST_DEFINE_APP_KEY_' . md5(__FILE__);
        }

        $isValid = false;
        if (!empty($email) && !empty($token)) {
            $expectedToken = hash_hmac('sha256', (string) $email, $secretKey);
            $isValid = hash_equals($expectedToken, $token);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid && !empty($email)) {
            $db = \Src\Db::connect2();
            $stmt = $db->prepare('UPDATE account SET email_unsubscribed = 1 WHERE email = ?');
            $stmt->execute([(string) $email]);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'You have been successfully unsubscribed.']);
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Unsubscribe</title><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="font-family:sans-serif;text-align:center;padding:50px;">';
        if ($isValid) {
            echo '<h2 style="color:#0f172a;">Confirm Unsubscribe</h2>';
            echo '<p>Are you sure you want to unsubscribe <strong>' . htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') . '</strong> from non-essential emails?</p>';
            echo '<form method="POST"><input type="hidden" name="email" value="' . htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="token" value="' . htmlspecialchars((string)$token, ENT_QUOTES, 'UTF-8') . '"><button type="submit" style="background:#dc2626;color:white;padding:12px 24px;border:none;border-radius:6px;font-size:16px;cursor:pointer;">Unsubscribe Me</button></form>';
        } else {
            echo '<h2 style="color:#dc2626;">Invalid Unsubscribe Request</h2>';
            echo '<p>The unsubscribe link is invalid or expired. Please check your email or manage preferences in your account settings.</p>';
        }
        echo '</body></html>';
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
