<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\PostMessage;
use App\model\RegisterTableData;
use Tests\Support\SocialFeedTestCase;

final class CrossTenantCommentNotificationTest extends SocialFeedTestCase
{
    /** @var list<string> */
    private array $extraUserIds = [];

    /** @var list<int> */
    private array $extraPostNos = [];

    /** @var list<int> */
    private array $extraCommentNos = [];

    /** @var list<string> */
    private array $extraFamCodes = [];

    protected function tearDown(): void
    {
        if (!empty($this->extraCommentNos)) {
            $in = implode(',', array_fill(0, count($this->extraCommentNos), '?'));
            $this->pdo->prepare("DELETE FROM comment WHERE comment_no IN ($in)")->execute($this->extraCommentNos);
        }

        if (!empty($this->extraPostNos)) {
            $in = implode(',', array_fill(0, count($this->extraPostNos), '?'));
            $this->pdo->prepare("DELETE FROM post WHERE post_no IN ($in)")->execute($this->extraPostNos);
        }

        if (!empty($this->extraUserIds)) {
            $in = implode(',', array_fill(0, count($this->extraUserIds), '?'));
            $this->pdo->prepare("DELETE FROM notification WHERE receiver_id IN ($in) OR sender_id IN ($in)")->execute(array_merge($this->extraUserIds, $this->extraUserIds));
            $this->pdo->prepare("DELETE FROM personal WHERE id IN ($in)")->execute($this->extraUserIds);
            $this->pdo->prepare("DELETE FROM account WHERE id IN ($in)")->execute($this->extraUserIds);
        }

        if (!empty($this->extraFamCodes)) {
            $in = implode(',', array_fill(0, count($this->extraFamCodes), '?'));
            $this->pdo->prepare("DELETE FROM user_families WHERE family_code IN ($in)")->execute($this->extraFamCodes);
        }

        parent::tearDown();
    }

    private function createTestUser(string $famCode, string $fName, string $lName): string
    {
        $uid = 'TUSER_' . bin2hex(random_bytes(6));
        $this->extraUserIds[] = $uid;

        $stmt = $this->pdo->prepare("INSERT INTO account (id, email, type, status) VALUES (?, ?, 'member', 'approved')");
        $stmt->execute([$uid, strtolower($uid) . '@example.com']);

        $stmt = $this->pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uid, $fName, $lName, $famCode]);

        $this->extraFamCodes[] = $famCode;
        return $uid;
    }

    public function testRegisterTableDataDoesNotContainPlaceholderComment(): void
    {
        $cleanData = [
            'id' => 'REG_' . bin2hex(random_bytes(4)),
            'firstName' => 'Karen',
            'lastName' => 'Jane',
            'email' => 'karen.jane@example.com',
            'famCode' => 'FAM_' . bin2hex(random_bytes(3)),
        ];

        $tables = RegisterTableData::createRegisterTable($cleanData);

        $this->assertArrayNotHasKey(
            'comment',
            $tables,
            'RegisterTableData must never seed phantom starter comments with non-existent post_no 1000.'
        );
    }

    public function testOrphanCommentOnNonExistentPostDoesNotBleedToActiveFamily(): void
    {
        $famA = 'FAM_A_' . bin2hex(random_bytes(4));
        $userA1 = $this->createTestUser($famA, 'Alice', 'Smith');
        $userA2 = $this->createTestUser($famA, 'Bob', 'Smith');

        // Legitimate post in Family A
        $stmt = $this->pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, post_status, post_time) VALUES (?, ?, 'Post in Family A', 'published', ?)");
        $stmt->execute([$userA1, $famA, (string)(time() * 1000)]);
        $postANo = (int)$this->pdo->lastInsertId();
        $this->extraPostNos[] = $postANo;

        // Legitimate comment in Family A
        $stmt = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (?, ?, 'Alice Smith', 'Legit comment', 'new', NOW())");
        $stmt->execute([$postANo, $userA1]);
        $commentANo = (int)$this->pdo->lastInsertId();
        $this->extraCommentNos[] = $commentANo;

        // Orphan comment from foreign user on non-existent post 999999
        $foreignUid = 'FOR_' . bin2hex(random_bytes(5));
        $this->extraUserIds[] = $foreignUid;
        $stmt = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (999999, ?, 'Karen Jane', 'Orphan comment payload', 'new', NOW())");
        $stmt->execute([$foreignUid]);
        $orphanCommentNo = (int)$this->pdo->lastInsertId();
        $this->extraCommentNos[] = $orphanCommentNo;

        // Process unpublished comments batch
        PostMessage::getNewCommentPusher();

        // Verify Bob in Family A received notification for Alice's comment
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userA2, $userA1]);
        $aliceNotifs = $stmt->fetchAll();
        $this->assertNotEmpty($aliceNotifs, 'Family A member should receive notification for Family A comment');

        // Verify Bob in Family A DID NOT receive any notification from Karen Jane
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userA2, $foreignUid]);
        $karenNotifs = $stmt->fetchAll();
        $this->assertEmpty($karenNotifs, 'Family A member must never receive notifications for orphan foreign comments');
    }

    public function testCrossFamilyCommentIsolation(): void
    {
        $famA = 'FAM_ISOL_A_' . bin2hex(random_bytes(4));
        $userA1 = $this->createTestUser($famA, 'UserA1', 'Alpha');
        $userA2 = $this->createTestUser($famA, 'UserA2', 'Alpha');

        $famB = 'FAM_ISOL_B_' . bin2hex(random_bytes(4));
        $userB1 = $this->createTestUser($famB, 'UserB1', 'Beta');
        $userB2 = $this->createTestUser($famB, 'UserB2', 'Beta');

        // Post in Family A
        $stmt = $this->pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, post_status, post_time) VALUES (?, ?, 'Family A post', 'published', ?)");
        $stmt->execute([$userA1, $famA, (string)(time() * 1000)]);
        $postANo = (int)$this->pdo->lastInsertId();
        $this->extraPostNos[] = $postANo;

        // Post in Family B
        $stmt = $this->pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, post_status, post_time) VALUES (?, ?, 'Family B post', 'published', ?)");
        $stmt->execute([$userB1, $famB, (string)(time() * 1000)]);
        $postBNo = (int)$this->pdo->lastInsertId();
        $this->extraPostNos[] = $postBNo;

        // Both comments created simultaneously as 'new'
        $stmt = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (?, ?, 'UserA1 Alpha', 'Comment for A', 'new', NOW())");
        $stmt->execute([$postANo, $userA1]);
        $this->extraCommentNos[] = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (?, ?, 'UserB1 Beta', 'Comment for B', 'new', NOW())");
        $stmt->execute([$postBNo, $userB1]);
        $this->extraCommentNos[] = (int)$this->pdo->lastInsertId();

        // Process batch
        PostMessage::getNewCommentPusher();

        // Family A member UserA2 should ONLY receive UserA1 notification
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userA2, $userA1]);
        $this->assertNotEmpty($stmt->fetchAll(), 'UserA2 must receive notification from UserA1');

        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userA2, $userB1]);
        $this->assertEmpty($stmt->fetchAll(), 'UserA2 must NOT receive notification from UserB1 (cross-tenant leak prevented)');

        // Family B member UserB2 should ONLY receive UserB1 notification
        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userB2, $userB1]);
        $this->assertNotEmpty($stmt->fetchAll(), 'UserB2 must receive notification from UserB1');

        $stmt = $this->pdo->prepare("SELECT * FROM notification WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$userB2, $userA1]);
        $this->assertEmpty($stmt->fetchAll(), 'UserB2 must NOT receive notification from UserA1 (cross-tenant leak prevented)');
    }
}
