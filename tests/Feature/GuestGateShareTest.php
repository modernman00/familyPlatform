<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\PostShareController;
use App\services\ShareEngagementService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class GuestGateShareTest extends TestCase
{
    private ?PDO $db = null;
    private string $testItemKey = 'test_gg_999';

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        ShareEngagementService::ensureTableExists($this->db);
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        if ($this->db === null) {
            return;
        }
        $stmt = $this->db->prepare('DELETE FROM public_share_engagements WHERE item_key = ?');
        $stmt->execute([$this->testItemKey]);
    }

    public function test_share_engagement_service_records_valid_events_and_rejects_invalid_ones(): void
    {
        $this->assertNotNull($this->db);

        $okView = ShareEngagementService::recordEvent($this->db, [
            'share_type' => 'post',
            'item_key' => $this->testItemKey,
            'event_type' => 'view',
            'provider' => '',
            'referrer_source' => 'whatsapp',
        ]);
        $this->assertTrue($okView);

        $okSkip = ShareEngagementService::recordEvent($this->db, [
            'share_type' => 'spark',
            'item_key' => $this->testItemKey,
            'event_type' => 'skip',
            'provider' => '',
            'referrer_source' => 'direct',
        ]);
        $this->assertTrue($okSkip);

        $okAuth = ShareEngagementService::recordEvent($this->db, [
            'share_type' => 'post',
            'item_key' => $this->testItemKey,
            'event_type' => 'auth_click',
            'provider' => 'google',
            'referrer_source' => 'whatsapp',
        ]);
        $this->assertTrue($okAuth);

        $invalid = ShareEngagementService::recordEvent($this->db, [
            'share_type' => 'post',
            'item_key' => $this->testItemKey,
            'event_type' => 'xss_probe',
        ]);
        $this->assertFalse($invalid);

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM public_share_engagements WHERE item_key = ?');
        $stmt->execute([$this->testItemKey]);
        $this->assertSame(3, (int) $stmt->fetchColumn());
    }

    public function test_share_intent_cookie_rejects_tampered_signature(): void
    {
        $payload = json_encode(['post_no' => 42, 'famCode' => 'FAM99', 't' => time()]);
        $this->assertIsString($payload);

        $secret = (string) ($_ENV['APP_KEY'] ?? 'familyPlatformIntentKey');
        $sig = hash_hmac('sha256', $payload, $secret);
        $validCookie = $sig . '.' . base64_encode($payload);

        $parsed = PostShareController::parseShareIntentCookie($validCookie);
        $this->assertIsArray($parsed);
        $this->assertSame(42, $parsed['post_no']);
        $this->assertSame('FAM99', $parsed['famCode']);

        $tampered = 'deadbeef.' . base64_encode($payload);
        $this->assertNull(PostShareController::parseShareIntentCookie($tampered));
    }

    public function test_guest_gate_partial_contains_skip_oauth_and_accessibility_invariants(): void
    {
        $root = dirname(__DIR__, 2);
        $partial = (string) file_get_contents($root . '/resources/views/partials/guest-gate.blade.php');
        $this->assertStringContainsString('data-gg-skip', $partial);
        $this->assertStringContainsString('/auth/google', $partial);
        $this->assertStringContainsString('/auth/facebook', $partial);
        $this->assertStringNotContainsString('/auth/apple', $partial);
        $this->assertStringContainsString('x-trap.noscroll="open"', $partial);
        $this->assertStringContainsString('min-height: 44px', $partial);
        $this->assertStringContainsString('/api/public/share/track', $partial);

        $postView = (string) file_get_contents($root . '/resources/views/public/postShareLanding.blade.php');
        $this->assertStringContainsString("partials.guest-gate", $postView);

        $sparkView = (string) file_get_contents($root . '/resources/views/public/sparkShareLanding.blade.php');
        $this->assertStringContainsString("partials.guest-gate", $sparkView);
    }
}
