<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\Organogram;
use App\services\FamilyTreeAccessService;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Db;

final class FamilyTreeAccessControlTest extends TestCase
{
    private PDO $pdo;
    private string $ownerId;
    private string $ownerFamCode;
    private string $relativeId;
    private string $outsiderId;
    private string $outsiderFamCode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        FamilyTreeAccessService::ensureSchema($this->pdo);

        $suffix = bin2hex(random_bytes(4));
        $this->ownerFamCode = 'FAM_OWNER_' . $suffix;
        $this->outsiderFamCode = 'FAM_OUTSIDER_' . $suffix;

        $this->ownerId = 'OWNER_' . $suffix;
        $this->relativeId = 'RELATIVE_' . $suffix;
        $this->outsiderId = 'OUTSIDER_' . $suffix;

        // Provision Owner
        $stmt = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmt->execute([$this->ownerId, 'OwnerFirst', 'OwnerLast', $this->ownerFamCode]);

        // Provision Same-Family Relative
        $stmt->execute([$this->relativeId, 'RelativeFirst', 'RelativeLast', $this->ownerFamCode]);

        // Provision Outsider (Different Family Code)
        $stmt->execute([$this->outsiderId, 'OutsiderFirst', 'OutsiderLast', $this->outsiderFamCode]);

        // Provision Contact entries
        $stmtC = $this->pdo->prepare('INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)');
        $stmtC->execute([$this->ownerId, 'owner_' . $suffix . '@test.com', 'United Kingdom', '+447000000001', 'on']);
        $stmtC->execute([$this->relativeId, 'rel_' . $suffix . '@test.com', 'United Kingdom', '+447000000002', 'on']);
        $stmtC->execute([$this->outsiderId, 'out_' . $suffix . '@test.com', 'United States', '+12025550100', 'on']);
    }

    protected function tearDown(): void
    {
        // Cleanup test fixtures
        $uids = [$this->ownerId, $this->relativeId, $this->outsiderId];
        $delViews = $this->pdo->prepare('DELETE FROM family_tree_views WHERE tree_owner_id = ? OR viewer_id = ?');
        $delPriv = $this->pdo->prepare('DELETE FROM family_tree_privacy_settings WHERE user_id = ?');
        $delRules = $this->pdo->prepare('DELETE FROM family_tree_access_rules WHERE tree_owner_id = ? OR target_user_id = ?');
        $delPers = $this->pdo->prepare('DELETE FROM personal WHERE id = ?');
        $delCont = $this->pdo->prepare('DELETE FROM contact WHERE id = ?');
        $delUF = $this->pdo->prepare('DELETE FROM user_families WHERE user_id = ?');
        $delReq = $this->pdo->prepare('DELETE FROM requestMgt WHERE approver_id = ? OR requester_id = ?');

        foreach ($uids as $uid) {
            $delViews->execute([$uid, $uid]);
            $delPriv->execute([$uid]);
            $delRules->execute([$uid, $uid]);
            $delPers->execute([$uid]);
            $delCont->execute([$uid]);
            $delUF->execute([$uid]);
            $delReq->execute([$uid, $uid]);
        }

        unset($_SESSION['id'], $_SESSION['famCode'], $_SESSION['token'], $_SERVER['REQUEST_METHOD'], $_POST);
        parent::tearDown();
    }

    /**
     * Requirement 1: Users should be able to see people who viewed their family tree
     */
    public function test_users_can_see_people_who_viewed_their_family_tree(): void
    {
        // 1. Initially 0 viewers
        $initial = FamilyTreeAccessService::getTreeViewers($this->ownerId, null, $this->pdo);
        $this->assertSame(0, $initial['total_views']);
        $this->assertSame(0, $initial['unique_viewers']);
        $this->assertEmpty($initial['viewers']);

        // 2. Relative views owner's family tree
        FamilyTreeAccessService::recordTreeView($this->relativeId, $this->ownerId, $this->ownerFamCode, $this->pdo);

        // 3. Owner retrieves viewers list
        $viewersData = FamilyTreeAccessService::getTreeViewers($this->ownerId, null, $this->pdo);
        $this->assertSame(1, $viewersData['total_views']);
        $this->assertSame(1, $viewersData['unique_viewers']);
        $this->assertCount(1, $viewersData['viewers']);

        $entry = $viewersData['viewers'][0];
        $this->assertSame($this->relativeId, $entry['viewer_id']);
        $this->assertSame('RelativeFirst RelativeLast', $entry['full_name']);
        $this->assertSame(1, $entry['view_count']);
        $this->assertTrue($entry['is_allowed']);

        // 4. Relative views a second time (atomic counter increment)
        FamilyTreeAccessService::recordTreeView($this->relativeId, $this->ownerId, $this->ownerFamCode, $this->pdo);

        $viewersData2 = FamilyTreeAccessService::getTreeViewers($this->ownerId, null, $this->pdo);
        $this->assertSame(2, $viewersData2['total_views']);
        $this->assertSame(1, $viewersData2['unique_viewers']);
        $this->assertSame(2, $viewersData2['viewers'][0]['view_count']);
    }

    /**
     * Requirement 2: Family tree can ONLY be viewed by family member sharing same family code
     */
    public function test_family_tree_can_only_be_viewed_by_same_family_code_member(): void
    {
        // Case A: Relative sharing same family code is ALLOWED
        $resRelative = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertTrue($resRelative['allowed'], 'Relative sharing the same family code must be allowed to view tree.');

        // Case B: Outsider with different family code is STRICTLY BLOCKED
        $resOutsider = FamilyTreeAccessService::canViewFamilyTree(
            $this->outsiderId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertFalse($resOutsider['allowed'], 'Member with different family code must be blocked.');
        $this->assertSame('different_family_code', $resOutsider['code']);

        // Case C: Even if Outsider has an approved connection in requestMgt, they STILL cannot view the tree!
        $reqStmt = $this->pdo->prepare("
            INSERT INTO requestMgt (requester_id, approver_id, status)
            VALUES (?, ?, 'approved')
        ");
        $reqStmt->execute([$this->outsiderId, $this->ownerId]);

        $resConnectedOutsider = FamilyTreeAccessService::canViewFamilyTree(
            $this->outsiderId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertFalse(
            $resConnectedOutsider['allowed'],
            'External approved connection without same family code must still be blocked from family tree.'
        );
        $this->assertSame('different_family_code', $resConnectedOutsider['code']);
    }

    /**
     * Requirement 3: Users should have the power to limit access to specific persons as well
     */
    public function test_user_can_limit_access_to_specific_persons_in_family(): void
    {
        // 1. By default, relative has access
        $check1 = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertTrue($check1['allowed']);

        // 2. Owner limits access for this specific relative
        $ruleRes = FamilyTreeAccessService::setPersonAccessRule(
            $this->ownerId,
            $this->relativeId,
            'restrict',
            $this->pdo
        );
        $this->assertTrue($ruleRes['success']);
        $this->assertSame('deny', $ruleRes['rule_type']);
        $this->assertFalse($ruleRes['is_allowed']);

        // 3. Now relative is blocked from viewing owner's family tree
        $check2 = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertFalse($check2['allowed'], 'Restricted relative must be denied access to tree.');
        $this->assertSame('restricted_person', $check2['code']);

        // 4. Owner restores access for this relative
        $restoreRes = FamilyTreeAccessService::setPersonAccessRule(
            $this->ownerId,
            $this->relativeId,
            'allow',
            $this->pdo
        );
        $this->assertTrue($restoreRes['success']);

        $check3 = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertTrue($check3['allowed'], 'Restored relative must have tree access again.');
    }

    /**
     * Test specific_persons allowlist mode and only_me private lock
     */
    public function test_specific_persons_allowlist_and_only_me_lock(): void
    {
        // Switch to 'specific_persons' mode with empty allowlist
        $saveRes = FamilyTreeAccessService::saveAccessControlPolicy(
            $this->ownerId,
            FamilyTreeAccessService::MODE_SPECIFIC_PERSONS,
            [], // no allowed members
            [],
            false,
            $this->pdo
        );
        $this->assertTrue($saveRes['success']);

        // Relative sharing same code is now blocked because they are not in the allowlist
        $check1 = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertFalse($check1['allowed']);
        $this->assertSame('not_in_allowlist', $check1['code']);

        // Switch to 'only_me' mode
        FamilyTreeAccessService::saveAccessControlPolicy(
            $this->ownerId,
            FamilyTreeAccessService::MODE_ONLY_ME,
            [$this->relativeId],
            [],
            false,
            $this->pdo
        );

        $check2 = FamilyTreeAccessService::canViewFamilyTree(
            $this->relativeId,
            $this->ownerId,
            $this->ownerFamCode,
            $this->pdo
        );
        $this->assertFalse($check2['allowed']);
        $this->assertSame('locked_only_me', $check2['code']);
    }

    /**
     * Test API Endpoints for Tree Viewers and Access Management
     */
    public function test_api_endpoints_for_tree_viewers_and_access_settings(): void
    {
        $_SESSION['id'] = $this->ownerId;
        $_SESSION['famCode'] = $this->ownerFamCode;
        $token = 'csrf_token_' . bin2hex(random_bytes(6));
        $_SESSION['token'] = $token;

        $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();

        // 1. Record a view from relative
        FamilyTreeAccessService::recordTreeView($this->relativeId, $this->ownerId, $this->ownerFamCode, $this->pdo);

        // 2. GET /member/organogram/tree-viewers
        ob_start();
        $controller->getTreeViewers();
        $rawViewers = ob_get_clean() ?: '';
        $respViewers = json_decode($rawViewers, true) ?: [];

        $this->assertTrue($respViewers['success'] ?? false);
        $this->assertSame(1, $respViewers['unique_viewers'] ?? 0);
        $this->assertCount(1, $respViewers['viewers'] ?? []);

        // 3. GET /member/organogram/access-settings
        ob_start();
        $controller->getAccessSettings();
        $rawSettings = ob_get_clean() ?: '';
        $respSettings = json_decode($rawSettings, true) ?: [];

        $this->assertTrue($respSettings['success'] ?? false);
        $this->assertNotEmpty($respSettings['members'] ?? []);

        // 4. POST /member/organogram/access-rule with valid CSRF
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'target_user_id' => $this->relativeId,
            'action' => 'restrict',
            'token' => $token,
            '_token' => $token,
        ];

        ob_start();
        $controller->setPersonAccessRule();
        $rawRule = ob_get_clean() ?: '';
        $respRule = json_decode($rawRule, true) ?: [];

        $this->assertTrue($respRule['success'] ?? false);
        $this->assertSame('deny', $respRule['rule_type'] ?? '');
    }

    /**
     * Requirement 4: All of these once implemented should be made obvious to the users
     */
    public function test_ui_templates_make_viewers_and_privacy_controls_obvious(): void
    {
        $organogramBlade = (string)file_get_contents(__DIR__ . '/../../resources/views/member/organogram.blade.php');
        $modalsBlade = (string)file_get_contents(__DIR__ . '/../../resources/views/member/includes/treeAccessModals.blade.php');

        // 1. Organogram must feature the obvious Who Viewed Tree and Limit Access buttons in the header
        $this->assertStringContainsString('id="btnWhoViewedTree"', $organogramBlade);
        $this->assertStringContainsString('openWhoViewedTreeModal()', $organogramBlade);
        $this->assertStringContainsString('id="btnTreeAccessControl"', $organogramBlade);
        $this->assertStringContainsString('openTreeAccessControlModal()', $organogramBlade);

        // 2. Organogram must display the Same Family Code Only badge & prominent security banner
        $this->assertStringContainsString('Same Family Code Only', $organogramBlade);
        $this->assertStringContainsString('id="treeSecurityBanner"', $organogramBlade);

        // 3. Modals template must include Who Viewed My Family Tree modal and Limit Family Tree Access modal
        $this->assertStringContainsString('id="whoViewedTreeModal"', $modalsBlade);
        $this->assertStringContainsString('Who Viewed My Family Tree', $modalsBlade);
        $this->assertStringContainsString('id="treeAccessControlModal"', $modalsBlade);
        $this->assertStringContainsString('Limit Family Tree Access', $modalsBlade);
        $this->assertStringContainsString('All Family Members (Default)', $modalsBlade);
        $this->assertStringContainsString('Only Specific Selected Persons', $modalsBlade);
        $this->assertStringContainsString('Only Me (Private Lock)', $modalsBlade);
    }
}
