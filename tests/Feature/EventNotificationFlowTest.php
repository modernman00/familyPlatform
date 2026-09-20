<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\Event;
use App\model\AllMembersData;
use Tests\Support\SocialFeedTestCase;

final class EventNotificationFlowTest extends SocialFeedTestCase
{
    /** @var list<int> */
    private array $createdEventNos = [];
    /** @var list<string> */
    private array $seededMemberIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Seed personal record for author
        $stmt = $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
            VALUES (?, 'EventHost', 'Tester', ?, 15, 'September', 1990)
        ");
        $stmt->execute([$this->authorId, $this->famCode]);
        $this->seededMemberIds[] = $this->authorId;
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdEventNos)) {
            $in = implode(',', array_fill(0, count($this->createdEventNos), '?'));
            $this->pdo->prepare("DELETE FROM events WHERE no IN ($in)")->execute($this->createdEventNos);
        }

        if (!empty($this->seededMemberIds)) {
            $inUids = implode(',', array_fill(0, count($this->seededMemberIds), '?'));
            $this->pdo->prepare("DELETE FROM personal WHERE id IN ($inUids)")->execute($this->seededMemberIds);
            $this->pdo->prepare("DELETE FROM notification WHERE sender_id IN ($inUids) OR receiver_id IN ($inUids)")->execute([...$this->seededMemberIds, ...$this->seededMemberIds]);
        }

        parent::tearDown();
    }

    private function seedEvent(string $userId, string $famCode, string $name, string $date, ?string $deletedAt = null): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO events (id, eventCode, eventName, eventDate, eventType, eventDescription, eventFrequency, deleted_at)
            VALUES (?, ?, ?, ?, 'Party', 'A test celebration', 'One-off', ?)
        ");
        $stmt->execute([$userId, $famCode, $name, $date, $deletedAt]);
        $no = (int) $this->pdo->lastInsertId();
        $this->createdEventNos[] = $no;
        return $no;
    }

    public function testGetEventDataByFamCodeReturnsUpcomingEventsBeyond7Days(): void
    {
        $soonDate = date('Y-m-d', strtotime('+3 days'));
        $farDate = date('Y-m-d', strtotime('+28 days'));
        $pastDate = date('Y-m-d', strtotime('-5 days'));

        $this->seedEvent($this->authorId, $this->famCode, 'Soon Event', $soonDate);
        $this->seedEvent($this->authorId, $this->famCode, 'Far Event (>7 days)', $farDate);
        $this->seedEvent($this->authorId, $this->famCode, 'Past Event', $pastDate);

        $events = AllMembersData::getEventDataByFamCode($this->famCode);
        $eventNames = array_column($events, 'eventName');

        $this->assertContains('Soon Event', $eventNames);
        $this->assertContains('Far Event (>7 days)', $eventNames, 'Events scheduled >7 days out must be returned');
        $this->assertNotContains('Past Event', $eventNames, 'Past events must not be returned');
    }

    public function testGetEventDataByFamCodeSupportsMultiFamilyArray(): void
    {
        $secondaryFamCode = 'PHPUNIT_FAM2_' . bin2hex(random_bytes(4));

        // Seed secondary member for secondary famCode
        $secondMemberId = 'PU_SEC_' . bin2hex(random_bytes(4));
        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (?, 'Relative', 'SecondFam', ?)
        ")->execute([$secondMemberId, $secondaryFamCode]);
        $this->seededMemberIds[] = $secondMemberId;

        $date1 = date('Y-m-d', strtotime('+5 days'));
        $date2 = date('Y-m-d', strtotime('+10 days'));

        $this->seedEvent($this->authorId, $this->famCode, 'Family 1 Gathering', $date1);
        $this->seedEvent($secondMemberId, $secondaryFamCode, 'Family 2 Reunion', $date2);

        $multiEvents = AllMembersData::getEventDataByFamCode([$this->famCode, $secondaryFamCode]);
        $names = array_column($multiEvents, 'eventName');

        $this->assertContains('Family 1 Gathering', $names);
        $this->assertContains('Family 2 Reunion', $names);
    }

    public function testDeleteEventRejectsNonAuthor(): void
    {
        $eventNo = $this->seedEvent($this->authorId, $this->famCode, 'Protected Event', date('Y-m-d', strtotime('+5 days')));

        // Switch session identity to an attacker
        $attackerId = 'PU_ATTACKER_' . bin2hex(random_bytes(4));
        $_SESSION['id'] = $attackerId;

        $res = $this->captureLastJson(fn() => Event::deleteEvent($eventNo));
        $this->assertSame(403, $res['code'] ?? null);
        $this->assertStringContainsString('own events', (string)($res['message'] ?? ''));
    }

    public function testUpdateEventRejectsNonAuthor(): void
    {
        $eventNo = $this->seedEvent($this->authorId, $this->famCode, 'Protected Event', date('Y-m-d', strtotime('+5 days')));

        $attackerId = 'PU_ATTACKER_' . bin2hex(random_bytes(4));
        $_SESSION['id'] = $attackerId;

        $res = $this->captureLastJson(fn() => Event::updateEvent($eventNo));
        $this->assertSame(403, $res['code'] ?? null);
        $this->assertStringContainsString('own events', (string)($res['message'] ?? ''));
    }

    public function testPostEventNotificationBarInsertsNotificationRow(): void
    {
        $_POST = [
            'eventName' => 'Annual Summer BBQ',
            'eventDate' => date('Y-m-d', strtotime('+14 days')),
            'eventType' => 'Barbecue',
            'eventDescription' => 'Family summer gathering in the park.',
            'eventFrequency' => 'Annually',
        ];

        $res = $this->captureLastJson(fn() => Event::PostEventNotificationBar());
        $this->assertSame('success', $res['status'] ?? null);
        $this->assertNotEmpty($res['message'] ?? null);

        // Verify row was created in notification table
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND notification_name = ?");
        $stmt->execute([$this->famCode, 'Annual Summer BBQ']);
        $row = $stmt->fetch();

        $this->assertNotEmpty($row);
        $this->assertSame('Annual Summer BBQ', $row['notification_name'] ?? '');
        $this->assertSame('new', $row['notification_status'] ?? '');
    }

    public function testAllMembersEmailByFamCodeResolvesUserFamiliesMembers(): void
    {
        $joinedUserId = 'PU_JOINED_' . bin2hex(random_bytes(4));
        $joinedEmail = "joined_{$joinedUserId}@example.test";

        // Seed account and personal with different primary famCode
        $this->pdo->prepare("
            INSERT INTO account (id, email, password, status) VALUES (?, ?, 'test_password_hash', 'active')
        ")->execute([$joinedUserId, $joinedEmail]);

        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode)
            VALUES (?, 'Joined', 'Member', 'PRIMARY_FAM_CODE')
        ")->execute([$joinedUserId]);

        // Link to $this->famCode via user_families table
        $this->pdo->prepare("
            INSERT INTO user_families (user_id, family_code, status, role)
            VALUES (?, ?, 'approved', 'member')
        ")->execute([$joinedUserId, $this->famCode]);

        $this->seededMemberIds[] = $joinedUserId;

        $results = AllMembersData::AllMembersEmailByFamCode($this->famCode);
        $emails = array_column($results, 'email');

        $this->assertContains($joinedEmail, $emails, 'Multi-family member must receive notifications for joined family code');

        // Cleanup user_families record
        $this->pdo->prepare("DELETE FROM user_families WHERE user_id = ?")->execute([$joinedUserId]);
        $this->pdo->prepare("DELETE FROM account WHERE id = ?")->execute([$joinedUserId]);
    }

    public function testNotificationOrchestratorDispatchPersistsDurableNotification(): void
    {
        $targetUserId = 'PU_ORCH_TARGET_' . bin2hex(random_bytes(4));
        $this->seededMemberIds[] = $targetUserId;

        $notifId = \App\services\NotificationOrchestrator::dispatch(
            userId: $targetUserId,
            category: 'social',
            priority: 'medium',
            title: 'Test Notification Title',
            body: 'Test Notification Body Content',
            actionUrl: '/profilePage',
            tag: 'test-tag',
            familyCode: $this->famCode,
            metadata: [
                'sender_id' => $this->authorId,
                'sender_name' => 'AuthorTester'
            ]
        );

        $this->assertNotEmpty($notifId);

        // Verify row in notification table
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND notification_name = ?");
        $stmt->execute([$targetUserId, 'Test Notification Title']);
        $row = $stmt->fetch();

        $this->assertNotEmpty($row, 'NotificationOrchestrator::dispatch must write to notification table');
        $this->assertSame('Test Notification Title', $row['notification_name'] ?? '');
        $this->assertSame('new', $row['notification_status'] ?? '');
    }
}
