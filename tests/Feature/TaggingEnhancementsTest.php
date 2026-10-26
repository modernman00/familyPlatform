<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\TagController;
use App\repository\FeedRepository;
use Tests\Support\SocialFeedTestCase;

final class TaggingEnhancementsTest extends SocialFeedTestCase
{
    private string $relativeId = '';
    private string $siblingId = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->relativeId = 'REL_' . bin2hex(random_bytes(6));
        $this->siblingId = 'SIB_' . bin2hex(random_bytes(6));

        // Seed personal records for author, sibling and relative
        $insertPersonal = $this->pdo->prepare("
            INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
            VALUES (?, ?, ?, ?, 1, 'January', 1990)
        ");
        $insertPersonal->execute([$this->authorId, 'Author', 'Olaogun', $this->famCode]);
        $insertPersonal->execute([$this->siblingId, 'Sibling', 'Olaogun', $this->famCode]);
        $insertPersonal->execute([$this->relativeId, 'Cousin', 'Olaogun', $this->famCode]);

        // Link sibling relationship in sibling table
        $insertSibling = $this->pdo->prepare("
            INSERT INTO sibling (id, famCode, sibling_name, sibling_linked)
            VALUES (?, ?, 'Sibling Olaogun', ?)
        ");
        $insertSibling->execute([$this->authorId, $this->famCode, $this->siblingId]);
    }

    protected function tearDown(): void
    {
        $cleanupUids = [$this->authorId, $this->siblingId, $this->relativeId];
        $in = implode(',', array_fill(0, count($cleanupUids), '?'));

        $this->pdo->prepare("DELETE FROM sibling WHERE id IN ($in) OR sibling_linked IN ($in)")
            ->execute([...$cleanupUids, ...$cleanupUids]);
        $this->pdo->prepare("DELETE FROM personal WHERE id IN ($in)")
            ->execute($cleanupUids);

        parent::tearDown();
    }

    public function testFeedRepositoryHydratesTagsWithKinshipLabels(): void
    {
        $postNo = $this->seedPost($this->authorId, $this->famCode, 'Celebration with family!');

        // Tag the viewer (author) and the sibling
        $insertTag = $this->pdo->prepare("
            INSERT INTO post_tags (post_no, user_id, tagged_by, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insertTag->execute([$postNo, $this->authorId, $this->authorId]);
        $insertTag->execute([$postNo, $this->siblingId, $this->authorId]);

        $feedRepo = new FeedRepository($this->pdo);
        $posts = $feedRepo->getFeedPostsAsArray($this->authorId, [$this->famCode], 10, 0);

        $matchingPost = null;
        foreach ($posts as $p) {
            if ((int)$p['post_no'] === $postNo) {
                $matchingPost = $p;
                break;
            }
        }

        $this->assertNotNull($matchingPost, 'Seeded post must be present in feed');
        $tags = $matchingPost['post_tags'];
        $this->assertCount(2, $tags);

        $tagMap = [];
        foreach ($tags as $t) {
            $tagMap[$t['user_id']] = $t['kinship'];
        }

        $this->assertSame('You', $tagMap[$this->authorId], 'Viewer tag must resolve to kinship label "You"');
        $this->assertSame('Sibling', $tagMap[$this->siblingId], 'Linked sibling must resolve to kinship label "Sibling"');
    }

    public function testFeedRepositoryFiltersByTaggedStream(): void
    {
        $postTagged = $this->seedPost($this->authorId, $this->famCode, 'Memory featuring viewer');
        $postNotTagged = $this->seedPost($this->authorId, $this->famCode, 'Generic family announcement');

        // Only tag viewer in postTagged
        $insertTag = $this->pdo->prepare("
            INSERT INTO post_tags (post_no, user_id, tagged_by, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insertTag->execute([$postTagged, $this->authorId, $this->authorId]);

        $feedRepo = new FeedRepository($this->pdo);

        // 1. Tagged stream filter
        $taggedOnly = $feedRepo->getFeedPostsAsArray($this->authorId, [$this->famCode], 10, 0, 'tagged');
        $taggedPostNos = array_column($taggedOnly, 'post_no');

        $this->assertContains($postTagged, $taggedPostNos, 'Tagged stream must contain post featuring viewer');
        $this->assertNotContains($postNotTagged, $taggedPostNos, 'Tagged stream must NOT contain untagged post');

        // 2. All posts stream
        $allPosts = $feedRepo->getFeedPostsAsArray($this->authorId, [$this->famCode], 10, 0, 'all');
        $allPostNos = array_column($allPosts, 'post_no');

        $this->assertContains($postTagged, $allPostNos);
        $this->assertContains($postNotTagged, $allPostNos);
    }

    public function testCountFeedPostsWithTaggedFilter(): void
    {
        $postTagged = $this->seedPost($this->authorId, $this->famCode, 'Memory with tag');
        $this->seedPost($this->authorId, $this->famCode, 'Memory without tag');

        $insertTag = $this->pdo->prepare("
            INSERT INTO post_tags (post_no, user_id, tagged_by, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insertTag->execute([$postTagged, $this->authorId, $this->authorId]);

        $feedRepo = new FeedRepository($this->pdo);
        $countTagged = $feedRepo->countFeedPosts($this->authorId, [$this->famCode], 'tagged');
        $countAll = $feedRepo->countFeedPosts($this->authorId, [$this->famCode], 'all');

        $this->assertSame(1, $countTagged, 'Only 1 post is tagged with author');
        $this->assertGreaterThanOrEqual(2, $countAll, 'All posts count must reflect both posts');
    }

    public function testTagControllerAllowsSelfUntagging(): void
    {
        $postNo = $this->seedPost($this->authorId, $this->famCode, 'Tagged post');

        $insertTag = $this->pdo->prepare("
            INSERT INTO post_tags (post_no, user_id, tagged_by, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $insertTag->execute([$postNo, $this->siblingId, $this->authorId]);

        // Sibling logs in and untags themselves
        $_SESSION['id'] = $this->siblingId;
        $_SESSION['famCode'] = $this->famCode;

        $tagController = new TagController($this->pdo);

        // Execute removal
        $tagController->removePostTag($postNo, $this->siblingId);

        // Verify row removed from DB
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM post_tags WHERE post_no = ? AND user_id = ?");
        $stmt->execute([$postNo, $this->siblingId]);
        $remaining = (int)$stmt->fetchColumn();

        $this->assertSame(0, $remaining, 'Self-untagging must remove the post_tags record');
    }
}
