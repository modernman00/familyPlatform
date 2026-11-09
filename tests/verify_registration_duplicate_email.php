<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Src\Exceptions\ValidationException;
use Src\Utility;

echo "=================================================================\n";
echo "🛡️  VERIFY REGISTRATION DUPLICATE EMAIL SUITE\n";
echo "=================================================================\n\n";

$testEmail = 'bikeoguns@gmail.com';

echo "[TEST 1] ValidationException carries HTTP 422 status and friendly copy...\n";
$exception = new ValidationException("Your email ({$testEmail}) is already registered. Please sign in or reset your password.");
if ($exception->getStatusCode() !== 422) {
    echo "  ❌ FAILED: Expected status code 422, got " . $exception->getStatusCode() . "\n";
    exit(1);
}
if (!str_contains($exception->getMessage(), $testEmail)) {
    echo "  ❌ FAILED: Exception message does not contain email: " . $exception->getMessage() . "\n";
    exit(1);
}
echo "  ✅ PASSED: ValidationException correctly configured with HTTP 422.\n\n";

echo "[TEST 2] Verifying production error payload preserves friendly message (no 'An unexpected error occurred.')...\n";
$originalEnv = getenv('APP_ENV');
putenv('APP_ENV=production');
$_ENV['APP_ENV'] = 'production';

try {
    $refMethod = new \ReflectionMethod(Utility::class, 'showError2');
    $refMethod->setAccessible(true);
    $logger = \Src\LoggerFactory::getLogger();

    /** @var string $jsonResponse */
    $jsonResponse = $refMethod->invoke(null, $exception, $logger);
    $data = json_decode($jsonResponse, true);

    if (!is_array($data)) {
        echo "  ❌ FAILED: Response is not valid JSON.\n";
        exit(1);
    }

    if (($data['code'] ?? null) !== 422) {
        echo "  ❌ FAILED: Expected code 422 in response, got " . var_export($data['code'] ?? null, true) . "\n";
        exit(1);
    }

    if (($data['status'] ?? null) !== 'error') {
        echo "  ❌ FAILED: Expected status 'error', got " . var_export($data['status'] ?? null, true) . "\n";
        exit(1);
    }

    $expectedMsg = "Your email ({$testEmail}) is already registered. Please sign in or reset your password.";
    if (($data['message'] ?? '') !== $expectedMsg) {
        echo "  ❌ FAILED: Message was redacted or mismatched.\n";
        echo "     Expected: {$expectedMsg}\n";
        echo "     Got: " . ($data['message'] ?? 'null') . "\n";
        exit(1);
    }

    echo "  ✅ PASSED: Production error response returns HTTP 422 and exact friendly message.\n\n";
} finally {
    if ($originalEnv !== false) {
        putenv('APP_ENV=' . $originalEnv);
        $_ENV['APP_ENV'] = $originalEnv;
    } else {
        putenv('APP_ENV');
        unset($_ENV['APP_ENV']);
    }
}

echo "[TEST 3] Verifying Register.php uses ValidationException for existing email check...\n";
$registerCode = file_get_contents(__DIR__ . '/../app/controller/register/Register.php');
if (!is_string($registerCode)) {
    echo "  ❌ FAILED: Could not read Register.php\n";
    exit(1);
}

if (str_contains($registerCode, 'throw new Exception("Your email-{$cleanData[\'email\']} is already registered");')) {
    echo "  ❌ FAILED: Register.php still contains legacy generic Exception throw.\n";
    exit(1);
}

if (!str_contains($registerCode, 'throw new ValidationException("Your email ({$cleanData[\'email\']}) is already registered. Please sign in or reset your password.");')) {
    echo "  ❌ FAILED: Register.php does not throw ValidationException with friendly copy.\n";
    exit(1);
}
echo "  ✅ PASSED: Register.php properly throws ValidationException.\n\n";

echo "[TEST 4] Verifying RecaptchaException inherits HttpException and preserves actionable guidance in production...\n";
$recaptchaEx = new \Src\Exceptions\RecaptchaFailedException("Security verification failed. Please refresh the page and try again.");
if (!($recaptchaEx instanceof \Src\Exceptions\HttpException)) {
    echo "  ❌ FAILED: RecaptchaFailedException does not inherit from HttpException.\n";
    exit(1);
}

putenv('APP_ENV=production');
$_ENV['APP_ENV'] = 'production';
try {
    $refMethod = new \ReflectionMethod(Utility::class, 'showError2');
    $refMethod->setAccessible(true);
    $logger = \Src\LoggerFactory::getLogger();

    /** @var string $jsonResponse */
    $jsonResponse = $refMethod->invoke(null, $recaptchaEx, $logger);
    $data = json_decode($jsonResponse, true);

    if (($data['code'] ?? null) !== 400) {
        echo "  ❌ FAILED: Expected code 400 for RecaptchaFailedException, got " . var_export($data['code'] ?? null, true) . "\n";
        exit(1);
    }

    if (($data['message'] ?? '') !== "Security verification failed. Please refresh the page and try again.") {
        echo "  ❌ FAILED: Recaptcha message was redacted to: " . var_export($data['message'] ?? null, true) . "\n";
        exit(1);
    }
    echo "  ✅ PASSED: RecaptchaFailedException retains actionable guidance in production.\n\n";
} finally {
    if ($originalEnv !== false) {
        putenv('APP_ENV=' . $originalEnv);
        $_ENV['APP_ENV'] = $originalEnv;
    } else {
        putenv('APP_ENV');
        unset($_ENV['APP_ENV']);
    }
}

echo "=================================================================\n";
echo "🏆 ALL REGISTRATION DUPLICATE EMAIL TESTS PASSED!\n";
echo "=================================================================\n";
