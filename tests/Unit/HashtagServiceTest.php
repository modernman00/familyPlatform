<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\services\HashtagService;
use PHPUnit\Framework\TestCase;

final class HashtagServiceTest extends TestCase
{
    public function test_extract_hashtags_normalises_and_deduplicates(): void
    {
        $text = "Enjoying #SundayDinner with the kin at #FamilyReunion! #sundaydinner and #FirstSteps.";
        $tags = HashtagService::extractHashtags($text);

        $this->assertContains('sundaydinner', $tags);
        $this->assertContains('familyreunion', $tags);
        $this->assertContains('firststeps', $tags);
        // Ensure deduplication
        $this->assertSame(3, count($tags));
    }

    public function test_extract_hashtags_respects_max_limit(): void
    {
        $text = "#one #two #three #four #five #six #seven #eight";
        $tags = HashtagService::extractHashtags($text, 4);

        $this->assertCount(4, $tags);
        $this->assertSame(['one', 'two', 'three', 'four'], $tags);
    }

    public function test_extract_hashtags_defends_against_xss_polyglots(): void
    {
        $malicious = "Attacking #<script>alert(1)</script> and #test'-- and #valid_tag_123";
        $tags = HashtagService::extractHashtags($malicious);

        $this->assertNotContains('<script>', $tags);
        $this->assertNotContains("test'--", $tags);
        $this->assertContains('valid_tag_123', $tags);
    }

    public function test_sync_and_suggest_hashtags_workflow(): void
    {
        $famCode = 'TEST_FAM_' . bin2hex(random_bytes(3));
        $targetId = 'test_post_' . bin2hex(random_bytes(4));
        $authorId = 'user_test_1';
        $postContent = "Celebrating great moments with #LivingHeritage and #SundayFeast in the family!";

        // 1. Sync hashtags
        $synced = HashtagService::syncHashtags($famCode, 'post', $targetId, $authorId, $postContent);
        $this->assertContains('livingheritage', $synced);
        $this->assertContains('sundayfeast', $synced);

        // 2. Autocomplete suggestions
        $suggestions = HashtagService::suggestHashtags($famCode, 'living');
        $this->assertNotEmpty($suggestions);
        $this->assertSame('livingheritage', $suggestions[0]['tag']);

        // 3. Trending hashtags
        $trending = HashtagService::getTrendingHashtags($famCode, 5);
        $tagsOnly = array_column($trending, 'tag');
        $this->assertContains('livingheritage', $tagsOnly);
        $this->assertContains('sundayfeast', $tagsOnly);

        // 4. Cleanup
        HashtagService::deleteLinksForTarget('post', $targetId);
    }
}
