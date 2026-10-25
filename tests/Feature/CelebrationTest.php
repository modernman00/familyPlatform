<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\CelebrationController;
use App\services\CelebrationService;
use App\services\MemoryMilestoneService;
use DateTimeImmutable;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;
use Tests\Support\SocialFeedTestCase;

/**
 * RFC-0008: birthday and wedding-anniversary notices, the private greeting,
 * private wishes, and the age/anniversary privacy settings.
 *
 * $this->authorId (from SocialFeedTestCase) is the logged-in viewer and has no
 * birthday of their own unless a test seeds one.
 */
final class CelebrationTest extends SocialFeedTestCase
{
    /** @var list<string> */
    private array $userIds = [];
    /** @var list<int> */
    private array $nodeIds = [];
    /** @var list<int> */
    private array $unionIds = [];
    /** @var list<string> */
    private array $famCodes = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->famCodes[] = $this->famCode;
        // The viewer is a live member of $this->famCode (no birthday seeded)
        $this->seedPerson($this->famCode, 'Viewer', null, $this->authorId);
    }

    protected function tearDown(): void
    {
        if ($this->userIds !== []) {
            $in = implode(',', array_fill(0, count($this->userIds), '?'));
            foreach (['account' => 'id', 'personal' => 'id', 'user_families' => 'user_id', 'celebration_prefs' => 'user_id'] as $table => $col) {
                try {
                    $this->pdo->prepare("DELETE FROM {$table} WHERE {$col} IN ($in)")->execute($this->userIds);
                } catch (\PDOException) {
                    // celebration_* tables are created lazily by the service
                }
            }
            $this->pdo->prepare("DELETE FROM notification WHERE receiver_id IN ($in) OR sender_id IN ($in)")->execute([...$this->userIds, ...$this->userIds]);
            try {
                $this->pdo->prepare("DELETE FROM celebration_wishes WHERE recipient_id IN ($in) OR sender_id IN ($in)")->execute([...$this->userIds, ...$this->userIds]);
            } catch (\PDOException) {
            }
        }
        if ($this->unionIds !== []) {
            $in = implode(',', array_fill(0, count($this->unionIds), '?'));
            $this->pdo->prepare("DELETE FROM family_unions WHERE id IN ($in)")->execute($this->unionIds);
            foreach ($this->unionIds as $unionId) {
                try {
                    $this->pdo->prepare("DELETE FROM celebration_notice_log WHERE occasion_key LIKE ?")->execute(["anniversary:{$unionId}:%"]);
                } catch (\PDOException) {
                }
            }
        }
        if ($this->nodeIds !== []) {
            $in = implode(',', array_fill(0, count($this->nodeIds), '?'));
            $this->pdo->prepare("DELETE FROM family_nodes WHERE id IN ($in)")->execute($this->nodeIds);
        }
        try {
            $this->pdo->prepare("DELETE FROM celebration_notice_log WHERE occasion_key LIKE ?")->execute(['%PU\_%']);
        } catch (\PDOException) {
        }
        parent::tearDown();
    }

    /**
     * Seeds a live member (account + personal). $birthday is 'Y-m-d' or null.
     */
    private function seedPerson(string $famCode, string $firstName, ?string $birthday, ?string $userId = null, string $status = 'active'): string
    {
        $userId ??= 'PU_CEL_' . bin2hex(random_bytes(4));
        $this->pdo->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'x', ?)")
            ->execute([$userId, "cel_{$userId}@example.test", $status]);
        $day = $month = $year = null;
        if ($birthday !== null) {
            $d = new DateTimeImmutable($birthday);
            $day = (int) $d->format('j');
            $month = $d->format('F'); // stored as a month name, e.g. "September"
            $year = (int) $d->format('Y');
        }
        $this->pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, day, month, year) VALUES (?, ?, 'Tester', ?, ?, ?, ?)")
            ->execute([$userId, $firstName, $famCode, $day, $month, $year]);
        $this->userIds[] = $userId;
        return $userId;
    }

    private function seedCouple(string $famCode, string $userA, string $nameA, string $nameB, ?string $weddingDate, ?string $userB = null): int
    {
        $ins = $this->pdo->prepare("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender) VALUES (?, ?, ?, 'Tester', ?)");
        $ins->execute([$famCode, $userA, $nameA, 'Male']);
        $nodeA = (int) $this->pdo->lastInsertId();
        $ins->execute([$famCode, $userB, $nameB, 'Female']);
        $nodeB = (int) $this->pdo->lastInsertId();
        $this->nodeIds[] = $nodeA;
        $this->nodeIds[] = $nodeB;
        $this->pdo->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_date, marriage_year, is_current) VALUES (?, ?, ?, 'married', ?, ?, 1)")
            ->execute([$famCode, $nodeA, $nodeB, $weddingDate, $weddingDate !== null ? (int) substr($weddingDate, 0, 4) : null]);
        $unionId = (int) $this->pdo->lastInsertId();
        $this->unionIds[] = $unionId;
        return $unionId;
    }

    private function notices(string $receiverId, string $nameLike = '%'): array
    {
        $stmt = $this->pdo->prepare("SELECT notification_name, notification_content FROM notification WHERE receiver_id = ? AND notification_name LIKE ? ORDER BY no");
        $stmt->execute([$receiverId, $nameLike]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function newFamily(): string
    {
        $code = 'PHPUNIT_CEL_' . bin2hex(random_bytes(4));
        $this->famCodes[] = $code;
        return $code;
    }

    // ---------------------------------------------------------------- notices

    public function testDayBeforeBirthdayNoticeGoesToFamilyButNotTheCelebrant(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-26');
        $aunt = $this->seedPerson($this->famCode, 'Aunt', null);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(1, $this->notices($aunt, "%Wale Tester's birthday is tomorrow%"));
        $this->assertCount(1, $this->notices($this->authorId, "%Wale Tester's birthday is tomorrow%"));
        $this->assertCount(0, $this->notices($wale), 'The celebrant must not be told about their own birthday');
    }

    public function testOnTheDayFamilyIsInvitedToWishAndCelebrantGetsAGreeting(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $aunt = $this->seedPerson($this->famCode, 'Aunt', null);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $auntNotices = $this->notices($aunt, "%It's Wale Tester's birthday today%");
        $this->assertCount(1, $auntNotices);
        $this->assertStringContainsString('Send your wishes', (string) $auntNotices[0]['notification_content']);

        $greeting = $this->notices($wale, '%Happy birthday%');
        $this->assertCount(1, $greeting);
        $this->assertStringContainsString('Wale', (string) $greeting[0]['notification_name']);
        $this->assertStringContainsString('from all of us at Family Platform', (string) $greeting[0]['notification_content']);
    }

    public function testAgeIsHiddenUnlessThePersonAllowsIt(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $ola = $this->seedPerson($this->famCode, 'Ola', '1986-10-25');
        CelebrationService::savePreferences($ola, showAge: true, announceAnniversary: null);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $aboutWale = $this->notices($this->authorId, '%Wale%');
        $this->assertCount(1, $aboutWale);
        $this->assertStringNotContainsString('60', $aboutWale[0]['notification_name'] . $aboutWale[0]['notification_content'], 'Age must stay hidden by default');

        $this->assertCount(1, $this->notices($this->authorId, '%Ola Tester turns 40 today%'), 'Age shown once the person allows it');
    }

    public function testLeapDayBirthdayIsCelebratedOnTheTwentyEighthInOtherYears(): void
    {
        $leap = $this->seedPerson($this->famCode, 'Leap', '2000-02-29');

        CelebrationService::runDaily(new DateTimeImmutable('2027-02-27'));
        $this->assertCount(1, $this->notices($this->authorId, "%Leap Tester's birthday is tomorrow%"));

        CelebrationService::runDaily(new DateTimeImmutable('2027-02-28'));
        $this->assertCount(1, $this->notices($this->authorId, "%It's Leap Tester's birthday today%"));
        $this->assertCount(1, $this->notices($leap, '%Happy birthday%'));
    }

    public function testOtherFamiliesAreNotTold(): void
    {
        $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $stranger = $this->seedPerson($this->newFamily(), 'Stranger', null);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(0, $this->notices($stranger));
    }

    public function testJoinedFamilyMembersAreTold(): void
    {
        $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $cousin = $this->seedPerson($this->newFamily(), 'Cousin', null);
        $this->pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')")
            ->execute([$cousin, $this->famCode]);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(1, $this->notices($cousin, '%Wale%'));
    }

    public function testErasedOrDeletingMembersAreNotCelebrated(): void
    {
        $this->seedPerson($this->famCode, 'Gone', '1966-10-25', null, 'erased');
        $this->seedPerson($this->famCode, 'Leaving', '1966-10-25', null, 'pending_deletion');

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(0, $this->notices($this->authorId, '%Gone%'));
        $this->assertCount(0, $this->notices($this->authorId, '%Leaving%'));
    }

    public function testAStaleDeletedAtStampOnALiveAccountDoesNotHideTheirBirthday(): void
    {
        // RFC-0006: deleted_at used to be auto-stamped on live rows; status is the real signal
        $wale = $this->seedPerson($this->famCode, 'Stamped', '1966-10-25');
        $this->pdo->prepare('UPDATE personal SET deleted_at = NOW() WHERE id = ?')->execute([$wale]);
        $this->pdo->prepare('UPDATE account SET deleted_at = NOW() WHERE id = ?')->execute([$wale]);

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(1, $this->notices($this->authorId, '%Stamped%'));
    }

    public function testRunningTwiceOnTheSameDaySendsNothingNew(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');

        $first = CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));
        $second = CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));

        $this->assertCount(1, $this->notices($this->authorId, '%Wale%'));
        $this->assertCount(1, $this->notices($wale, '%Happy birthday%'));
        $this->assertGreaterThanOrEqual(1, $first['notices']);
        $this->assertSame(0, $second['notices']);
        $this->assertSame(0, $second['greetings']);
    }

    public function testIfTheClaimCannotBeSavedNothingIsSent(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
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

        $result = CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'), $brokenDb);

        $this->assertGreaterThanOrEqual(1, $result['failed']);
        $this->assertCount(0, $this->notices($this->authorId, '%Wale%'));
        $this->assertCount(0, $this->notices($wale));
    }

    public function testAnniversaryIsAnnouncedOnlyWhenACoupleOptsIn(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', null);
        $this->seedCouple($this->famCode, $wale, 'Wale', 'Ade', '2001-10-25');

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));
        $this->assertCount(0, $this->notices($this->authorId, '%anniversary%'), 'Not announced without opt-in');
        $this->assertCount(0, $this->notices($this->authorId, '%married%'));

        CelebrationService::savePreferences($wale, showAge: null, announceAnniversary: true);
        CelebrationService::runDaily(new DateTimeImmutable('2026-10-26'));
        $this->assertCount(0, $this->notices($this->authorId, '%Wale & Ade%'), 'Day after the wedding date: nothing');

        $this->pdo->prepare("DELETE FROM celebration_notice_log WHERE occasion_key LIKE 'anniversary:%'")->execute();
        CelebrationService::runDaily(new DateTimeImmutable('2026-10-24'));
        $this->assertCount(1, $this->notices($this->authorId, "%Wale & Ade's wedding anniversary is tomorrow%"));

        CelebrationService::runDaily(new DateTimeImmutable('2026-10-25'));
        $this->assertCount(1, $this->notices($this->authorId, '%Wale & Ade have been married 25 years today%'));
        $this->assertCount(1, $this->notices($wale, '%Happy anniversary%'));
    }

    // ---------------------------------------------------------------- wishes

    public function testWishIsStoredPrivatelyAndTheCelebrantIsNotified(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');

        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'Happy birthday uncle!', new DateTimeImmutable('2026-10-25'));

        $wishes = CelebrationService::receivedWishes($wale, new DateTimeImmutable('2026-10-25'));
        $this->assertCount(1, $wishes);
        $this->assertSame('Happy birthday uncle!', $wishes[0]['message']);
        $this->assertSame('birthday', $wishes[0]['occasion_type']);
        $this->assertSame('Viewer Tester', $wishes[0]['sender_name']);
        $this->assertCount(1, $this->notices($wale, '%sent you birthday wishes%'));

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notification WHERE notification_content LIKE ? AND receiver_id <> ?");
        $stmt->execute(['%Happy birthday uncle!%', $wale]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'Nobody but the celebrant sees the wish');
    }

    public function testWishingSomeoneOutsideYourFamiliesIsForbidden(): void
    {
        $stranger = $this->seedPerson($this->newFamily(), 'Stranger', '1980-10-25');

        $this->expectException(ForbiddenException::class);
        CelebrationService::sendWish($this->authorId, 'birthday', $stranger, 'hi', new DateTimeImmutable('2026-10-25'));
    }

    public function testYouCannotWishYourself(): void
    {
        $this->pdo->prepare("UPDATE personal SET day = 25, month = 'October', year = 1990 WHERE id = ?")->execute([$this->authorId]);

        $this->expectException(ValidationException::class);
        CelebrationService::sendWish($this->authorId, 'birthday', $this->authorId, 'me!', new DateTimeImmutable('2026-10-25'));
    }

    public function testWishesAreAcceptedUpToTwoDaysLateButNotBeforeOrAfter(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');

        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'Belated wishes', new DateTimeImmutable('2026-10-27'));
        $this->assertCount(1, CelebrationService::receivedWishes($wale, new DateTimeImmutable('2026-10-27')));

        foreach (['2026-10-24', '2026-10-28'] as $outside) {
            try {
                CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'x', new DateTimeImmutable($outside));
                $this->fail("Wish on {$outside} should be rejected");
            } catch (NotFoundException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testWishMessageMustBeOneToFiveHundredCharactersOfPlainText(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $today = new DateTimeImmutable('2026-10-25');

        foreach (['', '   ', '<b></b>', str_repeat('a', 501)] as $bad) {
            try {
                CelebrationService::sendWish($this->authorId, 'birthday', $wale, $bad, $today);
                $this->fail('Message "' . substr($bad, 0, 20) . '" should be rejected');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        CelebrationService::sendWish($this->authorId, 'birthday', $wale, '<script>alert(1)</script>Have a great day', $today);
        $wishes = CelebrationService::receivedWishes($wale, $today);
        $this->assertSame('alert(1)Have a great day', $wishes[0]['message'], 'Tags are stripped; the text is stored as plain text');
    }

    public function testSendingAgainReplacesYourEarlierWish(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $today = new DateTimeImmutable('2026-10-25');

        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'First draft', $today);
        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'Second, better wish', $today);

        $wishes = CelebrationService::receivedWishes($wale, $today);
        $this->assertCount(1, $wishes);
        $this->assertSame('Second, better wish', $wishes[0]['message']);
        $this->assertCount(1, $this->notices($wale, '%sent you birthday wishes%'), 'The celebrant is notified once, not on every edit');
    }

    public function testAnniversaryWishesReachBothPartnersWithAccounts(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', null);
        $ade = $this->seedPerson($this->famCode, 'Ade', null);
        $union = $this->seedCouple($this->famCode, $wale, 'Wale', 'Ade', '2001-10-25', $ade);
        CelebrationService::savePreferences($wale, showAge: null, announceAnniversary: true);
        $today = new DateTimeImmutable('2026-10-25');

        CelebrationService::sendWish($this->authorId, 'anniversary', (string) $union, 'Congratulations you two!', $today);

        $this->assertCount(1, CelebrationService::receivedWishes($wale, $today));
        $this->assertCount(1, CelebrationService::receivedWishes($ade, $today));
    }

    public function testAnniversaryWishesNeedTheCoupleToHaveOptedIn(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', null);
        $union = $this->seedCouple($this->famCode, $wale, 'Wale', 'Ade', '2001-10-25');

        $this->expectException(NotFoundException::class);
        CelebrationService::sendWish($this->authorId, 'anniversary', (string) $union, 'hi', new DateTimeImmutable('2026-10-25'));
    }

    public function testReadingWishesMarksThemRead(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $today = new DateTimeImmutable('2026-10-25');
        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'Cheers', $today);

        $first = CelebrationService::receivedWishes($wale, $today);
        $this->assertFalse($first[0]['read']);
        $second = CelebrationService::receivedWishes($wale, $today);
        $this->assertTrue($second[0]['read']);
    }

    // ---------------------------------------------------------------- what the viewer sees

    public function testOccasionsListsTodaysFamilyCelebrationsWithWishState(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', '1966-10-25');
        $this->seedPerson($this->newFamily(), 'Stranger', '1966-10-25');
        $today = new DateTimeImmutable('2026-10-25');

        $occasions = CelebrationService::occasionsFor($this->authorId, $today);
        $this->assertCount(1, $occasions);
        $this->assertSame('birthday', $occasions[0]['type']);
        $this->assertSame($wale, $occasions[0]['target_id']);
        $this->assertSame('Today', $occasions[0]['date_label']);
        $this->assertFalse($occasions[0]['already_wished']);

        CelebrationService::sendWish($this->authorId, 'birthday', $wale, 'Hi', $today);
        $this->assertTrue(CelebrationService::occasionsFor($this->authorId, $today)[0]['already_wished']);
    }

    // ---------------------------------------------------------------- HTTP layer

    public function testWishEndpointRequiresCsrfToken(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', date('Y-m-d', strtotime('-40 years')));
        unset($_SERVER['HTTP_X_XSRF_TOKEN']);
        $_POST = ['type' => 'birthday', 'target_id' => $wale, 'message' => 'hi'];

        $res = $this->captureLastJson(fn() => (new CelebrationController())->wish());
        $this->assertSame(401, $res['code'] ?? null);
        $this->assertCount(0, CelebrationService::receivedWishes($wale));
    }

    public function testWishEndpointReturnsForbiddenForOtherFamilies(): void
    {
        $stranger = $this->seedPerson($this->newFamily(), 'Stranger', date('Y-m-d', strtotime('-40 years')));
        $_POST = ['token' => $_SESSION['token'], 'type' => 'birthday', 'target_id' => $stranger, 'message' => 'hi'];

        $res = $this->captureLastJson(fn() => (new CelebrationController())->wish());
        $this->assertSame(403, $res['code'] ?? null);
    }

    public function testWishEndpointAndListingWorkForTodaysBirthday(): void
    {
        $wale = $this->seedPerson($this->famCode, 'Wale', date('Y-m-d', strtotime('-40 years')));
        $_POST = ['token' => $_SESSION['token'], 'type' => 'birthday', 'target_id' => $wale, 'message' => 'Happy birthday!'];

        $res = $this->captureLastJson(fn() => (new CelebrationController())->wish());
        $this->assertSame('success', $res['status'] ?? null);

        $list = $this->captureLastJson(fn() => (new CelebrationController())->index());
        $occasions = $list['data']['occasions'] ?? [];
        $this->assertSame($wale, $occasions[0]['target_id'] ?? null);
        $this->assertTrue($occasions[0]['already_wished'] ?? false);

        $_SESSION['id'] = $wale;
        $_SESSION['famCode'] = $this->famCode;
        $mine = $this->captureLastJson(fn() => (new CelebrationController())->index());
        $this->assertSame('Happy birthday!', $mine['data']['wishes'][0]['message'] ?? null);
    }

    // ---------------------------------------------------------------- settings

    public function testWeddingDateIsSavedOnTheCurrentMarriage(): void
    {
        $union = $this->seedCouple($this->famCode, $this->authorId, 'Viewer', 'Spouse', null);

        $this->assertTrue(CelebrationService::saveWeddingDate($this->authorId, $this->famCode, '2001-06-16'));
        $row = $this->pdo->query("SELECT marriage_date, marriage_year FROM family_unions WHERE id = {$union}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('2001-06-16', $row['marriage_date']);
        $this->assertSame(2001, (int) $row['marriage_year']);
        $this->assertSame('2001-06-16', CelebrationService::weddingDate($this->authorId, $this->famCode));

        $this->assertFalse(CelebrationService::saveWeddingDate($this->authorId, $this->famCode, 'not-a-date'));
        $this->assertFalse(CelebrationService::saveWeddingDate($this->authorId, $this->famCode, date('Y-m-d', strtotime('+1 year'))), 'Future dates are rejected');
    }

    public function testAccountSettingsSavesShowMyAge(): void
    {
        $_POST = ['action' => 'updatePreferences', 'token' => $_SESSION['token'], 'email_notifications' => 'on', 'sms_notifications' => 'off', 'celebrate_show_age' => 'on'];

        try {
            $res = $this->captureLastJson(fn() => (new \App\controller\members\SettingController())->post());
            $this->assertSame('success', $res['status'] ?? null);
            $this->assertTrue(CelebrationService::preferences($this->authorId)['show_age']);

            $_POST['celebrate_show_age'] = 'off';
            $this->captureLastJson(fn() => (new \App\controller\members\SettingController())->post());
            $this->assertFalse(CelebrationService::preferences($this->authorId)['show_age']);
        } finally {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$this->authorId]);
        }
    }

    public function testAccountSettingsSavesWeddingDateAndAnniversaryChoiceForAMarriage(): void
    {
        $this->pdo->prepare("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender) VALUES (?, ?, 'Viewer', 'Tester', 'Male')")
            ->execute([$this->famCode, $this->authorId]);
        $this->nodeIds[] = (int) $this->pdo->lastInsertId();
        $_POST = [
            'action' => 'updateMarital', 'token' => $_SESSION['token'],
            'maritalStatus' => 'Yes - Add Wife', 'spouse_name' => 'Ade Tester',
            'marriage_date' => '2001-06-16', 'announce_anniversary' => 'on',
        ];

        try {
            $res = $this->captureLastJson(fn() => (new \App\controller\members\SettingController())->post());
            $this->assertSame('success', $res['status'] ?? null, json_encode($res) ?: '');

            $union = $this->pdo->prepare("SELECT fu.id, fu.marriage_date FROM family_unions fu INNER JOIN family_nodes n ON n.id = fu.partner_1_id OR n.id = fu.partner_2_id WHERE n.user_id = ?");
            $union->execute([$this->authorId]);
            $row = $union->fetch(\PDO::FETCH_ASSOC);
            $this->assertIsArray($row, 'Saving the spouse creates the marriage');
            $this->unionIds[] = (int) $row['id'];
            $spouse = $this->pdo->query('SELECT partner_1_id, partner_2_id FROM family_unions WHERE id = ' . (int) $row['id'])->fetch(\PDO::FETCH_ASSOC);
            $this->nodeIds[] = (int) $spouse['partner_1_id'];
            $this->nodeIds[] = (int) $spouse['partner_2_id'];

            $this->assertSame('2001-06-16', $row['marriage_date']);
            $this->assertTrue(CelebrationService::preferences($this->authorId)['announce_anniversary']);

            $_POST['marriage_date'] = '2999-01-01';
            $bad = $this->captureLastJson(fn() => (new \App\controller\members\SettingController())->post());
            $this->assertSame(422, $bad['code'] ?? null, 'A future wedding date is rejected');
        } finally {
            $this->pdo->prepare('DELETE FROM otherFamily WHERE id = ?')->execute([$this->authorId]);
        }
    }

    public function testPreferencesDefaultToPrivateAndCanBeChanged(): void
    {
        $this->assertSame(['show_age' => false, 'announce_anniversary' => false], CelebrationService::preferences($this->authorId));

        CelebrationService::savePreferences($this->authorId, showAge: true, announceAnniversary: null);
        CelebrationService::savePreferences($this->authorId, showAge: null, announceAnniversary: true);
        $this->assertSame(['show_age' => true, 'announce_anniversary' => true], CelebrationService::preferences($this->authorId));
    }

    // ---------------------------------------------------------------- existing milestone widget

    public function testMilestoneWidgetRespectsAgeAndAnniversaryChoices(): void
    {
        $soon = new DateTimeImmutable('+2 days');
        $this->seedPerson($this->famCode, 'Private', $soon->modify('-60 years')->format('Y-m-d'));
        $open = $this->seedPerson($this->famCode, 'Open', $soon->modify('-50 years')->format('Y-m-d'));
        CelebrationService::savePreferences($open, showAge: true, announceAnniversary: null);
        $hush = $this->seedPerson($this->famCode, 'Hush', null);
        $this->seedCouple($this->famCode, $hush, 'Hush', 'Quiet', $soon->modify('-10 years')->format('Y-m-d'));

        $milestones = (new MemoryMilestoneService())->getUpcomingMilestones($this->authorId, [$this->famCode], 7);
        $byTitle = [];
        foreach ($milestones as $m) {
            $byTitle[(string) $m['title']] = (string) $m['subtitle'];
        }

        $this->assertArrayHasKey("Private Tester's Birthday", $byTitle);
        $this->assertSame('Birthday Celebration', $byTitle["Private Tester's Birthday"], 'No age or age-based jubilee without permission');
        $this->assertStringContainsString('50', $byTitle["Open Tester's Birthday"] ?? '', 'Age shown when allowed');
        foreach (array_keys($byTitle) as $title) {
            $this->assertStringNotContainsString('Hush', $title, 'Anniversary hidden unless the couple opted in');
        }
    }
}
