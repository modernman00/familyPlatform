<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\MemoryMilestoneController;
use App\services\MemoryMilestoneService;
use PDO;
use Tests\Support\SocialFeedTestCase;

/**
 * MemoryMilestoneTest
 *
 * Verifies the nostalgia memory lookup, milestone anniversary calculations,
 * IDOR security boundaries, and 1-click feed resharing.
 */
final class MemoryMilestoneTest extends SocialFeedTestCase
{
    private MemoryMilestoneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MemoryMilestoneService($this->pdo);

        // Seed personal table entry for author
        $stmt = $this->pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, day, month, year) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $this->authorId,
            'TestFirstName',
            'TestLastName',
            $this->famCode,
            (int)date('j'),
            (string)date('F'),
            (int)date('Y') - 30,
        ]);
    }

    protected function tearDown(): void
    {
        $this->pdo->prepare("DELETE FROM personal WHERE id = ?")->execute([$this->authorId]);
        parent::tearDown();
    }

    public function testNostalgiaMemoriesSurfacesPastYearPostsOnSameDay(): void
    {
        // Seed post from exactly 2 years ago today
        $pastDate = date('Y-m-d H:i:s', strtotime('-2 years'));
        $stmt = $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created)
             VALUES (?, ?, ?, ?, 'published', ?)"
        );
        $stmt->execute([
            $this->authorId,
            'Nostalgia Author',
            'Cherished memories from 2 years ago at the beach!',
            $this->famCode,
            $pastDate,
        ]);
        $postNo = (int)$this->pdo->lastInsertId();

        $memories = $this->service->getNostalgiaMemories($this->authorId, [$this->famCode]);

        $this->assertNotEmpty($memories);
        $found = false;
        foreach ($memories as $m) {
            if ($m['post_no'] === $postNo) {
                $found = true;
                $this->assertSame(2, $m['years_ago']);
                $this->assertSame('2 years ago today', $m['years_ago_label']);
                $this->assertSame('Cherished memories from 2 years ago at the beach!', $m['postMessage']);
            }
        }
        $this->assertTrue($found, 'Past year post was not returned in nostalgia memories');

        // Cleanup
        $this->pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    }

    public function testNostalgiaMemoriesEnforcesFamilyIsolation(): void
    {
        $otherFamCode = 'OTHER_FAM_' . bin2hex(random_bytes(4));
        $pastDate = date('Y-m-d H:i:s', strtotime('-1 year'));

        $stmt = $this->pdo->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created)
             VALUES (?, ?, ?, ?, 'published', ?)"
        );
        $stmt->execute([
            'other_user_' . bin2hex(random_bytes(4)),
            'Other Family Person',
            'Secret other family memory',
            $otherFamCode,
            $pastDate,
        ]);
        $postNo = (int)$this->pdo->lastInsertId();

        // Current user should NOT see other family's memory
        $memories = $this->service->getNostalgiaMemories($this->authorId, [$this->famCode]);
        $postNos = array_column($memories, 'post_no');

        $this->assertNotContains($postNo, $postNos, 'IDOR violation: Cross-family memory leaked');

        // Cleanup
        $this->pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    }

    public function testUpcomingMilestonesDetectsBirthdays(): void
    {
        $milestones = $this->service->getUpcomingMilestones($this->authorId, [$this->famCode], 7);

        $this->assertNotEmpty($milestones);
        $found = false;
        foreach ($milestones as $m) {
            if ($m['user_id'] === $this->authorId && $m['type'] === 'birthday') {
                $found = true;
                $this->assertStringContainsString("TestFirstName", $m['title']);
                $this->assertSame('Today! 🎉', $m['date_badge']);
                $this->assertStringContainsString('30th Milestone', $m['subtitle']);
            }
        }
        $this->assertTrue($found, 'Birthday milestone for active user was not calculated');
    }

    public function testShareMemoryToFeedCreatesNewReflectionPost(): void
    {
        // 1. Seed original post
        $postNo = $this->seedPost('Original memory caption from our picnic');

        // 2. Reshare memory
        $result = $this->service->shareMemoryToFeed(
            userId: $this->authorId,
            postNo: $postNo,
            reflectionMessage: 'Can not believe how fast time flies!',
            famCode: $this->famCode
        );

        $this->assertSame('success', $result['status']);
        $newPostNo = (int)($result['new_post_no'] ?? 0);
        $this->assertGreaterThan(0, $newPostNo);

        // 3. Verify in database
        $stmt = $this->pdo->prepare("SELECT * FROM post WHERE post_no = ?");
        $stmt->execute([$newPostNo]);
        $newPost = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($newPost);
        $this->assertStringContainsString('Memory Flashback', (string)$newPost['postMessage']);
        $this->assertStringContainsString('Can not believe how fast time flies!', (string)$newPost['postMessage']);
        $this->assertStringContainsString('Original memory caption from our picnic', (string)$newPost['postMessage']);

        // Cleanup
        $this->pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$newPostNo]);
    }

    public function testControllerGetMemoriesAndMilestonesOutput(): void
    {
        $controller = new MemoryMilestoneController($this->service);

        ob_start();
        $controller->getMemoriesAndMilestones();
        $output = ob_get_clean();

        $this->assertNotFalse($output);
        $json = json_decode((string)$output, true);

        $this->assertSame('success', $json['status'] ?? null);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('memories', $json['data']);
        $this->assertArrayHasKey('milestones', $json['data']);
    }

    public function testAnniversaryCardCreateUrlPreFillsOccasionAndRecipient(): void
    {
        $url = $this->service->buildAnniversaryCardCreateUrl("Ada & Tunde's Anniversary", '10th Tin Anniversary 🔔');
        $this->assertStringStartsWith('/member/festive-cards?', $url);
        $this->assertStringContainsString('occasion=wedding', $url);
        $this->assertStringContainsString('recipient=Ada+%26+Tunde', $url);
        $this->assertStringContainsString('headline=Happy+10th+Tin+Anniversary', $url);
    }

    public function testNotificationEnrichmentAttachesCelebratoryCardContextForAnniversaries(): void
    {
        $_SESSION['id'] = $this->authorId;
        $_SESSION['famCode'] = $this->famCode;
        $_SESSION['famCodes'] = [$this->famCode];

        $tokenOwn = bin2hex(random_bytes(8));
        $tokenOther = bin2hex(random_bytes(8));
        $otherFamCode = 'OTHER_FAM_' . bin2hex(random_bytes(4));

        $insertCard = $this->pdo->prepare(
            "INSERT INTO festive_cards (uuid, share_token, user_id, fam_code, occasion, template_style, theme, headline, verse, sign_off, sender_name)
             VALUES (?, ?, ?, ?, 'wedding', 'classic', 'onyx_gold', ?, 'Happy anniversary!', 'With love', ?)"
        );
        $insertCard->execute([bin2hex(random_bytes(16)), $tokenOwn, $this->authorId, $this->famCode, 'Golden Anniversary Card', 'Ada']);
        $insertCard->execute([bin2hex(random_bytes(16)), $tokenOther, 'other_user', $otherFamCode, 'Secret Other Family Card', 'Stranger']);

        $items = [
            [
                'no' => 101,
                'notification_type' => 'Milestones',
                'notification_name' => "💍 Ada & Tunde's Anniversary",
                'notification_content' => "Happy Anniversary! Ada & Tunde's Anniversary is today (10th Tin Anniversary).",
                'action_url' => '/profilePage#memoriesCard',
            ],
            [
                'no' => 102,
                'notification_type' => 'Comment',
                'notification_name' => 'New comment',
                'notification_content' => 'Someone commented on your post.',
                'action_url' => '/profilePage',
            ],
        ];

        $enriched = \App\controller\NotificationController::enrichWithActionUrls($items, $this->authorId);

        // Anniversary notification has prominent card CTA + only own family's created card
        $this->assertArrayHasKey('celebration_card_url', $enriched[0]);
        $this->assertStringContainsString('/member/festive-cards?occasion=wedding', (string)$enriched[0]['celebration_card_url']);
        $this->assertArrayHasKey('celebration_cards', $enriched[0]);
        $tokens = array_column($enriched[0]['celebration_cards'], 'share_token');
        $this->assertContains($tokenOwn, $tokens);
        $this->assertNotContains($tokenOther, $tokens, 'IDOR violation: Cross-family celebratory card leaked');

        // Non-anniversary notification does NOT receive celebratory card metadata
        $this->assertArrayNotHasKey('celebration_card_url', $enriched[1]);
        $this->assertArrayNotHasKey('celebration_cards', $enriched[1]);

        // Cleanup
        $this->pdo->prepare("DELETE FROM festive_cards WHERE share_token IN (?, ?)")->execute([$tokenOwn, $tokenOther]);
    }
}
