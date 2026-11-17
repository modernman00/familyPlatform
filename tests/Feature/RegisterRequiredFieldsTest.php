<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\register\Register;
use PHPUnit\Framework\TestCase;

final class RegisterRequiredFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = ['token' => 'test-csrf-token'];
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_SERVER['CONTENT_TYPE'], $_SERVER['HTTP_CONTENT_TYPE']);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
        unset($_SERVER['CONTENT_TYPE'], $_SERVER['HTTP_CONTENT_TYPE']);
        parent::tearDown();
    }

    public function test_raw_api_register_omitting_country_returns_400_validation_error(): void
    {
        $_POST = [
            'token' => 'test-csrf-token',
            'firstName' => 'Ada',
            'lastName' => 'Lovelace',
            'mobile' => '+447700900123',
            'email' => 'ada.missing.country@example.com',
            'password' => 'StrongPass123!',
            'confirm_password' => 'StrongPass123!',
        ];

        ob_start();
        (new Register())->processForm();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded, 'Expected JSON error response, got: ' . $output);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertSame(400, $decoded['code'] ?? null);
        $this->assertStringContainsString('country', strtolower((string) ($decoded['message'] ?? '')));
    }

    public function test_raw_api_register_omitting_mobile_returns_400_validation_error(): void
    {
        $_POST = [
            'token' => 'test-csrf-token',
            'firstName' => 'Ada',
            'lastName' => 'Lovelace',
            'country' => 'United Kingdom',
            'email' => 'ada.missing.mobile@example.com',
            'password' => 'StrongPass123!',
            'confirm_password' => 'StrongPass123!',
        ];

        ob_start();
        (new Register())->processForm();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded, 'Expected JSON error response, got: ' . $output);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertSame(400, $decoded['code'] ?? null);
        $this->assertStringContainsString('mobile', strtolower((string) ($decoded['message'] ?? '')));
    }
}
