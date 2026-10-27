<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\TranslateController;
use PHPUnit\Framework\TestCase;

class TranslateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    public function testUnauthenticatedRequestIsRejected(): void
    {
        unset($_SESSION['id']);
        unset($_COOKIE['token']);

        $this->expectException(\Src\Exceptions\UnauthorisedException::class);
        new TranslateController();
    }

    public function testEmptyTextReturnsEmptyTranslation(): void
    {
        $_SESSION['id'] = '999';
        $_POST = ['text' => '', 'target' => 'fr'];

        $controller = new TranslateController();

        ob_start();
        $controller->translate();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertTrue($data['success']);
        $this->assertSame('', $data['translation']);
    }

    public function testOverlongTextIsRejected(): void
    {
        $_SESSION['id'] = '999';
        $_POST = ['text' => str_repeat('a', 5001), 'target' => 'fr'];

        $controller = new TranslateController();

        ob_start();
        $controller->translate();
        $output = ob_get_clean();

        $this->assertSame(400, http_response_code());
        $data = json_decode($output, true);
        $this->assertFalse($data['success']);
    }

    public function testTranslationSuccessAndSessionCaching(): void
    {
        $_SESSION['id'] = '999';
        $_SESSION['trans_cache'] = [];
        $_POST = ['text' => 'Good morning family', 'target' => 'fr'];

        $controller = new TranslateController();

        ob_start();
        $controller->translate();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['translation']);

        // Second call should hit the session cache
        ob_start();
        $controller->translate();
        $output2 = ob_get_clean();

        $data2 = json_decode($output2, true);
        $this->assertTrue($data2['success']);
        $this->assertTrue($data2['cached']);
        $this->assertSame($data['translation'], $data2['translation']);
    }

    public function testAdversarialPayloadsHandledGracefully(): void
    {
        $_SESSION['id'] = '999';
        $malicious = [
            '<script>alert("XSS")</script>',
            "'; DROP TABLE users; --",
            "en\0nullbyte",
            "../../../etc/passwd",
        ];

        $controller = new TranslateController();

        foreach ($malicious as $payload) {
            $_POST = ['text' => $payload, 'target' => 'es'];
            ob_start();
            $controller->translate();
            $output = ob_get_clean();

            $data = json_decode($output, true);
            $this->assertNotNull($data);
            $this->assertTrue($data['success']);
            $this->assertNotEmpty($data['translation']);
        }
    }
}
