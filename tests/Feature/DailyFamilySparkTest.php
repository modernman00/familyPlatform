<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\DailyFamilySparkController;
use App\services\DailyFamilySparkService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class DailyFamilySparkTest extends TestCase
{
    private string $testFamCode = 'TEST_SPARK_FAM_999';
    private string $otherFamCode = 'OTHER_SPARK_FAM_888';
    private string $testUserId = 'usr_spark_test_1';
    private ?PDO $db = null;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->db = Db::connect2();
        DailyFamilySparkService::ensureTablesExist($this->db);
        $this->cleanupTestRows();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestRows();
        parent::tearDown();
    }

    private function cleanupTestRows(): void
    {
        if ($this->db === null) {
            return;
        }
        $stmt1 = $this->db->prepare("DELETE FROM daily_family_spark_responses WHERE famCode IN (?, ?)");
        $stmt1->execute([$this->testFamCode, $this->otherFamCode]);

        $stmt2 = $this->db->prepare("DELETE FROM daily_family_sparks WHERE famCode IN (?, ?)");
        $stmt2->execute([$this->testFamCode, $this->otherFamCode]);
    }

    public function test_get_active_spark_seeds_curated_spark_for_family(): void
    {
        $spark = DailyFamilySparkService::getActiveSpark($this->testFamCode, $this->testUserId, $this->db);

        $this->assertNotEmpty($spark['id']);
        $this->assertSame($this->testFamCode, $spark['famCode']);
        $this->assertNotEmpty($spark['title']);
        $this->assertNotEmpty($spark['prompt_text']);
        $this->assertSame(0, $spark['total_replies']);
        $this->assertStringContainsString('https://api.whatsapp.com/send?text=', (string)$spark['whatsapp_url']);
    }

    public function test_member_spark_creation_and_single_vote_upsert_and_replies(): void
    {
        $created = DailyFamilySparkService::createMemberSpark(
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            title: 'Sunday Pudding Debate',
            promptText: 'Which dessert wins at our Sunday table?',
            options: ['Apple Crumble', 'Sticky Toffee', 'Fruit Salad'],
            dbOverride: $this->db
        );

        $this->assertSame('success', $created['status']);
        $sparkId = (string)$created['data']['id'];
        $this->assertCount(3, $created['data']['options']);

        // Cast first vote (option 0)
        $vote1 = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            payload: ['response_type' => 'vote', 'selected_option_index' => 0],
            dbOverride: $this->db
        );
        $this->assertSame('success', $vote1['status']);
        $this->assertSame(1, $vote1['data']['total_votes']);
        $this->assertSame(0, $vote1['data']['user_vote_index']);

        // Change vote to option 1 (must upsert, total_votes stays 1)
        $vote2 = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            payload: ['response_type' => 'vote', 'selected_option_index' => 1],
            dbOverride: $this->db
        );
        $this->assertSame('success', $vote2['status']);
        $this->assertSame(1, $vote2['data']['total_votes']);
        $this->assertSame(1, $vote2['data']['user_vote_index']);
        $this->assertSame(0, $vote2['data']['options'][0]['votes']);
        $this->assertSame(1, $vote2['data']['options'][1]['votes']);

        // Add conversational reply inside the same Living Spark Card
        $reply = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            payload: ['response_type' => 'text', 'body' => 'Sticky Toffee every single time!'],
            dbOverride: $this->db
        );
        $this->assertSame('success', $reply['status']);
        $this->assertSame(1, $reply['data']['total_replies']);
        $this->assertSame('Sticky Toffee every single time!', $reply['data']['replies'][0]['body']);
    }

    public function test_cross_family_idor_on_submit_response_is_blocked(): void
    {
        $created = DailyFamilySparkService::createMemberSpark(
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            title: 'Private Family Question',
            promptText: 'Where did Grandad grow up?',
            options: [],
            dbOverride: $this->db
        );
        $sparkId = (string)$created['data']['id'];

        $idorAttempt = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->otherFamCode,
            userId: 'usr_attacker_999',
            authorName: 'Attacker',
            payload: ['response_type' => 'text', 'body' => 'Trying to inject into another family'],
            dbOverride: $this->db
        );

        $this->assertSame('error', $idorAttempt['status']);
        $this->assertSame(404, $idorAttempt['http_code']);
    }

    public function test_privacy_safe_whatsapp_share_exposes_zero_pii_or_private_prompt(): void
    {
        $share = DailyFamilySparkService::buildPrivacySafeShareData([
            'title' => 'Secret Child Milestone',
            'prompt_text' => 'Little Maya lost her first tooth!',
            'media_url' => '/resources/images/post/private_child.jpg',
        ]);

        $this->assertStringNotContainsString('Maya', $share['teaser_text']);
        $this->assertStringNotContainsString('private_child.jpg', $share['teaser_text']);
        $this->assertStringNotContainsString('Secret Child Milestone', $share['teaser_text']);
    }

    public function test_controller_rejects_unauthenticated_and_cross_family_requests(): void
    {
        // 1. Unauthenticated instantiation blocked by RoleMiddleware (UnauthorisedException)
        $blocked = false;
        try {
            new DailyFamilySparkController();
        } catch (\Src\Exceptions\UnauthorisedException $e) {
            $blocked = true;
        }
        $this->assertTrue($blocked, 'Unauthenticated controller instantiation must be blocked.');

        // 2. Authenticated via JWT cookie, but requesting another family_code -> 403
        $jwt = \Src\JwtHandler::jwtEncodeData([
            'id' => $this->testUserId,
            'famCode' => $this->testFamCode,
            'role' => 'member',
        ], 900);
        $cookieName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $_COOKIE[$cookieName] = $jwt;
        $_SESSION['id'] = $this->testUserId;
        $_SESSION['famCode'] = $this->testFamCode;
        $_GET['family_code'] = $this->otherFamCode;

        $controller = new DailyFamilySparkController();
        ob_start();
        $controller->getActive();
        $out2 = (string)ob_get_clean();
        $this->assertSame(403, http_response_code());
        $this->assertStringContainsString('Access denied', $out2);
    }

    public function test_member_spark_rotates_after_48_hours_of_inactivity(): void
    {
        $created = DailyFamilySparkService::createMemberSpark(
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            title: 'Active Member Spark',
            promptText: 'Who remembers our 2012 road trip?',
            options: [],
            dbOverride: $this->db
        );
        $sparkId = (string)$created['data']['id'];

        // Backdate spark_date to yesterday, but keep last_activity_at recent (10h ago) -> stays active
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $recentActivity = date('Y-m-d H:i:s', time() - 10 * 3600);
        $upd1 = $this->db?->prepare("UPDATE daily_family_sparks SET spark_date = ?, last_activity_at = ? WHERE id = ?");
        $upd1?->execute([$yesterday, $recentActivity, $sparkId]);

        $stillActive = DailyFamilySparkService::getActiveSpark($this->testFamCode, $this->testUserId, $this->db);
        $this->assertSame($sparkId, $stillActive['id']);

        // Backdate last_activity_at to 49h ago -> automatically rotates to today's fresh system Spark
        $staleActivity = date('Y-m-d H:i:s', time() - 49 * 3600);
        $upd2 = $this->db?->prepare("UPDATE daily_family_sparks SET last_activity_at = ? WHERE id = ?");
        $upd2?->execute([$staleActivity, $sparkId]);

        $rotated = DailyFamilySparkService::getActiveSpark($this->testFamCode, $this->testUserId, $this->db);
        $this->assertNotSame($sparkId, $rotated['id']);
        $this->assertSame(date('Y-m-d'), $rotated['spark_date']);
    }

    public function test_non_numeric_vote_index_is_rejected_with_422(): void
    {
        $created = DailyFamilySparkService::createMemberSpark(
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            title: 'Vote Validation Test',
            promptText: 'Pick an option',
            options: ['Option A', 'Option B'],
            dbOverride: $this->db
        );
        $sparkId = (string)$created['data']['id'];

        $res = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->testFamCode,
            userId: $this->testUserId,
            authorName: 'Wale Test',
            payload: ['response_type' => 'vote', 'selected_option_index' => 'abc'],
            dbOverride: $this->db
        );

        $this->assertSame('error', $res['status']);
        $this->assertSame(422, $res['http_code']);
    }

    public function test_hashed_and_unhashed_family_codes_share_same_spark(): void
    {
        $spark1 = DailyFamilySparkService::getActiveSpark('#' . strtolower($this->testFamCode), $this->testUserId, $this->db);
        $spark2 = DailyFamilySparkService::getActiveSpark($this->testFamCode, $this->testUserId, $this->db);

        $this->assertSame($spark1['id'], $spark2['id']);
        $this->assertSame($this->testFamCode, $spark1['famCode']);
    }

    public function test_controller_returns_http_exception_code_on_missing_csrf(): void
    {
        $_SESSION['id'] = $this->testUserId;
        $_SESSION['famCode'] = $this->testFamCode;
        $_SESSION['token'] = 'expected_csrf_token_123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['title' => 'Test', 'prompt_text' => 'Prompt'];

        $controller = new DailyFamilySparkController();
        ob_start();
        $controller->create();
        $out = (string)ob_get_clean();

        $this->assertNotSame(500, http_response_code());
        $this->assertContains(http_response_code(), [401, 403]);
        $decoded = json_decode($out, true);
        $this->assertSame('error', $decoded['status'] ?? null);
    }
}
