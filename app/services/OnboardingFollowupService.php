<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\functionality\SendEmailFunctionality;
use App\services\NotificationOrchestrator;
use PDO;
use Throwable;

/**
 * Automated 60-Day Multi-Touch Onboarding & Heritage Engagement Engine
 *
 * Implements automated follow-up across Email, WebPush, and In-App Bell channels:
 * - Day 1 (24h post-registration): Lineage kickstart (prompt to add parents & siblings)
 * - Day 3 (72h post-registration): Living voice capsule oral history invitation
 * - Day 7 (7d post-registration): WhatsApp viral referral flywheel
 * - Day 14 (14d post-registration): Heritage roots & ancestral origin quests
 * - Day 21 (21d post-registration): Treasured family recipes & private journals
 * - Day 28 (28d post-registration): Sunday heritage trivia contest kick-off
 * - Day 35 (35d post-registration): Digital family time capsules for future milestones
 * - Day 42 (42d post-registration): 60-second elder video reels & wisdom preservation
 * - Day 49 (49d post-registration): Family reunion, shared events & celebration calendar
 * - Day 60 (60d post-registration): 60-Day dynasty celebration & printable family tree
 *
 * All touchpoints are 100% idempotent via the user_lifecycle_events table.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class OnboardingFollowupService
{
    public const EVENT_DAY1_TREE     = 'welcome_day1_tree';
    public const EVENT_DAY3_VOICE    = 'voice_capsule_day3';
    public const EVENT_DAY7_REFERRAL = 'referral_flywheel_day7';
    public const EVENT_DAY14_ROOTS   = 'heritage_roots_day14';
    public const EVENT_DAY21_RECIPE  = 'family_recipe_day21';
    public const EVENT_DAY28_TRIVIA  = 'sunday_trivia_day28';
    public const EVENT_DAY35_CAPSULE = 'time_capsule_day35';
    public const EVENT_DAY42_ELDER   = 'elder_voices_day42';
    public const EVENT_DAY49_REUNION = 'reunion_planner_day49';
    public const EVENT_DAY60_DYNASTY = 'dynasty_snapshot_day60';

    // Inactivity & Dormancy Re-Engagement Lifecycle Events
    public const EVENT_INACTIVITY_TIER1_GHOST    = 'inactivity_tier1_one_name';
    public const EVENT_INACTIVITY_TIER2_DELEGATE = 'inactivity_tier2_delegate_copilot';
    public const EVENT_INACTIVITY_TIER3_BANTER   = 'inactivity_tier3_sunday_banter';
    public const EVENT_INACTIVITY_TIER4_DEEP     = 'inactivity_tier4_deep_dormant_magic';

    /**
     * Run all automated onboarding touchpoints across eligible cohorts.
     *
     * @param bool $dryRun If true, computes matches without sending emails or push alerts
     * @param string|null $forceUserId If set, executes for this specific user regardless of registration age
     * @return array{
     *     day1_processed: int,
     *     day3_processed: int,
     *     day7_processed: int,
     *     day14_processed: int,
     *     day21_processed: int,
     *     day28_processed: int,
     *     day35_processed: int,
     *     day42_processed: int,
     *     day49_processed: int,
     *     day60_processed: int,
     *     dry_run: bool
     * }
     */
    public static function runAllTouchpoints(bool $dryRun = false, ?string $forceUserId = null): array
    {
        $d1  = self::processDay1TreeKickstart($dryRun, $forceUserId);
        $d3  = self::processDay3VoiceCapsule($dryRun, $forceUserId);
        $d7  = self::processDay7ReferralFlywheel($dryRun, $forceUserId);
        $d14 = self::processDay14HeritageRoots($dryRun, $forceUserId);
        $d21 = self::processDay21FamilyRecipe($dryRun, $forceUserId);
        $d28 = self::processDay28FamilyTrivia($dryRun, $forceUserId);
        $d35 = self::processDay35TimeCapsule($dryRun, $forceUserId);
        $d42 = self::processDay42ElderVoices($dryRun, $forceUserId);
        $d49 = self::processDay49ReunionPlanner($dryRun, $forceUserId);
        $d60 = self::processDay60DynastySnapshot($dryRun, $forceUserId);

        // Process all 4 Inactivity Dormancy Tiers (Ghost, Delegate, Banter, Deep Hibernation)
        $inactivity = self::runAllInactivityTouchpoints($dryRun, $forceUserId);

        return [
            'day1_processed'  => $d1,
            'day3_processed'  => $d3,
            'day7_processed'  => $d7,
            'day14_processed' => $d14,
            'day21_processed' => $d21,
            'day28_processed' => $d28,
            'day35_processed' => $d35,
            'day42_processed' => $d42,
            'day49_processed' => $d49,
            'day60_processed' => $d60,
            'tier1_ghost_processed'    => $inactivity['tier1_ghost_processed'],
            'tier2_delegate_processed' => $inactivity['tier2_delegate_processed'],
            'tier3_banter_processed'   => $inactivity['tier3_banter_processed'],
            'tier4_deep_processed'     => $inactivity['tier4_deep_processed'],
            'dry_run'         => $dryRun,
        ];
    }

    /**
     * Touchpoint 1: Day 1 (24h post-registration) Tree Building Kickstart
     */
    public static function processDay1TreeKickstart(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-7 days'));

        $users = self::fetchCandidates(self::EVENT_DAY1_TREE, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user already built their tree (more than 1 node exists)
            $nStmt = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ?');
            $nStmt->execute([$famCode]);
            $nodeCount = (int)$nStmt->fetchColumn();

            if ($nodeCount > 1) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY1_TREE, 'skipped_already_built', [
                    'node_count' => $nodeCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
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

                self::recordLifecycleEvent($userId, self::EVENT_DAY1_TREE, 'omni', [
                    'email'      => $email,
                    'node_count' => $nodeCount,
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
        $minDate = date('Y-m-d H:i:s', strtotime('-72 hours'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-14 days'));

        $users = self::fetchCandidates(self::EVENT_DAY3_VOICE, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            $vStmt = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND voice_capsule_url IS NOT NULL AND voice_capsule_url != ?');
            $vStmt->execute([$famCode, '']);
            $voiceCount = (int)$vStmt->fetchColumn();

            if ($voiceCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY3_VOICE, 'skipped_already_recorded', [
                    'voice_count' => $voiceCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
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

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🎙️ Preserve the Voices of Your Elders',
                        body: 'Did you know you can record living voice capsules on your tree? Capture stories and memories from parents before they fade.',
                        actionUrl: '/organogram',
                        tag: 'onboarding-day3',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY3_VOICE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 3 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY3_VOICE, 'omni', [
                    'email'       => $email,
                    'voice_count' => $voiceCount,
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
        $minDate = date('Y-m-d H:i:s', strtotime('-7 days'));

        $users = self::fetchCandidates(self::EVENT_DAY7_REFERRAL, $minDate, null, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            $mStmt = $db->prepare('SELECT COUNT(*) FROM personal WHERE famCode = ?');
            $mStmt->execute([$famCode]);
            $memberCount = (int)$mStmt->fetchColumn();

            if ($memberCount > 3) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY7_REFERRAL, 'skipped_already_populated', [
                    'member_count' => $memberCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day7_referral_flywheel',
                        '✨ Bring your siblings and cousins home: Expand your family tree',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 7 Email error for user {$userId}: " . $e->getMessage());
                }

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

                self::recordLifecycleEvent($userId, self::EVENT_DAY7_REFERRAL, 'omni', [
                    'email'        => $email,
                    'member_count' => $memberCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 4: Day 14 (Week 2) Heritage Roots & Ancestral Origin Quests
     */
    public static function processDay14HeritageRoots(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-14 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-28 days'));

        $users = self::fetchCandidates(self::EVENT_DAY14_ROOTS, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if family has documented ancestral origins
            $rStmt = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND birth_place IS NOT NULL AND birth_place != ?');
            $rStmt->execute([$famCode, '']);
            $rootsCount = (int)$rStmt->fetchColumn();

            if ($rootsCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY14_ROOTS, 'skipped_already_documented', [
                    'roots_count' => $rootsCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day14_heritage_roots',
                        'Where Did the Story Begin? Discover Your Roots',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 14 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🗺️ Where Did Your Family Story Begin?',
                        body: 'Document your parents or grandparents ancestral hometown to watch your heritage map light up with generations of history.',
                        actionUrl: '/organogram',
                        tag: 'onboarding-day14',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY14_ROOTS]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 14 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY14_ROOTS, 'omni', [
                    'email'       => $email,
                    'roots_count' => $rootsCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 5: Day 21 (Week 3) Treasured Family Recipes & Private Journals
     */
    public static function processDay21FamilyRecipe(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-21 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-35 days'));

        $users = self::fetchCandidates(self::EVENT_DAY21_RECIPE, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if family has created any family journals
            $jStmt = $db->prepare('SELECT COUNT(*) FROM family_journals WHERE family_code = ?');
            $jStmt->execute([$famCode]);
            $journalCount = (int)$jStmt->fetchColumn();

            if ($journalCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY21_RECIPE, 'skipped_already_journaled', [
                    'journal_count' => $journalCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day21_family_recipe',
                        'What Dish Defines Sunday Dinner in Your Home?',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 21 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🍲 What Dish Defines Sunday Dinner in Your Home?',
                        body: 'Preserve secret recipes, festive holiday dishes, and family cooking traditions inside your private Family Journal.',
                        actionUrl: '/member/journal',
                        tag: 'onboarding-day21',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY21_RECIPE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 21 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY21_RECIPE, 'omni', [
                    'email'         => $email,
                    'journal_count' => $journalCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 6: Day 28 (Week 4) Sunday Heritage Trivia Contest Kick-off
     */
    public static function processDay28FamilyTrivia(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-28 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-42 days'));

        $users = self::fetchCandidates(self::EVENT_DAY28_TRIVIA, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user has voted in trivia polls
            $tStmt = $db->prepare('SELECT COUNT(*) FROM post_poll_votes WHERE user_id = ?');
            $tStmt->execute([$userId]);
            $triviaVoteCount = (int)$tStmt->fetchColumn();

            if ($triviaVoteCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY28_TRIVIA, 'skipped_already_voted', [
                    'vote_count' => $triviaVoteCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                $activeTriviaPostNo = null;
                try {
                    $triviaStmt = $db->prepare(
                        "SELECT p.post_no
                         FROM post p
                         WHERE (p.postFamCode = ? OR REPLACE(p.postFamCode, '#', '') = REPLACE(?, '#', ''))
                           AND p.postMessage LIKE '%Weekly Family Trivia%'
                           AND p.date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         ORDER BY p.post_no DESC
                         LIMIT 1"
                    );
                    $triviaStmt->execute([$famCode, $famCode]);
                    $foundPostNo = $triviaStmt->fetchColumn();
                    if ($foundPostNo) {
                        $activeTriviaPostNo = (int)$foundPostNo;
                    }
                } catch (Throwable) {}

                $triviaActionUrl = $activeTriviaPostNo 
                    ? "/profilePage?post_no={$activeTriviaPostNo}#post{$activeTriviaPostNo}" 
                    : '/profilePage';

                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                        'post_no'   => $activeTriviaPostNo,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day28_family_trivia',
                        'Who Knows the Family Best? Sunday Heritage Trivia',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 28 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🧠 Who Knows Your Family Best? Sunday Trivia',
                        body: "Cast your vote in this week's private family trivia poll! See who claims the title of Family Historian.",
                        actionUrl: $triviaActionUrl,
                        tag: 'onboarding-day28',
                        familyCode: $famCode,
                        metadata: [
                            'event' => self::EVENT_DAY28_TRIVIA,
                            'post_no' => $activeTriviaPostNo,
                        ]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 28 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY28_TRIVIA, 'omni', [
                    'email'      => $email,
                    'vote_count' => $triviaVoteCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 7: Day 35 (Week 5) Digital Family Time Capsules for Future Milestones
     */
    public static function processDay35TimeCapsule(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-35 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-49 days'));

        $users = self::fetchCandidates(self::EVENT_DAY35_CAPSULE, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user has created a time capsule
            $cStmt = $db->prepare('SELECT COUNT(*) FROM family_time_capsules WHERE creator_id = ?');
            $cStmt->execute([$userId]);
            $capsuleCount = (int)$cStmt->fetchColumn();

            if ($capsuleCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY35_CAPSULE, 'skipped_already_created', [
                    'capsule_count' => $capsuleCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day35_time_capsule',
                        'Lock a Time Capsule: A Gift for Future Milestones',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 35 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '⏳ Send a Message Into the Future',
                        body: 'Lock a private photo, video, or letter into a digital time capsule to unlock on an 18th birthday or 25th wedding anniversary.',
                        actionUrl: '/member/time-capsule',
                        tag: 'onboarding-day35',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY35_CAPSULE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 35 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY35_CAPSULE, 'omni', [
                    'email'         => $email,
                    'capsule_count' => $capsuleCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 8: Day 42 (Week 6) 60-Second Elder Video Reels & Wisdom Preservation
     */
    public static function processDay42ElderVoices(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-42 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-56 days'));

        $users = self::fetchCandidates(self::EVENT_DAY42_ELDER, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if user has uploaded any video reel
            $reelStmt = $db->prepare('SELECT COUNT(*) FROM family_reels WHERE user_id = ?');
            $reelStmt->execute([$userId]);
            $reelCount = (int)$reelStmt->fetchColumn();

            if ($reelCount > 0) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY42_ELDER, 'skipped_already_posted', [
                    'reel_count' => $reelCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day42_elder_voices',
                        'A 60-Second Video That Will Be Priceless in 30 Years',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 42 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🎬 A 60-Second Video That Will Be Priceless in 30 Years',
                        body: 'Record a quick 60-second video of an elder or relative sharing their favourite life wisdom on Family Reels.',
                        actionUrl: '/member/reels',
                        tag: 'onboarding-day42',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY42_ELDER]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 42 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY42_ELDER, 'omni', [
                    'email'      => $email,
                    'reel_count' => $reelCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 9: Day 49 (Week 7) Family Reunion, Shared Events & Celebration Calendar
     */
    public static function processDay49ReunionPlanner(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $db = Db::connect2();
        $minDate = date('Y-m-d H:i:s', strtotime('-49 days'));
        $maxDate = date('Y-m-d H:i:s', strtotime('-63 days'));

        $users = self::fetchCandidates(self::EVENT_DAY49_REUNION, $minDate, $maxDate, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            // Check if family has created events
            $evStmt = $db->prepare('SELECT COUNT(*) FROM events WHERE eventCode = ? AND deleted_at IS NULL');
            $evStmt->execute([$famCode]);
            $eventCount = (int)$evStmt->fetchColumn();

            if ($eventCount > 1) {
                self::recordLifecycleEvent($userId, self::EVENT_DAY49_REUNION, 'skipped_already_planned', [
                    'event_count' => $eventCount,
                ]);
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day49_reunion_planner',
                        'When is the Family Gathering Next? Shared Celebrations Calendar',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 49 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '📅 When is the Family Gathering Next?',
                        body: 'Plan your next family reunion, holiday lunch, or birthday gathering with automated RSVPs on your shared calendar.',
                        actionUrl: '/member/event',
                        tag: 'onboarding-day49',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY49_REUNION]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 49 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY49_REUNION, 'omni', [
                    'email'       => $email,
                    'event_count' => $eventCount,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Touchpoint 10: Day 60 (2 Months) 60-Day Dynasty Celebration & Milestone Snapshot
     */
    public static function processDay60DynastySnapshot(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $minDate = date('Y-m-d H:i:s', strtotime('-60 days'));

        $users = self::fetchCandidates(self::EVENT_DAY60_DYNASTY, $minDate, null, $forceUserId);
        $processed = 0;

        foreach ($users as $user) {
            $userId    = (string)$user['id'];
            $famCode   = (string)($user['famCode'] ?? '');
            $email     = (string)($user['email'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            if (!$dryRun) {
                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/day60_dynasty_snapshot',
                        '🎉 60 Days of Heritage: Look at What You Have Built!',
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 60 Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'high',
                        title: '🎉 60 Days of Heritage: Your Dynasty Milestone',
                        body: 'Look at what you have built! Review your living family tree, export your high-resolution chart, and celebrate your heritage.',
                        actionUrl: '/organogram',
                        tag: 'onboarding-day60',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_DAY60_DYNASTY]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Day 60 Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_DAY60_DYNASTY, 'omni', [
                    'email' => $email,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Fetch candidates for a specific lifecycle event safely and without variable interpolation in SQL.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function fetchCandidates(
        string $eventType,
        string $minAgeDate,
        ?string $maxAgeDate = null,
        ?string $forceUserId = null
    ): array {
        $db = Db::connect2();

        if ($forceUserId !== null && $forceUserId !== '') {
            $sql = '
                SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
                FROM account a
                JOIN personal p ON a.id = p.id
                LEFT JOIN contact c ON a.id = c.id
                LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
                WHERE a.deleted_at IS NULL
                  AND a.status IN (\'active\', \'approved\', \'verified\')
                  AND a.id = ?
                  AND e.id IS NULL
            ';
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventType, $forceUserId]);
            /** @var array<int, array<string, mixed>> $res */
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }

        if ($maxAgeDate !== null) {
            $sql = '
                SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
                FROM account a
                JOIN personal p ON a.id = p.id
                LEFT JOIN contact c ON a.id = c.id
                LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
                WHERE a.deleted_at IS NULL
                  AND a.status IN (\'active\', \'approved\', \'verified\')
                  AND a.created_at <= ?
                  AND a.created_at >= ?
                  AND e.id IS NULL
            ';
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventType, $minAgeDate, $maxAgeDate]);
            /** @var array<int, array<string, mixed>> $res */
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }

        $sql = '
            SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode
            FROM account a
            JOIN personal p ON a.id = p.id
            LEFT JOIN contact c ON a.id = c.id
            LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
            WHERE a.deleted_at IS NULL
              AND a.status IN (\'active\', \'approved\', \'verified\')
              AND a.created_at <= ?
              AND e.id IS NULL
        ';
        $stmt = $db->prepare($sql);
        $stmt->execute([$eventType, $minAgeDate]);
        /** @var array<int, array<string, mixed>> $res */
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res;
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
            $stmt = $db->prepare('
                INSERT INTO user_lifecycle_events (user_id, event_type, channel, metadata, dispatched_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $metaJson = json_encode($metadata) ?: '{}';
            $stmt->execute([$userId, $eventType, $channel, $metaJson]);
        } catch (Throwable $e) {
            error_log("[OnboardingFollowupService] Failed to record lifecycle event: " . $e->getMessage());
        }
    }

    /**
     * Run all automated inactivity & dormancy re-engagement touchpoints.
     *
     * @param bool $dryRun If true, computes matches without sending emails or push alerts
     * @param string|null $forceUserId If set, executes for this specific user
     * @return array{
     *     tier1_ghost_processed: int,
     *     tier2_delegate_processed: int,
     *     tier3_banter_processed: int,
     *     tier4_deep_processed: int,
     *     dry_run: bool
     * }
     */
    public static function runAllInactivityTouchpoints(bool $dryRun = false, ?string $forceUserId = null): array
    {
        $t1 = self::processInactivityTier1Ghost($dryRun, $forceUserId);
        $t2 = self::processInactivityTier2Delegate($dryRun, $forceUserId);
        $t3 = self::processInactivityTier3SundayBanter($dryRun, $forceUserId);
        $t4 = self::processInactivityTier4DeepDormant($dryRun, $forceUserId);

        return [
            'tier1_ghost_processed'    => $t1,
            'tier2_delegate_processed' => $t2,
            'tier3_banter_processed'   => $t3,
            'tier4_deep_processed'     => $t4,
            'dry_run'                  => $dryRun,
        ];
    }

    /**
     * Inactivity Tier 1: Ghost Pioneers (48h–7d inactive, 0 nodes, 0 posts)
     * Micro-Action: Start with just one name
     */
    public static function processInactivityTier1Ghost(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $candidates = InactivityScoringService::fetchCandidatesForTier(
            self::EVENT_INACTIVITY_TIER1_GHOST,
            $forceUserId
        );
        $processed = 0;

        foreach ($candidates as $user) {
            $userId    = (string)$user['id'];
            $email     = (string)($user['email'] ?? '');
            $famCode   = (string)($user['famCode'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            if (!$dryRun) {
                $magicUrl = MagicAuthService::generateMagicUrl(
                    $userId,
                    '/organogram?action=add_first',
                    72,
                    ['campaign' => self::EVENT_INACTIVITY_TIER1_GHOST]
                );

                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                        'magicUrl'  => $magicUrl,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/inactivity_tier1_one_name',
                        "{$firstName}, start with just one name — your family sanctuary awaits",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 1 Inactivity Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'high',
                        title: '🌱 Start with Just One Name',
                        body: "Hello {$firstName}! You do not need to map four generations today. Add just one person you cherish in 15 seconds.",
                        actionUrl: '/organogram?action=add_first',
                        tag: 'inactivity-tier1',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_INACTIVITY_TIER1_GHOST]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 1 Inactivity Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_INACTIVITY_TIER1_GHOST, 'omni', [
                    'email'    => $email,
                    'fam_code' => $famCode,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Inactivity Tier 2: Stalled Explorers (7d–14d inactive, <= 1 node, 0 posts)
     * Micro-Action: WhatsApp Co-Pilot Delegation
     */
    public static function processInactivityTier2Delegate(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $candidates = InactivityScoringService::fetchCandidatesForTier(
            self::EVENT_INACTIVITY_TIER2_DELEGATE,
            $forceUserId
        );
        $processed = 0;

        foreach ($candidates as $user) {
            $userId    = (string)$user['id'];
            $email     = (string)($user['email'] ?? '');
            $famCode   = (string)($user['famCode'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            if (!$dryRun) {
                $magicUrl = MagicAuthService::generateMagicUrl(
                    $userId,
                    '/organogram',
                    72,
                    ['campaign' => self::EVENT_INACTIVITY_TIER2_DELEGATE]
                );

                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                        'magicUrl'  => $magicUrl,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/inactivity_tier2_delegate_copilot',
                        "Too busy to build your family tree alone? Delegate the torch",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 2 Inactivity Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'medium',
                        title: '🤝 Invite a Family Co-Pilot',
                        body: 'Preserving family history should not be a solo job. Invite a sibling or parent on WhatsApp to help build your tree.',
                        actionUrl: '/organogram',
                        tag: 'inactivity-tier2',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_INACTIVITY_TIER2_DELEGATE]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 2 Inactivity Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_INACTIVITY_TIER2_DELEGATE, 'omni', [
                    'email'    => $email,
                    'fam_code' => $famCode,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Inactivity Tier 3: Dormant Banter (14d–30d inactive)
     * Micro-Action: Lighthearted Sunday Banter Poll
     */
    public static function processInactivityTier3SundayBanter(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $candidates = InactivityScoringService::fetchCandidatesForTier(
            self::EVENT_INACTIVITY_TIER3_BANTER,
            $forceUserId
        );
        $processed = 0;

        foreach ($candidates as $user) {
            $userId    = (string)$user['id'];
            $email     = (string)($user['email'] ?? '');
            $famCode   = (string)($user['famCode'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            if (!$dryRun) {
                $magicUrl = MagicAuthService::generateMagicUrl(
                    $userId,
                    '/profilePage',
                    72,
                    ['campaign' => self::EVENT_INACTIVITY_TIER3_BANTER]
                );

                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                        'magicUrl'  => $magicUrl,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/inactivity_tier3_sunday_banter',
                        "Quick question: Who actually makes the best Sunday roast in your family?",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 3 Inactivity Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'social',
                        priority: 'medium',
                        title: '🍗 Family Banter Time!',
                        body: 'Who is the best cook in your family? Settle the debate on this week\'s family poll.',
                        actionUrl: '/profilePage',
                        tag: 'inactivity-tier3',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_INACTIVITY_TIER3_BANTER]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 3 Inactivity Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_INACTIVITY_TIER3_BANTER, 'omni', [
                    'email'    => $email,
                    'fam_code' => $famCode,
                ]);
            }

            $processed++;
        }

        return $processed;
    }

    /**
     * Inactivity Tier 4: Deep Hibernation (30d+ inactive)
     * Micro-Action: Loss Aversion & 1-Click Ephemeral Magic Link
     */
    public static function processInactivityTier4DeepDormant(bool $dryRun = false, ?string $forceUserId = null): int
    {
        $candidates = InactivityScoringService::fetchCandidatesForTier(
            self::EVENT_INACTIVITY_TIER4_DEEP,
            $forceUserId
        );
        $processed = 0;

        foreach ($candidates as $user) {
            $userId    = (string)$user['id'];
            $email     = (string)($user['email'] ?? '');
            $famCode   = (string)($user['famCode'] ?? '');
            $firstName = (string)($user['firstName'] ?? 'Family Member');
            $lastName  = (string)($user['lastName'] ?? '');

            if (empty($email) || empty($famCode)) {
                continue;
            }

            if (!$dryRun) {
                $magicUrl = MagicAuthService::generateMagicUrl(
                    $userId,
                    '/organogram',
                    72,
                    ['campaign' => self::EVENT_INACTIVITY_TIER4_DEEP]
                );

                try {
                    $emailData = [
                        'id'        => $userId,
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $email,
                        'famCode'   => $famCode,
                        'magicUrl'  => $magicUrl,
                    ];
                    SendEmailFunctionality::email(
                        'msg/lifecycle/inactivity_tier4_deep_dormant_magic',
                        "{$firstName}, your family sanctuary is still reserved — 1-click access (no password needed)",
                        $emailData,
                        'member'
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 4 Inactivity Email error for user {$userId}: " . $e->getMessage());
                }

                try {
                    NotificationOrchestrator::dispatch(
                        userId: $userId,
                        category: 'onboarding',
                        priority: 'high',
                        title: '🏛️ Your Family Space is Reserved',
                        body: "Hello {$firstName}! Your family sanctuary is safely reserved. Preserving one memory takes under 2 minutes.",
                        actionUrl: '/organogram',
                        tag: 'inactivity-tier4',
                        familyCode: $famCode,
                        metadata: ['event' => self::EVENT_INACTIVITY_TIER4_DEEP]
                    );
                } catch (Throwable $e) {
                    error_log("[OnboardingFollowupService] Tier 4 Inactivity Push error for user {$userId}: " . $e->getMessage());
                }

                self::recordLifecycleEvent($userId, self::EVENT_INACTIVITY_TIER4_DEEP, 'omni', [
                    'email'    => $email,
                    'fam_code' => $famCode,
                ]);
            }

            $processed++;
        }

        return $processed;
    }
}
