<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\FamilyRecommendationService;
use PHPUnit\Framework\TestCase;

final class FamilyRecommendationServiceTest extends TestCase
{
    public function test_generate_and_verify_valid_token(): void
    {
        $userId = '823456JOHN';
        $userName = 'John Doe';

        $token = FamilyRecommendationService::generateSignedReferralToken($userId, $userName);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);

        $resolved = FamilyRecommendationService::verifyAndResolveReferralToken($token);
        $this->assertNotNull($resolved);
        $this->assertSame($userId, $resolved['inviter_id']);
        $this->assertSame($userName, $resolved['inviter_name']);
        $this->assertTrue($resolved['is_valid']);
        $this->assertGreaterThan(0, $resolved['created_at']);
    }

    public function test_verify_rejects_empty_and_malformed_tokens(): void
    {
        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken(''));
        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken('not-a-valid-token'));
        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken('part1.part2.part3'));
        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken('.'));
    }

    public function test_verify_rejects_tampered_payload(): void
    {
        $token = FamilyRecommendationService::generateSignedReferralToken('100ALICE', 'Alice Walker');
        [$encodedPayload, $sig] = explode('.', $token);

        // Tamper with payload by replacing with evil user ID
        $evilPayload = base64_encode(json_encode([
            'uid' => 'EVIL_ATTACKER',
            'name' => 'Evil Attacker',
            'iat' => time(),
            'exp' => time() + 3600,
        ]));
        $tamperedToken = rtrim(strtr($evilPayload, '+/', '-_'), '=') . '.' . $sig;

        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken($tamperedToken));
    }

    public function test_verify_rejects_tampered_signature(): void
    {
        $token = FamilyRecommendationService::generateSignedReferralToken('100ALICE', 'Alice Walker');
        [$encodedPayload] = explode('.', $token);

        $fakeSig = hash('sha256', 'fake-signature-attempt');
        $tamperedToken = $encodedPayload . '.' . $fakeSig;

        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken($tamperedToken));
    }

    public function test_verify_rejects_expired_token(): void
    {
        // Manually build an expired payload
        $expiredPayload = [
            'uid' => '100BOB',
            'name' => 'Bob Builder',
            'iat' => time() - 3000000,
            'exp' => time() - 100, // Expired
            'rnd' => 'abc123',
        ];
        $json = (string) json_encode($expiredPayload);
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $encoded, FamilyRecommendationService::getSecretKey());

        $expiredToken = $encoded . '.' . $sig;
        $this->assertNull(FamilyRecommendationService::verifyAndResolveReferralToken($expiredToken));
    }

    public function test_share_payload_contains_usp_message_and_clean_urls(): void
    {
        $payload = FamilyRecommendationService::getSharePayload('200SARAH', 'Sarah Connor', 'https://familyplatform.test');

        $this->assertArrayHasKey('share_url', $payload);
        $this->assertArrayHasKey('whatsapp_url', $payload);
        $this->assertArrayHasKey('message', $payload);
        $this->assertArrayHasKey('token', $payload);

        $this->assertStringStartsWith('https://familyplatform.test/join?ref=', $payload['share_url']);
        $this->assertStringStartsWith('https://api.whatsapp.com/send?text=', $payload['whatsapp_url']);
        
        // Assert the Walled Family Sanctuary USP messaging is explicitly present
        $this->assertStringContainsString('only connected kins and approved family can see through the wall', $payload['message']);
        $this->assertStringContainsString('Sarah', $payload['message']);
    }
}
