<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\dto\PostDto;

echo "=== [BRATS & Adversarial Security Gate] 3D Text Stage Studio Verification ===\n";

$failures = 0;
function assertStageTrue(bool $cond, string $msg): void
{
    global $failures;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
    } else {
        echo "  [FAIL] {$msg}\n";
        $failures++;
    }
}

// 1. Verify all 6 allowed 3D stage archetypes
$expectedStyles = [
    'cyber_glass',
    'alert_pulse',
    'grand_announcement',
    'dynasty_holo',
    'aurora_prism',
    'heirloom_velvet',
];

foreach ($expectedStyles as $slug) {
    assertStageTrue(
        PostDto::cleanPostStyle($slug) === $slug,
        "Allowed 3D stage '{$slug}' accepted by PostDto::cleanPostStyle()"
    );
}

// 2. Retroactive conversion of CEO's post ("Make our family tree bigger by adding your branch 👋")
$ceoRawPost = 'Make our family tree bigger by adding your branch 👋';
$embeddedDynasty = PostDto::embedStyleToken($ceoRawPost, 'dynasty_holo');
assertStageTrue(
    $embeddedDynasty === 'Make our family tree bigger by adding your branch 👋 [style:dynasty_holo]',
    'Retroactive conversion embeds [style:dynasty_holo] cleanly'
);
assertStageTrue(
    PostDto::extractStyleFromMessage($embeddedDynasty) === 'dynasty_holo',
    'PostDto::extractStyleFromMessage() extracts dynasty_holo from stored postMessage'
);
assertStageTrue(
    PostDto::stripStyleToken($embeddedDynasty) === $ceoRawPost,
    'PostDto::stripStyleToken() strips [style:dynasty_holo] leaving clean caption'
);

// Convert from dynasty_holo -> alert_pulse -> none
$embeddedAlert = PostDto::embedStyleToken($embeddedDynasty, 'alert_pulse');
assertStageTrue(
    $embeddedAlert === 'Make our family tree bigger by adding your branch 👋 [style:alert_pulse]',
    'Switching stage replaces previous token without duplicating [style:*] prefixes'
);
$revertedPlain = PostDto::embedStyleToken($embeddedAlert, null);
assertStageTrue(
    $revertedPlain === $ceoRawPost,
    'Reverting stage to plain strips token completely'
);

// 3. Verify PostDto::fromDatabaseRow hydration & toArray serialization
$dto = PostDto::fromDatabaseRow([
    'post_no' => 999123,
    'id' => 'CEO001',
    'fullName' => 'Wally Olaogun',
    'postMessage' => $embeddedDynasty,
    'postFamCode' => 'OLA001',
    'target_scope' => 'all',
]);
$arr = $dto->toArray();
assertStageTrue(
    ($arr['post_style'] ?? null) === 'dynasty_holo',
    'PostDto::toArray() exposes post_style = dynasty_holo'
);
assertStageTrue(
    ($arr['displayMessage'] ?? '') === $ceoRawPost,
    'PostDto::toArray() strips [style:dynasty_holo] from displayMessage'
);

// 4. Dr. Viktor Kovács & Marcus Adversarial Exploit Payloads (XSS / SQLi / Arbitrary Class Injection)
$maliciousPayloads = [
    'cyber_glass" onmouseover="alert(document.cookie)" data-x="',
    '<script>alert(1)</script>',
    "alert_pulse'; DROP TABLE post; --",
    '../../../etc/passwd',
    'evil_custom_class',
    'none',
    '',
];

foreach ($maliciousPayloads as $payload) {
    assertStageTrue(
        PostDto::cleanPostStyle($payload) === null,
        'Adversarial payload neutralised: ' . json_encode($payload)
    );
}

$forgedMessage = '[style:evil_xss_stage] Make our family tree bigger';
assertStageTrue(
    PostDto::extractStyleFromMessage($forgedMessage) === null,
    'Forged [style:evil_xss_stage] token in postMessage is rejected by allowlist'
);
assertStageTrue(
    PostDto::stripStyleToken($forgedMessage) === 'Make our family tree bigger',
    'Forged [style:evil_xss_stage] token is still stripped from visible displayMessage'
);

// 5. Structural audit of views and controllers
$postCardView = (string) file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
assertStageTrue(
    str_contains($postCardView, 'data-testid="feed-text-stage-card"') && str_contains($postCardView, 'data-testid="infeed-stage-converter"'),
    'post-card.blade.php contains 3D stage card and 1-tap retroactive in-feed converter'
);

$composerView = (string) file_get_contents(__DIR__ . '/../resources/views/member/modals/post.blade.php');
assertStageTrue(
    str_contains($composerView, 'id="composerPostStyleInput"') && str_contains($composerView, 'id="composerTextStageBar"'),
    'modals/post.blade.php contains hidden post_style input and 3D Text Stage Studio bar'
);

$postMessageCtrl = (string) file_get_contents(__DIR__ . '/../app/controller/members/PostMessage.php');
assertStageTrue(
    str_contains($postMessageCtrl, 'PostDto::cleanPostStyle') && str_contains($postMessageCtrl, 'if ($userId !== $postAuthorId)'),
    'PostMessage::updatePost enforces PostDto::cleanPostStyle and BOLA author ownership check ($userId !== $postAuthorId)'
);

if ($failures > 0) {
    echo "\nFAILED with {$failures} error(s).\n";
    exit(1);
}

echo "\nALL 3D TEXT STAGE & ADVERSARIAL CHECKS PASSED (0 errors).\n";
exit(0);
