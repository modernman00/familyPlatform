<?php
declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\model\FamilyJournal;
use App\model\FamilyAnnouncement;
use Src\Select;

final class FamilyJournalAndAnnouncementTest extends TestCase
{
    private static string $userAId = '888801USERA';
    private static string $famCodeA = 'FAMTESTA100';

    private static string $userBId = '888802USERB';
    private static string $famCodeB = 'FAMTESTB200';

    private static ?int $journalAId = null;
    private static ?int $announcementAId = null;

    public static function setUpBeforeClass(): void
    {
        $pdo = Select::connect2();

        // Clean any existing test fixtures
        $delJournals = $pdo->prepare("DELETE FROM family_journals WHERE family_code IN (?, ?)");
        $delJournals->execute([self::$famCodeA, self::$famCodeB]);

        $delAnnounce = $pdo->prepare("DELETE FROM family_announcements WHERE family_code IN (?, ?)");
        $delAnnounce->execute([self::$famCodeA, self::$famCodeB]);

        $delPersonal = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)");
        $delPersonal->execute([self::$userAId, self::$userBId]);

        // Insert test user A
        $stmtA = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmtA->execute([self::$userAId, 'Alice', 'FamilyA', self::$famCodeA]);

        // Insert test user B
        $stmtB = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmtB->execute([self::$userBId, 'Bob', 'FamilyB', self::$famCodeB]);
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Select::connect2();

        $delJournals = $pdo->prepare("DELETE FROM family_journals WHERE family_code IN (?, ?)");
        $delJournals->execute([self::$famCodeA, self::$famCodeB]);

        $delAnnounce = $pdo->prepare("DELETE FROM family_announcements WHERE family_code IN (?, ?)");
        $delAnnounce->execute([self::$famCodeA, self::$famCodeB]);

        $delPersonal = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)");
        $delPersonal->execute([self::$userAId, self::$userBId]);
    }

    public function testCanCreateFamilyJournal(): void
    {
        $newId = FamilyJournal::createJournal([
            'family_code' => self::$famCodeA,
            'author_id'   => self::$userAId,
            'title'       => 'Grandma Memories in Devon',
            'slug'        => 'grandma-memories-in-devon-test',
            'summary'     => 'A wonderful story about summers in Devon with grandma.',
            'content'     => '<p>Every summer we visited the seaside and collected seashells.</p>',
            'cover_image' => '/public/img/og-invite.jpg',
            'category'    => 'Oral History',
            'mood'        => 'heartwarming',
            'status'      => 'published'
        ]);

        $this->assertNotEmpty($newId);
        self::$journalAId = (int)$newId;
    }

    public function testCanRetrieveFamilyJournalsForOwnFamily(): void
    {
        $this->assertNotNull(self::$journalAId);

        $journals = FamilyJournal::getFamilyJournals(self::$famCodeA, 10, 0);
        $this->assertNotEmpty($journals);
        $this->assertSame('Grandma Memories in Devon', $journals[0]['title']);
        $this->assertSame(self::$famCodeA, $journals[0]['family_code']);
        $this->assertSame('Alice', $journals[0]['firstName']);
    }

    public function testEnforcesCrossFamilyJournalIsolation(): void
    {
        $this->assertNotNull(self::$journalAId);

        // User B from Family B tries to read Family A's journal
        $unauthorizedResult = FamilyJournal::getJournalById(self::$journalAId, self::$famCodeB);
        $this->assertNull($unauthorizedResult, 'Cross-family journal access must return null');

        // User A from Family A reading their own journal succeeds
        $authorizedResult = FamilyJournal::getJournalById(self::$journalAId, self::$famCodeA);
        $this->assertNotNull($authorizedResult);
        $this->assertSame('Grandma Memories in Devon', $authorizedResult['title']);
    }

    public function testCanCreateFamilyAnnouncement(): void
    {
        $newId = FamilyAnnouncement::createAnnouncement([
            'family_code' => self::$famCodeA,
            'author_id'   => self::$userAId,
            'title'       => 'Annual Summer Reunion 2026',
            'message'     => 'Please mark your calendars for August 15th at the cottage.',
            'priority'    => 'urgent',
            'type'        => 'reunion',
            'is_pinned'   => 1,
            'expires_at'  => date('Y-m-d', strtotime('+30 days'))
        ]);

        $this->assertNotEmpty($newId);
        self::$announcementAId = (int)$newId;
    }

    public function testCanRetrieveActiveAnnouncementsForOwnFamily(): void
    {
        $this->assertNotNull(self::$announcementAId);

        $announcements = FamilyAnnouncement::getActiveAnnouncements(self::$famCodeA, 5);
        $this->assertNotEmpty($announcements);
        $this->assertSame('Annual Summer Reunion 2026', $announcements[0]['title']);
        $this->assertSame('urgent', $announcements[0]['priority']);
        $this->assertSame('Alice', $announcements[0]['firstName']);

        // Verify Family B sees zero announcements
        $famBAnnouncements = FamilyAnnouncement::getActiveAnnouncements(self::$famCodeB, 5);
        $this->assertEmpty($famBAnnouncements, 'Family B must not see Family A announcements');
    }

    public function testCanDeleteAnnouncementWithAuthorization(): void
    {
        $this->assertNotNull(self::$announcementAId);

        // Unauthorized user cannot delete
        $unauthorizedDelete = FamilyAnnouncement::deleteAnnouncement(self::$announcementAId, self::$famCodeA, self::$userBId);
        $this->assertFalse($unauthorizedDelete, 'Unauthorized user must not be able to delete announcement');

        // Authorized author can delete
        $authorizedDelete = FamilyAnnouncement::deleteAnnouncement(self::$announcementAId, self::$famCodeA, self::$userAId);
        $this->assertTrue($authorizedDelete, 'Author must be able to delete announcement');

        // Verify deleted
        $remaining = FamilyAnnouncement::getActiveAnnouncements(self::$famCodeA, 5);
        $this->assertEmpty($remaining);
    }
}
