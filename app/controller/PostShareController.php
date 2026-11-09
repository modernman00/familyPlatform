<?php

declare(strict_types=1);

namespace App\controller;

use App\controller\BaseController;
use Src\Db;
use Src\functionality\SignIn;

final class PostShareController
{
    /**
     * Public handler for /post/{postNo} links.
     *
     * Invariants:
     * 1. If visitor is authenticated: redirect directly to the post in their family feed.
     * 2. If visitor is not registered/logged in: redirect to /register with pre-populated famCode.
     * 3. Programmatic API JSON callers receive standard JSON payload.
     *
     * @param int|string|null $postNo
     */
    public function handlePostLink(int|string|null $postNo = null): void
    {
        $postNo = (int)($postNo ?: ($_GET['post_no'] ?? 0));
        if ($postNo <= 0) {
            redirect('/');
            return;
        }

        $isJsonRequest = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        try {
            $db = Db::connect2();
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, postFamCode FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$post) {
                if ($isJsonRequest) {
                    http_response_code(404);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['status' => 'error', 'message' => 'Post not found or has been removed']);
                    return;
                }
                redirect('/profilePage');
                return;
            }

            // Check authentication state silently
            $isLoggedIn = false;
            if (!empty($_SESSION['id'])) {
                $isLoggedIn = true;
            } else {
                $tokenName = (string)($_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie');
                if (!empty($_COOKIE[$tokenName])) {
                    $user = SignIn::rehydrateSession($tokenName);
                    if (!empty($user) && !empty($user['id'])) {
                        $_SESSION['id'] = checkInput((string)$user['id']);
                        $isLoggedIn = true;
                    }
                }
            }

            // Authenticated: forward directly to the post on the family feed
            if ($isLoggedIn) {
                if ($isJsonRequest) {
                    \App\controller\members\PostMessage::getSinglePost($postNo);
                    return;
                }
                redirect('/member/profilePage?post_no=' . $postNo . '#post' . $postNo);
                return;
            }

            // Unregistered / Guest: redirect to /register with the author's family code pre-populated
            if ($isJsonRequest) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
                return;
            }

            $rawFamCode = (string)($post['postFamCode'] ?? '');
            $cleanFamCode = preg_replace('/^#+/', '', $rawFamCode);
            $redirectTarget = '/profilePage?post_no=' . $postNo;

            $queryParams = [
                'redirect' => $redirectTarget,
            ];
            if ($cleanFamCode !== '') {
                $queryParams['famCode'] = $cleanFamCode;
            }

            redirect('/register?' . http_build_query($queryParams));
        } catch (\Throwable $e) {
            error_log('[PostShareController] handlePostLink error: ' . $e->getMessage());
            if ($isJsonRequest) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred']);
                return;
            }
            redirect('/profilePage');
        }
    }

    /**
     * Author-Only API to generate or verify a post share link.
     *
     * SEC-1: Strict Author Ownership Gate (Marcus PoC Neutralisation).
     * Only the original author can share this post.
     *
     * @param int|string|null $postNo
     */
    public function sharePostApi(int|string|null $postNo = null): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (empty($_SESSION['id'])) {
            $tokenName = (string)($_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie');
            if (!empty($_COOKIE[$tokenName])) {
                $user = SignIn::rehydrateSession($tokenName);
                if (!empty($user) && !empty($user['id'])) {
                    $_SESSION['id'] = checkInput((string)$user['id']);
                }
            }
        }

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorised: Please sign in to share posts.']);
            return;
        }

        $userId = (string)($_SESSION['id'] ?? '');
        $postNo = (int)($postNo ?: ($_POST['post_no'] ?? $_GET['post_no'] ?? 0));

        if ($postNo <= 0 || $userId === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid post number specified.']);
            return;
        }

        try {
            $db = Db::connect2();
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, postFamCode FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$post) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Post not found or has been removed.']);
                return;
            }

            // Marcus & SecOps Author Ownership Gate
            if ((string)($post['id'] ?? '') !== $userId) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorised: Only the author can share this post.']);
                return;
            }

            $appUrl = rtrim((string)($_ENV['APP_URL'] ?? 'http://localhost'), '/');
            $shareUrl = $appUrl . '/post/' . $postNo;

            echo json_encode([
                'status'  => 'success',
                'message' => 'Post share link generated successfully.',
                'data'    => [
                    'post_no'   => $postNo,
                    'share_url' => $shareUrl,
                    'title'     => ($post['fullName'] ?? 'Family Member') . '\'s Family Memory',
                    'text'      => (string)($post['postMessage'] ?? ''),
                    'fam_code'  => (string)($post['postFamCode'] ?? ''),
                    'is_author' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to share post: ' . $e->getMessage()]);
        }
    }
}
