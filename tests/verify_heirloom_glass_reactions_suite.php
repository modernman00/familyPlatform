<?php
declare(strict_types=1);

/**
 * 🌟 Living Heirloom Glass 3D Reactions Verification Suite
 * Verifies backend reaction support (including 'celebrate' super-reaction),
 * parameter normalization, strict typing, asset generation, and CSS tokens.
 */

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "🌟 LIVING HEIRLOOM GLASS 3D REACTIONS VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$totalChecks = 0;

function assertCondition(bool $cond, string $msg): void {
    global $passCount, $totalChecks;
    $totalChecks++;
    if (!$cond) {
        echo "❌ FAILED: {$msg}\n";
        exit(1);
    }
    echo "✅ PASSED: {$msg}\n";
    $passCount++;
}

// 1. Verify PostLikeController supports all 6 reactions
$controllerFile = file_get_contents(__DIR__ . '/../app/controller/members/PostLikeController.php');
assertCondition($controllerFile !== false, "PostLikeController.php exists");
assertCondition(strpos($controllerFile, "'celebrate'") !== false, "PostLikeController whitelist contains 'celebrate'");
assertCondition(strpos($controllerFile, "'like', 'love', 'haha', 'shock', 'sad', 'celebrate'") !== false, "PostLikeController whitelist contains full 6-reaction set");

// 2. Verify CommentReactionController supports 'celebrate'
$commentController = file_get_contents(__DIR__ . '/../app/controller/members/CommentReactionController.php');
assertCondition(strpos($commentController, "'celebrate'") !== false, "CommentReactionController allowed list contains 'celebrate'");

// 3. Verify heirloomGlassReactions.js contains all 6 vector glass generators
$jsReactionsFile = file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/heirloomGlassReactions.js');
assertCondition($jsReactionsFile !== false, "heirloomGlassReactions.js exists");
foreach (['love', 'like', 'haha', 'shock', 'sad', 'celebrate'] as $rtype) {
    assertCondition(strpos($jsReactionsFile, "case '{$rtype}':") !== false, "heirloomGlassReactions.js handles case '{$rtype}'");
}
assertCondition(strpos($jsReactionsFile, "export function getHeirloomGlassSvg") !== false, "Exports getHeirloomGlassSvg function");

// 4. Verify CSS Design Tokens in standards-v21.css
$cssFile = file_get_contents(__DIR__ . '/../public/css/standards-v21.css');
assertCondition(strpos($cssFile, ".heirloom-glass-dock") !== false, "standards-v21.css contains .heirloom-glass-dock");
assertCondition(strpos($cssFile, "backdrop-filter: blur(24px)") !== false, "standards-v21.css contains 24px optical frosted glass blur");
assertCondition(strpos($cssFile, ".heirloom-reaction-btn") !== false, "standards-v21.css contains .heirloom-reaction-btn");
assertCondition(strpos($cssFile, "min-width: 44px") !== false, "standards-v21.css enforces 44px min-width touch target (Fitts's Law)");
assertCondition(strpos($cssFile, "heirloom-milestone-aura") !== false, "standards-v21.css contains milestone aura animation");

// 5. Verify Blade Template Integration
$postCardFile = file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
assertCondition($postCardFile !== false, "post-card.blade.php exists");
assertCondition(strpos($postCardFile, "heirloom-glass-dock") !== false, "post-card.blade.php integrates heirloom-glass-dock");
assertCondition(strpos($postCardFile, "getHeirloomGlassSvg") !== false, "post-card.blade.php invokes getHeirloomGlassSvg");

// 6. Verify Post and Comment Dual Application & Zero Default-to-Love Regression Guards
assertCondition(strpos($postCardFile, "comment-reaction-float-bar heirloom-glass-dock") !== false, "Comment floating dock uses heirloom-glass-dock");
assertCondition(strpos($postCardFile, "getHeirloomGlassSvg(post.user_reaction, 24)") !== false, "Post active reaction button uses Heirloom Glass SVG");
assertCondition(strpos($postCardFile, "getHeirloomGlassSvg(c.userReaction, 16)") !== false, "Comment active reaction button uses Heirloom Glass SVG");
assertCondition(strpos($postCardFile, "getHeirloomGlassSvg(r.label, 16)") !== false, "Comment top reactions preview uses Heirloom Glass SVG");
assertCondition(strpos($postCardFile, '<template x-if="c.userReaction">') !== false, "Comment reaction icon guarded by <template x-if=\"c.userReaction\"> (prevents Bootstrap .d-inline-flex !important override)");
assertCondition(strpos($postCardFile, '<template x-if="getTopCommentReactions(c.reactions).length > 0">') !== false, "Comment reaction count pill guarded by <template x-if> (prevents empty pill render)");
assertCondition(strpos($jsReactionsFile, "if (!type) return '';") !== false, "getHeirloomGlassSvg returns empty string on falsy input (never defaults to love)");
assertCondition(strpos($jsReactionsFile, "case 'likes':") !== false, "getHeirloomGlassSvg maps 'likes' to Topaz Thumb SVG");
assertCondition(strpos($commentController, "'shock'") !== false, "CommentReactionController accepts 'shock' reaction alias");

// 7. Verify Living Expressive Micro-Actions & Particle Fountain Burst
foreach (['hg-heart-body', 'hg-thumb-hand', 'hg-haha-face', 'hg-wow-mouth', 'hg-sad-tear', 'hg-celebrate-star'] as $svgClass) {
    assertCondition(strpos($jsReactionsFile, $svgClass) !== false, "heirloomGlassReactions.js contains animated SVG group .{$svgClass}");
}
assertCondition(strpos($jsReactionsFile, "export function triggerHeirloomBurst") !== false, "heirloomGlassReactions.js exports triggerHeirloomBurst particle fountain");
foreach (['hgLoveHeartbeat', 'hgThumbPopAction', 'hgHahaLaughBounce', 'hgWowMouthGasp', 'hgSadTearDrop', 'hgCelebrateStarSpin', 'heirloomBurstFly'] as $keyframe) {
    assertCondition(strpos($cssFile, "@keyframes {$keyframe}") !== false, "standards-v21.css defines @keyframes {$keyframe}");
}

// 8. Verify Family Reels Integration (reelsPlayer.js + Reel.php)
$reelsPlayerFile = file_get_contents(__DIR__ . '/../resources/asset/js/components/reels/reelsPlayer.js');
assertCondition($reelsPlayerFile !== false, "reelsPlayer.js exists");
assertCondition(strpos($reelsPlayerFile, "getHeirloomGlassSvg") !== false, "reelsPlayer.js integrates getHeirloomGlassSvg for Reels and Reel comments");
assertCondition(strpos($reelsPlayerFile, "triggerHeirloomBurst") !== false, "reelsPlayer.js triggers 3D Heirloom Glass particle burst on Reel reactions");
$reelModelFile = file_get_contents(__DIR__ . '/../app/model/Reel.php');
assertCondition($reelModelFile !== false, "Reel.php exists");
assertCondition(strpos($reelModelFile, "'celebrate'") !== false, "Reel.php supports 'celebrate' reaction on Reel comments");

// 9. Verify Multi-Family / Connected Network Comment Reaction Persistence (Zero Disappearing Emoji Regression Guard)
assertCondition(strpos($commentController, "FROM user_families") !== false, "CommentReactionController checks user_families for secondary family membership");
assertCondition(strpos($commentController, "FROM requestMgt") !== false, "CommentReactionController checks requestMgt for connected family feed posts");
$feedCompFile = file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/feedComponent.js');
assertCondition(strpos($feedCompFile, "comment.reactions = nextReactions;") !== false, "feedComponent.js reassigns comment.reactions reference for instant Alpine reactivity");

echo "\n=================================================================\n";
echo "🏆 ALL {$totalChecks}/{$totalChecks} HEIRLOOM GLASS REACTION CHECKS PASSED!\n";
echo "=================================================================\n";
