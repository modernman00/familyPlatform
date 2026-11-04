<?php

declare(strict_types=1);

namespace App\services;

use App\classes\PushNotificationClass;
use App\model\AllMembersData;
use App\model\FamilyAnnouncement;
use App\model\Post;
use PDO;
use Src\Db;

/**
 * 🌳 FamilyJoinAnnouncementService
 *
 * Broadcasts an official family announcement, feed post, and multi-channel
 * notifications whenever someone joins a shared family circle (familyCode).
 *
 * Adheres to:
 * - British English (UK) Invariant (Standard 1.6 & Jumoke Mandate)
 * - Zero-Interpolation SQL Invariant
 * - David's 4 Structural Safety Gates (Defensive Typing, Idempotency, Non-blocking)
 */
final class FamilyJoinAnnouncementService
{
    /**
     * Broadcast an announcement and notifications across all members of a family circle.
     *
     * @param string $familyCode Target family code
     * @param string $userId The user who joined the family
     * @param string|null $welcomedById Optional approver or inviter who welcomed them
     * @return array<string, mixed>
     */
    public static function broadcastNewMemberAnnouncement(
        string $familyCode,
        string $userId,
        ?string $welcomedById = null
    ): array {
        $cleanCode = strtoupper(trim(str_replace('#', '', $familyCode)));
        $userId = trim($userId);

        if ($cleanCode === '' || $userId === '') {
            return [
                'success' => false,
                'reason' => 'invalid_parameters',
            ];
        }

        try {
            $db = Db::connect2();

            // 1. Fetch new member details
            $stmtUser = $db->prepare('
                SELECT p.firstName, p.lastName, pp.img AS profile_img
                FROM personal p
                LEFT JOIN profilePics pp ON p.id = pp.id
                WHERE p.id = ?
                LIMIT 1
            ');
            $stmtUser->execute([$userId]);
            $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                return [
                    'success' => false,
                    'reason' => 'member_not_found',
                ];
            }

            $firstName = trim((string)($userData['firstName'] ?? ''));
            $lastName = trim((string)($userData['lastName'] ?? ''));
            $fullName = trim($firstName . ' ' . $lastName);
            if ($fullName === '') {
                $fullName = 'A new family member';
            }
            $profileImg = !empty($userData['profile_img']) ? (string)$userData['profile_img'] : 'avatarM.png';

            // 2. Fetch all other active members in this family circle
            $coMembers = AllMembersData::AllMembersEmailByFamCode($cleanCode, $userId);
            if (empty($coMembers)) {
                // If there are no other co-members, this is a solo founder or first member.
                return [
                    'success' => true,
                    'notified_count' => 0,
                    'reason' => 'sole_family_member',
                ];
            }

            // 3. Idempotency Check: Prevent duplicate join announcements within 24 hours
            $namePattern = '%' . $fullName . '%';
            $stmtCheck = $db->prepare("
                SELECT id FROM family_announcements
                WHERE family_code = ? AND type = 'celebration'
                  AND (author_id = ? OR title LIKE ? OR message LIKE ?)
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $stmtCheck->execute([$cleanCode, $userId, $namePattern, $namePattern]);
            $existingAnnouncementId = $stmtCheck->fetchColumn();

            if ($existingAnnouncementId) {
                return [
                    'success'           => true,
                    'already_announced' => true,
                    'family_code'       => $cleanCode,
                    'new_member_id'     => $userId,
                    'announcement_id'   => (int)$existingAnnouncementId,
                    'notified_count'    => 0,
                    'reason'            => 'already_announced_within_24h',
                ];
            }

            // 4. Create Family Announcement in family_announcements table
            $announcementTitle = "🎉 Warm Welcome to {$fullName}!";
            $announcementMsg = "A warm welcome to {$fullName}, who has just joined our family circle (#{$cleanCode})! Explore our shared family tree, share cherished memories, and give them a warm family welcome! 🌳✨";

            $authorIdForAnnouncement = (!empty($welcomedById) && $welcomedById !== $userId) ? $welcomedById : $userId;

            $announcementId = FamilyAnnouncement::createAnnouncement([
                'family_code' => $cleanCode,
                'author_id'   => $authorIdForAnnouncement,
                'title'       => $announcementTitle,
                'message'     => $announcementMsg,
                'priority'    => 'important',
                'type'        => 'celebration',
                'is_pinned'   => 1,
                'expires_at'  => date('Y-m-d', strtotime('+30 days')),
            ]);

            // 5. Publish celebratory post to the family feed
            $stmtFeedCheck = $db->prepare("
                SELECT post_no FROM post
                WHERE postFamCode = ? AND id = ? AND postMessage LIKE '%joined our family circle%'
                  AND date_created >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $stmtFeedCheck->execute([$cleanCode, $userId]);
            $existingPostNo = $stmtFeedCheck->fetchColumn();

            $postNo = 0;
            if ($existingPostNo) {
                $postNo = (int)$existingPostNo;
            } else {
                $timeMs = function_exists('milliSeconds') ? (string)milliSeconds() : (string)(int)(microtime(true) * 1000);
                $feedMessage = "🎉 Warm Welcome to the Family!\n\n{$fullName} has just joined our family circle (#{$cleanCode})! Welcome aboard to our private sanctuary. Let us give them a warm family welcome! 🌳✨";

                $stmtPost = $db->prepare('
                    INSERT INTO post (
                        id, fullName, profileImg, postMessage,
                        postFamCode, post_time, post_status, date_created
                    ) VALUES (?, ?, ?, ?, ?, ?, "published", NOW())
                ');
                $stmtPost->execute([
                    $userId,
                    $fullName,
                    $profileImg,
                    $feedMessage,
                    $cleanCode,
                    $timeMs,
                ]);
                $postNo = (int)$db->lastInsertId();

                if ($postNo > 0 && class_exists(Post::class)) {
                    try {
                        Post::updatePostByStatusAsPublished($postNo);
                    } catch (\Throwable) {}
                }

                // Defensively broadcast via Pusher to real-time subscribers
                try {
                    if (class_exists('\Src\functionality\Pusher')) {
                        $broadcastPayload = [
                            'post_no'     => $postNo,
                            'id'          => $userId,
                            'fullName'    => $fullName,
                            'postMessage' => $feedMessage,
                            'postFamCode' => $cleanCode,
                            'profileImg'  => $profileImg,
                            'post_time'   => $timeMs,
                            'date_created'=> date('Y-m-d H:i:s'),
                            'is_welcome'  => true,
                        ];
                        \Src\functionality\Pusher::broadcastToFamily($cleanCode, 'new-post', [$broadcastPayload]);
                    }
                } catch (\Throwable $pusherEx) {
                    error_log('[FamilyJoinAnnouncementService::Pusher] ' . $pusherEx->getMessage());
                }
            }

            // 6. Dispatch In-App Notifications & Web Push to each co-member
            $notifTitle = "📢 Family Announcement: {$fullName} has joined!";
            $notifBody = "{$fullName} has joined our family circle (#{$cleanCode}). Tap to welcome them to the dynasty!";
            $actionUrl = '/profilePage#announcementCard-' . $announcementId;
            $notifiedCount = 0;

            $stmtNotif = $db->prepare("
                INSERT INTO notification 
                (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                VALUES (?, ?, ?, ?, 'Announcement', ?, ?, 'new', NOW())
            ");

            foreach ($coMembers as $member) {
                $targetId = trim((string)($member['id'] ?? ''));
                if ($targetId === '' || $targetId === $userId) {
                    continue;
                }

                try {
                    $stmtNotif->execute([
                        $userId,
                        $targetId,
                        $fullName,
                        $notifTitle,
                        $notifBody,
                        $actionUrl,
                    ]);
                    $notifiedCount++;
                } catch (\Throwable $notifErr) {
                    error_log('[FamilyJoinAnnouncementService::in-app notif warning] ' . $notifErr->getMessage());
                }

                // Web Push Notification
                try {
                    PushNotificationClass::sendPushNotification(
                        userId: $targetId,
                        message: $notifBody,
                        url: $actionUrl,
                        title: $notifTitle,
                        tag: 'family-join-' . $cleanCode . '-' . $userId
                    );
                } catch (\Throwable $pushErr) {
                    error_log('[FamilyJoinAnnouncementService::web push warning] ' . $pushErr->getMessage());
                }

                // NotificationOrchestrator Cascade
                if (class_exists(NotificationOrchestrator::class)) {
                    try {
                        NotificationOrchestrator::dispatch(
                            userId: $targetId,
                            category: 'social',
                            priority: 'high',
                            title: $notifTitle,
                            body: $notifBody,
                            actionUrl: $actionUrl,
                            tag: 'family_join_' . $cleanCode . '_' . $userId,
                            familyCode: $cleanCode,
                            metadata: [
                                'type' => 'family_join_announcement',
                                'new_member_id' => $userId,
                                'new_member_name' => $fullName,
                                'family_code' => $cleanCode,
                                'announcement_id' => $announcementId,
                                'post_no' => $postNo,
                            ]
                        );
                    } catch (\Throwable $orchErr) {
                        error_log('[FamilyJoinAnnouncementService::Orchestrator warning] ' . $orchErr->getMessage());
                    }
                }
            }

            return [
                'success'         => true,
                'family_code'     => $cleanCode,
                'new_member_id'   => $userId,
                'new_member_name' => $fullName,
                'announcement_id' => $announcementId,
                'post_no'         => $postNo,
                'notified_count'  => $notifiedCount,
            ];
        } catch (\Throwable $e) {
            error_log('[FamilyJoinAnnouncementService::broadcastNewMemberAnnouncement] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
