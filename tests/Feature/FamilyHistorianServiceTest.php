<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\dto\PostDto;
use App\services\FamilyHistorianCardRenderer;
use App\services\FamilyHistorianService;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class FamilyHistorianServiceTest extends TestCase
{
    private string $famCode = 'TEST_FH_FAM_901';
    private string $otherFamCode = 'OTHER_FH_FAM_902';
    /** @var array<int, string> */
    private array $userIds = ['usr_fh_1', 'usr_fh_2', 'usr_fh_3', 'usr_fh_other'];
    /** @var array<int, string> */
    private array $generatedImages = [];
    private ?PDO $db = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        FamilyHistorianService::ensureTablesExist($this->db);
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        foreach ($this->generatedImages as $img) {
            $path = dirname(__DIR__, 2) . '/resources/images/post/' . basename($img);
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->generatedImages = [];
        parent::tearDown();
    }

    private function cleanup(): void
    {
        if ($this->db === null) {
            return;
        }
        $stmtRuns = $this->db->prepare("DELETE FROM family_historian_runs WHERE family_code IN (?, ?)");
        $stmtRuns->execute([$this->famCode, $this->otherFamCode]);

        $stmtPost = $this->db->prepare("DELETE FROM post WHERE postFamCode IN (?, ?)");
        $stmtPost->execute([$this->famCode, $this->otherFamCode]);

        $stmtUnions = $this->db->prepare("DELETE FROM family_unions WHERE family_code IN (?, ?)");
        $stmtUnions->execute([$this->famCode, $this->otherFamCode]);

        $stmtNodes = $this->db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?)");
        $stmtNodes->execute([$this->famCode, $this->otherFamCode]);

        $ph = implode(',', array_fill(0, count($this->userIds), '?'));
        $this->db->prepare(sprintf("DELETE FROM work WHERE id IN (%s)", $ph))->execute($this->userIds);
        $this->db->prepare(sprintf("DELETE FROM contact WHERE id IN (%s)", $ph))->execute($this->userIds);
        $this->db->prepare(sprintf("DELETE FROM personal WHERE id IN (%s)", $ph))->execute($this->userIds);
    }

    public function test_normalisation_helpers_handle_mixed_inputs(): void
    {
        $this->assertSame(11, FamilyHistorianService::normaliseMonthNumber('Nov'));
        $this->assertSame(11, FamilyHistorianService::normaliseMonthNumber('NOVEMBER'));
        $this->assertSame(11, FamilyHistorianService::normaliseMonthNumber('11'));
        $this->assertSame(6, FamilyHistorianService::normaliseMonthNumber('06'));
        $this->assertNull(FamilyHistorianService::normaliseMonthNumber(''));
        $this->assertNull(FamilyHistorianService::normaliseMonthNumber('invalid'));

        $this->assertNull(FamilyHistorianService::normaliseOccupation('Not specified'));
        $this->assertNull(FamilyHistorianService::normaliseOccupation('None'));
        $this->assertNull(FamilyHistorianService::normaliseOccupation('n/a'));
        $this->assertNull(FamilyHistorianService::normaliseOccupation('sdsd'));
        $this->assertSame('Accountant', FamilyHistorianService::normaliseOccupation('ACCOUNTANT'));
        $this->assertSame('Software & Tech', FamilyHistorianService::normaliseOccupation('software developer'));

        $this->assertSame('United Kingdom', FamilyHistorianService::normaliseCountry('UK'));
        $this->assertSame('United Kingdom', FamilyHistorianService::normaliseCountry('GB'));
        $this->assertSame('United States', FamilyHistorianService::normaliseCountry('USA'));
        $this->assertNull(FamilyHistorianService::normaliseCountry('Not specified'));
    }

    public function test_monthly_birthdays_omits_year_and_age_and_enforces_idempotency(): void
    {
        $db = $this->db;
        $this->assertNotNull($db);

        // Insert two birthday members in November and one in another family
        $insP = $db->prepare(
            "INSERT INTO personal (id, firstName, lastName, famCode, day, month, year) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $insP->execute(['usr_fh_1', 'Ada', 'Okafor', $this->famCode, 14, 'Nov', 1988]);
        $insP->execute(['usr_fh_2', 'Tunde', 'Okafor', $this->famCode, 22, '11', 1995]);
        $insP->execute(['usr_fh_other', 'Secret', 'Stranger', $this->otherFamCode, 14, 'November', 1990]);

        $now = new DateTimeImmutable('2026-11-01 08:00:00');
        $res1 = FamilyHistorianService::runForFamily($this->famCode, $db, $now, false, 'monthly_birthdays');

        $this->assertCount(1, $res1['actions']);
        $action1 = $res1['actions'][0];
        $this->assertSame('published', $action1['status']);
        $postNo = (int)$action1['post_no'];
        $img = (string)$action1['image_filename'];
        $this->generatedImages[] = $img;

        $this->assertNotEmpty($img);
        $imgPath = dirname(__DIR__, 2) . '/resources/images/post/' . $img;
        $this->assertFileExists($imgPath);
        $this->assertNotFalse(@getimagesize($imgPath));

        // Verify post row & privacy invariants
        $pStmt = $db->prepare("SELECT * FROM post WHERE post_no = ?");
        $pStmt->execute([$postNo]);
        $postRow = $pStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($postRow);
        $this->assertSame(FamilyHistorianService::HISTORIAN_ID, $postRow['id']);
        $this->assertSame($this->famCode, $postRow['postFamCode']);
        $this->assertStringContainsString('14 November', (string)$postRow['postMessage']);
        $this->assertStringContainsString('22 November', (string)$postRow['postMessage']);
        $this->assertStringNotContainsString('1988', (string)$postRow['postMessage']);
        $this->assertStringNotContainsString('1995', (string)$postRow['postMessage']);
        $this->assertStringNotContainsString('Secret Stranger', (string)$postRow['postMessage']);

        // Verify PostDto hydration
        $dto = PostDto::fromDatabaseRow($postRow, 'usr_fh_1')->toArray();
        $this->assertTrue($dto['is_historian']);
        $this->assertSame(FamilyHistorianService::HISTORIAN_AVATAR, $dto['profileImg']);
        $this->assertSame([$img], $dto['images']);

        // Second run in same month MUST be idempotent
        $res2 = FamilyHistorianService::runForFamily($this->famCode, $db, $now, false, 'monthly_birthdays');
        $this->assertSame('skipped_already_posted', $res2['actions'][0]['status']);
    }

    public function test_weekly_pillars_and_union_privacy_and_thresholds(): void
    {
        $db = $this->db;
        $this->assertNotNull($db);

        // Empty family must skip below threshold
        $emptyRes = FamilyHistorianService::runForFamily($this->famCode, $db, new DateTimeImmutable('2026-11-16 08:00:00'));
        $this->assertSame('skipped_below_threshold', $emptyRes['actions'][0]['status']);

        // Seed members + occupations + countries
        $insP = $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $insP->execute(['usr_fh_1', 'Amara', 'Bello', $this->famCode]);
        $insP->execute(['usr_fh_2', 'Chidi', 'Bello', $this->famCode]);
        $insP->execute(['usr_fh_3', 'Ngozi', 'Bello', $this->famCode]);

        $insW = $db->prepare("INSERT INTO work (id, employmentStatus, occupation) VALUES (?, 'Employed', ?)");
        $insW->execute(['usr_fh_1', 'ACCOUNTANT']);
        $insW->execute(['usr_fh_2', 'software developer']);
        $insW->execute(['usr_fh_3', 'Not specified']);

        $insC = $db->prepare("INSERT INTO contact (id, email, country, mobile) VALUES (?, ?, ?, '07000000000')");
        $insC->execute(['usr_fh_1', 'amara@example.com', 'UK']);
        $insC->execute(['usr_fh_2', 'chidi@example.com', 'Nigeria']);

        // Seed family_nodes & family_unions (including a divorced union that MUST NEVER appear)
        $insN = $db->prepare(
            "INSERT INTO family_nodes (family_code, first_name, last_name, generation_level, is_erased) VALUES (?, ?, ?, ?, 0)"
        );
        $insN->execute([$this->famCode, 'Amara', 'Bello', 1]);
        $n1 = (int)$db->lastInsertId();
        $insN->execute([$this->famCode, 'Chidi', 'Bello', 1]);
        $n2 = (int)$db->lastInsertId();
        $insN->execute([$this->famCode, 'Zara', 'Bello', 2]);
        $n3 = (int)$db->lastInsertId();
        $insN->execute([$this->famCode, 'ExPartner', 'Hidden', 1]);
        $nEx = (int)$db->lastInsertId();

        $insU = $db->prepare(
            "INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_date, marriage_year, is_current)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $insU->execute([$this->famCode, $n1, $n2, 'married', '2016-11-12', 2016, 1]);
        $insU->execute([$this->famCode, $n1, $nEx, 'divorced', '2005-05-01', 2005, 0]);

        // 1. Occupation & Skills pillar
        $occData = FamilyHistorianService::getOccupationSkillsData($db, $this->famCode);
        $this->assertTrue($occData['eligible']);
        $this->assertSame(2, $occData['total_members']);

        $occRun = FamilyHistorianService::runForFamily($this->famCode, $db, new DateTimeImmutable('2026-11-02 08:00:00'), false, 'occupation_skills');
        $this->assertSame('published', $occRun['actions'][0]['status']);
        $this->generatedImages[] = (string)$occRun['actions'][0]['image_filename'];

        // 2. Family Tree & Roots pillar
        $treeData = FamilyHistorianService::getFamilyTreeRootsData($db, $this->famCode);
        $this->assertTrue($treeData['eligible']);
        $this->assertSame(4, $treeData['total_branches']);
        $this->assertSame(2, $treeData['generation_depth']);
        $this->assertSame(['Nigeria', 'United Kingdom'], $treeData['countries']);

        // 3. Union Milestones pillar — MUST exclude divorced union
        $unionData = FamilyHistorianService::getUnionMilestonesData($db, $this->famCode, 11, 2026);
        $this->assertTrue($unionData['eligible']);
        $this->assertSame(1, $unionData['total_unions']);
        $this->assertSame('Amara Bello & Chidi Bello', $unionData['unions'][0]['couple_label']);
        $this->assertStringNotContainsString('ExPartner', (string)json_encode($unionData));
        $this->assertStringNotContainsString('divorced', strtolower((string)json_encode($unionData)));

        // 4. Activity Milestone pillar (seed 3 posts)
        $insPost = $db->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_likes, post_time, post_status, date_created)
             VALUES ('usr_fh_1', 'Amara Bello', ?, ?, ?, ?, 'published', NOW())"
        );
        $insPost->execute(['First family memory', $this->famCode, 2, (string)(int)(microtime(true) * 1000)]);
        $insPost->execute(['Best Sunday roast together!', $this->famCode, 9, (string)(int)(microtime(true) * 1000)]);
        $insPost->execute(['Third story', $this->famCode, 1, (string)(int)(microtime(true) * 1000)]);

        $actData = FamilyHistorianService::getActivityMilestoneData($db, $this->famCode, new DateTimeImmutable('2026-11-16 08:00:00'));
        $this->assertTrue($actData['eligible']);
        $this->assertSame(3, $actData['total_posts']);
        $this->assertSame(9, $actData['top_score']);
        $this->assertSame('Best Sunday roast together!', $actData['top_excerpt']);
    }

    public function test_activity_spotlight_strips_media_paths_and_bounds_long_tokens(): void
    {
        $db = $this->db;
        $this->assertNotNull($db);

        $insPost = $db->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_likes, post_time, post_status, date_created)
             VALUES ('usr_fh_1', 'Laf Olaogun', ?, ?, ?, ?, 'published', NOW())"
        );
        $nowMs = (string)(int)(microtime(true) * 1000);
        $insPost->execute([
            "/resources/videos/posts/post_vid_937619LAFANE_1794321691_8c820fb6262d.mp4#t=0.1\n/resources/videos/posts/post_vid_937619LAFANE_1794321695_af2ea61556e5.mp4#t=0.1",
            $this->famCode,
            5,
            $nowMs,
        ]);
        $insPost->execute(['Second post', $this->famCode, 1, $nowMs]);
        $insPost->execute(['Third post', $this->famCode, 1, $nowMs]);

        $actData = FamilyHistorianService::getActivityMilestoneData($db, $this->famCode, new DateTimeImmutable('2026-11-16 08:00:00'));
        $this->assertSame('A cherished family moment shared on our timeline.', $actData['top_excerpt']);

        $rendered = FamilyHistorianCardRenderer::renderAndSave(
            'activity',
            [
                'total_posts' => 3,
                'peak_day' => 'Tuesday',
                'top_author' => 'Laf Olaogun',
                'top_score' => 5,
                'top_excerpt' => '/resources/videos/posts/post_vid_937619LAFANE_1794321691_8c820fb6262d.mp4#t=0.1 ' . str_repeat('Supercalifragilistic', 6),
            ],
            $this->famCode,
            'test_overflow_' . time()
        );
        $this->assertNotNull($rendered);
        $this->generatedImages[] = $rendered;
        $imgPath = dirname(__DIR__, 2) . '/resources/images/post/' . $rendered;
        $this->assertFileExists($imgPath);
        $this->assertNotFalse(@getimagesize($imgPath));
    }
}
