<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\BaseController;
use App\controller\members\ReelsController;
use App\controller\members\TagController;
use App\model\AllMembersData;
use App\model\FamilyAnnouncement;
use App\model\FamilyJournal;
use App\model\Reel;
use App\repository\FeedRepository;
use App\services\CommentThreadService;
use App\services\FamilyPostAudienceService;
use App\services\FamilyTimeCapsuleService;
use App\services\MemberRestrictionService;
use App\services\OmniSearchService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;

final class MemberRestrictionTest extends TestCase
{
    private PDO $pdo;

    private string $famCode;
    private string $otherFamCode;
    private string $ownerId;
    private string $restrictedId;
    private string $siblingId;
    private string $strangerId;

    /** @var list<int> */
    private array $postNos = [];

    /** @var list<int> */
    private array $commentNos = [];

    /** @var list<int> */
    private array $reelIds = [];

    /** @var list<int> */
    private array $journalIds = [];

    /** @var list<int> */
    private array $announcementIds = [];

    /** @var list<int> */
    private array $capsuleIds = [];

    /** @var list<int> */
    private array $eventNos = [];

    /** @var array<string, mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->sessionBackup = $_SESSION ?? [];
        MemberRestrictionService::clearCache();

        $suffix = bin2hex(random_bytes(4));
        $this->famCode = 'FMREST' . strtoupper($suffix);
        $this->otherFamCode = 'FMOTHR' . strtoupper($suffix);

        $this->ownerId = 'MR_OWN_' . $suffix;
        $this->restrictedId = 'MR_RST_' . $suffix;
        $this->siblingId = 'MR_SIB_' . $suffix;
        $this->strangerId = 'MR_STR_' . $suffix;

        $this->seedUser($this->ownerId, 'Owner', 'Member', $this->famCode);
        $this->seedUser($this->restrictedId, 'Restricted', 'Cousin', $this->famCode);
        $this->seedUser($this->siblingId, 'Trusted', 'Sibling', $this->famCode);
        $this->seedUser($this->strangerId, 'External', 'Stranger', $this->otherFamCode);
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->sessionBackup;
        MemberRestrictionService::clearCache();

        $this->deleteByIds('post_tags', 'post_no', $this->postNos);
        $this->deleteByIds('comment', 'comment_no', $this->commentNos);
        $this->deleteByIds('post', 'post_no', $this->postNos);
        $this->deleteByIds('family_reels', 'id', $this->reelIds);
        $this->deleteByIds('family_journals', 'id', $this->journalIds);
        $this->deleteByIds('family_announcements', 'id', $this->announcementIds);
        $this->deleteByIds('family_time_capsules', 'id', $this->capsuleIds);
        $this->deleteByIds('events', 'no', $this->eventNos);

        $userIds = [$this->ownerId, $this->restrictedId, $this->siblingId, $this->strangerId];
        $ph = implode(',', array_fill(0, count($userIds), '?'));
        $this->pdo->prepare(sprintf('DELETE FROM user_member_restrictions WHERE owner_id IN (%s) OR restricted_user_id IN (%s)', $ph, $ph))
            ->execute(array_merge($userIds, $userIds));
        $this->pdo->prepare(sprintf('DELETE FROM notification WHERE sender_id IN (%s) OR receiver_id IN (%s)', $ph, $ph))
            ->execute(array_merge($userIds, $userIds));
        $this->pdo->prepare(sprintf('DELETE FROM profilePics WHERE id IN (%s)', $ph))->execute($userIds);
        $this->pdo->prepare(sprintf('DELETE FROM personal WHERE id IN (%s)', $ph))->execute($userIds);
        $this->pdo->prepare(sprintf('DELETE FROM account WHERE id IN (%s)', $ph))->execute($userIds);

        parent::tearDown();
    }

    private function seedUser(string $id, string $first, string $last, string $famCode): void
    {
        $stmtAcc = $this->pdo->prepare("INSERT INTO account (id, email, type, status) VALUES (?, ?, 'member', 'approved')");
        $stmtAcc->execute([$id, strtolower($id) . '@example.test']);

        $stmtPer = $this->pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmtPer->execute([$id, $first, $last, $famCode]);

        $stmtPic = $this->pdo->prepare("INSERT INTO profilePics (id, img) VALUES (?, 'avatarM.png')");
        $stmtPic->execute([$id]);
    }

    /**
     * @param list<int> $ids
     */
    private function deleteByIds(string $table, string $col, array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $this->pdo->prepare(sprintf('DELETE FROM %s WHERE %s IN (%s)', $table, $col, $ph))->execute($ids);
    }

    private function createPost(string $authorId, string $authorName, string $message, ?string $dateCreated = null): int
    {
        $created = $dateCreated ?? date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, target_scope, post_status, post_time, date_created)
             VALUES (?, ?, ?, ?, 'all', 'published', '10:00', ?)"
        );
        $stmt->execute([$authorId, $authorName, $message, $this->famCode, $created]);
        $postNo = (int)$this->pdo->lastInsertId();
        $this->postNos[] = $postNo;
        return $postNo;
    }

    public function test_red_team_rejects_self_and_cross_family_restrictions(): void
    {
        try {
            MemberRestrictionService::upsertRestriction($this->pdo, $this->ownerId, $this->ownerId, ['posts' => true]);
            $this->fail('PoC-1 Failed: Self-restriction should throw ValidationException.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('own account', $e->getMessage());
        }

        try {
            MemberRestrictionService::upsertRestriction($this->pdo, $this->ownerId, $this->strangerId, ['posts' => true]);
            $this->fail('PoC-2 Failed: Cross-family stranger restriction should throw ForbiddenException.');
        } catch (ForbiddenException $e) {
            $this->assertStringContainsString('family circles', $e->getMessage());
        }
    }

    public function test_post_feed_memories_direct_access_and_tag_bypass_are_blocked(): void
    {
        $ownerPostNo = $this->createPost($this->ownerId, 'Owner Member', 'Secret anniversary surprise #quietplan');
        $siblingPostNo = $this->createPost($this->siblingId, 'Trusted Sibling', 'Sunday brunch update');
        $memoryPostNo = $this->createPost($this->ownerId, 'Owner Member', 'Last year private throwback', date('Y-m-d H:i:s', strtotime('-1 year')));

        // Even if the restricted user was previously tagged in post_tags (PoC-3), restriction must win
        $this->pdo->prepare('INSERT INTO post_tags (post_no, user_id, tagged_by, created_at) VALUES (?, ?, ?, NOW())')
            ->execute([$ownerPostNo, $this->restrictedId, $this->ownerId]);

        MemberRestrictionService::upsertRestriction($this->pdo, $this->ownerId, $this->restrictedId, [
            'posts' => true,
            'comments' => true,
            'reels' => true,
            'events' => true,
            'journals' => true,
            'announcements' => true,
            'capsules' => true,
            'profile' => false,
        ]);

        $audienceSvc = new FamilyPostAudienceService($this->pdo);
        $this->assertFalse($audienceSvc->canUserAccessPost($ownerPostNo, $this->restrictedId), 'Restricted member must not access post even if tagged.');
        $this->assertTrue($audienceSvc->canUserAccessPost($ownerPostNo, $this->siblingId), 'Unrestricted sibling must still see post.');
        $this->assertTrue($audienceSvc->canUserAccessPost($ownerPostNo, $this->ownerId), 'Owner always sees own post.');
        $this->assertTrue(MemberRestrictionService::authorHasAnyRestriction($this->pdo, $this->ownerId, 'posts'));

        // New tagging attempt by owner or sibling against restricted pair must refuse restricted target
        $newTags = TagController::syncPostTags($siblingPostNo, $this->ownerId, [$this->restrictedId, $this->siblingId], $this->famCode);
        $taggedIds = array_column($newTags, 'user_id');
        $this->assertNotContains($this->restrictedId, $taggedIds);

        // Feed, count, and memories filtering
        $repo = new FeedRepository($this->pdo);
        $restrictedFeed = $repo->getFeedPostsAsArray($this->restrictedId, [$this->famCode], 20, 0);
        $restrictedPostIds = array_map(static fn(array $r): int => (int)$r['post_no'], $restrictedFeed);
        $this->assertNotContains($ownerPostNo, $restrictedPostIds);
        $this->assertContains($siblingPostNo, $restrictedPostIds);
        $this->assertSame(1, $repo->countFeedPosts($this->restrictedId, [$this->famCode]));

        $restrictedMemories = $repo->getMemories($this->restrictedId, [$this->famCode]);
        $memoryPostIds = array_map(static fn($dto): int => (int)$dto->postNo, $restrictedMemories);
        $this->assertNotContains($memoryPostNo, $memoryPostIds);

        $siblingFeed = $repo->getFeedPostsAsArray($this->siblingId, [$this->famCode], 20, 0);
        $siblingPostIds = array_map(static fn(array $r): int => (int)$r['post_no'], $siblingFeed);
        $this->assertContains($ownerPostNo, $siblingPostIds);
        $this->assertSame(3, $repo->countFeedPosts($this->siblingId, [$this->famCode]));
    }

    public function test_comments_emails_and_notifications_are_suppressed_for_restricted_member(): void
    {
        $siblingPostNo = $this->createPost($this->siblingId, 'Trusted Sibling', 'Family group discussion');

        $stmtC1 = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, date_created) VALUES (?, ?, 'Owner Member', 'Hidden owner comment', NOW())");
        $stmtC1->execute([$siblingPostNo, $this->ownerId]);
        $cOwner = (int)$this->pdo->lastInsertId();
        $this->commentNos[] = $cOwner;

        $stmtC2 = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, date_created) VALUES (?, ?, 'Trusted Sibling', 'Visible sibling comment', NOW())");
        $stmtC2->execute([$siblingPostNo, $this->siblingId]);
        $cSibling = (int)$this->pdo->lastInsertId();
        $this->commentNos[] = $cSibling;

        MemberRestrictionService::upsertRestriction($this->pdo, $this->ownerId, $this->restrictedId, [
            'posts' => true,
            'comments' => true,
        ]);

        // Comments rendered on sibling post must hide owner comment from restricted member
        $groupedForRestricted = AllMembersData::commentProfilePicByPostNos([$siblingPostNo], $this->restrictedId);
        $visibleCommentsForRestricted = array_column($groupedForRestricted[(string)$siblingPostNo] ?? [], 'id');
        $this->assertNotContains($this->ownerId, $visibleCommentsForRestricted);
        $this->assertContains($this->siblingId, $visibleCommentsForRestricted);

        $groupedForSibling = AllMembersData::commentProfilePicByPostNos([$siblingPostNo], $this->siblingId);
        $visibleCommentsForSibling = array_column($groupedForSibling[(string)$siblingPostNo] ?? [], 'id');
        $this->assertContains($this->ownerId, $visibleCommentsForSibling);

        // Recipient list for emails/notifications excludes restricted member
        $recipients = AllMembersData::AllMembersEmailByFamCode($this->famCode, $this->ownerId, 'posts');
        $recipientIds = array_column($recipients, 'id');
        $this->assertNotContains($this->restrictedId, $recipientIds);
        $this->assertContains($this->siblingId, $recipientIds);

        // Mention/reply notifications must never insert row for restricted member
        $_ENV['ALLOW_OUTBOUND_NOTIFICATIONS'] = '0';
        $notified = CommentThreadService::notify(
            $this->pdo,
            $this->ownerId,
            'Owner Member',
            (string)$siblingPostNo,
            [$this->restrictedId, $this->siblingId],
            $this->restrictedId,
            $cOwner
        );
        $this->assertNotContains($this->restrictedId, $notified);
        $this->assertContains($this->siblingId, $notified);

        $notifStmt = $this->pdo->prepare('SELECT COUNT(*) FROM notification WHERE sender_id = ? AND receiver_id = ?');
        $notifStmt->execute([$this->ownerId, $this->restrictedId]);
        $this->assertSame(0, (int)$notifStmt->fetchColumn());
    }

    public function test_reels_journals_announcements_events_capsules_search_and_profile_restrictions(): void
    {
        // Seed owner reel, journal, announcement, event, capsule
        $rStmt = $this->pdo->prepare(
            "INSERT INTO family_reels (user_id, famCode, caption, video_url, category) VALUES (?, ?, 'Hidden Reel X99', '/resources/videos/test.mp4', 'Memory')"
        );
        $rStmt->execute([$this->ownerId, $this->famCode]);
        $reelId = (int)$this->pdo->lastInsertId();
        $this->reelIds[] = $reelId;

        $journalId = FamilyJournal::createJournal([
            'family_code' => $this->famCode,
            'author_id' => $this->ownerId,
            'title' => 'Hidden Journal Z88',
            'slug' => 'hidden-journal-z88-' . bin2hex(random_bytes(4)),
            'summary' => 'Secret journal summary',
            'content' => '<p>Private family chronicle Z88</p>',
            'status' => 'published',
        ]);
        $this->journalIds[] = $journalId;

        $annId = FamilyAnnouncement::createAnnouncement([
            'family_code' => $this->famCode,
            'author_id' => $this->ownerId,
            'title' => 'Hidden Notice Q77',
            'message' => 'Private announcement',
        ]);
        $this->announcementIds[] = $annId;

        $evStmt = $this->pdo->prepare(
            "INSERT INTO events (id, eventCode, eventName, eventDate, eventType, eventFrequency, eventDescription)
             VALUES (?, ?, 'UniqueEvtX55Q', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Dinner', 'One-off', 'Private event')"
        );
        $evStmt->execute([$this->ownerId, $this->famCode]);
        $evNo = (int)$this->pdo->lastInsertId();
        $this->eventNos[] = $evNo;

        $capId = FamilyTimeCapsuleService::createCapsule($this->famCode, $this->ownerId, [
            'recipient_name' => 'Everyone',
            'title' => 'Hidden Capsule K44',
            'message' => 'Vault message',
            'unlock_date' => date('Y-m-d'),
        ]);
        $this->capsuleIds[] = $capId;

        $searchPostNo = $this->createPost($this->ownerId, 'Owner Member', 'UniqueKeywordV123 celebration');

        // Apply full restriction including profile
        MemberRestrictionService::upsertRestriction($this->pdo, $this->ownerId, $this->restrictedId, [
            'posts' => true,
            'comments' => true,
            'reels' => true,
            'events' => true,
            'journals' => true,
            'announcements' => true,
            'capsules' => true,
            'profile' => true,
        ]);

        // 1. Reels
        $reelsForRestricted = Reel::getReelsFeed($this->restrictedId, $this->famCode, 20, 0);
        $this->assertNotContains($reelId, array_map(static fn($r): int => (int)$r['id'], $reelsForRestricted));
        $this->assertFalse(ReelsController::canAccessReel(['id' => $reelId, 'user_id' => $this->ownerId, 'famCode' => $this->famCode], $this->restrictedId));
        $this->assertTrue(ReelsController::canAccessReel(['id' => $reelId, 'user_id' => $this->ownerId, 'famCode' => $this->famCode], $this->siblingId));

        // 2. Journals & Announcements & Events (session viewer context)
        $_SESSION['id'] = $this->restrictedId;
        $_SESSION['famCode'] = $this->famCode;
        $this->assertEmpty(FamilyJournal::getFamilyJournals($this->famCode, 20, 0));
        $this->assertNull(FamilyJournal::getJournalById($journalId, $this->famCode));
        $this->assertEmpty(FamilyAnnouncement::getActiveAnnouncements($this->famCode, 10));
        $this->assertEmpty(AllMembersData::getUpcomingEventsForMonths($this->famCode, 3));
        $this->assertFalse(BaseController::sessionCanViewMember($this->ownerId, $this->famCode));

        // 3. Time Capsules
        $this->assertEmpty(FamilyTimeCapsuleService::getFamilyCapsules($this->famCode, $this->restrictedId));
        $this->assertNotEmpty(FamilyTimeCapsuleService::getFamilyCapsules($this->famCode, $this->siblingId));

        // 4. OmniSearch isolation
        $omni = new OmniSearchService($this->pdo);
        $postSearch = $omni->searchData('UniqueKeywordV123', $this->restrictedId, [$this->famCode]);
        $this->assertEmpty($postSearch['posts']);
        $journalSearch = $omni->searchData('Hidden Journal Z88', $this->restrictedId, [$this->famCode]);
        $this->assertEmpty($journalSearch['journals']);
        $eventSearch = $omni->searchData('UniqueEvtX55Q', $this->restrictedId, [$this->famCode]);
        $this->assertNotContains((string)$evNo, array_column($eventSearch['events'], 'id'));
        $this->assertEmpty($eventSearch['events']);
        $reelSearch = $omni->searchData('Hidden Reel X99', $this->restrictedId, [$this->famCode]);
        $this->assertEmpty($reelSearch['reels']);
        $memberSearch = $omni->searchData('Owner Member', $this->restrictedId, [$this->famCode]);
        $this->assertNotContains($this->ownerId, array_column($memberSearch['members'], 'id'));

        // Sibling still finds owner post via OmniSearch
        $siblingSearch = $omni->searchData('UniqueKeywordV123', $this->siblingId, [$this->famCode]);
        $this->assertContains((string)$searchPostNo, array_column($siblingSearch['posts'], 'id'));

        // 5. Strict ONLY_FULL_GROUP_BY directory query for Privacy Settings & SovereignSearchEngine integration
        $this->pdo->exec("SET SESSION sql_mode = CONCAT(@@sql_mode, ',ONLY_FULL_GROUP_BY')");
        $directory = MemberRestrictionService::getRestrictionDirectoryForOwner($this->pdo, $this->ownerId);
        $this->assertNotEmpty($directory);
        $this->assertTrue($directory[0]['is_restricted']);

        // 6. SovereignSearchEngine searchRestrictionDirectory from shared-lib
        $searchResults = MemberRestrictionService::searchRestrictionDirectory($this->pdo, $this->ownerId, '#' . $this->famCode);
        $this->assertNotEmpty($searchResults);
        $matchedIds = array_column($searchResults, 'id');
        $this->assertContains($this->restrictedId, $matchedIds);

        // Removing restriction restores access immediately
        MemberRestrictionService::removeRestriction($this->pdo, $this->ownerId, $this->restrictedId);
        $this->assertNotNull(FamilyJournal::getJournalById($journalId, $this->famCode));
        $this->assertTrue((new FamilyPostAudienceService($this->pdo))->canUserAccessPost($searchPostNo, $this->restrictedId));
    }
}
