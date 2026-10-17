<?php
namespace App\model;

final class EmailData
{
    private null|string $username;
    private null|string $password;
    private null|string $senderEmail;
    private null|string $senderName;
    private null|string $testEmail;

    public function __construct(private string $sender)
    {
        if ($sender === 'member') {
            $this->username = ($_ENV['APP_USERNAME'] ?? null) ?: (getenv("APP_USERNAME") ?: null);
            $this->password = ($_ENV['APP_PASSWORD'] ?? null) ?: (getenv("APP_PASSWORD") ?: null);
            $this->senderName = ($_ENV['APP_SENDER'] ?? null) ?: (getenv('APP_SENDER') ?: null);
            $this->senderEmail = ($_ENV['APP_EMAIL'] ?? null) ?: (getenv("APP_EMAIL") ?: null);
            $this->testEmail = ($_ENV['TEST_EMAIL'] ?? null) ?: (getenv("TEST_EMAIL") ?: null);
        } elseif ($sender === 'admin') {
            $this->username = ($_ENV['ADMIN_USERNAME'] ?? null) ?: (getenv("ADMIN_USERNAME") ?: null);
            $this->password = ($_ENV['ADMIN_PASSWORD'] ?? null) ?: (getenv("ADMIN_PASSWORD") ?: null);
            $this->senderName = ($_ENV['ADMIN_SENDER'] ?? null) ?: (getenv('ADMIN_SENDER') ?: null);
            $this->senderEmail = ($_ENV['ADMIN_EMAIL'] ?? null) ?: (getenv("ADMIN_EMAIL") ?: null);
            $this->testEmail = ($_ENV['TEST_EMAIL'] ?? null) ?: (getenv("TEST_EMAIL") ?: null);
        }
    }

    private function setEmailData(): void
    {
        if (!defined('USER_APP')) {
            define('USER_APP', $this->username);
        }
        if (!defined('PASS')) {
            define('PASS', $this->password);
        }
        if (!defined('APP_EMAIL')) {
            define('APP_EMAIL', $this->senderEmail);
        }
        if (!defined('APP_NAME')) {
            define('APP_NAME', $this->senderName);
        }
        if (!defined('TEST_EMAIL')) {
            define('TEST_EMAIL', $this->testEmail);
        }
    }

    public function getEmailData(): void
    {
        $this->setEmailData();
    }
}
