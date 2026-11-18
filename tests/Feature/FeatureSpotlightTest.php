<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\FeatureSpotlightController;
use App\services\FeatureSpotlightService;
use PHPUnit\Framework\TestCase;

final class FeatureSpotlightTest extends TestCase
{
    public function testCatalogHasRichCuratedSpotlightsWithSafeUrls(): void
    {
        $catalog = FeatureSpotlightService::getCatalog('usr_test_123');
        $this->assertGreaterThanOrEqual(15, count($catalog));

        $ids = [];
        foreach ($catalog as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('badge', $item);
            $this->assertArrayHasKey('subtitle', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('story', $item);
            $this->assertArrayHasKey('highlights', $item);
            $this->assertArrayHasKey('cta_label', $item);
            $this->assertArrayHasKey('cta_url', $item);
            $this->assertArrayHasKey('icon', $item);
            $this->assertArrayHasKey('theme', $item);

            $this->assertIsArray($item['highlights']);
            $this->assertGreaterThanOrEqual(2, count($item['highlights']));
            $this->assertStringStartsWith('/', (string)$item['cta_url']);
            $this->assertStringNotContainsString('javascript:', strtolower((string)$item['cta_url']));

            $ids[] = $item['id'];
        }

        $this->assertContains('family-roundtable', $ids);
        $this->assertContains('creative-studio', $ids);
        $this->assertContains('kinship-calculator', $ids);
        $this->assertContains('gift-wishlists', $ids);
    }

    public function testFeedSpotlightsExcludesDismissedAndRotates(): void
    {
        $all = FeatureSpotlightService::getFeedSpotlights('usr_alpha', [], '2026-11-18');
        $this->assertNotEmpty($all);

        $firstId = (string)$all[0]['id'];
        $filtered = FeatureSpotlightService::getFeedSpotlights('usr_alpha', [$firstId], '2026-11-18');

        $filteredIds = array_column($filtered, 'id');
        $this->assertNotContains($firstId, $filteredIds);
        $this->assertCount(count($all) - 1, $filtered);

        $noneLeft = FeatureSpotlightService::getFeedSpotlights('usr_alpha', array_column($all, 'id'), '2026-11-18');
        $this->assertSame([], $noneLeft);
    }

    public function testAllowlistValidation(): void
    {
        $this->assertTrue(FeatureSpotlightService::exists('family-roundtable'));
        $this->assertFalse(FeatureSpotlightService::exists('../etc/passwd'));
        $this->assertFalse(FeatureSpotlightService::exists('nonexistent-feature'));

        $this->assertTrue(FeatureSpotlightService::isValidAction('dismiss'));
        $this->assertTrue(FeatureSpotlightService::isValidAction('launch'));
        $this->assertTrue(FeatureSpotlightService::isValidAction('shuffle'));
        $this->assertFalse(FeatureSpotlightService::isValidAction('drop_table'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUnauthenticatedGetFeedReturns401(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
        $this->expectException(\Src\Exceptions\UnauthorisedException::class);
        new FeatureSpotlightController();
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidSpotlightPayloadReturns422(): void
    {
        $_SESSION = ['id' => 'usr_test_sec_1'];
        $_POST = [
            'spotlight_id' => '../malicious',
            'action' => 'dismiss',
        ];
        $controller = new FeatureSpotlightController();

        ob_start();
        $controller->interact();
        $output = (string)ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertSame(422, http_response_code());
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAuthenticatedDismissUpdatesSession(): void
    {
        $_SESSION = ['id' => 'usr_test_sec_2'];
        $_POST = [
            'spotlight_id' => 'family-roundtable',
            'action' => 'dismiss',
        ];
        $controller = new FeatureSpotlightController();

        ob_start();
        $controller->interact();
        $output = (string)ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertSame(200, http_response_code());
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['status'] ?? null);
        $this->assertContains('family-roundtable', $decoded['dismissed_ids'] ?? []);
    }
}
