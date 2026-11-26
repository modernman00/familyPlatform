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
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, postFamCode, profileImg, post_img0, post_img1, post_img2, voice_capsule_url, media_meta, date_created FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
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

            $isShareable = \App\controller\members\PostMessage::isPostShareable($post);
            $authorId = (string)($post['id'] ?? '');

            // Prepare rich post metadata for social crawlers (WhatsApp, X, Facebook, LinkedIn, etc.)
            // Privacy Invariant: External preview strings resolve via UserHandleService (defaults to First Name + Initial or @handle)
            $authorName = \App\services\UserHandleService::resolveExternalAuthorName($db, $authorId, (string)($post['fullName'] ?? 'A Family Member'));
            $rawFamCode = (string)($post['postFamCode'] ?? '');
            $cleanFamCode = preg_replace('/^#+/', '', $rawFamCode);
            $rawMessage = (string)($post['postMessage'] ?? '');

            // Strip style tokens, internal filenames, media paths, and resource URLs
            $cleanCaption = trim(preg_replace('/https?:\/\/[^\s]+|\/resources\/(?:videos|images|profile)\/[^\s]+|post_vid_[^\s]+|[a-f0-9_]+_Sho_[^\s]+|\.(?:webm|mp4|mov|jpg|jpeg|png|gif|webp|avif)(?:#[^\s]*)?|\[style:[a-z0-9_]+\]/i', '', $rawMessage));
            $cleanCaption = strip_tags(html_entity_decode($cleanCaption, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $cleanCaption = trim(preg_replace('/\s+/', ' ', $cleanCaption));

            // Detect media type (including YouTube, Google Photos, OneDrive, Facebook, Vimeo, TikTok, and local uploads)
            $hasVideo = (bool)preg_match('/(\/resources\/videos\/|\.webm|\.mp4|\.mov|post_vid_|youtube\.com|youtu\.be|vimeo\.com|tiktok\.com|fb\.watch|facebook\.com\/(?:watch|reel|[^\/]+\/videos)|onedrive\.live\.com|1drv\.ms|drive\.google\.com)/i', $rawMessage)
                || \App\classes\VideoParser::extractVideoFromText($rawMessage) !== null;
            $hasPhoto = !empty($post['post_img0']);
            $hasVoice = !empty($post['voice_capsule_url']);

            // Compose rich, app-promoting headline & description
            $mediaLabel = 'Post';
            if ($hasVideo) {
                $mediaLabel = 'Video';
            } elseif ($hasPhoto) {
                $mediaLabel = 'Photo';
            } elseif ($hasVoice) {
                $mediaLabel = 'Voice Note';
            }
            $kind = $hasVideo ? 'video' : ($hasPhoto ? 'photo' : ($hasVoice ? 'voice note' : 'post'));

            if ($cleanCaption !== '') {
                $snippet = mb_strlen($cleanCaption) > 65 ? mb_substr($cleanCaption, 0, 62) . '…' : $cleanCaption;
                $longSnippet = mb_strlen($cleanCaption) > 120 ? rtrim(mb_substr($cleanCaption, 0, 117)) . '…' : $cleanCaption;
                $ogTitle = "“{$snippet}” — {$authorName} on FamilyPlatform";
                $ogDescription = "“{$longSnippet}” · Join {$authorName}’s private family circle on FamilyPlatform — your ad-free family network & living archive.";
            } else {
                $ogTitle = "{$authorName} shared a {$kind} on FamilyPlatform";
                $ogDescription = "View {$authorName}’s {$kind} on FamilyPlatform — the private, ad-free family network & living archive for photos, videos, and milestones.";
            }

            // Resolve absolute preview image URL
            $appUrl = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
            if ($appUrl === '') {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443 ? 'https://' : 'http://';
                $appUrl = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            }

            $authorId = (string)($post['id'] ?? '');

            if (!$isShareable) {
                // Fanciful golden heritage card fallback when sharing is disabled outside family
                $ogImage = $appUrl . '/public/img/og-invite.jpg';
                $ogTitle = 'Family Memory on FamilyPlatform';
                $ogDescription = 'Sign in to FamilyPlatform to view moments shared with your family circle.';
                $mediaLabel = 'Family Memory';
                $hasVideo = false;
            } else {
                // Designed preview card (photo excerpt, quote, author & wax seal in FamilyPlatform couture style)
                $ogImage = $appUrl . '/post/' . $postNo . '/card.jpg?v=' . substr(md5($rawMessage . ($post['post_img0'] ?? '')), 0, 8);
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
                    'isGuestHuman' => false,
                    'postNo' => $postNo,
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

            // Authenticated human: enforce restriction & audience access before forwarding
            if ($isLoggedIn) {
                $viewerId = trim((string)($_SESSION['id'] ?? ''));
                if ($viewerId !== '' && !(new \App\services\FamilyPostAudienceService($db))->canUserAccessPost((int)$postNo, $viewerId)) {
                    if ($isJsonRequest) {
                        http_response_code(403);
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['status' => 'error', 'message' => 'Post not available.']);
                        return;
                    }
                    redirect('/profilePage');
                    return;
                }
                if ($isJsonRequest) {
                    \App\controller\members\PostMessage::getSinglePost($postNo);
                    return;
                }
                redirect('/member/profilePage?post_no=' . $postNo . '#post' . $postNo);
                return;
            }

            // Unregistered / Guest human: issue signed intent cookie to survive registration & OAuth SSO
            self::issueShareIntentCookie((int)$postNo, $cleanFamCode);

            if ($isJsonRequest) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
                return;
            }

            if (isset($_GET['direct'])) {
                redirect($targetRedirectUrl);
                return;
            }

            // Render the high-converting social landing preview screen for unregistered guests
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
                'isGuestHuman' => true,
                'postNo' => $postNo,
                'referrerSource' => \App\services\ShareEngagementService::detectReferrerSource(),
            ]);
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
     * Family-Member & Author API to generate or verify a post share link,
     * enforce the author's `allow_sharing` preference, and dispatch an immediate
     * Web Push + in-app notification to the post author when a relative shares their post.
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

        // SEC: this endpoint now has side effects (in-app + Web Push to the author), so it
        // must be CSRF-protected and rate-limited to stop cross-site notification spam.
        try {
            BaseController::validateCsrf();
            \Src\Limiter::limit($userId . ':postshare', 'post');
        } catch (\Throwable $csrfEx) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: invalid or missing security token.']);
            return;
        }

        $rawBody = file_get_contents('php://input');
        $jsonBody = is_string($rawBody) && $rawBody !== '' ? json_decode($rawBody, true) : null;
        $input = is_array($jsonBody) ? $jsonBody : $_POST;

        $postNo = (int)($postNo ?: ($input['post_no'] ?? $_POST['post_no'] ?? $_GET['post_no'] ?? 0));
        $channel = isset($input['channel']) && is_string($input['channel'])
            ? trim($input['channel'])
            : (isset($_GET['channel']) && is_string($_GET['channel']) ? trim($_GET['channel']) : 'link');

        if ($postNo <= 0 || $userId === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid post number specified.']);
            return;
        }

        try {
            $db = Db::connect2();
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, postFamCode, media_meta FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$post) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Post not found or has been removed.']);
                return;
            }

            $authorId = (string)($post['id'] ?? '');
            $isAuthor = ($authorId === $userId);
            $allowSharing = \App\controller\members\PostMessage::isPostShareable($post);

            if (!$isAuthor) {
                if (!$allowSharing) {
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Unauthorised: The author has disabled sharing for this post.',
                    ]);
                    return;
                }

                $audienceSvc = new \App\services\FamilyPostAudienceService($db);
                if (!$audienceSvc->canUserAccessPost($postNo, $userId)) {
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Unauthorised: Only authorised family members or the author can share this post.',
                    ]);
                    return;
                }
            }

            $authorNotified = false;
            if (!$isAuthor && $authorId !== '') {
                $sharerName = \App\controller\members\PostMessage::resolveUserDisplayName($db, $userId);
                $authorNotified = \App\controller\members\PostMessage::notifyAuthorOfPostShare(
                    $db,
                    $postNo,
                    $authorId,
                    $userId,
                    $sharerName,
                    $channel
                );
            }

            $appUrl = rtrim((string)($_ENV['APP_URL'] ?? 'http://localhost'), '/');
            $shareUrl = $appUrl . '/post/' . $postNo;

            echo json_encode([
                'status'  => 'success',
                'message' => 'Post share link generated successfully.',
                'data'    => [
                    'post_no'         => $postNo,
                    'share_url'       => $shareUrl,
                    'title'           => ($post['fullName'] ?? 'Family Member') . '\'s Family Memory',
                    'text'            => (string)($post['postMessage'] ?? ''),
                    'fam_code'        => (string)($post['postFamCode'] ?? ''),
                    'is_author'       => $isAuthor,
                    'allow_sharing'   => $allowSharing,
                    'author_notified' => $authorNotified,
                ],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to share post: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /post/{postNo}/card.jpg
     * 1200x630 designed preview card for a shared post, rendered by the festive card engine.
     * Shows only what the share page already exposes (author, a short excerpt, the first photo).
     */
    public function cardImage(int|string|null $postNo = null): void
    {
        $postNo = (int)$postNo;
        try {
            $db = Db::connect2();
            $stmt = $db->prepare('SELECT post_no, id, fullName, postMessage, post_img0, post_img1, post_img2, post_img3, post_img4, post_img5, voice_capsule_url, media_meta FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1');
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$post) {
                $fallback = dirname(__DIR__, 2) . '/public/img/og-invite.jpg';
                if (is_file($fallback)) {
                    header('Content-Type: image/jpeg');
                    header('Cache-Control: public, max-age=3600');
                    readfile($fallback);
                    return;
                }
                http_response_code(404);
                return;
            }

            $authorId = (string)($post['id'] ?? '');
            $viewerId = trim((string)($_SESSION['id'] ?? ''));
            if (!\App\controller\members\PostMessage::isPostShareable($post) && ($viewerId === '' || $viewerId !== $authorId)) {
                $fallback = dirname(__DIR__, 2) . '/public/img/og-invite.jpg';
                if (is_file($fallback)) {
                    header('Content-Type: image/jpeg');
                    header('Cache-Control: public, max-age=3600');
                    readfile($fallback);
                    return;
                }
                http_response_code(403);
                return;
            }
            if ($authorId !== '') {
                if ($viewerId !== '' && \App\services\MemberRestrictionService::isRestricted($db, $authorId, $viewerId, 'posts')) {
                    http_response_code(403);
                    return;
                }
            }

            $raw = (string)($post['postMessage'] ?? '');
            $text = preg_replace('/https?:\/\/[^\s]+|\/resources\/(?:videos|images|profile)\/[^\s]+|post_vid_[^\s]+|\[style:[a-z0-9_]+\]/i', '', $raw) ?? '';
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?? '');
            $isVideo = (bool)preg_match('/(\/resources\/videos\/|\.webm|\.mp4|\.mov|post_vid_|youtube\.com|youtu\.be|vimeo\.com|tiktok\.com|fb\.watch|facebook\.com\/(?:watch|reel|[^\/]+\/videos)|onedrive\.live\.com|1drv\.ms|drive\.google\.com)/i', $raw)
                || \App\classes\VideoParser::extractVideoFromText($raw) !== null;

            $img = trim((string)($post['post_img0'] ?? ''));
            if ($img === '') {
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($post['post_img' . $i])) {
                        $img = trim((string)$post['post_img' . $i]);
                        break;
                    }
                }
            }
            $heroUrl = '';
            if ($img !== '' && !preg_match('#^https?://#i', $img) && !str_contains($img, '..')) {
                $baseName = basename($img);
                $root = \App\services\AtgFestiveCardService::projectRoot();
                $candidates = [
                    '/resources/images/post/' . $baseName,
                    '/public/img/post/' . $baseName,
                    '/public/images/post/' . $baseName,
                    '/resources/images/' . $baseName,
                    '/public/images/' . $baseName,
                ];
                foreach ($candidates as $cand) {
                    if (is_file($root . $cand)) {
                        $heroUrl = $cand;
                        break;
                    }
                }
                if ($heroUrl === '') {
                    $heroUrl = (str_starts_with($img, '/resources/') || str_starts_with($img, '/public/'))
                        ? $img
                        : '/resources/images/post/' . $baseName;
                }
            }

            // Headline = first sentence (short); verse = the rest, capped
            $headline = $text;
            $verse = '';
            if (preg_match('/^(.{12,90}?[.!?])\s+(.+)$/us', $text, $m)) {
                $headline = $m[1];
                $verse = $m[2];
            } elseif (mb_strlen($text) > 90) {
                $headline = rtrim(mb_substr($text, 0, 80)) . '…';
                $verse = '';
            }
            if (mb_strlen($verse) > 220) {
                $verse = rtrim(mb_substr($verse, 0, 217)) . '…';
            }
            $kind = $isVideo ? 'A VIDEO' : ($heroUrl !== '' ? 'A PHOTO' : (!empty($post['voice_capsule_url']) ? 'A VOICE NOTE' : 'AN UPDATE'));
            if ($headline === '') {
                $headline = ucwords(strtolower($kind));
            }

            $card = \App\services\AtgFestiveCardService::hydrate([
                'share_token' => 'post' . $postNo . '_' . substr(md5($raw . $img), 0, 10),
                'occasion' => 'custom',
                'custom_occasion' => $kind,
                'mood' => 'joyful',
                'theme' => $heroUrl !== '' ? 'gold_champagne' : 'celestial_navy',
                'template_style' => $heroUrl !== '' ? 'spatial_3d' : 'translucent',
                'headline' => $headline,
                'verse' => $verse,
                'sign_off' => 'Shared with love by',
                'sender_name' => \App\services\UserHandleService::resolveExternalAuthorName(
                    $db,
                    (string)($post['id'] ?? ''),
                    (string)($post['fullName'] ?? '')
                ),
                'recipient_name' => '',
                'hero_photo_url' => $heroUrl,
            ]);
            $card['brand_line'] = 'FAMILYPLATFORM  ·  TAP TO SEE IT IN THE FAMILY HUB';

            $jpg = \App\services\AtgFestiveCardRenderer::renderJpeg($card, 'og');
            header('Content-Type: image/jpeg');
            header('Cache-Control: public, max-age=86400');
            header('Content-Length: ' . strlen($jpg));
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
                echo $jpg;
            }
        } catch (\Throwable $e) {
            error_log('[PostShareController::cardImage] ' . $e->getMessage());
            $fallback = dirname(__DIR__, 2) . '/public/img/og-invite.jpg';
            if (is_file($fallback)) {
                header('Content-Type: image/jpeg');
                header('Cache-Control: public, max-age=3600');
                readfile($fallback);
                return;
            }
            http_response_code(500);
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

    /**
     * Issues an HMAC-signed share intent cookie that persists the post_no and famCode
     * across registration, including third-party OAuth flows.
     */
    public static function issueShareIntentCookie(int $postNo, string $famCode): void
    {
        $payload = json_encode([
            'post_no' => $postNo,
            'famCode' => $famCode,
            't'       => time(),
        ]);
        if ($payload === false) {
            return;
        }

        $secret = (string)($_ENV['APP_KEY'] ?? 'familyPlatformIntentKey');
        $signature = hash_hmac('sha256', $payload, $secret);
        $cookieVal = $signature . '.' . base64_encode($payload);

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        setcookie('fp_share_intent', $cookieVal, [
            'expires'  => time() + (7 * 86400),
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Validates and unpacks the signed fp_share_intent cookie.
     *
     * @return array{post_no: int, famCode: string}|null
     */
    public static function parseShareIntentCookie(?string $rawCookie = null): ?array
    {
        $raw = $rawCookie ?? ($_COOKIE['fp_share_intent'] ?? null);
        if (!is_string($raw) || !str_contains($raw, '.')) {
            return null;
        }

        [$sig, $encoded] = explode('.', $raw, 2);
        $payload = base64_decode($encoded, true);
        if ($payload === false) {
            return null;
        }

        $secret = (string)($_ENV['APP_KEY'] ?? 'familyPlatformIntentKey');
        $expectedSig = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['post_no'])) {
            return null;
        }

        return [
            'post_no' => (int)$data['post_no'],
            'famCode' => (string)($data['famCode'] ?? ''),
        ];
    }
}
