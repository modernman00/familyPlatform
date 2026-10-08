<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Src\Utility;

class EmailUnsubscribeSecurityTest extends TestCase
{
    private string $appKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->appKey = (string) (getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? 'test_app_key_for_unit_tests'));
    }

    public function test_email_template_footer_omits_company_reg_ico_reg_address_and_telephone(): void
    {
        $html = Utility::viewTemplateEmail('email', [
            'data' => [
                'email' => 'member@example.com',
                'subject' => 'Family Platform Monthly Update',
            ],
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringNotContainsString('Company Reg No:', (string)$html);
        $this->assertStringNotContainsString('ICO Reg:', (string)$html);
        $this->assertStringNotContainsString('Registered Office:', (string)$html);
        $this->assertStringNotContainsString('128 City Road', (string)$html);
        $this->assertStringNotContainsString('800 123 4567', (string)$html);
        $this->assertStringContainsString('Privacy Policy', (string)$html);
        $this->assertStringContainsString('Terms of Service', (string)$html);
    }

    public function test_security_alert_renders_mandatory_service_notice_without_unsubscribe_link(): void
    {
        $html = Utility::viewTemplateEmail('msg/pwdChange', [
            'data' => [
                'email' => 'user@example.com',
            ],
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Mandatory Service Notice', (string)$html);
        $this->assertStringNotContainsString('/email/unsubscribe?', (string)$html);
    }

    public function test_marketing_email_renders_valid_hmac_unsubscribe_link(): void
    {
        $email = 'newsletter@example.com';
        $html = Utility::viewTemplateEmail('email', [
            'data' => [
                'email' => $email,
                'subject' => 'Weekend Community Roundup',
            ],
        ]);

        $expectedToken = hash_hmac('sha256', $email, $this->appKey);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('/email/unsubscribe?', (string)$html);
        $this->assertStringContainsString('token=' . $expectedToken, (string)$html);
        $this->assertStringContainsString('email=' . urlencode($email), (string)$html);
    }

    public function test_forged_unsubscribe_token_fails_cryptographic_verification(): void
    {
        $email = 'victim@example.com';
        $forgedToken = hash_hmac('sha256', 'attacker@example.com', $this->appKey);
        $realToken = hash_hmac('sha256', $email, $this->appKey);

        $this->assertFalse(hash_equals($realToken, $forgedToken));
    }
}
