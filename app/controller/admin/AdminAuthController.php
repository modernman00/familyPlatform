<?php

declare(strict_types=1);

namespace App\controller\admin;

use Src\Auth\BaseAdminAuthController;
use App\middleware\AdminGuardMiddleware;

class AdminAuthController extends BaseAdminAuthController
{
    protected function getAppName(): string
    {
        return 'FamilyPlatform Admin';
    }

    protected function getDashboardUrl(): string
    {
        return '/admin/dashboard';
    }

    protected function getClientIp(): string
    {
        return AdminGuardMiddleware::getClientIp();
    }

    protected function generateFingerprint(string $ip, string $userAgent): string
    {
        return AdminGuardMiddleware::generateFingerprint($ip, $userAgent);
    }

    protected function getAdminTableName(): string
    {
        return 'account';
    }

    /**
     * @return array<string, string>
     */
    protected function getAdminColumnMap(): array
    {
        return [
            'id'                      => 'id',
            'email'                   => 'email',
            'password'                => 'password',
            'type'                    => 'type',
            'status'                  => 'status',
            'totp_enabled'            => 'totp_enabled',
            'totp_secret'             => 'totp_secret',
            'totp_last_used_step'     => 'totp_last_used_step',
            'reset_token'             => 'reset_token',
            'reset_token_expires_at'  => 'reset_token_expires_at',
        ];
    }
}
