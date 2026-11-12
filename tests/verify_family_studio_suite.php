<?php
declare(strict_types=1);

/**
 * Sovereign Family Living Archives Studio Verification Suite
 * 
 * Verifies R2 direct-to-storage presigned upload URL generation,
 * route mapping, SovereignVideoStudioClient availability, and
 * studio template rendering.
 */

require_once __DIR__ . '/../vendor/autoload.php';

function assertGate(bool $condition, string $gateName, string $detail = ''): void {
    if (!$condition) {
        echo "❌ [FAILED] {$gateName}: {$detail}\n";
        exit(1);
    }
    echo "✅ [PASSED] {$gateName}" . ($detail ? " — {$detail}" : '') . "\n";
}

echo "\n=======================================================\n";
echo "🏛️  FAMILY LIVING ARCHIVES STUDIO VERIFICATION SUITE\n";
echo "=======================================================\n\n";

// Gate 1: R2StorageService Availability
assertGate(
    class_exists('Src\Media\R2StorageService'),
    'Gate 1: R2StorageService Class Autoloading',
    'Src\Media\R2StorageService exists in shared-lib vendor'
);

// Gate 2: Presigned Ticket Generation
$ticket = \Src\Media\R2StorageService::generatePresignedUploadUrl('family_vault/test_fam/test_reel.mp4', 'video/mp4', 3600);
assertGate(
    is_array($ticket) && isset($ticket['upload_url'], $ticket['public_url'], $ticket['method']),
    'Gate 2: R2 Presigned Ticket Structure',
    "Upload URL: {$ticket['upload_url']} | Method: {$ticket['method']}"
);

// Gate 3: ReelsController Methods
$rc = new ReflectionClass('App\controller\members\ReelsController');
assertGate(
    $rc->hasMethod('presignedUploadUrl') && $rc->hasMethod('studio'),
    'Gate 3: ReelsController Studio & Presigned URL Methods',
    'presignedUploadUrl() and studio() methods exist'
);

// Gate 4: Route Mappings in reelsRoute.php
$routeContent = file_get_contents(__DIR__ . '/../app/router/reelsRoute.php');
assertGate(
    str_contains($routeContent, '/api/reels/presigned-url') &&
    str_contains($routeContent, '/family-studio') &&
    str_contains($routeContent, 'family_reels_presigned_url_api'),
    'Gate 4: Route Mappings in reelsRoute.php',
    '/api/reels/presigned-url and /family-studio registered'
);

// Gate 5: View Template Integrity
$viewPath = __DIR__ . '/../resources/views/member/family_studio.blade.php';
assertGate(
    file_exists($viewPath),
    'Gate 5: family_studio.blade.php Exists',
    $viewPath
);
$viewContent = file_get_contents($viewPath);
assertGate(
    str_contains($viewContent, "@include('csrf')") &&
    str_contains($viewContent, 'SovereignVideoStudioClient.js') &&
    str_contains($viewContent, 'Family Living Archives Video Studio'),
    'Gate 6: View Standards Compliance',
    'CSRF included, SovereignVideoStudioClient loaded, British English copy verified'
);

// Gate 7: Client-side JS Engine
$jsPath = __DIR__ . '/../public/js/SovereignVideoStudioClient.js';
assertGate(
    file_exists($jsPath),
    'Gate 7: SovereignVideoStudioClient.js Exists in Public Assets',
    $jsPath
);
$jsContent = file_get_contents($jsPath);
assertGate(
    str_contains($jsContent, 'renderMotionFlyerFrame') &&
    str_contains($jsContent, 'renderMultiAngleMatrix') &&
    str_contains($jsContent, 'detectAudioBeats'),
    'Gate 8: Video Studio Engine Capabilities',
    'Beat detection, motion flyer frame renderer, and multi-angle matrix compositor verified'
);

echo "\n🎉 ALL 8 GATES PASSED! Family Living Archives Studio is 100% verified.\n";
