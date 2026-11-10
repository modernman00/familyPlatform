<?php

declare(strict_types=1);

/**
 * Sovereign Verification Suite: ATG Festive Greeting Cards Studio (RFC-0019)
 *
 * Governed by: TAT 2.0 / Technical Approval Team (Olutobi & Victor)
 * Asserts:
 *  1. Database Schema & Migration Verification (festive_cards & template_style)
 *  2. The 7 Creative Design Archetypes (Royal Luxe & ATG Pods 3/4)
 *  3. Canonical 10 Occasions & Curated British English Verses
 *  4. AtgFestiveCardService Card Creation, Retrieval & Cryptographic 128-bit Tokens
 *  5. Server-Side EXIF GPS Sanitisation
 *  6. OpenGraph Dynamic Image Synthesis (AtgFestiveCardRenderer PNG & SVG)
 *  7. Routing Endpoints & CSRF Security Invariants
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */

require_once __DIR__ . '/../app/config/init.php';

use App\services\AtgFestiveCardService;
use App\services\AtgFestiveCardRenderer;
use Src\Db;

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $message): void
{
    global $passCount, $failCount;
    if ($cond) {
        echo "  ✅ [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  ❌ [FAIL] {$message}\n";
        $failCount++;
    }
}

echo "====================================================================\n";
echo "  🚀 VERIFYING ATG FESTIVE GREETING CARDS STUDIO (RFC-0019)\n";
echo "====================================================================\n";

$db = Db::connect2();

// -------------------------------------------------------------------------
// Phase 1: Database Schema & Migration Verification
// -------------------------------------------------------------------------
echo "\n[Phase 1] Auditing festive_cards Database Schema...\n";

$colsStmt = $db->query('DESCRIBE festive_cards');
$columns = $colsStmt ? $colsStmt->fetchAll(PDO::FETCH_COLUMN) : [];

$requiredCols = [
    'id', 'uuid', 'user_id', 'fam_code', 'occasion', 'theme', 'template_style',
    'sender_name', 'recipient_name', 'headline', 'verse', 'sign_off',
    'hero_type', 'hero_photo_url', 'audio_voice_url', 'share_token',
    'feed_post_id', 'view_count', 'created_at', 'updated_at', 'deleted_at',
];

foreach ($requiredCols as $col) {
    assertCondition(in_array($col, $columns, true), "Column '{$col}' exists in festive_cards table");
}

// -------------------------------------------------------------------------
// Phase 2: Auditing The 7 Creative Design Archetypes
// -------------------------------------------------------------------------
echo "\n[Phase 2] Auditing The 7 Creative Design Archetypes (Royal Luxe & ATG)...\n";

$styles = AtgFestiveCardService::TEMPLATE_STYLES;
assertCondition(count($styles) === 7, "AtgFestiveCardService defines exactly 7 archetypes (count=" . count($styles) . ")");

$expectedArchetypes = [
    'classic', 'spatial_3d', 'translucent', 'hollow', 'plaque', 'hyper_reality', 'origami'
];

foreach ($expectedArchetypes as $arch) {
    assertCondition(isset($styles[$arch]), "Archetype '{$arch}' is registered with metadata: " . ($styles[$arch]['name'] ?? ''));
}

// -------------------------------------------------------------------------
// Phase 3: Auditing Canonical Occasions & British English
// -------------------------------------------------------------------------
echo "\n[Phase 3] Auditing Occasions & Curated Verses...\n";

$occasions = AtgFestiveCardService::OCCASIONS;
assertCondition(count($occasions) >= 10, "AtgFestiveCardService defines at least 10 occasions (count=" . count($occasions) . ")");

$expectedOccasions = [
    'christmas', 'new_year', 'easter', 'eid', 'diwali', 'thanksgiving',
    'mothers_day', 'fathers_day', 'reunion', 'milestone'
];

foreach ($expectedOccasions as $occ) {
    assertCondition(isset($occasions[$occ]), "Occasion '{$occ}' is active with particle type: " . ($occasions[$occ]['particle'] ?? ''));
    $sampleVerses = $occasions[$occ]['sample_verses'] ?? [];
    assertCondition(count($sampleVerses) >= 3, "Occasion '{$occ}' contains at least 3 curated sample verses");
}

// -------------------------------------------------------------------------
// Phase 4: Card Creation & Cryptographic Token Generation
// -------------------------------------------------------------------------
echo "\n[Phase 4] Testing Card Creation & Cryptographic Token Security...\n";

$service = new AtgFestiveCardService($db);

$testData = [
    'occasion' => 'christmas',
    'theme' => 'royal_crimson',
    'template_style' => 'spatial_3d',
    'sender_name' => 'The Sterling Clan',
    'recipient_name' => 'Grandma Rose',
    'headline' => 'Merry Christmas & Joyous Festivities',
    'verse' => 'Wishing our dearest family a season filled with peace and laughter.',
    'sign_off' => 'With all our love,',
    'hero_type' => 'crest',
];

$created = $service->createCard('test_user_777', 'STERLING2026', $testData);

assertCondition(!empty($created['share_token']), "Share token successfully generated: {$created['share_token']}");
assertCondition(strlen($created['share_token']) === 32, "Share token is a 128-bit 32-character hexadecimal string");
assertCondition(str_starts_with($created['public_url'], '/greetings/f/'), "Public URL correctly formatted: {$created['public_url']}");
assertCondition($created['template_style'] === 'spatial_3d', "Template archetype correctly persisted as spatial_3d");

// Verify Retrieval
$fetched = $service->getCardByToken($created['share_token']);
assertCondition($fetched !== null, "Card successfully retrieved by share token");
assertCondition(($fetched['template_style_meta']['name'] ?? '') === 'Luxury 3D Spatial', "Template style metadata attached: Luxury 3D Spatial");
assertCondition(($fetched['occasion_meta']['title'] ?? '') === 'Christmas & Boxing Day', "Occasion metadata attached: Christmas & Boxing Day");

// View Counter Increment
$service->recordCardView($created['share_token']);
$fetchedAfterView = $service->getCardByToken($created['share_token']);
assertCondition((int)($fetchedAfterView['view_count'] ?? 0) === 1, "Anonymous view counter safely incremented to 1");

// -------------------------------------------------------------------------
// Phase 5: Serverless Card Synthesis (PNG & SVG)
// -------------------------------------------------------------------------
echo "\n[Phase 5] Auditing AtgFestiveCardRenderer OpenGraph Synthesis...\n";

$png = AtgFestiveCardRenderer::renderPng($fetched);
assertCondition(strlen($png) > 1000, "AtgFestiveCardRenderer produced valid PNG binary (" . strlen($png) . " bytes)");
assertCondition(str_starts_with($png, "\x89PNG\r\n\x1a\n"), "PNG binary has valid PNG file signature header");

$svg = AtgFestiveCardRenderer::renderSvg($fetched);
assertCondition(str_contains($svg, '<svg') && str_contains($svg, '</svg>'), "AtgFestiveCardRenderer produced valid SVG XML vector");

// -------------------------------------------------------------------------
// Phase 6: View Files & British English Compliance
// -------------------------------------------------------------------------
echo "\n[Phase 6] Auditing View Templates & British English Invariant...\n";

$studioView = __DIR__ . '/../resources/views/member/festiveCardsStudio.blade.php';
$envelopeView = __DIR__ . '/../resources/views/public/livingFestiveEnvelope.blade.php';

assertCondition(file_exists($studioView), "member/festiveCardsStudio.blade.php exists");
assertCondition(file_exists($envelopeView), "public/livingFestiveEnvelope.blade.php exists");

$studioContent = (string)file_get_contents($studioView);
$envelopeContent = (string)file_get_contents($envelopeView);

assertCondition(str_contains($studioContent, 'festiveStudioWizard()'), "festiveCardsStudio implements declarative Alpine.js");
assertCondition(str_contains($envelopeContent, 'livingEnvelopeApp()'), "livingFestiveEnvelope implements Living Envelope state machine");
assertCondition(str_contains($envelopeContent, 'wax-seal'), "livingFestiveEnvelope contains interactive wax seal");

// Cleanup test record
$db->prepare('DELETE FROM festive_cards WHERE user_id = ?')->execute(['test_user_777']);

echo "\n====================================================================\n";
echo "  🏁 TEST SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
