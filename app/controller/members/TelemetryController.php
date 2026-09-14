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
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }
}
