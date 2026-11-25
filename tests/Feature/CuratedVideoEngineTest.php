<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\dto\PostDto;
use App\services\CuratedVideoEngineService;
use App\services\FamilyPlatformAccountService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class CuratedVideoEngineTest extends TestCase
{
    private PDO $db;
    /** @var list<int> */
    private array $createdPostNos = [];
    /** @var list<int> */
    private array $createdVideoIds = [];
    /** @var list<int> */
    private array $createdSourceIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        $engine = new CuratedVideoEngineService($this->db);
        $engine->ensureSchema();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdPostNos)) {
            $delPost = $this->db->prepare('DELETE FROM post WHERE post_no = ?');
            foreach ($this->createdPostNos as $postNo) {
                $delPost->execute([$postNo]);
            }
        }
        if (!empty($this->createdVideoIds)) {
            $delVid = $this->db->prepare('DELETE FROM curated_videos WHERE id = ?');
            foreach ($this->createdVideoIds as $vidId) {
                $delVid->execute([$vidId]);
            }
        }
        if (!empty($this->createdSourceIds)) {
            $delSrc = $this->db->prepare('DELETE FROM curated_video_sources WHERE id = ?');
            foreach ($this->createdSourceIds as $srcId) {
                $delSrc->execute([$srcId]);
            }
        }
        parent::tearDown();
    }

    public function testParsesYouTubeAtomFeedAndFiltersUnsafeContent(): void
    {
        $sampleXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns:media="http://search.yahoo.com/mrss/" xmlns="http://www.w3.org/2005/Atom">
  <title>Family Laughs</title>
  <entry>
    <yt:videoId>dQw4w9WgXcQ</yt:videoId>
    <title>Cute Puppy Learns To High Five</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"/>
    <author><name>Wholesome Pets</name></author>
    <media:group>
      <media:description>A lovely family moment with our golden retriever puppy.</media:description>
    </media:group>
  </entry>
  <entry>
    <yt:videoId>abc123XYZ89</yt:videoId>
    <title>NSFW 18+ Gambling Casino Clickbait</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v=abc123XYZ89"/>
    <author><name>Spammer</name></author>
    <media:group>
      <media:description>Explicit betting promo.</media:description>
    </media:group>
  </entry>
</feed>
XML;

        $engine = new CuratedVideoEngineService($this->db);
        $entries = $engine->parseFeedEntries($sampleXml, 10);

        $this->assertCount(2, $entries);
        $this->assertTrue($engine->isFamilySafe($entries[0]['title'], $entries[0]['description']));
        $this->assertFalse($engine->isFamilySafe($entries[1]['title'], $entries[1]['description']));
        // BBC Earth descriptions containing "Dramatic" or "seaweed" must not false-positive
        $this->assertTrue($engine->isFamilySafe(
            'Why Are These Sea Urchins Wearing Hats? | Blue Planet III | BBC Earth',
            'Dramatic, rare, and exclusive natural history beneath seaweed forests.'
        ));
    }

    public function testSsrfProtectionBlocksPrivateAndNonHttpsUrls(): void
    {
        $engine = new CuratedVideoEngineService($this->db);

        $this->assertTrue($engine->isAllowedExternalUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertFalse($engine->isAllowedExternalUrl('http://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertFalse($engine->isAllowedExternalUrl('https://localhost/feed.xml'));
        $this->assertFalse($engine->isAllowedExternalUrl('https://127.0.0.1/feed.xml'));
        $this->assertFalse($engine->isAllowedExternalUrl('https://169.254.169.254/latest/meta-data/'));
        $this->assertFalse($engine->isAllowedExternalUrl('file:///etc/passwd'));
    }

    public function testImportAndPublishVideoToUniversalFeed(): void
    {
        $uniqueId = 'fp' . substr(md5((string)microtime(true)), 0, 9);
        $videoUrl = 'https://www.youtube.com/watch?v=' . $uniqueId;

        $mockFetcher = static function (string $url) use ($uniqueId): array {
            if (str_contains($url, 'oembed')) {
                return [
                    'ok' => true,
                    'body' => json_encode([
                        'title' => 'Granddad Dancing With The Kids',
                        'author_name' => 'Family Fun UK',
                        'thumbnail_url' => "https://img.youtube.com/vi/{$uniqueId}/hqdefault.jpg",
                    ]) ?: '{}',
                    'error' => '',
                ];
            }
            return ['ok' => false, 'body' => '', 'error' => 'Not used'];
        };

        $officialService = new FamilyPlatformAccountService($this->db);
        $engine = new CuratedVideoEngineService($this->db, $officialService, $mockFetcher);

        $curatedId = $engine->importSingleVideoUrl($videoUrl, 'funny');
        $this->createdVideoIds[] = $curatedId;
        $this->assertGreaterThan(0, $curatedId);

        $postNo = $engine->publishQueuedVideo($curatedId);
        $this->createdPostNos[] = $postNo;
        $this->assertGreaterThan(0, $postNo);

        $stmt = $this->db->prepare('SELECT * FROM post WHERE post_no = ? LIMIT 1');
        $stmt->execute([$postNo]);
        $postRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($postRow);
        $this->assertSame(FamilyPlatformAccountService::getAccountId(), (string)$postRow['id']);
        $this->assertSame('published', (string)$postRow['post_status']);
        $this->assertStringContainsString($videoUrl, (string)$postRow['postMessage']);

        $dto = PostDto::fromDatabaseRow($postRow);
        $this->assertNotNull($dto->video);
        $this->assertSame('youtube', $dto->video['type']);
        $this->assertSame($uniqueId, $dto->video['videoId']);
    }

    public function testCronEndpointRejectsUnauthorizedSecret(): void
    {
        $controller = new \App\controller\CronController();
        $prevRequest = $_REQUEST;
        $prevServer = $_SERVER;

        $_REQUEST = ['secret' => 'invalid_secret_attempt'];
        unset($_SERVER['HTTP_X_CRON_SECRET']);

        ob_start();
        $controller->contentEngine();
        $output = (string)ob_get_clean();

        $_REQUEST = $prevRequest;
        $_SERVER = $prevServer;

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status']);

        ob_start();
        $controller->familyHistorian();
        $fhOutput = (string)ob_get_clean();
        $fhDecoded = json_decode($fhOutput, true);
        $this->assertIsArray($fhDecoded);
        $this->assertSame('error', $fhDecoded['status']);
    }
}
