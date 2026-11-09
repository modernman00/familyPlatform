<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Src\Exceptions\ValidationException;
use Src\Utility;

final class RegistrationDuplicateEmailTest extends TestCase
{
    public function test_validation_exception_retains_message_in_production(): void
    {
        $testEmail = 'bikeoguns@gmail.com';
        $exception = new ValidationException("Your email ({$testEmail}) is already registered. Please sign in or reset your password.");

        $this->assertSame(422, $exception->getStatusCode());
        $this->assertStringContainsString($testEmail, $exception->getMessage());

        // Simulate production environment
        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        $_ENV['APP_ENV'] = 'production';

        try {
            // Reflect on Utility::showError2 to verify the error response payload in production
            $refMethod = new \ReflectionMethod(Utility::class, 'showError2');
            $refMethod->setAccessible(true);
            $logger = \Src\LoggerFactory::getLogger();

            /** @var string $jsonResponse */
            $jsonResponse = $refMethod->invoke(null, $exception, $logger);
            $data = json_decode($jsonResponse, true);

            $this->assertIsArray($data);
            $this->assertSame(422, $data['code']);
            $this->assertSame('error', $data['status']);
            $this->assertSame(
                "Your email ({$testEmail}) is already registered. Please sign in or reset your password.",
                $data['message'],
                'ValidationException message must not be redacted to "An unexpected error occurred." in production.'
            );
        } finally {
            if ($originalEnv !== false) {
                putenv('APP_ENV=' . $originalEnv);
                $_ENV['APP_ENV'] = $originalEnv;
            } else {
                putenv('APP_ENV');
                unset($_ENV['APP_ENV']);
            }
        }
    }
}
