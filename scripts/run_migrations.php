<?php

declare(strict_types=1);

/**
 * FamilyPlatform Automated Database Migration Runner
 * 
 * Manages idempotent execution of database migrations with an automated ledger (`schema_migrations`).
 * Guarantees every migration runs exactly once across environments (local, staging, production).
 *
 * Usage:
 *   php scripts/run_migrations.php          # Run all pending migrations
 *   php scripts/run_migrations.php --status # View migration ledger status
 *   php scripts/run_migrations.php --dry-run # Preview pending migrations
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden: CLI execution only.\n");
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Db;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsDir;
    private string $scriptsDir;

    public function __construct(PDO $pdo, string $migrationsDir, string $scriptsDir)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->migrationsDir = rtrim($migrationsDir, '/');
        $this->scriptsDir = rtrim($scriptsDir, '/');
        $this->ensureLedgerTable();
    }

    /**
     * Ensure the `schema_migrations` tracking table exists.
     */
    private function ensureLedgerTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT UNSIGNED NOT NULL DEFAULT 1,
            `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sql);
    }

    /**
     * Get all previously applied migration names.
     *
     * @return array<string, string> Key: migration name, Value: executed_at
     */
    public function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration, executed_at FROM schema_migrations ORDER BY id ASC");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[(string)$row['migration']] = (string)$row['executed_at'];
        }
        return $results;
    }

    /**
     * Get the next batch number.
     */
    private function getNextBatch(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM schema_migrations");
        $max = (int) $stmt->fetchColumn();
        return $max + 1;
    }

    /**
     * Discover all candidate migration files in chronological order.
     *
     * @return array<int, array{name: string, path: string, type: string}>
     */
    public function discoverMigrations(): array
    {
        $candidates = [];

        // 1. Scan migrations/ directory
        if (is_dir($this->migrationsDir)) {
            $files = scandir($this->migrationsDir) ?: [];
            foreach ($files as $file) {
                // Ignore snapshots, dotfiles, and non-migration files
                if (str_starts_with($file, '.') || str_contains($file, 'snapshot') || !preg_match('/^\d{4}_\d{2}_\d{2}_.*\.(sql|php)$/i', $file)) {
                    continue;
                }
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $candidates[$file] = [
                    'name' => $file,
                    'path' => $this->migrationsDir . '/' . $file,
                    'type' => $ext,
                ];
            }
        }

        // 2. Scan dedicated migration scripts in scripts/
        $scriptMigrations = [
            '2026_10_18_001_migrate_journals_announcements.php' => $this->scriptsDir . '/migrate_journals_announcements.php',
            '2026_10_19_001_migrate_member_privacy_public.php'  => $this->scriptsDir . '/migrate_member_privacy_public.php',
        ];

        foreach ($scriptMigrations as $aliasName => $scriptPath) {
            if (file_exists($scriptPath)) {
                $candidates[$aliasName] = [
                    'name' => $aliasName,
                    'path' => $scriptPath,
                    'type' => 'php',
                ];
            }
        }

        ksort($candidates, SORT_NATURAL);
        return array_values($candidates);
    }

    /**
     * Print migration status.
     */
    public function showStatus(): void
    {
        $applied = $this->getAppliedMigrations();
        $discovered = $this->discoverMigrations();

        echo "\n=================================================================\n";
        echo "📊  FamilyPlatform Database Migration Ledger Status\n";
        echo "=================================================================\n";
        echo sprintf("%-50s | %-12s | %s\n", "Migration", "Status", "Applied At");
        echo str_repeat("-", 85) . "\n";

        $pendingCount = 0;
        foreach ($discovered as $item) {
            $name = $item['name'];
            if (isset($applied[$name])) {
                echo sprintf("%-50s | \033[32mAPPLIED\033[0m      | %s\n", $name, $applied[$name]);
            } else {
                echo sprintf("%-50s | \033[33mPENDING\033[0m      | -\n", $name);
                $pendingCount++;
            }
        }

        echo "=================================================================\n";
        echo sprintf("Total: %d migration(s) | Applied: %d | Pending: %d\n\n", count($discovered), count($applied), $pendingCount);
    }

    /**
     * Run all pending migrations.
     *
     * @param bool $dryRun
     * @return int Number of migrations executed
     */
    public function runPending(bool $dryRun = false): int
    {
        $applied = $this->getAppliedMigrations();
        $discovered = $this->discoverMigrations();
        $batch = $this->getNextBatch();

        $pending = [];
        foreach ($discovered as $item) {
            if (!isset($applied[$item['name']])) {
                $pending[] = $item;
            }
        }

        if (empty($pending)) {
            echo "✨ Database is already up to date (0 pending migrations).\n";
            return 0;
        }

        echo "\n=================================================================\n";
        echo sprintf("🚀 Executing %d Pending Migration(s) [Batch %d]%s\n", count($pending), $batch, $dryRun ? " (DRY-RUN)" : "");
        echo "=================================================================\n";

        $insertLedger = $this->pdo->prepare("INSERT INTO schema_migrations (migration, batch) VALUES (?, ?)");

        $executed = 0;
        foreach ($pending as $item) {
            $name = $item['name'];
            $path = $item['path'];
            $type = $item['type'];

            echo sprintf("⏳ Applying: %s ... ", $name);

            if ($dryRun) {
                echo "\033[33m[SKIPPED - DRY-RUN]\033[0m\n";
                $executed++;
                continue;
            }

            try {
                if ($type === 'sql') {
                    $this->executeSqlFile($path);
                } elseif ($type === 'php') {
                    $this->executePhpFile($path);
                }

                // Record in ledger
                $insertLedger->execute([$name, $batch]);
                echo "\033[32m[DONE]\033[0m\n";
                $executed++;
            } catch (Throwable $e) {
                echo "\033[31m[FAILED]\033[0m\n";
                fwrite(STDERR, sprintf("\n❌ Migration Error in '%s': %s\n", $name, $e->getMessage()));
                throw $e;
            }
        }

        echo "=================================================================\n";
        echo sprintf("🏆 Successfully applied %d migration(s) in Batch %d.\n\n", $executed, $batch);
        return $executed;
    }

    /**
     * Execute a SQL migration file.
     */
    private function executeSqlFile(string $path): void
    {
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return;
        }

        // Strip MySQL dump headers and comment lines
        $lines = explode("\n", $content);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        $cleanSql = trim($cleanSql);
        if ($cleanSql === '') {
            return;
        }

        $statements = $this->splitSqlStatements($cleanSql);

        foreach ($statements as $stmt) {
            // Auto-convert CREATE TABLE to CREATE TABLE IF NOT EXISTS for resilience
            $normalizedStmt = preg_replace('/^CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/i', 'CREATE TABLE IF NOT EXISTS ', $stmt);
            // Auto-convert ADD COLUMN IF NOT EXISTS to ADD COLUMN for MySQL compatibility
            $normalizedStmt = preg_replace('/ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\s+/i', 'ADD COLUMN ', (string) $normalizedStmt);
            if ($normalizedStmt === null || trim($normalizedStmt) === '') {
                continue;
            }

            try {
                $this->pdo->exec($normalizedStmt);
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                $code = (string) $e->getCode();
                // Safe to ignore if table, column, or key already exists
                if ($code === '42S01' 
                    || $code === '42S21' 
                    || str_contains($msg, 'Duplicate column') 
                    || str_contains($msg, 'already exists') 
                    || str_contains($msg, 'Duplicate key name')
                    || str_contains($msg, 'Duplicate entry')
                ) {
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * Split SQL string into individual statements respecting quotes and escapes.
     *
     * @return array<int, string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $prevChar = $i > 0 ? $sql[$i - 1] : '';

            // Handle quote toggles if not escaped
            if ($char === "'" && !$inDoubleQuote && !$inBacktick && $prevChar !== '\\') {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick && $prevChar !== '\\') {
                $inDoubleQuote = !$inDoubleQuote;
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote && $prevChar !== '\\') {
                $inBacktick = !$inBacktick;
            }

            // Check statement delimiter
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    /**
     * Execute a PHP migration script in an isolated process.
     */
    private function executePhpFile(string $path): void
    {
        $phpBin = PHP_BINARY ?: 'php';
        $cmd = escapeshellcmd($phpBin) . ' ' . escapeshellarg($path);
        
        $output = [];
        $exitCode = 0;
        exec($cmd . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $errDetail = implode("\n", $output);
            throw new RuntimeException("PHP migration script failed with exit code {$exitCode}:\n{$errDetail}");
        }
    }
}

try {
    $pdo = Db::connect2();
    $migrationsDir = dirname(__DIR__) . '/migrations';
    $scriptsDir = dirname(__DIR__) . '/scripts';

    $runner = new MigrationRunner($pdo, $migrationsDir, $scriptsDir);

    $args = array_slice($argv, 1);
    if (in_array('--status', $args, true)) {
        $runner->showStatus();
        exit(0);
    }

    $isDryRun = in_array('--dry-run', $args, true);
    $runner->runPending($isDryRun);
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "🛑 Fatal Migration Failure: " . $e->getMessage() . "\n");
    exit(1);
}
