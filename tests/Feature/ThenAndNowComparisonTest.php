<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\ThenNowController;
use App\model\PostComparison;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * RFC-0002 "Then & Now": label hygiene, linking two stored photos, batch hydration,
 * answer counts and the sad paths (one photo, bogus parent, empty batch).
 */
final class ThenAndNowComparisonTest extends TestCase
{
    private PDO $pdo;
    /** @var array<int> */
    private array $postNos = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
    }

    protected function tearDown(): void
    {
        foreach ($this->postNos as $no) {
            $this->pdo->prepare('DELETE FROM post_comparisons WHERE post_no = ?')->execute([$no]);
            $this->pdo->prepare('DELETE FROM post WHERE post_no = ?')->execute([$no]);
        }
        parent::tearDown();
    }

    private function makePost(?string $img0, ?string $img1): int
    {
        $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_img0, post_img1, post_status)
             VALUES ('PU_TN_TEST', 'Then Now', 'x', 'PU_TN_FAM', ?, ?, 'published')"
        )->execute([$img0, $img1]);
        $no = (int)$this->pdo->lastInsertId();
        $this->postNos[] = $no;
        return $no;
    }

    public function testCleanLabelStripsTagsTrimsAndCaps(): void
    {
        $this->assertSame('alert(1)1998', PostComparison::cleanLabel('<script>alert(1)</script>1998'));
        $this->assertNull(PostComparison::cleanLabel('   '));
        $this->assertNull(PostComparison::cleanLabel(null));
        $this->assertSame(40, mb_strlen((string)PostComparison::cleanLabel(str_repeat('a', 90))));
    }

    public function testCreateLinksBothStoredPhotosAndHydrates(): void
    {
        $no = $this->makePost('then.jpg', 'now.jpg');
        $this->assertTrue(PostComparison::createForPost($no, '1998', '2026'));

        $row = PostComparison::getForPosts([$no])[$no] ?? null;
        $this->assertNotNull($row);
        $this->assertSame('then.jpg', $row['before_img']);
        $this->assertSame('now.jpg', $row['after_img']);
        $this->assertSame('1998', $row['before_label']);
        $this->assertSame(0, $row['answer_count']);
        $this->assertNull($row['parent_post_no']);
    }

    public function testPipelineStyleColumnsStartingAtImg1AreLinkedInOrder(): void
    {
        $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_img1, post_img2, post_status)
             VALUES ('PU_TN_TEST', 'Then Now', 'x', 'PU_TN_FAM', 'first.jpg', 'second.jpg', 'published')"
        )->execute();
        $no = (int)$this->pdo->lastInsertId();
        $this->postNos[] = $no;

        $this->assertTrue(PostComparison::createForPost($no, null, null));
        $row = PostComparison::getForPosts([$no])[$no];
        $this->assertSame('first.jpg', $row['before_img']);
        $this->assertSame('second.jpg', $row['after_img']);
    }

    public function testSadPathOnlyOnePhotoIsRejected(): void
    {
        $no = $this->makePost('only.jpg', null);
        $this->assertFalse(PostComparison::createForPost($no, null, null));
        $this->assertArrayNotHasKey($no, PostComparison::getForPosts([$no]));
    }

    public function testSadPathMissingPostIsRejected(): void
    {
        $this->assertFalse(PostComparison::createForPost(999999999, null, null));
    }

    public function testAnswersAreCountedAndBogusParentIsDropped(): void
    {
        $parent = $this->makePost('a.jpg', 'b.jpg');
        PostComparison::createForPost($parent, null, null);

        $answer = $this->makePost('c.jpg', 'd.jpg');
        PostComparison::createForPost($answer, null, null, $parent);

        $plainPost = $this->makePost('e.jpg', 'f.jpg');
        $orphan = $this->makePost('g.jpg', 'h.jpg');
        PostComparison::createForPost($orphan, null, null, $plainPost); // not a comparison

        $rows = PostComparison::getForPosts([$parent, $answer, $orphan]);
        $this->assertSame(1, $rows[$parent]['answer_count']);
        $this->assertSame($parent, $rows[$answer]['parent_post_no']);
        $this->assertNull($rows[$orphan]['parent_post_no']);
    }

    public function testEmptyBatchReturnsEmptyArray(): void
    {
        $this->assertSame([], PostComparison::getForPosts([]));
    }

    public function testDuplicateComparisonForSamePostIsRefusedByUniqueKey(): void
    {
        $no = $this->makePost('a.jpg', 'b.jpg');
        $this->assertTrue(PostComparison::createForPost($no, null, null));
        $this->expectException(\PDOException::class);
        PostComparison::createForPost($no, null, null);
    }

    // ── RFC-0003: member timeline ────────────────────────────────────────

    private function makeComparisonPost(string $author, string $fam, string $status = 'published', bool $deleted = false): int
    {
        $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_img1, post_img2, post_status, date_deleted)
             VALUES (?, 'Tl Test', 'x', ?, 'a.jpg', 'b.jpg', ?, " . ($deleted ? 'NOW()' : 'NULL') . ')'
        )->execute([$author, $fam, $status]);
        $no = (int)$this->pdo->lastInsertId();
        $this->postNos[] = $no;
        PostComparison::createForPost($no, null, null);
        return $no;
    }

    private function nos(array $rows): array
    {
        return array_column($rows, 'post_no');
    }

    public function testTimelineShowsAuthoredAndTaggedPostsWithinViewerFamily(): void
    {
        $own = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM');
        $tagged = $this->makeComparisonPost('PU_TL_OTHER', 'PU_TL_FAM');
        $this->pdo->prepare("INSERT INTO post_tags (post_no, user_id, tagged_by) VALUES (?, 'PU_TL_MEMBER', 'PU_TL_OTHER')")->execute([$tagged]);
        $unrelated = $this->makeComparisonPost('PU_TL_OTHER', 'PU_TL_FAM');

        $rows = PostComparison::forMember('PU_TL_MEMBER', 'PU_TL_VIEWER', ['PU_TL_FAM']);
        $this->assertContains($own, $this->nos($rows));
        $this->assertContains($tagged, $this->nos($rows));
        $this->assertNotContains($unrelated, $this->nos($rows));

        $this->pdo->prepare('DELETE FROM post_tags WHERE post_no = ?')->execute([$tagged]);
    }

    public function testTimelineNeverLeaksAnotherFamilyDeletedOrDraftPosts(): void
    {
        $otherFamily = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM_B');
        $deleted = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM', 'published', true);
        $draft = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM', 'new');

        $nos = $this->nos(PostComparison::forMember('PU_TL_MEMBER', 'PU_TL_VIEWER', ['PU_TL_FAM']));
        $this->assertNotContains($otherFamily, $nos, 'cross-family IDOR');
        $this->assertNotContains($deleted, $nos);
        $this->assertNotContains($draft, $nos);
    }

    public function testViewerWithNoFamilyOnlySeesTheirOwnPosts(): void
    {
        $theirs = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM');
        $this->assertNotContains($theirs, $this->nos(PostComparison::forMember('PU_TL_MEMBER', 'PU_TL_VIEWER', [])));
        $this->assertContains($theirs, $this->nos(PostComparison::forMember('PU_TL_MEMBER', 'PU_TL_MEMBER', [])));
    }

    public function testTimelineEmptyIdsAreSafe(): void
    {
        $this->assertSame([], PostComparison::forMember('', 'PU_TL_VIEWER', ['X']));
        $this->assertSame([], PostComparison::forMember('PU_TL_MEMBER', '', ['X']));
    }

    public function testTimelineEndpointRejectsAnonymousAndMalformedIds(): void
    {
        $controller = new ThenNowController();

        $_SESSION = [];
        ob_start();
        $controller->memberTimeline('PU_TL_MEMBER');
        $this->assertSame(401, http_response_code());
        $this->assertStringContainsString('Not authenticated', (string)ob_get_clean());

        $_SESSION = ['id' => 'PU_TL_VIEWER', 'famCode' => 'PU_TL_FAM'];
        ob_start();
        $controller->memberTimeline("x' OR '1'='1");
        $this->assertSame(422, http_response_code());
        ob_end_clean();
    }

    public function testTimelineEndpointReturnsJsonForAuthenticatedViewer(): void
    {
        $no = $this->makeComparisonPost('PU_TL_MEMBER', 'PU_TL_FAM');
        $_SESSION = ['id' => 'PU_TL_VIEWER', 'famCode' => 'PU_TL_FAM', 'famCodes' => ['PU_TL_FAM']];

        ob_start();
        (new ThenNowController())->memberTimeline('PU_TL_MEMBER');
        $body = json_decode((string)ob_get_clean(), true);

        $this->assertSame(200, http_response_code());
        $this->assertSame('success', $body['status']);
        $this->assertContains($no, array_column($body['data']['items'], 'post_no'));
    }
}
