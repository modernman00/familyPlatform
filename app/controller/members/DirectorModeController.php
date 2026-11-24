<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\Cinematic\DirectorApi;
use Src\Exceptions\ValidationException;
use Src\Limiter;
use Src\Utility;
use Throwable;

/**
 * DirectorModeController (FamilyPlatform)
 *
 * Member-only Director Mode: plan and render multi-shot family heirloom & reunion films
 * with @asset references at £0 recurring cost.
 */
final class DirectorModeController extends BaseController
{
    public const FAMILY_ASSETS = ['elders', 'parents', 'children', 'grandparents', 'homestead', 'reunion', 'heirloom', 'ancestors'];

    public function __construct()
    {
        // Auth and CSRF are enforced explicitly per action so JSON routes always return structured 403s.
    }

    public function page(): void
    {
        self::rehydrateSession();

        $userId = trim((string) ($_SESSION['id'] ?? ''));
        if ($userId === '') {
            if (!headers_sent()) {
                header('Location: /login');
            }
            if (PHP_SAPI !== 'cli' && empty($GLOBALS['__TEST_NO_EXIT__'])) {
                exit;
            }
            return;
        }

        Utility::view('member/directorMode', [
            'assets' => self::FAMILY_ASSETS,
        ]);
    }

    public function plan(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            self::rehydrateSession();

            $sessionToken = (string) ($_SESSION['token'] ?? '');
            $provided = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if ($sessionToken === '' || $provided === '' || !hash_equals($sessionToken, $provided)) {
                $this->respond(403, ['ok' => false, 'message' => 'Security token invalid or expired. Please refresh the page.']);
                return;
            }

            $userId = trim((string) ($_SESSION['id'] ?? ''));
            if ($userId === '') {
                $this->respond(403, ['ok' => false, 'message' => 'Please sign in to plan a family film.']);
                return;
            }

            Limiter::limit('family_director_mode_plan', 'post');

            $data = json_decode((string) (file_get_contents('php://input') ?: ''), true);
            if (!is_array($data)) {
                $this->respond(400, ['ok' => false, 'message' => 'Invalid request.']);
                return;
            }

            $payload = [
                'shots'  => is_array($data['shots'] ?? null) ? $data['shots'] : [],
                'beats'  => [],
                'style'  => (string) ($data['style'] ?? 'storytelling'),
                'assets' => self::FAMILY_ASSETS,
                'lock'   => ['enabled' => true, 'characterId' => 'elders', 'worldId' => 'homestead'],
            ];

            $this->respond(200, DirectorApi::plan($payload));
        } catch (ValidationException $e) {
            $this->respond(422, ['ok' => false, 'message' => $e->getMessage()]);
        } catch (Throwable) {
            $this->respond(500, ['ok' => false, 'message' => 'The plan could not be created. Please try again.']);
        }
    }

    public function render(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            self::rehydrateSession();

            $sessionToken = (string) ($_SESSION['token'] ?? '');
            $provided = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if ($sessionToken === '' || $provided === '' || !hash_equals($sessionToken, $provided)) {
                $this->respond(403, ['ok' => false, 'message' => 'Security token invalid or expired. Please refresh the page.']);
                return;
            }

            $userId = trim((string) ($_SESSION['id'] ?? ''));
            if ($userId === '' || preg_match('/^[A-Za-z0-9_-]{1,64}$/', $userId) !== 1) {
                $this->respond(403, ['ok' => false, 'message' => 'Please sign in to render a family film.']);
                return;
            }

            Limiter::limit('family_director_mode_render', 'post');

            $data = json_decode((string) (file_get_contents('php://input') ?: ''), true);
            if (!is_array($data)) {
                $this->respond(400, ['ok' => false, 'message' => 'Invalid request.']);
                return;
            }

            $rootDir = dirname(__DIR__, 3);
            $outDir = $rootDir . '/public/uploads/director_mode/' . $userId;
            if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
                $this->respond(500, ['ok' => false, 'message' => 'Render storage could not be prepared.']);
                return;
            }

            $baseName = 'heirloom_' . bin2hex(random_bytes(6));
            $payload = [
                'shots'  => is_array($data['shots'] ?? null) ? $data['shots'] : [],
                'beats'  => [],
                'style'  => (string) ($data['style'] ?? 'storytelling'),
                'assets' => self::FAMILY_ASSETS,
                'lock'   => ['enabled' => true, 'characterId' => 'elders', 'worldId' => 'homestead'],
            ];

            $res = DirectorApi::render($payload, $outDir, $baseName);
            $publicPrefix = '/public/uploads/director_mode/' . rawurlencode($userId) . '/' . $baseName;

            $this->respond(200, [
                'ok'               => true,
                'costGbp'          => 0.0,
                'totalDurationSec' => $res['totalDurationSec'],
                'timeline'         => $res['timeline'],
                'videoUrl'         => $res['render']['videoPath'] !== null ? $publicPrefix . '.mp4' : null,
                'srtUrl'           => $publicPrefix . '.srt',
                'renderStatus'     => $res['render']['status'],
            ]);
        } catch (ValidationException $e) {
            $this->respond(422, ['ok' => false, 'message' => $e->getMessage()]);
        } catch (Throwable) {
            $this->respond(500, ['ok' => false, 'message' => 'The film could not be rendered. Please try again.']);
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    private function respond(int $status, array $body): void
    {
        if (!headers_sent()) {
            http_response_code($status);
        }
        // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        echo json_encode($body + ['status' => $status]);
        if (PHP_SAPI !== 'cli' && empty($GLOBALS['__TEST_NO_EXIT__'])) {
            exit;
        }
    }
}
