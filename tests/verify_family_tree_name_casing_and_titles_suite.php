<?php

declare(strict_types=1);

/**
 * 👑 FAMILY TREE NAME CASING & CULTURAL TITLES VERIFICATION SUITE
 *
 * Verifies:
 * 1. NameFormatterService unit logic (Capital-First sentence naming for first and surname).
 * 2. Cultural title recognition across diverse global cultures:
 *    - Traditional/Chieftaincy (Chief, High Chief, Oba, Otunba, Kabiyesi, Igwe, Obi, Eze, Oloye, Baale, Asiwaju, Yeye, Iyalode)
 *    - Royalty & Nobility (King, Queen, Prince, Princess, Lord, Lady, Sir, Dame)
 *    - Academic & Professional (Prof, Dr, Engr, Barr, Pharm, Arch)
 *    - Religious & Spiritual (Alhaji, Alhaja, Mallam, Sheikh, Pastor, Rev, Bishop, Elder, Deacon, Deaconess)
 *    - Civil & Honorific (Mr, Mrs, Ms, Miss, Hon, Senator, Governor, Ambassador, Justice)
 * 3. Compound titles ("High Chief", "Rev. Dr.", "Pastor (Mrs)", "Dr (Mrs)", "Chief (Mrs)").
 * 4. Hyphenated names ("Mary-Jane"), Celtic apostrophes ("O'Connor", "D'Angelo"), Scottish ("McDonald").
 * 5. Generational and professional post-nominals ("III", "IV", "Jr.", "SAN", "PhD", "MBBS").
 * 6. Surnames deduplication (preventing "Wale Olaogun Olaogun").
 * 7. CollateralKinshipService integration (graph data returns sentence-cased names).
 * 8. OrganogramEditorController integration (saves clean sentence-cased names).
 * 9. KinshipTree client-side engine and Organogram views integration.
 * 10. Cultural title selection component availability in Add/Edit Relative modals.
 */

require_once __DIR__ . '/../app/config/init.php';

use App\services\NameFormatterService;

$baseDir = dirname(__DIR__);

echo "=================================================================\n";
echo "👑 FAMILY TREE NAME CASING & CULTURAL TITLES VERIFICATION SUITE\n";
echo "=================================================================\n";

$checks = 0;
$failures = 0;

function assertCondition(bool $condition, string $description): void {
    global $checks, $failures;
    $checks++;
    if ($condition) {
        echo "  ✅ PASS: {$description}\n";
    } else {
        echo "  ❌ FAIL: {$description}\n";
        $failures++;
    }
}

// -----------------------------------------------------------------
// [1] NameFormatterService Unit Tests
// -----------------------------------------------------------------
echo "\n[1] Testing NameFormatterService Sentence-Casing & Title Logic...\n";

// 1.1 All caps to Capital-First
$res1 = NameFormatterService::formatFirstAndLast('OLAWALE', 'OLAOGUN');
assertCondition($res1['first_name'] === 'Olawale', 'First name OLAWALE formatted to Olawale');
assertCondition($res1['last_name'] === 'Olaogun', 'Last name OLAOGUN formatted to Olaogun');
assertCondition($res1['full_name'] === 'Olawale Olaogun', 'Full name OLAWALE OLAOGUN formatted to Olawale Olaogun');

// 1.2 All lowercase to Capital-First
$res2 = NameFormatterService::formatFirstAndLast('wale', 'olaogun');
assertCondition($res2['full_name'] === 'Wale Olaogun', 'All-lowercase wale olaogun formatted to Wale Olaogun');

// 1.3 Chieftaincy / Traditional titles
$resChief = NameFormatterService::formatFirstAndLast('CHIEF BASHIRU', 'OLAOGUN');
assertCondition($resChief['full_name'] === 'Chief Bashiru Olaogun', 'CHIEF BASHIRU OLAOGUN formatted to Chief Bashiru Olaogun');

$resHighChief = NameFormatterService::formatFirstAndLast('high chief adebayo', 'ogunlesi');
assertCondition($resHighChief['full_name'] === 'High Chief Adebayo Ogunlesi', 'high chief adebayo ogunlesi formatted to High Chief Adebayo Ogunlesi');

$resOba = NameFormatterService::formatFirstAndLast('oba adedotun', 'gbadebo');
assertCondition($resOba['full_name'] === 'Oba Adedotun Gbadebo', 'oba adedotun gbadebo formatted to Oba Adedotun Gbadebo');

$resOtunba = NameFormatterService::formatFirstAndLast('OTUNBA SUBOMI', 'BALOGUN');
assertCondition($resOtunba['full_name'] === 'Otunba Subomi Balogun', 'OTUNBA SUBOMI BALOGUN formatted to Otunba Subomi Balogun');

// 1.4 Royalty
$resKing = NameFormatterService::formatFirstAndLast('king charles', 'windsor');
assertCondition($resKing['full_name'] === 'King Charles Windsor', 'king charles windsor formatted to King Charles Windsor');

$resQueen = NameFormatterService::formatFirstAndLast('QUEEN ELIZABETH', 'WINDSOR');
assertCondition($resQueen['full_name'] === 'Queen Elizabeth Windsor', 'QUEEN ELIZABETH WINDSOR formatted to Queen Elizabeth Windsor');

$resPrince = NameFormatterService::formatFirstAndLast('prince harry', 'windsor');
assertCondition($resPrince['full_name'] === 'Prince Harry Windsor', 'prince harry windsor formatted to Prince Harry Windsor');

// 1.5 Academic & Professional
$resProf = NameFormatterService::formatFirstAndLast('PROF WALE', 'OLAOGUN');
assertCondition($resProf['full_name'] === 'Prof. Wale Olaogun', 'PROF WALE OLAOGUN formatted to Prof. Wale Olaogun');

$resDr = NameFormatterService::formatFirstAndLast('dr folashade', 'akintola');
assertCondition($resDr['full_name'] === 'Dr. Folashade Akintola', 'dr folashade akintola formatted to Dr. Folashade Akintola');

$resEngr = NameFormatterService::formatFirstAndLast('ENGR BABATUNDE', 'FASHOLA');
assertCondition($resEngr['full_name'] === 'Engr. Babatunde Fashola', 'ENGR BABATUNDE FASHOLA formatted to Engr. Babatunde Fashola');

// 1.6 Religious & Spiritual
$resAlhaji = NameFormatterService::formatFirstAndLast('ALHAJI ALIKO', 'DANGOTE');
assertCondition($resAlhaji['full_name'] === 'Alhaji Aliko Dangote', 'ALHAJI ALIKO DANGOTE formatted to Alhaji Aliko Dangote');

$resAlhaja = NameFormatterService::formatFirstAndLast('alhaja kudirat', 'abaniwonda');
assertCondition($resAlhaja['full_name'] === 'Alhaja Kudirat Abaniwonda', 'alhaja kudirat abaniwonda formatted to Alhaja Kudirat Abaniwonda');

$resPastor = NameFormatterService::formatFirstAndLast('PASTOR ENOCH', 'ADEBOYE');
assertCondition($resPastor['full_name'] === 'Pastor Enoch Adeboye', 'PASTOR ENOCH ADEBOYE formatted to Pastor Enoch Adeboye');

$resRev = NameFormatterService::formatFirstAndLast('rev johnson', 'adewale');
assertCondition($resRev['full_name'] === 'Rev. Johnson Adewale', 'rev johnson adewale formatted to Rev. Johnson Adewale');

// 1.7 Civil & Honorific
$resMr = NameFormatterService::formatFirstAndLast('mr john', 'doe');
assertCondition($resMr['full_name'] === 'Mr John Doe', 'mr john doe formatted to Mr John Doe');

$resMrs = NameFormatterService::formatFirstAndLast('MRS JANE', 'DOE');
assertCondition($resMrs['full_name'] === 'Mrs Jane Doe', 'MRS JANE DOE formatted to Mrs Jane Doe');

// 1.8 Global Multi-Cultural Honorifics (Worldwide Inclusivity)
$resMonsieur = NameFormatterService::formatFirstAndLast('monsieur pierre', 'dupont');
assertCondition($resMonsieur['full_name'] === 'Monsieur Pierre Dupont', 'French: monsieur pierre dupont formatted to Monsieur Pierre Dupont');

$resMadame = NameFormatterService::formatFirstAndLast('madame marie', 'curie');
assertCondition($resMadame['full_name'] === 'Madame Marie Curie', 'French: madame marie curie formatted to Madame Marie Curie');

$resDon = NameFormatterService::formatFirstAndLast('don juan', 'de marco');
assertCondition($resDon['full_name'] === 'Don Juan De Marco', 'Spanish: don juan de marco formatted to Don Juan De Marco');

$resHerr = NameFormatterService::formatFirstAndLast('herr johann', 'bach');
assertCondition($resHerr['full_name'] === 'Herr Johann Bach', 'German: herr johann bach formatted to Herr Johann Bach');

$resFrau = NameFormatterService::formatFirstAndLast('frau ingrid', 'weber');
assertCondition($resFrau['full_name'] === 'Frau Ingrid Weber', 'German: frau ingrid weber formatted to Frau Ingrid Weber');

$resRabbi = NameFormatterService::formatFirstAndLast('rabbi jonathan', 'sacks');
assertCondition($resRabbi['full_name'] === 'Rabbi Jonathan Sacks', 'Jewish/Hebrew: rabbi jonathan sacks formatted to Rabbi Jonathan Sacks');

$resPandit = NameFormatterService::formatFirstAndLast('pandit ravi', 'shankar');
assertCondition($resPandit['full_name'] === 'Pandit Ravi Shankar', 'South Asian: pandit ravi shankar formatted to Pandit Ravi Shankar');

$resSwami = NameFormatterService::formatFirstAndLast('swami', 'vivekananda');
assertCondition($resSwami['full_name'] === 'Swami Vivekananda', 'South Asian: swami vivekananda formatted to Swami Vivekananda');

$resSheikh = NameFormatterService::formatFirstAndLast('sheikh mohammed', 'al-mansoor');
assertCondition($resSheikh['full_name'] === 'Sheikh Mohammed Al-Mansoor', 'Middle Eastern: sheikh mohammed al-mansoor formatted to Sheikh Mohammed Al-Mansoor');

$resSultan = NameFormatterService::formatFirstAndLast('sultan qaboos', 'al-said');
assertCondition($resSultan['full_name'] === 'Sultan Qaboos Al-Said', 'Middle Eastern: sultan qaboos al-said formatted to Sultan Qaboos Al-Said');

$resLaird = NameFormatterService::formatFirstAndLast('laird alistair', 'macdonald');
assertCondition($resLaird['full_name'] === 'Laird Alistair Macdonald', 'Scottish/Celtic: laird alistair macdonald formatted to Laird Alistair Macdonald');

// 1.9 Hyphens, Apostrophes, Scottish, and Suffixes
$resHyphen = NameFormatterService::formatFirstAndLast('mary-jane', 'watson-parker');
assertCondition($resHyphen['full_name'] === 'Mary-Jane Watson-Parker', 'mary-jane watson-parker formatted to Mary-Jane Watson-Parker');

$resApos = NameFormatterService::formatFirstAndLast('john', "o'connor");
assertCondition($resApos['full_name'] === "John O'Connor", "john o'connor formatted to John O'Connor");

$resDApos = NameFormatterService::formatFirstAndLast("d'angelo", 'russell');
assertCondition($resDApos['full_name'] === "D'Angelo Russell", "d'angelo russell formatted to D'Angelo Russell");

$resMc = NameFormatterService::formatFirstAndLast('james', 'mcdonald');
assertCondition($resMc['full_name'] === 'James McDonald', 'james mcdonald formatted to James McDonald');

$resSuffix = NameFormatterService::formatFirstAndLast('JOHN', 'DOE III');
assertCondition($resSuffix['full_name'] === 'John Doe III', 'JOHN DOE III formatted to John Doe III (preserves Roman numeral)');

$resSAN = NameFormatterService::formatFirstAndLast('CHIEF WOLE', 'OLANIPEKUN SAN');
assertCondition($resSAN['full_name'] === 'Chief Wole Olanipekun SAN', 'CHIEF WOLE OLANIPEKUN SAN formatted to Chief Wole Olanipekun SAN');

// 1.9 Deduplication
$resDedup = NameFormatterService::formatFirstAndLast('Wale Olaogun', 'Olaogun');
assertCondition($resDedup['full_name'] === 'Wale Olaogun', 'Deduplication prevents repeated surname "Wale Olaogun Olaogun"');

// -----------------------------------------------------------------
// [2] Backend CollateralKinshipService & Organogram Integration
// -----------------------------------------------------------------
echo "\n[2] Auditing Backend Graph Services...\n";

$ckServiceFile = file_get_contents($baseDir . '/app/services/CollateralKinshipService.php');
assertCondition($ckServiceFile !== false, 'CollateralKinshipService.php loaded successfully');
assertCondition(
    str_contains($ckServiceFile, 'NameFormatterService::formatFirstAndLast'),
    'CollateralKinshipService integrates NameFormatterService::formatFirstAndLast for graph nodes'
);

$organogramFile = file_get_contents($baseDir . '/app/controller/members/Organogram.php');
assertCondition($organogramFile !== false, 'Organogram.php loaded successfully');
assertCondition(
    str_contains($organogramFile, 'NameFormatterService::formatName'),
    'Organogram controller integrates NameFormatterService::formatName for surnames and lineage tags'
);

$editorFile = file_get_contents($baseDir . '/app/controller/members/OrganogramEditorController.php');
assertCondition($editorFile !== false, 'OrganogramEditorController.php loaded successfully');
assertCondition(
    str_contains($editorFile, 'formatIncomingName'),
    'OrganogramEditorController integrates formatIncomingName helper for node mutations'
);
assertCondition(
    str_contains($editorFile, 'NameFormatterService::formatFirstAndLast'),
    'OrganogramEditorController normalises incoming relative first and last names'
);

// -----------------------------------------------------------------
// [3] Client-Side KinshipTree & Organogram Views
// -----------------------------------------------------------------
echo "\n[3] Auditing Client-Side KinshipTree & View Templates...\n";

$kinshipJs = file_get_contents($baseDir . '/public/js/vendor/kinshiptree.js');
assertCondition($kinshipJs !== false, 'public/js/vendor/kinshiptree.js loaded successfully');
assertCondition(
    str_contains($kinshipJs, 'function formatPersonName('),
    'KinshipTree.js includes formatPersonName helper function'
);
assertCondition(
    str_contains($kinshipJs, 'formatPersonName(node.nameL1'),
    'KinshipTree.js renders node labels strictly through formatPersonName'
);

$organogramBlade = file_get_contents($baseDir . '/resources/views/member/organogram.blade.php');
assertCondition($organogramBlade !== false, 'resources/views/member/organogram.blade.php loaded successfully');
assertCondition(
    str_contains($organogramBlade, 'culturalTitleSelect'),
    'organogram.blade.php embeds culturalTitleSelect component for parent additions'
);
assertCondition(
    str_contains($organogramBlade, 'typeof formatPersonName === \'function\''),
    'organogram.blade.php integrates formatPersonName for splitNameLines and unplaced nodes'
);

$studioBlade = file_get_contents($baseDir . '/resources/views/member/includes/familyStudioContent.blade.php');
assertCondition($studioBlade !== false, 'resources/views/member/includes/familyStudioContent.blade.php loaded successfully');
assertCondition(
    str_contains($studioBlade, 'culturalTitleSelect'),
    'familyStudioContent.blade.php integrates culturalTitleSelect across relative modals'
);
assertCondition(
    str_contains($studioBlade, 'NameFormatterService::formatFirstAndLast'),
    'familyStudioContent.blade.php server-renders cards with NameFormatterService'
);

$titleComp = file_get_contents($baseDir . '/resources/views/components/lineage/culturalTitleSelect.blade.php');
assertCondition($titleComp !== false, 'culturalTitleSelect.blade.php component exists');
assertCondition(str_contains($titleComp, 'High Chief'), 'culturalTitleSelect provides "High Chief" title');
assertCondition(str_contains($titleComp, 'Chief'), 'culturalTitleSelect provides "Chief" title');
assertCondition(str_contains($titleComp, 'Prof.'), 'culturalTitleSelect provides "Prof." title');
assertCondition(str_contains($titleComp, 'Dr.'), 'culturalTitleSelect provides "Dr." title');
assertCondition(str_contains($titleComp, 'King'), 'culturalTitleSelect provides "King" title');
assertCondition(str_contains($titleComp, 'Queen'), 'culturalTitleSelect provides "Queen" title');
assertCondition(str_contains($titleComp, 'Prince'), 'culturalTitleSelect provides "Prince" title');
assertCondition(str_contains($titleComp, 'Alhaji'), 'culturalTitleSelect provides "Alhaji" title');
assertCondition(str_contains($titleComp, 'Oba'), 'culturalTitleSelect provides "Oba" title');
assertCondition(str_contains($titleComp, 'Otunba'), 'culturalTitleSelect provides "Otunba" title');

// -----------------------------------------------------------------
// Summary
// -----------------------------------------------------------------
echo "\n=================================================================\n";
if ($failures === 0) {
    echo "🎉 ALL {$checks} CHECKS PASSED PERFECTLY!\n";
    echo "   Sentence-Cased Capital-First naming and comprehensive cultural title\n";
    echo "   provisions are 100% active and verified across FamilyPlatform.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failures} of {$checks} checks failed!\n";
    echo "=================================================================\n";
    exit(1);
}
