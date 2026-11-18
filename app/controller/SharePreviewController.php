<?php

declare(strict_types=1);

namespace App\controller;

use App\services\ShareMetadataProtocol;
use PDO;
use Src\Db;

/**
 * SharePreviewController
 *
 * Public crawler-safe Open Graph & WhatsApp/Messages rich preview handler
 * for Daily Family Sparks (/spark/{sparkId} and /spark/{sparkId}/card.jpg).
 *
 * Privacy invariant:
 * Never exposes private family member names, replies, family codes, or private
 * archive photos to unauthenticated social crawlers.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class SharePreviewController
{
    /**
     * GET|HEAD /spark/{sparkId}
     */
    public function handleSparkLink(string $sparkId = ''): void
    {
        $cleanId = preg_replace('/[^A-Za-z0-9_-]/', '', trim($sparkId)) ?? '';
        if ($cleanId === '') {
            redirect('/member');
            return;
        }

        $meta = $this->resolveSparkPreviewMeta($cleanId);
        $isCrawler = ShareMetadataProtocol::isSocialCrawler()
            || isset($_GET['crawler'])
            || isset($_GET['preview']);

        $isJsonRequest = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isCrawler) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'success',
                    'meta' => ShareMetadataProtocol::buildMeta($meta),
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return;
            }

            view('public/sparkShareLanding', [
                'meta' => ShareMetadataProtocol::buildMeta($meta),
                'badgeLabel' => $meta['badge'],
                'shouldRedirect' => false,
                'targetRedirectUrl' => '/member',
            ]);
            return;
        }

        // Human visitor: route authenticated members straight to /member; guests to /register?redirect=/member
        if (!empty($_SESSION['id'])) {
            redirect('/member');
            return;
        }

        redirect('/register?redirect=' . rawurlencode('/member'));
    }

    /**
     * GET|HEAD /spark/{sparkId}/card.jpg
     * Generates a 1200x630 JPEG (<300 KB) preview card for WhatsApp/Messages.
     */
    public function sparkCardImage(string $sparkId = ''): void
    {
        $cleanId = preg_replace('/[^A-Za-z0-9_-]/', '', trim($sparkId)) ?? '';
        $meta = $cleanId !== '' ? $this->resolveSparkPreviewMeta($cleanId) : $this->defaultSparkMeta('default');

        $jpeg = ShareMetadataProtocol::renderPreviewCardJpeg(
            badge: $meta['badge'],
            title: $meta['card_title'],
            subtitle: $meta['description']
        );

        if ($jpeg === '') {
            http_response_code(404);
            return;
        }

        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($jpeg));
        header('Cache-Control: public, max-age=86400');
        echo $jpeg;
    }

    /**
     * @return array{
     *   badge: string,
     *   title: string,
     *   card_title: string,
     *   description: string,
     *   image: string,
     *   url: string,
     *   type: string
     * }
     */
    private function resolveSparkPreviewMeta(string $sparkId): array
    {
        try {
            $db = Db::connect2();
            $stmt = $db->prepare(
                "SELECT id, spark_type, title
                 FROM daily_family_sparks
                 WHERE id = ?
                 LIMIT 1"
            );
            $stmt->execute([$sparkId]);
            /** @var array<string, mixed>|false $row */
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $this->defaultSparkMeta($sparkId);
            }

            $type = (string)($row['spark_type'] ?? 'poll');
            $badge = match ($type) {
                'game' => 'FAMILY GAME',
                'poll' => 'QUICK POLL',
                'heritage' => 'HERITAGE SPARK',
                'archive' => 'ARCHIVE FLASHBACK',
                'member_question' => 'FAMILY QUESTION',
                default => 'DAILY FAMILY SPARK',
            };

            $rawTitle = trim(strip_tags((string)($row['title'] ?? 'Daily Family Spark')));
            $safeTitle = ($type === 'member_question' || $rawTitle === '')
                ? 'Daily Family Spark'
                : $rawTitle;

            $description = match ($type) {
                'archive' => 'A cherished memory from our family archive is live. Open FamilyPlatform to guess who & where or share the story!',
                'heritage' => 'Join today’s Family Heritage Spark on FamilyPlatform—share your story and preserve memories across generations.',
                'game', 'poll' => 'A new Family Spark poll is live in our Family Hub! Tap to cast your vote and see everyone’s answers.',
                default => 'A new Daily Family Spark is live in our Family Hub! Tap to cast your vote or share your answer.',
            };

            $baseUrl = ShareMetadataProtocol::baseUrl();
            $shareUrl = $baseUrl . '/spark/' . rawurlencode($sparkId);
            $cardImg = $baseUrl . '/spark/' . rawurlencode($sparkId) . '/card.jpg?v=' . substr(md5($sparkId . $safeTitle), 0, 8);

            return [
                'badge' => $badge,
                'title' => "{$safeTitle} — FamilyPlatform",
                'card_title' => $safeTitle,
                'description' => $description,
                'image' => $cardImg,
                'url' => $shareUrl,
                'type' => 'article',
            ];
        } catch (\Throwable $e) {
            return $this->defaultSparkMeta($sparkId);
        }
    }

    /**
     * @return array{
     *   badge: string,
     *   title: string,
     *   card_title: string,
     *   description: string,
     *   image: string,
     *   url: string,
     *   type: string
     * }
     */
    private function defaultSparkMeta(string $sparkId): array
    {
        $baseUrl = ShareMetadataProtocol::baseUrl();
        return [
            'badge' => 'DAILY FAMILY SPARK',
            'title' => 'Daily Family Spark — FamilyPlatform',
            'card_title' => 'Daily Family Spark',
            'description' => 'A new Daily Family Spark is live in our Family Hub! Tap to cast your vote or share your answer.',
            'image' => $baseUrl . '/spark/' . rawurlencode($sparkId) . '/card.jpg',
            'url' => $baseUrl . '/spark/' . rawurlencode($sparkId),
            'type' => 'website',
        ];
    }
}
