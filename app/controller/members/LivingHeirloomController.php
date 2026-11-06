<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\Db;
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

        view('member.livingHeirloom', compact('data', 'userData', 'heirlooms'));
    }

    /**
     * API: Save an animated heirloom portrait to the family archives.
     * Route: POST /api/heirloom/save-portrait
     */
    public function savePortrait(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        return;
    }
}
