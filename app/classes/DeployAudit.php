<?php
declare(strict_types=1);

namespace App\Classes;

use PDO;

/**
 * Simple audit logger for deployments.
 *
 * If a PDO instance is provided via `init()`, the record will be persisted to the
 * `deployment_audit` table. If no PDO is set, the logger falls back to a file‑based
 * log located at `storage/deploy_audit.log` (relative to the project root).
 */
final class DeployAudit
{
    /** @var PDO|null */
    private static ?PDO $pdo = null;

    /**
     * Initialise the logger with a PDO connection.
     */
    public static function init(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Record a deployment event.
     *
     * @param string $deployer   Username performing the deployment (e.g. `whoami`).
     * @param string $gitSha     Git commit SHA being deployed.
     * @param string $appName    Application identifier (matches keys in deployments.yml).
     * @param string $status     Either `success` or `failed`.
     */
    public static function record(string $deployer, string $gitSha, string $appName, string $status): void
    {
        // If we have a DB connection, write to the table.
        if (self::$pdo !== null) {
            $stmt = self::$pdo->prepare(
                'INSERT INTO deployment_audit (deployer, git_sha, app_name, status, ts) VALUES (:deployer, :git_sha, :app_name, :status, NOW())'
            );
            $stmt->execute([
                ':deployer' => $deployer,
                ':git_sha'  => $gitSha,
                ':app_name' => $appName,
                ':status'   => $status,
            ]);
            return;
        }

        // File‑based fallback – one JSON line per record.
        $logEntry = [
            'timestamp' => (new \DateTimeImmutable('now'))->format('c'),
            'deployer'  => $deployer,
            'git_sha'   => $gitSha,
            'app_name'  => $appName,
            'status'    => $status,
        ];
        $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES) . "\n";
        $logPath = __DIR__ . '/../../storage/deploy_audit.log';
        // Ensure the storage directory exists.
        if (!is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0750, true);
        }
        file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
    }
}
?>
