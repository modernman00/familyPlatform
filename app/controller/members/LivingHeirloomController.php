<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\AncestralReverseEngine;
use App\services\FamilyPortraitSynthesisService;
use InvalidArgumentException;
use Src\Db;
use Src\Limiter;
use Src\SelectFn;
use Src\functionality\SignIn;
use Src\Exceptions\ForbiddenException;
use Throwable;

final class LivingHeirloomController extends BaseController
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
            throw new ForbiddenException("Authentication required to access Living Heirloom.");
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
     * Render the Living Heirloom studio.
     */
    public function index(): void
    {
        $userData = SelectFn::selectOneRow('personal', 'id', $this->userId) ?: [];
        $data = $userData;

        $pdo = Db::connect2();
        $pdo->exec("CREATE TABLE IF NOT EXISTS `family_living_heirlooms` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` VARCHAR(64) NOT NULL,
            `fam_code` VARCHAR(64) NOT NULL,
            `ancestor_name` VARCHAR(255) NOT NULL,
            `era_year` VARCHAR(50) NULL,
            `image_url` TEXT NOT NULL,
            `story_note` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_fam` (`fam_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $stmt = $pdo->prepare("SELECT * FROM family_living_heirlooms WHERE fam_code = ? ORDER BY id DESC LIMIT 20");
        $stmt->execute([$this->famCode]);
        $heirlooms = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $reverseEngine = new AncestralReverseEngine();
        $treeInventory = $reverseEngine->getTreePortraitInventory($pdo, $this->famCode);

        view('member.livingHeirloom', compact('data', 'userData', 'heirlooms', 'treeInventory'));
    }

    /**
     * API: Save an animated or synthesised heirloom portrait to the family archives.
     * Route: POST /api/heirloom/save-portrait
     */
    public function savePortrait(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            $ancestorName = trim(strip_tags((string)($json['ancestor_name'] ?? $_POST['ancestor_name'] ?? 'Family Ancestor')));
            $eraYear = trim(strip_tags((string)($json['era_year'] ?? $_POST['era_year'] ?? 'Circa 1950')));
            $imageUrl = trim((string)($json['image_url'] ?? $_POST['image_url'] ?? ''));
            $storyNote = trim(strip_tags((string)($json['story_note'] ?? $_POST['story_note'] ?? '')));

            if ($imageUrl === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid portrait image required.']);
                return;
            }

            $pdo = Db::connect2();
            $pdo->exec("CREATE TABLE IF NOT EXISTS `family_living_heirlooms` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` VARCHAR(64) NOT NULL,
                `fam_code` VARCHAR(64) NOT NULL,
                `ancestor_name` VARCHAR(255) NOT NULL,
                `era_year` VARCHAR(50) NULL,
                `image_url` TEXT NOT NULL,
                `story_note` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_fam` (`fam_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $stmt = $pdo->prepare("INSERT INTO family_living_heirlooms (user_id, fam_code, ancestor_name, era_year, image_url, story_note) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$this->userId, $this->famCode, $ancestorName, $eraYear, $imageUrl, $storyNote]);

            echo json_encode([
                'success' => true,
                'id'      => (int)$pdo->lastInsertId(),
                'message' => "Living Heirloom for {$ancestorName} saved to family archive.",
            ]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        return;
    }

    /**
     * API: Generate a Re-Emotion portrait keyframe (Warm Smile, Joyful Laugh, etc.).
     * Route: POST /api/heirloom/re-emotion
     */
    public function reEmotion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            if (class_exists(Limiter::class)) {
                Limiter::limit($this->userId, 'heirloom_re_emotion');
            }

            $imageSource = (string)($json['image_source'] ?? '');
            $emotion = (string)($json['emotion'] ?? 'warm_smile');

            $service = new FamilyPortraitSynthesisService();
            $bytes = $service->resolveSafeImageBytes($imageSource);
            $result = $service->generateReEmotionPortrait($bytes, $emotion);

            echo json_encode($result);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Predict how someone will look in +10, +20, +30, +40 years.
     * Route: POST /api/heirloom/age-progression
     */
    public function ageProgression(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            if (class_exists(Limiter::class)) {
                Limiter::limit($this->userId, 'heirloom_age_prog');
            }

            $imageSource = (string)($json['image_source'] ?? '');
            $currentAge = (int)($json['current_age'] ?? 30);
            $yearsDelta = (int)($json['years_delta'] ?? 20);
            $gender = trim(strip_tags((string)($json['gender'] ?? 'Unspecified')));

            $service = new FamilyPortraitSynthesisService();
            $bytes = $service->resolveSafeImageBytes($imageSource);
            $result = $service->generateAgeProgression($bytes, $currentAge, $yearsDelta, $gender);

            echo json_encode($result);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: List photographed and unphotographed family tree members for reverse computation.
     * Route: GET /api/heirloom/tree-ancestors
     */
    public function treeAncestors(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $pdo = Db::connect2();
            $engine = new AncestralReverseEngine();
            $inventory = $engine->getTreePortraitInventory($pdo, $this->famCode);

            echo json_encode([
                'success'                  => true,
                'photographed'             => $inventory['photographed'],
                'unphotographed_ancestors' => $inventory['unphotographed_ancestors'],
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Run Leave-One-Out calibration across photographed family tree members.
     * Route: POST /api/heirloom/calibrate-tree
     */
    public function calibrateTree(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            $pdo = Db::connect2();
            $engine = new AncestralReverseEngine();
            $calibration = $engine->calibrateFamilyTree($pdo, $this->famCode);

            echo json_encode([
                'success'     => true,
                'calibration' => $calibration,
            ]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Synthesise an unphotographed ancestor's portrait via calibrated reverse computation.
     * Route: POST /api/heirloom/reverse-ancestor
     */
    public function reverseAncestor(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            if (class_exists(Limiter::class)) {
                Limiter::limit($this->userId, 'heirloom_reverse_ancestor');
            }

            $targetNodeId = (int)($json['target_node_id'] ?? 0);
            if ($targetNodeId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please select a valid ancestor from your Family Tree.']);
                return;
            }

            $pdo = Db::connect2();
            $engine = new AncestralReverseEngine();
            $result = $engine->predictAncestorPortrait($pdo, $this->famCode, $targetNodeId);

            echo json_encode($result);
        } catch (InvalidArgumentException $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Publish a Living Heirloom, Age Progression, or Ancestral Reconstruction portrait
     * directly to the Family Post Feed.
     * Route: POST /api/heirloom/publish-to-feed
     */
    public function publishToFeed(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            self::validateCsrf(is_array($json) ? $json : []);

            if (class_exists(Limiter::class)) {
                Limiter::limit($this->userId, 'heirloom_publish_feed');
            }

            $imageSource = (string)($json['image_source'] ?? '');
            $caption = trim(strip_tags((string)($json['caption'] ?? '')));
            $modeLabel = trim(strip_tags((string)($json['mode_label'] ?? 'Living Heirloom Portrait')));

            if ($imageSource === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'A portrait image is required to post to the feed.']);
                return;
            }

            $service = new FamilyPortraitSynthesisService();
            $bytes = $service->resolveSafeImageBytes($imageSource);
            $jpegBytes = $service->normaliseToJpeg($bytes);

            $postDir = dirname(__DIR__, 3) . '/resources/images/post';
            if (!is_dir($postDir) && !@mkdir($postDir, 0755, true) && !is_dir($postDir)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Post image storage is unavailable right now.']);
                return;
            }

            $fileName = 'heirloom_' . bin2hex(random_bytes(10)) . '.jpg';
            if (@file_put_contents($postDir . '/' . $fileName, $jpegBytes) === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Unable to save portrait image for feed post.']);
                return;
            }

            $pdo = Db::connect2();
            $uStmt = $pdo->prepare(
                'SELECT firstName, lastName, (SELECT img FROM profilePics WHERE id = personal.id LIMIT 1) AS profile_img
                 FROM personal WHERE id = ? LIMIT 1'
            );
            $uStmt->execute([$this->userId]);
            $author = $uStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            $fullName = trim((string)($author['firstName'] ?? '') . ' ' . (string)($author['lastName'] ?? '')) ?: 'Family Member';
            $profileImg = (string)($author['profile_img'] ?? 'default.png');

            $postMessage = $caption !== '' ? $caption : "🏛️ {$modeLabel} — shared from the Heritage & Portrait Studio.";
            $postTime = function_exists('milliSeconds') ? (string)milliSeconds() : date('Y-m-d H:i:s');

            $ins = $pdo->prepare(
                'INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $ins->execute([$this->userId, $fullName, $profileImg, $postMessage, $this->famCode, $postTime, 'published', $fileName]);

            echo json_encode([
                'success' => true,
                'post_no' => (int)$pdo->lastInsertId(),
                'message' => 'Published to your Family Feed!',
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            $code = ($e instanceof \Src\Exceptions\UnauthorisedException) ? 403 : 500;
            http_response_code($code);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
