<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\SettingController;
use PHPUnit\Framework\TestCase;

final class AccountSettingsPreactTest extends TestCase
{
    public function testBuildSafeBootstrapAllowlistsFieldsAndStripsSensitiveTokens(): void
    {
        $accountData = [
            'id' => 123,
            'firstName' => 'Wale',
            'lastName' => 'Olaogun',
            'fullName' => 'Wale Olaogun',
            'email' => 'wale@example.com',
            'mobile' => '+447700900111',
            'country' => 'United Kingdom',
            'famCode' => 'OLAO604',
            'otherFamCode' => 'ADEY202',
            'password' => '$2y$10$SHOULD_NEVER_BE_EXPOSED',
            'token' => 'RAW_SESSION_SECRET',
        ];

        $pendingRequest = [
            'id' => '123',
            'family_code' => 'NEW999',
            'inviter_first_name' => 'Tunde',
            'inviter_last_name' => 'Adeyemi',
            'purpose' => 'join',
            'approval_token' => 'SECRET_APPROVAL_TOKEN_DO_NOT_LEAK',
            'created_at' => '2026-11-20 12:00:00',
        ];

        $birthFamily = [
            'state' => 'pending',
            'code' => 'ADEY202',
            'origin' => 'linked',
            'pending' => [
                'family_code' => 'ADEY202',
                'inviter_first_name' => 'Bisi',
                'inviter_last_name' => 'Adeyemi',
                'purpose' => 'birth_link',
                'approval_token' => 'BIRTH_SECRET_TOKEN_DO_NOT_LEAK',
            ],
        ];

        $codeRename = [
            'is_creator' => true,
            'can_propose' => true,
            'code' => 'OLAO604',
            'cooldown' => false,
            'open' => null,
        ];

        $passkeys = [
            [
                'id' => 9,
                'credential_id' => 'cred_abc',
                'device_name' => 'MacBook Touch ID',
                'created_at' => '2026-11-01 09:30:00',
                'last_used_at' => '2026-11-22 10:00:00',
                'public_key' => 'SENSITIVE_PUBLIC_KEY_MATERIAL',
            ],
        ];

        $restrictions = [
            [
                'id' => 55,
                'fullName' => 'Test Relative',
                'famCode' => 'OLAO604',
                'avatar' => '/resources/images/profile/avatarM.png',
                'is_restricted' => false,
                'restrict_posts' => false,
                'note' => 'Private note',
            ],
        ];

        $bootstrap = SettingController::buildSafeBootstrap(
            $accountData,
            $pendingRequest,
            $birthFamily,
            $codeRename,
            $passkeys,
            $restrictions,
            'Wale Olaogun',
            'csrf_abc',
            'email_required_for_party_studio'
        );

        $this->assertSame('123', $bootstrap['accountData']['id']);
        $this->assertArrayNotHasKey('password', $bootstrap['accountData']);
        $this->assertArrayNotHasKey('token', $bootstrap['accountData']);

        $this->assertIsArray($bootstrap['pendingFamilyRequest']);
        $this->assertSame('NEW999', $bootstrap['pendingFamilyRequest']['family_code']);
        $this->assertArrayNotHasKey('approval_token', $bootstrap['pendingFamilyRequest']);

        $this->assertIsArray($bootstrap['birthFamily']['pending']);
        $this->assertArrayNotHasKey('approval_token', $bootstrap['birthFamily']['pending']);

        $this->assertSame('9', $bootstrap['passkeys'][0]['id']);
        $this->assertArrayNotHasKey('public_key', $bootstrap['passkeys'][0]);
        $this->assertSame('55', $bootstrap['restrictionDirectory'][0]['id']);
        $this->assertSame('email_required_for_party_studio', $bootstrap['notice']);

        $maliciousNotice = SettingController::buildSafeBootstrap(
            $accountData,
            null,
            $birthFamily,
            $codeRename,
            [],
            [],
            '',
            '',
            '<script>alert(1)</script>'
        );
        $this->assertSame('', $maliciousNotice['notice']);
    }

    public function testAccountSettingsStateRouteIsRegisteredAndProtected(): void
    {
        $rootDir = dirname(__DIR__, 2);
        $settingRoutes = (string) file_get_contents($rootDir . '/app/router/setting.php');
        $dispatch = (string) file_get_contents($rootDir . '/app/router/RouteDispatch.php');

        $this->assertStringContainsString("'/api/account-settings/state'", $settingRoutes);
        $this->assertStringContainsString('SettingController@state', $settingRoutes);
        $this->assertStringNotContainsString('SettingController', $dispatch);
    }

    public function testBladeTemplateMountsPreactShellSafely(): void
    {
        $rootDir = dirname(__DIR__, 2);
        $blade = (string) file_get_contents($rootDir . '/resources/views/member/accountSetting.blade.php');

        $this->assertStringContainsString('id="account-settings-root"', $blade);
        $this->assertStringContainsString('id="account-settings-bootstrap"', $blade);
        $this->assertStringContainsString('id="restrictionDirectoryData"', $blade);
        $this->assertStringContainsString('account-settings-app.js', $blade);
        $this->assertStringContainsString('JSON_HEX_TAG', $blade);
        $this->assertStringContainsString('body.dark-mode .content-card', $blade);
    }
}
