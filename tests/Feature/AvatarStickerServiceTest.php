<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\AtgAvatarRenderer;
use App\services\AvatarStickerService;
use App\services\EmojiPlainText;
use App\dto\PostDto;
use App\services\LinkPreviewService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * RFC-0011 Me-stickers: token ownership, trait persistence, family gate, sticker SVG.
 * Writes only `member_avatar_traits` rows for synthetic ids, removed in tearDown().
 */
final class AvatarStickerServiceTest extends TestCase
{
    private PDO $db;
    private AvatarStickerService $svc;
    /** @var list<string> */
    private array $ids = [];
    /** @var array<string, mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->db = Db::connect2();
        $this->svc = new AvatarStickerService($this->db);
        $this->sessionBackup = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        foreach ($this->ids as $id) {
            $this->db->prepare('DELETE FROM member_avatar_traits WHERE user_id = ?')->execute([$id]);
        }
        $_SESSION = $this->sessionBackup;
    }

    private function newId(): string
    {
        $id = 'phpunit_stk_' . bin2hex(random_bytes(4));
        $this->ids[] = $id;
        return $id;
    }

    public function testStripsTokensThatDoNotBelongToTheAuthor(): void
    {
        $text = 'Go Mum :me_clap@ME1: :me_love@SOMEONE_ELSE: :me_dance@ME1:';
        $this->assertSame('Go Mum :me_clap@ME1:  :me_dance@ME1:', AvatarStickerService::stripForeignTokens($text, 'ME1'));
        $this->assertSame('x ', AvatarStickerService::stripForeignTokens('x :me_clap@ME1:', ''), 'no author → all removed');
        $this->assertSame('plain text', AvatarStickerService::stripForeignTokens('plain text', 'ME1'));
    }

    public function testUnknownReactionIsNotATokenAndSurvivesUntouched(): void
    {
        $this->assertSame(':me_hack@ME1:', AvatarStickerService::stripForeignTokens(':me_hack@ME1:', 'OTHER'));
    }

    public function testPostSanitiserEnforcesOwnershipFromSession(): void
    {
        $_SESSION['id'] = 'AUTHOR9';
        $clean = LinkPreviewService::sanitizePostMessage('Hi :me_wow@AUTHOR9: :me_wow@VICTIM1:');
        $this->assertStringContainsString(':me_wow@AUTHOR9:', $clean);
        $this->assertStringNotContainsString('VICTIM1', $clean);
    }

    public function testSaveTraitsUpsertsAndBumpsVersion(): void
    {
        $id = $this->newId();
        $traits = AtgAvatarRenderer::sanitise(['hairstyle' => 'afro_crown']);
        $this->svc->saveTraits($id, $traits);
        $first = $this->svc->getTraits($id);
        $this->assertNotNull($first);
        $this->assertSame('afro_crown', $first['traits']['hairstyle']);
        $this->assertSame('1', $first['version']);

        $this->svc->saveTraits($id, AtgAvatarRenderer::sanitise(['hairstyle' => 'high_fade']));
        $second = $this->svc->getTraits($id);
        $this->assertSame('2', $second['version'] ?? null);
        $this->assertSame('high_fade', $second['traits']['hairstyle'] ?? null);
    }

    public function testInvalidIdsAreRejected(): void
    {
        $this->assertNull($this->svc->getTraits('../../etc/passwd'));
        $this->assertFalse(AvatarStickerService::isValidUserId("a'b"));
        $this->assertFalse(AvatarStickerService::isValidReaction('explode'));
    }

    public function testCanViewOwnerOnlyWithoutSharedFamily(): void
    {
        $_SESSION['famCode'] = 'NO_SUCH_FAMILY_' . bin2hex(random_bytes(3));
        $_SESSION['famCodes'] = [];
        $this->assertTrue($this->svc->canView('OWNER1', 'OWNER1'));
        $this->assertFalse($this->svc->canView('OWNER1', 'phpunit_nobody_' . bin2hex(random_bytes(3))));
        $this->assertFalse($this->svc->canView('', 'OWNER1'));
    }

    public function testCanViewMemberOfSameFamily(): void
    {
        $row = $this->db->query("SELECT id, famCode FROM personal WHERE famCode IS NOT NULL AND famCode <> '' LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $this->markTestSkipped('No member with a family code in dev DB.');
        }
        $_SESSION['famCode'] = (string)$row['famCode'];
        $_SESSION['famCodes'] = [];
        $this->assertTrue($this->svc->canView('SOME_VIEWER', (string)$row['id']));
    }

    public function testStickerSvgIsWellFormedAnimatedAndBadged(): void
    {
        $traits = AtgAvatarRenderer::sanitise([]);
        foreach (array_keys(AvatarStickerService::REACTIONS) as $reaction) {
            $svg = $this->svc->renderSticker($traits, $reaction);
            $doc = new \DOMDocument();
            $this->assertTrue(@$doc->loadXML($svg), "well-formed XML for $reaction");
            $this->assertStringContainsString('@keyframes atgS{', $svg);
            $this->assertStringContainsString('prefers-reduced-motion:no-preference', $svg);
            $this->assertStringContainsString('class="atg-st-badge"', $svg);
            $this->assertStringContainsString(AvatarStickerService::REACTIONS[$reaction], $svg);
            $this->assertStringNotContainsString('<script', $svg);
            $this->assertStringNotContainsString('<image', $svg);
        }
    }

    public function testPlainTextSurfacesShowGlyphsNotRawTokens(): void
    {
        $this->assertSame('Well done 👏 ✨ team', EmojiPlainText::humanize('Well done :me_clap@ME1: :family_toast_3d: team'));
        $this->assertSame('no tokens: here', EmojiPlainText::humanize('no tokens: here'));
        $hook = PostDto::deriveHookFromMessage(':me_dance@ME1: Party at Grandma');
        $this->assertNotNull($hook);
        $this->assertStringNotContainsString(':me_', $hook);
        $this->assertStringContainsString('Party at Grandma', $hook);
    }
}
