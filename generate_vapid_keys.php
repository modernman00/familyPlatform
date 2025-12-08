<?php

/**
 * VAPID Key Generator for Web Push Notifications
 * 
 * This script generates a pair of VAPID keys (public and private)
 * that you need to add to your .env file for push notifications.
 * 
 * Run this script once: php generate_vapid_keys.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

echo "Generating VAPID keys...\n\n";

try {
    // Generate VAPID keys
    $keys = VAPID::createVapidKeys();

    echo "✅ VAPID keys generated successfully!\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Add these to your .env file:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "VAPID_PUBLIC_KEY=\"{$keys['publicKey']}\"\n";
    echo "VAPID_PRIVATE_KEY=\"{$keys['privateKey']}\"\n\n";

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "⚠️  IMPORTANT:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "1. Copy the lines above to your .env file\n";
    echo "2. Keep the private key SECRET - never commit it to git!\n";
    echo "3. The public key will be used in your JavaScript code\n";
    echo "4. After adding to .env, restart your web server\n\n";

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "For your JavaScript (Service Worker):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "const applicationServerKey = '{$keys['publicKey']}';\n\n";

} catch (Exception $e) {
    echo "❌ Error generating VAPID keys: " . $e->getMessage() . "\n";
    exit(1);
}
