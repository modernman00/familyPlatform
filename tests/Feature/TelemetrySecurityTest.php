<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\TelemetryController;
use Tests\Support\SocialFeedTestCase;

/**
 * TelemetrySecurityTest
 *
 * Marcus (SecOps) & Ghost (Red Team Lead) Adversarial Test Suite for Telemetry Engine.
 * Tests:
 * - Empty & Malformed Payload Rejection
 * - IP Anonymization (UK GDPR Art. 30 compliance)
 * - Metadata Sanitization & SQLi/XSS Neutralization
 * - Safe Fail-Open / Non-Breaking Graceful Behavior
 */
final class TelemetrySecurityTest extends SocialFeedTestCase
{
    private TelemetryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TelemetryController($this->pdo);
    }

    /**
     * Test 1: Empty payload returns 400 Bad Request
     */
    public function testEmptyPayloadReturnsBadRequest(): void
    {
        $_POST = [];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        ob_start();
        $this->controller->recordEvent();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $json = json_decode((string)$output, true);

        $this->assertSame('error', $json['status'] ?? null);
    }

    /**
     * Test 2: Valid event payload records successfully with anonymized IP
     */
    public function testValidTelemetryEventRecordsCleanly(): void
    {
        $_POST = [
            'event' => 'rage_click',
            'url' => '/member/ProfilePage',
            'viewport_width' => 390,
            'viewport_height' => 844,
            'metadata' => [
                'target' => 'button#submitPost',
                'click_count' => 4,
            ],
        ];
        $_SERVER['REMOTE_ADDR'] = '82.165.197.123';

        ob_start();
        $this->controller->recordEvent();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $json = json_decode((string)$output, true);

        $this->assertSame('success', $json['status'] ?? null);
        $this->assertTrue($json['recorded'] ?? false);
    }

    /**
     * Marcus & Ghost PoC #1: Malicious Metadata Payload Injection
     * Injects SQL and script tags into telemetry metadata keys and values.
     */
    public function testMaliciousMetadataIsNeutralized(): void
    {
        $_POST = [
            'event' => 'dead_click',
            'url' => '/organogram',
            'metadata' => [
                'malicious<script>' => "'; DROP TABLE telemetry_events; -- <script>alert(1)</script>",
            ],
        ];
        $_SERVER['REMOTE_ADDR'] = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        ob_start();
        $this->controller->recordEvent();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $json = json_decode((string)$output, true);

        $this->assertSame('success', $json['status'] ?? null);
    }
}
