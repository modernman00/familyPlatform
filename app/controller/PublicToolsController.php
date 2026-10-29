<?php

declare(strict_types=1);

namespace App\controller;

use Src\Utility;

/**
 * PublicToolsController
 *
 * High-converting public interactive utilities ("Engineering as Marketing").
 * Powers unauthenticated top-of-funnel customer acquisition tools:
 * 1. Family Memory & Oral History Prompt Generator (/tools/family-memory-prompts)
 * 2. 7-Generation Kinship & Consanguinity Calculator (/tools/kinship-calculator)
 */
final class PublicToolsController
{
    /**
     * Display the Oral History & Family Journal Prompt Generator.
     */
    public function memoryPrompts(): void
    {
        try {
            Utility::view('tools/memory_prompts', [
                'pageTitle' => 'Chronicle Your Elders: Oral History & Family Journal Prompt Studio',
                'metaDescription' => 'Generate sensory storytelling prompts to chronicle grandparent memories, childhood origins, and heirloom recipes before they fade. Free interactive family archive tool.',
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Display the 7-Generation Kinship & Degree Calculator.
     */
    public function kinshipCalculator(): void
    {
        try {
            Utility::view('tools/kinship_calculator', [
                'pageTitle' => '7-Generation Kinship Degree & Consanguinity Calculator',
                'metaDescription' => 'Determine exact genealogical relationships, degrees of kinship, and shared ancestry across your family tree. Free visual kinship calculator by FamilyPlatform.',
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Display a programmatic oral history prompt guide.
     *
     * @param string|array<string, string> $slug Parameter from AltoRouter or query
     */
    public function promptGuide(mixed $slug = null): void
    {
        try {
            $slugStr = is_array($slug) ? (string) ($slug['slug'] ?? '') : (string) $slug;
            if ($slugStr === '') {
                $slugStr = (string) ($_GET['slug'] ?? '');
            }

            $guide = \App\services\PromptGuideService::findBySlug($slugStr);
            if ($guide === null) {
                header('Location: /prompts', true, 302);
                exit;
            }

            $all = \App\services\PromptGuideService::all();
            $related = [];
            foreach ($guide['related_slugs'] as $relSlug) {
                if (isset($all[$relSlug])) {
                    $related[] = $all[$relSlug];
                }
            }

            Utility::view('tools/prompt_guide', [
                'guide' => $guide,
                'related' => $related,
                'pageTitle' => $guide['title'] . ' | FamilyPlatform',
                'metaDescription' => $guide['meta_description'],
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
