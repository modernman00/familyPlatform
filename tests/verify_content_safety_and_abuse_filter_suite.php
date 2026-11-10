<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\ContentSafetyService;
use Src\Exceptions\ValidationException;

echo "=================================================================\n";
echo "🛡️ SOVEREIGN CONTENT SAFETY & ABUSE GUARDRAIL VERIFICATION SUITE\n";
echo "=================================================================\n";

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$label}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$label}\n";
        $failed++;
    }
}

// 1. Tier 1 Racial & Ethnic Hate Speech (including Homoglyphs, ZWJ, Leetspeak, Dotted)
$racialAttacks = [
    'Direct racial slur' => 'You are a nigger',
    'Leetspeak racial slur' => 'n1gg3r',
    'Zero-width joiner racial slur' => "n\u{200B}i\u{200C}g\u{200D}g\u{FEFF}e\u{200B}r",
    'Cyrillic homoglyph racial slur' => 'nіggеr',
    'Spaced/dotted evasion racial slur' => 'n . i . g . g . e . r',
    'Asterisk-masked racial slur' => 'n*gger',
    'Antisemitic & anti-Asian slurs' => 'kike and chink',
    'Anti-South-Asian & anti-Middle-Eastern slurs' => 'paki and raghead',
    'White supremacist slogan' => 'heil hitler and white power',
    'Direct group hatred (i hate jews)' => 'i hate jews',
    'Leetspeak group hatred (i h@te jews)' => 'i h@te jews',
    'Dotted group hatred (i hate j.e.w.s)' => 'i hate j.e.w.s',
    'Genocidal incitement (gas the jews)' => 'gas the jews',
    'Violent group incitement (death to jews)' => 'death to jews',
    'Violent incitement against other groups' => 'kill all muslims',
    'Dehumanisation predicate' => 'jews are parasites',
];

foreach ($racialAttacks as $label => $payload) {
    $res = ContentSafetyService::inspectText($payload, 'post');
    assertCheck(
        $res['is_clean'] === false && $res['tier'] === ContentSafetyService::TIER_1_HATE_SPEECH,
        "Tier 1 Racial Hate Blocked ({$label})"
    );
}

// 2. Tier 1 Homophobic & Transphobic Hate Speech
$homophobicAttacks = [
    'Direct homophobic slur' => 'faggot',
    'Marcus PoC 1 (ZWJ + Cyrillic + Leetspeak)' => "You are a f\u{200B}аgg0t",
    'Dotted homophobic slur' => 'f.a.g.g.o.t',
    'Asterisk-masked homophobic slur' => 'f*ggot',
    'Transphobic slur' => 'tranny and shemale',
    'Caribbean/UK homophobic slur' => 'batty boy and poofter',
];

foreach ($homophobicAttacks as $label => $payload) {
    $res = ContentSafetyService::inspectText($payload, 'comment');
    assertCheck(
        $res['is_clean'] === false && $res['tier'] === ContentSafetyService::TIER_1_HATE_SPEECH,
        "Tier 1 Homophobic/Transphobic Hate Blocked ({$label})"
    );
}

// 3. Tier 1 Self-Harm & Violent Threats
$threatAttacks = [
    'Direct self-harm instruction' => 'go kill yourself',
    'Abbreviated self-harm' => 'kys loser',
];

foreach ($threatAttacks as $label => $payload) {
    $res = ContentSafetyService::inspectText($payload, 'comment');
    assertCheck(
        $res['is_clean'] === false && $res['tier'] === ContentSafetyService::TIER_1_HATE_SPEECH,
        "Tier 1 Threat Blocked ({$label})"
    );
}

// 4. Tier 2 Severe Curse Words & Harassment
$profanityAttacks = [
    'Direct f-word' => 'fuck this',
    'Run-length stretched f-word' => 'fuuuuuucking',
    'Dotted f-word' => 'f.u.c.k',
    'Asterisk f-word' => 'f*ck',
    'C-word' => 'you cunt',
    'Leetspeak curse words' => 'sh1t b!tch a$$hole',
    'UK abusive insults' => 'total wanker and bellend and twat',
];

foreach ($profanityAttacks as $label => $payload) {
    $res = ContentSafetyService::inspectText($payload, 'post');
    assertCheck(
        $res['is_clean'] === false && $res['tier'] === ContentSafetyService::TIER_2_SEVERE_PROFANITY,
        "Tier 2 Profanity Blocked ({$label})"
    );
}

// 5. Scunthorpe Safe-Word Allowlist (Zero False Positives)
$safeInputs = [
    'UK towns (Scunthorpe, Penistone, Cockfosters)' => 'Visiting family in Scunthorpe, Penistone, and Cockfosters this weekend!',
    'UK counties (Essex, Sussex, Middlesex)' => 'Our cousins live in Essex, Sussex, and Middlesex.',
    'Countries (Nigeria, Niger, Pakistan)' => 'Celebrating our heritage across Nigeria, Niger, and Pakistan.',
    'Surnames (Dickson, Dickens, Hitchcock, Peacock)' => 'Aunt Sarah Dickson and Uncle Charles Dickens fed the peacock.',
    'Culinary & Sports (shiitake, cucumber, mishit)' => 'We ate shiitake mushrooms and cucumber after I mishit the golf ball.',
    'Medical & Professional (physiotherapist, analysis)' => 'My physiotherapist shared a thorough document analysis.',
    'Idioms & Everyday words (spick and span, pass, class)' => 'Grandma kept the kitchen spick and span for the whole class.',
    'URLs containing random character sequences' => 'Check out https://myfamilyplatform.com/media?id=fuk99sh1t',
    'Legitimate cultural & religious mention' => 'Our Jewish neighbours invited us to celebrate Hanukkah together.',
    'Legitimate everyday frustration (I hate cancer / spinach)' => 'I hate cancer so much, but I am proud of Mum staying strong. Also I hate spinach!',
    'Legitimate learning history' => 'We read a book about Jewish heritage and European history.',
];

foreach ($safeInputs as $label => $payload) {
    $res = ContentSafetyService::inspectText($payload, 'post');
    assertCheck($res['is_clean'] === true, "Scunthorpe Safe-Word Allowlist Passed ({$label})");
}

// 6. Marcus PoC 2: Nested Poll Option Array Injection
$pollBlocked = false;
try {
    ContentSafetyService::assertCleanPayload([
        'post' => 'Family Poll',
        'poll question' => 'Best dish?',
        'poll option' => ['Jollof Rice', 'Roast Beef', 'You f@gg0t'],
    ], 'verify_suite');
} catch (ValidationException $e) {
    $pollBlocked = true;
}
assertCheck($pollBlocked, 'Marcus PoC 2: Poll Option Array Slur Injection Blocked via ValidationException');

// 7. Controller Wiring Static Verification
$controllersToCheck = [
    'app/controller/members/ProfilePage.php',
    'app/controller/members/PostMessage.php',
    'app/controller/members/FamilyAnnouncementController.php',
    'app/services/FamilyRoundTableService.php',
    'app/controller/members/FamilyJournalController.php',
    'app/controller/members/Event.php',
    'app/controller/members/TimeCapsuleController.php',
    'app/services/AtgFestiveCardService.php',
];

foreach ($controllersToCheck as $relPath) {
    $fullPath = dirname(__DIR__) . '/' . $relPath;
    $contents = (string)file_get_contents($fullPath);
    assertCheck(
        str_contains($contents, 'ContentSafetyService::'),
        "Endpoint Guardrail Wired: {$relPath}"
    );
}

echo "-----------------------------------------------------------------\n";
echo "Total Checks: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
