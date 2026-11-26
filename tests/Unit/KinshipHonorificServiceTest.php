<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\KinshipCalculatorService;
use App\services\KinshipHonorificService;
use PHPUnit\Framework\TestCase;

final class KinshipHonorificServiceTest extends TestCase
{
    /**
     * Build a realistic 4-generation Olaogun family graph:
     * - Node 1: Pa Samuel Olaogun (Male) — Great-Grandfather
     * - Node 2: Oluyomi Olaogun (Male) + Node 3: Adeola Olaogun (Female) — Grandparents
     * - Node 4: Adesoji Olaogun (Male, Brother of Oluyomi's son Wale) + Node 5: Funke Olaogun (Female, Adesoji's Wife)
     * - Node 6: Wale Olaogun (Male, Son of Oluyomi & Adeola) + Node 7: Bisi Olaogun (Female, Wale's Wife)
     * - Node 8: Tobi Olaogun (Male, Son of Wale & Bisi — Grandson of Oluyomi)
     * - Node 9: Dami Olaogun (Female, Daughter of Adesoji & Funke — First Cousin of Tobi)
     *
     * @return array{
     *     nodes: list<array<string, mixed>>,
     *     unions: list<array<string, mixed>>,
     *     children: list<array<string, mixed>>
     * }
     */
    private function sampleFamilyGraph(): array
    {
        return [
            'nodes' => [
                ['id' => 1, 'user_id' => 'u_samuel', 'first_name' => 'Samuel', 'last_name' => 'Olaogun', 'gender' => 'Male', 'generation_level' => -3],
                ['id' => 2, 'user_id' => 'u_oluyomi', 'first_name' => 'Oluyomi', 'last_name' => 'Olaogun', 'gender' => 'Male', 'generation_level' => -2],
                ['id' => 3, 'user_id' => 'u_adeola', 'first_name' => 'Adeola', 'last_name' => 'Olaogun', 'gender' => 'Female', 'generation_level' => -2],
                ['id' => 4, 'user_id' => 'u_adesoji', 'first_name' => 'Adesoji', 'last_name' => 'Olaogun', 'gender' => 'Male', 'generation_level' => -1],
                ['id' => 5, 'user_id' => 'u_funke', 'first_name' => 'Funke', 'last_name' => 'Olaogun', 'gender' => 'Female', 'generation_level' => -1],
                ['id' => 6, 'user_id' => 'u_wale', 'first_name' => 'Wale', 'last_name' => 'Olaogun', 'gender' => 'Male', 'generation_level' => -1],
                ['id' => 7, 'user_id' => 'u_bisi', 'first_name' => 'Bisi', 'last_name' => 'Olaogun', 'gender' => 'Female', 'generation_level' => -1],
                ['id' => 8, 'user_id' => 'u_tobi', 'first_name' => 'Tobi', 'last_name' => 'Olaogun', 'gender' => 'Male', 'generation_level' => 0],
                ['id' => 9, 'user_id' => 'u_dami', 'first_name' => 'Dami', 'last_name' => 'Olaogun', 'gender' => 'Female', 'generation_level' => 0],
            ],
            'unions' => [
                ['id' => 100, 'partner_1_id' => 1, 'partner_2_id' => 1, 'union_type' => 'married', 'is_current' => 1],
                ['id' => 101, 'partner_1_id' => 2, 'partner_2_id' => 3, 'union_type' => 'married', 'is_current' => 1],
                ['id' => 102, 'partner_1_id' => 4, 'partner_2_id' => 5, 'union_type' => 'married', 'is_current' => 1],
                ['id' => 103, 'partner_1_id' => 6, 'partner_2_id' => 7, 'union_type' => 'married', 'is_current' => 1],
            ],
            'children' => [
                ['union_id' => 100, 'child_id' => 2, 'relationship_type' => 'biological'],
                ['union_id' => 101, 'child_id' => 4, 'relationship_type' => 'biological'],
                ['union_id' => 101, 'child_id' => 6, 'relationship_type' => 'biological'],
                ['union_id' => 102, 'child_id' => 9, 'relationship_type' => 'biological'],
                ['union_id' => 103, 'child_id' => 8, 'relationship_type' => 'biological'],
            ],
        ];
    }

    public function testSameSubjectProjectsViewerRelativeHonorificAcrossDifferentRecipients(): void
    {
        $graph = $this->sampleFamilyGraph();

        // 1. Viewed by Grandchild Tobi (Node 8) -> Oluyomi (Node 2) is Grandpa, Samuel (Node 1) is Great-Grandpa
        $fromTobi = KinshipCalculatorService::calculateEgoKinship($graph, 8);
        $this->assertSame('Paternal Grandfather', $fromTobi[2]['ego_relation']);
        $this->assertSame('Grandpa', KinshipHonorificService::mapRelationToHonorific($fromTobi[2]['ego_relation'], 'u_tobi', 'u_oluyomi'));
        $this->assertSame(
            'Grandpa Oluyomi Olaogun',
            KinshipHonorificService::formatDisplayName('Oluyomi Olaogun', 'Grandpa')
        );

        $this->assertSame('Great-Grandfather', $fromTobi[1]['ego_relation']);
        $this->assertSame('Great-Grandpa', KinshipHonorificService::mapRelationToHonorific($fromTobi[1]['ego_relation'], 'u_tobi', 'u_samuel'));

        // Uncle Adesoji (Node 4) &Uncle's Wife Funke (Node 5 — marital Aunty!)
        $this->assertSame('Paternal Uncle', $fromTobi[4]['ego_relation']);
        $this->assertSame('Uncle', KinshipHonorificService::mapRelationToHonorific($fromTobi[4]['ego_relation'], 'u_tobi', 'u_adesoji'));

        $this->assertSame('Paternal Aunt', $fromTobi[5]['ego_relation']);
        $this->assertSame(0.0, $fromTobi[5]['shared_dna_pct'], 'Spouse of Uncle must remain 0% non-biological kin');
        $this->assertSame('Aunty', KinshipHonorificService::mapRelationToHonorific($fromTobi[5]['ego_relation'], 'u_tobi', 'u_funke'));

        // Same-generation First Cousin Dami (Node 9) -> NO elder honorific
        $this->assertSame('Paternal First Cousin', $fromTobi[9]['ego_relation']);
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific($fromTobi[9]['ego_relation'], 'u_tobi', 'u_dami'));

        // 2. Viewed by Son Wale (Node 6) -> Oluyomi (Node 2) is Dad, Adeola (Node 3) is Mum, Adesoji (Node 4) is Brother (plain)
        $fromWale = KinshipCalculatorService::calculateEgoKinship($graph, 6);
        $this->assertSame('Dad', KinshipHonorificService::mapRelationToHonorific($fromWale[2]['ego_relation'], 'u_wale', 'u_oluyomi'));
        $this->assertSame('Mum', KinshipHonorificService::mapRelationToHonorific($fromWale[3]['ego_relation'], 'u_wale', 'u_adeola'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific($fromWale[4]['ego_relation'], 'u_wale', 'u_adesoji'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific($fromWale[8]['ego_relation'], 'u_wale', 'u_tobi'), 'Younger son never gets an elder prefix');

        // 3. Viewed by Daughter-in-law Bisi (Node 7) -> Oluyomi (Node 2) is Father-in-law -> Dad, Wale (Node 6) is Husband -> plain
        $fromBisi = KinshipCalculatorService::calculateEgoKinship($graph, 7);
        $this->assertSame('Father-in-law', $fromBisi[2]['ego_relation']);
        $this->assertSame('Dad', KinshipHonorificService::mapRelationToHonorific($fromBisi[2]['ego_relation'], 'u_bisi', 'u_oluyomi'));
        $this->assertSame('Mother-in-law', $fromBisi[3]['ego_relation']);
        $this->assertSame('Mum', KinshipHonorificService::mapRelationToHonorific($fromBisi[3]['ego_relation'], 'u_bisi', 'u_adeola'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific($fromBisi[6]['ego_relation'], 'u_bisi', 'u_wale'));

        // 4. Viewed by Wife Adeola (Node 3) -> Oluyomi (Node 2) is Husband -> plain
        $fromAdeola = KinshipCalculatorService::calculateEgoKinship($graph, 3);
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific($fromAdeola[2]['ego_relation'], 'u_adeola', 'u_oluyomi'));

        // 5. Viewed by Oluyomi himself (Node 2) -> Self-view always returns null honorific
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Father', 'u_oluyomi', 'u_oluyomi'));
    }

    public function testStepParentsAndDeepAncestorsMapCleanly(): void
    {
        $this->assertSame('Dad', KinshipHonorificService::mapRelationToHonorific('Stepfather'));
        $this->assertSame('Mum', KinshipHonorificService::mapRelationToHonorific('Stepmother'));
        $this->assertSame('Dad', KinshipHonorificService::mapRelationToHonorific("Eniola's Father"));
        $this->assertSame('Mum', KinshipHonorificService::mapRelationToHonorific("Eniola's Mother"));
        $this->assertSame('Great-Uncle', KinshipHonorificService::mapRelationToHonorific('Great-Uncle'));
        $this->assertSame('Great-Aunty', KinshipHonorificService::mapRelationToHonorific('Great-Aunt'));
        $this->assertSame('Great-Great-Grandpa', KinshipHonorificService::mapRelationToHonorific('Great-Great-Grandfather'));
        $this->assertSame('3rd Great-Grandma', KinshipHonorificService::mapRelationToHonorific('3rd Great-Grandmother'));

        // Former/late spouses, siblings-in-law, and nieces/nephews never get elder prefixes
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Former Husband'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Brother-in-law'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Sister-in-law'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Nephew'));
        $this->assertNull(KinshipHonorificService::mapRelationToHonorific('Granddaughter'));
    }

    public function testFormatDisplayNameAndAmbiguityGuard(): void
    {
        $this->assertSame('Grandpa Oluyomi Olaogun', KinshipHonorificService::formatDisplayName('Oluyomi Olaogun', 'Grandpa'));
        $this->assertSame('Grandpa Oluyomi Olaogun', KinshipHonorificService::formatDisplayName('Grandpa Oluyomi Olaogun', 'Grandpa'));
        $this->assertSame('Grandpa Chief Oluyomi Olaogun', KinshipHonorificService::formatDisplayName('Chief Oluyomi Olaogun', 'Grandpa'));
        $this->assertSame('Oluyomi Olaogun', KinshipHonorificService::formatDisplayName('Oluyomi Olaogun', null));

        $this->assertSame('on', KinshipHonorificService::normalisePreference(null));
        $this->assertSame('on', KinshipHonorificService::normalisePreference('on'));
        $this->assertSame('off', KinshipHonorificService::normalisePreference('off'));
        $this->assertSame('off', KinshipHonorificService::normalisePreference('0'));

        // Same-name ambiguity guard neutralises automatic honorific until disambiguated by post_tags
        $indexed = KinshipHonorificService::indexEnrichedRosterByName([
            [
                'id' => 'u_sr',
                'fullName' => 'Oluyomi Olaogun',
                'kinship' => 'Paternal Grandfather',
                'honorific' => 'Grandpa',
                'displayFullName' => 'Grandpa Oluyomi Olaogun',
            ],
            [
                'id' => 'u_jr',
                'fullName' => 'Oluyomi Olaogun',
                'kinship' => 'Brother',
                'honorific' => null,
                'displayFullName' => 'Oluyomi Olaogun',
            ],
        ]);

        $this->assertTrue($indexed['oluyomi olaogun']['ambiguous'] ?? false);
        $this->assertNull($indexed['oluyomi olaogun']['honorific']);
        $this->assertSame('Oluyomi Olaogun', $indexed['oluyomi olaogun']['displayFullName']);
    }
}
