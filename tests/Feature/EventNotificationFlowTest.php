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
            try {
                $this->pdo->prepare("DELETE FROM event_reminder_log WHERE event_no IN ($in)")->execute($this->createdEventNos);
            } catch (\PDOException) {
                // Table is created on the first reminder run; nothing to clean before that
            }
        }

        if (!empty($this->seededMemberIds)) {
            $inUids = implode(',', array_fill(0, count($this->seededMemberIds), '?'));
            $this->pdo->prepare("DELETE FROM account WHERE id IN ($inUids)")->execute($this->seededMemberIds);
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

    /**
     * Seeds a member (account + personal) whose primary family is $famCode.
     */
    private function seedMember(string $famCode, string $firstName = 'Relative'): string
    {
        $memberId = 'PU_MEM_' . bin2hex(random_bytes(4));
        $this->pdo->prepare("
            INSERT INTO account (id, email, password, status) VALUES (?, ?, 'test_password_hash', 'active')
        ")->execute([$memberId, "member_{$memberId}@example.test"]);
        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, 'Member', ?)
        ")->execute([$memberId, $firstName, $famCode]);
        $this->seededMemberIds[] = $memberId;
        return $memberId;
    }

    private function seedAuthorAccount(): void
    {
        $this->pdo->prepare("
            INSERT INTO account (id, email, password, status) VALUES (?, ?, 'test_password_hash', 'active')
        ")->execute([$this->authorId, "eventhost_{$this->authorId}@example.test"]);
    }

    private function countNotifications(string $receiverId, string $nameLike): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND notification_name LIKE ?");
        $stmt->execute([$receiverId, $nameLike]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Rejected submissions render an HTML error page rather than JSON, so the
     * JSON shape is only asserted by callers that expect success.
     *
     * @return array{0: string, 1: string} [first output chunk, all output]
     */
    private function submitEventAsAuthor(string $name, string $date): array
    {
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        // The validation-error page builds absolute URLs from the host header
        $_SERVER['HTTP_HOST'] ??= 'familyplatform.test';
        $_POST = [
            'token' => $_SESSION['token'],
            'eventName' => $name,
            'eventDate' => $date,
            'eventType' => 'Party',
            'eventDescription' => 'Bring a dish.',
            'eventFrequency' => 'One-off',
        ];

        $output = $this->captureOutput(fn() => Event::submitEvent());
        $chunks = preg_split('/(?<=})\s*(?={)/', trim($output)) ?: [$output];
        $response = json_decode((string) $chunks[0], true);
        $createdId = is_array($response) ? (int) ($response['token'] ?? 0) : 0;
        if ($createdId > 0) {
            $this->createdEventNos[] = $createdId;
        }
        return [(string) $chunks[0], $output];
    }

    public function testSubmitEventNotifiesFamilyMembersButNotTheCreator(): void
    {
        $this->seedAuthorAccount();
        $relativeId = $this->seedMember($this->famCode);

        [$json, $output] = $this->submitEventAsAuthor('Annual Summer BBQ', date('Y-m-d', strtotime('+14 days')));
        $response = json_decode($json, true);
        $this->assertIsArray($response, "Expected JSON, got: $output");
        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('Event submitted', $response['message'] ?? null);

        $this->assertSame(1, $this->countNotifications($relativeId, '🎉 New Event: Annual Summer BBQ'), 'Family member must get exactly one in-app notification');
        $this->assertSame(0, $this->countNotifications($this->authorId, '%Annual Summer BBQ%'), 'Creator must not be notified about their own event');
        $this->assertSame(0, $this->countNotifications($this->famCode, '%Annual Summer BBQ%'), 'No family-wide row (it duplicated every member\'s bell entry)');
    }

    public function testSubmitEventThatFailsValidationNotifiesNobody(): void
    {
        $this->seedAuthorAccount();
        $relativeId = $this->seedMember($this->famCode);

        // eventName min length is 3, so "No" is rejected before anything is saved
        $this->submitEventAsAuthor('No', date('Y-m-d', strtotime('+3 days')));

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id IN (?, ?)");
        $stmt->execute([$relativeId, $this->famCode]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'A rejected event must not notify anyone');
    }

    public function testDeprecatedNotificationEndpointRejectsMissingCsrfToken(): void
    {
        unset($_SERVER['HTTP_X_XSRF_TOKEN']);
        $_POST = ['eventName' => 'Spam', 'eventDescription' => 'buy now'];

        $res = $this->captureLastJson(fn() => Event::PostEventNotificationBar());
        $this->assertSame(401, $res['code'] ?? null);
    }

    public function testDeprecatedNotificationEndpointSendsNothing(): void
    {
        $this->seedAuthorAccount();
        $relativeId = $this->seedMember($this->famCode);
        $_POST = ['token' => $_SESSION['token'], 'eventName' => 'Arbitrary text', 'eventDate' => date('Y-m-d'), 'eventType' => 'Party', 'eventDescription' => 'x'];

        $res = $this->captureLastJson(fn() => Event::PostEventNotificationBar());
        $this->assertSame('success', $res['status'] ?? null);
        // Old cached createEvent.js only calls the GET broadcast when this is truthy
        $this->assertNull($res['message'] ?? null);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id IN (?, ?, ?)");
        $stmt->execute([$relativeId, $this->authorId, $this->famCode]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'Deprecated endpoint must not create notifications');
    }

    public function testDeprecatedBroadcastEndpointDoesNotExposeOtherFamiliesNotifications(): void
    {
        $secret = 'PRIVATE_' . bin2hex(random_bytes(6));
        $otherMember = $this->seedMember('PHPUNIT_OTHER_' . bin2hex(random_bytes(4)));
        $this->pdo->prepare("
            INSERT INTO notification (sender_id, receiver_id, notification_name, notification_type, notification_content, notification_status)
            VALUES ('system', ?, 'Private', 'Event', ?, 'new')
        ")->execute([$otherMember, $secret]);
        $_GET = ['notificationNo' => (string) $this->pdo->lastInsertId()];

        $output = $this->captureOutput(fn() => Event::GetEventNotificationBar());
        $this->assertStringNotContainsString($secret, $output);
        $res = json_decode(trim($output), true);
        $this->assertIsArray($res, "Deprecated endpoint must answer with JSON, got: $output");
        $this->assertSame('success', $res['status'] ?? null);
        $this->assertNull($res['message'] ?? null);
    }

    public function testRemindersSkipDeletedEvents(): void
    {
        $today = new \DateTimeImmutable('today');
        $relativeId = $this->seedMember($this->famCode);
        $this->seedEvent($this->authorId, $this->famCode, 'Live Reunion', $today->modify('+7 days')->format('Y-m-d'));
        $this->seedEvent($this->authorId, $this->famCode, 'Cancelled Reunion', $today->modify('+7 days')->format('Y-m-d'), date('Y-m-d H:i:s'));

        Event::runReminders($today);

        $this->assertSame(1, $this->countNotifications($relativeId, 'Live Reunion%'));
        $this->assertSame(0, $this->countNotifications($relativeId, 'Cancelled Reunion%'), 'Deleted events must not send reminders');
    }

    public function testRemindersGoToTheEventsFamilyNotTheCreatorsPrimaryFamily(): void
    {
        $today = new \DateTimeImmutable('today');
        $eventFamily = 'PHPUNIT_EVFAM_' . bin2hex(random_bytes(4));
        $eventFamilyMember = $this->seedMember($eventFamily);
        $creatorFamilyMember = $this->seedMember($this->famCode);

        // Creator's personal.famCode is $this->famCode, but the event was posted to $eventFamily
        $this->seedEvent($this->authorId, $eventFamily, 'Other Side Picnic', $today->modify('+1 day')->format('Y-m-d'));

        Event::runReminders($today);

        $this->assertSame(1, $this->countNotifications($eventFamilyMember, 'Other Side Picnic%'));
        $this->assertSame(0, $this->countNotifications($creatorFamilyMember, 'Other Side Picnic%'));
    }

    public function testRemindersAreSentOncePerEventPerDay(): void
    {
        $today = new \DateTimeImmutable('today');
        $relativeId = $this->seedMember($this->famCode);
        $this->seedEvent($this->authorId, $this->famCode, 'Once Only Dinner', $today->modify('+1 day')->format('Y-m-d'));

        $first = Event::runReminders($today);
        $second = Event::runReminders($today);

        $this->assertSame(1, $this->countNotifications($relativeId, 'Once Only Dinner%'), 'Second run on the same day must not resend');
        $this->assertGreaterThanOrEqual(1, $first['sent']);
        $this->assertSame(0, $second['sent']);
    }

    public function testRemindersOnlyFireAtSevenDaysOneDayAndOnTheDay(): void
    {
        $today = new \DateTimeImmutable('today');
        $relativeId = $this->seedMember($this->famCode);
        $this->seedEvent($this->authorId, $this->famCode, 'Three Days Out', $today->modify('+3 days')->format('Y-m-d'));
        $this->seedEvent($this->authorId, $this->famCode, 'Today Party', $today->format('Y-m-d'));

        Event::runReminders($today);

        $this->assertSame(0, $this->countNotifications($relativeId, 'Three Days Out%'));
        $this->assertSame(1, $this->countNotifications($relativeId, 'Today Party%'));
    }

    public function testReminderClaimFailureSendsNothingAndDoesNotThrow(): void
    {
        $today = new \DateTimeImmutable('today');
        $relativeId = $this->seedMember($this->famCode);
        $this->seedEvent($this->authorId, $this->famCode, 'Outage Gala', $today->modify('+7 days')->format('Y-m-d'));

        // Simulated database outage on the idempotency claim
        $brokenDb = new class ('sqlite::memory:') extends \PDO {
            public function prepare(string $query, array $options = []): \PDOStatement|false
            {
                throw new \PDOException('simulated outage');
            }
            public function exec(string $statement): int|false
            {
                throw new \PDOException('simulated outage');
            }
        };

        $result = Event::runReminders($today, $brokenDb);

        $this->assertSame(0, $result['sent']);
        $this->assertGreaterThanOrEqual(1, $result['failed']);
        $this->assertSame(0, $this->countNotifications($relativeId, 'Outage Gala%'), 'If we cannot record the claim we must not send (no duplicates on retry)');
    }

    public function testGetEventDataOnlyReturnsTheCallersFamilies(): void
    {
        $otherFamily = 'PHPUNIT_FOREIGN_' . bin2hex(random_bytes(4));
        $foreigner = $this->seedMember($otherFamily);
        $date = date('Y-m-d', strtotime('+2 days'));
        $this->seedEvent($this->authorId, $this->famCode, 'Our Family Lunch', $date);
        $this->seedEvent($foreigner, $otherFamily, 'Someone Elses Wedding', $date);

        $res = $this->captureLastJson(fn() => Event::getEventData());
        $names = array_column(is_array($res['message'] ?? null) ? $res['message'] : [], 'eventName');

        $this->assertContains('Our Family Lunch', $names);
        $this->assertNotContains('Someone Elses Wedding', $names, 'Other families\' events must not be visible');
    }

    public function testFallbackPushIsSkippedWhenCentralPushWasLoggedAsSent(): void
    {
        $sentId = 'notif_phpunit_' . bin2hex(random_bytes(6));
        $unsentId = 'notif_phpunit_' . bin2hex(random_bytes(6));
        $this->pdo->prepare("
            INSERT INTO notification_delivery_logs (notification_id, channel, status, details, attempted_at)
            VALUES (?, 'web_push', 'sent', 'phpunit', NOW()), (?, 'web_push', 'skipped_no_subscription', 'phpunit', NOW())
        ")->execute([$sentId, $unsentId]);

        try {
            $this->assertTrue(\App\services\NotificationOrchestrator::centralPushDelivered($sentId));
            $this->assertFalse(\App\services\NotificationOrchestrator::centralPushDelivered($unsentId));
            $this->assertFalse(\App\services\NotificationOrchestrator::centralPushDelivered(''));
        } finally {
            $this->pdo->prepare("DELETE FROM notification_delivery_logs WHERE notification_id IN (?, ?)")->execute([$sentId, $unsentId]);
        }
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

    public function testSubmitEventSucceedsWithEmptyOrShortDescription(): void
    {
        $token = 'test_token_' . bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_SESSION['fName'] = 'Test';
        $_SESSION['lName'] = 'Host';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        // 1. Submit event with EMPTY description
        $_POST = [
            'token' => $token,
            'eventName' => 'Family Gathering',
            'eventDate' => date('Y-m-d', strtotime('+10 days')),
            'eventType' => 'Party',
            'eventDescription' => '',
            'eventFrequency' => 'One-off'
        ];

        ob_start();
        Event::submitEvent();
        $output = ob_get_clean();
        $response = json_decode((string) $output, true);

        $this->assertIsArray($response, "Output should be valid JSON, got: $output");
        $this->assertSame('success', $response['status'] ?? '');
        $this->assertSame('Event submitted', $response['message'] ?? '');
        $createdId = (int) ($response['token'] ?? 0);
        $this->assertGreaterThan(0, $createdId);
        $this->createdEventNos[] = $createdId;

        // 2. Submit event with SHORT description (< 5 chars, e.g. "BBQ")
        $_POST = [
            'token' => $token,
            'eventName' => 'Backyard Cookout',
            'eventDate' => date('Y-m-d', strtotime('+12 days')),
            'eventType' => 'Party',
            'eventDescription' => 'BBQ',
            'eventFrequency' => 'One-off'
        ];

        ob_start();
        Event::submitEvent();
        $output2 = ob_get_clean();
        $response2 = json_decode((string) $output2, true);

        $this->assertIsArray($response2, "Output should be valid JSON, got: $output2");
        $this->assertSame('success', $response2['status'] ?? '');
        $this->assertSame('Event submitted', $response2['message'] ?? '');
        $createdId2 = (int) ($response2['token'] ?? 0);
        $this->assertGreaterThan(0, $createdId2);
        $this->createdEventNos[] = $createdId2;
    }
}
