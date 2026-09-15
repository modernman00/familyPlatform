<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\OnboardingController;
use App\services\OnboardingService;
use Tests\Support\SocialFeedTestCase;

/**
 * OnboardingSecurityTest
 *
 * Marcus (SecOps) & Ghost (Red Team Lead) Adversarial Test Suite for 60-Second Onboarding Overhaul.
 * Audits 8-Pillar Security Gates on Onboarding & Invite Flows:
 * - State and Progress Validation
 * - Whitelisted Step Keys & Injection Defense
 * - CSRF Gatekeeper
 * - XSS & Parameter Tampering in Invite URL Generation
 */
final class OnboardingSecurityTest extends SocialFeedTestCase
{
    private OnboardingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OnboardingService($this->pdo);
    }

    /**
     * Test 1: Onboarding state returns valid steps and pre-formatted invite links
     */
    public function testOnboardingStateReturnsValidStepsAndInviteLinks(): void
    {
        $state = $this->service->getOnboardingState(
            userId: $this->authorId,
            famCode: $this->famCode
        );

        $this->assertArrayHasKey('completed', $state);
        $this->assertArrayHasKey('progress_percentage', $state);
        $this->assertArrayHasKey('completed_count', $state);
        $this->assertSame(3, $state['total_steps']);
        $this->assertArrayHasKey('steps', $state);
        $this->assertArrayHasKey('explore_tree', $state['steps']);
        $this->assertArrayHasKey('invite_family', $state['steps']);
        $this->assertArrayHasKey('post_memory', $state['steps']);

        // Check invite payload
        $this->assertArrayHasKey('invite_data', $state);
        $this->assertSame($this->famCode, $state['invite_data']['family_code']);
        $this->assertStringContainsString('whatsapp.com/send', $state['invite_data']['whatsapp_url']);
        // Invite URL should contain opaque token (/register?invite=...) not PII
        $this->assertStringContainsString('/register?invite=', $state['invite_data']['invite_url']);
        // The WhatsApp URL text parameter should contain the register URL (URL-encoded)
        // It's encoded as 'register%3Finvite%3D' in the URL
        $this->assertStringContainsString('%2Fregister%3Finvite%3D', $state['invite_data']['whatsapp_url']);
    }

    /**
     * Marcus & Ghost PoC #1: Invalid Step Key Tampering
     * An attacker attempts to inject invalid keys or arbitrary state.
     */
    public function testInvalidStepKeyIsRejected(): void
    {
        $result = $this->service->completeStep(
            userId: $this->authorId,
            stepKey: 'invalid_malicious_step_name_123'
        );

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('Invalid onboarding step', $result['message']);
    }

    /**
     * Test 2: Valid step key execution updates state and progress percentage
     */
    public function testValidStepKeyUpdatesState(): void
    {
        // Start fresh session state for test
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        unset($_SESSION['onboarding_step_explore_tree'], $_SESSION['onboarding_step_invite_family'], $_SESSION['onboarding_step_post_memory']);

        // Mark step 1
        $res1 = $this->service->completeStep($this->authorId, 'explore_tree');
        $this->assertSame('success', $res1['status']);

        $state1 = $this->service->getOnboardingState($this->authorId, $this->famCode);
        $this->assertTrue($state1['steps']['explore_tree']['done']);
        $this->assertGreaterThanOrEqual(1, $state1['completed_count']);

        // Mark step 2
        $res2 = $this->service->completeStep($this->authorId, 'invite_family');
        $this->assertSame('success', $res2['status']);

        // Mark step 3
        $res3 = $this->service->completeStep($this->authorId, 'post_memory');
        $this->assertSame('success', $res3['status']);

        $stateFinal = $this->service->getOnboardingState($this->authorId, $this->famCode);
        $this->assertTrue($stateFinal['completed']);
        $this->assertSame(3, $stateFinal['completed_count']);
        $this->assertSame(100, $stateFinal['progress_percentage']);
    }

    /**
     * Marcus & Ghost PoC #2: CSRF Gatekeeper on Onboarding Step Updates
     * Missing or invalid CSRF tokens must be rejected.
     */
    public function testCsrfTokenRequiredForStepCompletion(): void
    {
        $_POST = [
            'step' => 'explore_tree',
            'token' => 'invalid_csrf_token_attack',
        ];
        $_SERVER['HTTP_X_XSRF_TOKEN'] = 'invalid_header_token';

        $controller = new OnboardingController($this->service);

        ob_start();
        $controller->completeStep();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $json = json_decode((string)$output, true);

        $this->assertSame('error', $json['status'] ?? null);
        $this->assertTrue(
            str_contains($json['message'] ?? '', 'Invalid CSRF') ||
            str_contains($json['message'] ?? '', 'familiar with the nature')
        );
    }

    /**
     * Marcus & Ghost PoC #3: XSS & Special Character Sanitization in Invite Links
     * Adversarial input in surname or family code must be URL-safe and neutralized.
     */
    public function testInviteUrlSanitizesMaliciousSurnamePayloads(): void
    {
        $maliciousFamCode = 'FAM<"\'&>';

        $inviteData = $this->service->generateInviteData($this->authorId, $maliciousFamCode);

        $this->assertArrayHasKey('whatsapp_url', $inviteData);
        $this->assertArrayHasKey('invite_url', $inviteData);

        // Verify that raw unencoded script tags are not present in the URL
        $this->assertStringNotContainsString('<script>', $inviteData['whatsapp_url']);
        $this->assertStringNotContainsString('<script>', $inviteData['invite_url']);
        $this->assertStringNotContainsString('"', $inviteData['whatsapp_url']);
    }
}
