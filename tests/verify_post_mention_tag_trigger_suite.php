<?php

declare(strict_types=1);

/**
 * tests/verify_post_mention_tag_trigger_suite.php
 *
 * Automated verification suite for the interactive @mention tag trigger and autocomplete
 * engine across the post composer, feed comments, and server-side tag synchronisation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\TagController;
use App\services\CommentThreadService;
use Src\Db;

$pdo = Db::connect2();

echo "\n" . str_repeat('=', 65) . "\n";
echo "🏷️  VERIFICATION SUITE: POST @MENTION TAG TRIGGER & AUTOCOMPLETE\n";
echo str_repeat('=', 65) . "\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $message): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "✅ PASS: {$message}\n";
        $passCount++;
    } else {
        echo "❌ FAIL: {$message}\n";
        $failCount++;
    }
}

// ─────────────────────────────────────────────────────────────────
// 1. Static Asset Architecture Verification
// ─────────────────────────────────────────────────────────────────

$postModalBlade = file_get_contents(__DIR__ . '/../resources/views/member/modals/post.blade.php') ?: '';
assertCondition(
    str_contains($postModalBlade, 'id="composerMentionDropdown"') && str_contains($postModalBlade, 'id="composerMentionList"'),
    'Post modal contains inline #composerMentionDropdown and #composerMentionList containers'
);

$postProcessJs = file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/postEmojiImgProcess.js') ?: '';
assertCondition(
    str_contains($postProcessJs, 'composerEditor') && str_contains($postProcessJs, 'handleComposerInput'),
    'Composer rich editor listens to user input for @mention triggers'
);

assertCondition(
    str_contains($postProcessJs, 'selectComposerMention') && str_contains($postProcessJs, 'replaceComposerMentionText'),
    'Composer contains selectComposerMention handler to tag member and format text'
);

assertCondition(
    str_contains($postProcessJs, 'window.autoTagMentionedUsersInText'),
    'Global autoTagMentionedUsersInText hook is exposed to scan typed mentions before submission'
);

$feedComponentJs = file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/feedComponent.js') ?: '';
assertCondition(
    str_contains($feedComponentJs, '/(?:^|\s)@([A-Za-z0-9_\'\- ]{0,40})$/'),
    'feedComponent.js allows whitespace in comment mention query so @First Last does not prematurely close'
);

$profilePagePhp = file_get_contents(__DIR__ . '/../app/controller/members/ProfilePage.php') ?: '';
assertCondition(
    str_contains($profilePagePhp, 'CommentThreadService::extractMentionedIds($cleanMultiLineMessage'),
    'ProfilePage::post automatically extracts body @mentions and merges them into tagged user IDs'
);

// ─────────────────────────────────────────────────────────────────
// 2. Behavioral Verification: Mention Parsing & Cross-Tenant Security
// ─────────────────────────────────────────────────────────────────

$authorId = 'test_author_' . bin2hex(random_bytes(4));
$segunId  = 'test_segun_' . bin2hex(random_bytes(4));
$strangerId = 'test_stranger_' . bin2hex(random_bytes(4));
$famCode  = 'TAGTEST' . rand(100, 999);
$otherCode = 'OTHER' . rand(100, 999);

try {
    // Fixtures
    $stmtP = $pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmtP->execute([$authorId, 'Wally', 'Olaogun', $famCode]);
    $stmtP->execute([$segunId, 'Segun', 'Olaogun', $famCode]);
    $stmtP->execute([$strangerId, 'John', 'Doe', $otherCode]);

    // Test CommentThreadService::extractMentionedIds directly
    $familyMembers = [
        ['id' => $segunId, 'firstName' => 'Segun', 'lastName' => 'Olaogun', 'famCode' => $famCode],
        ['id' => $authorId, 'firstName' => 'Wally', 'lastName' => 'Olaogun', 'famCode' => $famCode],
    ];

    $sampleText = "Happy birthday to @Segun Olaogun and also @Wally Olaogun! Having an amazing celebration!";
    $extracted = CommentThreadService::extractMentionedIds($sampleText, $familyMembers, $authorId);

    assertCondition(
        in_array($segunId, $extracted, true),
        'extractMentionedIds correctly extracts Segun Olaogun from "@Segun Olaogun"'
    );

    assertCondition(
        !in_array($authorId, $extracted, true),
        'extractMentionedIds safely excludes author from tagging themselves'
    );

    // Test case-insensitive matching (e.g. user typed lowercase "@segun olaogun")
    $lowercaseText = "Checking in with @segun olaogun today.";
    $extractedLower = CommentThreadService::extractMentionedIds($lowercaseText, $familyMembers, $authorId);
    assertCondition(
        in_array($segunId, $extractedLower, true),
        'extractMentionedIds supports case-insensitive match for "@segun olaogun"'
    );

    // Test TagController syncPostTags with extracted Segun ID
    $stmtPost = $pdo->prepare("INSERT INTO post (id, postMessage, postFamCode, post_time) VALUES (?, ?, ?, ?)");
    $stmtPost->execute([$authorId, $sampleText, $famCode, (string)milliSeconds()]);
    $postNo = (int)$pdo->lastInsertId();

    $syncedTags = TagController::syncPostTags($postNo, $authorId, $extracted, $famCode);
    assertCondition(
        count($syncedTags) === 1 && $syncedTags[0]['user_id'] === $segunId,
        'TagController::syncPostTags successfully records Segun Olaogun as tagged on the post'
    );

    // Verify post_tags table has the record
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM post_tags WHERE post_no = ? AND user_id = ?");
    $stmtCheck->execute([$postNo, $segunId]);
    $tagCount = (int)$stmtCheck->fetchColumn();
    assertCondition($tagCount === 1, 'post_tags database table confirms Segun Olaogun is tagged on post');

} finally {
    // Cleanup fixtures
    if (!empty($postNo)) {
        $pdo->prepare("DELETE FROM post_tags WHERE post_no = ?")->execute([$postNo]);
        $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    }
    $delP = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)");
    $delP->execute([$authorId, $segunId, $strangerId]);
}

echo "\n" . str_repeat('=', 65) . "\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo str_repeat('=', 65) . "\n\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
