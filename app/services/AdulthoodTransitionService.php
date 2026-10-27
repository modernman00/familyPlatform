<?php

declare(strict_types=1);

namespace App\services;

use Exception;
use PDO;
use Src\Db;

/**
 * 🎓 AdulthoodTransitionService
 * 
 * Orchestrates the "Turning 18" Adulthood Graduation Engine:
 * 1. Automatically recognizes when a child reaches legal majority (18th birthday).
 * 2. Unlocks the "Family Vault / Time Capsule" (Dr. Silas Thorne contrarian feature).
 * 3. Publishes an emotional family celebration post to the family feed.
 * 4. Automatically severs parental surveillance and unlocks sovereign adult privileges.
 */
final class AdulthoodTransitionService
{
    /**
     * Checks if a user has reached their 18th birthday transition date and executes graduation.
     *
     * @param string $userId
     * @return array<string, mixed>|null Returns celebration payload if graduated, or null
     */
    public static function checkAndProcessUserGraduation(string $userId): ?array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return null;
        }

        $db = Db::connect2();
        $stmt = $db->prepare('
            SELECT id, adulthood_status, is_child_account, transition_due_date, parent_user_id
            FROM account
            WHERE id = ?
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        $acc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc) {
            return null;
        }

        $status = (string)($acc['adulthood_status'] ?? '');
        $transitionDue = (string)($acc['transition_due_date'] ?? '');

        // Only transition accounts currently flagged as minors whose transition date is reached
        if (in_array($status, ['minor_supervised', 'minor_teen'], true) && $transitionDue !== '') {
            $today = date('Y-m-d');
            if ($transitionDue <= $today) {
                return self::processGraduation($userId);
            }
        }

        return null;
    }

    /**
     * Executes the complete graduation transition for a young adult turning 18.
     *
     * @param string $childUserId
     * @return array<string, mixed>
     */
    public static function processGraduation(string $childUserId): array
    {
        $db = Db::connect2();

        // 1. Fetch child and family context
        $stmtP = $db->prepare('
            SELECT p.firstName, p.lastName, p.famCode, a.parent_user_id, a.transition_due_date
            FROM account a
            LEFT JOIN personal p ON p.id = a.id
            WHERE a.id = ?
            LIMIT 1
        ');
        $stmtP->execute([$childUserId]);
        $data = $stmtP->fetch(PDO::FETCH_ASSOC);

        $firstName = trim((string)($data['firstName'] ?? 'Family Member'));
        $lastName = trim((string)($data['lastName'] ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);
        $famCode = (string)($data['famCode'] ?? '');
        $parentUserId = (string)($data['parent_user_id'] ?? '');

        $db->beginTransaction();
        try {
            // 2. Transition account to graduated adult and revoke child flags
            $stmtUpdAcc = $db->prepare('
                UPDATE account
                SET adulthood_status = "graduated_adult",
                    is_child_account = 0,
                    graduated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ');
            $stmtUpdAcc->execute([$childUserId]);

            // 3. Unseal 18th Birthday Time Capsules (Dr. Silas Thorne contrarian innovation)
            $stmtUnseal = $db->prepare('
                UPDATE family_time_capsules
                SET is_unlocked = 1,
                    unlocked_at = CURRENT_TIMESTAMP
                WHERE (recipient_user_id = ? OR recipient_name = ?)
                  AND (unlock_type = "milestone_18" OR unlock_date <= CURDATE())
                  AND is_unlocked = 0
            ');
            $stmtUnseal->execute([$childUserId, $fullName]);
            $unsealedCount = $stmtUnseal->rowCount();

            // 4. Publish celebratory announcement post to the family feed
            $celebrationMessage = "🎂✨ Milestone Celebration: {$fullName} has officially turned 18 today! Happy 18th Birthday and welcome to full adulthood in our family! Let us celebrate this wonderful milestone together! 🎉🎈";
            $currentTimeMs = (string)(int)(microtime(true) * 1000);
            $currentDate = date('Y-m-d H:i:s');

            $stmtPost = $db->prepare('
                INSERT INTO post (
                    id, fullName, profileImg, postMessage,
                    postFamCode, post_time, post_status
                ) VALUES (?, ?, "avatarM.png", ?, ?, ?, "published")
            ');
            $stmtPost->execute([
                $childUserId,
                $fullName,
                $celebrationMessage,
                $famCode,
                $currentTimeMs
            ]);

            $db->commit();

            // 5. Dispatch real-time milestone notifications
            if (class_exists(NotificationOrchestrator::class)) {
                // To the newly graduated 18-year-old
                NotificationOrchestrator::dispatch(
                    userId: $childUserId,
                    category: 'social',
                    priority: 'high',
                    title: 'Happy 18th Birthday! 🎂',
                    body: 'Welcome to full family adulthood! Your account has graduated with full sovereign privileges.',
                    actionUrl: '/member/profilePage',
                    tag: 'adulthood_graduation_' . $childUserId,
                    familyCode: $famCode !== '' ? $famCode : null,
                    metadata: [
                        'type' => 'adulthood_graduation',
                        'unsealed_capsules' => $unsealedCount,
                    ]
                );

                // To the parent
                if ($parentUserId !== '') {
                    NotificationOrchestrator::dispatch(
                        userId: $parentUserId,
                        category: 'social',
                        priority: 'medium',
                        title: 'A Beautiful Family Milestone 🌟',
                        body: "{$fullName} has officially turned 18 today. Happy 18th Birthday to your child!",
                        actionUrl: '/member/profilePage',
                        tag: 'parent_milestone_18_' . $childUserId,
                        familyCode: $famCode !== '' ? $famCode : null,
                        metadata: [
                            'type' => 'child_turned_18',
                            'child_id' => $childUserId,
                        ]
                    );
                }
            }

            return [
                'graduated' => true,
                'user_id' => $childUserId,
                'full_name' => $fullName,
                'family_code' => $famCode,
                'unsealed_capsules' => $unsealedCount,
                'transition_date' => date('Y-m-d'),
                'message' => "Happy 18th Birthday, {$fullName}! You have successfully graduated to full adult status.",
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Daily batch watchdog processor: finds all accounts whose 18th birthday is due and executes graduation.
     *
     * @return int Number of accounts graduated
     */
    public static function processAllPendingGraduations(): int
    {
        $db = Db::connect2();
        $stmt = $db->prepare('
            SELECT id
            FROM account
            WHERE adulthood_status IN ("minor_supervised", "minor_teen")
              AND transition_due_date IS NOT NULL
              AND transition_due_date <= CURDATE()
            LIMIT 100
        ');
        $stmt->execute();
        $pendingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($pendingIds as $id) {
            if (is_string($id) && $id !== '') {
                try {
                    self::processGraduation($id);
                    $count++;
                } catch (\Throwable $e) {
                    error_log('Error processing graduation for user ' . $id . ': ' . $e->getMessage());
                }
            }
        }

        return $count;
    }
}
