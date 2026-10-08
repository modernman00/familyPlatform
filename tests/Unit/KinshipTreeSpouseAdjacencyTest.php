<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class KinshipTreeSpouseAdjacencyTest extends TestCase
{
    /**
     * Test that KinshipLayoutEngine guarantees spousal adjacency and prevents
     * siblings from being interleaved between a member and their spouse.
     */
    public function testSpouseIsImmediatelyAdjacentToMemberAndNeverSeparatedBySibling(): void
    {
        $nodeScript = <<<'JS'
const fs = require("fs");
const code = fs.readFileSync("public/js/vendor/kinshiptree.js", "utf8");
const window = { innerWidth: 1200 };
const document = {};
eval(code);

const rawNodes = [
  { id: 20, name: "OLUYOMI OLAOGUN", gender: "male", pids: [21] },
  { id: 21, name: "IYABO OLAOGUN", gender: "female", pids: [20] },
  { id: 39, name: "Sola Olaogun", gender: "male", fid: 20, mid: 21, pids: [] },
  { id: 42, name: "Wale Olaogun", gender: "male", fid: 20, mid: 21, pids: [62] },
  { id: 38, name: "Temitope Olaogun", gender: "female", fid: 20, mid: 21, pids: [] },
  { id: 62, name: "Ajibike Olaogun", gender: "female", pids: [42] },
  { id: 41, name: "Olajumoke Eniola Olaogun", gender: "female", fid: 42, mid: 62, pids: [] },
  { id: 56, name: "Oladele Olaogun", gender: "female", fid: 42, mid: 62, pids: [] },
  { id: 63, name: "Olutobi Olaogun", gender: "male", fid: 42, mid: 62, pids: [] }
];

const engine = new window.KinshipTree.LayoutEngine({ rootId: "42" });
const result = engine.computeLayout(rawNodes);

const gen1 = result.nodes.filter(n => n.gen === 1).sort((a,b) => a.x - b.x);
console.log(JSON.stringify(gen1.map(n => ({ id: n.id, name: n.name, x: n.x }))));
JS;

        $output = shell_exec('node -e ' . escapeshellarg($nodeScript));
        $this->assertNotEmpty($output, 'Node layout script should produce output');

        /** @var array<int, array{id: int, name: string, x: float}> $gen1Nodes */
        $gen1Nodes = json_decode((string)$output, true);
        $this->assertIsArray($gen1Nodes);
        $this->assertCount(4, $gen1Nodes);

        // Find index of Wale (42), Ajibike (62), and Temitope (38)
        $ids = array_map('strval', array_column($gen1Nodes, 'id'));
        $waleIdx = array_search('42', $ids, true);
        $ajibikeIdx = array_search('62', $ids, true);
        $temitopeIdx = array_search('38', $ids, true);

        $this->assertNotFalse($waleIdx);
        $this->assertNotFalse($ajibikeIdx);
        $this->assertNotFalse($temitopeIdx);

        // Wale and Ajibike MUST be immediately adjacent
        $this->assertSame(1, abs($waleIdx - $ajibikeIdx), 'Wale and Ajibike must be immediately adjacent in generation row');

        // Temitope must NOT be between Wale and Ajibike
        $minCoupleIdx = min($waleIdx, $ajibikeIdx);
        $maxCoupleIdx = max($waleIdx, $ajibikeIdx);
        $this->assertTrue(
            $temitopeIdx < $minCoupleIdx || $temitopeIdx > $maxCoupleIdx,
            'Temitope (sibling) must never be positioned between Wale and Ajibike (spouses)'
        );
    }
}
