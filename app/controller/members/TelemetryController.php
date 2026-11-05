<?php

declare(strict_types=1);

namespace App\controller\members;

use Src\Db;
use PDO;
use PDOException;

/**
 * TelemetryController
 *
 * Handles privacy-preserving, GDPR-compliant client telemetry ingest (N1).
 * Ingests rage clicks, dead clicks, and funnel step drops with strict PII stripping,
 * IP anonymization, and rate-limiting.
 */
class TelemetryController
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Ingest telemetry event via POST /api/telemetry/event
     *
     * @return void
     */
    public function recordEvent(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Read raw JSON or form-data
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput) && !empty($_POST)) {
            $rawInput = (string)json_encode($_POST);
        }

        if (empty($rawInput)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Empty telemetry payload']);
            return;
        }

        $data = json_decode((string)$rawInput, true);
        if (!is_array($data)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid event payload']);
            return;
        }

        $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $result = \Src\TelemetryIngestService::processEvent($this->db, $data, $clientIp);

        if ($result['status'] === 'error') {
            http_response_code(422);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request (JSON API output, not HTML; json_encode is safe)
            echo json_encode($result);
            return;
        }

        http_response_code(200);
        // nosemgrep: php.lang.security.injection.echoed-request.echoed-request (JSON API output, not HTML; json_encode is safe)
        echo json_encode($result);
    }

    /**
     * Expose aggregated telemetry & RUM friction stats via GET /api/telemetry/stats
     *
     * @return void
     */
    public function getStats(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $stats = \Src\TelemetryMetricsService::getSummaryStats($this->db);
        $stats['rum_vitals'] = $this->computeRumVitalsSummary();
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }

    /**
     * Computes bounded p75 / average Core Web Vitals (INP, LCP, CLS, Long Tasks)
     * and Mobile Viewport Profile distribution from the latest 500 RUM beacons.
     *
     * @return array{
     *     samples_count: int,
     *     lcp_p75_ms: int,
     *     lcp_avg_ms: int,
     *     inp_p75_ms: int,
     *     inp_avg_ms: int,
     *     cls_avg: float,
     *     longtasks_total: int,
     *     max_longtask_ms: int,
     *     viewport_profiles: array<string, int>
     * }
     */
    private function computeRumVitalsSummary(): array
    {
        $default = [
            'samples_count' => 0,
            'lcp_p75_ms' => 0,
            'lcp_avg_ms' => 0,
            'inp_p75_ms' => 0,
            'inp_avg_ms' => 0,
            'cls_avg' => 0.0,
            'longtasks_total' => 0,
            'max_longtask_ms' => 0,
            'viewport_profiles' => [
                'compact_375' => 0,
                'standard_390' => 0,
                'large_412' => 0,
                'tablet' => 0,
                'desktop' => 0,
            ],
        ];

        try {
            $stmt = $this->db->prepare(
                'SELECT viewport_w, metadata_json FROM telemetry_events WHERE event_name = ? ORDER BY id DESC LIMIT 500'
            );
            $stmt->execute(['rum_vitals']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return $default;
            }

            $lcpValues = [];
            $inpValues = [];
            $clsSum = 0.0;
            $clsCount = 0;
            $longtasksTotal = 0;
            $maxLongtaskMs = 0;
            $profiles = $default['viewport_profiles'];

            foreach ($rows as $row) {
                $vw = (int)($row['viewport_w'] ?? 0);
                $meta = json_decode((string)($row['metadata_json'] ?? '{}'), true);
                if (!is_array($meta)) {
                    $meta = [];
                }

                $lcp = isset($meta['lcp_ms']) ? (int)$meta['lcp_ms'] : 0;
                if ($lcp > 0) {
                    $lcpValues[] = $lcp;
                }

                $inp = isset($meta['inp_ms']) ? (int)$meta['inp_ms'] : 0;
                if ($inp > 0) {
                    $inpValues[] = $inp;
                }

                if (isset($meta['cls_score']) && is_numeric($meta['cls_score'])) {
                    $clsSum += (float)$meta['cls_score'];
                    $clsCount++;
                }

                $ltCount = isset($meta['longtask_count']) ? (int)$meta['longtask_count'] : 0;
                $longtasksTotal += max(0, $ltCount);

                $ltMax = isset($meta['max_longtask_ms']) ? (int)$meta['max_longtask_ms'] : 0;
                if ($ltMax > $maxLongtaskMs) {
                    $maxLongtaskMs = $ltMax;
                }

                $profileKey = isset($meta['viewport_profile']) && is_string($meta['viewport_profile'])
                    ? $meta['viewport_profile']
                    : ($vw > 0 && $vw <= 380 ? 'compact_375' : ($vw <= 400 ? 'standard_390' : ($vw <= 480 ? 'large_412' : ($vw <= 1024 ? 'tablet' : 'desktop'))));

                if (isset($profiles[$profileKey])) {
                    $profiles[$profileKey]++;
                } else {
                    $profiles['desktop']++;
                }
            }

            return [
                'samples_count' => count($rows),
                'lcp_p75_ms' => $this->calculatePercentile($lcpValues, 0.75),
                'lcp_avg_ms' => !empty($lcpValues) ? (int)round(array_sum($lcpValues) / count($lcpValues)) : 0,
                'inp_p75_ms' => $this->calculatePercentile($inpValues, 0.75),
                'inp_avg_ms' => !empty($inpValues) ? (int)round(array_sum($inpValues) / count($inpValues)) : 0,
                'cls_avg' => $clsCount > 0 ? round($clsSum / $clsCount, 4) : 0.0,
                'longtasks_total' => $longtasksTotal,
                'max_longtask_ms' => $maxLongtaskMs,
                'viewport_profiles' => $profiles,
            ];
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Calculates a given percentile (e.g. 0.75 for p75) from an array of integer milliseconds.
     *
     * @param array<int, int> $values
     * @param float $percentile
     * @return int
     */
    private function calculatePercentile(array $values, float $percentile): int
    {
        if (empty($values)) {
            return 0;
        }
        sort($values, SORT_NUMERIC);
        $index = (int)ceil($percentile * count($values)) - 1;
        $index = max(0, min($index, count($values) - 1));
        return (int)$values[$index];
    }
}
