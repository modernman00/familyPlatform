<?php

declare(strict_types=1);

namespace App\services;

use App\controller\BaseController;
use App\classes\PushNotificationClass;
use App\model\AllMembersData;
use Src\Db;
use PDO;

/**
 * FamilyTimeCapsuleService
 *
 * Powers the Living Heritage & Digital Family Time Capsule Vault.
 * Enforces zero-trust time-locking: sealed messages remain strictly redacted
 * on the server side until their verified unlock date or milestone arrives.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyTimeCapsuleService
{
    /**
     * Create a new sealed time capsule.
     *
     * @param string $familyCode Target family code
     * @param string $creatorId Authoring member user ID
     * @param array{
     *     recipient_name: string,
     *     recipient_user_id?: string|null,
     *     title: string,
     *     message: string,
     *     media_url?: string|null,
     *     media_type?: string,
     *     unlock_type?: string,
     *     unlock_date: string
     * } $data
     * @return int Created capsule ID
     */
    public static function createCapsule(string $familyCode, string $creatorId, array $data): int
    {
        $db = Db::connect2();

        $recipientName = trim($data['recipient_name']);
        $recipientUserId = !empty($data['recipient_user_id']) ? trim($data['recipient_user_id']) : null;
        $title = trim($data['title']);
        $message = trim($data['message']);
        $mediaUrl = !empty($data['media_url']) ? trim($data['media_url']) : null;
        $mediaType = !empty($data['media_type']) ? trim($data['media_type']) : 'none';
        $unlockType = !empty($data['unlock_type']) ? trim($data['unlock_type']) : 'date';
        $unlockDate = trim($data['unlock_date']);

        $stmt = $db->prepare(
            'INSERT INTO family_time_capsules 
             (family_code, creator_id, recipient_name, recipient_user_id, title, message, media_url, media_type, unlock_type, unlock_date, is_unlocked, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())'
        );

        $stmt->execute([
            $familyCode,
            $creatorId,
            $recipientName,
            $recipientUserId,
            $title,
            $message,
            $mediaUrl,
            $mediaType,
            $unlockType,
            $unlockDate,
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Retrieve all time capsules for a family with zero-trust redaction for locked items.
     *
     * @param string $familyCode
     * @param string $viewerUserId
     * @return array<int, array<string, mixed>>
     */
    public static function getFamilyCapsules(string $familyCode, string $viewerUserId): array
    {
        $db = Db::connect2();

        $stmt = $db->prepare(
            'SELECT id, family_code, creator_id, recipient_name, recipient_user_id, title, message, media_url, media_type, unlock_type, unlock_date, is_unlocked, unlocked_at, created_at
             FROM family_time_capsules
             WHERE family_code = ?
             ORDER BY unlock_date ASC, id DESC'
        );
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $today = date('Y-m-d');
        $sanitized = [];

        foreach ($rows as $row) {
            $capsuleId = (int)$row['id'];
            $creatorId = (string)($row['creator_id'] ?? '');
            $unlockDate = (string)($row['unlock_date'] ?? '');
            $isUnlocked = !empty($row['is_unlocked']);

            $isDue = ($unlockDate <= $today);
            $isCreator = ($creatorId === $viewerUserId);

            // Auto-unlock if date has arrived
            if ($isDue && !$isUnlocked) {
                $upStmt = $db->prepare('UPDATE family_time_capsules SET is_unlocked = 1, unlocked_at = NOW() WHERE id = ?');
                $upStmt->execute([$capsuleId]);
                $isUnlocked = true;
                $row['is_unlocked'] = 1;
            }

            $isAccessible = $isUnlocked || $isCreator;

            $nowTs = time();
            $unlockTs = strtotime($unlockDate);
            $secondsRemaining = $unlockTs !== false ? max(0, $unlockTs - $nowTs) : 0;
            $daysRemaining = (int)ceil($secondsRemaining / 86400);

            $item = [
                'id'                  => $capsuleId,
                'family_code'         => (string)$row['family_code'],
                'creator_id'          => $creatorId,
                'recipient_name'      => (string)$row['recipient_name'],
                'recipient_user_id'   => $row['recipient_user_id'],
                'title'               => (string)$row['title'],
                'media_type'          => (string)$row['media_type'],
                'unlock_type'         => (string)$row['unlock_type'],
                'unlock_date'         => $unlockDate,
                'is_unlocked'         => $isUnlocked,
                'is_locked'           => !$isUnlocked,
                'is_creator'          => $isCreator,
                'days_remaining'      => $daysRemaining,
                'created_at'          => (string)$row['created_at'],
            ];

            if ($isAccessible) {
                $item['message'] = (string)$row['message'];
                $item['media_url'] = $row['media_url'];
            } else {
                // Strict Zero-Trust Redaction: payload never leaves server
                $formattedUnlock = $unlockTs !== false ? date('d M Y', $unlockTs) : $unlockDate;
                $item['message'] = '🔒 This message is securely sealed until ' . $formattedUnlock;
                $item['media_url'] = null;
            }

            $sanitized[] = $item;
        }

        return $sanitized;
    }

    /**
     * Unlock due capsules across all families and broadcast notifications.
     */
    public static function checkAndUnlockDueCapsules(): int
    {
        $db = Db::connect2();
        $today = date('Y-m-d');

        $stmt = $db->prepare(
            'SELECT id, family_code, creator_id, recipient_name, title
             FROM family_time_capsules
             WHERE unlock_date <= ? AND is_unlocked = 0'
        );
        $stmt->execute([$today]);
        /** @var array<int, array<string, mixed>> $due */
        $due = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $count = count($due);
        if ($count === 0) {
            return 0;
        }

        $upStmt = $db->prepare('UPDATE family_time_capsules SET is_unlocked = 1, unlocked_at = NOW() WHERE id = ?');
        $notifStmt = $db->prepare("
            INSERT INTO notification 
            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
            VALUES ('system', ?, 'Family Platform Vault', '⏳ Time Capsule Unlocked!', 'Capsule', ?, '/profilePage#timeCapsuleCard', 'new', NOW())
        ");

        foreach ($due as $capsule) {
            $capsuleId = (int)$capsule['id'];
            $upStmt->execute([$capsuleId]);

            $creatorId = (string)($capsule['creator_id'] ?? '');
            $title = (string)($capsule['title'] ?? 'Milestone Memory');
            $famCode = (string)($capsule['family_code'] ?? '');
            $msg = "A family time capsule has unlocked today: \"{$title}\"! View it now in your family vault.";

            if ($creatorId !== '') {
                try {
                    $notifStmt->execute([$creatorId, $msg]);
                    PushNotificationClass::sendPushNotification(
                        userId: $creatorId,
                        message: $msg,
                        url: '/profilePage#timeCapsuleCard',
                        title: '⏳ Time Capsule Unlocked!',
                        tag: 'capsule-' . $capsuleId
                    );
                } catch (\Throwable) {}
            }

            if ($famCode !== '') {
                try {
                    $members = AllMembersData::AllMembersEmailByFamCode($famCode, $creatorId !== '' ? $creatorId : null);
                    foreach ($members as $m) {
                        $mId = (string)($m['id'] ?? '');
                        if ($mId === '' || $mId === $creatorId) {
                            continue;
                        }
                        try {
                            $notifStmt->execute([$mId, $msg]);
                            PushNotificationClass::sendPushNotification(
                                userId: $mId,
                                message: $msg,
                                url: '/profilePage#timeCapsuleCard',
                                title: '⏳ Time Capsule Unlocked!',
                                tag: 'capsule-' . $capsuleId
                            );
                        } catch (\Throwable) {}
                    }
                } catch (\Throwable) {}
            }
        }

        return $count;
    }
}
