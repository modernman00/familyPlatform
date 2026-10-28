<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\model\FamilyAnnouncement;
use eftec\bladeone\BladeOne;
use PHPUnit\Framework\TestCase;
use Src\Select;

/**
 * Announcement cards offer a one-tap WhatsApp share so a notice reaches relatives who are not on the app yet.
 */
final class AnnouncementWhatsappShareTest extends TestCase
{
    private string $user;
    private string $fam;

    protected function setUp(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $this->user = '888803U' . $suffix;
        $this->fam = 'FAMW' . $suffix;

        $pdo = Select::connect2();
        $this->cleanup();
        $pdo->prepare('DELETE FROM personal WHERE id = ?')->execute(['888803USERW']);
        $pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)')
            ->execute([$this->user, 'Wanda', 'Share', $this->fam]);
        $_SESSION = ['famCode' => $this->fam, 'id' => $this->user, 'token' => 't'];
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    private function cleanup(): void
    {
        if (empty($this->fam) || empty($this->user)) {
            return;
        }
        $pdo = Select::connect2();
        $pdo->prepare('DELETE FROM family_announcements WHERE family_code = ?')->execute([$this->fam]);
        $pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$this->user]);
    }

    private function render(): string
    {
        $root = dirname(__DIR__, 2);
        $blade = new BladeOne($root . '/resources/views', $root . '/bootstrap/cache', BladeOne::MODE_DEBUG);

        return $blade->run('member.includes.announcementBanner', []);
    }

    public function test_card_links_to_whatsapp_with_the_encoded_announcement(): void
    {
        FamilyAnnouncement::createAnnouncement([
            'family_code' => $this->fam, 'author_id' => $this->user,
            'title' => 'Reunion & BBQ', 'message' => 'Bring food!', 'priority' => 'important', 'type' => 'general',
        ]);

        $html = $this->render();

        $this->assertStringContainsString('data-testid="announcement-whatsapp-share"', $html);
        $this->assertMatchesRegularExpression('#href="https://api\.whatsapp\.com/send\?text=[^"]+"#', $html);
        $this->assertStringContainsString(rawurlencode('Reunion & BBQ'), $html);
        $this->assertStringContainsString(rawurlencode('Wanda Share'), $html);
    }

    public function test_hostile_markup_in_the_announcement_cannot_break_out_of_the_link(): void
    {
        FamilyAnnouncement::createAnnouncement([
            'family_code' => $this->fam, 'author_id' => $this->user,
            'title' => '"><script>alert(1)</script>', 'message' => "x\" onmouseover=\"alert(2)", 'priority' => 'important', 'type' => 'general',
        ]);

        $html = $this->render();
        preg_match('#href="(https://api\.whatsapp\.com/send\?text=[^"]*)"#', $html, $m);

        $this->assertNotEmpty($m, 'share link must still be a single well-formed href');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertDoesNotMatchRegularExpression('/[<>"\s]/', $m[1], 'the text parameter must be fully percent-encoded');
    }

    public function test_no_share_link_when_the_family_has_no_announcements(): void
    {
        $this->assertStringNotContainsString('announcement-whatsapp-share', $this->render());
    }
}
