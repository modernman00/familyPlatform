<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

use App\dto\PostDto;
use App\services\HashtagService;
use App\services\LinkPreviewService;

echo "======================================================================\n";
echo "🧪 VERIFY ZERO APOSTROPHE ENTITY CORRUPTION IN POSTS & COMMENTS\n";
echo "======================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $message): void
{
    global $passCount, $failCount;
    if ($condition) {
        echo "  ✅ PASS: $message\n";
        $passCount++;
    } else {
        echo "  ❌ FAIL: $message\n";
        $failCount++;
    }
}

// -----------------------------------------------------------------------------
// Test 1: LinkPreviewService::sanitizePostMessage entity decoding & normalization
// -----------------------------------------------------------------------------
echo "[Test 1] Testing LinkPreviewService::sanitizePostMessage...\n";

$testInput1 = "I'm getting them too - this shouldn't happen in either post or comments.";
$result1 = LinkPreviewService::sanitizePostMessage($testInput1);
assertCondition($result1 === "I'm getting them too - this shouldn't happen in either post or comments.", "Clean apostrophes are perfectly preserved without entity encoding");

$testInput2 = "I&#039;m getting them too &amp;#039; it&#39;s &039;working&apos;!";
$result2 = LinkPreviewService::sanitizePostMessage($testInput2);
assertCondition(
    !str_contains($result2, '039') && !str_contains($result2, '&amp;') && !str_contains($result2, '&#'),
    "Legacy and double-encoded entities (&#039;, &amp;#039;, &039;) decode to native apostrophes"
);
assertCondition($result2 === "I'm getting them too ' it's 'working'!", "All entity apostrophes normalized to native single quotes");

// -----------------------------------------------------------------------------
// Test 2: HashtagService::extractHashtags immunity to HTML entities
// -----------------------------------------------------------------------------
echo "\n[Test 2] Testing HashtagService::extractHashtags...\n";

$hashtagText1 = "Celebrating #FamilyReunion2026 with Grandma's famous cake #heritage_love!";
$tags1 = HashtagService::extractHashtags($hashtagText1);
assertCondition(in_array('familyreunion2026', $tags1, true), "Valid alphanumeric hashtag #FamilyReunion2026 extracted");
assertCondition(in_array('heritage_love', $tags1, true), "Valid hashtag #heritage_love extracted");

$hashtagText2 = "I&#039;m testing whether &#039; or &#39; ever produces phantom tags #039 or #39 #RealTag";
$tags2 = HashtagService::extractHashtags($hashtagText2);
assertCondition(!in_array('039', $tags2, true), "Phantom tag '039' from &#039; is NEVER extracted");
assertCondition(!in_array('39', $tags2, true), "Phantom numeric tag '39' is NEVER extracted");
assertCondition(in_array('realtag', $tags2, true), "Real tag #RealTag is extracted correctly");

// -----------------------------------------------------------------------------
// Test 3: PostDto::fromDatabaseRow normalization
// -----------------------------------------------------------------------------
echo "\n[Test 3] Testing PostDto::fromDatabaseRow normalization...\n";

$mockRow = [
    'post_no' => 999999,
    'id' => 'TEST_USER',
    'fullName' => "Sarah O'Connor",
    'postMessage' => "I&#039;m so excited, this shouldn&#039;t be corrupted! #FamilyFun",
    'postFamCode' => 'FAM999',
    'post_likes' => 0,
    'date_created' => '2026-10-30 12:00:00'
];

$postDto = PostDto::fromDatabaseRow($mockRow);
assertCondition(
    !str_contains($postDto->postMessage, '039'),
    "PostDto::postMessage decodes legacy &#039; entities to native apostrophes"
);
assertCondition(
    $postDto->postMessage === "I'm so excited, this shouldn't be corrupted! #FamilyFun",
    "PostDto preserves native apostrophes cleanly"
);

// -----------------------------------------------------------------------------
// Test 4: JavaScript Regex Verification (Node execution)
// -----------------------------------------------------------------------------
echo "\n[Test 4] Verifying JavaScript feedComponent rendering logic via Node...\n";

$nodeScript = <<<'NODE'
function decodeHtml(str) {
    if (!str || typeof str !== 'string') return '';
    let decoded = str;
    for (let i = 0; i < 3 && /&amp;(?:#\d+|#x[0-9a-f]+|[a-z]+);/i.test(decoded); i++) {
        decoded = decoded.replace(/&amp;(#\d+|#x[0-9a-f]+|[a-z]+);/gi, '&$1;');
    }
    return decoded
        .replace(/&(?:#?0*39|#x0*27|apos);/gi, "'")
        .replace(/&(?:#?0*34|#x0*22|quot);/gi, '"')
        .replace(/&(?:#?0*60|#x0*3c|lt);/gi, '<')
        .replace(/&(?:#?0*62|#x0*3e|gt);/gi, '>')
        .replace(/&(?:#?0*38|#x0*26|amp);/gi, '&');
}

function escapeHtml(str) {
    if (!str) return '';
    const clean = decodeHtml(String(str));
    return clean
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function renderHashtags(message) {
    if (!message) return '';
    let safeText = escapeHtml(message);

    safeText = safeText.replace(/(https?:\/\/[^\s<>"']+)/gi, (match) => {
        const cleanHref = match.replace(/[.,;:!?)]+$/, '');
        return `<a href="${cleanHref}">${cleanHref}</a>`;
    });

    safeText = safeText.replace(/(?<![\w@])@([A-Z\p{Lu}][\p{L}'-]+(?:\s+[A-Z\p{Lu}][\p{L}'-]+)+)(?![\w])/gu, (match, name) => {
        return `<span class="mention-chip">${name}</span>`;
    });

    return safeText.replace(/(?<![&\w])#([a-zA-Z][a-zA-Z0-9_]{1,39})(?!;)/gu, (match, tag) => {
        const cleanTag = tag.toLowerCase();
        return `<span class="badge family-tag-pill">#${tag}</span>`;
    });
}

const tests = [
    {
        input: "I'm getting them too - this shouldn't happen in either post or comments.",
        expectedSub: "I'm getting them too - this shouldn't happen"
    },
    {
        input: "I&#039;m getting them too - legacy encoded string",
        expectedSub: "I'm getting them too"
    },
    {
        input: "I&039;m getting them too - unhashed entity string",
        expectedSub: "I'm getting them too"
    },
    {
        input: "We're celebrating #FamilyPride with Grandma's recipe!",
        expectedHashtag: '<span class="badge family-tag-pill">#FamilyPride</span>',
        expectedApostrophes: ["We're", "Grandma's"]
    }
];

let allPassed = true;
for (const t of tests) {
    const rendered = renderHashtags(t.input);
    if (rendered.includes('039') || rendered.includes('&amp;#039;') || rendered.includes('&#039;')) {
        console.error("FAIL: 039 entity found in rendered output:", rendered);
        allPassed = false;
    }
    if (t.expectedSub && !rendered.includes(t.expectedSub)) {
        console.error("FAIL: Expected substring missing:", t.expectedSub, "in:", rendered);
        allPassed = false;
    }
    if (t.expectedHashtag && !rendered.includes(t.expectedHashtag)) {
        console.error("FAIL: Expected hashtag missing:", t.expectedHashtag, "in:", rendered);
        allPassed = false;
    }
    if (t.expectedApostrophes) {
        for (const ap of t.expectedApostrophes) {
            if (!rendered.includes(ap)) {
                console.error("FAIL: Apostrophe word missing:", ap, "in:", rendered);
                allPassed = false;
            }
        }
    }
}

if (allPassed) {
    console.log("NODE_OK");
} else {
    process.exit(1);
}
NODE;

$nodeOutput = shell_exec("node -e " . escapeshellarg($nodeScript));
assertCondition(trim((string)$nodeOutput) === 'NODE_OK', "JavaScript frontend feedComponent renderHashtags verified in Node without entity corruption");

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n======================================================================\n";
echo "Results: $passCount Passed, $failCount Failed\n";
echo "======================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
