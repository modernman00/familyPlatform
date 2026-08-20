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
            $this->username = getenv("APP_USERNAME") ?: null;
            $this->password = getenv("APP_PASSWORD") ?: null;
            $this->senderName = getenv('APP_SENDER') ?: null;
            $this->senderEmail = getenv("APP_EMAIL") ?: null;
            $this->testEmail = getenv("TEST_EMAIL") ?: null;
        } elseif ($sender === 'admin') {
            $this->username = getenv("ADMIN_USERNAME") ?: null;
            $this->password = getenv("ADMIN_PASSWORD") ?: null;
            $this->senderName = getenv('ADMIN_SENDER') ?: null;
            $this->senderEmail = getenv("ADMIN_EMAIL") ?: null;
            $this->testEmail = getenv("TEST_EMAIL") ?: null;
        }
    }

    private function setEmailData(): void
    {
        define('USER_APP', $this->username);
        define('PASS', $this->password);
        define('APP_EMAIL', $this->senderEmail);
        define('APP_NAME', $this->senderName);
        define('TEST_EMAIL', $this->testEmail);
    }

    public function getEmailData(): void
    {
        $this->setEmailData();
    }
}
