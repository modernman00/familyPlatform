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
}
