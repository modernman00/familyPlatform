<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InviteKinshipPersonalisationTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = dirname(__DIR__, 2);
    }

    public function testInviteFamilyModalExposesUncleAuntAndCustomPersonaliser(): void
    {
        $modalPath = $this->rootDir . '/resources/views/member/modals/inviteFamilyModal.blade.php';
        self::assertFileExists($modalPath);

        $content = (string) file_get_contents($modalPath);

        // 1. Segmented easy-navigation category filter bar (All | Close | Extended | Custom)
        self::assertStringContainsString('id="kinshipCategoryNav"', $content);
        self::assertStringContainsString("setCategory('all')", $content);
        self::assertStringContainsString("setCategory('close')", $content);
        self::assertStringContainsString("setCategory('extended')", $content);
        self::assertStringContainsString('openCustomCreator()', $content);

        // 2. Uncle, Aunt, and Add your own chips inside #kinshipChipsContainer
        self::assertStringContainsString('data-role="Uncle"', $content);
        self::assertStringContainsString('data-role="Aunt"', $content);
        self::assertStringContainsString('id="btnKinshipAddCustom"', $content);
        self::assertStringContainsString('data-role="Custom"', $content);
        self::assertStringContainsString('Add your own…', $content);

        // 3. Inline personalisation controls & templates
        self::assertStringContainsString('id="customKinshipRoleInput"', $content);
        self::assertStringContainsString('id="customKinshipNameInput"', $content);
        self::assertStringContainsString('id="presetKinshipNameInput"', $content);
        self::assertStringContainsString('id="btnSaveCustomKinship"', $content);
        self::assertStringContainsString("'Uncle': (surname, url, code, isBirth, name)", $content);
        self::assertStringContainsString("'Aunt': (surname, url, code, isBirth, name)", $content);
        self::assertStringContainsString("'Custom': (surname, url, code, isBirth, name, customTitle)", $content);

        // 4. Birth/Maiden auto-routing includes Uncle & Aunt
        self::assertStringContainsString("'Uncle', 'Aunt'", $content);

        // 5. Strict British English (en-GB) compliance
        $cleanVisible = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content) ?? $content;
        $cleanVisible = strip_tags($cleanVisible);
        foreach (['personalize', 'customize', 'organize', 'prioritize'] as $usWord) {
            self::assertStringNotContainsStringIgnoringCase($usWord, $cleanVisible, "Prohibited US spelling found: {$usWord}");
        }
    }

    public function testInviteControllerSanitisesCustomRelationshipsAndGuardsKinshipSurnames(): void
    {
        $controllerPath = $this->rootDir . '/app/controller/InviteController.php';
        self::assertFileExists($controllerPath);

        $content = (string) file_get_contents($controllerPath);
        self::assertStringContainsString("custom_relationship", $content);
        self::assertStringContainsString("mb_substr(\$cleanRelationship, 0, 50)", $content);
        self::assertStringContainsString("'auntie'", $content);
        self::assertStringContainsString("'uncle'", $content);
        self::assertStringContainsString("'aunt'", $content);
        self::assertStringContainsString("'relationship'       => \$relationship !== '' ? \$relationship : null", $content);
    }

    public function testLivingGuidesAndWhatsNewDocumentKinshipInviteNavigation(): void
    {
        $whatsNew = require $this->rootDir . '/resources/data/whats_new.php';
        self::assertIsArray($whatsNew);
        self::assertSame('2026_11_w21', $whatsNew['version_key'] ?? '');
        self::assertCount(4, $whatsNew['items'] ?? []);

        $howToUse = (string) file_get_contents($this->rootDir . '/resources/views/howToUse.blade.php');
        self::assertStringContainsString('Uncle', $howToUse);
        self::assertStringContainsString('Aunt', $howToUse);
        self::assertStringContainsString('Add your own…', $howToUse);

        $playbook = (string) file_get_contents($this->rootDir . '/resources/views/playbook.blade.php');
        self::assertStringContainsString('Add your own personaliser', $playbook);
    }
}
