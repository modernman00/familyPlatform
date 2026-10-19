<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\model\AllMembersData;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class DirectorySearchSecurityTest extends TestCase
{
    private PDO $pdo;
    private string $callerId;
    private string $callerFamCode;
    private string $targetMemberId;
    private string $targetFamCode;
    private string $privateMemberId;
    private string $privateFamCode;
    private AllMembersData $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->model = new AllMembersData();

        $suffix = bin2hex(random_bytes(4));
        $this->callerId = 'OLA_CALLER_' . $suffix;
        $this->callerFamCode = 'FAM_CALLER_' . $suffix;

        $this->targetMemberId = 'OLA_TARGET_' . $suffix;
        $this->targetFamCode = 'FAM_TARGET_' . $suffix;

        $this->privateMemberId = 'OLA_PRIV_' . $suffix;
        $this->privateFamCode = 'FAM_PRIV_' . $suffix;

        // Provision Caller
        $stmtCallerP = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmtCallerP->execute([$this->callerId, 'CallerFirst', 'CallerLast', $this->callerFamCode]);

        $stmtCallerC = $this->pdo->prepare('INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)');
        $stmtCallerC->execute([$this->callerId, 'caller_' . $suffix . '@test.com', 'United Kingdom', '+447801111111', 'on']);

        // Provision Public Searchable Member (Different Family)
        $stmtTargetP = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmtTargetP->execute([$this->targetMemberId, 'Temitope' . $suffix, 'Olaogun' . $suffix, $this->targetFamCode]);

        $stmtTargetC = $this->pdo->prepare('INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)');
        $stmtTargetC->execute([$this->targetMemberId, 'tope_' . $suffix . '@gmail.com', 'United Kingdom', '+447809650816', 'on']);

        // Provision Private Opted-Out Member (Different Family, show_my_profile = off)
        $stmtPrivP = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmtPrivP->execute([$this->privateMemberId, 'SecretFirst' . $suffix, 'SecretLast' . $suffix, $this->privateFamCode]);

        $stmtPrivC = $this->pdo->prepare('INSERT INTO contact (id, email, country, mobile, show_my_profile) VALUES (?, ?, ?, ?, ?)');
        $stmtPrivC->execute([$this->privateMemberId, 'secret_' . $suffix . '@gmail.com', 'United Kingdom', '+447809999999', 'off']);
    }

    protected function tearDown(): void
    {
        $stmtDelContact = $this->pdo->prepare('DELETE FROM contact WHERE id IN (?, ?, ?)');
        $stmtDelContact->execute([$this->callerId, $this->targetMemberId, $this->privateMemberId]);

        $stmtDelPersonal = $this->pdo->prepare('DELETE FROM personal WHERE id IN (?, ?, ?)');
        $stmtDelPersonal->execute([$this->callerId, $this->targetMemberId, $this->privateMemberId]);

        parent::tearDown();
    }

    public function test_search_finds_member_by_first_name_case_insensitive(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            'temitope' . $suffix
        );

        $this->assertNotEmpty($results, 'Search by lowercase first name must return matching member.');
        $ids = array_column($results, 'id');
        $this->assertContains($this->targetMemberId, $ids);
    }

    public function test_search_finds_member_by_last_name(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            'Olaogun' . $suffix
        );

        $this->assertNotEmpty($results, 'Search by last name must return matching member.');
        $ids = array_column($results, 'id');
        $this->assertContains($this->targetMemberId, $ids);
    }

    public function test_search_finds_member_by_full_name(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            'Temitope' . $suffix . ' Olaogun' . $suffix
        );

        $this->assertNotEmpty($results, 'Search by full combined name (first + last) must return member.');
        $ids = array_column($results, 'id');
        $this->assertContains($this->targetMemberId, $ids);
    }

    public function test_search_finds_member_by_email(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        $email = 'tope_' . $suffix . '@gmail.com';

        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            $email
        );

        $this->assertNotEmpty($results, 'Search by email address must return member.');
        $ids = array_column($results, 'id');
        $this->assertContains($this->targetMemberId, $ids);
    }

    public function test_search_finds_member_by_mobile_digits_even_without_country_code(): void
    {
        // Stored as +447809650816, searching for digits 7809650816
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            '7809650816'
        );

        $this->assertNotEmpty($results, 'Search by mobile digits without country prefix must match stored mobile.');
        $ids = array_column($results, 'id');
        $this->assertContains($this->targetMemberId, $ids);
    }

    public function test_search_excludes_members_who_opted_out_of_directory_search(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        // Caller belongs to $this->callerFamCode, target has show_my_profile = 'off' in $this->privateFamCode
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            'SecretFirst' . $suffix
        );

        $ids = array_column($results, 'id');
        $this->assertNotContains(
            $this->privateMemberId,
            $ids,
            'Members with show_my_profile = off must never appear in search for cross-family callers.'
        );
    }

    public function test_search_allows_opted_out_member_to_be_found_by_own_family_members(): void
    {
        $suffix = explode('_', $this->targetMemberId)[2];
        // When searching as a member of the SAME family ($this->privateFamCode)
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->privateFamCode,
            'SecretFirst' . $suffix
        );

        $ids = array_column($results, 'id');
        $this->assertContains(
            $this->privateMemberId,
            $ids,
            'Members with show_my_profile = off must remain searchable by members of their own family code.'
        );
    }

    public function test_search_excludes_the_requester_themselves(): void
    {
        $results = $this->model->searchMembers(
            $this->callerId,
            $this->callerFamCode,
            'CallerFirst'
        );

        $ids = array_column($results, 'id');
        $this->assertNotContains(
            $this->callerId,
            $ids,
            'A member searching directory must never receive their own profile in the search results.'
        );
    }

    public function test_red_team_sql_injection_payloads_neutralized(): void
    {
        $maliciousPayloads = [
            "' OR '1'='1",
            "admin'--",
            "' UNION SELECT id, firstName, lastName, famCode, created_at, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l' FROM personal --",
            "%' AND 1=0 UNION ALL SELECT * FROM contact WHERE '1'='1",
            "1'; DROP TABLE non_existent_dummy; --",
        ];

        foreach ($maliciousPayloads as $payload) {
            $results = $this->model->searchMembers(
                $this->callerId,
                $this->callerFamCode,
                $payload
            );

            $this->assertIsArray($results, 'Search with attack payload must return a safe array without exception.');
            $ids = array_column($results, 'id');
            // Malicious payload must not dump the private member
            $this->assertNotContains(
                $this->privateMemberId,
                $ids,
                "SQL Injection attack payload '{$payload}' must not leak opted-out private members."
            );
        }
    }

    public function test_get_all_users_data_returns_directory_members_outside_family_respecting_privacy(): void
    {
        $results = $this->model->getAllUsersData($this->callerId, $this->callerFamCode, 200, 0);

        $ids = array_column($results, 'id');

        // Target member outside family must be present
        $this->assertContains(
            $this->targetMemberId,
            $ids,
            'Public directory member outside family must be returned by getAllUsersData.'
        );

        // Caller themselves must not be present
        $this->assertNotContains(
            $this->callerId,
            $ids,
            'Caller must not be included in directory results.'
        );

        // Private member outside family must NOT be present
        $this->assertNotContains(
            $this->privateMemberId,
            $ids,
            'Opted-out member (show_my_profile = off) must not be returned by getAllUsersData.'
        );
    }

    public function test_super_admin_can_search_and_see_opted_out_private_members(): void
    {
        // 1. Provision a Super Admin user
        $adminSuffix = bin2hex(random_bytes(4));
        $adminId = 'ADMIN_' . $adminSuffix;
        $adminFamCode = 'ADMIN_FAM_' . $adminSuffix;
        $adminEmail = 'superadmin_' . $adminSuffix . '@example.com';

        $stmtA = $this->pdo->prepare('INSERT INTO account (id, email, password, type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
        $stmtA->execute([$adminId, $adminEmail, 'hash', 'super_admin', 'active']);

        $stmtP = $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmtP->execute([$adminId, 'AdminFirst', 'AdminLast', $adminFamCode]);

        // 2. Normal user searching for 'Priv' cannot find private member
        $normalResults = $this->model->searchMembers($this->callerId, $this->callerFamCode, 'Priv');
        $normalIds = array_column($normalResults, 'id');
        $this->assertNotContains(
            $this->privateMemberId,
            $normalIds,
            'Standard member search must strictly exclude private member with show_my_profile = off.'
        );

        // 3. Super Admin searching for 'Priv' MUST find private member
        $adminResults = $this->model->searchMembers($adminId, $adminFamCode, 'Priv');
        $adminIds = array_column($adminResults, 'id');
        $this->assertContains(
            $this->privateMemberId,
            $adminIds,
            'Super Admin search must discover private member irrespective of show_my_profile = off.'
        );

        // Clean up
        $this->pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$adminId]);
        $this->pdo->prepare('DELETE FROM account WHERE id = ?')->execute([$adminId]);
    }

    public function test_designated_super_admin_email_bypasses_privacy_filter(): void
    {
        // Search using the designated CEO root email account 318422WALLY / waleolaogunrac@gmail.com
        $results = $this->model->searchMembers('318422WALLY', 'OLAO60446', 'Priv');
        $ids = array_column($results, 'id');
        $this->assertContains(
            $this->privateMemberId,
            $ids,
            'Designated root super admin waleolaogunrac@gmail.com must see private members irrespective of privacy preferences.'
        );

        // Also verify in getAllUsersData
        $dirResults = $this->model->getAllUsersData('318422WALLY', 'OLAO60446', 200, 0);
        $dirIds = array_column($dirResults, 'id');
        $this->assertContains(
            $this->privateMemberId,
            $dirIds,
            'Designated root super admin must receive private members in wider directory results.'
        );
    }

    public function test_super_admin_can_view_any_member_profile_in_base_controller(): void
    {
        // Standard user cannot view private target without shared family or approved connection
        $_SESSION['id'] = $this->callerId;
        $_SESSION['auth'] = ['email' => 'caller@test.com', 'type' => 'member'];
        $canViewNormal = \App\controller\BaseController::sessionCanViewMember($this->privateMemberId, $this->privateFamCode);
        $this->assertFalse($canViewNormal, 'Normal session must not be allowed to view unlinked member profile.');

        // Super Admin session CAN view private target
        $_SESSION['id'] = '318422WALLY';
        $_SESSION['auth'] = ['email' => 'waleolaogunrac@gmail.com', 'type' => 'super_admin'];
        $canViewAdmin = \App\controller\BaseController::sessionCanViewMember($this->privateMemberId, $this->privateFamCode);
        $this->assertTrue($canViewAdmin, 'Super Admin session must be allowed to view any member profile.');

        unset($_SESSION['id'], $_SESSION['auth']);
    }
}

