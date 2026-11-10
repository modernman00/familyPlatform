<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use PDO;
use PDOException;

/**
 * FeatureDiscoveryService
 *
 * Powers the 60-Day Omnichannel Feature Discovery & Activation Journey for FamilyPlatform.
 * Manages the 15 signature feature milestones across the first 60 days of membership:
 *  1. Day 1:  7-Generation Dynasty Family Tree & Kinship Engine
 *  2. Day 2:  First Message & 3D Glassmorphic Stage Studio
 *  3. Day 4:  Living Avatar Studio (Photo-to-Avatar)
 *  4. Day 7:  Inviting Maternal/In-Laws & Non-Family Friends
 *  5. Day 10: Living 3D Comment Emojis & Custom Me-Stickers
 *  6. Day 14: Then & Now Photo Studio & Drag Slider
 *  7. Day 18: Rich Cinema Video Sharing (YouTube/Vimeo/TikTok)
 *  8. Day 21: Family Reels Sanctuary & Kinetic Montages
 *  9. Day 25: Family Journal & AI Oral Story Assistant
 * 10. Day 30: Photo Gallery & Encrypted Family Vault
 * 11. Day 35: WhatsApp Clan Status Flyer Studio
 * 12. Day 40: Family Roundtable & Intergenerational Hub
 * 13. Day 45: 3-Gear PartyPlatform Event Studio & Announcements
 * 14. Day 52: Sunday Trivia, Guess Who & Two Truths and a Lie
 * 15. Day 60: 60-Day Dynasty Masterpiece Celebration
 *
 * Implements Silas Thorne's Adaptive Kinetic Fast-Forwarding: active users can
 * explore and complete any studio at any time without waiting for chronological drips.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FeatureDiscoveryService
{
    private PDO $db;

    /**
     * The canonical 15 feature milestones across the 60-day lifecycle.
     *
     * @var array<int, array{
     *     day: int,
     *     key: string,
     *     title: string,
     *     tagline: string,
     *     description: string,
     *     chapter: string,
     *     icon: string,
     *     action_url: string,
     *     action_label: string,
     *     badge: string
     * }>
     */
    public const MILESTONES = [
        1 => [
            'day' => 1,
            'key' => 'welcome_day1_tree',
            'title' => '7-Generation Dynasty Family Tree',
            'tagline' => 'Preserve your ancestral roots in under 60 seconds',
            'description' => 'Add your parents and siblings to unlock your interactive 7-generation family tree with automated kinship calculation and deceased ancestor honours.',
            'chapter' => 'Chapter 1: Roots & Identity',
            'icon' => 'bi-diagram-3-fill',
            'action_url' => '/organogram',
            'action_label' => 'Build Family Tree',
            'badge' => 'Dynasty Engine',
        ],
        2 => [
            'day' => 2,
            'key' => 'first_post_day2',
            'title' => 'First Family Message & 3D Stages',
            'tagline' => 'Break the ice with 3D glassmorphic text stages',
            'description' => 'Share your first family greeting or memory using 6 curated 3D translucent text stages like Dynasty Tree, 3D Holo Glass, and Royal Velvet.',
            'chapter' => 'Chapter 1: Roots & Identity',
            'icon' => 'bi-chat-quote-fill',
            'action_url' => '/profilePage#openPostModalTrigger',
            'action_label' => 'Post in 3D',
            'badge' => '3D Stage Studio',
        ],
        3 => [
            'day' => 4,
            'key' => 'avatar_studio_day4',
            'title' => 'Living Avatar Studio',
            'tagline' => 'Turn your photo into a stylised living avatar',
            'description' => 'Create your digital family persona with instant photo-to-avatar rendering, hair and facial customisation, and generational clothing.',
            'chapter' => 'Chapter 1: Roots & Identity',
            'icon' => 'bi-person-bounding-box',
            'action_url' => '/member/emojiStudio',
            'action_label' => 'Launch Avatar Studio',
            'badge' => 'Living Avatars',
        ],
        4 => [
            'day' => 7,
            'key' => 'invite_extended_day7',
            'title' => 'Invite Other Families & Guests',
            'tagline' => 'Federate maternal lines, in-laws & non-family guests',
            'description' => 'Unite your maternal branch and in-laws with dual family codes, or invite non-family friends as verified guest contributors via 1-tap WhatsApp links.',
            'chapter' => 'Chapter 1: Roots & Identity',
            'icon' => 'bi-people-fill',
            'action_url' => '/invite',
            'action_label' => 'Invite Relatives & Guests',
            'badge' => 'Kinship Network',
        ],
        5 => [
            'day' => 10,
            'key' => 'living_emojis_day10',
            'title' => 'Living 3D Emojis & Me-Stickers',
            'tagline' => 'Express yourself with interactive 3D reactions',
            'description' => 'React to family stories with animated 3D emojis, voice audio comments, and personalized Me-Stickers rendered directly from your avatar.',
            'chapter' => 'Chapter 2: Expression & Media',
            'icon' => 'bi-emoji-heart-eyes-fill',
            'action_url' => '/member/emojiStudio',
            'action_label' => 'Try 3D Emojis',
            'badge' => 'ATG 3D Emojis',
        ],
        6 => [
            'day' => 14,
            'key' => 'then_now_studio_day14',
            'title' => 'Then & Now Photo Studio',
            'tagline' => 'Compare yesterday and today side-by-side',
            'description' => 'Upload a vintage childhood photo alongside a modern photo to create an interactive before-and-after drag slider with ghost-overlay alignment.',
            'chapter' => 'Chapter 2: Expression & Media',
            'icon' => 'bi-images',
            'action_url' => '/profilePage?tab=then_now',
            'action_label' => 'Create Then & Now',
            'badge' => 'Photo Studio',
        ],
        7 => [
            'day' => 18,
            'key' => 'cinema_video_day18',
            'title' => 'Rich Cinema Video Sharing',
            'tagline' => 'Share videos that unfurl into beautiful 16:9 cinema players',
            'description' => 'Paste YouTube, Vimeo, or TikTok video links on the feed and watch them automatically transform into high-definition, responsive cinema players.',
            'chapter' => 'Chapter 2: Expression & Media',
            'icon' => 'bi-play-circle-fill',
            'action_url' => '/profilePage',
            'action_label' => 'Share Video Clip',
            'badge' => 'Cinema Player',
        ],
        8 => [
            'day' => 21,
            'key' => 'family_reels_day21',
            'title' => 'Family Reels Sanctuary',
            'tagline' => 'Preserve 30-second kinetic video memories',
            'description' => 'Capture generational life updates, toddler giggles, and elder wisdom in short-form video reels with ambient background soundtracks and 1-level reply threads.',
            'chapter' => 'Chapter 2: Expression & Media',
            'icon' => 'bi-camera-reels-fill',
            'action_url' => '/reels',
            'action_label' => 'Watch & Post Reels',
            'badge' => 'Video Reels',
        ],
        9 => [
            'day' => 25,
            'key' => 'family_journal_day25',
            'title' => 'Family Journal & AI Story Assistant',
            'tagline' => 'Chronicle cherished oral memories for future generations',
            'description' => 'Preserve your family chronicles with the built-in AI Story Assistant. Collaborative journals allow grandparents, parents, and grandchildren to co-author.',
            'chapter' => 'Chapter 3: Living Chronicles',
            'icon' => 'bi-journal-bookmark-fill',
            'action_url' => '/journal',
            'action_label' => 'Write Family Story',
            'badge' => 'Oral History',
        ],
        10 => [
            'day' => 30,
            'key' => 'photo_vault_day30',
            'title' => 'Photo Gallery & Encrypted Vault',
            'tagline' => 'Secure treasured heirlooms with AES-256-GCM encryption',
            'description' => 'Organise high-resolution albums with face tagging, and store sensitive family documents, deeds, and birth certificates in the AES-256 encrypted vault.',
            'chapter' => 'Chapter 3: Living Chronicles',
            'icon' => 'bi-shield-lock-fill',
            'action_url' => '/profilePage?tab=gallery',
            'action_label' => 'Explore Vault & Albums',
            'badge' => 'Secure Vault',
        ],
        11 => [
            'day' => 35,
            'key' => 'flyer_studio_day35',
            'title' => 'Flyer Studio & Clan Crests',
            'tagline' => 'Generate high-res flyers for WhatsApp status',
            'description' => 'Render beautiful visual clan crests and family tree overview flyers optimized for WhatsApp Status, Instagram Stories, and family WhatsApp groups.',
            'chapter' => 'Chapter 3: Living Chronicles',
            'icon' => 'bi-file-earmark-richtext-fill',
            'action_url' => '/organogram?flyer=1',
            'action_label' => 'Generate Clan Flyer',
            'badge' => 'Flyer Studio',
        ],
        12 => [
            'day' => 40,
            'key' => 'family_roundtable_day40',
            'title' => 'Family Roundtable & Intergenerational Hub',
            'tagline' => 'Spark warm debates and heartfelt discussions',
            'description' => 'Gather all generations for weekly discussion prompts, family decisions, and consensus votes without the clutter of external chat apps.',
            'chapter' => 'Chapter 4: Gatherings & Dynasty',
            'icon' => 'bi-chat-dots-fill',
            'action_url' => '/profilePage?tab=roundtable',
            'action_label' => 'Join Roundtable',
            'badge' => 'Family Hub',
        ],
        13 => [
            'day' => 45,
            'key' => 'events_studio_day45',
            'title' => 'Events & 3-Gear PartyPlatform Studio',
            'tagline' => 'Plan reunions and celebrations with live RSVP tracking',
            'description' => 'Organise birthdays and reunions using 3-Gear event architecture. One-click Single Sign-On syncs your family tree directly into PartyPlatform Guest Builder.',
            'chapter' => 'Chapter 4: Gatherings & Dynasty',
            'icon' => 'bi-calendar-event-fill',
            'action_url' => '/event',
            'action_label' => 'Plan Family Event',
            'badge' => 'PartyPlatform Sync',
        ],
        14 => [
            'day' => 52,
            'key' => 'heritage_games_day52',
            'title' => 'Sunday Trivia & Heritage Games',
            'tagline' => 'Play Guess Who and Two Truths & a Lie',
            'description' => 'Compete in weekly Sunday family heritage trivia, vote in interactive polls, and test how well you know your aunts, uncles, and cousins.',
            'chapter' => 'Chapter 4: Gatherings & Dynasty',
            'icon' => 'bi-controller',
            'action_url' => '/member/trivia',
            'action_label' => 'Play Family Games',
            'badge' => 'Heritage Games',
        ],
        15 => [
            'day' => 60,
            'key' => 'dynasty_snapshot_day60',
            'title' => '60-Day Dynasty Masterpiece',
            'tagline' => 'Celebrate your family heritage milestone',
            'description' => 'Claim your 60-Day Dynasty Pioneer badge, export your high-resolution printable family tree, and review your family\'s 60-day memory reel.',
            'chapter' => 'Chapter 4: Gatherings & Dynasty',
            'icon' => 'bi-award-fill',
            'action_url' => '/organogram?view=dynasty',
            'action_label' => 'View Dynasty Masterpiece',
            'badge' => 'Dynasty Pioneer',
        ],
    ];

    /**
     * In-memory request-level cache to eliminate duplicate queries within the same request lifecycle.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $requestCache = [];

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Get the full 60-day feature discovery status for a user.
     *
     * @param string $userId
     * @param string $famCode
     * @param bool $skipCache
     * @return array{
     *     account_age_days: int,
     *     is_in_discovery_window: bool,
     *     completed_count: int,
     *     total_milestones: int,
     *     progress_percentage: int,
     *     current_milestone: array<string, mixed>,
     *     milestones: array<int, array<string, mixed>>
     * }
     */
    public function getUserDiscoveryState(string $userId, string $famCode, bool $skipCache = false): array
    {
        $userId = trim($userId);
        $famCode = trim($famCode);

        $cacheKey = $userId . '_' . $famCode;
        if (!$skipCache && isset(self::$requestCache[$cacheKey])) {
            /** @var array{account_age_days: int, is_in_discovery_window: bool, completed_count: int, total_milestones: int, progress_percentage: int, current_milestone: array<string, mixed>, milestones: array<int, array<string, mixed>>} */
            return self::$requestCache[$cacheKey];
        }

        // Check short-lived session cache (300 seconds TTL) to eliminate query storms across feed tabs
        $sessionKey = 'discovery_state_' . $userId;
        if (!$skipCache && isset($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
            $cachedTime = (int)($_SESSION[$sessionKey]['t'] ?? 0);
            if (time() - $cachedTime < 300 && !empty($_SESSION[$sessionKey]['data']) && is_array($_SESSION[$sessionKey]['data'])) {
                /** @var array{account_age_days: int, is_in_discovery_window: bool, completed_count: int, total_milestones: int, progress_percentage: int, current_milestone: array<string, mixed>, milestones: array<int, array<string, mixed>>} $cachedData */
                $cachedData = $_SESSION[$sessionKey]['data'];
                self::$requestCache[$cacheKey] = $cachedData;
                return $cachedData;
            }
        }

        $accountAgeDays = $this->getAccountAgeInDays($userId);
        $completedKeys = $this->getCompletedEventKeys($userId);

        $milestones = [];
        $completedCount = 0;
        $currentMilestone = null;

        foreach (self::MILESTONES as $stepIndex => $item) {
            $key = $item['key'];
            $isDone = in_array($key, $completedKeys, true) || $this->checkHeuristicCompletion($userId, $famCode, $key);
            if ($isDone) {
                $completedCount++;
            }

            $isUnlocked = ($accountAgeDays >= $item['day']) || $isDone;

            $milestoneData = array_merge($item, [
                'step_index'  => $stepIndex,
                'completed'   => $isDone,
                'unlocked'    => $isUnlocked,
                'is_current'  => false,
            ]);

            if ($currentMilestone === null && !$isDone) {
                $milestoneData['is_current'] = true;
                $currentMilestone = $milestoneData;
            }

            $milestones[] = $milestoneData;
        }

        // If all 15 are done, current milestone defaults to the 60-day milestone
        if ($currentMilestone === null) {
            $currentMilestone = end($milestones);
            $currentMilestone['is_current'] = true;
        }

        $totalMilestones = count(self::MILESTONES);
        $progressPercentage = (int)round(($completedCount / max(1, $totalMilestones)) * 100);

        $result = [
            'account_age_days'        => $accountAgeDays,
            'is_in_discovery_window'  => ($accountAgeDays <= 60),
            'completed_count'         => $completedCount,
            'total_milestones'        => $totalMilestones,
            'progress_percentage'     => $progressPercentage,
            'current_milestone'       => $currentMilestone,
            'milestones'              => $milestones,
        ];

        self::$requestCache[$cacheKey] = $result;
        $_SESSION[$sessionKey] = [
            't' => time(),
            'data' => $result,
        ];

        return $result;
    }

    /**
     * Mark a feature milestone completed immediately (Fast-Forward).
     *
     * @param string $userId
     * @param string $featureKey
     * @return array{status: string, message: string, key: string}
     */
    public function markMilestoneCompleted(string $userId, string $featureKey): array
    {
        $featureKey = trim($featureKey);
        $validKeys = array_column(self::MILESTONES, 'key');

        if (!in_array($featureKey, $validKeys, true)) {
            return ['status' => 'error', 'message' => 'Invalid feature milestone key', 'key' => $featureKey];
        }

        try {
            $stmt = $this->db->prepare('
                INSERT IGNORE INTO user_lifecycle_events (user_id, event_type, channel, metadata, dispatched_at)
                VALUES (?, ?, \'interactive_fast_forward\', JSON_OBJECT(\'action\', \'user_launched\'), NOW())
            ');
            $stmt->execute([$userId, $featureKey]);

            // Invalidate caches immediately so updated progress reflects instantly
            self::$requestCache = [];
            unset($_SESSION['discovery_state_' . $userId]);

            return ['status' => 'success', 'message' => 'Milestone completed successfully', 'key' => $featureKey];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Database error recording milestone', 'key' => $featureKey];
        }
    }

    /**
     * Compute account age in calendar days.
     */
    private function getAccountAgeInDays(string $userId): int
    {
        try {
            $stmt = $this->db->prepare('SELECT TIMESTAMPDIFF(DAY, created_at, NOW()) FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $age = $stmt->fetchColumn();
            return ($age !== false && $age !== null) ? max(0, (int)$age) : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Fetch all recorded lifecycle events for this user.
     *
     * @param string $userId
     * @return array<int, string>
     */
    private function getCompletedEventKeys(string $userId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT DISTINCT event_type FROM user_lifecycle_events WHERE user_id = ?');
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Heuristic checks: if the user already performed the feature action organically,
     * consider it completed so they are never nagged.
     */
    private function checkHeuristicCompletion(string $userId, string $famCode, string $key): bool
    {
        try {
            switch ($key) {
                case 'welcome_day1_tree':
                    $s = $this->db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ?');
                    $s->execute([$famCode]);
                    return ((int)$s->fetchColumn()) > 1;

                case 'first_post_day2':
                    $s = $this->db->prepare('SELECT post_no FROM post WHERE id = ? AND date_deleted IS NULL LIMIT 1');
                    $s->execute([$userId]);
                    return (bool)$s->fetchColumn();

                case 'avatar_studio_day4':
                    $s = $this->db->prepare('SELECT img FROM profilePics WHERE id = ? LIMIT 1');
                    $s->execute([$userId]);
                    $img = (string)$s->fetchColumn();
                    return ($img !== '' && !str_contains($img, 'avatarM.png') && !str_contains($img, 'avatarF.png'));

                case 'invite_extended_day7':
                    $s = $this->db->prepare('SELECT COUNT(*) FROM personal WHERE famCode = ? AND id != ?');
                    $s->execute([$famCode, $userId]);
                    return ((int)$s->fetchColumn()) >= 2;

                case 'then_now_studio_day14':
                    $s = $this->db->prepare('SELECT 1 FROM post_comparisons pc JOIN post p ON pc.post_no = p.post_no WHERE p.id = ? LIMIT 1');
                    $s->execute([$userId]);
                    return (bool)$s->fetchColumn();

                case 'family_reels_day21':
                    $s = $this->db->prepare('SELECT 1 FROM family_reels WHERE user_id = ? LIMIT 1');
                    $s->execute([$userId]);
                    return (bool)$s->fetchColumn();

                case 'family_journal_day25':
                    $s = $this->db->prepare('SELECT 1 FROM family_journals WHERE author_id = ? LIMIT 1');
                    $s->execute([$userId]);
                    return (bool)$s->fetchColumn();

                default:
                    return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
}
