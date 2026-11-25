<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\controller\members\CommentReactionController;
use App\repository\FeedRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class WorldClassFeedPipelineTest extends TestCase
{
    public function testCursorEncodeAndDecodeRoundTrip(): void
    {
        $cursor = FeedRepository::encodeCursor('2026-11-25 01:23:45', 98765);
        $this->assertNotSame('', $cursor);

        $decoded = FeedRepository::decodeCursor($cursor);
        $this->assertSame([
            'date_created' => '2026-11-25 01:23:45',
            'post_no' => 98765,
        ], $decoded);
    }

    public function testInvalidCursorsFailClosedToNull(): void
    {
        $this->assertNull(FeedRepository::decodeCursor(null));
        $this->assertNull(FeedRepository::decodeCursor(''));
        $this->assertNull(FeedRepository::decodeCursor('not-valid-base64!!!'));
        $this->assertNull(FeedRepository::decodeCursor(base64_encode('{"d":"2026-11-25","p":0}')));
        $this->assertNull(FeedRepository::decodeCursor(base64_encode('{"p":10}')));
    }

    public function testFetchReactionsBatchIsReadOnlyAndAggregatesCounts(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE personal (id TEXT PRIMARY KEY, firstName TEXT, lastName TEXT)');
        $pdo->exec('CREATE TABLE comment_reactions (comment_no INTEGER, id TEXT, reaction TEXT, label TEXT)');

        $pdo->exec("INSERT INTO personal (id, firstName, lastName) VALUES ('u1', 'Ada', 'Okafor'), ('u2', 'Tunde', 'Bello')");
        $pdo->exec("INSERT INTO comment_reactions (comment_no, id, reaction, label) VALUES
            (101, 'u1', 'like', 'likes'),
            (101, 'u2', 'love', 'love'),
            (102, 'u1', 'celebrate', 'celebrate')
        ");

        $batch = CommentReactionController::fetchReactionsBatch([101, 102, 999], $pdo);

        $this->assertArrayHasKey('101', $batch);
        $this->assertSame(1, $batch['101']['counts']['likes']);
        $this->assertSame(1, $batch['101']['counts']['love']);
        $this->assertSame(2, $batch['101']['counts']['totalReactions']);
        $this->assertCount(2, $batch['101']['who']);

        $this->assertSame(1, $batch['102']['counts']['celebrate']);
        $this->assertSame(1, $batch['102']['counts']['totalReactions']);

        $this->assertSame(0, $batch['999']['counts']['totalReactions']);
        $this->assertSame([], $batch['999']['who']);
    }
}
