<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\NotificationController;
use App\service\NotificationService;
use Tests\Support\SocialFeedTestCase;

final class InAppNotificationSyncTest extends SocialFeedTestCase
{
    /** @var list<int> */
    private array $seededNotifNos = [];

    /** @var list<string> */
    private array $extraFamilies = [];

    protected function tearDown(): void
    {
        if (!empty($this->seededNotifNos)) {
            $in = implode(',', array_fill(0, count($this->seededNotifNos), '?'));
            $stmt = $this->pdo->prepare("DELETE FROM notification WHERE no IN ($in)");
            $stmt->execute($this->seededNotifNos);
        }

        if (!empty($this->extraFamilies)) {
            $inFam = implode(',', array_fill(0, count($this->extraFamilies), '?'));
            $stmt = $this->pdo->prepare("DELETE FROM user_families WHERE family_code IN ($inFam)");
            $stmt->execute($this->extraFamilies);
        }

        $stmt = $this->pdo->prepare("DELETE FROM account WHERE id = ?");
        $stmt->execute([$this->authorId]);
        $stmt = $this->pdo->prepare("DELETE FROM personal WHERE id = ?");
        $stmt->execute([$this->authorId]);
        $stmt = $this->pdo->prepare("DELETE FROM contact WHERE id = ?");
        $stmt->execute([$this->authorId]);

        parent::tearDown();
    }

    private function seedNotification(string $receiverId, string $title, string $createdAt): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO notification 
            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, notification_status, notification_date, created_at)
            VALUES ('system', ?, 'Tester', ?, 'General', 'Notification Content', 'new', NOW(), ?)
        ");
        $stmt->execute([$receiverId, $title, $createdAt]);
        $no = (int)$this->pdo->lastInsertId();
        $this->seededNotifNos[] = $no;
        return $no;
    }

    public function testNotificationByIdReturnsOrderedByNewestFirst(): void
    {
        $olderNo = $this->seedNotification($this->authorId, 'Older Notification', date('Y-m-d H:i:s', strtotime('-2 hours')));
        $newerNo = $this->seedNotification($this->authorId, 'Newer Notification', date('Y-m-d H:i:s', strtotime('-1 minute')));

        ob_start();
        $result = NotificationController::notificationById();
        ob_end_clean();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, count($result));

        // Locate our seeded notifications
        $filtered = array_values(array_filter($result, static function (mixed $item) use ($olderNo, $newerNo): bool {
            if (!is_array($item)) return false;
            $no = (int)($item['no'] ?? 0);
            return $no === $olderNo || $no === $newerNo;
        }));

        $this->assertCount(2, $filtered);
        // The first item must be the newer one
        $this->assertSame($newerNo, (int)$filtered[0]['no'], 'Newer notification must appear first (DESC order)');
        $this->assertSame($olderNo, (int)$filtered[1]['no']);
    }

    public function testNotificationByIdExcludesSelfNotifications(): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO notification 
            (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, notification_status, notification_date, created_at)
            VALUES (?, ?, 'Wally Oguns', 'New update from Wally Oguns', 'Post', 'Wally Oguns posted a new comment', 'new', NOW(), NOW())
        ");
        $stmt->execute([$this->authorId, $this->authorId]);
        $selfDirectNo = (int)$this->pdo->lastInsertId();
        $this->seededNotifNos[] = $selfDirectNo;

        $stmt->execute([$this->authorId, $this->famCode]);
        $selfFamNo = (int)$this->pdo->lastInsertId();
        $this->seededNotifNos[] = $selfFamNo;

        $otherNo = $this->seedNotification($this->authorId, 'External Relative Comment', date('Y-m-d H:i:s'));

        ob_start();
        $result = NotificationController::notificationById();
        ob_end_clean();

        $this->assertIsArray($result);
        $returnedNos = array_map(static fn(mixed $item): int => is_array($item) ? (int)($item['no'] ?? 0) : 0, $result);

        $this->assertNotContains($selfDirectNo, $returnedNos, 'User must not see their own direct notification');
        $this->assertNotContains($selfFamNo, $returnedNos, 'User must not see their own family-scoped notification');
        $this->assertContains($otherNo, $returnedNos, 'User must still see notifications from other members');
    }

    public function testSessionReceiverIdsIncludesUserFamiliesApprovedCodes(): void
    {
        $multiFamCode = 'MULTIFAM_' . bin2hex(random_bytes(4));
        $this->extraFamilies[] = $multiFamCode;

        // Seed membership in user_families table
        $stmt = $this->pdo->prepare("
            INSERT INTO user_families (user_id, family_code, status, role)
            VALUES (?, ?, 'approved', 'member')
        ");
        $stmt->execute([$this->authorId, $multiFamCode]);

        // Seed a notification targeted to the multiFamCode
        $famNotifNo = $this->seedNotification($multiFamCode, 'Multi-Family Alert', date('Y-m-d H:i:s'));

        ob_start();
        $result = NotificationController::notificationById();
        ob_end_clean();

        $this->assertIsArray($result);
        $found = false;
        foreach ($result as $item) {
            if (is_array($item) && (int)($item['no'] ?? 0) === $famNotifNo) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'User should receive notifications addressed to approved user_families codes');
    }

    public function testSendApprovalConfirmationNotificationWritesToNotificationTable(): void
    {
        $testCode = 'TESTFAM_' . bin2hex(random_bytes(3));
        $this->extraFamilies[] = $testCode;

        // Seed account + personal + contact record for user
        $stmt = $this->pdo->prepare("
            INSERT INTO account (id, email, status)
            VALUES (?, 'newmember@example.com', 'active')
            ON DUPLICATE KEY UPDATE email = 'newmember@example.com'
        ");
        $stmt->execute([$this->authorId]);

        $stmt = $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
            VALUES (?, 'New', 'Member', ?, 1, 'January', 1995)
            ON DUPLICATE KEY UPDATE firstName = 'New'
        ");
        $stmt->execute([$this->authorId, $this->famCode]);

        $stmt = $this->pdo->prepare("
            INSERT INTO contact (id, email, country, mobile)
            VALUES (?, 'newmember@example.com', 'United Kingdom', '+447000000000')
            ON DUPLICATE KEY UPDATE email = 'newmember@example.com'
        ");
        $stmt->execute([$this->authorId]);

        $service = new NotificationService($this->pdo);
        $service->sendApprovalConfirmationNotification($this->authorId, $testCode);

        // Check that a notification row was inserted
        $stmt = $this->pdo->prepare("
            SELECT * FROM notification 
            WHERE receiver_id = ? AND notification_name = 'Membership Approved'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$this->authorId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertNotEmpty($row);
        $this->seededNotifNos[] = (int)$row['no'];
        $this->assertStringContainsString($testCode, (string)$row['notification_content']);
    }
}
