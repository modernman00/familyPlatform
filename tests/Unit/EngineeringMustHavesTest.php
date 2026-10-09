<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Quality\AssertsEngineeringMustHaves;

class EngineeringMustHavesTest extends TestCase
{
    use AssertsEngineeringMustHaves;

    #[Test]
    public function repositoryPassesEngineeringMustHavesScan(): void
    {
        $this->assertMustHavesPass(dirname(__DIR__, 2));
    }
}
