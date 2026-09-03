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
