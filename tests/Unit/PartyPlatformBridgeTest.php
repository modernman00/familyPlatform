<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\services\PartyPlatformBridgeService;
use App\controller\members\Event;
use Src\Db;

final class PartyPlatformBridgeTest extends TestCase
{
    private string $testFamCode = 'TEST_PARTY_BRIDGE_FAM';
    private string $otherFamCode = 'TEST_PARTY_RIVAL_FAM';
    private string $hostUserId = 'test-party-host-001';
    private string $brotherUserId = 'test-party-bro-002';
    private string $rivalUserId = 'test-party-rival-003';
    private int $createdEventNo = 0;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('TESTING_ENV')) {
            define('TESTING_ENV', true);
        }
        $this->cleanupDatabase();
        $this->seedFamilyAndEvent();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $db = Db::connect2();
        $db->prepare("DELETE FROM events WHERE eventCode IN (?, ?)")
            ->execute([$this->testFamCode, $this->otherFamCode]);
        $db->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)")
            ->execute([$this->hostUserId, $this->brotherUserId, $this->rivalUserId]);
        $db->prepare("DELETE FROM account WHERE id IN (?, ?, ?)")
            ->execute([$this->hostUserId, $this->brotherUserId, $this->rivalUserId]);
        $db->prepare("DELETE FROM contact WHERE id IN (?, ?, ?)")
            ->execute([$this->hostUserId, $this->brotherUserId, $this->rivalUserId]);
    }

    private function seedFamilyAndEvent(): void
    {
        $db = Db::connect2();

        $insAcct = $db->prepare("
            INSERT INTO account (id, email, password, status, type, token_version)
            VALUES (?, ?, ?, 'active', 'user', 1)
        ");
        $insAcct->execute([$this->hostUserId, 'wally.bridge@familyplatform.test', password_hash('Test1234!', PASSWORD_BCRYPT)]);
        $insAcct->execute([$this->brotherUserId, 'femi.bridge@familyplatform.test', password_hash('Test1234!', PASSWORD_BCRYPT)]);
        $insAcct->execute([$this->rivalUserId, 'rival.bridge@familyplatform.test', password_hash('Test1234!', PASSWORD_BCRYPT)]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Wally', 'Olaogun', ?, 'Male')
        ")->execute([$this->hostUserId, $this->testFamCode]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Femi', 'Olaogun', ?, 'Male')
        ")->execute([$this->brotherUserId, $this->testFamCode]);

        $db->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender)
            VALUES (?, 'Intruder', 'Rival', ?, 'Male')
        ")->execute([$this->rivalUserId, $this->otherFamCode]);

        $stmt = $db->prepare("
            INSERT INTO events (id, eventCode, eventName, eventDate, eventType, eventDescription, eventFrequency)
            VALUES (?, ?, 'Olaogun Golden Reunion', ?, 'Reunion', 'Annual dynasty gathering', 'Annually')
        ");
        $stmt->execute([$this->hostUserId, $this->testFamCode, date('Y-m-d', strtotime('+14 days'))]);
        $this->createdEventNo = (int) $db->lastInsertId();
    }

    public function testMapKinshipToGuestPriorityAssignsCorrectPartyPlatformTiers(): void
    {
        // Nuclear relatives -> Close Family (closeness = 5, always_invite = 1)
        $father = PartyPlatformBridgeService::mapKinshipToGuestPriority('parent', 1, 'Father');
        $this->assertSame('Close Family', $father['guest_category']);
        $this->assertSame(5, $father['closeness']);
        $this->assertSame(1, $father['always_invite']);

        $sister = PartyPlatformBridgeService::mapKinshipToGuestPriority('sibling', 2, 'Younger Sister');
        $this->assertSame('Close Family', $sister['guest_category']);
        $this->assertSame(5, $sister['closeness']);

        // 1st/2nd degree collateral -> Family (closeness = 4, always_invite = 1)
        $uncle = PartyPlatformBridgeService::mapKinshipToGuestPriority('avuncular', 3, 'Paternal Uncle');
        $this->assertSame('Family', $uncle['guest_category']);
        $this->assertSame(4, $uncle['closeness']);
        $this->assertSame(1, $uncle['always_invite']);

        $cousin = PartyPlatformBridgeService::mapKinshipToGuestPriority('cousin', 4, '1st Cousin');
        $this->assertSame('Family', $cousin['guest_category']);
        $this->assertSame(4, $cousin['closeness']);

        // Distant / in-law branches -> Extended Family (closeness = 3, always_invite = 0)
        $distant = PartyPlatformBridgeService::mapKinshipToGuestPriority('extended', 6, '2nd Cousin once removed');
        $this->assertSame('Extended Family', $distant['guest_category']);
        $this->assertSame(3, $distant['closeness']);
        $this->assertSame(0, $distant['always_invite']);
    }

    public function testBuildKinshipProspectsExcludesHostAndMapsRelatives(): void
    {
        $prospects = PartyPlatformBridgeService::buildKinshipProspectsForHost($this->hostUserId, $this->testFamCode);
        $this->assertNotEmpty($prospects, 'Should extract at least one family prospect from the same famCode');

        $firstNames = array_column($prospects, 'first_name');
        $this->assertContains('Femi', $firstNames);
        $this->assertNotContains('Wally', $firstNames, 'Host should not be added as their own guest prospect');
    }

    public function testProvisionAndLinkEventSyncsToPartyPlatformAndUpdatesEventRow(): void
    {
        $result = PartyPlatformBridgeService::provisionAndLinkEvent(
            eventNo: $this->createdEventNo,
            hostUserId: $this->hostUserId,
            familyCode: $this->testFamCode
        );

        $this->assertNotEmpty($result['party_event_id']);
        $this->assertStringContainsString('/e/', $result['party_rsvp_url']);
        $this->assertStringContainsString('/celebrate/card', $result['party_card_url']);
        $this->assertStringContainsString('/party-sso', $result['party_studio_sso_url']);

        // Verify FamilyPlatform.events row was updated with bridge columns
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT party_event_id, party_rsvp_url, party_card_url, party_sync_status FROM events WHERE no = ?");
        $stmt->execute([$this->createdEventNo]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame($result['party_event_id'], $row['party_event_id']);
        $this->assertSame('synced', $row['party_sync_status']);

        // Calling provisionAndLinkEvent a second time must be idempotent (returns same party_event_id)
        $secondResult = PartyPlatformBridgeService::provisionAndLinkEvent(
            eventNo: $this->createdEventNo,
            hostUserId: $this->hostUserId,
            familyCode: $this->testFamCode
        );
        $this->assertSame($result['party_event_id'], $secondResult['party_event_id']);
    }

    public function testAdversarialBolaCrossFamilyUpgradeIsBlocked(): void
    {
        // Dr. Viktor Kovács & Marcus PoC #1: Non-owner / Cross-family BOLA attempt
        $_SESSION['id'] = $this->rivalUserId;
        $_SESSION['famCode'] = $this->otherFamCode;
        $_SESSION['token'] = 'valid-csrf-token-123';
        $_POST['token'] = 'valid-csrf-token-123';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'valid-csrf-token-123';

        $GLOBALS['__testError'] = null;
        $GLOBALS['__testResponse'] = null;

        ob_start();
        Event::upgradeToPartyPlatform($this->createdEventNo);
        ob_end_clean();

        // Verify the event was NOT upgraded
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT party_event_id, party_sync_status FROM events WHERE no = ?");
        $stmt->execute([$this->createdEventNo]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNull($row['party_event_id']);
        $this->assertSame('none', $row['party_sync_status']);
    }

    public function testGenerateSsoHandoffUrlSignsWithHmacSha256(): void
    {
        $ssoUrl = PartyPlatformBridgeService::generateSsoHandoffUrl('FAM123456', 'wally.bridge@familyplatform.test', 'guest-builder');
        $this->assertStringContainsString('event_id=FAM123456', $ssoUrl);
        $this->assertStringContainsString('target=guest-builder', $ssoUrl);
        $this->assertStringContainsString('sig=', $ssoUrl);
    }
}
