<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\controller\members\ProfilePage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * ProfilePage::sanitizeCommentGifUrl() decides which URLs may be rendered inside
 * every family member's feed, so it only admits https Giphy media hosts.
 */
final class CommentGifUrlTest extends TestCase
{
    private function sanitize(mixed $raw): ?string
    {
        $m = new ReflectionMethod(ProfilePage::class, 'sanitizeCommentGifUrl');
        $m->setAccessible(true);

        return $m->invoke(null, $raw);
    }

    #[DataProvider('acceptedUrls')]
    public function test_accepts_curated_giphy_urls(string $url): void
    {
        $this->assertSame($url, $this->sanitize($url));
    }

    /** @return array<string, array{string}> */
    public static function acceptedUrls(): array
    {
        return [
            'media host' => ['https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif'],
            'numbered media host' => ['https://media2.giphy.com/media/abc/giphy.gif'],
            'bare host' => ['https://giphy.com/gifs/abc'],
        ];
    }

    #[DataProvider('rejectedValues')]
    public function test_rejects_anything_else(mixed $raw): void
    {
        $this->assertNull($this->sanitize($raw));
    }

    /** @return array<string, array{mixed}> */
    public static function rejectedValues(): array
    {
        return [
            'null' => [null],
            'array' => [['https://media.giphy.com/a.gif']],
            'empty' => [''],
            'http scheme' => ['http://media.giphy.com/media/a/giphy.gif'],
            'other host' => ['https://evil.example.com/a.gif'],
            'lookalike suffix' => ['https://media.giphy.com.evil.example/a.gif'],
            'lookalike prefix' => ['https://evilgiphy.com/a.gif'],
            'userinfo trick' => ['https://media.giphy.com@evil.example/a.gif'],
            'localhost' => ['https://localhost/a.gif'],
            'javascript scheme' => ['javascript:alert(1)'],
            'data uri' => ['data:image/gif;base64,R0lGODlhAQABAAAAACw='],
            'too long' => ['https://media.giphy.com/' . str_repeat('a', 2100)],
        ];
    }
}
