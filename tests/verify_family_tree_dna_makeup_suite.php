<?php

declare(strict_types=1);

/**
 * Sovereign Verification Suite: Family Tree Node DNA Make-Up Engine (Layers 1 & 2)
 *
 * Verifies:
 * 1. Layer 1: Mendelian Shared DNA % and centiMorgan (cM) ranges across Ego (100%),
 *    Parents/Children (50%), Full Siblings (~50%), Half-Siblings (~25%),
 *    Grandparents/Aunts/Uncles/Nieces/Nephews (~25%), First Cousins (~12.5%),
 *    and Non-Biological Relatives (Spouse/In-Laws = 0%).
 * 2. Layer 2A: Zero-Cold-Start Tree-Synthesised Ancestral Founder Lineage Admixture.
 * 3. Layer 2B: Recorded Biogeographical Ethnicity 50/50 Mendelian Inheritance across
 *    generations, Full-Sibling Parity, Y-DNA Patrilineal & mtDNA Matrilineal propagation,
 *    and XSS / Malicious Payload Neutralisation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\KinshipCalculatorService;

$passed = 0;
$failed = 0;

function assertTrue(bool $cond, string $msg): void
{
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "  ✅ PASS: {$msg}\n";
    } else {
        $failed++;
        echo "  ❌ FAIL: {$msg}\n";
    }
}

echo "=================================================================\n";
echo "🧬 FAMILY TREE NODE DNA MAKE-UP SUITE (LAYERS 1 & 2)\n";
echo "=================================================================\n";

// Construct a 3-Generation Multi-Branch Family Graph:
// - Paternal Grandfather (1) + Paternal Grandmother (2) -> Union 100 -> Father (3), Uncle (4)
// - Father (3) [Recorded: 80% Nigerian (Yoruba), 20% Ghanaian, Y-DNA: E-M2]
//   + Mother (5) [Recorded: 50% Nigerian (Yoruba), 50% British & Irish, mtDNA: L3b]
//   -> Union 101 -> Ego (6, Male), Full Sister (7, Female)
// - Father (3) + Second Partner (8) -> Union 102 -> Half-Brother (9, Male)
// - Uncle (4) + Aunt-in-Law (10) -> Union 103 -> First Cousin (11, Male)
// - Ego (6) + Wife (12) [Recorded: 100% Scottish & Welsh] -> Union 104 -> Son (13, Male)

$nodes = [
    [
        'id' => 1,
        'first_name' => 'Adesanya',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => -2,
    ],
    [
        'id' => 2,
        'first_name' => 'Iyabo',
        'last_name' => 'Olaogun',
        'maiden_name' => 'Adebayo',
        'gender' => 'Female',
        'generation_level' => -2,
    ],
    [
        'id' => 3,
        'first_name' => 'Babatunde',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => -1,
        'dna_ethnicity_json' => json_encode([
            'regions' => [
                ['region' => 'Nigerian (Yoruba)', 'percentage' => 80, 'color' => '#10b981'],
                ['region' => 'Ghanaian & West African', 'percentage' => 20, 'color' => '#f59e0b'],
            ],
            'haplogroup_y' => 'E-M2',
        ]),
    ],
    [
        'id' => 4,
        'first_name' => 'Olusegun',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => -1,
    ],
    [
        'id' => 5,
        'first_name' => 'Folake',
        'last_name' => 'Olaogun',
        'maiden_name' => 'Balogun',
        'gender' => 'Female',
        'generation_level' => -1,
        'dna_ethnicity_json' => json_encode([
            'regions' => [
                ['region' => 'Nigerian (Yoruba)<script>alert(1)</script>', 'percentage' => 50, 'color' => 'javascript:evil'],
                ['region' => 'British & Irish', 'percentage' => 50, 'color' => '#3b82f6'],
            ],
            'haplogroup_mt' => 'L3b<img onerror=alert(1)>',
        ]),
    ],
    [
        'id' => 6,
        'first_name' => 'Wale',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => 0,
    ],
    [
        'id' => 7,
        'first_name' => 'Eniola',
        'last_name' => 'Olaogun',
        'gender' => 'Female',
        'generation_level' => 0,
    ],
    [
        'id' => 8,
        'first_name' => 'Bisi',
        'last_name' => 'Ogunleye',
        'gender' => 'Female',
        'generation_level' => -1,
    ],
    [
        'id' => 9,
        'first_name' => 'Damilola',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => 0,
    ],
    [
        'id' => 10,
        'first_name' => 'Funke',
        'last_name' => 'Olaogun',
        'maiden_name' => 'Coker',
        'gender' => 'Female',
        'generation_level' => -1,
    ],
    [
        'id' => 11,
        'first_name' => 'Tobi',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => 0,
    ],
    [
        'id' => 12,
        'first_name' => 'Fiona',
        'last_name' => 'Olaogun',
        'maiden_name' => 'Campbell',
        'gender' => 'Female',
        'generation_level' => 0,
        'dna_ethnicity_json' => json_encode([
            'regions' => [
                ['region' => 'Scottish & Welsh', 'percentage' => 100, 'color' => '#06b6d4'],
            ],
        ]),
    ],
    [
        'id' => 13,
        'first_name' => 'Kaelen',
        'last_name' => 'Olaogun',
        'gender' => 'Male',
        'generation_level' => 1,
    ],
];

$unions = [
    ['id' => 100, 'partner_1_id' => 1, 'partner_2_id' => 2, 'is_current' => 1, 'union_type' => 'married'],
    ['id' => 101, 'partner_1_id' => 3, 'partner_2_id' => 5, 'is_current' => 1, 'union_type' => 'married'],
    ['id' => 102, 'partner_1_id' => 3, 'partner_2_id' => 8, 'is_current' => 0, 'union_type' => 'divorced'],
    ['id' => 103, 'partner_1_id' => 4, 'partner_2_id' => 10, 'is_current' => 1, 'union_type' => 'married'],
    ['id' => 104, 'partner_1_id' => 6, 'partner_2_id' => 12, 'is_current' => 1, 'union_type' => 'married'],
];

$children = [
    ['union_id' => 100, 'child_id' => 3],
    ['union_id' => 100, 'child_id' => 4],
    ['union_id' => 101, 'child_id' => 6],
    ['union_id' => 101, 'child_id' => 7],
    ['union_id' => 102, 'child_id' => 9],
    ['union_id' => 103, 'child_id' => 11],
    ['union_id' => 104, 'child_id' => 13],
];

$decorated = KinshipCalculatorService::calculateEgoKinship([
    'nodes' => $nodes,
    'unions' => $unions,
    'children' => $children,
], 6);

// 1. Verify Layer 1 Shared DNA % & cM Consanguinity
assertTrue($decorated[6]['shared_dna_pct'] === 100.0 && $decorated[6]['shared_cm_avg'] === 6800, 'Ego (Node 6) has 100% Reference DNA (6,800 cM)');
assertTrue($decorated[3]['shared_dna_pct'] === 50.0 && $decorated[3]['shared_dna_formatted'] === '50%', 'Father (Node 3) has exact 50% Shared DNA');
assertTrue($decorated[5]['shared_dna_pct'] === 50.0 && $decorated[5]['shared_dna_formatted'] === '50%', 'Mother (Node 5) has exact 50% Shared DNA');
assertTrue($decorated[13]['shared_dna_pct'] === 50.0 && $decorated[13]['shared_dna_formatted'] === '50%', 'Son (Node 13) has exact 50% Shared DNA');
assertTrue($decorated[7]['shared_dna_pct'] === 50.0 && $decorated[7]['shared_dna_formatted'] === '~50%', 'Full Sister (Node 7) has ~50% Shared DNA (2,613 cM avg)');
assertTrue($decorated[9]['shared_dna_pct'] === 25.0 && $decorated[9]['shared_dna_formatted'] === '~25%', 'Half-Brother (Node 9) has ~25% Shared DNA');
assertTrue($decorated[1]['shared_dna_pct'] === 25.0 && $decorated[2]['shared_dna_pct'] === 25.0, 'Paternal Grandparents (Nodes 1 & 2) have ~25% Shared DNA');
assertTrue($decorated[4]['shared_dna_pct'] === 25.0, 'Paternal Uncle (Node 4) has ~25% Shared DNA');
assertTrue($decorated[11]['shared_dna_pct'] === 12.5 && $decorated[11]['shared_dna_formatted'] === '~12.5%', 'First Cousin (Node 11) has ~12.5% Shared DNA');
assertTrue($decorated[12]['shared_dna_pct'] === 0.0 && $decorated[12]['is_biological_kin'] === false, 'Wife (Node 12) has 0% Shared Autosomal DNA (is_biological_kin = false)');
assertTrue($decorated[8]['shared_dna_pct'] === 0.0 && $decorated[8]['is_biological_kin'] === false, 'Step-mother / Father\'s other partner (Node 8) has 0% Shared DNA');

// 2. Verify Layer 2 Mendelian 50/50 Biogeographical Inheritance
// Father (3): 80% Nigerian (Yoruba), 20% Ghanaian
// Mother (5): 50% Nigerian (Yoruba), 50% British & Irish
// Expected Ego (6) & Full Sister (7):
// - Nigerian (Yoruba): 0.5*80 + 0.5*50 = 65%
// - British & Irish: 0.5*50 = 25%
// - Ghanaian & West African: 0.5*20 = 10%
$egoMakeup = $decorated[6]['dna_makeup'];
$egoRegionsByKey = [];
foreach ($egoMakeup['active_regions'] as $r) {
    $egoRegionsByKey[$r['region']] = $r['percentage'];
}

assertTrue($egoMakeup['mode'] === 'ethnicity' && $egoMakeup['source'] === 'inherited_dna', 'Ego DNA make-up is automatically synthesised from Parents (mode=ethnicity, source=inherited_dna)');
assertTrue(($egoRegionsByKey['Nigerian (Yoruba)'] ?? 0.0) === 65.0, 'Ego inherited 65.0% Nigerian (Yoruba) (40% Father + 25% Mother)');
assertTrue(($egoRegionsByKey['British & Irish'] ?? 0.0) === 25.0, 'Ego inherited 25.0% British & Irish from Mother');
assertTrue(($egoRegionsByKey['Ghanaian & West African'] ?? 0.0) === 10.0, 'Ego inherited 10.0% Ghanaian & West African from Father');

// 3. Verify Second-Generation Cascade into Ego's Son (Node 13)
// Ego (6): 65% Nigerian (Yoruba), 25% British & Irish, 10% Ghanaian
// Wife (12): 100% Scottish & Welsh
// Expected Son (13): 50% Scottish & Welsh, 32.5% Nigerian (Yoruba), 12.5% British & Irish, 5% Ghanaian
$sonRegionsByKey = [];
foreach ($decorated[13]['dna_makeup']['active_regions'] as $r) {
    $sonRegionsByKey[$r['region']] = $r['percentage'];
}
assertTrue(($sonRegionsByKey['Scottish & Welsh'] ?? 0.0) === 50.0, 'Son (Node 13) inherited 50.0% Scottish & Welsh from Mother');
assertTrue(($sonRegionsByKey['Nigerian (Yoruba)'] ?? 0.0) === 32.5, 'Son (Node 13) inherited 32.5% Nigerian (Yoruba) across 2 generational cascades');
assertTrue(($sonRegionsByKey['British & Irish'] ?? 0.0) === 12.5, 'Son (Node 13) inherited 12.5% British & Irish across 2 generational cascades');

// 4. Verify Uniparental Haplogroup Propagation (Y-DNA & mtDNA)
assertTrue($decorated[1]['dna_makeup']['haplogroup_y'] === 'E-M2', 'Y-DNA E-M2 propagated upward from Father (3) to Paternal Grandfather (1)');
assertTrue($decorated[4]['dna_makeup']['haplogroup_y'] === 'E-M2', 'Y-DNA E-M2 propagated to Paternal Uncle (4) via Paternal Grandfather');
assertTrue($decorated[6]['dna_makeup']['haplogroup_y'] === 'E-M2', 'Y-DNA E-M2 propagated downward to Ego (6)');
assertTrue($decorated[13]['dna_makeup']['haplogroup_y'] === 'E-M2', 'Y-DNA E-M2 propagated downward to Grandson (13)');
assertTrue($decorated[7]['dna_makeup']['haplogroup_y'] === null, 'Female nodes (Sister 7) do not carry Y-DNA haplogroup');
assertTrue(str_starts_with((string)$decorated[6]['dna_makeup']['haplogroup_mt'], 'L3b') && !str_contains((string)$decorated[6]['dna_makeup']['haplogroup_mt'], '<'), 'mtDNA L3b propagated from Mother (5) to Ego (6) with XSS stripped');
assertTrue(str_starts_with((string)$decorated[7]['dna_makeup']['haplogroup_mt'], 'L3b'), 'mtDNA L3b propagated from Mother (5) to Full Sister (7)');

// 5. Verify Zero-Cold-Start Tree Founder Lineage Make-up on Unrecorded Ancestors
// Uncle (4) has parents Adesanya Olaogun (1) & Iyabo Adebayo (2)
$uncleLineage = $decorated[4]['dna_makeup']['lineage_regions'];
assertTrue(count($uncleLineage) === 2 && $uncleLineage[0]['percentage'] === 50.0 && $uncleLineage[1]['percentage'] === 50.0, 'Uncle (Node 4) has 50/50 Tree Founder Lineage Make-up from Paternal Grandfather & Grandmother');

// 6. Verify XSS & Malicious Colour Sanitisation
foreach ($decorated[5]['dna_makeup']['active_regions'] as $reg) {
    assertTrue(!str_contains($reg['region'], '<script>'), 'XSS script tags stripped from region names');
    assertTrue((bool)preg_match('/^#[0-9a-fA-F]{6}$/', $reg['color']), 'Malicious colour string replaced with safe hex colour token');
}

echo "-----------------------------------------------------------------\n";
echo "Results: {$passed} passed, {$failed} failed.\n";
if ($failed > 0) {
    exit(1);
}
echo "🏆 ALL FAMILY TREE DNA MAKE-UP ASSERTIONS PASSED!\n";
