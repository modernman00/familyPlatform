<?php

declare(strict_types=1);

/**
 * One-Time Idempotent Migration Script:
 * Set all registered members' privacy (`show_my_profile`) to 'on' (Public/Searchable)
 * and `profile_visibility` to 'Public' by default across the database schema and existing records.
 *
 * Usage:
 *   php scripts/migrate_member_privacy_public.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden: CLI execution only.');
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Db;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

try {
    $pdo = Db::connect2();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=================================================================\n";
    echo "🔓 FamilyPlatform: Default Public Privacy & Visibility Migration\n";
    echo "=================================================================\n";

    // 1. Audit pre-migration state
    $prePrivateStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM contact WHERE COALESCE(profile_visibility, '') != ? OR COALESCE(show_my_profile, '') != ?"
    );
    $prePrivateStmt->execute(['Public', 'on']);
    $preNeedsUpdateCount = (int) $prePrivateStmt->fetchColumn();

    $missingContactCountStmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT p.id)
         FROM personal p
         LEFT JOIN contact c ON p.id = c.id
         WHERE c.id IS NULL AND p.id != ?"
    );
    $missingContactCountStmt->execute(['']);
    $missingContactCount = (int) $missingContactCountStmt->fetchColumn();

    echo sprintf(
        "📊 Pre-Migration Audit:\n   - Contact rows needing update to Public/on: %d\n   - Registered members missing contact row  : %d\n\n",
        $preNeedsUpdateCount,
        $missingContactCount
    );

    // 2. Alter `contact` table column defaults so all future inserts default to Public & on
    $alterSql = "ALTER TABLE `contact`
        MODIFY COLUMN `profile_visibility` VARCHAR(30) DEFAULT 'Public',
        MODIFY COLUMN `show_my_profile` VARCHAR(10) DEFAULT 'on',
        MODIFY COLUMN `email_notifications` VARCHAR(10) DEFAULT 'on',
        MODIFY COLUMN `sms_notifications` VARCHAR(10) DEFAULT 'on'";
    $pdo->exec($alterSql);
    echo "✅ Step 1/3: Updated `contact` table schema defaults (`profile_visibility` = 'Public', `show_my_profile` = 'on').\n";

    $pdo->beginTransaction();

    // 3. Backfill missing `contact` rows for registered members in `personal` / `account`
    $missingMembersStmt = $pdo->prepare(
        "SELECT DISTINCT p.id, COALESCE(a.email, '') AS email
         FROM personal p
         LEFT JOIN account a ON p.id = a.id
         LEFT JOIN contact c ON p.id = c.id
         WHERE c.id IS NULL AND p.id != ?"
    );
    $missingMembersStmt->execute(['']);
    /** @var array<int, array{id: string, email: string}> $missingMembers */
    $missingMembers = $missingMembersStmt->fetchAll(PDO::FETCH_ASSOC);

    $insertContactStmt = $pdo->prepare(
        "INSERT INTO contact (id, email, country, mobile, email_notifications, sms_notifications, two_factor_auth, profile_visibility, show_my_profile)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $backfilledCount = 0;
    foreach ($missingMembers as $member) {
        $memberId = trim((string) ($member['id'] ?? ''));
        if ($memberId === '') {
            continue;
        }
        $email = substr(trim((string) ($member['email'] ?? '')), 0, 50);
        $insertContactStmt->execute([
            $memberId,
            $email,
            '',
            '',
            'on',
            'on',
            'off',
            'Public',
            'on',
        ]);
        $backfilledCount++;
    }
    echo sprintf("✅ Step 2/3: Backfilled %d missing `contact` record(s) with Public visibility.\n", $backfilledCount);

    // 4. Update all existing `contact` records to profile_visibility = 'Public' and show_my_profile = 'on'
    $updateStmt = $pdo->prepare(
        "UPDATE contact
         SET profile_visibility = ?,
             show_my_profile = ?
         WHERE COALESCE(profile_visibility, '') != ?
            OR COALESCE(show_my_profile, '') != ?"
    );
    $updateStmt->execute(['Public', 'on', 'Public', 'on']);
    $updatedRows = $updateStmt->rowCount();

    $pdo->commit();
    echo sprintf("✅ Step 3/3: Updated %d existing `contact` record(s) to `profile_visibility` = 'Public' and `show_my_profile` = 'on'.\n\n", $updatedRows);

    // 5. Post-migration verification
    $verifyRemainingStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM contact WHERE COALESCE(profile_visibility, '') != ? OR COALESCE(show_my_profile, '') != ?"
    );
    $verifyRemainingStmt->execute(['Public', 'on']);
    $remainingPrivate = (int) $verifyRemainingStmt->fetchColumn();

    $totalPublicStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM contact WHERE profile_visibility = ? AND show_my_profile = ?"
    );
    $totalPublicStmt->execute(['Public', 'on']);
    $totalPublic = (int) $totalPublicStmt->fetchColumn();

    echo "=================================================================\n";
    echo sprintf("🏆 Migration Complete! Total Public & Searchable Members: %d\n", $totalPublic);
    echo sprintf("🔒 Remaining Private/Off Records                        : %d\n", $remainingPrivate);
    echo "=================================================================\n";

    if ($remainingPrivate > 0) {
        exit(1);
    }
    exit(0);
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "❌ Migration Error: " . $e->getMessage() . "\n");
    exit(1);
}
