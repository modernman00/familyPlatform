<?php
declare(strict_types=1);

// RFC-0005: settle family-code votes whose 7-day window has closed (applies or closes them and notifies members).
// Cron (hourly): 0 * * * * php /path/to/scripts/close_family_code_votes.php
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once dirname(__DIR__) . '/app/config/init.php';

try {
    $settled = (new \App\services\FamilyCodeRenameService(\Src\Db::connect2()))->expireDue();
    echo json_encode(['event' => 'code_rename.sweep', 'settled' => $settled]) . PHP_EOL;
} catch (\Throwable $e) {
    error_log('[close_family_code_votes] ' . $e->getMessage());
    exit(1);
}
