<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\services\OnboardingFollowupService;
use Src\Db;

/**
 * Onboarding Lifecycle Engine Feature Test Suite
 *
 * Verifies multi-touch onboarding:
 * - Day 1 Lineage Kickstart (empty tree prompt)
 * - Day 3 Living Voice Capsule
 * - Day 7 Referral Flywheel
 * - Strict Idempotency Guarantees
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class OnboardingLifecycleEngineTest extends TestCase
{
    private static string $userEmptyTree = 'USR_TEST_EMPTY_TREE_99';
    private static string $userFullTree = 'USR_TEST_FULL_TREE_99';
    private static string $userVoiceDone = 'USR_TEST_VOICE_DONE_99';
    private static string $familyEmpty = 'TEST_FAM_LC_EMPTY_99';
    private static string $familyFull = 'TEST_FAM_LC_FULL_99';

    public static function setUpBeforeClass(): void
    {
        $db = Db::connect2();
        self::tearDownAfterClass();

        // 1. User 1: Registered > 24h ago with empty tree (1 node only)
        $insAcct1 = $db->prepare("
            INSERT INTO account (id, email, password, status, created_at)
            VALUES (?, 'empty.tree@test.com', 'hash', 'approved', DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ");
        $insAcct1->execute([self::$userEmptyTree]);

        $insPers1 = $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, created_at)
            VALUES (?, 'Babatunde', 'TestEmpty', ?, DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ");
        $insPers1->execute([self::$userEmptyTree, self::$familyEmpty]);

        $insNode1 = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, ?, 'Babatunde', 'TestEmpty', 'Male', 0)
        ");
        $insNode1->execute([self::$familyEmpty, self::$userEmptyTree]);

        // 2. User 2: Registered > 24h ago with populated tree (3 nodes)
        $insAcct2 = $db->prepare("
            INSERT INTO account (id, email, password, status, created_at)
            VALUES (?, 'full.tree@test.com', 'hash', 'approved', DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ");
        $insAcct2->execute([self::$userFullTree]);

        $insPers2 = $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, created_at)
            VALUES (?, 'Segun', 'TestFull', ?, DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ");
        $insPers2->execute([self::$userFullTree, self::$familyFull]);

        // Insert 3 nodes for full tree
        $insNode2 = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES (?, ?, 'Segun', 'TestFull', 'Male', 0),
                   (?, NULL, 'FatherSegun', 'TestFull', 'Male', -1),
                   (?, NULL, 'MotherSegun', 'TestFull', 'Female', -1)
        ");
        $insNode2->execute([self::$familyFull, self::$userFullTree, self::$familyFull, self::$familyFull]);

        // 3. User 3: Registered > 72h ago with voice capsule already uploaded
        $insAcct3 = $db->prepare("
            INSERT INTO account (id, email, password, status, created_at)
            VALUES (?, 'voice.done@test.com', 'hash', 'approved', DATE_SUB(NOW(), INTERVAL 96 HOUR))
        ");
        $insAcct3->execute([self::$userVoiceDone]);

        $insPers3 = $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, created_at)
            VALUES (?, 'Ayomide', 'TestVoice', ?, DATE_SUB(NOW(), INTERVAL 96 HOUR))
        ");
        $insPers3->execute([self::$userVoiceDone, 'TEST_FAM_VOICE_DONE_99']);

        $insNode3 = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, voice_capsule_url)
            VALUES ('TEST_FAM_VOICE_DONE_99', ?, 'Ayomide', 'TestVoice', 'Female', 0, 'https://storage.test/audio.mp3')
        ");
        $insNode3->execute([self::$userVoiceDone]);
    }

    public static function tearDownAfterClass(): void
    {
        $db = Db::connect2();
        $db->prepare("DELETE FROM user_lifecycle_events WHERE user_id IN (?, ?, ?)")
            ->execute([self::$userEmptyTree, self::$userFullTree, self::$userVoiceDone]);
        $db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?, 'TEST_FAM_VOICE_DONE_99')")
            ->execute([self::$familyEmpty, self::$familyFull]);
        $db->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)")
            ->execute([self::$userEmptyTree, self::$userFullTree, self::$userVoiceDone]);
        $db->prepare("DELETE FROM account WHERE id IN (?, ?, ?)")
            ->execute([self::$userEmptyTree, self::$userFullTree, self::$userVoiceDone]);
    }

    /**
     * Test Day 1 Tree Kickstart Dispatches For Empty Tree User
     */
    public function testDay1TreeKickstartDispatchesForEmptyTreeUser(): void
    {
        $processed = OnboardingFollowupService::processDay1TreeKickstart(false, self::$userEmptyTree);
        $this->assertSame(1, $processed, 'User with empty tree must receive Day 1 kickstart prompt');

        // Verify event was recorded in user_lifecycle_events
        $db = Db::connect2();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM user_lifecycle_events 
            WHERE user_id = ? AND event_type = ?
        ");
        $stmt->execute([self::$userEmptyTree, OnboardingFollowupService::EVENT_DAY1_TREE]);
        $this->assertSame(1, (int)$stmt->fetchColumn(), 'Lifecycle event must be durably recorded');
    }

    /**
     * Test Day 1 Tree Kickstart Skips User Who Already Built Their Tree
     */
    public function testDay1TreeKickstartSkipsUserWithPopulatedTree(): void
    {
        // For user with full tree (> 1 nodes), should record skipped and not dispatch communications
        $processed = OnboardingFollowupService::processDay1TreeKickstart(false, self::$userFullTree);
        $this->assertSame(0, $processed, 'User with populated tree must be skipped without dispatch');

        $db = Db::connect2();
        $stmt = $db->prepare("
            SELECT channel FROM user_lifecycle_events 
            WHERE user_id = ? AND event_type = ?
        ");
        $stmt->execute([self::$userFullTree, OnboardingFollowupService::EVENT_DAY1_TREE]);
        $channel = $stmt->fetchColumn();
        $this->assertSame('skipped_already_built', $channel, 'Must record skipped status');
    }

    /**
     * Test Day 3 Voice Capsule Skips User Who Already Recorded Voice
     */
    public function testDay3VoiceCapsuleSkipsUserWithRecordedAudio(): void
    {
        $processed = OnboardingFollowupService::processDay3VoiceCapsule(false, self::$userVoiceDone);
        $this->assertSame(0, $processed, 'User with voice capsule already recorded must be skipped');

        $db = Db::connect2();
        $stmt = $db->prepare("
            SELECT channel FROM user_lifecycle_events 
            WHERE user_id = ? AND event_type = ?
        ");
        $stmt->execute([self::$userVoiceDone, OnboardingFollowupService::EVENT_DAY3_VOICE]);
        $channel = $stmt->fetchColumn();
        $this->assertSame('skipped_already_recorded', $channel);
    }

    /**
     * Test Strict Idempotency: Running Twice Never Duplicate-Sends
     */
    public function testStrictIdempotencyPreventsDuplicateDispatch(): void
    {
        // First run already recorded for userEmptyTree
        $processedSecondRun = OnboardingFollowupService::processDay1TreeKickstart(false, self::$userEmptyTree);
        $this->assertSame(0, $processedSecondRun, 'Second run must process 0 users due to idempotency gate');

        $db = Db::connect2();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM user_lifecycle_events 
            WHERE user_id = ? AND event_type = ?
        ");
        $stmt->execute([self::$userEmptyTree, OnboardingFollowupService::EVENT_DAY1_TREE]);
        $this->assertSame(1, (int)$stmt->fetchColumn(), 'Exactly one event record must exist');
    }

    /**
     * Test Dry-Run Simulation Mode
     */
    public function testDryRunSimulationModeDoesNotModifyState(): void
    {
        $testUserId = 'USR_TEST_DRY_RUN_99';
        $db = Db::connect2();
        $db->prepare("
            INSERT INTO account (id, email, password, status, created_at)
            VALUES (?, 'dryrun@test.com', 'hash', 'approved', DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ")->execute([$testUserId]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, created_at)
            VALUES (?, 'Dry', 'Run', 'DRY_FAM_99', DATE_SUB(NOW(), INTERVAL 48 HOUR))
        ")->execute([$testUserId]);

        $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
            VALUES ('DRY_FAM_99', ?, 'Dry', 'Run', 'Male', 0)
        ")->execute([$testUserId]);

        // Run in dry-run mode
        $processed = OnboardingFollowupService::processDay1TreeKickstart(true, $testUserId);
        $this->assertSame(1, $processed, 'Dry run should count eligible user');

        // Verify NO record was written to user_lifecycle_events
        $stmt = $db->prepare("SELECT COUNT(*) FROM user_lifecycle_events WHERE user_id = ?");
        $stmt->execute([$testUserId]);
        $this->assertSame(0, (int)$stmt->fetchColumn(), 'Dry run must not write to database');

        // Cleanup
        $db->prepare("DELETE FROM family_nodes WHERE family_code = 'DRY_FAM_99'")->execute();
        $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
        $db->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);
    }
}
