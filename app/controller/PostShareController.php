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
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, postFamCode, profileImg, post_img0, post_img1, post_img2, voice_capsule_url, date_created FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
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

            // Prepare rich post metadata for social crawlers (WhatsApp, X, Facebook, LinkedIn, etc.)
            $authorName = trim((string)($post['fullName'] ?? 'A Family Member'));
            $rawFamCode = (string)($post['postFamCode'] ?? '');
            $cleanFamCode = preg_replace('/^#+/', '', $rawFamCode);
            $rawMessage = (string)($post['postMessage'] ?? '');

            // Strip style tokens, internal filenames, media paths, and resource URLs
            $cleanCaption = trim(preg_replace('/https?:\/\/[^\s]+|\/resources\/(?:videos|images|profile)\/[^\s]+|post_vid_[^\s]+|[a-f0-9_]+_Sho_[^\s]+|\.(?:webm|mp4|mov|jpg|jpeg|png|gif|webp|avif)(?:#[^\s]*)?|\[style:[a-z0-9_]+\]/i', '', $rawMessage));
            $cleanCaption = strip_tags(html_entity_decode($cleanCaption, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $cleanCaption = trim(preg_replace('/\s+/', ' ', $cleanCaption));

            // Detect media type
            $hasVideo = (bool)preg_match('/(\/resources\/videos\/|\.webm|\.mp4|\.mov|post_vid_)/i', $rawMessage);
            $hasPhoto = !empty($post['post_img0']);
            $hasVoice = !empty($post['voice_capsule_url']);

            // Compose human headline & description
            $mediaLabel = 'Family Memory';
            if ($cleanCaption !== '') {
                $snippet = mb_strlen($cleanCaption) > 65 ? mb_substr($cleanCaption, 0, 62) . '...' : $cleanCaption;
                $ogTitle = "“{$snippet}” — {$authorName}";
                $ogDescription = "{$authorName} shared a family moment on FamilyPlatform. Connect with the {$cleanFamCode} family circle to view, comment, and celebrate together.";
            } elseif ($hasVideo) {
                $ogTitle = "{$authorName} shared a family video memory";
                $ogDescription = "Watch {$authorName}’s family video memory on FamilyPlatform. Connect with the {$cleanFamCode} family circle to cherish moments together.";
                $mediaLabel = 'Video Memory';
            } elseif ($hasPhoto) {
                $ogTitle = "{$authorName} shared a family photo memory";
                $ogDescription = "View {$authorName}’s family photo on FamilyPlatform. Connect with the {$cleanFamCode} family circle to cherish moments together.";
                $mediaLabel = 'Photo Memory';
            } elseif ($hasVoice) {
                $ogTitle = "{$authorName} shared a family voice capsule";
                $ogDescription = "Listen to {$authorName}’s voice recording on FamilyPlatform. Connect with the {$cleanFamCode} family circle.";
                $mediaLabel = 'Voice Capsule';
            } else {
                $ogTitle = "{$authorName} shared a family memory";
                $ogDescription = "Cherish and preserve memories with the {$cleanFamCode} family on FamilyPlatform.";
            }

            // Resolve absolute preview image URL
            $appUrl = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
            if ($appUrl === '') {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443 ? 'https://' : 'http://';
                $appUrl = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            }

            if (!empty($post['post_img0'])) {
                $ogImage = $appUrl . '/resources/images/post/' . ltrim($post['post_img0'], '/');
            } elseif (!empty($post['profileImg'])) {
                $ogImage = $appUrl . '/resources/images/profile/' . ltrim($post['profileImg'], '/');
            } else {
                $ogImage = $appUrl . '/public/img/og-invite.jpg';
            }

            $canonicalUrl = $appUrl . '/post/' . $postNo;
            $redirectTarget = '/profilePage?post_no=' . $postNo;
            $queryParams = ['redirect' => $redirectTarget];
            if ($cleanFamCode !== '') {
                $queryParams['famCode'] = $cleanFamCode;
            }
            $targetRedirectUrl = '/register?' . http_build_query($queryParams);

            // Handle Social Crawlers (WhatsApp, Facebook, Twitter, Telegram, LinkedIn, etc.)
            // Or explicit crawler preview debug query (?crawler=1 or ?preview=1)
            $isCrawler = self::isSocialCrawler() || isset($_GET['crawler']) || isset($_GET['preview']);
            if ($isCrawler) {
                if ($isJsonRequest) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'success',
                        'ogTitle' => $ogTitle,
                        'ogDescription' => $ogDescription,
                        'ogImage' => $ogImage,
                        'canonicalUrl' => $canonicalUrl,
                    ]);
                    return;
                }

                view('public/postShareLanding', [
                    'ogTitle' => $ogTitle,
                    'ogDescription' => $ogDescription,
                    'ogImage' => $ogImage,
                    'ogType' => $hasVideo ? 'video.other' : 'article',
                    'canonicalUrl' => $canonicalUrl,
                    'cleanFamCode' => $cleanFamCode,
                    'mediaLabel' => $mediaLabel,
                    'shouldRedirect' => false,
                    'targetRedirectUrl' => $targetRedirectUrl,
                    'redirectTarget' => $redirectTarget,
                ]);
                return;
            }

            // Check authentication state silently for human visitors
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

            // Authenticated human: forward directly to the post on the family feed
            if ($isLoggedIn) {
                if ($isJsonRequest) {
                    \App\controller\members\PostMessage::getSinglePost($postNo);
                    return;
                }
                redirect('/member/profilePage?post_no=' . $postNo . '#post' . $postNo);
                return;
            }

            // Unregistered / Guest human: redirect to /register with the author's family code pre-populated
            if ($isJsonRequest) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
                return;
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

    /**
     * Detects automated social media scrapers / link-preview bots.
     */
    public static function isSocialCrawler(?string $userAgent = null): bool
    {
        $ua = strtolower($userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($ua === '') {
            return false;
        }

        $crawlers = [
            'whatsapp',
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'telegrambot',
            'linkedinbot',
            'slackbot',
            'discordbot',
            'applebot',
            'pinterest',
            'skypeuripreview',
            'googlebot',
            'bingbot',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'vkshare',
            'w3c_validator',
            'redditbot',
            'bitlybot',
            'tumblr',
            'quora link preview',
            'yahoo! slurp',
        ];

        foreach ($crawlers as $crawler) {
            if (str_contains($ua, $crawler)) {
                return true;
            }
        }

        return false;
    }
}
