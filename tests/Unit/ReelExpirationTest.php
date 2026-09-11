<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\model\Reel;
use PHPUnit\Framework\TestCase;

final class ReelExpirationTest extends TestCase
{
    private ?string $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalEnv = $_ENV['REELS_EXPIRATION_DAYS'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->originalEnv !== null) {
            $_ENV['REELS_EXPIRATION_DAYS'] = $this->originalEnv;
        } else {
            unset($_ENV['REELS_EXPIRATION_DAYS']);
        }
        parent::tearDown();
    }

    public function test_defaults_to_seven_days_when_unset(): void
    {
        unset($_ENV['REELS_EXPIRATION_DAYS']);
        $this->assertSame(7, Reel::getExpirationDays());
    }

    public function test_reads_custom_expiration_days(): void
    {
        $_ENV['REELS_EXPIRATION_DAYS'] = '14';
        $this->assertSame(14, Reel::getExpirationDays());

        $_ENV['REELS_EXPIRATION_DAYS'] = '3';
        $this->assertSame(3, Reel::getExpirationDays());
    }

    public function test_allows_zero_for_permanent_retention(): void
    {
        $_ENV['REELS_EXPIRATION_DAYS'] = '0';
        $this->assertSame(0, Reel::getExpirationDays());
    }

    public function test_handles_empty_string_gracefully_with_default(): void
    {
        $_ENV['REELS_EXPIRATION_DAYS'] = '';
        $this->assertSame(7, Reel::getExpirationDays());
    }
}
