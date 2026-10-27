<?php
declare(strict_types=1);

namespace Tests\Unit;

use eftec\bladeone\BladeOne;
use PHPUnit\Framework\TestCase;

final class KinshipSuggestionsZeroSpaceTest extends TestCase
{
    private string $viewDir;
    private string $cacheDir;
    private BladeOne $blade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewDir = dirname(__DIR__, 2) . '/resources/views';
        $this->cacheDir = dirname(__DIR__, 2) . '/bootstrap/cache';
        $this->blade = new BladeOne($this->viewDir, $this->cacheDir, BladeOne::MODE_DEBUG);
    }

    public function test_kinship_suggestions_container_occupies_zero_space_when_empty(): void
    {
        // 1. Rendering with empty suggestedKin
        $renderedEmpty = trim($this->blade->run('member.includes.kinshipSuggestions', [
            'suggestedKin' => [],
            'kinshipWidgetId' => 'kinshipRadarWidget'
        ]));

        $this->assertSame('', $renderedEmpty, 'Kinship suggestions container must emit 0 HTML when suggestedKin is empty so it occupies zero space');
        $this->assertStringNotContainsString('card', $renderedEmpty);
        $this->assertStringNotContainsString('kinship-radar-widget', $renderedEmpty);
        $this->assertStringNotContainsString('Suggested Kin &amp; In-Laws', $renderedEmpty);
        $this->assertStringNotContainsString('Kinship Radar', $renderedEmpty);

        // 2. Rendering with null suggestedKin
        $renderedNull = trim($this->blade->run('member.includes.kinshipSuggestions', [
            'suggestedKin' => null,
            'kinshipWidgetId' => 'kinshipRadarWidget'
        ]));

        $this->assertSame('', $renderedNull, 'Kinship suggestions container must emit 0 HTML when suggestedKin is null');
    }

    public function test_kinship_suggestions_renders_card_when_suggestions_exist(): void
    {
        $sampleSuggestions = [
            [
                'user_id' => 'user_123',
                'firstName' => 'Sarah',
                'lastName' => 'Olaogun',
                'profilePics' => 'avatar1.png',
                'kinship_type' => 'Paternal Cousin',
                'primary_reason' => 'Shared family heritage network',
                'confidence_score' => 88
            ]
        ];

        $rendered = $this->blade->run('member.includes.kinshipSuggestions', [
            'suggestedKin' => $sampleSuggestions,
            'kinshipWidgetId' => 'kinshipRadarWidget'
        ]);

        $this->assertStringContainsString('kinship-radar-widget', $rendered);
        $this->assertStringContainsString('Suggested Kin &amp; In-Laws', $rendered);
        $this->assertStringContainsString('Sarah Olaogun', $rendered);
        $this->assertStringContainsString('Paternal Cousin', $rendered);
        $this->assertStringContainsString('88% Match', $rendered);
    }

    public function test_middle_and_right_column_protect_container_with_not_empty_check(): void
    {
        $middleColumnPath = $this->viewDir . '/member/includes/middleColumn.blade.php';
        $rightColumnPath = $this->viewDir . '/member/includes/rightColumn.blade.php';

        $this->assertFileExists($middleColumnPath);
        $this->assertFileExists($rightColumnPath);

        $middleContent = file_get_contents($middleColumnPath);
        $rightContent = file_get_contents($rightColumnPath);

        $this->assertStringContainsString("@if(!empty(\$suggestedKin))", $middleContent);
        $this->assertStringContainsString("@if(!empty(\$suggestedKin))", $rightContent);
    }

    public function test_kinship_radar_js_hides_container_when_cards_reach_zero(): void
    {
        $jsPath = dirname(__DIR__, 2) . '/resources/asset/js/components/kinship/kinshipRadar.js';
        $this->assertFileExists($jsPath);

        $jsContent = file_get_contents($jsPath);
        $this->assertStringContainsString("widget.querySelectorAll('.kinship-item-card').length === 0", $jsContent);
        $this->assertStringContainsString("widget.style.display = 'none'", $jsContent);
        $this->assertStringContainsString("removeKinCardAcrossWidgets", $jsContent);
    }
}
