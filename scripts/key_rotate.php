<?php

/**
 * Key Rotation CLI
 * 
 * Safely rotates the JWT_KEY in the .env file.
 * The old key is moved to JWT_KEY_PREVIOUS to support Graceful Key Overlapping,
 * ensuring users do not get suddenly logged out.
 */

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die("Error: .env file not found at $envFile\n");
}

$envContent = file_get_contents($envFile);

// Extract the current active JWT_KEY
preg_match('/^JWT_KEY\s*=\s*["\']?(.*?)["\']?$/m', $envContent, $matches);

if (empty($matches[1])) {
    die("Error: Could not find active JWT_KEY in .env. Is it set?\n");
}

$oldKey = $matches[1];

// Generate a cryptographically secure 128-character hex string (64 bytes)
$newKey = bin2hex(random_bytes(64));

echo "🔐 Rotating JWT Key...\n";
echo "Old Key: " . substr($oldKey, 0, 8) . "...\n";
echo "New Key: " . substr($newKey, 0, 8) . "...\n\n";

// Ensure JWT_KEY_PREVIOUS is updated or inserted
if (preg_match('/^JWT_KEY_PREVIOUS\s*=/m', $envContent)) {
    // It exists, replace it
    $envContent = preg_replace('/^JWT_KEY_PREVIOUS\s*=.*$/m', 'JWT_KEY_PREVIOUS="' . $oldKey . '"', $envContent);
} else {
    // It doesn't exist, insert it just before JWT_KEY
    $envContent = preg_replace(
        '/^JWT_KEY\s*=.*$/m',
        'JWT_KEY_PREVIOUS="' . $oldKey . "\"\n" . '$0',
        $envContent
    );
}

// Now replace the primary JWT_KEY
$envContent = preg_replace('/^JWT_KEY\s*=.*$/m', 'JWT_KEY="' . $newKey . '"', $envContent);

// Save the updated content
if (file_put_contents($envFile, $envContent) === false) {
    die("❌ Error: Failed to write to .env file. Check permissions.\n");
}

// ISO 27001 (A.12.4): Log Key Rotation to Immutable Audit Store
require_once __DIR__ . '/../vendor/autoload.php';
// Load environment variables for the logger
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

\Src\AuditLogger::log('key_rotation', [
    'message' => 'JWT Cryptographic Key rotated successfully',
    'status' => 'success'
]);

echo "✅ JWT Key successfully rotated.\n";
echo "🔄 Previous key has been archived to JWT_KEY_PREVIOUS for zero-downtime graceful token overlapping.\n";
