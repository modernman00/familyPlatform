<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\functionality\SendEmailFunctionality;
use App\services\NotificationOrchestrator;
use PDO;
use Throwable;

/**
 * Automated Multi-Touch Onboarding & Engagement Lifecycle Engine
 *
 * Implements automated follow-up across Email, WebPush, and In-App Bell channels:
 * - Day 1 (24h post-registration): Lineage kickstart (prompt to add parents & siblings)
 * - Day 3 (72h post-registration): Living voice capsule oral history invitation
 * - Day 7 (7d post-registration): WhatsApp viral referral flywheel
 *
 * All touchpoints are 100% idempotent via the user_lifecycle_events table.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class OnboardingFollowupService
{
    public const EVENT_DAY1_TREE = 'welcome_day1_tree';
    public const EVENT_DAY3_VOICE = 'voice_capsule_day3';
    public const EVENT_DAY7_REFERRAL = 'referral_flywheel_day7';

    /**
     * Run all automated onboarding touchpoints across eligible cohorts.
     *
     * @param bool $dryRun If true, computes matches without sending emails or push alerts
     * @param string|null $forceUserId If set, executes for this specific user regardless of registration age
     * @return array{day1_processed: int, day3_processed: int, day7_processed: int, dry_run: bool}
     */
    public static function runAllTouchpoints(bool $dryRun = false, ?string $forceUserId = null): array
    {
        $d1 = self::processDay1TreeKickstart($dryRun, $forceUserId);
        $d3 = self::processDay3VoiceCapsule($dryRun, $forceUserId);
        $d7 = self::processDay7ReferralFlywheel($dryRun, $forceUserId);

        return [
            'day1_processed' => $d1,
            'day3_processed' => $d3,
            'day7_processed' => $d7,
            'dry_run'        => $dryRun
        ];
    }

    /**
     * Touchpoint 1: Day 1 (24h post-registration) Tree Building Kickstart
     */
    public static function processDay1TreeKickstart(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();

        // 1. Find candidates: registered >= 24h ago, active, not yet sent day 1 event
        $params = [];
        $timeCondition = "a.created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        if ($forceUserId !== null && $forceUserId !== '') {
            $timeCondition = "a.id = ?";
            $params[] = $forceUserId;
        }

        $sql = "
            SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
            FROM account a
            JOIN personal p ON a.id = p.id
            LEFT JOIN contact c ON a.id = c.id
            LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = '" . self::EVENT_DAY1_TREE . "'
            WHERE a.deleted_at IS NULL
              AND a.status = 'approved'
              AND {$timeCondition}
              AND e.id IS NULL
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $users */
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed = 0;

        foreach ($users as $user) {
            $userId = (string)$user['id'];
            $famCode = (string)($user['famCode'] ?? '');
            $email = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user already built their tree (more than 1 node exists)
            $nStmt = $db->prepare("SELECT COUNT(*) FROM family_nodes WHERE family_code = ?");
            $nStmt->execute([$famCode]);
            $nodeCount = (int)$nStmt->fetchColumn();

            // If user already added parents or relatives, mark event as satisfied and skip
            if ($nodeCount > 1) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY1_TREE, 'skipped_already_built', [
                    'node_count' => $nodeCount
                ]);
                continue;
            }

            if (!$dryRun) {
                // A. Send Day 1 Email
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day1_tree_kickstart',
                        "{$firstName}, your family tree is waiting — preserve your heritage today",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 1 Email error for user {$userId}: " . $e->getMessage());
                }

                // B. Dispatch In-App & WebPush Notification
                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'high',
                        title: '🌳 Your Family Tree is Waiting',
                        body: "Hello {$firstName}! Add your parents and siblings in under 60 seconds to unlock your 6-generation interactive family tree.",
                        actionUrl: '/organogram',
                        tag: 'onboarding-day1',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY1_TREE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 1 Push error for user {$userId}: " . $e->getMessage());
                }

                // C. Record Idempotent Event
                self::recordLifecycleEvent($userId, self::EVENT_DAY1_TREE, 'omni', [
                    'email'      => $email,
                    'node_count' => $nodeCount
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 2: Day 3 (72h post-registration) Living Voice Capsule Invitation
     */
    public static function processDay3VoiceCapsule(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();

        $params = [];
        $timeCondition = "a.created_at <= DATE_SUB(NOW(), INTERVAL 72 HOUR) AND a.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
        if ($forceUserId !== null && $forceUserId !== '') {
            $timeCondition = "a.id = ?";
            $params[] = $forceUserId;
        }

        $sql = "
            SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
            FROM account a
            JOIN personal p ON a.id = p.id
            LEFT JOIN contact c ON a.id = c.id
            LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = '" . self::EVENT_DAY3_VOICE . "'
            WHERE a.deleted_at IS NULL
              AND a.status = 'approved'
              AND {$timeCondition}
              AND e.id IS NULL
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $users */
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed = 0;

        foreach ($users as $user) {
            $userId = (string)$user['id'];
            $famCode = (string)($user['famCode'] ?? '');
            $email = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user or family already uploaded a voice capsule
            $vStmt = $db->prepare("
                SELECT COUNT(*) FROM family_nodes 
                WHERE family_code = ? 
                  AND voice_capsule_url IS NOT NULL 
                  AND voice_capsule_url != ''
            ");
            $vStmt->execute([$famCode]);
            $voiceCount = (int)$vStmt->fetchColumn();

            if ($voiceCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY3_VOICE, 'skipped_already_recorded', [
                    'voice_count' => $voiceCount
                ]);
                continue;
            }

            if (!$dryRun) {
                // A. Send Day 3 Email
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day3_voice_capsule',
                        "🎙️ The sound of family: Preserve your elders' voices forever",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 3 Email error for user {$userId}: " . $e->getMessage());
                }

                // B. Dispatch In-App & WebPush Notification
                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🎙️ Preserve the Voices of Your Elders',
                        body: "Did you know you can record living voice capsules on your tree? Capture stories and memories from parents before they fade.",
                        actionUrl: '/organogram',
                        tag: 'onboarding-day3',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY3_VOICE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 3 Push error for user {$userId}: " . $e->getMessage());
                }

                // C. Record Idempotent Event
                self::recordLifecycleEvent($userId, self::EVENT_DAY3_VOICE, 'omni', [
                    'email'       => $email,
                    'voice_count' => $voiceCount
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 3: Day 7 (7d post-registration) Referral Flywheel (WhatsApp Sibling Invite)
     */
    public static function processDay7ReferralFlywheel(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();

        $params = [];
        $timeCondition = "a.created_at <= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        if ($forceUserId !== null && $forceUserId !== '') {
            $timeCondition = "a.id = ?";
            $params[] = $forceUserId;
        }

        $sql = "
            SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
            FROM account a
            JOIN personal p ON a.id = p.id
            LEFT JOIN contact c ON a.id = c.id
            LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = '" . self::EVENT_DAY7_REFERRAL . "'
            WHERE a.deleted_at IS NULL
              AND a.status = 'approved'
              AND {$timeCondition}
              AND e.id IS NULL
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $users */
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed = 0;

        foreach ($users as $user) {
            $userId = (string)$user['id'];
            $famCode = (string)($user['famCode'] ?? '');
            $email = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check family size: only prompt if <= 3 members have registered with this family code
            $mStmt = $db->prepare("SELECT COUNT(*) FROM personal WHERE famCode = ?");
            $mStmt->execute([$famCode]);
            $memberCount = (int)$mStmt->fetchColumn();

            if ($memberCount > 3) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY7_REFERRAL, 'skipped_already_populated', [
                    'member_count' => $memberCount
                ]);
                continue;
            }

            if (!$dryRun) {
                // A. Send Day 7 Email
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day7_referral_flywheel',
                        "✨ Bring your siblings and cousins home: Expand your family tree",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 7 Email error for user {$userId}: " . $e->getMessage());
                }

                // B. Dispatch In-App & WebPush Notification
                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '✨ Connect Your Kin on FamilyPlatform',
                        body: "Invite your brothers, sisters, and cousins using Family Code {$famCode}. When they join, our auto-matching engine links them directly to you!",
                        actionUrl: '/profilePage',
                        tag: 'onboarding-day7',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY7_REFERRAL]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 7 Push error for user {$userId}: " . $e->getMessage());
                }

                // C. Record Idempotent Event
                self::recordLifecycleEvent($userId, self::EVENT_DAY7_REFERRAL, 'omni', [
                    'email'        => $email,
                    'member_count' => $memberCount
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Record a dispatched lifecycle event in database for strict idempotency
     *
     * @param array<string, mixed> $metadata
     */
    private static function recordLifecycleEvent(
        string $userId,
        string $eventType,
        string $channel,
        array $metadata = []
    ): void {
        try {
            $db = Db::connect2();
            $stmt = $db->prepare("
                INSERT INTO user_lifecycle_events (user_id, event_type, channel, metadata, dispatched_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $metaJson = json_encode($metadata) ?: '{}';
            $stmt->execute([$userId, $eventType, $channel, $metaJson]);
        } catch (Throwable $e) {
            error_log("[OnboardingFollowupService] Failed to record lifecycle event: " . $e->getMessage());
        }
    }
}
