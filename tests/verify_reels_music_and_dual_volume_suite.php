<?php
declare(strict_types=1);

/**
 * Sovereign Verification Suite: Reels Music Studio, Dual-Track Volume & Viral Audio ("Use This Sound")
 * Governance: TAT 2.0 & Engineering Council
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Db;
use App\model\Reel;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

echo "=================================================================\n";
echo "🎵 REELS MUSIC STUDIO, DUAL VOLUME & VIRAL AUDIO VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$pdo = Db::connect2();
$testFamCode = 'TEST_FAM_MUSIC_' . bin2hex(random_bytes(3));
$otherFamCode = 'TEST_OTHER_FAM_' . bin2hex(random_bytes(3));

$existingUserStmt = $pdo->query("SELECT id, famCode FROM personal LIMIT 1");
$existingUser = $existingUserStmt->fetch(PDO::FETCH_ASSOC);
$testUserId = $existingUser ? (string)$existingUser['id'] : '432292OLAWALE';

$createdReelIds = [];

try {
    // TEST 1: Database Schema Verification
    echo "[TEST 1] Verifying Database Schema for Soundtrack & Dual Volume Columns...\n";
    $stmt = $pdo->query("DESCRIBE family_reels");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    assert(in_array('audio_url', $columns, true), "Column audio_url is missing from family_reels!");
    assert(in_array('video_volume', $columns, true), "Column video_volume is missing from family_reels!");
    assert(in_array('music_volume', $columns, true), "Column music_volume is missing from family_reels!");
    assert(in_array('audio_start_sec', $columns, true), "Column audio_start_sec is missing from family_reels!");
    echo "  ✅ PASS: All 4 soundtrack & volume columns exist in family_reels table.\n\n";

    // TEST 2: Curated Starter Library Verification
    echo "[TEST 2] Verifying Curated Audio Library tracks...\n";
    $library = Reel::getAudioLibrary();
    assert(count($library) === 6, "Expected exactly 6 curated library tracks, got " . count($library));

    $expectedIds = [
        'birthday_fanfare',
        'acoustic_warmth',
        'gospel_faith',
        'afrobeats_groove',
        'lofi_nostalgia',
        'wedding_reunion',
    ];

    foreach ($library as $track) {
        assert(in_array($track['id'], $expectedIds, true), "Unexpected track ID: " . $track['id']);
        assert(!empty($track['title']), "Track title should not be empty");
        assert(!empty($track['url']), "Track URL should not be empty");
        
        // Verify audio file exists on disk
        $localPath = dirname(__DIR__) . '/public' . $track['url'];
        assert(is_file($localPath), "Audio file not found on disk at: {$localPath}");
        assert(filesize($localPath) > 1000, "Audio file is suspiciously small or corrupted: {$localPath}");
    }
    echo "  ✅ PASS: All 6 curated tracks exist on disk with valid file sizes.\n\n";

    // TEST 3: Create Reel with Dual-Track Volume and Curated Audio
    echo "[TEST 3] Creating Reel with Curated Audio & Dual-Track Balance (Voice: 100%, Music: 35%, Start: 1.5s)...\n";
    $birthdayTrack = $library[0];
    $reel1Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Testing birthday celebration with backing fanfare',
        'video_url' => '/resources/videos/reels/test_video_music_1.mp4',
        'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb_music_1.jpg',
        'aspect_ratio' => '9:16',
        'category' => 'birthday',
        'music_title' => $birthdayTrack['title'],
        'audio_url' => $birthdayTrack['url'],
        'video_volume' => 100,
        'music_volume' => 35,
        'audio_start_sec' => 1.5,
    ]);
    $createdReelIds[] = $reel1Id;
    assert($reel1Id > 0, "Failed to create reel with audio!");

    $fetchedReel1 = Reel::getReelById($reel1Id, $testUserId);
    assert($fetchedReel1 !== null, "Created reel could not be fetched by ID!");
    assert($fetchedReel1['music_title'] === $birthdayTrack['title'], "Music title mismatch: " . $fetchedReel1['music_title']);
    assert($fetchedReel1['audio_url'] === $birthdayTrack['url'], "Audio URL mismatch: " . $fetchedReel1['audio_url']);
    assert((int)$fetchedReel1['video_volume'] === 100, "Video volume mismatch: " . $fetchedReel1['video_volume']);
    assert((int)$fetchedReel1['music_volume'] === 35, "Music volume mismatch: " . $fetchedReel1['music_volume']);
    assert((float)$fetchedReel1['audio_start_sec'] === 1.5, "Audio start sec mismatch: " . $fetchedReel1['audio_start_sec']);
    echo "  ✅ PASS: Reel created and retrieved with exact dual volume and audio offset.\n\n";

    // TEST 4: Create Peer Reel with Same Sound (Viral "Use This Sound" Simulation)
    echo "[TEST 4] Creating Second Reel with Same Sound (\"Use This Sound\" by Relative)...\n";
    $reel2Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Auntie responding with the same fanfare song',
        'video_url' => '/resources/videos/reels/test_video_music_2.mp4',
        'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb_music_2.jpg',
        'aspect_ratio' => '9:16',
        'category' => 'birthday',
        'music_title' => $birthdayTrack['title'],
        'audio_url' => $birthdayTrack['url'],
        'video_volume' => 80,
        'music_volume' => 60,
        'audio_start_sec' => 0.0,
    ]);
    $createdReelIds[] = $reel2Id;
    assert($reel2Id > 0, "Failed to create peer reel!");
    echo "  ✅ PASS: Second reel using same sound successfully created with ID: {$reel2Id}\n\n";

    // TEST 5: Query Reels By Sound & Strict Tenant Isolation
    echo "[TEST 5] Verifying Reel::getReelsBySound() & Strict Multi-Tenant Isolation...\n";
    $soundReelsSameTenant = Reel::getReelsBySound($birthdayTrack['title'], $testFamCode);
    assert(count($soundReelsSameTenant) >= 2, "Expected at least 2 reels sharing sound, found " . count($soundReelsSameTenant));
    
    $soundReelIds = array_map(fn($r) => (int)$r['id'], $soundReelsSameTenant);
    assert(in_array($reel1Id, $soundReelIds, true), "Reel 1 missing from sound feed!");
    assert(in_array($reel2Id, $soundReelIds, true), "Reel 2 missing from sound feed!");

    // Dr. Viktor Kovács Isolation Check: Other tenant querying same sound MUST see 0 reels from testFamCode
    $soundReelsOtherTenant = Reel::getReelsBySound($birthdayTrack['title'], $otherFamCode);
    foreach ($soundReelsOtherTenant as $otherR) {
        assert($otherR['famCode'] === $otherFamCode, "Security Violation: Tenant isolation leak in getReelsBySound!");
        assert($otherR['id'] !== $reel1Id && $otherR['id'] !== $reel2Id, "Security Violation: Cross-tenant reel leaked!");
    }
    echo "  ✅ PASS: Sound reels query correctly returns peer reels and enforces strict famCode tenant isolation.\n\n";

    // TEST 6: Defensive Clamping & Edge-Case Input
    echo "[TEST 6] Testing Defensive Clamping for Out-of-Bounds Volumes & Audio Offsets...\n";
    $reel3Id = Reel::createReel([
        'user_id' => $testUserId,
        'famCode' => $testFamCode,
        'caption' => 'Edge-case clamping test',
        'video_url' => '/resources/videos/reels/test_video_clamp.mp4',
        'thumbnail_url' => null,
        'aspect_ratio' => '9:16',
        'category' => 'milestone',
        'music_title' => 'Clamped Audio Test',
        'audio_url' => null,
        'video_volume' => 250, // Should clamp to 100
        'music_volume' => -40, // Should clamp to 0
        'audio_start_sec' => -10.5, // Should clamp to 0.0
    ]);
    $createdReelIds[] = $reel3Id;

    $fetchedReel3 = Reel::getReelById($reel3Id, $testUserId);
    assert($fetchedReel3 !== null, "Could not fetch clamped reel!");
    assert((int)$fetchedReel3['video_volume'] === 100, "Video volume not clamped to 100: " . $fetchedReel3['video_volume']);
    assert((int)$fetchedReel3['music_volume'] === 0, "Music volume not clamped to 0: " . $fetchedReel3['music_volume']);
    assert((float)$fetchedReel3['audio_start_sec'] === 0.0, "Audio start sec not clamped to 0.0: " . $fetchedReel3['audio_start_sec']);
    echo "  ✅ PASS: Defensive volume and offset clamping verified (0-100%, 0.0s min).\n\n";

    // TEST 7: 7-Day Expiration Retention Verification (CEO Invariant)
    echo "[TEST 7] Verifying 7-Day Reels Retention Policy Invariant...\n";
    $expDays = Reel::getExpirationDays();
    assert($expDays === 7, "CEO Mandate Violation: REELS_EXPIRATION_DAYS must strictly be 7, got {$expDays}!");
    echo "  ✅ PASS: 7-day retention period strictly preserved.\n\n";

} finally {
    // Cleanup created test records using parameterized PDO statement (Rule 3)
    if (!empty($createdReelIds)) {
        echo "🧹 Cleaning up " . count($createdReelIds) . " test reel records...\n";
        $deleteStmt = $pdo->prepare("DELETE FROM family_reels WHERE id = ?");
        foreach ($createdReelIds as $delId) {
            $deleteStmt->execute([$delId]);
        }
        echo "  ✅ PASS: Test cleanup completed cleanly.\n\n";
    }
}

echo "=================================================================\n";
echo "🎉 ALL TESTS PASSED! REELS MUSIC STUDIO & VIRAL SOUND VERIFIED!\n";
echo "=================================================================\n";
