<?php
declare(strict_types=1);

namespace App\controller;

use App\services\SocialMediaAgentService;

class CronController
{
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
