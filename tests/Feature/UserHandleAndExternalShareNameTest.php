<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\PostShareController;
use App\services\UserHandleService;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class UserHandleAndExternalShareNameTest extends TestCase
{
    private \PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Db::connect2();
        UserHandleService::ensureSchema($this->db);
    }

    public function testHandleNormalisationAndValidation(): void
    {
        $this->assertSame('modernman', UserHandleService::normaliseHandle('@modernman'));
        $this->assertSame('modernman', UserHandleService::normaliseHandle('modernman'));
        $this->assertSame('wally_o', UserHandleService::normaliseHandle('@WALLY_O'));
        $this->assertNull(UserHandleService::normaliseHandle(''));
        $this->assertNull(UserHandleService::normaliseHandle('@'));
        $this->assertNull(UserHandleService::normaliseHandle('a')); // Too short (< 2 chars)
        $this->assertNull(UserHandleService::normaliseHandle('user name with spaces'));
        $this->assertNull(UserHandleService::normaliseHandle('invalid!characters'));

        $this->assertTrue(UserHandleService::isValidHandleFormat('@modernman'));
        $this->assertTrue(UserHandleService::isValidHandleFormat('modernman_99'));
        $this->assertFalse(UserHandleService::isValidHandleFormat('x'));
        $this->assertFalse(UserHandleService::isValidHandleFormat('invalid handle'));
    }

    public function testFormatExternalNameRespectsPreferences(): void
    {
        // 1. Default first_initial
        $this->assertSame(
            'Wally O.',
            UserHandleService::formatExternalName('Wally', 'Olaogun', 'modernman', 'first_initial')
        );

        // 2. Handle preference with valid handle
        $this->assertSame(
            '@modernman',
            UserHandleService::formatExternalName('Wally', 'Olaogun', 'modernman', 'handle')
        );

        // 3. Handle preference without handle falls back to first_initial
        $this->assertSame(
            'Wally O.',
            UserHandleService::formatExternalName('Wally', 'Olaogun', null, 'handle')
        );

        // 4. First only
        $this->assertSame(
            'Wally',
            UserHandleService::formatExternalName('Wally', 'Olaogun', 'modernman', 'first_only')
        );

        // 5. Full name
        $this->assertSame(
            'Wally Olaogun',
            UserHandleService::formatExternalName('Wally', 'Olaogun', 'modernman', 'full_name')
        );

        // 6. Anonymous
        $this->assertSame(
            'A family member',
            UserHandleService::formatExternalName('Wally', 'Olaogun', 'modernman', 'anonymous')
        );

        // 7. Single name
        $this->assertSame(
            'Ade',
            UserHandleService::formatExternalName('Ade', '', null, 'first_initial')
        );
    }

    public function testFormatInitialFallbackStringParsing(): void
    {
        $this->assertSame('Wally O.', UserHandleService::formatInitialFallback('Wally Olaogun'));
        $this->assertSame('Wally O.', UserHandleService::formatInitialFallback('WALLY OLAOGUN'));
        $this->assertSame('Wally', UserHandleService::formatInitialFallback('Wally'));
        $this->assertSame('A family member', UserHandleService::formatInitialFallback(''));
        $this->assertSame('A family member', UserHandleService::formatInitialFallback('Family Member'));
    }

    public function testPostShareControllerResolvesPrivacyShieldedAuthor(): void
    {
        $resolved = UserHandleService::resolveExternalAuthorName($this->db, '999999999', 'Wally Olaogun');
        $this->assertSame('Wally O.', $resolved);
    }
}
