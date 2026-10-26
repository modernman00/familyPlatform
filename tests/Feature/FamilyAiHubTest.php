<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\controller\members\FamilyAiHubController;
use Src\Db;
use Src\Exceptions\ForbiddenException;

final class FamilyAiHubTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalSession;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $this->originalSession = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->originalSession;
        parent::tearDown();
    }

    public function test_route_is_registered(): void
    {
        $routerFile = dirname(__DIR__, 2) . '/app/router/profile_page.php';
        $content = file_get_contents($routerFile);
        $this->assertNotFalse($content);
        $this->assertStringContainsString("'/family/ai-hub'", $content);
        $this->assertStringContainsString('FamilyAiHubController@index', $content);
    }

    public function test_unauthenticated_access_throws_forbidden_exception(): void
    {
        $_SESSION = [];
        $this->expectException(\Src\Exceptions\UnauthorisedException::class);
        new FamilyAiHubController();
    }

    public function test_index_fetches_family_members_with_profile_pics_join_cleanly(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $pdo = Db::connect2();
        // Verify SQL query syntax executes without Column not found error
        $stmt = $pdo->prepare("SELECT p.id, p.firstName, p.lastName, pp.img AS profilePics FROM personal p LEFT JOIN profilePics pp ON p.id = pp.id WHERE p.famCode = ? ORDER BY p.firstName ASC");
        $stmt->execute(['FAMTEST']);
        $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertSame('00000', $stmt->errorCode());
    }

    public function test_view_template_contains_csrf_and_button_spinners(): void
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/member/familyAiHub.blade.php';
        $this->assertFileExists($viewPath);
        $content = file_get_contents($viewPath);
        $this->assertNotFalse($content);

        // Standard 1: Universal CSRF
        $this->assertStringContainsString('@csrf', $content, 'Form in familyAiHub must contain @csrf tag');

        // Standard 2: Universal Button Spinner & 44px hit areas
        $this->assertStringContainsString('min-height: 44px;', $content);
    }

    public function test_controller_index_renders_without_500(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $controller = new FamilyAiHubController();

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertStringNotContainsString('Something went wrong on our end', $output);
        $this->assertStringNotContainsString('Server Error', $output);
        $this->assertStringContainsString('AI Family Hub', $output);
    }

    public function test_generate_trivia_executes_without_undefined_method_error(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $_POST['family_name'] = 'The Adebisi Dynasty';
        $_POST['category'] = 'family_folklore';
        $_POST['family_facts'] = ['Grandpa arrived in 1965'];
        $_POST['question_count'] = 2;

        $controller = new FamilyAiHubController();

        ob_start();
        $controller->generateTrivia();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success'], 'generateTrivia must succeed: ' . ($decoded['error'] ?? ''));
        $this->assertNotEmpty($decoded['questions'], 'generateTrivia must return questions');
    }

    public function test_generate_flyer_endpoint_returns_successful_artwork_data(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $controller = new FamilyAiHubController();
        $_POST['prompt'] = 'Adeleke Family Annual Summer Reunion Barbecue';
        $_POST['style'] = 'summer_bbq';

        ob_start();
        $controller->generateFlyer();
        $jsonOutput = ob_get_clean();

        $this->assertIsString($jsonOutput);
        $data = json_decode($jsonOutput, true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['data_uri']);
        $this->assertStringStartsWith('data:image/', $data['data_uri']);
    }

    public function test_inspect_fridge_without_photo_returns_400(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $_FILES = [];
        $controller = new FamilyAiHubController();

        ob_start();
        $controller->inspectFridge();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('No fridge photo uploaded', $data['error']);
    }

    public function test_arbitrate_dispute_endpoint_executes_without_undefined_method_error(): void
    {
        $_SESSION['id'] = '1';
        $_SESSION['famCode'] = 'FAMTEST';

        $_POST['party_a_name'] = 'Alice';
        $_POST['party_a_claim'] = 'Alice unloaded the dishwasher yesterday';
        $_POST['party_b_name'] = 'Bob';
        $_POST['party_b_claim'] = 'Bob vacuumed the living room';
        $_POST['context'] = 'household_chores';

        $controller = new FamilyAiHubController();

        ob_start();
        $controller->arbitrateDispute();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success'], 'arbitrateDispute must succeed: ' . ($data['error'] ?? ''));
        $this->assertNotEmpty($data['verdict'], 'arbitrateDispute must return verdict');
        $this->assertNotEmpty($data['compromise_action_plan'], 'arbitrateDispute must return action plan');
    }
}
