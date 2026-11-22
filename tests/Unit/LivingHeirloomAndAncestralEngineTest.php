<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\AncestralReverseEngine;
use App\services\FamilyPortraitSynthesisService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class LivingHeirloomAndAncestralEngineTest extends TestCase
{
    private string $testFamCode;
    private string $otherFamCode;
    private string $tmpDir;
    /** @var array<int, int> */
    private array $createdNodeIds = [];
    /** @var array<int, int> */
    private array $createdUnionIds = [];
    private ?PDO $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFamCode = 'TEST_ANC_' . bin2hex(random_bytes(4));
        $this->otherFamCode = 'OTHER_ANC_' . bin2hex(random_bytes(4));
        $this->tmpDir = dirname(__DIR__, 2) . '/public/uploads/test_heirloom_' . bin2hex(random_bytes(3));
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if ($this->pdo instanceof PDO) {
            foreach ($this->createdUnionIds as $uid) {
                $stmt = $this->pdo->prepare('DELETE FROM family_node_children WHERE union_id = ?');
                $stmt->execute([$uid]);
                $stmt2 = $this->pdo->prepare('DELETE FROM family_unions WHERE id = ?');
                $stmt2->execute([$uid]);
            }
            foreach ($this->createdNodeIds as $nid) {
                $stmt = $this->pdo->prepare('DELETE FROM family_nodes WHERE id = ?');
                $stmt->execute([$nid]);
            }
        }

        if (is_dir($this->tmpDir)) {
            foreach (glob($this->tmpDir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->tmpDir);
        }
        parent::tearDown();
    }

    private function createSyntheticPortraitBytes(int $r = 185, int $g = 145, int $b = 115): string
    {
        $img = imagecreatetruecolor(120, 140);
        self::assertNotFalse($img);
        $bg = imagecolorallocate($img, 45, 35, 30);
        imagefill($img, 0, 0, $bg);
        $skin = imagecolorallocate($img, $r, $g, $b);
        imagefilledellipse($img, 60, 62, 54, 72, $skin);
        $eye = imagecolorallocate($img, 30, 20, 15);
        imagefilledellipse($img, 48, 54, 6, 4, $eye);
        imagefilledellipse($img, 72, 54, 6, 4, $eye);

        ob_start();
        imagejpeg($img, null, 90);
        $bytes = (string)ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    public function testAgeProgressionAndReEmotionDeterministicFallbacks(): void
    {
        $service = new FamilyPortraitSynthesisService();
        $rawBytes = $this->createSyntheticPortraitBytes();

        $aged = $service->generateAgeProgression($rawBytes, 28, 20, 'Female', ['force_local' => true]);
        self::assertTrue($aged['success']);
        self::assertSame(28, $aged['current_age']);
        self::assertSame(48, $aged['target_age']);
        self::assertSame(20, $aged['years_delta']);
        self::assertSame('sovereign_gd', $aged['tier']);
        self::assertStringStartsWith('data:image/jpeg;base64,', $aged['image_data_url']);

        $reEmotion = $service->generateReEmotionPortrait($rawBytes, 'warm_smile', ['force_local' => true]);
        self::assertTrue($reEmotion['success']);
        self::assertSame('warm_smile', $reEmotion['emotion']);
        self::assertStringStartsWith('data:image/jpeg;base64,', $reEmotion['image_data_url']);
    }

    public function testResolveSafeImageBytesBlocksSsrfAndPathTraversal(): void
    {
        $service = new FamilyPortraitSynthesisService();

        $this->expectException(InvalidArgumentException::class);
        $service->resolveSafeImageBytes('http://127.0.0.1/secret.jpg');
    }

    public function testResolveSafeImageBytesBlocksTraversal(): void
    {
        $service = new FamilyPortraitSynthesisService();

        $this->expectException(InvalidArgumentException::class);
        $service->resolveSafeImageBytes('/../../../etc/passwd');
    }

    public function testAncestralReverseCalibrationAndIdorIsolation(): void
    {
        $this->pdo = Db::connect2();
        $projectRoot = dirname(__DIR__, 2);

        $photo1Path = $this->tmpDir . '/grandparent.jpg';
        $photo2Path = $this->tmpDir . '/child.jpg';
        file_put_contents($photo1Path, $this->createSyntheticPortraitBytes(180, 140, 110));
        file_put_contents($photo2Path, $this->createSyntheticPortraitBytes(184, 144, 114));

        $relPhoto1 = str_replace($projectRoot, '', $photo1Path);
        $relPhoto2 = str_replace($projectRoot, '', $photo2Path);

        // Insert Great-Grandfather (unphotographed) -> Grandparent (photographed) -> Child (photographed)
        $insNode = $this->pdo->prepare(
            'INSERT INTO family_nodes (family_code, first_name, last_name, gender, birth_year, generation_level, avatar_url, is_deceased) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insNode->execute([$this->testFamCode, 'Samuel', 'Olaogun', 'Male', 1898, -2, '', 1]);
        $greatGrandId = (int)$this->pdo->lastInsertId();
        $this->createdNodeIds[] = $greatGrandId;

        $insNode->execute([$this->testFamCode, 'Joseph', 'Olaogun', 'Male', 1930, -1, $relPhoto1, 1]);
        $grandparentId = (int)$this->pdo->lastInsertId();
        $this->createdNodeIds[] = $grandparentId;

        $insNode->execute([$this->testFamCode, 'Tunde', 'Olaogun', 'Male', 1962, 0, $relPhoto2, 0]);
        $childId = (int)$this->pdo->lastInsertId();
        $this->createdNodeIds[] = $childId;

        // Other family node (must NEVER be accessible to $this->testFamCode)
        $insNode->execute([$this->otherFamCode, 'Stranger', 'Node', 'Female', 1910, -2, '', 1]);
        $otherId = (int)$this->pdo->lastInsertId();
        $this->createdNodeIds[] = $otherId;

        $insUnion = $this->pdo->prepare('INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type) VALUES (?, ?, ?, ?)');
        $insUnion->execute([$this->testFamCode, $greatGrandId, 0, 'married']);
        $u1 = (int)$this->pdo->lastInsertId();
        $this->createdUnionIds[] = $u1;

        $insUnion->execute([$this->testFamCode, $grandparentId, 0, 'married']);
        $u2 = (int)$this->pdo->lastInsertId();
        $this->createdUnionIds[] = $u2;

        $insChild = $this->pdo->prepare('INSERT INTO family_node_children (union_id, parent_node_id, child_id, relationship_type) VALUES (?, ?, ?, ?)');
        $insChild->execute([$u1, $greatGrandId, $grandparentId, 'biological']);
        $insChild->execute([$u2, $grandparentId, $childId, 'biological']);

        $engine = new AncestralReverseEngine();
        $inventory = $engine->getTreePortraitInventory($this->pdo, $this->testFamCode);

        self::assertCount(2, $inventory['photographed']);
        self::assertCount(1, $inventory['unphotographed_ancestors']);
        self::assertSame($greatGrandId, $inventory['unphotographed_ancestors'][0]['id']);

        // Run LOOCV Calibration ("Check Closeness to Exact")
        $calibration = $engine->calibrateFamilyTree($this->pdo, $this->testFamCode);
        self::assertSame('calibrated', $calibration['calibration_status']);
        self::assertGreaterThanOrEqual(2, $calibration['calibrated_pairs_count']);
        self::assertGreaterThan(50.0, $calibration['average_closeness_pct']);

        // Run Reverse Ancestral Computation
        $prediction = $engine->predictAncestorPortrait($this->pdo, $this->testFamCode, $greatGrandId, ['force_local' => true]);
        self::assertTrue($prediction['success']);
        self::assertStringStartsWith('data:image/jpeg;base64,', $prediction['image_data_url']);
        self::assertNotEmpty($prediction['reference_relatives']);

        // Cross-family IDOR must be rejected
        $this->expectException(InvalidArgumentException::class);
        $engine->predictAncestorPortrait($this->pdo, $this->testFamCode, $otherId, ['force_local' => true]);
    }
}
