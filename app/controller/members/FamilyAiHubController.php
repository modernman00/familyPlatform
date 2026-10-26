<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\Db;
use Src\SelectFn;
use Src\FileUploader;
use Src\functionality\SignIn;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

final class FamilyAiHubController extends BaseController
{
    private string $userId;
    private string $famCode;

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }

        $this->userId = (string)($_SESSION['id'] ?? '');
        if (empty($this->userId)) {
            throw new ForbiddenException("Authentication required to access AI Family Hub.");
        }

        $famCode = (string)($_SESSION['famCode'] ?? '');
        if (empty($famCode)) {
            $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId);
            $famCode = (string)($userRow['famCode'] ?? '');
            $_SESSION['famCode'] = $famCode;
        }
        $this->famCode = $famCode;
    }

    /**
     * Render the AI Family Hub dashboard view
     */
    public function index(): void
    {
        $userData = SelectFn::selectOneRow('personal', 'id', $this->userId) ?: [];
        $data = $userData;

        // Fetch family members in the same family code for dispute referee dropdown
        $pdo = Db::connect2();
        $stmt = $pdo->prepare("SELECT p.id, p.firstName, p.lastName, pp.img AS profilePics FROM personal p LEFT JOIN profilePics pp ON p.id = pp.id WHERE p.famCode = ? ORDER BY p.firstName ASC");
        $stmt->execute([$this->famCode]);
        $familyMembers = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        view('member.familyAiHub', compact('data', 'userData', 'familyMembers'));
    }

    /**
     * Endpoint: Arbitrate Household / Sibling Dispute
     */
    public function arbitrateDispute(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $partyA = (string)($json['party_a_name'] ?? $_POST['party_a_name'] ?? '');
            $claimA = (string)($json['party_a_claim'] ?? $_POST['party_a_claim'] ?? '');
            $partyB = (string)($json['party_b_name'] ?? $_POST['party_b_name'] ?? '');
            $claimB = (string)($json['party_b_claim'] ?? $_POST['party_b_claim'] ?? '');
            $context = (string)($json['context'] ?? $_POST['context'] ?? 'household_chores');

            $service = new \Src\Ai\AiEntertainmentService();
            $result = $service->arbitrateDispute($partyA, $claimA, $partyB, $claimB, $context);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Inspect Fridge / Pantry and Suggest 15-Minute Meals
     *
     * Processes photo using shared-lib FileUploader for auto-downscaling,
     * HEIC transcoding, and image optimization before passing to AI Vision.
     */
    public function inspectFridge(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $uploadedPath = null;
        try {
            if (!isset($_FILES['photo']) || empty($_FILES['photo']['tmp_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No fridge photo uploaded.']);
                return;
            }

            $docRoot = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
            $targetDir = rtrim($docRoot, '/') . '/storage/ai_temp/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Route through shared-lib FileUploader: downscales >2000px, converts HEIC->JPG, optimizes with Spatie
            $uploadResult = FileUploader::fileUploadSingle($targetDir, 'photo');
            $uploadedPath = $uploadResult['filePath'];

            $service = new \Src\Ai\AiVisionService();
            $result = $service->inspectFridge($uploadedPath);

            echo json_encode($result);
        } catch (ValidationException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } finally {
            if ($uploadedPath !== null && isset($targetDir)) {
                $safeFile = $targetDir . basename($uploadedPath);
                if (is_file($safeFile)) {
                    // nosemgrep: php.lang.security.unlink-use.unlink-use
                    @unlink($safeFile);
                }
            }
        }
    }

    /**
     * Endpoint: Transcribe Voice Chores / Groceries
     */
    public function transcribeVoice(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!isset($_FILES['audio']) || empty($_FILES['audio']['tmp_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No audio file uploaded.']);
                return;
            }

            if (($_FILES['audio']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Audio upload failed with error code: ' . (int)$_FILES['audio']['error']]);
                return;
            }

            $tmpPath = (string)$_FILES['audio']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo !== false ? finfo_file($finfo, $tmpPath) : false;

            $allowedAudioMimes = [
                'audio/webm', 'video/webm', 'audio/ogg', 'application/ogg',
                'audio/wav', 'audio/x-wav', 'audio/mp4', 'video/mp4',
                'audio/x-m4a', 'audio/mpeg', 'audio/mp3', 'audio/aac',
                'audio/x-aac', 'application/octet-stream',
            ];
            if (!is_string($mime) || !in_array($mime, $allowedAudioMimes, true)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Invalid audio format detected.']);
                return;
            }

            $service = new \Src\Ai\VoiceTranscriptionService();
            $result = $service->transcribe($tmpPath);

            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Generate Family Reunion / Celebration Flyer via FLUX Diffusion
     */
    public function generateFlyer(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $theme = 'Family Reunion Picnic';
        $style = 'summer_bbq';

        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            $theme = (string)($json['theme'] ?? $json['prompt'] ?? $_POST['theme'] ?? $_POST['prompt'] ?? $theme);
            $style = (string)($json['style'] ?? $_POST['style'] ?? $style);

            $service = new \Src\Ai\AiImageGenerationService();
            $result = $service->generatePosterBackground($theme, $style);
            unset($result['binary_bytes']);
            $result['image_base64'] = $result['data_uri'];

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Generate Dynasty Trivia Clash Quiz Questions
     */
    public function generateTrivia(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $familyName = (string)($json['family_name'] ?? $_POST['family_name'] ?? 'The Family');
            $familyFacts = (array)($json['family_facts'] ?? $_POST['family_facts'] ?? []);
            $category = (string)($json['category'] ?? $_POST['category'] ?? 'family_folklore');
            $questionCount = (int)($json['question_count'] ?? $_POST['question_count'] ?? 3);

            $service = new \Src\Ai\AiEntertainmentService();
            $result = $service->generateFamilyTrivia($familyName, $familyFacts, $category, $questionCount);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
