<?php

declare(strict_types=1);

/**
 * CLI Autonomous Blog Generation Runner for FamilyPlatform.
 *
 * Usage:
 *   php scripts/generate_blog.php
 *   php scripts/generate_blog.php --topic="Preserving Multi-Generational Oral Histories"
 */

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "👨‍👩‍👧‍👦 FAMILYPLATFORM AUTONOMOUS BLOG BOT (KINSHIP & HEIRLOOMS)\n";
echo "=================================================================\n";

$options = getopt('', ['topic:']);
$customTopic = !empty($options['topic']) ? (string) $options['topic'] : null;

try {
    $bot = new \App\services\AutonomousBlogService();

    echo "🔄 Generating and validating UK family kinship guide...\n";
    $result = $bot->generateAndPublish($customTopic);

    if (($result['status'] ?? '') === 'success') {
        echo "\n✅ ARTICLE AUTONOMOUSLY GENERATED & PUBLISHED!\n";
        echo "   • Article ID  : " . ($result['blog_id'] ?? 'N/A') . "\n";
        echo "   • Title       : " . ($result['title'] ?? 'N/A') . "\n";
        echo "   • Category    : " . ($result['category'] ?? 'N/A') . "\n";
        echo "   • Word Count  : " . ($result['word_count'] ?? 0) . " words\n";
        echo "   • URL         : " . (rtrim(getenv('APP_URL') ?: 'https://familyplatform.test', '/')) . "/blog/" . ($result['blog_id'] ?? '') . "\n\n";
        exit(0);
    } else {
        echo "\n❌ Generation Failed: " . ($result['reason'] ?? 'Unknown error') . "\n\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
