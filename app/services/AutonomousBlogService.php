<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use RuntimeException;
use Src\Db;

/**
 * 100% Autonomous Family & Kinship Blog Engine for FamilyPlatform.
 *
 * Enforces editorial excellence, prompt-injection defense,
 * zero-interpolation SQL persistence, and dual-boundary HTML sanitization.
 */
class AutonomousBlogService
{
    /**
     * Editorial AI clichés strictly forbidden.
     *
     * @var array<int, string>
     */
    private const EDITORIAL_BANNED_TERMS = [
        "in today's fast-paced world",
        'delve',
        'tapestry',
        'game-changer',
        'revolutionize',
        'beacon',
        'navigating the landscape',
        'furthermore',
        'in conclusion',
        'at the end of the day',
        'unlocking potential',
        'testament to',
    ];

    /**
     * Curated Family Heritage, Lineage & Kinship Themes.
     *
     * @var array<int, array{category: string, theme: string, angle: string, image: string}>
     */
    private const CURATED_THEMES = [
        [
            'category' => 'Kinship & Lineage',
            'theme'    => 'Preserving Multi-Generational Heritage: How to Document Oral Family Histories',
            'angle'    => 'Interview techniques for elders, recording audio memories, and cataloguing family tree lineages.',
            'image'    => 'family_history.jpg',
        ],
        [
            'category' => 'Reunions & Gatherings',
            'theme'    => 'The 90-Day Family Reunion Playbook: Budgeting, Accommodation and Activity Choreography',
            'angle'    => 'Step-by-step coordination for multi-generational families from initial headcount surveys to keepsake photos.',
            'image'    => 'family_reunion.jpg',
        ],
        [
            'category' => 'Parenting & Keepsakes',
            'theme'    => 'Digital Time Capsules: Creating Living Keepsakes for Your Children\'s 18th Birthday',
            'angle'    => 'How capturing annual voice memos, milestone photos, and letters builds an emotional legacy for children.',
            'image'    => 'time_capsule.jpg',
        ],
        [
            'category' => 'Elder Care & Connection',
            'theme'    => 'Bridging Generational Gaps: Engaging Grandparents with Interactive Family Trivia and Storytelling',
            'angle'    => 'Overcoming digital friction to connect grandchildren with older relatives through guided family quizzes.',
            'image'    => 'grandparents_trivia.jpg',
        ],
        [
            'category' => 'Living Traditions',
            'theme'    => 'The Living Heirloom Cookbook: Archiving Secret Family Recipes for Posterity',
            'angle'    => 'How preserving handwritten heritage recipes and kitchen stories connects generations across dining tables.',
            'image'    => 'family_recipes.jpg',
        ],
        [
            'category' => 'Family Governance',
            'theme'    => 'The Family Council: How Structured Monthly Check-Ins Strengthen Bonds and Resolve Friction',
            'angle'    => 'Empowering kids and teens through transparent family roundtables, holiday planning, and shared decision-making.',
            'image'    => 'family_roundtable.jpg',
        ],
    ];

    /**
     * Autonomously generates and publishes an editorial-grade family article.
     *
     * @param string|null $customTopic Optional manual topic override
     * @return array{status: string, blog_id?: int, title?: string, category?: string, word_count?: int, reason?: string}
     */
    public function generateAndPublish(?string $customTopic = null): array
    {
        $pdo = Db::connect2();

        if ($customTopic !== null) {
            $cleanTopic = preg_replace('/[^\p{L}\p{N}\s\-_.,?!()]/u', '', trim(strip_tags($customTopic)));
            $customTopic = substr((string)$cleanTopic, 0, 150);
            if ($customTopic === '') {
                $customTopic = null;
            }
        }

        // 1. Select rotating theme
        $selectedTheme = $this->selectTheme($pdo, $customTopic);

        // 2. Generate article via AIService
        try {
            $draft = AIService::generateBlogDraft(
                $selectedTheme['theme'] . ' — ' . $selectedTheme['angle'],
                'warm, inspiring, and family-focused'
            );
        } catch (\Throwable $e) {
            error_log('FamilyPlatform AutonomousBlogService generation failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'reason' => 'AI Generation failed: ' . $e->getMessage(),
            ];
        }

        if (empty($draft['title']) || empty($draft['content'])) {
            return [
                'status' => 'error',
                'reason' => 'AI generation returned incomplete article payload.',
            ];
        }

        // 3. Editorial Negative Lexicon Check
        $fullText = strtolower($draft['title'] . ' ' . $draft['summary'] . ' ' . $draft['content']);
        foreach (self::EDITORIAL_BANNED_TERMS as $bannedEditorial) {
            if (str_contains($fullText, $bannedEditorial)) {
                return [
                    'status' => 'error',
                    'reason' => "Article rejected by Editorial quality gate: contains cliché '{$bannedEditorial}'.",
                ];
            }
        }

        // 4. Word count check
        $wordCount = str_word_count(strip_tags($draft['content']));
        if ($wordCount < 250) {
            return [
                'status' => 'error',
                'reason' => "Article failed length quality gate ({$wordCount} words; minimum 250 required).",
            ];
        }

        // 5. Dual-Boundary HTML purification
        $content = self::purifyHtml($draft['content']);

        $safeTitle = htmlspecialchars_decode(strip_tags($draft['title']), ENT_QUOTES);
        $safeSummary = htmlspecialchars_decode(strip_tags($draft['summary']), ENT_QUOTES);
        $category = $selectedTheme['category'];
        $image = $selectedTheme['image'];

        // 6. Generate unique slug
        $baseSlug = $this->slugify($safeTitle);
        $slug = $this->ensureUniqueSlug($pdo, $baseSlug);

        // 7. Calculate read time
        $readTime = max(1, (int) ceil($wordCount / 200));
        $now = date('Y-m-d H:i:s');

        // 8. Zero-Interpolation SQL persistence
        $stmt = $pdo->prepare('INSERT INTO blogs (title, slug, summary, content, cover_image, views, category, read_time, author_id, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $safeTitle,
            $slug,
            $safeSummary,
            $content,
            $image,
            $category,
            $readTime,
            'FamilyPlatform Editorial Team',
            'published',
            $now,
            $now,
        ]);

        $newId = (int)$pdo->lastInsertId();

        return [
            'status'     => 'success',
            'blog_id'    => $newId,
            'title'      => $safeTitle,
            'category'   => $category,
            'word_count' => $wordCount,
        ];
    }

    /**
     * @param PDO $pdo
     * @param string|null $customTopic
     * @return array{category: string, theme: string, angle: string, image: string}
     */
    private function selectTheme(PDO $pdo, ?string $customTopic = null): array
    {
        if (!empty($customTopic)) {
            return [
                'category' => 'Family Playbook',
                'theme'    => $customTopic,
                'angle'    => 'Practical family coordination guidance and heirloom storytelling.',
                'image'    => 'family_history.jpg',
            ];
        }

        $stmt = $pdo->query('SELECT title FROM blogs ORDER BY id DESC LIMIT 10');
        $recentTitles = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        $recentText = strtolower(implode(' ', (array)$recentTitles));

        foreach (self::CURATED_THEMES as $theme) {
            $normalizedTheme = strtolower($theme['theme']);
            if (!str_contains($recentText, substr($normalizedTheme, 0, 20))) {
                return $theme;
            }
        }

        return self::CURATED_THEMES[array_rand(self::CURATED_THEMES)];
    }

    private function slugify(string $text): string
    {
        $text = (string) preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
        $text = (string) preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);

        return $text !== '' ? $text : 'family-guide-' . time();
    }

    private function ensureUniqueSlug(PDO $pdo, string $slug): string
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM blogs WHERE slug = ?');
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }

        return $slug . '-' . time();
    }

    /**
     * Enterprise HTML purifier: strips disallowed tags and sanitizes attributes
     * to eliminate Stored XSS, protocol injection, and dangerous event handlers.
     */
    public static function purifyHtml(string $rawHtml): string
    {
        $allowedTags = '<h3><h4><h5><h6><p><ul><ol><li><strong><b><em><i><blockquote><br><hr><a>';
        $stripped = strip_tags($rawHtml, $allowedTags);

        if (trim($stripped) === '') {
            return '';
        }

        $dom = new \DOMDocument();
        $libxmlState = libxml_use_internal_errors(true);
        $htmlWithEncoding = '<?xml encoding="utf-8" ?><div>' . $stripped . '</div>';
        $dom->loadHTML($htmlWithEncoding, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*');

        if ($elements !== false) {
            /** @var \DOMElement $el */
            foreach ($elements as $el) {
                if ($el->tagName === 'div' && $el->parentNode === null) {
                    continue;
                }

                $attrsToRemove = [];
                if ($el->hasAttributes()) {
                    foreach ($el->attributes as $attr) {
                        $attrName = strtolower($attr->name);
                        if (str_starts_with($attrName, 'on') || in_array($attrName, ['style', 'id', 'class', 'src', 'formaction', 'srcset', 'data'], true)) {
                            $attrsToRemove[] = $attr->name;
                            continue;
                        }

                        if ($el->tagName === 'a') {
                            if ($attrName === 'href') {
                                $href = trim($attr->value);
                                $isSafeScheme = (str_starts_with($href, 'http://') || str_starts_with($href, 'https://') || str_starts_with($href, '/'))
                                    && !preg_match('~^(javascript|data|vbscript|file):~i', $href);

                                if (!$isSafeScheme) {
                                    $attrsToRemove[] = $attr->name;
                                }
                            } else {
                                $attrsToRemove[] = $attr->name;
                            }
                        } else {
                            $attrsToRemove[] = $attr->name;
                        }
                    }

                    foreach ($attrsToRemove as $badAttr) {
                        $el->removeAttribute($badAttr);
                    }
                }

                if ($el->tagName === 'a' && $el->hasAttribute('href')) {
                    $el->setAttribute('target', '_blank');
                    $el->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }

        $rootDiv = $dom->getElementsByTagName('div')->item(0);
        if (!$rootDiv) {
            return $stripped;
        }

        $cleanHtml = '';
        foreach ($rootDiv->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }

        return $cleanHtml;
    }
}
