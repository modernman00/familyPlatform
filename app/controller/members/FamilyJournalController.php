<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\FamilyJournal;
use App\services\AIService;
use Src\CheckToken;
use Src\FileUploader;
use Src\SelectFn;
use Src\functionality\SignIn;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

final class FamilyJournalController extends BaseController
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
            throw new ForbiddenException("Authentication required to access family journals.");
        }

        $famCode = (string)($_SESSION['famCode'] ?? '');
        if (empty($famCode)) {
            $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId);
            $famCode = (string)($userRow['famCode'] ?? '');
            $_SESSION['famCode'] = $famCode;
        }
        $this->famCode = $famCode;
        if (empty($this->famCode)) {
            throw new ForbiddenException("Active family group membership is required to access family chronicles.");
        }
    }

    /**
     * Display the Family Journal shelf
     */
    public function index(): void
    {
        try {
            $userData = $this->membersData();
            $journals = FamilyJournal::getFamilyJournals($this->famCode, 50, 0);

            parent::viewWithCsp('member/journal/index', [
                'data' => $userData,
                'journals' => $journals,
                'familyCode' => $this->famCode,
                'totalCount' => count($journals)
            ]);
        } catch (\Throwable $th) {
            \Src\Utility::showError($th);
        }
    }

    /**
     * Show form to create a new family journal entry
     */
    public function create(): void
    {
        try {
            $userData = $this->membersData();

            parent::viewWithCsp('member/journal/create', [
                'data' => $userData,
                'familyCode' => $this->famCode
            ]);
        } catch (\Throwable $th) {
            \Src\Utility::showError($th);
        }
    }

    /**
     * Display a single family journal
     */
    public function show(int|string $id): void
    {
        try {
            $userData = $this->membersData();
            $journal = FamilyJournal::getJournalById($id, $this->famCode);

            if (!$journal) {
                throw new NotFoundException("Journal entry not found or belongs to another family network.");
            }

            parent::viewWithCsp('member/journal/show', [
                'data' => $userData,
                'journal' => $journal,
                'familyCode' => $this->famCode
            ]);
        } catch (\Throwable $th) {
            \Src\Utility::showError($th);
        }
    }

    /**
     * Store a new family journal entry
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        try {
            CheckToken::tokenCheck();

            $title = trim((string)filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $category = trim((string)filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $mood = trim((string)filter_input(INPUT_POST, 'mood', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $summary = trim((string)filter_input(INPUT_POST, 'summary', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $contentRaw = $_POST['content'] ?? '';
            $status = (string)filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if (empty($title) || empty($contentRaw)) {
                throw new ValidationException("Title and story content are required.");
            }

            if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
                throw new ValidationException("Story title must be between 3 and 255 characters.");
            }

            if (strlen((string)$contentRaw) > 500000) {
                throw new ValidationException("Story content exceeds maximum allowed length (500KB).");
            }

            $allowedCategories = ['Milestone', 'Heritage & Tradition', 'Recipe', 'Oral History', 'Celebration', 'Memorial'];
            if (!in_array($category, $allowedCategories, true)) {
                $category = 'Milestone';
            }

            $allowedMoods = ['heartwarming', 'reflective', 'inspiring', 'humorous'];
            if (!in_array($mood, $allowedMoods, true)) {
                $mood = 'heartwarming';
            }

            $allowedStatuses = ['published', 'draft', 'archived'];
            if (!in_array($status, $allowedStatuses, true)) {
                $status = 'published';
            }

            // Sanitize rich HTML content using modern DOM-based HTML purifier
            $cleanContent = AIService::purifyHtml((string)$contentRaw);

            if (empty($summary)) {
                $plain = strip_tags($cleanContent);
                $summary = mb_substr($plain, 0, 200) . (mb_strlen($plain) > 200 ? '...' : '');
            } else {
                $summary = mb_substr($summary, 0, 500);
            }

            // Slug generation
            $slug = strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            $slug .= '-' . substr(uniqid(), -4);

            $coverImage = null;
            if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Enterprise Gate: Strictly enforce image mime types and extensions for cover photos
                $uploadedName = (string)($_FILES['cover_image']['name'] ?? '');
                $fileExtension = strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($fileExtension, $allowedExtensions, true)) {
                    throw new ValidationException("Cover photo must be a valid image file (JPEG, PNG, or WebP).");
                }

                $tmpFile = (string)($_FILES['cover_image']['tmp_name'] ?? '');
                if (is_file($tmpFile)) {
                    $imgInfo = @getimagesize($tmpFile);
                    if ($imgInfo === false || !in_array($imgInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                        throw new ValidationException("Cover photo file contents do not match a valid image format.");
                    }
                }

                $docRoot = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
                $targetDir = rtrim($docRoot, '/') . '/public/images/journals/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                $uploadResult = FileUploader::fileUploadSingle($targetDir, 'cover_image');
                $coverImage = '/public/images/journals/' . $uploadResult['fileName'];
            }

            $journalData = [
                'family_code' => $this->famCode,
                'author_id'   => $this->userId,
                'title'       => $title,
                'slug'        => $slug,
                'summary'     => $summary,
                'content'     => $cleanContent,
                'cover_image' => $coverImage,
                'category'    => $category,
                'mood'        => $mood,
                'status'      => $status
            ];

            $insertedId = FamilyJournal::createJournal($journalData);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response with json_encode and application/json header
            echo json_encode([
                'status'  => 'success',
                'message' => 'Family journal entry preserved successfully!',
                'id'      => $insertedId,
                'redirect'=> '/familyJournal/' . $insertedId
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * AI Story Assistant: Draft a family memory from prompt
     */
    public function aiDraft(): void
    {
        header('Content-Type: application/json');
        try {
            CheckToken::tokenCheck();

            // Enterprise Rate Limiting: Prevent LLM quota exhaustion and Denial of Wallet
            try {
                \Src\Limiter::limit($this->userId, 'post');
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Too many AI story generation requests. Please wait a few minutes before trying again.'
                ]);
                exit;
            }

            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
            $prompt = trim((string)($input['prompt'] ?? ''));
            $rawTone = trim((string)($input['tone'] ?? 'Heartwarming'));

            if (empty($prompt)) {
                throw new ValidationException("Please provide a family story prompt or topic.");
            }

            if (mb_strlen($prompt) < 5 || mb_strlen($prompt) > 500) {
                throw new ValidationException("Story prompt must be between 5 and 500 characters.");
            }

            $allowedTones = [
                'Heartwarming' => 'Heartwarming',
                'Heritage & Tradition' => 'Heritage & Tradition',
                'Celebratory' => 'Celebratory',
                'Humorous' => 'Humorous',
                'Reflective & Nostalgic' => 'Reflective & Nostalgic',
                'Inspiring' => 'Inspiring',
            ];
            $tone = $allowedTones[$rawTone] ?? 'Heartwarming';

            $draft = AIService::generateBlogDraft($prompt, $tone);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response with json_encode and application/json header
            echo json_encode([
                'status' => 'success',
                'data'   => $draft
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Delete a family journal entry
     */
    public function delete(int|string $id): void
    {
        header('Content-Type: application/json');
        try {
            CheckToken::tokenCheck();

            $isAdmin = ($_SESSION['type'] ?? '') === 'admin';
            $success = FamilyJournal::deleteJournal($id, $this->famCode, $this->userId, $isAdmin);

            if (!$success) {
                throw new ForbiddenException("Unable to delete entry. Only the author or family admin may remove this journal.");
            }

            echo json_encode([
                'status'  => 'success',
                'message' => 'Journal entry removed.'
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}
