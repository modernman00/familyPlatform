<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\api\EcosystemMilestoneCallbackController;
use App\controller\members\MemoryMilestoneController;
use App\services\MemoryMilestoneService;
use App\services\MilestoneSurpriseBridgeService;
use App\services\PartyPlatformBridgeService;
use Src\Exceptions\ForbiddenException;
use Tests\Support\SocialFeedTestCase;

/**
 * Verifies the 3D Memories & Milestones Surprise Celebration Bridge,
 * Celebrant & Partner Privacy Shield, Full Name / Copy Cleanup, and
 * First-Clicker vs. Subsequent-Clickers PartyPlatform Handoff State Machine.
 */
final class MilestoneSurpriseBridgeTest extends SocialFeedTestCase
{
    private MemoryMilestoneService $milestoneService;
    private MilestoneSurpriseBridgeService $bridgeService;

    /** @var list<string> */
    private array $createdPersonalIds = [];

    /** @var list<int> */
    private array $createdNodeIds = [];

    /** @var list<int> */
    private array $createdUnionIds = [];

    /** @var list<string> */
    private array $createdMilestoneKeys = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->milestoneService = new MemoryMilestoneService($this->pdo);
        $this->bridgeService    = new MilestoneSurpriseBridgeService($this->pdo);

        $stmt = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->authorId,
            'OrganiserFirst',
            'OrganiserLast',
            $this->famCode,
            15,
            'January',
            1990,
        ]);
        $this->createdPersonalIds[] = $this->authorId;
    }

    protected function tearDown(): void
    {
        if ($this->createdMilestoneKeys !== []) {
            $ph = implode(',', array_fill(0, count($this->createdMilestoneKeys), '?'));
            $delLinks = $this->pdo->prepare(sprintf('DELETE FROM milestone_party_links WHERE milestone_key IN (%s)', $ph));
            $delLinks->execute($this->createdMilestoneKeys);
        }

        if ($this->createdUnionIds !== []) {
            $ph = implode(',', array_fill(0, count($this->createdUnionIds), '?'));
            $delUnions = $this->pdo->prepare(sprintf('DELETE FROM family_unions WHERE id IN (%s)', $ph));
            $delUnions->execute($this->createdUnionIds);
        }

        if ($this->createdNodeIds !== []) {
            $ph = implode(',', array_fill(0, count($this->createdNodeIds), '?'));
            $delNodes = $this->pdo->prepare(sprintf('DELETE FROM family_nodes WHERE id IN (%s)', $ph));
            $delNodes->execute($this->createdNodeIds);
        }

        if ($this->createdPersonalIds !== []) {
            $ph = implode(',', array_fill(0, count($this->createdPersonalIds), '?'));
            $delPersonal = $this->pdo->prepare(sprintf('DELETE FROM personal WHERE id IN (%s)', $ph));
            $delPersonal->execute($this->createdPersonalIds);
        }

        parent::tearDown();
    }

    public function testFullNameIsPreservedAndGenericAnotherWonderfulYearIsRemoved(): void
    {
        $celebrantId = 'CELEB_' . bin2hex(random_bytes(4));
        $longFirst   = 'Oluwatobiloba Oluwadamilola';
        $longLast    = 'Adeyemi-Olaogun-Babatunde';

        $stmt = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $celebrantId,
            $longFirst,
            $longLast,
            $this->famCode,
            (int) date('j'),
            (string) date('F'),
            (int) date('Y') - 34,
        ]);
        $this->createdPersonalIds[] = $celebrantId;

        $milestones = $this->milestoneService->getUpcomingMilestones($this->authorId, [$this->famCode], 7);
        $matched = null;
        foreach ($milestones as $m) {
            if (($m['user_id'] ?? '') === $celebrantId && ($m['type'] ?? '') === 'birthday') {
                $matched = $m;
                break;
            }
        }

        $this->assertNotNull($matched);
        $this->assertSame("{$longFirst} {$longLast}", $matched['full_name']);
        $this->assertSame('', $matched['meaningful_message']);
        $this->assertStringNotContainsStringIgnoringCase('another wonderful year', (string) $matched['meaningful_message']);
        $this->assertTrue($matched['can_contribute_surprise']);
        $this->assertNotEmpty($matched['sign_card_url']);
        $this->assertNotEmpty($matched['record_video_url']);
    }

    public function testBirthdayCelebrantNeverSeesOrLaunchesOwnSurprise(): void
    {
        $celebrantId = 'SELF_BDAY_' . bin2hex(random_bytes(4));
        $stmt = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $celebrantId,
            'Chidinma',
            'Olaogun',
            $this->famCode,
            (int) date('j'),
            (string) date('F'),
            (int) date('Y') - 29,
        ]);
        $this->createdPersonalIds[] = $celebrantId;

        // 1. Celebrant fetches their own milestones -> surprise fields strictly stripped
        $selfView = $this->milestoneService->getUpcomingMilestones($celebrantId, [$this->famCode], 7);
        $foundSelf = null;
        foreach ($selfView as $m) {
            if (($m['user_id'] ?? '') === $celebrantId && ($m['type'] ?? '') === 'birthday') {
                $foundSelf = $m;
                break;
            }
        }
        $this->assertNotNull($foundSelf);
        $this->assertFalse($foundSelf['can_contribute_surprise']);
        $this->assertSame('', $foundSelf['sign_card_url']);
        $this->assertSame('', $foundSelf['record_video_url']);

        // 2. Celebrant attempts direct URL tamper on launch endpoint -> 403 Forbidden
        $this->expectException(ForbiddenException::class);
        $this->bridgeService->resolveLaunchRedirectUrl(
            $celebrantId,
            [$this->famCode],
            'birthday',
            $celebrantId,
            'card'
        );
    }

    public function testAnniversaryHidesSurpriseFromBothPartnersButAllowsRelatives(): void
    {
        $partner1Id = 'P1_' . bin2hex(random_bytes(4));
        $partner2Id = 'P2_' . bin2hex(random_bytes(4));

        $insP = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, ?, ?, ?, 10, \'March\', 1988)'
        );
        $insP->execute([$partner1Id, 'Tunde', 'Olaogun', $this->famCode]);
        $insP->execute([$partner2Id, 'Adaeze', 'Olaogun', $this->famCode]);
        $this->createdPersonalIds[] = $partner1Id;
        $this->createdPersonalIds[] = $partner2Id;

        $insNode = $this->pdo->prepare(
            'INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
             VALUES (?, ?, ?, ?, ?, 0)'
        );
        $insNode->execute([$this->famCode, $partner1Id, 'Tunde', 'Olaogun', 'Male']);
        $node1Id = (int) $this->pdo->lastInsertId();
        $this->createdNodeIds[] = $node1Id;

        $insNode->execute([$this->famCode, $partner2Id, 'Adaeze', 'Olaogun', 'Female']);
        $node2Id = (int) $this->pdo->lastInsertId();
        $this->createdNodeIds[] = $node2Id;

        $marriageDate = sprintf('%04d-%s', (int) date('Y') - 10, date('m-d'));
        $insUnion = $this->pdo->prepare(
            "INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_date, marriage_year, is_current)
             VALUES (?, ?, ?, 'married', ?, ?, 1)"
        );
        $insUnion->execute([$this->famCode, $node1Id, $node2Id, $marriageDate, (int) date('Y') - 10]);
        $unionId = (int) $this->pdo->lastInsertId();
        $this->createdUnionIds[] = $unionId;

        // Partner 1 must NOT see surprise CTAs
        $p1List = $this->milestoneService->getUpcomingMilestones($partner1Id, [$this->famCode], 7);
        $p1Anniv = $this->findAnniversary($p1List, 'Tunde Olaogun & Adaeze Olaogun');
        $this->assertNotNull($p1Anniv);
        $this->assertFalse($p1Anniv['can_contribute_surprise']);

        // Partner 2 must NOT see surprise CTAs
        $p2List = $this->milestoneService->getUpcomingMilestones($partner2Id, [$this->famCode], 7);
        $p2Anniv = $this->findAnniversary($p2List, 'Tunde Olaogun & Adaeze Olaogun');
        $this->assertNotNull($p2Anniv);
        $this->assertFalse($p2Anniv['can_contribute_surprise']);

        // Other relative (authorId) MUST see surprise CTAs and full couple name
        $relList = $this->milestoneService->getUpcomingMilestones($this->authorId, [$this->famCode], 7);
        $relAnniv = $this->findAnniversary($relList, 'Tunde Olaogun & Adaeze Olaogun');
        $this->assertNotNull($relAnniv);
        $this->assertTrue($relAnniv['can_contribute_surprise']);
        $this->assertNotEmpty($relAnniv['sign_card_url']);
        $this->assertNotEmpty($relAnniv['record_video_url']);

        // Partner 2 direct launch attempt must throw 403 Forbidden
        try {
            $this->bridgeService->resolveLaunchRedirectUrl($partner2Id, [$this->famCode], 'anniversary', (string) $unionId, 'goodwill');
            $this->fail('Expected ForbiddenException when Partner 2 attempts to access own surprise anniversary.');
        } catch (ForbiddenException $expected) {
            $this->assertStringContainsString('hidden from the celebrating couple', $expected->getMessage());
        }
    }

    public function testCrossFamilyIdorLaunchIsBlockedWith403(): void
    {
        $otherFam  = 'VICTIM_FAM_' . bin2hex(random_bytes(3));
        $victimUid = 'VICTIM_BDAY_' . bin2hex(random_bytes(4));

        $stmt = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, \'Secret\', \'Relative\', ?, ?, ?, 1992)'
        );
        $stmt->execute([$victimUid, $otherFam, (int) date('j'), (string) date('F')]);
        $this->createdPersonalIds[] = $victimUid;

        $this->expectException(ForbiddenException::class);
        $this->bridgeService->resolveLaunchRedirectUrl(
            $this->authorId,
            [$this->famCode],
            'birthday',
            $victimUid,
            'card'
        );
    }

    public function testFirstClickerRoutesToClaimThenSubsequentRelativesRouteDirectlyToSharedEvent(): void
    {
        $celebrantId = 'SHARED_BDAY_' . bin2hex(random_bytes(4));
        $cousin2Id   = 'COUSIN_TWO_' . bin2hex(random_bytes(4));

        $stmt = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$celebrantId, 'Babatunde', 'Fashola-Olaogun', $this->famCode, (int) date('j'), (string) date('F'), 1986]);
        $stmt->execute([$cousin2Id, 'Funke', 'Olaogun', $this->famCode, 20, 'May', 1994]);
        $this->createdPersonalIds[] = $celebrantId;
        $this->createdPersonalIds[] = $cousin2Id;

        // 1. Before claim: First relative clicking receives signed standalone claim URL on PartyPlatform
        $firstClickUrl = $this->bridgeService->resolveLaunchRedirectUrl(
            $this->authorId,
            [$this->famCode],
            'birthday',
            $celebrantId,
            'card'
        );
        $this->assertStringContainsString('/standalone/family-milestone-claim?', $firstClickUrl);
        $this->assertStringContainsString('feature=card', $firstClickUrl);
        $this->assertStringContainsString('sig=', $firstClickUrl);

        // 2. Simulate PartyPlatform completing standalone host authentication & S2S callback
        $normFam = MilestoneSurpriseBridgeService::normalizeFamilyCode($this->famCode);
        $year    = (int) date('Y');
        $mKey    = MilestoneSurpriseBridgeService::buildMilestoneKey($normFam, 'birthday', $celebrantId, $year);
        $this->createdMilestoneKeys[] = $mKey;

        $saved = $this->bridgeService->recordCallbackPayload([
            'milestone_key'       => $mKey,
            'family_code'         => $normFam,
            'milestone_type'      => 'birthday',
            'entity_id'           => $celebrantId,
            'celebration_year'    => $year,
            'party_event_id'      => 'EVT7788',
            'party_manager_id'    => 'MGR_FIRST',
            'claimed_by_user_id'  => $this->authorId,
            'card_guest_url'      => '/card/abcdef1234567890abcdef1234567890/sign?invite=GRP99887',
            'goodwill_guest_url'  => '/goodwill/video?event_id=EVT7788&token=hmac_tok_12345678901234',
            'card_manage_url'     => '/manager/celebratory-cards?event_id=EVT7788',
            'goodwill_manage_url' => '/manager/goodwill-videos?event_id=EVT7788',
        ]);
        $this->assertSame('EVT7788', $saved['party_event_id']);

        // 3. Subsequent relative clicks "Sign / Send a Card" -> goes directly to shared card sign page!
        $secondClickCard = $this->bridgeService->resolveLaunchRedirectUrl(
            $cousin2Id,
            [$this->famCode],
            'birthday',
            $celebrantId,
            'card'
        );
        $this->assertStringEndsWith('/card/abcdef1234567890abcdef1234567890/sign?invite=GRP99887', $secondClickCard);
        $this->assertStringNotContainsString('family-milestone-claim', $secondClickCard);

        // 4. Subsequent relative clicks "Record Goodwill Video" -> goes directly to shared video drop on SAME event!
        $secondClickVideo = $this->bridgeService->resolveLaunchRedirectUrl(
            $cousin2Id,
            [$this->famCode],
            'birthday',
            $celebrantId,
            'goodwill'
        );
        $this->assertStringEndsWith('/goodwill/video?event_id=EVT7788&token=hmac_tok_12345678901234', $secondClickVideo);

        // 4b. Organiser (authorId) sees Manage & Reveal controls and can launch them; Cousin 2 does NOT
        $orgMilestones = $this->milestoneService->getUpcomingMilestones($this->authorId, [$this->famCode], 7);
        $cousinMilestones = $this->milestoneService->getUpcomingMilestones($cousin2Id, [$this->famCode], 7);
        $orgMs = null;
        $cousinMs = null;
        foreach ($orgMilestones as $item) {
            if (($item['user_id'] ?? '') === $celebrantId && ($item['type'] ?? '') === 'birthday') {
                $orgMs = $item;
            }
        }
        foreach ($cousinMilestones as $item) {
            if (($item['user_id'] ?? '') === $celebrantId && ($item['type'] ?? '') === 'birthday') {
                $cousinMs = $item;
            }
        }
        $this->assertNotNull($orgMs);
        $this->assertTrue($orgMs['is_surprise_organiser']);
        $this->assertNotEmpty($orgMs['manage_cards_url']);
        $this->assertNotEmpty($orgMs['manage_videos_url']);

        $this->assertNotNull($cousinMs);
        $this->assertFalse($cousinMs['is_surprise_organiser']);
        $this->assertSame('', $cousinMs['manage_cards_url']);

        $manageCardRedirect = $this->bridgeService->resolveLaunchRedirectUrl(
            $this->authorId,
            [$this->famCode],
            'birthday',
            $celebrantId,
            'manage_cards'
        );
        $this->assertStringEndsWith('/manager/celebratory-cards?event_id=EVT7788', $manageCardRedirect);

        try {
            $this->bridgeService->resolveLaunchRedirectUrl($cousin2Id, [$this->famCode], 'birthday', $celebrantId, 'manage_cards');
            $this->fail('Non-organiser relative must not access host management route.');
        } catch (ForbiddenException $e) {
            $this->assertStringContainsString('Only the relative who started', $e->getMessage());
        }

        // 4c. Canonical key invariant: birth famCode and married familyCode2 produce the exact same milestone_key
        $this->assertSame(
            MilestoneSurpriseBridgeService::buildMilestoneKey('BIRTH_FAM', 'birthday', $celebrantId, $year),
            MilestoneSurpriseBridgeService::buildMilestoneKey('MARRIED_FAM', 'birthday', $celebrantId, $year)
        );

        // 5. Celebrant STILL receives 403 even after the event is claimed!
        try {
            $this->bridgeService->resolveLaunchRedirectUrl($celebrantId, [$this->famCode], 'birthday', $celebrantId, 'card');
            $this->fail('Celebrant must never access the claimed surprise card.');
        } catch (ForbiddenException $e) {
            $this->assertStringContainsString('hidden from the celebrant', $e->getMessage());
        }
    }

    public function testAdversarialForgedHmacAndMaliciousRedirectPayloadsAreRejected(): void
    {
        // 1. Forged HMAC signature on S2S callback must fail verification
        $rawBody = json_encode(['milestone_key' => 'FORGED:birthday:1:2026']);
        $this->assertFalse(
            MilestoneSurpriseBridgeService::verifyEcosystemSignature(
                (string) $rawBody,
                (string) time(),
                'sha256=deadbeef00000000000000000000000000000000000000000000000000000000'
            )
        );

        // 2. Expired timestamp (>300s drift) with real secret must fail verification
        $staleTs  = time() - 600;
        $staleSig = PartyPlatformBridgeService::signPayload($staleTs, (string) $rawBody);
        $this->assertFalse(
            MilestoneSurpriseBridgeService::verifyEcosystemSignature((string) $rawBody, (string) $staleTs, $staleSig)
        );

        // 3. Open-redirect / javascript: injection in callback URLs must throw InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->bridgeService->recordCallbackPayload([
            'milestone_key'      => 'FAM:birthday:U1:2026',
            'family_code'        => 'FAM',
            'milestone_type'     => 'birthday',
            'entity_id'          => 'U1',
            'celebration_year'   => 2026,
            'party_event_id'     => 'EVT1234',
            'card_guest_url'     => '//evil.attacker.example/steal',
            'goodwill_guest_url' => 'javascript:alert(1)',
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $list
     * @return array<string, mixed>|null
     */
    private function findAnniversary(array $list, string $fullName): ?array
    {
        foreach ($list as $item) {
            if (($item['type'] ?? '') === 'anniversary' && ($item['full_name'] ?? '') === $fullName) {
                return $item;
            }
        }
        return null;
    }
}
