<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\Organogram;
use App\services\LineagePromptService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Db;
use PDO;

/**
 * LineagePromptTest
 *
 * Automated regression & security test suite for the Lineage Tree Prompt Engine.
 * Verifies:
 *  1. Self-prompt denial (422)
 *  2. Unauthenticated caller rejection (401)
 *  3. Cross-family/unauthorized boundary rejection (422)
 *  4. CSRF protection verification (403)
 *  5. Successful multi-channel prompt dispatch
 *  6. 24-hour rate limit anti-spam debounce
 */
final class LineagePromptTest extends TestCase
{
    private PDO $pdo;
    private string $testFamCode;
    private string $senderId;
    private string $targetId;
    private string $outsiderId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();

        $this->testFamCode = 'PROMPT_TEST_' . bin2hex(random_bytes(4));
        $this->senderId = 'prompt_sender_' . bin2hex(random_bytes(4));
        $this->targetId = 'prompt_target_' . bin2hex(random_bytes(4));
        $this->outsiderId = 'prompt_outsider_' . bin2hex(random_bytes(4));

        // Seed sender (Family A)
        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
            VALUES (?, 'John', 'Doe', ?, 'Male', 1, 'Jan', 1990, 0)
        ")->execute([$this->senderId, $this->testFamCode]);

        $this->pdo->prepare("
            INSERT INTO contact (id, email, country, mobile)
            VALUES (?, 'sender@example.com', 'UK', '07123456789')
        ")->execute([$this->senderId]);

        // Seed target (Family A)
        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
            VALUES (?, 'Olusola', 'Olaogun', ?, 'Male', 1, 'Jan', 1985, 0)
        ")->execute([$this->targetId, $this->testFamCode]);

        $this->pdo->prepare("
            INSERT INTO contact (id, email, country, mobile)
            VALUES (?, 'olusola@example.com', 'UK', '07123456788')
        ")->execute([$this->targetId]);

        // Seed outsider (Family B)
        $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
            VALUES (?, 'Eve', 'Outsider', 'OTHER_FAMILY_99', 'Female', 1, 'Jan', 1992, 0)
        ")->execute([$this->outsiderId]);

        $this->pdo->prepare("
            INSERT INTO contact (id, email, country, mobile)
            VALUES (?, 'outsider@example.com', 'UK', '07123456787')
        ")->execute([$this->outsiderId]);
    }

    protected function tearDown(): void
    {
        // Clean up test notifications
        $stmt = $this->pdo->prepare("DELETE FROM notification WHERE sender_id IN (?, ?, ?) OR receiver_id IN (?, ?, ?)");
        $stmt->execute([$this->senderId, $this->targetId, $this->outsiderId, $this->senderId, $this->targetId, $this->outsiderId]);

        // Clean up test contacts
        $stmt = $this->pdo->prepare("DELETE FROM contact WHERE id IN (?, ?, ?)");
        $stmt->execute([$this->senderId, $this->targetId, $this->outsiderId]);

        // Clean up test members
        $stmt = $this->pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)");
        $stmt->execute([$this->senderId, $this->targetId, $this->outsiderId]);

        parent::tearDown();
    }

    public function test_self_prompt_is_strictly_rejected(): void
    {
        $service = new LineagePromptService();
        $result = $service->sendTreeBuildPrompt($this->senderId, $this->senderId, 'Check your tree');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('cannot prompt yourself', $result['message']);
    }

    public function test_cross_family_prompt_is_rejected_without_view_access(): void
    {
        $_SESSION = [
            'id' => $this->outsiderId,
            'famCode' => 'OTHER_FAMILY_99'
        ];

        $service = new LineagePromptService();
        $result = $service->sendTreeBuildPrompt($this->outsiderId, $this->targetId);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('own family network', $result['message']);
    }

    public function test_successful_prompt_dispatches_notification(): void
    {
        $_SESSION = [
            'id' => $this->senderId,
            'famCode' => $this->testFamCode
        ];

        $service = new LineagePromptService();
        $result = $service->sendTreeBuildPrompt($this->senderId, $this->targetId, 'Please add your parents!');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Olusola', $result['message']);

        // Verify in-app notification record was created
        $stmt = $this->pdo->prepare("
            SELECT * FROM notification 
            WHERE sender_id = ? AND receiver_id = ? AND notification_type = 'Lineage'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$this->senderId, $this->targetId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertStringContainsStringIgnoringCase('family tree', (string)($row['notification_name'] ?? ''));
        $this->assertStringContainsStringIgnoringCase('family tree', (string)($row['notification_content'] ?? ''));
        $this->assertStringContainsString('/organogram', (string)($row['action_url'] ?? ''));
    }

    public function test_subsequent_prompt_within_24_hours_is_debounced(): void
    {
        $_SESSION = [
            'id' => $this->senderId,
            'famCode' => $this->testFamCode
        ];

        $service = new LineagePromptService();
        
        // First prompt
        $firstResult = $service->sendTreeBuildPrompt($this->senderId, $this->targetId);
        $this->assertTrue($firstResult['success']);

        // Immediate subsequent prompt should be debounced
        $secondResult = $service->sendTreeBuildPrompt($this->senderId, $this->targetId);
        $this->assertFalse($secondResult['success']);
        $this->assertStringContainsString('already sent an invitation', $secondResult['message']);
    }

    public function test_controller_requires_valid_csrf_token(): void
    {
        $_SESSION = [
            'id' => $this->senderId,
            'famCode' => $this->testFamCode,
            'token' => 'real_token_123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_XSRF_TOKEN'] = 'invalid_tampered_token';
        $_POST = ['target_user_id' => $this->targetId];

        $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();

        ob_start();
        $controller->promptBuildTree();
        $raw = ob_get_clean() ?: '';

        $response = json_decode($raw, true) ?: [];
        $this->assertFalse($response['success'] ?? true);
        $this->assertSame(403, http_response_code());
    }

    public function test_controller_requires_authentication(): void
    {
        $token = 'test_csrf_token_' . bin2hex(random_bytes(8));
        $_SESSION = [
            'token' => $token
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_XSRF_TOKEN'] = $token;
        $_POST = [
            'target_user_id' => $this->targetId,
            'token' => $token
        ];

        $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();

        ob_start();
        $controller->promptBuildTree();
        $raw = ob_get_clean() ?: '';

        $response = json_decode($raw, true) ?: [];
        $this->assertFalse($response['success'] ?? true);
        $this->assertSame(401, http_response_code());
    }

    public function test_controller_validates_target_user_id_presence(): void
    {
        $token = 'test_csrf_token_' . bin2hex(random_bytes(8));
        $_SESSION = [
            'id' => $this->senderId,
            'famCode' => $this->testFamCode,
            'token' => $token
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_XSRF_TOKEN'] = $token;
        $_POST = [
            'target_user_id' => '',
            'token' => $token
        ];

        $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();

        ob_start();
        $controller->promptBuildTree();
        $raw = ob_get_clean() ?: '';

        $response = json_decode($raw, true) ?: [];
        $this->assertFalse($response['success'] ?? true);
        $this->assertSame(400, http_response_code());
    }
}
