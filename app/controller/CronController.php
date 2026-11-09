<?php
declare(strict_types=1);

namespace App\controller;

use App\services\AutonomousBlogService;
use App\services\SocialMediaAgentService;
use Src\Db;

class CronController
{
    /**
     * Endpoint to trigger autonomous family blog generation.
     * Enforces twice-a-week cadence, non-blocking process lock, and CRON_SECRET auth.
     */
    public function blog(): void
    {
        header('Content-Type: application/json');

        $providedSecret = (string) ($_REQUEST['secret'] ?? ($_SERVER['HTTP_X_CRON_SECRET'] ?? ''));
        $expectedSecret = (string) ($_ENV['CRON_SECRET'] ?? (getenv('CRON_SECRET') ?: ''));

        if (empty($expectedSecret) || !hash_equals($expectedSecret, $providedSecret)) {
            http_response_code(403);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Unauthorized. Invalid or missing secret.',
            ]);
            return;
        }

        // Check if explicit force override is requested
        $force = filter_var($_REQUEST['force'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Enforce the twice-a-week publication policy
        if (!$force) {
            $throttle = $this->checkPublicationThrottle();
            if ($throttle['allowed'] === false) {
                echo json_encode([
                    'status'                 => 'rate_limited',
                    'message'                => $throttle['reason'],
                    'next_eligible_at'       => $throttle['next_eligible_at'] ?? null,
                    'publications_this_week' => $throttle['publications_this_week'] ?? 0,
                    'policy'                 => 'Publication is restricted to twice a week (max 2 articles per rolling 7 days with a minimum 60-hour cooldown spacing).',
                ]);
                return;
            }
        }

        $lockPath = sys_get_temp_dir() . '/familyplatform-ai-cron.lock';
        $lockHandle = fopen($lockPath, 'c');
        if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }
            http_response_code(409);
            echo json_encode([
                'status'  => 'error',
                'message' => 'AI blog cron job is currently executing on another process.',
            ]);
            return;
        }

        try {
            $customTopic = !empty($_REQUEST['topic']) ? (string) $_REQUEST['topic'] : null;
            $bot = new AutonomousBlogService();
            $result = $bot->generateAndPublish($customTopic);

            http_response_code(200);
            echo json_encode([
                'status'              => 'success',
                'message'             => 'Family guide generated and published successfully.',
                'publication_cadence' => 'Twice-a-week policy active.',
                'data'                => $result,
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to run AI family blog bot: ' . $e->getMessage(),
            ]);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * Checks if blog publication is eligible under rolling twice-a-week rule.
     *
     * @return array{allowed: bool, reason: string, next_eligible_at?: string, publications_this_week?: int}
     */
    private function checkPublicationThrottle(): array
    {
        try {
            $pdo = Db::connect2();
            $sevenDaysAgo = date('Y-m-d H:i:s', time() - (7 * 86400));

            $stmt = $pdo->prepare('SELECT COUNT(*) AS total_this_week, MAX(created_at) AS last_published_at 
                FROM blogs 
                WHERE status = "published" AND created_at >= ?');
            $stmt->execute([$sevenDaysAgo]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            $totalThisWeek = (int) ($row['total_this_week'] ?? 0);
            $lastPublishedAt = $row['last_published_at'] ?? null;

            if ($totalThisWeek >= 2) {
                return [
                    'allowed'                => false,
                    'reason'                 => 'Publication limit reached: already published 2 articles in the last 7 days.',
                    'publications_this_week' => $totalThisWeek,
                ];
            }

            if (!empty($lastPublishedAt)) {
                $lastTime = strtotime((string) $lastPublishedAt);
                $cooldownSeconds = 60 * 3600; // 60 hours spacing
                $elapsedSeconds = time() - $lastTime;

                if ($elapsedSeconds < $cooldownSeconds) {
                    $remainingHours = (int) ceil(($cooldownSeconds - $elapsedSeconds) / 3600);
                    $nextEligible = date('Y-m-d H:i:s', $lastTime + $cooldownSeconds);

                    return [
                        'allowed'                => false,
                        'reason'                 => "Publication cadence cooldown active. Next eligible window opens in {$remainingHours} hour(s) ({$nextEligible}).",
                        'next_eligible_at'       => $nextEligible,
                        'publications_this_week' => $totalThisWeek,
                    ];
                }
            }

            return [
                'allowed'                => true,
                'reason'                 => 'Eligible for publication.',
                'publications_this_week' => $totalThisWeek,
            ];
        } catch (\Throwable $e) {
            error_log('[FamilyPlatform::checkPublicationThrottle] ' . $e->getMessage());

            return [
                'allowed' => true,
                'reason'  => 'Throttle check bypassed due to internal error.',
            ];
        }
    }

    /**
     * Endpoint for cron to trigger social media publishing
     * Designed to be called via CLI or a secured webhook.
     */
    public function publishSocial(): void
    {
        $queueFile = BASE_PATH . '/storage/social_queue.json';
        
        if (!file_exists($queueFile)) {
            echo json_encode(['status' => 'idle', 'message' => 'No jobs in queue.']);
            return;
        }

        $content = file_get_contents($queueFile);
        if (empty($content)) {
            echo json_encode(['status' => 'idle', 'message' => 'Queue is empty.']);
            return;
        }

        $queueData = json_decode($content, true);
        
        if (empty($queueData) || !is_array($queueData)) {
            echo json_encode(['status' => 'idle', 'message' => 'Queue is invalid or empty.']);
            return;
        }

        $job = array_shift($queueData); // Process one job per run to avoid timeouts

        // Save the remaining queue back immediately
        file_put_contents($queueFile, json_encode($queueData));

        try {
            $socialService = new SocialMediaAgentService();
            $result = $socialService->publish(
                $job['message'] ?? '',
                $job['image_url'] ?? null,
                $job['platforms'] ?? []
            );

            echo json_encode([
                'status' => 'processed',
                'job' => $job,
                'result' => $result
            ]);
        } catch (\Throwable $th) {
            // Put the job back at the end of the queue if it fails completely (optional, but let's log it for now)
            error_log("Social Cron Error: " . $th->getMessage());
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
}
