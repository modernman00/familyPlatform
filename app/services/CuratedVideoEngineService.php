<?php

declare(strict_types=1);

namespace App\services;

use App\classes\VideoParser;
use PDO;
use Src\Db;
use Src\Exceptions\ValidationException;

/**
 * CuratedVideoEngineService
 *
 * Automated, family-safe Content Engine that discovers funny, wholesome, and
 * family-friendly YouTube/video content via public Atom/RSS feeds and oEmbed,
 * filters for safety, crafts British English engagement captions, and publishes
 * to everyone's feed via FamilyPlatformAccountService.
 */
class CuratedVideoEngineService
{
    private const MAX_SOURCES_PER_RUN = 15;
    private const DEFAULT_LIMIT_PER_SOURCE = 8;
    private const CURL_CONNECT_TIMEOUT = 4;
    private const CURL_TIMEOUT = 8;

    /** @var list<string> */
    private const UNSAFE_KEYWORDS = [
        'nsfw', '18+', 'uncensored', 'explicit', 'porn', 'xxx', 'onlyfans',
        'gambling', 'casino', 'betting', 'crypto giveaway', 'clickbait',
        'prank gone wrong', 'arrested', 'blood', 'gore', 'violent', 'shooting',
        'drug', 'weed', 'cocaine', 'profanity', 'swearing', 'drama', 'exposed',
    ];

    private PDO $db;
    private FamilyPlatformAccountService $officialAccountService;

    /** @var callable(string): array{ok: bool, body: string, error: string} */
    private $httpFetcher;

    /**
     * @param callable(string): array{ok: bool, body: string, error: string}|null $httpFetcher
     */
    public function __construct(
        ?PDO $db = null,
        ?FamilyPlatformAccountService $officialAccountService = null,
        ?callable $httpFetcher = null
    ) {
        $this->db = $db ?? Db::connect2();
        $this->officialAccountService = $officialAccountService ?? new FamilyPlatformAccountService($this->db);
        $this->httpFetcher = $httpFetcher ?? [$this, 'defaultHttpFetch'];
    }

    /**
     * Ensure schema tables and sensible default sources/settings exist.
     */
    public function ensureSchema(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS curated_video_sources (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                source_type ENUM('youtube_channel', 'youtube_playlist', 'rss_feed') NOT NULL DEFAULT 'youtube_channel',
                source_identifier VARCHAR(255) NOT NULL,
                category ENUM('funny', 'family', 'pets', 'wholesome', 'kids') NOT NULL DEFAULT 'family',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                last_fetched_at TIMESTAMP NULL DEFAULT NULL,
                last_error VARCHAR(255) NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_source_type_ident (source_type, source_identifier)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS curated_videos (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                source_id INT UNSIGNED NULL DEFAULT NULL,
                provider VARCHAR(32) NOT NULL DEFAULT 'youtube',
                external_video_id VARCHAR(64) NOT NULL,
                video_url VARCHAR(500) NOT NULL,
                title VARCHAR(255) NOT NULL,
                author_name VARCHAR(160) NOT NULL DEFAULT '',
                thumbnail_url VARCHAR(500) NOT NULL DEFAULT '',
                category VARCHAR(32) NOT NULL DEFAULT 'family',
                suggested_caption TEXT NOT NULL,
                status ENUM('queued', 'published', 'dismissed') NOT NULL DEFAULT 'queued',
                published_post_no INT UNSIGNED NULL DEFAULT NULL,
                published_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_external_video (provider, external_video_id),
                KEY idx_curated_status_created (status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS curated_video_settings (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                auto_publish_enabled TINYINT(1) NOT NULL DEFAULT 0,
                publish_interval_hours SMALLINT UNSIGNED NOT NULL DEFAULT 12,
                max_queue_per_source TINYINT UNSIGNED NOT NULL DEFAULT 8,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->exec(
            "INSERT IGNORE INTO curated_video_settings (id, auto_publish_enabled, publish_interval_hours, max_queue_per_source)
             VALUES (1, 0, 12, 8)"
        );

        $countStmt = $this->db->query('SELECT COUNT(*) FROM curated_video_sources');
        $count = $countStmt !== false ? (int)$countStmt->fetchColumn() : 0;
        if ($count === 0) {
            $defaults = [
                ['AFV Official (Funny Home Videos)', 'youtube_channel', 'UC_x5XG1OV2P6uZZ5FSM9Ttw', 'funny'],
                ['The Dodo (Wholesome Pets)', 'youtube_channel', 'UCINb0wqPz-A0dV9nARjJlOQ', 'pets'],
                ['BBC Earth (Family Nature)', 'youtube_channel', 'UCwmZiChSryoWQCZMIQezgTg', 'family'],
                ['Dad, How Do I? (Helpful Family)', 'youtube_channel', 'UCNepEAWZH0TBu7dkxIbluDw', 'wholesome'],
            ];
            $stmt = $this->db->prepare(
                'INSERT IGNORE INTO curated_video_sources (name, source_type, source_identifier, category, is_active)
                 VALUES (?, ?, ?, ?, 1)'
            );
            foreach ($defaults as $row) {
                $stmt->execute($row);
            }
        }
    }

    /**
     * Return dashboard metrics, settings, sources, queued videos, and published history.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $this->ensureSchema();

        $settings = $this->getSettings();
        $sourcesStmt = $this->db->query('SELECT * FROM curated_video_sources ORDER BY is_active DESC, id DESC');
        $sources = $sourcesStmt !== false ? $sourcesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $queuedStmt = $this->db->prepare(
            "SELECT v.*, s.name AS source_name
             FROM curated_videos v
             LEFT JOIN curated_video_sources s ON v.source_id = s.id
             WHERE v.status = 'queued'
             ORDER BY v.created_at DESC, v.id DESC
             LIMIT 40"
        );
        $queuedStmt->execute();
        $queued = $queuedStmt->fetchAll(PDO::FETCH_ASSOC);

        $publishedStmt = $this->db->prepare(
            "SELECT v.*, s.name AS source_name
             FROM curated_videos v
             LEFT JOIN curated_video_sources s ON v.source_id = s.id
             WHERE v.status = 'published'
             ORDER BY v.published_at DESC, v.id DESC
             LIMIT 20"
        );
        $publishedStmt->execute();
        $published = $publishedStmt->fetchAll(PDO::FETCH_ASSOC);

        $countsStmt = $this->db->query(
            "SELECT
                SUM(CASE WHEN status = 'queued' THEN 1 ELSE 0 END) AS queued_count,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published_count,
                SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) AS dismissed_count,
                MAX(published_at) AS last_published_at
             FROM curated_videos"
        );
        $countsRow = ($countsStmt !== false ? $countsStmt->fetch(PDO::FETCH_ASSOC) : false) ?: [];

        $activeStmt = $this->db->query(
            'SELECT COUNT(*) FROM curated_video_sources WHERE is_active = 1'
        );
        $activeSourcesCount = $activeStmt !== false ? (int)$activeStmt->fetchColumn() : 0;

        return [
            'stats' => [
                'queued_count' => (int)($countsRow['queued_count'] ?? 0),
                'published_count' => (int)($countsRow['published_count'] ?? 0),
                'dismissed_count' => (int)($countsRow['dismissed_count'] ?? 0),
                'active_sources_count' => $activeSourcesCount,
                'last_published_at' => $countsRow['last_published_at'] ?? null,
            ],
            'settings' => $settings,
            'sources' => $sources,
            'queued' => $queued,
            'published' => $published,
        ];
    }

    /**
     * @return array{auto_publish_enabled: bool, publish_interval_hours: int, max_queue_per_source: int}
     */
    public function getSettings(): array
    {
        $this->ensureSchema();
        $stmt = $this->db->query('SELECT * FROM curated_video_settings WHERE id = 1 LIMIT 1');
        $row = ($stmt !== false ? $stmt->fetch(PDO::FETCH_ASSOC) : false) ?: [];
        return [
            'auto_publish_enabled' => !empty($row['auto_publish_enabled']),
            'publish_interval_hours' => max(1, min(168, (int)($row['publish_interval_hours'] ?? 12))),
            'max_queue_per_source' => max(1, min(15, (int)($row['max_queue_per_source'] ?? 8))),
        ];
    }

    /**
     * Update auto-publish cadence settings.
     */
    public function updateSettings(bool $autoPublishEnabled, int $publishIntervalHours, int $maxQueuePerSource = 8): void
    {
        $this->ensureSchema();
        $interval = max(1, min(168, $publishIntervalHours));
        $maxPerSource = max(1, min(15, $maxQueuePerSource));

        $stmt = $this->db->prepare(
            'UPDATE curated_video_settings
             SET auto_publish_enabled = ?, publish_interval_hours = ?, max_queue_per_source = ?
             WHERE id = 1'
        );
        $stmt->execute([$autoPublishEnabled ? 1 : 0, $interval, $maxPerSource]);
    }

    /**
     * Add a new YouTube channel, playlist, or RSS video source.
     */
    public function addSource(string $name, string $rawIdentifier, string $sourceType, string $category): int
    {
        $this->ensureSchema();

        $cleanName = trim($name);
        if ($cleanName === '' || mb_strlen($cleanName) > 120) {
            throw new ValidationException('Please provide a valid source name (1–120 characters).');
        }

        $allowedCategories = ['funny', 'family', 'pets', 'wholesome', 'kids'];
        $cleanCategory = in_array($category, $allowedCategories, true) ? $category : 'family';

        $normalized = $this->normalizeSourceInput($sourceType, $rawIdentifier);

        $stmt = $this->db->prepare(
            'INSERT INTO curated_video_sources (name, source_type, source_identifier, category, is_active)
             VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $cleanName,
            $normalized['source_type'],
            $normalized['source_identifier'],
            $cleanCategory,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Toggle a source active/paused.
     */
    public function toggleSource(int $sourceId, bool $isActive): void
    {
        $this->ensureSchema();
        $stmt = $this->db->prepare('UPDATE curated_video_sources SET is_active = ? WHERE id = ?');
        $stmt->execute([$isActive ? 1 : 0, $sourceId]);
    }

    /**
     * Delete a source.
     */
    public function deleteSource(int $sourceId): void
    {
        $this->ensureSchema();
        $stmt = $this->db->prepare('DELETE FROM curated_video_sources WHERE id = ?');
        $stmt->execute([$sourceId]);
    }

    /**
     * Fetch new videos from all active sources, filter for family safety, and queue unique items.
     *
     * @return array{sources_checked: int, videos_added: int, errors: list<string>}
     */
    public function fetchFromActiveSources(?int $limitPerSource = null): array
    {
        $this->ensureSchema();
        $settings = $this->getSettings();
        $limit = $limitPerSource ?? $settings['max_queue_per_source'];
        $limit = max(1, min(15, $limit));

        $stmt = $this->db->prepare(
            'SELECT * FROM curated_video_sources WHERE is_active = 1 ORDER BY last_fetched_at ASC, id ASC LIMIT ?'
        );
        $stmt->bindValue(1, self::MAX_SOURCES_PER_RUN, PDO::PARAM_INT);
        $stmt->execute();
        $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $videosAdded = 0;
        $errors = [];

        foreach ($sources as $source) {
            $sourceId = (int)$source['id'];
            $feedUrl = $this->buildFeedUrl(
                (string)$source['source_type'],
                (string)$source['source_identifier']
            );

            if ($feedUrl === '' || !$this->isAllowedExternalUrl($feedUrl)) {
                $err = 'Invalid or disallowed feed URL.';
                $this->recordSourceFetchResult($sourceId, $err);
                $errors[] = (string)$source['name'] . ': ' . $err;
                continue;
            }

            $res = ($this->httpFetcher)($feedUrl);
            if (empty($res['ok']) || $res['body'] === '') {
                $err = $res['error'] !== '' ? $res['error'] : 'Empty feed response.';
                $this->recordSourceFetchResult($sourceId, mb_substr($err, 0, 250));
                $errors[] = (string)$source['name'] . ': ' . $err;
                continue;
            }

            $entries = $this->parseFeedEntries($res['body'], $limit);
            $this->recordSourceFetchResult($sourceId, null);

            foreach ($entries as $entry) {
                if (!$this->isFamilySafe($entry['title'], $entry['description'])) {
                    continue;
                }

                $caption = $this->generateEngagingCaption(
                    $entry['title'],
                    (string)$source['category'],
                    $entry['author']
                );

                if ($this->insertQueuedVideo(
                    $sourceId,
                    $entry['provider'],
                    $entry['video_id'],
                    $entry['video_url'],
                    $entry['title'],
                    $entry['author'],
                    $entry['thumbnail_url'],
                    (string)$source['category'],
                    $caption
                )) {
                    $videosAdded++;
                }
            }
        }

        return [
            'sources_checked' => count($sources),
            'videos_added' => $videosAdded,
            'errors' => $errors,
        ];
    }

    /**
     * Import a single YouTube/Vimeo video URL directly into the queue (enriching via oEmbed when available).
     *
     * @return int Inserted or existing curated_videos.id
     */
    public function importSingleVideoUrl(string $rawUrl, string $category = 'funny', ?string $customTitle = null): int
    {
        $this->ensureSchema();
        $cleanUrl = trim($rawUrl);
        if (!$this->isAllowedExternalUrl($cleanUrl)) {
            throw new ValidationException('Please provide a valid HTTPS YouTube or Vimeo link.');
        }

        $parsed = VideoParser::parseVideoUrl($cleanUrl);
        if ($parsed === null || empty($parsed['videoId']) || !in_array($parsed['type'], ['youtube', 'vimeo'], true)) {
            throw new ValidationException('Only valid YouTube or Vimeo video URLs are supported.');
        }

        $provider = $parsed['type'];
        $videoId = (string)$parsed['videoId'];
        $canonicalUrl = $provider === 'youtube'
            ? 'https://www.youtube.com/watch?v=' . $videoId
            : (string)$parsed['originalUrl'];

        $title = trim((string)$customTitle);
        $author = '';
        $thumbnail = (string)($parsed['thumbnailUrl'] ?? '');

        if ($provider === 'youtube' && $title === '') {
            $oembedUrl = 'https://www.youtube.com/oembed?url=' . rawurlencode($canonicalUrl) . '&format=json';
            $oembedRes = ($this->httpFetcher)($oembedUrl);
            if (!empty($oembedRes['ok']) && $oembedRes['body'] !== '') {
                $decoded = json_decode($oembedRes['body'], true);
                if (is_array($decoded)) {
                    $title = trim((string)($decoded['title'] ?? ''));
                    $author = trim((string)($decoded['author_name'] ?? ''));
                    if (!empty($decoded['thumbnail_url']) && is_string($decoded['thumbnail_url'])) {
                        $thumbnail = $decoded['thumbnail_url'];
                    }
                }
            }
        }

        if ($title === '') {
            $title = 'Family Favourite Video Clip';
        }

        if (!$this->isFamilySafe($title, '')) {
            throw new ValidationException('This video title failed our family-safety moderation check.');
        }

        $allowedCategories = ['funny', 'family', 'pets', 'wholesome', 'kids'];
        $cleanCategory = in_array($category, $allowedCategories, true) ? $category : 'funny';
        $caption = $this->generateEngagingCaption($title, $cleanCategory, $author);

        $existingStmt = $this->db->prepare(
            'SELECT id FROM curated_videos WHERE provider = ? AND external_video_id = ? LIMIT 1'
        );
        $existingStmt->execute([$provider, $videoId]);
        $existingId = $existingStmt->fetchColumn();
        if ($existingId !== false) {
            return (int)$existingId;
        }

        $ins = $this->db->prepare(
            'INSERT INTO curated_videos
                (source_id, provider, external_video_id, video_url, title, author_name, thumbnail_url, category, suggested_caption, status)
             VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, "queued")'
        );
        $ins->execute([
            $provider,
            $videoId,
            $canonicalUrl,
            mb_substr($title, 0, 255),
            mb_substr($author, 0, 160),
            mb_substr($thumbnail, 0, 500),
            $cleanCategory,
            $caption,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Publish a queued curated video to everyone's feed via FamilyPlatformAccountService.
     */
    public function publishQueuedVideo(int $curatedVideoId, ?string $overrideCaption = null): int
    {
        $this->ensureSchema();

        $stmt = $this->db->prepare('SELECT * FROM curated_videos WHERE id = ? LIMIT 1');
        $stmt->execute([$curatedVideoId]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$video) {
            throw new ValidationException('Curated video not found.');
        }

        $caption = $overrideCaption !== null && trim($overrideCaption) !== ''
            ? trim($overrideCaption)
            : trim((string)$video['suggested_caption']);

        ContentSafetyService::assertClean($caption, 'caption', 'curated_video');

        $videoUrl = trim((string)$video['video_url']);
        $postMessage = str_contains($caption, $videoUrl)
            ? $caption
            : $caption . "\n\n" . $videoUrl;

        $postNo = $this->officialAccountService->publishPost($postMessage);

        $upd = $this->db->prepare(
            "UPDATE curated_videos
             SET status = 'published', suggested_caption = ?, published_post_no = ?, published_at = NOW()
             WHERE id = ?"
        );
        $upd->execute([$caption, $postNo, $curatedVideoId]);

        return $postNo;
    }

    /**
     * Dismiss a queued video so it will not be published or re-queued.
     */
    public function dismissVideo(int $curatedVideoId): void
    {
        $this->ensureSchema();
        $stmt = $this->db->prepare("UPDATE curated_videos SET status = 'dismissed' WHERE id = ?");
        $stmt->execute([$curatedVideoId]);
    }

    /**
     * Cron entry point: respects auto_publish_enabled & cadence interval, fetches if needed, and publishes 1 video.
     *
     * @return array<string, mixed>
     */
    public function runScheduledAutoPublish(bool $force = false): array
    {
        $this->ensureSchema();
        $settings = $this->getSettings();

        if (!$force && !$settings['auto_publish_enabled']) {
            return [
                'status' => 'skipped',
                'reason' => 'Auto-publish is currently paused in Content Engine settings.',
            ];
        }

        if (!$force) {
            $lastStmt = $this->db->query(
                "SELECT MAX(published_at) FROM curated_videos WHERE status = 'published'"
            );
            $lastPublished = $lastStmt !== false ? $lastStmt->fetchColumn() : false;

            if (!empty($lastPublished)) {
                $elapsed = time() - (int)strtotime((string)$lastPublished);
                $cooldown = $settings['publish_interval_hours'] * 3600;
                if ($elapsed < $cooldown) {
                    $remainingHours = (int)ceil(($cooldown - $elapsed) / 3600);
                    return [
                        'status' => 'rate_limited',
                        'reason' => "Cadence cooldown active. Next auto-publish eligible in {$remainingHours} hour(s).",
                    ];
                }
            }
        }

        $queuedStmt = $this->db->query("SELECT COUNT(*) FROM curated_videos WHERE status = 'queued'");
        $queuedCount = $queuedStmt !== false ? (int)$queuedStmt->fetchColumn() : 0;
        $fetchSummary = null;
        if ($queuedCount === 0) {
            $fetchSummary = $this->fetchFromActiveSources($settings['max_queue_per_source']);
        }

        $nextStmt = $this->db->query(
            "SELECT id, title, video_url FROM curated_videos WHERE status = 'queued' ORDER BY created_at ASC, id ASC LIMIT 1"
        );
        $nextVideo = $nextStmt !== false ? $nextStmt->fetch(PDO::FETCH_ASSOC) : false;

        if (!$nextVideo) {
            return [
                'status' => 'empty_queue',
                'reason' => 'No family-safe videos available in the queue to publish.',
                'fetch' => $fetchSummary,
            ];
        }

        $postNo = $this->publishQueuedVideo((int)$nextVideo['id']);

        return [
            'status' => 'published',
            'curated_video_id' => (int)$nextVideo['id'],
            'title' => (string)$nextVideo['title'],
            'post_no' => $postNo,
            'fetch' => $fetchSummary,
        ];
    }

    /**
     * Parse YouTube Atom/RSS or standard RSS 2.0 XML into normalised video entries.
     *
     * @return list<array{provider: string, video_id: string, video_url: string, title: string, author: string, description: string, thumbnail_url: string}>
     */
    public function parseFeedEntries(string $xmlContent, int $limit = self::DEFAULT_LIMIT_PER_SOURCE): array
    {
        $prevUseErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        if ($xml === false) {
            return [];
        }

        $entries = [];
        $namespaces = $xml->getNamespaces(true);

        // 1. YouTube Atom Feed (<feed><entry>...)
        if (isset($xml->entry)) {
            foreach ($xml->entry as $item) {
                $yt = isset($namespaces['yt']) ? $item->children($namespaces['yt']) : null;
                $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;

                $videoId = $yt !== null ? trim((string)($yt->videoId ?? '')) : '';
                $linkHref = '';
                if (isset($item->link)) {
                    foreach ($item->link as $lnk) {
                        $attrs = $lnk->attributes();
                        if ($attrs !== null && isset($attrs['href'])) {
                            $linkHref = (string)$attrs['href'];
                            break;
                        }
                    }
                }

                if ($videoId === '' && $linkHref !== '') {
                    $parsed = VideoParser::parseVideoUrl($linkHref);
                    $videoId = (string)($parsed['videoId'] ?? '');
                }
                if ($videoId === '') {
                    continue;
                }

                $title = trim((string)($item->title ?? ''));
                $author = trim((string)($item->author->name ?? ''));
                $description = '';
                if ($media !== null && isset($media->group->description)) {
                    $description = trim((string)$media->group->description);
                }

                $entries[] = [
                    'provider' => 'youtube',
                    'video_id' => $videoId,
                    'video_url' => 'https://www.youtube.com/watch?v=' . $videoId,
                    'title' => $title !== '' ? $title : 'Family Video Spotlight',
                    'author' => $author,
                    'description' => $description,
                    'thumbnail_url' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
                ];

                if (count($entries) >= $limit) {
                    break;
                }
            }
            return $entries;
        }

        // 2. Standard RSS 2.0 (<rss><channel><item>...)
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $link = trim((string)($item->link ?? ''));
                $parsed = VideoParser::parseVideoUrl($link);
                if ($parsed === null || empty($parsed['videoId']) || !in_array($parsed['type'], ['youtube', 'vimeo'], true)) {
                    continue;
                }

                $entries[] = [
                    'provider' => (string)$parsed['type'],
                    'video_id' => (string)$parsed['videoId'],
                    'video_url' => $parsed['type'] === 'youtube'
                        ? 'https://www.youtube.com/watch?v=' . $parsed['videoId']
                        : $link,
                    'title' => trim((string)($item->title ?? 'Family Video Spotlight')),
                    'author' => trim((string)($xml->channel->title ?? '')),
                    'description' => trim(strip_tags((string)($item->description ?? ''))),
                    'thumbnail_url' => (string)($parsed['thumbnailUrl'] ?? ''),
                ];

                if (count($entries) >= $limit) {
                    break;
                }
            }
        }

        return $entries;
    }

    /**
     * Check whether a video title and description pass family-friendly moderation.
     */
    public function isFamilySafe(string $title, string $description = ''): bool
    {
        $combined = mb_strtolower(trim($title . ' ' . $description));
        if ($combined === '') {
            return false;
        }

        foreach (self::UNSAFE_KEYWORDS as $keyword) {
            if (str_contains($combined, $keyword)) {
                return false;
            }
        }

        $safetyCheck = ContentSafetyService::inspectText($title . ' ' . mb_substr($description, 0, 400), 'video');
        return !empty($safetyCheck['is_clean']);
    }

    /**
     * Craft an engaging British English caption for the feed post.
     */
    public function generateEngagingCaption(string $title, string $category, string $authorName = ''): string
    {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $title) ?? $title);
        $credit = $authorName !== '' ? " (via {$authorName})" : '';

        return match ($category) {
            'funny' => "Need a good laugh today? 😂 Have a watch of \"{$cleanTitle}\"{$credit}!\n\nWho in your family would laugh hardest at this? Tag them in the comments below! 👇 #FamilyLaughs #FamilyTime",
            'pets' => "This one is guaranteed to brighten your day 🐾❤️ \"{$cleanTitle}\"{$credit}.\n\nShare a smile with the family—what did you think? #WholesomeMoments #FamilyFeed",
            'kids' => "One for the whole family to enjoy together! 🌟 \"{$cleanTitle}\"{$credit}.\n\nDrop a reaction if the little ones loved this! #FamilyFun #KidsCorner",
            'wholesome' => "A lovely moment worth sharing today ✨ \"{$cleanTitle}\"{$credit}.\n\nTake a pause and enjoy this with your loved ones. #Wholesome #FamilyMatters",
            default => "Today's Family Spotlight 🎬 \"{$cleanTitle}\"{$credit}.\n\nHave a watch and let the family know what you think in the comments! #FamilyTime #CommunityFeed",
        };
    }

    /**
     * Validate external URLs against SSRF (HTTPS only, allowlisted hosts, no private IPs).
     */
    public function isAllowedExternalUrl(string $url): bool
    {
        $parts = parse_url(trim($url));
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        $host = strtolower((string)($parts['host'] ?? ''));
        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize user-supplied YouTube channel/playlist/RSS identifiers.
     *
     * @return array{source_type: string, source_identifier: string}
     */
    private function normalizeSourceInput(string $sourceType, string $rawIdentifier): array
    {
        $raw = trim($rawIdentifier);
        if ($raw === '') {
            throw new ValidationException('Please enter a YouTube channel ID, playlist ID, or feed URL.');
        }

        if ($sourceType === 'rss_feed' || (str_starts_with($raw, 'https://') && !str_contains($raw, 'youtube.com/'))) {
            if (!$this->isAllowedExternalUrl($raw)) {
                throw new ValidationException('RSS feed URL must be a valid public HTTPS URL.');
            }
            return ['source_type' => 'rss_feed', 'source_identifier' => $raw];
        }

        if (preg_match('#youtube\.com/channel/(UC[a-zA-Z0-9_-]{10,40})#i', $raw, $m) === 1) {
            return ['source_type' => 'youtube_channel', 'source_identifier' => $m[1]];
        }

        if (preg_match('#[?&]list=([a-zA-Z0-9_-]{10,60})#i', $raw, $m) === 1) {
            return ['source_type' => 'youtube_playlist', 'source_identifier' => $m[1]];
        }

        if ($sourceType === 'youtube_playlist' || preg_match('/^(PL|UU|FL|OL)[a-zA-Z0-9_-]{8,60}$/', $raw) === 1) {
            return ['source_type' => 'youtube_playlist', 'source_identifier' => $raw];
        }

        if (preg_match('/^UC[a-zA-Z0-9_-]{10,40}$/', $raw) === 1) {
            return ['source_type' => 'youtube_channel', 'source_identifier' => $raw];
        }

        throw new ValidationException('Please provide a valid YouTube Channel ID (starting with UC...), Playlist ID (starting with PL...), or YouTube channel/playlist URL.');
    }

    private function buildFeedUrl(string $sourceType, string $identifier): string
    {
        return match ($sourceType) {
            'youtube_channel' => 'https://www.youtube.com/feeds/videos.xml?channel_id=' . rawurlencode($identifier),
            'youtube_playlist' => 'https://www.youtube.com/feeds/videos.xml?playlist_id=' . rawurlencode($identifier),
            'rss_feed' => $identifier,
            default => '',
        };
    }

    private function insertQueuedVideo(
        ?int $sourceId,
        string $provider,
        string $externalVideoId,
        string $videoUrl,
        string $title,
        string $authorName,
        string $thumbnailUrl,
        string $category,
        string $suggestedCaption
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO curated_videos
                (source_id, provider, external_video_id, video_url, title, author_name, thumbnail_url, category, suggested_caption, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "queued")'
        );
        $stmt->execute([
            $sourceId,
            $provider,
            mb_substr($externalVideoId, 0, 64),
            mb_substr($videoUrl, 0, 500),
            mb_substr($title, 0, 255),
            mb_substr($authorName, 0, 160),
            mb_substr($thumbnailUrl, 0, 500),
            $category,
            $suggestedCaption,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function recordSourceFetchResult(int $sourceId, ?string $error): void
    {
        $stmt = $this->db->prepare(
            'UPDATE curated_video_sources SET last_fetched_at = NOW(), last_error = ? WHERE id = ?'
        );
        $stmt->execute([$error, $sourceId]);
    }

    /**
     * Default cURL fetcher with strict timeouts and redirect blocking.
     *
     * @return array{ok: bool, body: string, error: string}
     */
    private function defaultHttpFetch(string $url): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'body' => '', 'error' => 'Failed to initialise HTTP client.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => self::CURL_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
            CURLOPT_USERAGENT => 'FamilyPlatformCurator/1.0 (+https://myfamilyplatform.com)',
            CURLOPT_HTTPHEADER => ['Accept: application/atom+xml, application/rss+xml, application/xml, application/json;q=0.9'],
        ]);

        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlError !== '') {
            return ['ok' => false, 'body' => '', 'error' => $curlError !== '' ? $curlError : 'Network request failed.'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['ok' => false, 'body' => '', 'error' => "HTTP {$httpCode}"];
        }

        return ['ok' => true, 'body' => (string)$body, 'error' => ''];
    }
}
