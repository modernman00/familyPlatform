<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\CommentThreadService;
use PHPUnit\Framework\TestCase;

/**
 * "@First Last" mention resolution is pure string/roster matching: a client can only
 * ever mention someone who is on the post's family roster.
 */
final class CommentMentionTest extends TestCase
{
    /** @return list<array{id: string, firstName: string, lastName: string}> */
    private function roster(): array
    {
        return [
            ['id' => 'A1', 'firstName' => 'Ada', 'lastName' => 'Obi'],
            ['id' => 'B2', 'firstName' => 'Tunde', 'lastName' => "O'Neil"],
            ['id' => 'C3', 'firstName' => 'Zoë', 'lastName' => 'Lawal'],
            ['id' => 'ME', 'firstName' => 'Me', 'lastName' => 'Myself'],
        ];
    }

    public function test_resolves_mentions_case_insensitively(): void
    {
        $ids = CommentThreadService::extractMentionedIds('hi @ada obi and @TUNDE O\'NEIL!', $this->roster(), 'ME');
        $this->assertSame(['A1', 'B2'], $ids);
    }

    public function test_handles_unicode_names(): void
    {
        $this->assertSame(['C3'], CommentThreadService::extractMentionedIds('thanks @Zoë Lawal', $this->roster(), 'ME'));
    }

    public function test_never_mentions_the_author(): void
    {
        $this->assertSame([], CommentThreadService::extractMentionedIds('@Me Myself', $this->roster(), 'ME'));
    }

    public function test_ignores_plain_names_and_non_roster_names(): void
    {
        $this->assertSame([], CommentThreadService::extractMentionedIds('Ada Obi is great', $this->roster(), 'ME'));
        $this->assertSame([], CommentThreadService::extractMentionedIds('@Stranger Person', $this->roster(), 'ME'));
    }

    public function test_requires_a_word_boundary(): void
    {
        $this->assertSame([], CommentThreadService::extractMentionedIds('mail me at x@Ada Obi', $this->roster(), 'ME'));
        $this->assertSame([], CommentThreadService::extractMentionedIds('@Ada Obinna', $this->roster(), 'ME'));
    }

    public function test_deduplicates_and_caps(): void
    {
        $this->assertSame(['A1'], CommentThreadService::extractMentionedIds('@Ada Obi @Ada Obi', $this->roster(), 'ME'));

        $big = [];
        $text = '';
        for ($i = 0; $i < 25; $i++) {
            $big[] = ['id' => "U$i", 'firstName' => "First$i", 'lastName' => "Last$i"];
            $text .= "@First$i Last$i ";
        }
        $this->assertCount(CommentThreadService::MAX_MENTIONS, CommentThreadService::extractMentionedIds($text, $big, 'ME'));
    }

    public function test_tolerates_malformed_roster_rows(): void
    {
        $roster = [['id' => 'X'], ['firstName' => 'Ada'], ['id' => 'Y', 'firstName' => '', 'lastName' => 'Obi']];
        $this->assertSame([], CommentThreadService::extractMentionedIds('@Ada Obi', $roster, 'ME'));
    }

    public function test_mention_chip_pattern_matches_valid_names_and_rejects_xss_attempts(): void
    {
        $pattern = '/(?<![\w@])@([A-Z\p{Lu}][\p{L}\'\-]+(?:\s+[A-Z\p{Lu}][\p{L}\'\-]+)+)(?![\w])/u';

        // Valid names
        $this->assertSame(1, preg_match($pattern, 'Hello @Ada Obi, welcome!'));
        $this->assertSame(1, preg_match($pattern, 'Thanks @Tunde O\'Neil'));
        $this->assertSame(1, preg_match($pattern, 'Congrats @Zoë Lawal'));

        // Malicious or un-escaped script injection attempts should not match as valid names
        $this->assertSame(0, preg_match($pattern, '@<script>alert(1)</script>'));
        $this->assertSame(0, preg_match($pattern, '@" onclick="alert(1)"'));

        // Simulated frontend escaping + chip substitution
        $raw = 'Hello @Ada Obi and <script>alert(1)</script>!';
        $escaped = htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $withChips = preg_replace_callback($pattern, static fn($m) => '<span class="mention-chip">' . $m[0] . '</span>', $escaped);

        $this->assertStringContainsString('<span class="mention-chip">@Ada Obi</span>', $withChips);
        $this->assertStringNotContainsString('<script>', $withChips);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $withChips);
    }
}
