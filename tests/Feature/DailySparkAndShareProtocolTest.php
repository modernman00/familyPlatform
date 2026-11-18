<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\DailyFamilySparkService;
use App\services\ShareMetadataProtocol;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class DailySparkAndShareProtocolTest extends TestCase
{
    private string $testFamCode = 'TEST_SPARK_EDIT_999';
    private string $authorId = 'usr_spark_author_1';
    private string $attackerId = 'usr_spark_attacker_2';
    private ?PDO $db = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        DailyFamilySparkService::ensureTablesExist($this->db);
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        if ($this->db === null) {
            return;
        }
        $s1 = $this->db->prepare("DELETE FROM daily_family_spark_responses WHERE famCode = ?");
        $s1->execute([$this->testFamCode]);

        $s2 = $this->db->prepare("DELETE FROM daily_family_sparks WHERE famCode = ?");
        $s2->execute([$this->testFamCode]);
    }

    public function test_author_can_edit_and_delete_spark_reply_and_idor_is_blocked(): void
    {
        $created = DailyFamilySparkService::createMemberSpark(
            famCode: $this->testFamCode,
            userId: $this->authorId,
            authorName: 'Ade Olaogun',
            title: 'Archive Flashback: Who & Where?',
            promptText: 'Who is in this photo?',
            options: [],
            dbOverride: $this->db
        );
        $this->assertSame('success', $created['status']);
        $sparkId = (string)$created['data']['id'];

        $replied = DailyFamilySparkService::submitResponse(
            sparkId: $sparkId,
            famCode: $this->testFamCode,
            userId: $this->authorId,
            authorName: 'Ade Olaogun',
            payload: ['response_type' => 'text', 'body' => 'That is Uncle Tunde in Lagos!'],
            dbOverride: $this->db
        );
        $this->assertSame('success', $replied['status']);
        $this->assertCount(1, $replied['data']['replies']);
        $replyId = (string)$replied['data']['replies'][0]['id'];
        $this->assertSame($this->authorId, (string)$replied['data']['current_user_id']);

        // Attacker IDOR edit must be blocked with 403
        $idorEdit = DailyFamilySparkService::updateReply(
            sparkId: $sparkId,
            replyId: $replyId,
            famCode: $this->testFamCode,
            userId: $this->attackerId,
            body: 'Tampered text',
            dbOverride: $this->db
        );
        $this->assertSame(403, $idorEdit['http_code']);

        // Attacker IDOR delete must be blocked with 403
        $idorDelete = DailyFamilySparkService::deleteReply(
            sparkId: $sparkId,
            replyId: $replyId,
            famCode: $this->testFamCode,
            userId: $this->attackerId,
            dbOverride: $this->db
        );
        $this->assertSame(403, $idorDelete['http_code']);

        // Nonexistent reply returns 404
        $missing = DailyFamilySparkService::updateReply(
            sparkId: $sparkId,
            replyId: 'spr_nonexistent_999',
            famCode: $this->testFamCode,
            userId: $this->authorId,
            body: 'Hello',
            dbOverride: $this->db
        );
        $this->assertSame(404, $missing['http_code']);

        // Empty body returns 422
        $emptyEdit = DailyFamilySparkService::updateReply(
            sparkId: $sparkId,
            replyId: $replyId,
            famCode: $this->testFamCode,
            userId: $this->authorId,
            body: '   ',
            dbOverride: $this->db
        );
        $this->assertSame(422, $emptyEdit['http_code']);

        // Author edit succeeds and sets updated_at
        $edited = DailyFamilySparkService::updateReply(
            sparkId: $sparkId,
            replyId: $replyId,
            famCode: $this->testFamCode,
            userId: $this->authorId,
            body: 'Actually that is Uncle Tunde in Ibadan, 1994!',
            dbOverride: $this->db
        );
        $this->assertSame(200, $edited['http_code']);
        $this->assertSame('Actually that is Uncle Tunde in Ibadan, 1994!', $edited['data']['replies'][0]['body']);
        $this->assertNotEmpty($edited['data']['replies'][0]['updated_at']);

        // Author delete succeeds
        $deleted = DailyFamilySparkService::deleteReply(
            sparkId: $sparkId,
            replyId: $replyId,
            famCode: $this->testFamCode,
            userId: $this->authorId,
            dbOverride: $this->db
        );
        $this->assertSame(200, $deleted['http_code']);
        $this->assertCount(0, $deleted['data']['replies']);
    }

    public function test_share_metadata_protocol_enforces_whatsapp_and_imessage_rich_preview_standards(): void
    {
        $this->assertTrue(ShareMetadataProtocol::isSocialCrawler('WhatsApp/2.23.20.0 A'));
        $this->assertTrue(ShareMetadataProtocol::isSocialCrawler('facebookexternalhit/1.1'));
        $this->assertTrue(ShareMetadataProtocol::isSocialCrawler('Applebot/0.1'));
        $this->assertFalse(ShareMetadataProtocol::isSocialCrawler('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'));

        $meta = ShareMetadataProtocol::buildMeta([
            'title' => 'Archive Flashback: Who & Where?',
            'description' => 'A cherished memory from our family archive is live on FamilyPlatform.',
            'image' => '/spark/spk_123/card.jpg',
            'url' => '/spark/spk_123',
            'type' => 'article',
        ]);

        $this->assertSame(1200, $meta['image_width']);
        $this->assertSame(630, $meta['image_height']);
        $this->assertSame('image/jpeg', $meta['image_type']);
        $this->assertSame('en_GB', $meta['locale']);
        $this->assertStringStartsWith('http', $meta['image']);
        $this->assertStringStartsWith('http', $meta['url']);

        $html = ShareMetadataProtocol::renderHeadMeta($meta);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:image:width" content="1200"', $html);
        $this->assertStringContainsString('property="og:image:height" content="630"', $html);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);

        $jpeg = ShareMetadataProtocol::renderPreviewCardJpeg(
            badge: 'ARCHIVE FLASHBACK',
            title: 'Archive Flashback: Who & Where?',
            subtitle: 'A cherished memory from our family archive is live on FamilyPlatform.'
        );
        $this->assertNotEmpty($jpeg);
        $this->assertLessThan(307200, strlen($jpeg), 'OG JPEG must be under WhatsApp 300 KB limit');

        $share = DailyFamilySparkService::buildPrivacySafeShareData([
            'id' => 'spk_abc123',
            'title' => 'Archive Flashback: Who & Where?',
        ]);
        $this->assertStringContainsString('/spark/spk_abc123', $share['teaser_text']);
        $this->assertStringNotContainsString('/member', $share['teaser_text']);

        // Reverse-proxy HTTPS and APP_URL precedence
        $prevAppUrl = $_ENV['APP_URL'] ?? null;
        $prevHost = $_SERVER['HTTP_HOST'] ?? null;
        $prevXfp = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        try {
            $_ENV['APP_URL'] = 'https://familyplatform.co.uk';
            $_SERVER['HTTP_HOST'] = 'evil.example.com';
            $this->assertSame('https://familyplatform.co.uk', ShareMetadataProtocol::baseUrl());

            unset($_ENV['APP_URL']);
            putenv('APP_URL=');
            $_SERVER['HTTP_HOST'] = 'proxy.familyplatform.co.uk';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $this->assertSame('https://proxy.familyplatform.co.uk', ShareMetadataProtocol::baseUrl());
        } finally {
            if ($prevAppUrl !== null) {
                $_ENV['APP_URL'] = $prevAppUrl;
            } else {
                unset($_ENV['APP_URL']);
            }
            if ($prevHost !== null) {
                $_SERVER['HTTP_HOST'] = $prevHost;
            } else {
                unset($_SERVER['HTTP_HOST']);
            }
            if ($prevXfp !== null) {
                $_SERVER['HTTP_X_FORWARDED_PROTO'] = $prevXfp;
            } else {
                unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
            }
        }
    }
}
