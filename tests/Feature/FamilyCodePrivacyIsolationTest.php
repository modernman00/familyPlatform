<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\BaseController;
use App\model\AllMembersData;
use App\repository\FeedRepository;
use App\services\FamilyMigrationService;
use App\services\KinWhisperService;
use App\services\OmniSearchService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class FamilyCodePrivacyIsolationTest extends TestCase
{
    private PDO $pdo;
    private string $suffix;
    private string $viewerId;
    private string $viewerFamCode;
    private string $sameFamId;
    private string $kinId;
    private string $kinFamCode;
    private string $kinSecondCode;
    private int $kinPostNo = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->suffix = strtoupper(bin2hex(random_bytes(3)));

        $this->viewerId = 'FCP_VWR_' . $this->suffix;
        $this->viewerFamCode = 'OLA' . $this->suffix;

        $this->sameFamId = 'FCP_FAM_' . $this->suffix;

        $this->kinId = 'FCP_KIN_' . $this->suffix;
        $this->kinFamCode = 'AKI' . $this->suffix;
        $this->kinSecondCode = 'LAF' . $this->suffix;

        $_SESSION = [
            'auth' => [
                'id' => $this->viewerId,
                'famCode' => $this->viewerFamCode,
                'email' => 'viewer_' . strtolower($this->suffix) . '@test.local',
                'type' => 'member',
            ],
            'id' => $this->viewerId,
            'famCode' => $this->viewerFamCode,
            'email' => 'viewer_' . strtolower($this->suffix) . '@test.local',
        ];

        $insPersonal = $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)'
        );
        $insPersonal->execute([$this->viewerId, 'Wally', 'Viewer' . $this->suffix, $this->viewerFamCode]);
        $insPersonal->execute([$this->sameFamId, 'Yomi', 'Sibling' . $this->suffix, $this->viewerFamCode]);
        $insPersonal->execute([$this->kinId, 'Eniola', 'Connected' . $this->suffix, $this->kinFamCode]);

        $insUf = $this->pdo->prepare(
            "INSERT INTO user_families (user_id, family_code, role, status) VALUES (?, ?, 'member', 'approved')"
        );
        $insUf->execute([$this->kinId, $this->kinSecondCode]);

        $insContact = $this->pdo->prepare(
            'INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)'
        );
        $insContact->execute([$this->viewerId, 'viewer_' . strtolower($this->suffix) . '@test.local', 'United Kingdom', '+447000000001', 'on']);
        $insContact->execute([$this->sameFamId, 'sibling_' . strtolower($this->suffix) . '@test.local', 'United Kingdom', '+447000000002', 'on']);
        $insContact->execute([$this->kinId, 'kin_' . strtolower($this->suffix) . '@test.local', 'United Kingdom', '+447000000003', 'on']);

        // Establish approved Connected Kin link between viewerId and kinId
        $insReq = $this->pdo->prepare(
            "INSERT INTO requestMgt (requester_id, requesterCode, approver_id, approverCode, status, relationship_category, relationship_role)
             VALUES (?, ?, ?, ?, 'Approved', 'family_friend', 'Family Friend')"
        );
        $insReq->execute([$this->kinId, $this->kinFamCode, $this->viewerId, $this->viewerFamCode]);

        // Also seed an active 'Request sent' row for getFriendRequestData testing
        $insPending = $this->pdo->prepare(
            "INSERT INTO requestMgt (requester_id, requesterCode, approver_id, approverCode, status, relationship_category, relationship_role)
             VALUES (?, ?, ?, ?, 'Request sent', 'in_laws', 'Cousin')"
        );
        $insPending->execute([$this->kinId, $this->kinFamCode, $this->viewerId, $this->viewerFamCode]);

        // Seed a public Connected Kin post from kinId
        $insPost = $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postFamCode, target_scope, postMessage, post_status)
             VALUES (?, ?, ?, 'all', ?, 'published')"
        );
        $insPost->execute([
            $this->kinId,
            'Eniola Connected' . $this->suffix,
            $this->kinFamCode,
            'Hello Connected Kin from outside branch ' . $this->suffix,
        ]);
        $this->kinPostNo = (int)$this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        if ($this->kinPostNo > 0) {
            $delPost = $this->pdo->prepare('DELETE FROM post WHERE post_no = ?');
            $delPost->execute([$this->kinPostNo]);
        }

        $delMig = $this->pdo->prepare('DELETE FROM family_migration_requests WHERE requester_id = ?');
        $delMig->execute([$this->viewerId]);

        $delReq = $this->pdo->prepare(
            'DELETE FROM requestMgt WHERE requester_id IN (?, ?, ?) OR approver_id IN (?, ?, ?)'
        );
        $delReq->execute([
            $this->viewerId, $this->sameFamId, $this->kinId,
            $this->viewerId, $this->sameFamId, $this->kinId,
        ]);

        $delContact = $this->pdo->prepare('DELETE FROM contact WHERE id IN (?, ?, ?)');
        $delContact->execute([$this->viewerId, $this->sameFamId, $this->kinId]);

        $delUf = $this->pdo->prepare('DELETE FROM user_families WHERE user_id IN (?, ?, ?)');
        $delUf->execute([$this->viewerId, $this->sameFamId, $this->kinId]);

        $delPersonal = $this->pdo->prepare('DELETE FROM personal WHERE id IN (?, ?, ?)');
        $delPersonal->execute([$this->viewerId, $this->sameFamId, $this->kinId]);

        $_SESSION = [];
        parent::tearDown();
    }

    public function testBaseControllerFamilyCodeVisibilityGates(): void
    {
        $this->assertTrue(BaseController::viewerCanSeeFamilyCode($this->viewerFamCode, $this->viewerId));
        $this->assertTrue(BaseController::viewerCanSeeFamilyCode('#' . strtolower($this->viewerFamCode), $this->viewerId));
        $this->assertFalse(BaseController::viewerCanSeeFamilyCode($this->kinFamCode, $this->viewerId));
        $this->assertFalse(BaseController::viewerCanSeeFamilyCode($this->kinSecondCode, $this->viewerId));
        // Even a Super Admin cannot view unshared family codes in social contexts unless explicitly requested
        $this->assertFalse(BaseController::viewerCanSeeFamilyCode($this->kinFamCode, '318422WALLY'));
    }

    public function testAllMembersDataMasksConnectedKinCodesAndBlocksCodeEnumeration(): void
    {
        $model = new AllMembersData();

        $allMembers = $model->getAllMembers($this->viewerId);
        $foundKin = null;
        $foundSibling = null;
        foreach ($allMembers as $row) {
            if (($row['id'] ?? '') === $this->kinId) {
                $foundKin = $row;
            }
            if (($row['id'] ?? '') === $this->sameFamId) {
                $foundSibling = $row;
            }
        }

        $this->assertNotNull($foundSibling);
        $this->assertSame($this->viewerFamCode, $foundSibling['famCode'] ?? '');

        $this->assertNotNull($foundKin);
        $this->assertSame('', (string)($foundKin['famCode'] ?? ''));
        $this->assertSame('', (string)($foundKin['otherFamCode'] ?? ''));
        $this->assertSame('', (string)($foundKin['familyCode2'] ?? ''));
        $kinJson = json_encode($foundKin) ?: '';
        $this->assertStringNotContainsString($this->kinFamCode, $kinJson);
        $this->assertStringNotContainsString($this->kinSecondCode, $kinJson);

        // Searching by the other family's code must NOT reveal or match kinId
        $enumResults = $model->searchMembers(
            $this->viewerId,
            $this->viewerFamCode,
            $this->kinFamCode,
            30,
            0
        );
        $enumIds = array_column($enumResults, 'id');
        $this->assertNotContains($this->kinId, $enumIds);

        // Pending friend request list must also mask requester's unshared family codes
        $reqRows = AllMembersData::getFriendRequestData($this->viewerId, 'Request sent');
        $this->assertIsArray($reqRows);
        foreach ($reqRows as $reqRow) {
            if (($reqRow['id'] ?? '') === $this->kinId) {
                $this->assertSame('', (string)($reqRow['famCode'] ?? ''));
                $this->assertSame('', (string)($reqRow['otherFamCode'] ?? ''));
                $this->assertSame('', (string)($reqRow['requesterCode'] ?? ''));
            }
        }
    }

    public function testFeedRepositoryMasksPostFamCodeForConnectedKinViewer(): void
    {
        $repo = new FeedRepository($this->pdo);

        $postDto = $repo->getPostByNo($this->kinPostNo, $this->viewerId);
        $this->assertNotNull($postDto);
        $postArr = $postDto->toArray();
        $this->assertSame('', $postArr['postFamCode'] ?? null);

        // Author themselves (and members of kinFamCode) still see kinFamCode
        $authorDto = $repo->getPostByNo($this->kinPostNo, $this->kinId);
        $this->assertNotNull($authorDto);
        $authorArr = $authorDto->toArray();
        $this->assertSame($this->kinFamCode, $authorArr['postFamCode'] ?? '');
    }

    public function testOmniSearchHidesOtherFamilyCodeFromConnectedKin(): void
    {
        $search = new OmniSearchService($this->pdo);

        $byName = $search->searchData('Connected' . $this->suffix, $this->viewerId, [$this->viewerFamCode]);
        $kinMatch = null;
        foreach ($byName['members'] as $m) {
            if (($m['id'] ?? '') === $this->kinId) {
                $kinMatch = $m;
            }
        }
        $this->assertNotNull($kinMatch);
        $encoded = json_encode($kinMatch) ?: '';
        $this->assertStringNotContainsString($this->kinFamCode, $encoded);
        $this->assertStringContainsString('Connected Kin', $encoded);

        $bySecretCode = $search->searchData($this->kinFamCode, $this->viewerId, [$this->viewerFamCode]);
        $this->assertSame([], $bySecretCode['members']);
    }

    public function testKinWhisperEligibleContactsMaskOtherFamilyCode(): void
    {
        $contacts = KinWhisperService::getConnectedKinMembers($this->viewerId);
        $kinContact = null;
        $siblingContact = null;
        foreach ($contacts as $c) {
            if (($c['id'] ?? '') === $this->kinId) {
                $kinContact = $c;
            }
            if (($c['id'] ?? '') === $this->sameFamId) {
                $siblingContact = $c;
            }
        }

        $this->assertNotNull($siblingContact);
        $this->assertSame($this->viewerFamCode, $siblingContact['famCode'] ?? '');

        $this->assertNotNull($kinContact);
        $this->assertSame('', $kinContact['famCode'] ?? null);
        $this->assertStringNotContainsString($this->kinFamCode, (string)($kinContact['description'] ?? ''));
    }

    public function testMigrationRequestViaTargetUserDoesNotLeakTargetFamilyCode(): void
    {
        $migSvc = new FamilyMigrationService($this->pdo);
        $res = $migSvc->createRequest($this->viewerId, '', $this->kinId, 'Joining family');

        $this->assertTrue($res['success'] ?? false);
        $this->assertStringNotContainsString($this->kinFamCode, (string)($res['message'] ?? ''));
    }
}
