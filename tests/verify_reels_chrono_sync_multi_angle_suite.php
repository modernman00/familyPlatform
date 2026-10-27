<?php
declare(strict_types=1);

/**
 * Sovereign Verification Suite: Family Reels Multi-Angle Chrono-Sync Mesh & Performance Engine
 * Evaluated under TAT & Meta / Instagram Best Practices.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Db;
use App\model\Reel;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

echo "=================================================================\n";
echo "🎥 REELS MULTI-ANGLE CHRONO-SYNC & PERFORMANCE VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$pdo = Db::connect2();
$testFamCode = 'TEST_CHRONO_' . bin2hex(random_bytes(3));
$otherFamCode = 'TEST_OTHER_' . bin2hex(random_bytes(3));

$existingUser = $pdo->query("SELECT id, famCode FROM personal LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$testUserId = $existingUser ? (string)$existingUser['id'] : '432292OLAWALE';

// Step 1: Verify Schema Columns
echo "[TEST 1] Verifying Database Schema for Chrono-Sync Columns...\n";
$stmt = $pdo->query("DESCRIBE family_reels");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

assert(in_array('chrono_sync_cluster_id', $columns, true), "Column chrono_sync_cluster_id missing!");
assert(in_array('chrono_sync_label', $columns, true), "Column chrono_sync_label missing!");
assert(in_array('time_bridge_node_id', $columns, true), "Column time_bridge_node_id missing!");
echo "  ✅ PASS: All 3 Chrono-Sync columns exist on family_reels table.\n\n";

$createdReelIds = [];
$clusterId = 'cluster_' . time() . '_' . bin2hex(random_bytes(4));

try {
    // Step 2: Create Parent Reel with Chrono-Sync Cluster
    echo "[TEST 2] Creating Multi-Angle Parent Reel (Angle: Dad's POV)...\n";
    $reel1Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Cake cutting celebration - Dad POV',
        'video_url' => '/resources/videos/reels/test_angle_1.mp4',
        'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb_1.jpg',
        'aspect_ratio' => '9:16',
        'category' => 'milestone',
        'music_title' => 'Happy Birthday Acoustic',
        'chrono_sync_cluster_id' => $clusterId,
        'chrono_sync_label' => "Dad's POV"
    ]);
    $createdReelIds[] = $reel1Id;
    echo "  ✅ PASS: Parent reel created with ID: {$reel1Id}\n\n";

    // Step 3: Create Child / Peer Reel 2 in Same Cluster
    echo "[TEST 3] Creating Peer Reel 2 in Cluster (Angle: Mum's Angle)...\n";
    $reel2Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Cake cutting celebration - Mum Angle',
        'video_url' => '/resources/videos/reels/test_angle_2.mp4',
        'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb_2.jpg',
        'aspect_ratio' => '9:16',
        'category' => 'milestone',
        'music_title' => 'Happy Birthday Acoustic',
        'chrono_sync_cluster_id' => $clusterId,
        'chrono_sync_label' => "Mum's Angle"
    ]);
    $createdReelIds[] = $reel2Id;
    echo "  ✅ PASS: Peer reel 2 created with ID: {$reel2Id}\n\n";

    // Step 4: Create Peer Reel 3 in Same Cluster
    echo "[TEST 4] Creating Peer Reel 3 in Cluster (Angle: Grandma's Reaction)...\n";
    $reel3Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Cake cutting celebration - Grandma Reaction',
        'video_url' => '/resources/videos/reels/test_angle_3.mp4',
        'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb_3.jpg',
        'aspect_ratio' => '9:16',
        'category' => 'milestone',
        'music_title' => 'Happy Birthday Acoustic',
        'chrono_sync_cluster_id' => $clusterId,
        'chrono_sync_label' => "Grandma's Reaction"
    ]);
    $createdReelIds[] = $reel3Id;
    echo "  ✅ PASS: Peer reel 3 created with ID: {$reel3Id}\n\n";

    // Step 5: Query Peer Angles for Reel 1
    echo "[TEST 5] Testing Reel::getChronoSyncAngles() Peer Retrieval...\n";
    $angles = Reel::getChronoSyncAngles($clusterId, $testFamCode, $reel1Id);
    assert(count($angles) === 2, "Expected 2 peer angles, got: " . count($angles));
    $labels = array_map(fn($a) => $a['chrono_sync_label'], $angles);
    assert(in_array("Mum's Angle", $labels, true), "Mum's Angle missing from peer angles");
    assert(in_array("Grandma's Reaction", $labels, true), "Grandma's Reaction missing from peer angles");
    echo "  ✅ PASS: Peer angles correctly retrieved (count: " . count($angles) . ")\n\n";

    // Step 6: Adversarial IDOR Check (Cross-Tenant Scoping)
    echo "[TEST 6] Testing Cross-Tenant IDOR Security Boundary...\n";
    $intruderAngles = Reel::getChronoSyncAngles($clusterId, $otherFamCode, $reel1Id);
    assert(count($intruderAngles) === 0, "Security Violation: Peer angles leaked across family boundary!");
    echo "  ✅ PASS: Cross-tenant access successfully blocked (0 angles leaked).\n\n";

    // Step 7: Verify Single Reel Model returns Chrono-Sync count
    echo "[TEST 7] Verifying getReelById() surfaces chrono_sync_count...\n";
    $parentReel = Reel::getReelById($reel1Id, $testUserId);
    assert($parentReel !== null, "Parent reel not found!");
    assert((int)$parentReel['chrono_sync_count'] === 2, "Expected chrono_sync_count = 2, got: " . ($parentReel['chrono_sync_count'] ?? 'null'));
    assert($parentReel['chrono_sync_label'] === "Dad's POV", "Expected label Dad's POV");
    echo "  ✅ PASS: getReelById() accurately calculates chrono_sync_count = 2.\n\n";

    // Step 8: Verify Recent Active Reels for Modal Linking
    echo "[TEST 8] Verifying Reel::getRecentActiveReelsForCluster()...\n";
    $recent = Reel::getRecentActiveReelsForCluster($testFamCode, 10);
    assert(count($recent) >= 3, "Expected at least 3 recent reels for cluster linking.");
    echo "  ✅ PASS: getRecentActiveReelsForCluster() successfully surfaced active reels.\n\n";

    // Step 9: Verify British English Invariants in View Templates
    echo "[TEST 9] Verifying British English Invariants in Reels Views...\n";
    $modalBlade = file_get_contents(__DIR__ . '/../resources/views/member/modals/createReelModal.blade.php');
    assert(!str_contains($modalBlade, 'customize'), "Found US English 'customize' in createReelModal.blade.php");
    assert(!str_contains($modalBlade, 'favorite'), "Found US English 'favorite' in createReelModal.blade.php");
    assert(str_contains($modalBlade, 'Multi-Angle Chrono-Sync'), "Multi-Angle Chrono-Sync label missing from modal");
    echo "  ✅ PASS: British English invariants and UI elements verified.\n\n";

} finally {
    // Cleanup test records
    echo "[CLEANUP] Removing test reels from database...\n";
    if (!empty($createdReelIds)) {
        $placeholders = implode(',', array_fill(0, count($createdReelIds), '?'));
        $delStmt = $pdo->prepare("DELETE FROM family_reels WHERE id IN ({$placeholders})");
        $delStmt->execute($createdReelIds);
    }
    echo "  ✅ PASS: Cleanup complete.\n\n";
}

echo "=================================================================\n";
echo "🏆 ALL 9 MULTI-ANGLE CHRONO-SYNC TESTS PASSED WITH 100% SUCCESS! \n";
echo "=================================================================\n";
