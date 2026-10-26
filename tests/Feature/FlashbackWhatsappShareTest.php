<?php

declare(strict_types=1);

namespace Tests\Feature;

use eftec\bladeone\BladeOne;
use PHPUnit\Framework\TestCase;

/**
 * Nostalgia Flashback memories offer a 1-tap WhatsApp share to pass memories directly
 * into family group chats.
 */
final class FlashbackWhatsappShareTest extends TestCase
{
    private function renderWidget(): string
    {
        $root = dirname(__DIR__, 2);
        $blade = new BladeOne($root . '/resources/views', $root . '/bootstrap/cache', BladeOne::MODE_DEBUG);

        return $blade->run('member.includes.memoriesMilestonesWidget', []);
    }

    private function renderMobileTray(): string
    {
        $root = dirname(__DIR__, 2);
        $blade = new BladeOne($root . '/resources/views', $root . '/bootstrap/cache', BladeOne::MODE_DEBUG);

        return $blade->run('member.includes.mobileHighlightsTray', ['eventData' => []]);
    }

    public function test_desktop_widget_renders_whatsapp_share_button_and_modal(): void
    {
        $html = $this->renderWidget();

        $this->assertStringContainsString('aria-label="Share memory to WhatsApp"', $html);
        $this->assertStringContainsString('title="Share to Family WhatsApp Group"', $html);
        $this->assertStringContainsString('whatsappMemoryUrl(memories[0])', $html);
        $this->assertStringContainsString('whatsappMemoryUrl(activeMemory, reflectionText)', $html);
        $this->assertStringContainsString('https://api.whatsapp.com/send?text=', $html);
    }

    public function test_mobile_highlights_tray_renders_whatsapp_share_button(): void
    {
        $html = $this->renderMobileTray();

        $this->assertStringContainsString('aria-label="Share memory to WhatsApp"', $html);
        $this->assertStringContainsString('whatsappMemoryUrl(memories[0])', $html);
        $this->assertStringContainsString('https://api.whatsapp.com/send?text=', $html);
    }

    public function test_whatsapp_url_generator_handles_hostile_characters_safely(): void
    {
        $memory = [
            'post_no' => '42',
            'years_ago_label' => '2 years ago',
            'fullName' => 'Grandpa <script>alert(1)</script>',
            'postMessage' => 'Remember this day? "Quotes" & <tags>',
        ];
        $reflection = 'So lovely! <script>bad()</script>';

        $yearsAgo = $memory['years_ago_label'];
        $author = ' — ' . $memory['fullName'];
        $quote = '"' . trim($memory['postMessage']) . '"';
        $refl = "\n\n💬 " . trim($reflection);
        $link = 'https://olaogun.test/profilePage#post42';

        $text = "✨ [Family Memory Flashback — {$yearsAgo}]{$author}\n\n{$quote}{$refl}\n\n🔗 {$link}";
        $url = 'https://api.whatsapp.com/send?text=' . rawurlencode($text);

        $this->assertStringStartsWith('https://api.whatsapp.com/send?text=', $url);
        $this->assertStringNotContainsString('<script>', $url);
        $this->assertStringNotContainsString('"', explode('?text=', $url)[1]);
    }
}
