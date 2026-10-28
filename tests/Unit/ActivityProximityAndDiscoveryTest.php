<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\services\ActivityProximityService;
use App\controller\members\ActivityDiscoveryController;
use Src\Db;

final class ActivityProximityAndDiscoveryTest extends TestCase
{
    private string $testUserId1 = 'test-prox-user-001';
    private string $testUserId2 = 'test-prox-user-002';
    private string $testUserId3 = 'test-prox-user-003';

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('TESTING_ENV')) {
            define('TESTING_ENV', true);
        }
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        unset($_SESSION['id'], $_SESSION['famCode']);
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $db = Db::connect2();
        $stmt = $db->prepare("DELETE FROM member_locations WHERE user_id IN (?, ?, ?)");
        $stmt->execute([$this->testUserId1, $this->testUserId2, $this->testUserId3]);
    }

    public function testHaversineDistanceAccuracy(): void
    {
        // London to Richmond is ~9.4 miles
        $distRichmond = ActivityProximityService::haversineDistanceMiles(51.5074, -0.1278, 51.4613, -0.3037);
        $this->assertGreaterThan(7.0, $distRichmond);
        $this->assertLessThan(12.0, $distRichmond);

        // London to Birmingham is ~101 miles
        $distBrum = ActivityProximityService::haversineDistanceMiles(51.5074, -0.1278, 52.4862, -1.8904);
        $this->assertGreaterThan(95.0, $distBrum);
        $this->assertLessThan(115.0, $distBrum);

        // Same point distance is 0.0
        $zero = ActivityProximityService::haversineDistanceMiles(51.5074, -0.1278, 51.5074, -0.1278);
        $this->assertEqualsWithDelta(0.0, $zero, 0.001);
    }

    public function testResolveCoordinatesCentroidsAndOutcodes(): void
    {
        $london = ActivityProximityService::resolveCoordinates('London');
        $this->assertNotNull($london);
        $this->assertEqualsWithDelta(51.5074, $london['lat'], 0.01);
        $this->assertEqualsWithDelta(-0.1278, $london['lng'], 0.01);

        $sw19 = ActivityProximityService::resolveCoordinates('SW19 4TL');
        $this->assertNotNull($sw19);
        $this->assertEquals('SW19', $sw19['name']);
        $this->assertEqualsWithDelta(51.4214, $sw19['lat'], 0.01);

        $manchester = ActivityProximityService::resolveCoordinates('Manchester');
        $this->assertNotNull($manchester);
        $this->assertEqualsWithDelta(53.4808, $manchester['lat'], 0.01);
    }

    public function testFilterMembersByProximityAndPrivacyShield(): void
    {
        $db = Db::connect2();

        // Seed User 1 in Richmond (within 25 miles of London)
        ActivityProximityService::setMemberLocation($this->testUserId1, 'Richmond', 51.4613, -0.3037);

        // Seed User 2 in Birmingham (101 miles away from London)
        ActivityProximityService::setMemberLocation($this->testUserId2, 'Birmingham', 52.4862, -1.8904);

        // Seed User 3 in London but with proximity_sharing = 'off' (Dr. Viktor Kovács privacy rule)
        ActivityProximityService::setMemberLocation($this->testUserId3, 'London', 51.5074, -0.1278);
        $upStmt = $db->prepare("UPDATE member_locations SET proximity_sharing = 'off' WHERE user_id = ?");
        $upStmt->execute([$this->testUserId3]);

        $familyMembers = [
            ['id' => $this->testUserId1, 'firstName' => 'Alice', 'lastName' => 'Nearby', 'email' => 'alice@test.local'],
            ['id' => $this->testUserId2, 'firstName' => 'Bob', 'lastName' => 'Distant', 'email' => 'bob@test.local'],
            ['id' => $this->testUserId3, 'firstName' => 'Charlie', 'lastName' => 'Private', 'email' => 'charlie@test.local'],
        ];

        $filtered = ActivityProximityService::filterMembersByProximity($familyMembers, 'London', 25);

        $nearbyIds = array_column($filtered['nearby_members'], 'id');
        $distantIds = array_column($filtered['distant_members'], 'id');

        // Alice (Richmond) should be in nearby_members
        $this->assertContains($this->testUserId1, $nearbyIds);

        // Bob (Birmingham) should be in distant_members
        $this->assertContains($this->testUserId2, $distantIds);

        // Charlie (proximity_sharing = off) MUST be in distant_members to guarantee privacy
        $this->assertContains($this->testUserId3, $distantIds);
        $this->assertNotContains($this->testUserId3, $nearbyIds);
    }

    public function testActivityDiscoveryUnauthorisedWithoutSession(): void
    {
        unset($_SESSION['id']);
        ob_start();
        ActivityDiscoveryController::discover();
        $output = ob_get_clean();
        $json = json_decode((string)$output, true);

        $this->assertIsArray($json);
        $this->assertEquals('error', $json['status']);
        $this->assertEquals(401, $json['code'] ?? $json['statusCode'] ?? 401);
    }

    public function testActivityDiscoveryGroundedSuccessWithSession(): void
    {
        $_SESSION['id'] = $this->testUserId1;
        $_GET['category'] = 'barbecue';
        $_GET['location'] = 'Richmond';
        unset($GLOBALS['__testResponse']);

        ob_start();
        ActivityDiscoveryController::discover();
        $output = ob_get_clean();

        $json = $GLOBALS['__testResponse'] ?? json_decode((string)$output, true);

        $this->assertIsArray($json);
        $this->assertEquals(200, $json['code'] ?? $json['statusCode'] ?? 200);

        $data = $json['message'] ?? $json['data'] ?? [];
        $this->assertEquals('barbecue', $data['category']);
        $this->assertNotEmpty($data['cards']);
        $this->assertArrayHasKey('viral_share_prompt', $data);
        $this->assertStringContainsString('Richmond', $data['viral_share_prompt']);
    }

    public function testFamilyAiHubDiscoverWeekendOutingsApi(): void
    {
        $_SESSION['id'] = $this->testUserId1;
        $_SESSION['famCode'] = 'FAM_TEST_AIHUB';

        $ctrl = new \App\controller\members\FamilyAiHubController();
        ob_start();
        $ctrl->discoverWeekendOutings();
        $output = ob_get_clean();

        $json = json_decode((string)$output, true);
        $this->assertIsArray($json);
        $this->assertTrue($json['success']);
        $this->assertNotEmpty($json['outings']);
        $first = $json['outings'][0];
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('snippet', $first);
        $this->assertArrayHasKey('url', $first);
    }
}
