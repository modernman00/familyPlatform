<?php

declare(strict_types=1);

/**
 * Social Media Publisher - Cron Script
 * 
 * To be executed via CLI:
 * /usr/local/bin/php /path/to/familyPlatform/cron/social_publisher.php
 */

require __DIR__ . '/../app/config/init.php';

use App\controller\CronController;

echo "Starting Social Media Publisher...\n";

try {
    $controller = new CronController();
    $controller->publishSocial();
    echo "\nCompleted.\n";
} catch (\Throwable $th) {
    echo "Error: " . $th->getMessage() . "\n";
}
