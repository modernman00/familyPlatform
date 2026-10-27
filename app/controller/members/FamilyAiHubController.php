<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\PollData;
use App\model\Post;
use Src\Db;
use Src\SelectFn;
use Src\FileUploader;
use Src\Pusher;
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
            throw new ForbiddenException("Authentication required to access The Fun Place.");
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
     * Render The Fun Place & Judge Yommy dashboard view
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

        // Fetch Judge Yommy family disputes
        $yommyService = new \App\services\JudgeYommyTribunalService();
        $disputes = $yommyService->listFamilyDisputes($this->famCode);

        $selectedDisputeId = isset($_GET['dispute_id']) ? (string)$_GET['dispute_id'] : '';
        $selectedDispute = null;
        if (!empty($selectedDisputeId)) {
            $selectedDispute = $yommyService->getDisputeDetails($selectedDisputeId, $this->famCode);
        }

        $twoTruthsGame = \App\services\TwoTruthsGameService::getActiveGame($this->famCode, $this->userId);
        if (!$twoTruthsGame) {
            $userName = trim((string)(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? '')));
            $twoTruthsGame = \App\services\TwoTruthsGameService::ensureStarterGameIfEmpty($this->famCode, $this->userId, $userName);
        }

        view('member.familyAiHub', compact('data', 'userData', 'familyMembers', 'disputes', 'selectedDispute', 'twoTruthsGame'));
    }

    /**
     * Endpoint: File a Complex Family Dispute before Judge Yommy
     * Can be initiated by a direct disputant or a 3rd-party peacemaker.
     */
    public function fileComplexDispute(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $isThirdParty = !empty($json['is_third_party'] ?? $_POST['is_third_party'] ?? false);
            $partyAId = (string)($json['party_a_id'] ?? $_POST['party_a_id'] ?? '');
            $partyBId = (string)($json['party_b_id'] ?? $_POST['party_b_id'] ?? '');
            $witnessIds = (array)($json['witness_ids'] ?? $_POST['witness_ids'] ?? []);
            $title = (string)($json['title'] ?? $_POST['title'] ?? '');
            $category = (string)($json['category'] ?? $_POST['category'] ?? 'general_harmony');
            $grievance = (string)($json['grievance'] ?? $_POST['grievance'] ?? '');

            // If not third party, initiator is Party A
            if (!$isThirdParty && empty($partyAId)) {
                $partyAId = $this->userId;
            }

            $service = new \App\services\JudgeYommyTribunalService();
            $result = $service->fileDispute(
                famCode: $this->famCode,
                initiatorId: $this->userId,
                isThirdParty: (bool)$isThirdParty,
                partyAId: $partyAId,
                partyBId: $partyBId,
                witnessIds: $witnessIds,
                title: $title,
                category: $category,
                grievance: $grievance
            );

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Submit testimony / counter-statement in an ongoing hearing
     */
    public function submitDisputeStatement(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $disputeId = (string)($json['dispute_id'] ?? $_POST['dispute_id'] ?? '');
            $statement = (string)($json['statement'] ?? $_POST['statement'] ?? '');

            \releaseSessionLock();
            $service = new \App\services\JudgeYommyTribunalService();
            $result = $service->submitStatement($disputeId, $this->userId, $statement, $this->famCode);

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Convene court and deliver Judge Yommy's reconciliatory judgement
     */
    public function adjudicateDispute(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $disputeId = (string)($json['dispute_id'] ?? $_POST['dispute_id'] ?? '');

            \releaseSessionLock();
            $service = new \App\services\JudgeYommyTribunalService();
            $result = $service->adjudicate($disputeId, $this->famCode);

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Fetch details of a specific dispute docket
     *
     * @param string $disputeId
     */
    public function getDisputeDetails(string $disputeId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $service = new \App\services\JudgeYommyTribunalService();
            $details = $service->getDisputeDetails($disputeId, $this->famCode);

            if (!$details) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Dispute docket not found.']);
                return;
            }

            echo json_encode(['success' => true, 'dispute' => $details]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Arbitrate Household / Sibling Dispute (Fast 2-Minute Instant Referee)
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
            $context = (string)($json['context'] ?? $_POST['context'] ?? 'household_harmony');
            $isThirdParty = !empty($json['is_third_party'] ?? $_POST['is_third_party'] ?? false);
            $initiatorName = (string)($json['initiator_name'] ?? $_POST['initiator_name'] ?? null);

            $service = new \App\services\JudgeYommyTribunalService();
            $result = $service->arbitrateInstant(
                partyAName: $partyA,
                partyAClaim: $claimA,
                partyBName: $partyB,
                partyBClaim: $claimB,
                context: $context,
                isThirdParty: (bool)$isThirdParty,
                initiatorName: $initiatorName
            );

            // Backward compatibility mapping for frontends expecting verdict, compromise_action_plan, closing_quip
            if ($result['success']) {
                $result['verdict'] = $result['finding_of_fact'] . "\n\n" . $result['reconciliatory_bridge'];
                $result['compromise_action_plan'] = $result['restorative_action_plan'];
                $result['penalty_or_chore'] = 'Restorative Family Act: Share a warm hug, an apology, and complete the action plan together.';
                $result['closing_quip'] = $result['yommy_wisdom'];
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
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

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
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

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
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
            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Generate & Post Family Game Show Quiz
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

            if (!empty($result['success']) && !empty($result['questions']) && is_array($result['questions'])) {
                $db = Db::connect2();
                $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId) ?: [];
                $firstName = (string)($userRow['firstName'] ?? '');
                $lastName = (string)($userRow['lastName'] ?? '');
                $fullName = trim($firstName . ' ' . $lastName);
                if ($fullName === '') {
                    $fullName = $familyName . ' Member';
                }

                $pStmt = $db->prepare("SELECT img FROM profilePics WHERE id = ? LIMIT 1");
                $pStmt->execute([$this->userId]);
                $profileImg = (string)($pStmt->fetchColumn() ?: '');

                $quizTitle = (string)($result['quiz_title'] ?? ($familyName . ' Family Game Show'));
                $quizTitle = str_ireplace(['Dynasty Trivia Clash', 'Dynasty Trivia'], 'Family Game Show', $quizTitle);
                $result['quiz_title'] = $quizTitle;

                $postMsg = "🎮 Family Game Show: " . $quizTitle . "!\n\n";
                $postMsg .= "We just launched our Family Game Show! Test how well you know our family stories and see who gets top marks.\n\n";
                $postMsg .= "Round 1 question is live in the poll below. Cast your vote and share your memories in the comments!";

                $currentTime = (string)(function_exists('milliSeconds') ? milliSeconds() : time());
                $postStmt = $db->prepare(
                    "INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, date_created) 
                     VALUES (?, ?, ?, ?, ?, ?, 'published', NOW())"
                );
                $postStmt->execute([
                    $this->userId,
                    $fullName,
                    $profileImg,
                    $postMsg,
                    $this->famCode,
                    $currentTime
                ]);
                $postNo = (int)$db->lastInsertId();

                if (!empty($result['questions'][0]['question']) && !empty($result['questions'][0]['options'])) {
                    $q1 = $result['questions'][0];
                    PollData::createPoll($postNo, (string)$q1['question'], (array)$q1['options']);
                }

                // Explicitly mark post as published
                Post::updatePostByStatusAsPublished($postNo);

                // Realtime broadcast via Pusher (resilient)
                try {
                    Pusher::broadcastToFamily($this->famCode, 'new-post', [[
                        'post_no' => $postNo,
                        'id' => $this->userId,
                        'fullName' => $fullName,
                        'profileImg' => $profileImg,
                        'postMessage' => $postMsg,
                        'postFamCode' => $this->famCode,
                        'post_img0' => '',
                        'date_created' => date('Y-m-d H:i:s'),
                    ]]);
                } catch (\Throwable $pusherEx) {
                    // Resilient broadcast fallback
                }

                $result['posted'] = true;
                $result['post_no'] = $postNo;
                $result['post_url'] = '/profilePage?post_no=' . $postNo . '#post' . $postNo;
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint: Discover Live UK Weekend Family Outings & Battle Card
     * Powered by Src\Ai\ResilientWebSearchService (72h SHA-256 Cache + DeepSeek Search API + £0 Fallback)
     */
    public function discoverWeekendOutings(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            $region = htmlspecialchars(strip_tags(trim((string)($json['region'] ?? $_POST['region'] ?? 'London'))), ENT_QUOTES, 'UTF-8');
            $ageGroup = htmlspecialchars(strip_tags(trim((string)($json['age_group'] ?? $_POST['age_group'] ?? 'all-ages'))), ENT_QUOTES, 'UTF-8');

            $service = new \Src\Ai\ResilientWebSearchService();
            $result = $service->discoverFamilyWeekendOutings($region, $ageGroup);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
