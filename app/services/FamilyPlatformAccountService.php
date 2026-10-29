<?php

declare(strict_types=1);

namespace App\services;

use App\model\PollData;
use App\services\LinkPreviewService;
use PDO;
use PDOException;
use Src\Db;
use Src\Exceptions\ValidationException;

/**
 * FamilyPlatformAccountService
 *
 * Sovereign service managing the official FamilyPlatform system account.
 * Controls universal feed broadcasting, official post lifecycle, engagement metrics,
 * and user-friendly update summaries (zero-jargon community announcements).
 */
class FamilyPlatformAccountService
{
    public const ACCOUNT_ID = 'FAMILY_PLATFORM_OFFICIAL';
    public const ACCOUNT_EMAIL = 'official@myfamilyplatform.com';
    public const ACCOUNT_NAME = 'FamilyPlatform';
    public const ACCOUNT_FIRST_NAME = 'Family';
    public const ACCOUNT_LAST_NAME = 'Platform';
    public const ACCOUNT_FAM_CODE = 'PLATFORM';
    public const DEFAULT_LOGO = 'logo.png';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Ensure the official FamilyPlatform account exists across account, personal, and profilePics.
     */
    public function ensureAccountExists(): void
    {
        try {
            // 1. Account Table
            $stmtAcct = $this->db->prepare('SELECT id FROM account WHERE id = ? LIMIT 1');
            $stmtAcct->execute([self::ACCOUNT_ID]);
            if (!$stmtAcct->fetchColumn()) {
                $insAcct = $this->db->prepare(
                    'INSERT INTO account (id, email, firstName, lastName, type, status, is_premium)
                     VALUES (?, ?, ?, ?, ?, ?, 1)'
                );
                $insAcct->execute([
                    self::ACCOUNT_ID,
                    self::ACCOUNT_EMAIL,
                    self::ACCOUNT_FIRST_NAME,
                    self::ACCOUNT_LAST_NAME,
                    'official',
                    'active',
                ]);
            }

            // 2. Personal Table
            $stmtPers = $this->db->prepare('SELECT id FROM personal WHERE id = ? LIMIT 1');
            $stmtPers->execute([self::ACCOUNT_ID]);
            if (!$stmtPers->fetchColumn()) {
                $insPers = $this->db->prepare(
                    'INSERT INTO personal (id, firstName, lastName, famCode)
                     VALUES (?, ?, ?, ?)'
                );
                $insPers->execute([
                    self::ACCOUNT_ID,
                    self::ACCOUNT_FIRST_NAME,
                    self::ACCOUNT_LAST_NAME,
                    self::ACCOUNT_FAM_CODE,
                ]);
            }

            // 3. ProfilePics Table
            $stmtPic = $this->db->prepare('SELECT id FROM profilePics WHERE id = ? LIMIT 1');
            $stmtPic->execute([self::ACCOUNT_ID]);
            if (!$stmtPic->fetchColumn()) {
                $insPic = $this->db->prepare(
                    'INSERT INTO profilePics (id, img, buttonHTML)
                     VALUES (?, ?, ?)'
                );
                $insPic->execute([
                    self::ACCOUNT_ID,
                    self::DEFAULT_LOGO,
                    'Platform',
                ]);
            }
        } catch (\Throwable $e) {
            \error_log('[FamilyPlatformAccountService::ensureAccountExists] ' . $e->getMessage());
        }
    }

    /**
     * Publish an official announcement post from FamilyPlatform to everyone's feed.
     *
     * @param string $message
     * @param string|null $img0
     * @param array{question?: string, options?: array<int, string>} $pollData
     * @return int Created post_no
     */
    public function publishPost(string $message, ?string $img0 = null, array $pollData = []): int
    {
        $this->ensureAccountExists();

        $cleanMessage = LinkPreviewService::sanitizePostMessage(trim($message));
        if ($cleanMessage === '' && empty($img0) && empty($pollData['question'])) {
            throw new ValidationException('Please provide a message, image, or poll question for the announcement.');
        }

        $now = date('Y-m-d H:i:s');
        $timeMs = function_exists('milliSeconds') ? \milliSeconds() : (string)(int)(microtime(true) * 1000);

        $query = 'INSERT INTO post (
            id, fullName, postMessage, postFamCode, profileImg, post_img0,
            post_time, post_status, date_created
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            self::ACCOUNT_ID,
            self::ACCOUNT_NAME,
            $cleanMessage,
            self::ACCOUNT_FAM_CODE,
            self::DEFAULT_LOGO,
            $img0,
            $timeMs,
            'published',
            $now,
        ]);

        $postNo = (int)$this->db->lastInsertId();

        // Optional Poll Creation
        if (!empty($pollData['question']) && !empty($pollData['options']) && is_array($pollData['options'])) {
            $question = trim((string)$pollData['question']);
            $options = array_values(array_filter(array_map('trim', $pollData['options']), fn($o) => $o !== ''));
            if ($question !== '' && count($options) >= 2) {
                $pollStmt = $this->db->prepare('INSERT INTO post_polls (post_no, question, is_active) VALUES (?, ?, 1)');
                $pollStmt->execute([$postNo, $question]);
                $pollId = (int)$this->db->lastInsertId();

                $optStmt = $this->db->prepare('INSERT INTO post_poll_options (poll_id, option_text) VALUES (?, ?)');
                foreach ($options as $optText) {
                    $optStmt->execute([$pollId, $optText]);
                }
            }
        }

        // Defensively broadcast via Pusher to real-time subscribers
        try {
            if (class_exists('\Src\functionality\Pusher')) {
                $postData = [
                    'post_no' => $postNo,
                    'id' => self::ACCOUNT_ID,
                    'fullName' => self::ACCOUNT_NAME,
                    'postMessage' => $cleanMessage,
                    'postFamCode' => self::ACCOUNT_FAM_CODE,
                    'profileImg' => '/images/logo/logo.png',
                    'post_img0' => $img0,
                    'post_time' => $timeMs,
                    'date_created' => $now,
                    'is_official' => true,
                ];
                \Src\functionality\Pusher::broadcastToFamily(self::ACCOUNT_FAM_CODE, 'new-post', [$postData]);
            }
        } catch (\Throwable $th) {
            \error_log('[FamilyPlatformAccountService::publishPost Pusher] ' . $th->getMessage());
        }

        return $postNo;
    }

    /**
     * Retrieve paginated official posts authored by FamilyPlatform.
     *
     * @param int $limit
     * @param int $offset
     * @return array<array<string, mixed>>
     */
    public function getOfficialPosts(int $limit = 50, int $offset = 0): array
    {
        $query = 'SELECT p.*,
                    (SELECT COUNT(*) FROM comment c WHERE c.post_no = p.post_no AND c.date_deleted IS NULL) as comments_count,
                    (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_no = p.post_no) as reactions_count
                  FROM post p
                  WHERE p.id = ?
                    AND p.date_deleted IS NULL
                  ORDER BY p.date_created DESC
                  LIMIT ? OFFSET ?';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, self::ACCOUNT_ID, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count total active official posts.
     */
    public function countOfficialPosts(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM post WHERE id = ? AND date_deleted IS NULL');
        $stmt->execute([self::ACCOUNT_ID]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get aggregate engagement statistics for the official account.
     *
     * @return array<string, mixed>
     */
    public function getOfficialStats(): array
    {
        $query = 'SELECT 
                    COUNT(*) as total_posts,
                    COALESCE(SUM(views_count), 0) as total_views,
                    COALESCE(SUM(post_likes), 0) as total_likes,
                    MAX(date_created) as last_post_at
                  FROM post
                  WHERE id = ? AND date_deleted IS NULL';

        $stmt = $this->db->prepare($query);
        $stmt->execute([self::ACCOUNT_ID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Count total comments across all official posts
        $cmtStmt = $this->db->prepare(
            'SELECT COUNT(*) FROM comment c
             JOIN post p ON c.post_no = p.post_no
             WHERE p.id = ? AND c.date_deleted IS NULL AND p.date_deleted IS NULL'
        );
        $cmtStmt->execute([self::ACCOUNT_ID]);
        $totalComments = (int)$cmtStmt->fetchColumn();

        return [
            'total_posts' => (int)($row['total_posts'] ?? 0),
            'total_views' => (int)($row['total_views'] ?? 0),
            'total_likes' => (int)($row['total_likes'] ?? 0),
            'total_comments' => $totalComments,
            'last_post_at' => $row['last_post_at'] ?? null,
        ];
    }

    /**
     * Update an official post message.
     */
    public function updateOfficialPost(int $postNo, string $message): bool
    {
        $cleanMessage = LinkPreviewService::sanitizePostMessage(trim($message));
        if ($cleanMessage === '') {
            throw new ValidationException('Post message cannot be empty.');
        }

        $stmt = $this->db->prepare(
            'UPDATE post SET postMessage = ?, date_updated = NOW()
             WHERE post_no = ? AND id = ? AND date_deleted IS NULL'
        );
        return $stmt->execute([$cleanMessage, $postNo, self::ACCOUNT_ID]);
    }

    /**
     * Soft-delete an official post from the feed.
     */
    public function deleteOfficialPost(int $postNo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE post SET date_deleted = NOW()
             WHERE post_no = ? AND id = ? AND date_deleted IS NULL'
        );
        return $stmt->execute([$postNo, self::ACCOUNT_ID]);
    }

    /**
     * Transform technical release notes or update points into warm, conversational,
     * user-friendly copy (zero technical jargon, family-first tone in British English).
     *
     * @param string $rawNotes
     * @param string $preset 'feature'|'milestone'|'tip'|'security'
     * @return string
     */
    public function friendlySummarise(string $rawNotes, string $preset = 'feature'): string
    {
        $raw = trim($rawNotes);
        if ($raw === '') {
            return '';
        }

        // Try AI generation if ResilientAiChat is configured
        if (\class_exists('\Src\Ai\ResilientAiChat')) {
            try {
                $prompt = "You are the warm, caring voice of FamilyPlatform. 
Convert the following technical notes into an exciting, readable, human-friendly update for families.
RULES:
1. Strict British English (organise, customise, colour, prioritise, etc.).
2. Zero technical jargon or engineering buzzwords (no refactoring, caching, microservices, AST, endpoints).
3. Focus entirely on user benefits: how this makes family memories simpler, safer, and more joyous.
4. Use warm emojis naturally.
5. Keep paragraphs short and inviting with bullet points if helpful.
6. Maximum 150-200 words.

Technical notes:
{$raw}";

                $aiResult = (new \Src\Ai\ResilientAiChat())->complete(
                    [['role' => 'user', 'content' => $prompt]],
                    [
                        'temperature' => 0.6,
                        'max_tokens' => 350,
                    ]
                );

                $aiResponse = !empty($aiResult['success']) ? (string)($aiResult['content'] ?? '') : '';
                if (strlen(trim($aiResponse)) > 20) {
                    return trim($aiResponse);
                }
            } catch (\Throwable $aiEx) {
                \error_log('[FamilyPlatformAccountService::friendlySummarise AI] ' . $aiEx->getMessage());
            }
        }

        // Deterministic high-quality fallback generator
        return $this->formatDeterministicFriendlyCopy($raw, $preset);
    }

    /**
     * Deterministic template transformer for zero-jargon family announcements.
     */
    private function formatDeterministicFriendlyCopy(string $rawNotes, string $preset): string
    {
        // Split lines and clean bullet points
        $lines = preg_split('/[\r\n]+/', $rawNotes);
        $points = [];
        
        $jargonMap = [
            '/\b(?:redis|memcached|caching|cache)\s*(?:layer|store)?\b/i' => 'lightning-fast photo loading',
            '/\b(?:refactor(?:ed)?|backend|microservices?|api(?:s)?|endpoints?)\b/i' => 'behind-the-scenes improvements',
            '/\b(?:bug\s*fixes?|patches?|hotfixes?|edge-case(?:\s*bug)?)\b/i' => 'smoother experience and polish',
            '/\b(?:optimised|optimized|optimising|optimizing|performance)\b/i' => 'snappier response times',
            '/\b(?:database|sql|queries|query|schema|indexes?)\b/i' => 'family memory vault',
            '/\b(?:ui|ux|frontend)\b/i' => 'visual design and layout',
            '/\b(?:memory\s*reduction|leak|heap)\b/i' => 'lighter and faster loading',
            '/\b(?:pipeline|architecture|infrastructure)\b/i' => 'photo sharing engine',
            '/\b(?:auth|jwt|totp|2fa)\b/i' => 'account security and protection',
            '/\b(?:deploy(?:ed)?|release(?:d)?|shipping)\b/i' => 'unveiled',
        ];

        foreach ($lines as $line) {
            $cleaned = trim(preg_replace('/^[-*•\d\.\s]+/', '', $line) ?? '');
            if ($cleaned !== '') {
                foreach ($jargonMap as $pattern => $friendlyTerm) {
                    $cleaned = preg_replace($pattern, $friendlyTerm, $cleaned) ?? $cleaned;
                }
                $points[] = $cleaned;
            }
        }

        $intro = match ($preset) {
            'milestone' => "🎉 Exciting Family Milestone!\n\nWe are delighted to share a wonderful new milestone with all our family communities today:\n",
            'tip' => "💡 Helpful Tip for Your Family Space:\n\nHere is a quick and thoughtful way to get the most out of your FamilyPlatform experience:\n",
            'security' => "🛡️ Privacy & Peace of Mind Update:\n\nKeeping your family memories and private moments completely safe is always our highest priority. Here is what has been improved:\n",
            default => "✨ Exciting New Update is Live!\n\nWe have just released some fresh improvements to make preserving and enjoying your family memories even more delightful:\n",
        };

        $body = '';
        foreach ($points as $pt) {
            $body .= "• " . ucfirst($pt) . "\n";
        }

        $outro = "\nWe hope your family loves these updates. Take a look around and let us know what you think in the comments below! 💛";

        return $intro . "\n" . trim($body) . "\n" . $outro;
    }
}
