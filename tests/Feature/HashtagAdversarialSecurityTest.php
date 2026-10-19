<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\services\HashtagService;
use PHPUnit\Framework\TestCase;

/**
 * Adversarial Red Team Attack Suite for Family Hashtags & Memory Pills
 *
 * Certified by Dr. Viktor Kovács & Marcus ("Ghost" Reinholt).
 */
final class HashtagAdversarialSecurityTest extends TestCase
{
    private string $familyA;
    private string $familyB;
    private string $userA;
    private string $userB;
    private string $targetA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyA = 'FAM_ATTACK_A_' . bin2hex(random_bytes(3));
        $this->familyB = 'FAM_ATTACK_B_' . bin2hex(random_bytes(3));
        $this->userA = 'user_victim_' . bin2hex(random_bytes(3));
        $this->userB = 'user_attacker_' . bin2hex(random_bytes(3));
        $this->targetA = 'target_a_' . bin2hex(random_bytes(3));
    }

    protected function tearDown(): void
    {
        HashtagService::deleteLinksForTarget('post', $this->targetA);
        parent::tearDown();
    }

    /**
     * Dr. Viktor Kovács Attack: Cross-Family BOLA / IDOR Tag Leakage
     * Verify that Family B cannot view or discover Family A's private hashtag stream.
     */
    public function test_cross_family_idor_tag_leakage_is_prevented(): void
    {
        // 1. Family A creates a private memory with a secret tag
        $secretTag = 'secret_heirloom_vault_' . bin2hex(random_bytes(2));
        HashtagService::syncHashtags(
            $this->familyA,
            'post',
            $this->targetA,
            $this->userA,
            "Confidential family memory #" . $secretTag
        );

        // 2. Attacker in Family B attempts to discover the tag via suggestions
        $attackerSuggestions = HashtagService::suggestHashtags($this->familyB, 'secret_heirloom');
        $this->assertEmpty($attackerSuggestions, 'Family B must not see suggestions from Family A');

        // 3. Attacker in Family B attempts to fetch the stream for that tag
        $stream = HashtagService::getTagStream($this->familyB, $secretTag);
        $this->assertSame(0, $stream['count']);
        $this->assertEmpty($stream['items'], 'Family B must receive 0 items from Family A stream');

        // 4. Verify Family A can view their own tag
        $legitSuggestions = HashtagService::suggestHashtags($this->familyA, 'secret_heirloom');
        $this->assertNotEmpty($legitSuggestions);
        $this->assertSame($secretTag, $legitSuggestions[0]['tag']);
    }

    /**
     * Marcus Attack: Stored XSS & Tag Boundary Injection
     * Verify that HTML tags, script injection, and SQL breakouts are neutralized.
     */
    public function test_xss_and_injection_payloads_neutralized(): void
    {
        $maliciousPayload = "Family celebration #<svg/onload=alert('XSS')> and #normal_tag and #drop'--";
        $extracted = HashtagService::extractHashtags($maliciousPayload);

        $this->assertNotContains("<svg/onload=alert('XSS')>", $extracted);
        $this->assertNotContains("drop'--", $extracted);
        $this->assertContains('normal_tag', $extracted);
    }
}
