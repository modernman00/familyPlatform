<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\LinkPreviewService;
use PHPUnit\Framework\TestCase;

final class LinkPreviewUrlExtractionTest extends TestCase
{
    public function testExtractsSchemeLessWwwAndBareCommonTldDomains(): void
    {
        $this->assertSame(
            'https://www.idecideapp.com',
            LinkPreviewService::extractFirstPreviewableUrl('www.idecideapp.com')
        );

        $this->assertSame(
            'https://www.idecideapp.com',
            LinkPreviewService::extractFirstPreviewableUrl('Check out www.idecideapp.com!')
        );

        $this->assertSame(
            'https://idecideapp.com',
            LinkPreviewService::extractFirstPreviewableUrl('Visit idecideapp.com for decisions.')
        );

        $this->assertSame(
            'https://www.idecideapp.com/blogs/108',
            LinkPreviewService::extractFirstPreviewableUrl('https://www.idecideapp.com/blogs/108')
        );
    }

    public function testIgnoresEmailsFilenamesAndAbbreviations(): void
    {
        $this->assertNull(LinkPreviewService::extractFirstPreviewableUrl('Email me at user@idecideapp.com'));
        $this->assertNull(LinkPreviewService::extractFirstPreviewableUrl('Look at photo.jpg and e.g. this note.'));
        $this->assertFalse(LinkPreviewService::containsPotentialUrl('Just plain family text'));
        $this->assertTrue(LinkPreviewService::containsPotentialUrl('www.idecideapp.com'));
    }

    public function testSsrfBlocksLocalhostAndPrivateMetadataIps(): void
    {
        $this->assertFalse(LinkPreviewService::isSafeUrl('http://127.0.0.1/secret'));
        $this->assertFalse(LinkPreviewService::isSafeUrl('http://169.254.169.254/latest/meta-data/'));
        $this->assertFalse(LinkPreviewService::isSafeUrl('http://localhost/admin'));
        $this->assertFalse(LinkPreviewService::isSafeUrl('https://test.local/internal'));
    }

    public function testFacebookStoryFallbackAndLoginWallRenderCleanStoryMetadata(): void
    {
        $method = new \ReflectionMethod(LinkPreviewService::class, 'formatPreviewPayload');

        $storyUrl = 'https://www.facebook.com/stories/1778112468889642/UzpfSVNDOjI1MjQ5OTUxMzEzMDA4/';

        $fallbackPayload = $method->invoke(null, [
            'url' => $storyUrl,
            'canonical_url' => $storyUrl,
            'domain' => 'facebook.com',
            'site_name' => 'Facebook',
            'title' => 'Facebook',
            'description' => '',
            'image_url' => null,
        ], $storyUrl);

        $this->assertSame('Facebook Story', $fallbackPayload['title']);
        $this->assertSame('Facebook Stories require you to sign in on Facebook to watch.', $fallbackPayload['description']);
        $this->assertSame('Watch Story on Facebook →', $fallbackPayload['cta_label']);
        $this->assertTrue($fallbackPayload['is_social_video']);
        $this->assertTrue($fallbackPayload['is_facebook']);

        $loginWallPayload = $method->invoke(null, [
            'url' => $storyUrl,
            'canonical_url' => $storyUrl,
            'domain' => 'facebook.com',
            'site_name' => 'Facebook',
            'title' => 'Log in to Facebook',
            'description' => 'Log into Facebook to start sharing and connecting with your friends, family, and people you know.',
            'image_url' => null,
        ], $storyUrl);

        $this->assertSame('Facebook Story', $loginWallPayload['title']);
        $this->assertSame('Facebook Stories require you to sign in on Facebook to watch.', $loginWallPayload['description']);
        $this->assertSame('Watch Story on Facebook →', $loginWallPayload['cta_label']);
    }
}
