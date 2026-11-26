<?php

declare(strict_types=1);

namespace App\services;

use GdImage;
use RuntimeException;

/**
 * FamilyHistorianCardRenderer
 *
 * Server-side 2x supersampled GD + FreeType graphic card synthesiser for the
 * "Do You Know?" Automated Timeline Posts Engine (£0 SaaS cost).
 *
 * Outputs crisp 1200x750 progressive JPEGs into resources/images/post/ and returns
 * the bare filename for post.post_img0.
 */
final class FamilyHistorianCardRenderer
{
    public const WIDTH = 1200;
    public const HEIGHT = 960;
    public const SPOTLIGHT_HEIGHT = 1200;
    public const COMPACT_HEIGHT = 680;
    private const SS = 2;

    /**
     * Render a "Do You Know?" card and save it to resources/images/post/<filename>.
     *
     * @param string $pillar One of: monthly_birthdays, occupation_skills, family_tree_roots, union_milestones, activity_milestone
     * @param array<string, mixed> $data
     * @param string $famCode
     * @param string $periodKey
     * @return string|null Bare filename for post.post_img0
     */
    public static function renderAndSave(string $pillar, array $data, string $famCode, string $periodKey): ?string
    {
        if (!extension_loaded('gd') || !function_exists('imagettftext')) {
            error_log('[FamilyHistorianCardRenderer] GD or FreeType extension unavailable.');
            return null;
        }

        try {
            $root = dirname(__DIR__, 2);
            $outDir = $root . '/resources/images/post';
            if (!is_dir($outDir) && !@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
                return null;
            }

            $hash = substr(hash('sha256', $famCode . '|' . $pillar . '|' . $periodKey), 0, 12);
            $safePillar = preg_replace('/[^a-z0-9_]/i', '', $pillar) ?? 'digest';
            $filename = 'historian_' . $safePillar . '_' . $hash . '.jpg';

            return self::renderToFilename($pillar, $data, $famCode, $filename) ? $filename : null;
        } catch (\Throwable $e) {
            error_log('[FamilyHistorianCardRenderer] Render error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Render a card directly into an existing or target bare filename under resources/images/post/.
     *
     * @param array<string, mixed> $data
     */
    public static function renderToFilename(string $pillar, array $data, string $famCode, string $filename): bool
    {
        if (!extension_loaded('gd') || !function_exists('imagettftext')) {
            return false;
        }
        $cleanName = basename(trim($filename));
        if ($cleanName === '' || !str_ends_with(strtolower($cleanName), '.jpg')) {
            return false;
        }

        try {
            $root = dirname(__DIR__, 2);
            $outDir = $root . '/resources/images/post';
            if (!is_dir($outDir) && !@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
                return false;
            }
            $fullPath = $outDir . '/' . $cleanName;

            $canvas = self::compose($pillar, $data, $famCode, $root);
            imageinterlace($canvas, true);
            ob_start();
            imagejpeg($canvas, null, 92);
            $jpeg = (string)ob_get_clean();
            imagedestroy($canvas);

            return $jpeg !== '' && @file_put_contents($fullPath, $jpeg) !== false;
        } catch (\Throwable $e) {
            error_log('[FamilyHistorianCardRenderer::renderToFilename] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compute the optimal output height (1x) for the given pillar and dataset
     * so cards never suffer from hollow bottom dead space.
     *
     * @param array<string, mixed> $data
     * @return int<540, 1200>
     */
    private static function resolveOutputHeight(string $pillar, array $data): int
    {
        $resolved = match ($pillar) {
            'milestone_spotlight' => self::SPOTLIGHT_HEIGHT,
            'monthly_birthdays' => (function () use ($data): int {
                $members = is_array($data['members'] ?? null) ? array_slice($data['members'], 0, 6) : [];
                $rows = max(1, (int)ceil(max(1, count($members)) / 2));
                // 1 row -> 580px, 2 rows -> 760px, 3 rows -> 940px
                return match ($rows) {
                    1 => 580,
                    2 => 760,
                    default => 940,
                };
            })(),
            'occupation_skills' => (function () use ($data): int {
                $items = is_array($data['items'] ?? null) ? array_slice($data['items'], 0, 5) : [];
                $cnt = max(1, count($items));
                return min(self::HEIGHT, max(540, 330 + $cnt * 120));
            })(),
            'union_milestones' => (function () use ($data): int {
                $unions = is_array($data['unions'] ?? null) ? array_slice($data['unions'], 0, 4) : [];
                $cnt = max(1, count($unions));
                return min(self::HEIGHT, max(560, 320 + $cnt * 155));
            })(),
            'family_tree_roots' => 760,
            'activity_milestone', 'activity' => 720,
            default => self::SPOTLIGHT_HEIGHT,
        };

        return max(540, min(1200, $resolved));
    }

    /**
     * Compose the 2x supersampled canvas and downsample to crisp 1x output.
     *
     * @param array<string, mixed> $data
     */
    private static function compose(string $pillar, array $data, string $famCode, string $root): GdImage
    {
        $outW = self::WIDTH;
        $outH = self::resolveOutputHeight($pillar, $data);
        $w = $outW * self::SS;
        $h = $outH * self::SS;
        $img = imagecreatetruecolor($w, $h);
        if ($img === false) {
            throw new RuntimeException('Failed to allocate GD canvas');
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $palette = self::paletteForPillar($pillar);
        if ($pillar === 'milestone_spotlight') {
            self::drawMilestoneSpotlightBody($img, $w, $h, $data, $palette, $root, $famCode);
        } else {
            self::drawGradientBackground($img, $w, $h, $palette['bg_top'], $palette['bg_bottom']);
            self::compositeSharedPosterBackdrop($img, $w, $h, $pillar, $root, $palette['bg_top']);
            self::drawAmbientGlow($img, $w, $h, $palette['accent']);

            // Subtle outer heritage frame
            self::drawRoundedRectOutline($img, 48, 48, $w - 48, $h - 48, 36, $palette['border'], 95, 3);

            // Header pill + Family Historian brand mark
            self::drawHeader($img, $w, $palette['pill_label'], $palette['accent'], $palette['accent_text'], $famCode);

            // Pillar-specific body
            match ($pillar) {
                'monthly_birthdays' => self::drawBirthdaysBody($img, $w, $h, $data, $palette, $root),
                'occupation_skills' => self::drawOccupationsBody($img, $w, $h, $data, $palette),
                'family_tree_roots' => self::drawFamilyTreeBody($img, $w, $h, $data, $palette),
                'union_milestones' => self::drawUnionsBody($img, $w, $h, $data, $palette),
                'activity_milestone', 'activity' => self::drawActivityBody($img, $w, $h, $data, $palette),
                default => self::drawFamilyTreeBody($img, $w, $h, $data, $palette),
            };

            // Footer watermark
            self::drawFooter($img, $w, $h, $famCode, $palette);
        }

        // Downsample 2x -> 1x for crisp anti-aliased edges and typography
        $out = imagecreatetruecolor($outW, $outH);
        if ($out === false) {
            imagedestroy($img);
            throw new RuntimeException('Failed to allocate output GD canvas');
        }
        imagecopyresampled($out, $img, 0, 0, 0, 0, $outW, $outH, $w, $h);
        imagedestroy($img);

        return $out;
    }

    /**
     * @return array{bg_top: array{0:int,1:int,2:int}, bg_bottom: array{0:int,1:int,2:int}, accent: array{0:int,1:int,2:int}, accent_text: array{0:int,1:int,2:int}, card_bg: array{0:int,1:int,2:int}, border: array{0:int,1:int,2:int}, pill_label: string}
     */
    private static function paletteForPillar(string $pillar): array
    {
        return match ($pillar) {
            'monthly_birthdays' => [
                'bg_top' => [30, 27, 75],       // Deep Indigo #1e1b4b
                'bg_bottom' => [49, 46, 129],   // Indigo #312e81
                'accent' => [245, 158, 11],     // Warm Gold #f59e0b
                'accent_text' => [15, 23, 42],  // Slate 900 on gold (high contrast)
                'card_bg' => [255, 255, 255],
                'border' => [252, 211, 77],
                'pill_label' => 'DO YOU KNOW?  •  BIRTHDAYS THIS MONTH',
            ],
            'occupation_skills' => [
                'bg_top' => [15, 23, 42],       // Slate 900 #0f172a
                'bg_bottom' => [30, 41, 59],    // Slate 800 #1e293b
                'accent' => [16, 185, 129],     // Emerald #10b981
                'accent_text' => [15, 23, 42],
                'card_bg' => [255, 255, 255],
                'border' => [110, 231, 183],
                'pill_label' => 'DO YOU KNOW?  •  OCCUPATIONS & SKILLS',
            ],
            'family_tree_roots' => [
                'bg_top' => [20, 44, 36],       // Deep Forest #142c24
                'bg_bottom' => [17, 58, 45],    // Forest #113a2d
                'accent' => [251, 191, 36],     // Amber #fbbf24
                'accent_text' => [15, 23, 42],
                'card_bg' => [255, 255, 255],
                'border' => [167, 243, 208],
                'pill_label' => 'DO YOU KNOW?  •  FAMILY TREE & ROOTS',
            ],
            'union_milestones' => [
                'bg_top' => [59, 17, 36],       // Deep Plum/Burgundy #3b1124
                'bg_bottom' => [88, 28, 56],    // Plum #581c38
                'accent' => [253, 224, 71],     // Champagne Gold #fde047
                'accent_text' => [15, 23, 42],
                'card_bg' => [255, 255, 255],
                'border' => [253, 186, 116],
                'pill_label' => 'DO YOU KNOW?  •  UNIONS & ANNIVERSARIES',
            ],
            'milestone_spotlight' => [
                'bg_top' => [30, 27, 75],       // Deep Imperial Indigo #1e1b4b
                'bg_bottom' => [15, 23, 42],    // Slate Obsidian #0f172a
                'accent' => [245, 158, 11],     // Warm Sovereign Gold #f59e0b
                'accent_text' => [15, 23, 42],
                'card_bg' => [255, 255, 255],
                'border' => [251, 191, 36],     // Gold Accent #fbbf24
                'pill_label' => 'FAMILY MILESTONE SPOTLIGHT',
            ],
            default => [
                'bg_top' => [23, 37, 84],       // Royal Navy #172554
                'bg_bottom' => [30, 58, 138],   // Blue 900 #1e3a8a
                'accent' => [56, 189, 248],     // Sky #38bdf8
                'accent_text' => [15, 23, 42],
                'card_bg' => [255, 255, 255],
                'border' => [147, 197, 253],
                'pill_label' => 'DO YOU KNOW?  •  MEMORY SPOTLIGHT',
            ],
        };
    }

    /**
     * @param array{0:int,1:int,2:int} $accent
     * @param array{0:int,1:int,2:int} $accentText
     */
    private static function drawHeader(GdImage $img, int $w, string $pillLabel, array $accent, array $accentText, string $famCode): void
    {
        $sansBold = self::font('sans_bold');
        $pillFontSize = 32;
        $bb = imagettfbbox($pillFontSize, 0, $sansBold, $pillLabel);
        $textW = $bb !== false ? abs($bb[2] - $bb[0]) : 620;
        $pillW = $textW + 84;
        $pillH = 76;
        $pillX = 110;
        $pillY = 96;

        self::drawFilledRoundedRect($img, $pillX, $pillY, $pillX + $pillW, $pillY + $pillH, 38, $accent, 0);
        imagettftext($img, $pillFontSize, 0, $pillX + 42, $pillY + 50, self::col($img, $accentText, 0), $sansBold, $pillLabel);

        // Family code badge on top right
        $cleanCode = '#' . ltrim(strtoupper(trim($famCode)), '#');
        $codeBb = imagettfbbox(32, 0, $sansBold, $cleanCode);
        $codeW = $codeBb !== false ? abs($codeBb[2] - $codeBb[0]) : 180;
        $rightX = $w - 110 - ($codeW + 64);
        self::drawFilledRoundedRect($img, $rightX, $pillY, $w - 110, $pillY + $pillH, 38, [255, 255, 255], 108);
        imagettftext($img, 32, 0, $rightX + 32, $pillY + 50, self::col($img, [255, 255, 255], 0), $sansBold, $cleanCode);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawBirthdaysBody(GdImage $img, int $w, int $h, array $data, array $palette, string $root): void
    {
        $serif = self::font('playfair');
        $sans = self::font('sans');
        /** @var array<int, array<string, mixed>> $members */
        $members = is_array($data['members'] ?? null) ? $data['members'] : [];
        $count = count($members);
        $monthName = (string)($data['month_name'] ?? date('F'));

        $headline = $count === 1
            ? "1 Birthday in {$monthName}"
            : "{$count} Birthdays in {$monthName}";
        imagettftext($img, 80, 0, 110, 300, self::col($img, [255, 255, 255], 0), $serif, $headline);

        $sub = 'Celebrating our wonderful family members this month';
        imagettftext($img, 38, 0, 110, 376, self::col($img, [226, 232, 240], 10), $sans, $sub);

        // Render up to 6 member cards in a 2-column x 3-row grid
        $shown = array_slice($members, 0, 6);
        $startX = 110;
        $startY = 445;
        $colW = 1040;
        $rowH = 330;
        $gapX = 100;
        $gapY = 42;

        $avatarColors = [
            [245, 158, 11],
            [16, 185, 129],
            [56, 189, 248],
            [244, 114, 182],
            [167, 139, 250],
            [251, 146, 60],
        ];

        foreach ($shown as $idx => $m) {
            $col = $idx % 2;
            $row = intdiv($idx, 2);
            $cx = $startX + $col * ($colW + $gapX);
            $cy = $startY + $row * ($rowH + $gapY);

            // Translucent glass card
            self::drawFilledRoundedRect($img, $cx, $cy, $cx + $colW, $cy + $rowH, 28, [255, 255, 255], 112);
            self::drawRoundedRectOutline($img, $cx, $cy, $cx + $colW, $cy + $rowH, 28, [255, 255, 255], 95, 2);

            $avatarCx = $cx + 115;
            $avatarCy = $cy + (int)($rowH / 2);
            $avatarR = 72;

            $name = self::truncateText((string)($m['full_name'] ?? 'Family Member'), $sans, 34, 680);
            $dayLabel = (string)($m['day_month_label'] ?? '');

            $drawnPhoto = self::drawCircularAvatar($img, (string)($m['avatar'] ?? ''), $avatarCx, $avatarCy, $avatarR, $root);
            if (!$drawnPhoto) {
                $bgCol = $avatarColors[$idx % count($avatarColors)];
                imagefilledellipse($img, $avatarCx, $avatarCy, $avatarR * 2, $avatarR * 2, self::col($img, $bgCol, 0));
                $initials = self::initials((string)($m['full_name'] ?? 'FM'));
                self::drawCenteredText($img, $initials, $sans, 42, $avatarCx, $avatarCy + 15, [15, 23, 42]);
            }

            imagettftext($img, 42, 0, $cx + 225, $cy + 145, self::col($img, [255, 255, 255], 0), $sans, $name);
            imagettftext($img, 34, 0, $cx + 225, $cy + 210, self::col($img, $palette['accent'], 0), $sans, $dayLabel);
        }

        if ($count > 6) {
            $more = '+' . ($count - 6) . ' more birthday' . (($count - 6) > 1 ? 's' : '') . ' this month';
            imagettftext($img, 32, 0, 110, $h - 175, self::col($img, $palette['accent'], 0), $sans, $more);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawOccupationsBody(GdImage $img, int $w, int $h, array $data, array $palette): void
    {
        $serif = self::font('playfair');
        $sans = self::font('sans');

        imagettftext($img, 78, 0, 110, 300, self::col($img, [255, 255, 255], 0), $serif, 'What Our Family Builds & Leads');

        $total = (int)($data['total_members'] ?? 0);
        $distinct = (int)($data['distinct_count'] ?? 0);
        $sub = "{$total} family members represented across {$distinct} professions & skills";
        imagettftext($img, 38, 0, 110, 376, self::col($img, [203, 213, 225], 5), $sans, $sub);

        /** @var array<int, array{label:string, count:int, pct:int}> $items */
        $items = array_slice(is_array($data['items'] ?? null) ? $data['items'] : [], 0, 5);
        $maxCount = 1;
        foreach ($items as $item) {
            if ((int)$item['count'] > $maxCount) {
                $maxCount = (int)$item['count'];
            }
        }

        $barColors = [
            [16, 185, 129],
            [56, 189, 248],
            [245, 158, 11],
            [167, 139, 250],
            [244, 114, 182],
        ];

        $y = 485;
        $trackX = 110;
        $trackW = $w - 220;
        foreach ($items as $idx => $item) {
            $label = self::truncateText((string)$item['label'], $sans, 38, 1400);
            $cnt = (int)$item['count'];
            $pct = (int)$item['pct'];
            $countLabel = $cnt . ' ' . ($cnt === 1 ? 'member' : 'members') . " ({$pct}%)";

            imagettftext($img, 38, 0, $trackX, $y, self::col($img, [255, 255, 255], 0), $sans, $label);
            $cBb = imagettfbbox(34, 0, $sans, $countLabel);
            $cW = $cBb !== false ? abs($cBb[2] - $cBb[0]) : 220;
            imagettftext($img, 34, 0, $trackX + $trackW - $cW, $y, self::col($img, [226, 232, 240], 5), $sans, $countLabel);

            $barY = $y + 30;
            $barH = 54;
            self::drawFilledRoundedRect($img, $trackX, $barY, $trackX + $trackW, $barY + $barH, 27, [255, 255, 255], 110);

            $fillW = max(60, (int)round(($cnt / $maxCount) * $trackW));
            self::drawFilledRoundedRect($img, $trackX, $barY, $trackX + $fillW, $barY + $barH, 27, $barColors[$idx % count($barColors)], 0);

            $y += 225;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawFamilyTreeBody(GdImage $img, int $w, int $h, array $data, array $palette): void
    {
        $serif = self::font('playfair');
        $sans = self::font('sans');

        imagettftext($img, 82, 0, 110, 300, self::col($img, [255, 255, 255], 0), $serif, 'Our Living Family Heritage');
        imagettftext($img, 38, 0, 110, 376, self::col($img, [209, 250, 229], 10), $sans, 'A snapshot of our growing family tree and global roots');

        $gens = (int)($data['generation_depth'] ?? 1);
        $branches = (int)($data['total_branches'] ?? 0);
        /** @var array<int, string> $countries */
        $countries = is_array($data['countries'] ?? null) ? $data['countries'] : [];
        $countryCount = count($countries);

        $cards = [
            ['value' => (string)$gens, 'label' => $gens === 1 ? 'Generation' : 'Generations'],
            ['value' => (string)$branches, 'label' => $branches === 1 ? 'Family Branch' : 'Family Branches'],
            ['value' => (string)max(1, $countryCount), 'label' => $countryCount === 1 ? 'Country' : 'Countries'],
        ];

        $cardW = 680;
        $cardH = 560;
        $gap = 70;
        $startX = 110;
        $cardY = 480;

        foreach ($cards as $i => $c) {
            $cx = $startX + $i * ($cardW + $gap);
            self::drawFilledRoundedRect($img, $cx, $cardY, $cx + $cardW, $cardY + $cardH, 36, [255, 255, 255], 112);
            self::drawRoundedRectOutline($img, $cx, $cardY, $cx + $cardW, $cardY + $cardH, 36, $palette['accent'], 50, 3);

            self::drawCenteredText($img, $c['value'], $serif, 156, $cx + (int)($cardW / 2), $cardY + 305, $palette['accent']);
            self::drawCenteredText($img, strtoupper($c['label']), $sans, 36, $cx + (int)($cardW / 2), $cardY + 440, [255, 255, 255]);
        }

        if ($countryCount > 0) {
            $list = 'Roots & Homes: ' . implode('  •  ', array_slice($countries, 0, 5));
            if ($countryCount > 5) {
                $list .= '  +' . ($countryCount - 5) . ' more';
            }
            $list = self::truncateText($list, $sans, 38, $w - 220);
            imagettftext($img, 38, 0, 110, $cardY + $cardH + 100, self::col($img, [226, 232, 240], 5), $sans, $list);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawUnionsBody(GdImage $img, int $w, int $h, array $data, array $palette): void
    {
        $serif = self::font('playfair');
        $sans = self::font('sans');

        imagettftext($img, 80, 0, 110, 300, self::col($img, [255, 255, 255], 0), $serif, 'Celebrating Enduring Unions');
        $totalUnions = (int)($data['total_unions'] ?? 0);
        $sub = "Honouring {$totalUnions} cherished " . ($totalUnions === 1 ? 'marriage & partnership' : 'marriages & partnerships') . ' in our family tree';
        imagettftext($img, 38, 0, 110, 376, self::col($img, [253, 230, 138], 5), $sans, $sub);

        /** @var array<int, array<string, mixed>> $unions */
        $unions = array_slice(is_array($data['unions'] ?? null) ? $data['unions'] : [], 0, 4);
        $y = 460;
        $cardW = $w - 220;
        $cardH = 240;

        foreach ($unions as $u) {
            self::drawFilledRoundedRect($img, 110, $y, 110 + $cardW, $y + $cardH, 30, [255, 255, 255], 112);
            self::drawRoundedRectOutline($img, 110, $y, 110 + $cardW, $y + $cardH, 30, $palette['accent'], 60, 2);

            $couple = self::truncateText((string)($u['couple_label'] ?? 'Family Couple'), $serif, 48, $cardW - 560);
            $milestone = (string)($u['milestone_label'] ?? 'Cherished Union');

            imagettftext($img, 48, 0, 160, $y + 138, self::col($img, [255, 255, 255], 0), $serif, $couple);

            $mBb = imagettfbbox(34, 0, $sans, $milestone);
            $mW = $mBb !== false ? abs($mBb[2] - $mBb[0]) : 280;
            $badgeX = 110 + $cardW - $mW - 96;
            self::drawFilledRoundedRect($img, $badgeX, $y + 78, 110 + $cardW - 45, $y + 162, 42, $palette['accent'], 0);
            imagettftext($img, 34, 0, $badgeX + 26, $y + 133, self::col($img, $palette['accent_text'], 0), $sans, $milestone);

            $y += $cardH + 42;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawActivityBody(GdImage $img, int $w, int $h, array $data, array $palette): void
    {
        $serif = self::font('playfair');
        $sans = self::font('sans');
        $sansBold = self::font('sans_bold');

        imagettftext($img, 82, 0, 110, 300, self::col($img, [255, 255, 255], 0), $serif, 'Most-Loved Family Moment');

        $peakDay = (string)($data['peak_day'] ?? 'Sunday');
        $totalPosts = (int)($data['total_posts'] ?? 0);
        $sub = "{$totalPosts} family stories shared recently  •  Peak day: {$peakDay}";
        imagettftext($img, 40, 0, 110, 376, self::col($img, [191, 219, 254], 5), $sans, $sub);

        // Spotlight quote card
        $cardX = 110;
        $cardY = 440;
        $cardW = $w - 220;
        $cardH = $h - 590;
        self::drawFilledRoundedRect($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 40, [255, 255, 255], 112);
        self::drawRoundedRectOutline($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 40, $palette['accent'], 50, 3);

        $author = self::stripEmojis((string)($data['top_author'] ?? 'Family Member'));
        $score = (int)($data['top_score'] ?? 0);
        $excerpt = self::sanitiseExcerpt((string)($data['top_excerpt'] ?? ''));

        // Wrap excerpt into max 4 lines at high-impact mobile-legible scale
        $lines = self::wrapText($excerpt, $serif, 64, $cardW - 160, 4);
        $lineCount = max(1, count($lines));
        $lineH = 98;
        $totalTextH = ($lineCount - 1) * $lineH + 64;
        $availH = $cardH - 180;
        $lineY = $cardY + max(130, intdiv(max(0, $availH - $totalTextH), 2) + 80);
        foreach ($lines as $line) {
            imagettftext($img, 64, 0, $cardX + 80, $lineY, self::col($img, [255, 255, 255], 0), $serif, $line);
            $lineY += $lineH;
        }

        // Author + love count
        $meta = "Shared by {$author}  •  {$score} " . ($score === 1 ? 'reaction' : 'reactions');
        imagettftext($img, 44, 0, $cardX + 80, $cardY + $cardH - 76, self::col($img, $palette['accent'], 0), $sansBold, $meta);
    }

    /**
     * Draw a high-prestige Royal Heritage Parchment & 24k Gold portrait card for an upcoming
     * personal birthday, anniversary, or family milestone.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $palette
     */
    private static function drawMilestoneSpotlightBody(
        GdImage $img,
        int $w,
        int $h,
        array $data,
        array $palette,
        string $root,
        string $famCode = ''
    ): void {
        $playfair = self::font('playfair');
        $cormorantItalic = self::font('cormorant_italic');
        $cinzel = self::font('cinzel');
        $sansBold = self::font('sans_bold');
        $sans = self::font('sans');

        $title = self::stripEmojis(trim((string)($data['title'] ?? 'Family Milestone')));
        $subtitle = self::stripEmojis(trim((string)($data['subtitle'] ?? 'Celebration')));
        $daysRemaining = (int)($data['days_remaining'] ?? 0);
        $targetDate = self::stripEmojis(trim((string)($data['target_date'] ?? '')));
        $avatarName = trim((string)($data['avatar'] ?? ''));
        $rawMsg = self::stripEmojis(trim((string)($data['meaningful_message'] ?? '')));
        $meaningfulMsg = $rawMsg !== ''
            ? $rawMsg
            : 'Another wonderful year of laughter, growth, and shared memories that enrich our family legacy.';
        $cleanCode = '#' . ltrim(strtoupper(trim($famCode !== '' ? $famCode : 'FAMILY')), '#');

        // 1. Rich Royal Bordeaux Velvet Outer Mat (#4a0e1e -> #24060e)
        self::drawGradientBackground($img, $w, $h, [74, 14, 30], [36, 6, 14]);

        // Outer fine gold frame on velvet mat
        imagerectangle($img, 20, 20, $w - 20, $h - 20, self::col($img, [184, 146, 68], 40));

        // 2. Main Warm Heritage Ivory Stationery Card (#fdfaf3) with 24k Gold Bezel
        $cardX = 40;
        $cardY = 40;
        $cardW = $w - 80;
        $cardH = $h - 80;
        $cx = intdiv($w, 2);

        // Soft drop shadow on velvet mat
        self::drawFilledRoundedRect($img, $cardX + 10, $cardY + 14, $cardX + $cardW + 10, $cardY + $cardH + 14, 44, [14, 2, 6], 45);
        // 24k Champagne Gold outer bezel (6px)
        self::drawFilledRoundedRect($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 42, [206, 166, 72], 0);
        // Warm Heritage Ivory Parchment surface (solid, 0 alpha -> razor-sharp contrast)
        self::drawFilledRoundedRect($img, $cardX + 8, $cardY + 8, $cardX + $cardW - 8, $cardY + $cardH - 8, 36, [253, 250, 243], 0);

        // Inner fine engraved double-rule border with corner diamonds
        $inX1 = $cardX + 28;
        $inY1 = $cardY + 28;
        $inX2 = $cardX + $cardW - 28;
        $inY2 = $cardY + $cardH - 28;
        imagerectangle($img, $inX1, $inY1, $inX2, $inY2, self::col($img, [212, 184, 128], 0));
        imagerectangle($img, $inX1 + 6, $inY1 + 6, $inX2 - 6, $inY2 - 6, self::col($img, [232, 217, 184], 0));

        $diamondCol = self::col($img, [184, 138, 44], 0);
        foreach ([[$inX1, $inY1], [$inX2, $inY1], [$inX1, $inY2], [$inX2, $inY2]] as [$dx, $dy]) {
            imagefilledpolygon($img, [$dx, $dy - 12, $dx + 12, $dy, $dx, $dy + 12, $dx - 12, $dy], $diamondCol);
        }

        // 3. Top Editorial Header Bar
        $headerY = $cardY + 56;
        $pillH = 88;
        $leftPillText = 'FAMILY MILESTONE SPOTLIGHT';
        $bbLeft = imagettfbbox(36, 0, $sansBold, $leftPillText);
        $leftTextW = $bbLeft !== false ? (int)abs($bbLeft[2] - $bbLeft[0]) : 720;
        $leftPillW = $leftTextW + 88;
        $leftPillX = $cardX + 64;

        // Solid Royal Bordeaux pill with gold border and crisp white bold text (11.4:1 contrast)
        self::drawFilledRoundedRect($img, $leftPillX - 3, $headerY - 3, $leftPillX + $leftPillW + 3, $headerY + $pillH + 3, 45, [206, 166, 72], 0);
        self::drawFilledRoundedRect($img, $leftPillX, $headerY, $leftPillX + $leftPillW, $headerY + $pillH, 44, [107, 18, 38], 0);
        imagettftext($img, 36, 0, $leftPillX + 44, $headerY + 58, self::col($img, [255, 255, 255], 0), $sansBold, $leftPillText);

        // Family Code Crest Pill on Top Right
        $bbCode = imagettfbbox(36, 0, $sansBold, $cleanCode);
        $codeW = $bbCode !== false ? (int)abs($bbCode[2] - $bbCode[0]) : 260;
        $rightPillW = $codeW + 84;
        $rightPillX = $cardX + $cardW - 64 - $rightPillW;
        self::drawFilledRoundedRect($img, $rightPillX - 3, $headerY - 3, $rightPillX + $rightPillW + 3, $headerY + $pillH + 3, 45, [196, 158, 78], 0);
        self::drawFilledRoundedRect($img, $rightPillX, $headerY, $rightPillX + $rightPillW, $headerY + $pillH, 44, [246, 238, 220], 0);
        imagettftext($img, 36, 0, $rightPillX + 42, $headerY + 58, self::col($img, [107, 18, 38], 0), $sansBold, $cleanCode);

        // 4. Centred Hero Portrait Medallion (Massive mobile-first focal point)
        $avatarCx = $cx;
        $avatarCy = $cardY + 515;
        $avatarR = 305;

        // Decorative lateral gold flourish wings flanking the portrait medallion
        $wingY = $avatarCy;
        imagefilledrectangle($img, $cardX + 100, $wingY - 2, $avatarCx - $avatarR - 68, $wingY + 2, self::col($img, [212, 184, 128], 0));
        imagefilledrectangle($img, $avatarCx + $avatarR + 68, $wingY - 2, $cardX + $cardW - 100, $wingY + 2, self::col($img, [212, 184, 128], 0));
        imagefilledpolygon($img, [$avatarCx - $avatarR - 68, $wingY - 12, $avatarCx - $avatarR - 56, $wingY, $avatarCx - $avatarR - 68, $wingY + 12, $avatarCx - $avatarR - 80, $wingY], $diamondCol);
        imagefilledpolygon($img, [$avatarCx + $avatarR + 68, $wingY - 12, $avatarCx + $avatarR + 80, $wingY, $avatarCx + $avatarR + 68, $wingY + 12, $avatarCx + $avatarR + 56, $wingY], $diamondCol);

        // Soft warm shadow beneath medallion
        imagefilledellipse($img, $avatarCx + 6, $avatarCy + 16, ($avatarR + 38) * 2, ($avatarR + 38) * 2, self::col($img, [226, 214, 190], 0));
        // Outer 24k Gold Bezel Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 30) * 2, ($avatarR + 30) * 2, self::col($img, [202, 160, 52], 0));
        // Middle Royal Bordeaux Inlay Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 18) * 2, ($avatarR + 18) * 2, self::col($img, [107, 18, 38], 0));
        // Inner Warm Ivory Mat Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 10) * 2, ($avatarR + 10) * 2, self::col($img, [253, 250, 243], 0));
        // Portrait disc backing
        imagefilledellipse($img, $avatarCx, $avatarCy, $avatarR * 2, $avatarR * 2, self::col($img, [107, 18, 38], 0));

        $drawn = self::drawCircularAvatar($img, $avatarName, $avatarCx, $avatarCy, $avatarR, $root);
        if (!$drawn) {
            $initials = self::initials($title);
            self::drawCenteredText($img, $initials, $cinzel, 174, $avatarCx, $avatarCy + 60, [253, 230, 138]);
        }

        // Date Ribbon Seal overlapping bottom of Portrait Medallion
        $dateSealText = $targetDate !== '' ? strtoupper($targetDate) : 'SPECIAL DATE';
        $bbDate = imagettfbbox(38, 0, $sansBold, $dateSealText);
        $dateTextW = $bbDate !== false ? (int)abs($bbDate[2] - $bbDate[0]) : 360;
        $sealW = (int)max(480, $dateTextW + 124);
        $sealH = 98;
        $sealX = $avatarCx - intdiv($sealW, 2);
        $sealY = $avatarCy + $avatarR - 38;

        self::drawFilledRoundedRect($img, $sealX - 4, $sealY - 4, $sealX + $sealW + 4, $sealY + $sealH + 4, 51, [202, 160, 52], 0);
        self::drawFilledRoundedRect($img, $sealX, $sealY, $sealX + $sealW, $sealY + $sealH, 49, [107, 18, 38], 0);
        self::drawCenteredText($img, $dateSealText, $sansBold, 38, $avatarCx, $sealY + 64, [255, 255, 255]);

        // 5. Full-Width Centred Editorial Typography Stack (Dynamically distributed to eliminate any bottom dead space)
        $contentMaxW = $cardW - 140; // 2180px full width

        // Pre-measure Countdown Eyebrow Pill
        $badgeText = match ($daysRemaining) {
            0 => 'CELEBRATING TODAY  •  HAPPY BIRTHDAY',
            1 => 'CELEBRATING TOMORROW  •  SAVE THE DATE',
            default => 'UPCOMING IN ' . $daysRemaining . ' DAYS  •  SAVE THE DATE',
        };
        $bbBadge = imagettfbbox(38, 0, $sansBold, $badgeText);
        $badgeTextW = $bbBadge !== false ? (int)abs($bbBadge[2] - $bbBadge[0]) : 760;
        $badgeW = $badgeTextW + 108;
        $badgeH = 94;

        // Pre-measure Celebrant Display Headline in Playfair Display (Full-width 136pt single line, or 106pt wrapped)
        $bbSingleTitle = imagettfbbox(136, 0, $playfair, $title);
        $singleTitleW = $bbSingleTitle !== false ? (int)abs($bbSingleTitle[2] - $bbSingleTitle[0]) : $contentMaxW + 1;
        if ($singleTitleW <= $contentMaxW) {
            $titleFontSize = 136;
            $titleLines = [$title];
            $titleLineH = 148;
        } else {
            $bbMedTitle = imagettfbbox(112, 0, $playfair, $title);
            $medTitleW = $bbMedTitle !== false ? (int)abs($bbMedTitle[2] - $bbMedTitle[0]) : $contentMaxW + 1;
            if ($medTitleW <= $contentMaxW) {
                $titleFontSize = 112;
                $titleLines = [$title];
                $titleLineH = 124;
            } else {
                $titleFontSize = 98;
                $titleLines = self::wrapText($title, $playfair, $titleFontSize, $contentMaxW, 2);
                $titleLineH = 112;
            }
        }
        $titleBlockH = count($titleLines) * $titleLineH;

        // Pre-measure Subtitle
        $subUpper = strtoupper($subtitle);
        if (!str_contains($subUpper, 'CELEBRATION') && !str_contains($subUpper, 'MILESTONE') && !str_contains($subUpper, 'YEARS')) {
            $subUpper .= '  •  FAMILY MILESTONE CELEBRATION';
        }
        $cleanSub = self::truncateText($subUpper, $cinzel, 48, $contentMaxW);

        // Pre-measure Quote Box (80pt italic editorial typography)
        $quoteFontSize = 80;
        $quoteLineH = 110;
        $quoteBoxW = $contentMaxW - 50;
        $quoteBoxX = $cx - intdiv($quoteBoxW, 2);
        $quoteLines = self::wrapText('"' . $meaningfulMsg . '"', $cormorantItalic, $quoteFontSize, $quoteBoxW - 140, 3);
        $quoteCount = max(1, count($quoteLines));
        $quoteBoxH = max(295, 108 + ($quoteCount - 1) * $quoteLineH + $quoteFontSize);

        // Pre-measure Call-to-Action Banner (42pt bold typography)
        $callToAction = match ($daysRemaining) {
            0 => 'Shower with warm wishes, favourite memories, and blessings in the comments below!',
            1 => 'Get your heartfelt wishes and celebration messages ready for tomorrow!',
            default => 'Mark your calendar! Share a warm family memory or celebration wish below.',
        };
        $ctaFontSize = 42;
        $ctaLines = self::wrapText($callToAction, $sansBold, $ctaFontSize, $quoteBoxW - 96, 2);
        $ctaH = count($ctaLines) > 1 ? 168 : 118;

        // Dynamically balance vertical rhythm between Date Seal bottom and Footer top
        $footY = $cardY + $cardH - 56;
        $stackTop = $sealY + $sealH + 46;
        $stackBottomLimit = $footY - 64;
        $fixedContentH = $badgeH + $titleBlockH + 54 + 14 + $quoteBoxH + $ctaH + 40;
        $remainingSlack = max(160, $stackBottomLimit - $stackTop - $fixedContentH);
        $stepGap = intdiv($remainingSlack, 5);

        // Render Countdown Eyebrow Pill
        $badgeX = $cx - intdiv($badgeW, 2);
        $badgeY = $stackTop;
        self::drawFilledRoundedRect($img, $badgeX - 3, $badgeY - 3, $badgeX + $badgeW + 3, $badgeY + $badgeH + 3, 48, [217, 119, 6], 0);
        self::drawFilledRoundedRect($img, $badgeX, $badgeY, $badgeX + $badgeW, $badgeY + $badgeH, 46, [254, 243, 199], 0);
        self::drawCenteredText($img, $badgeText, $sansBold, 38, $cx, $badgeY + 61, [120, 53, 15]);
        $curY = $badgeY + $badgeH + max(28, intdiv($stepGap * 4, 5));

        // Render Celebrant Display Headline
        $headlineBaseline = $curY + $titleFontSize - 10;
        foreach ($titleLines as $tLine) {
            self::drawCenteredText($img, $tLine, $playfair, $titleFontSize, $cx, $headlineBaseline, [28, 25, 23]);
            self::drawCenteredText($img, $tLine, $playfair, $titleFontSize, $cx + 1, $headlineBaseline, [28, 25, 23]);
            $headlineBaseline += $titleLineH;
        }
        $curY += $titleBlockH + max(20, intdiv($stepGap * 3, 5));

        // Render Engraved Occasion Subtitle
        self::drawCenteredText($img, $cleanSub, $cinzel, 48, $cx, $curY + 44, [136, 19, 55]);
        self::drawCenteredText($img, $cleanSub, $cinzel, 48, $cx + 1, $curY + 44, [136, 19, 55]);
        $curY += 54 + max(24, intdiv($stepGap * 3, 5));

        // Render Ornamental Burnished Gold Divider Rule with Centre Diamond
        $ruleW = 1520;
        $ruleY = $curY;
        imagefilledrectangle($img, $cx - intdiv($ruleW, 2), $ruleY, $cx - 24, $ruleY + 4, self::col($img, [202, 160, 52], 0));
        imagefilledrectangle($img, $cx + 24, $ruleY, $cx + intdiv($ruleW, 2), $ruleY + 4, self::col($img, [202, 160, 52], 0));
        imagefilledpolygon($img, [$cx, $ruleY - 10, $cx + 12, $ruleY + 2, $cx, $ruleY + 14, $cx - 12, $ruleY + 2], $diamondCol);
        $curY += 14 + $stepGap;

        // Render Full-Width Editorial Tribute Quote Panel
        $crimsonCol = self::col($img, [136, 19, 55], 0);
        self::drawFilledRoundedRect($img, $quoteBoxX, $curY, $quoteBoxX + $quoteBoxW, $curY + $quoteBoxH, 28, [245, 238, 222], 0);
        imagefilledrectangle($img, $quoteBoxX, $curY + 22, $quoteBoxX + 16, $curY + $quoteBoxH - 22, $crimsonCol);
        imagefilledrectangle($img, $quoteBoxX + $quoteBoxW - 16, $curY + 22, $quoteBoxX + $quoteBoxW, $curY + $quoteBoxH - 22, $crimsonCol);

        $qTotalTextH = ($quoteCount - 1) * $quoteLineH + $quoteFontSize;
        $qY = $curY + intdiv(max(0, $quoteBoxH - $qTotalTextH), 2) + $quoteFontSize - 10;
        foreach ($quoteLines as $qLine) {
            self::drawCenteredText($img, $qLine, $cormorantItalic, $quoteFontSize, $cx, $qY, [41, 37, 36]);
            self::drawCenteredText($img, $qLine, $cormorantItalic, $quoteFontSize, $cx + 1, $qY, [41, 37, 36]);
            $qY += $quoteLineH;
        }
        $curY += $quoteBoxH + $stepGap;

        // Render Full-Width Call-to-Action Banner
        self::drawFilledRoundedRect($img, $quoteBoxX - 3, $curY - 3, $quoteBoxX + $quoteBoxW + 3, $curY + $ctaH + 3, 28, [206, 166, 72], 0);
        self::drawFilledRoundedRect($img, $quoteBoxX, $curY, $quoteBoxX + $quoteBoxW, $curY + $ctaH, 26, [250, 243, 224], 0);

        $ctaLineH = 58;
        $ctaTextY = count($ctaLines) > 1 ? ($curY + 66) : ($curY + 74);
        foreach ($ctaLines as $cLine) {
            self::drawCenteredText($img, $cLine, $sansBold, $ctaFontSize, $cx, $ctaTextY, [107, 18, 38]);
            $ctaTextY += $ctaLineH;
        }
        $curY += $ctaH + max(32, $stepGap);

        // Render Engraved Family Dynasty Sign-Off
        $signOff = self::truncateText('WITH WARMEST BLESSINGS  •  THE ' . $cleanCode . ' FAMILY DYNASTY', $cinzel, 38, $contentMaxW);
        self::drawCenteredText($img, $signOff, $cinzel, 38, $cx, min($footY - 68, $curY + 36), [146, 102, 32]);
        self::drawCenteredText($img, $signOff, $cinzel, 38, $cx + 1, min($footY - 68, $curY + 36), [146, 102, 32]);

        // 6. Card Bottom Heritage Watermark (Anchored cleanly above lower border; leaves right corner clear for Animate pill)
        $footLeft = 'FamilyPlatform  •  Family Historian  •  Private Family Timeline';
        imagettftext($img, 32, 0, $cardX + 74, $footY, self::col($img, [120, 100, 74], 0), $sans, $footLeft);
    }

    /**
     * @param array<string, mixed> $palette
     */
    private static function drawFooter(GdImage $img, int $w, int $h, string $famCode, array $palette): void
    {
        $sans = self::font('sans');
        $left = 'FamilyPlatform  •  Family Historian';
        $right = 'Private Family Timeline';

        imagettftext($img, 30, 0, 110, $h - 84, self::col($img, [226, 232, 240], 20), $sans, $left);
        $bb = imagettfbbox(30, 0, $sans, $right);
        $rw = $bb !== false ? abs($bb[2] - $bb[0]) : 310;
        imagettftext($img, 30, 0, $w - 110 - $rw, $h - 84, self::col($img, [226, 232, 240], 20), $sans, $right);
    }

    /**
     * Safely load and draw a circular member avatar from resources/images/profile/ or public/uploads/.
     */
    private static function drawCircularAvatar(GdImage $dst, string $avatarName, int $cx, int $cy, int $radius, string $root): bool
    {
        $clean = basename(trim($avatarName));
        if ($clean === '') {
            return false;
        }

        // If SVG, check for corresponding PNG raster fallback
        if (str_ends_with(strtolower($clean), '.svg')) {
            $pngCandidate = substr($clean, 0, -4) . '.png';
            if (is_file($root . '/resources/images/profile/' . $pngCandidate)) {
                $clean = $pngCandidate;
            } elseif (is_file($root . '/public/uploads/' . $pngCandidate)) {
                $clean = $pngCandidate;
            } else {
                return false;
            }
        }

        $path = $root . '/resources/images/profile/' . $clean;
        if (!is_file($path)) {
            $path = $root . '/public/uploads/' . $clean;
            if (!is_file($path)) {
                return false;
            }
        }

        $info = @getimagesize($path);
        if (!is_array($info)) {
            return false;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
        if ($src === false) {
            return false;
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        $side = min($sw, $sh);
        $sx = (int)(($sw - $side) / 2);
        $sy = (int)(($sh - $side) / 2);
        $diam = max(1, $radius * 2);

        $tmp = imagecreatetruecolor($diam, $diam);
        if ($tmp === false) {
            imagedestroy($src);
            return false;
        }
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $src, 0, 0, $sx, $sy, $diam, $diam, $side, $side);
        imagedestroy($src);

        // Copy pixels inside circle preserving alpha
        $rSq = $radius * $radius;
        for ($py = 0; $py < $diam; $py++) {
            $dy = $py - $radius;
            for ($px = 0; $px < $diam; $px++) {
                $dx = $px - $radius;
                if (($dx * $dx + $dy * $dy) <= $rSq) {
                    $rgb = imagecolorat($tmp, $px, $py);
                    if ($rgb !== false) {
                        $alpha = ($rgb >> 24) & 0x7F;
                        if ($alpha < 127) {
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;
                            $col = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
                            if ($col !== false) {
                                imagesetpixel($dst, $cx - $radius + $px, $cy - $radius + $py, $col);
                            }
                        }
                    }
                }
            }
        }

        imagedestroy($tmp);
        return true;
    }

    /**
     * @param array{0:int,1:int,2:int} $top
     * @param array{0:int,1:int,2:int} $bottom
     */
    private static function drawGradientBackground(GdImage $img, int $w, int $h, array $top, array $bottom): void
    {
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $r = max(0, min(255, (int)round($top[0] + ($bottom[0] - $top[0]) * $t)));
            $g = max(0, min(255, (int)round($top[1] + ($bottom[1] - $top[1]) * $t)));
            $b = max(0, min(255, (int)round($top[2] + ($bottom[2] - $top[2]) * $t)));
            $col = imagecolorallocate($img, $r, $g, $b);
            if ($col !== false) {
                imageline($img, 0, $y, $w, $y, $col);
            }
        }
    }

    /**
     * Composite curated heritage texture from modernman00/shared-lib poster-assets
     * with a dark readability scrim so typography maintains WCAG AA/AAA contrast.
     *
     * @param array{0:int,1:int,2:int} $scrimRgb
     */
    private static function compositeSharedPosterBackdrop(GdImage $img, int $w, int $h, string $pillar, string $root, array $scrimRgb): void
    {
        $assetName = match ($pillar) {
            'union_milestones' => 'bg_crimson_velvet_floral.jpg',
            'monthly_birthdays', 'family_tree_roots', 'milestone_spotlight' => 'bg_family_golden_jubilee.jpg',
            default => 'bg_vip_gold_lounge.jpg',
        };
        $path = $root . '/vendor/modernman00/shared-lib/resources/poster-assets/' . $assetName;
        if (!is_file($path)) {
            return;
        }
        $bg = @imagecreatefromjpeg($path);
        if ($bg === false) {
            return;
        }
        $bw = imagesx($bg);
        $bh = imagesy($bg);
        $scale = max($w / $bw, $h / $bh);
        $dw = (int)round($bw * $scale);
        $dh = (int)round($bh * $scale);
        $dx = (int)round(($w - $dw) / 2);
        $dy = (int)round(($h - $dh) / 2);
        imagecopyresampled($img, $bg, $dx, $dy, 0, 0, $dw, $dh, $bw, $bh);
        // Apply 78% opacity dark scrim so white/gold typography pops cleanly
        imagefilledrectangle($img, 0, 0, $w, $h, self::col($img, $scrimRgb, 28));
        imagedestroy($bg);
    }

    /**
     * @param array{0:int,1:int,2:int} $accent
     */
    private static function drawAmbientGlow(GdImage $img, int $w, int $h, array $accent): void
    {
        for ($r = 900; $r > 100; $r -= 60) {
            $alpha = (int)min(125, 114 + ($r / 900) * 12);
            imagefilledellipse($img, $w - 220, 180, $r, $r, self::col($img, $accent, $alpha));
        }
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function drawFilledRoundedRect(GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, array $rgb, int $alpha): void
    {
        $c = self::col($img, $rgb, $alpha);
        $r = max(1, min($r, intdiv(max(2, $x2 - $x1), 2), intdiv(max(2, $y2 - $y1), 2)));
        // Center strip + non-overlapping left/right strips + 4 quarter-circle arcs (zero double-blending)
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $c);
        if ($y1 + $r <= $y2 - $r) {
            imagefilledrectangle($img, $x1, $y1 + $r, $x1 + $r - 1, $y2 - $r, $c);
            imagefilledrectangle($img, $x2 - $r + 1, $y1 + $r, $x2, $y2 - $r, $c);
        }
        imagefilledarc($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $c, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $c, IMG_ARC_PIE);
        imagefilledarc($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $c, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $c, IMG_ARC_PIE);
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function drawRoundedRectOutline(GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, array $rgb, int $alpha, int $thickness): void
    {
        $c = self::col($img, $rgb, $alpha);
        imagesetthickness($img, $thickness);
        imageline($img, $x1 + $r, $y1, $x2 - $r, $y1, $c);
        imageline($img, $x1 + $r, $y2, $x2 - $r, $y2, $c);
        imageline($img, $x1, $y1 + $r, $x1, $y2 - $r, $c);
        imageline($img, $x2, $y1 + $r, $x2, $y2 - $r, $c);
        imagearc($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, 180, 270, $c);
        imagearc($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, 270, 360, $c);
        imagearc($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, 90, 180, $c);
        imagearc($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, 0, 90, $c);
        imagesetthickness($img, 1);
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function drawCenteredText(GdImage $img, string $text, string $font, int $size, int $cx, int $baselineY, array $rgb): void
    {
        $bb = imagettfbbox($size, 0, $font, $text);
        $tw = $bb !== false ? abs($bb[2] - $bb[0]) : 0;
        $x = $cx - (int)($tw / 2);
        imagettftext($img, $size, 0, $x, $baselineY, self::col($img, $rgb, 0), $font, $text);
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function col(GdImage $img, array $rgb, int $alpha): int
    {
        $r = max(0, min(255, $rgb[0]));
        $g = max(0, min(255, $rgb[1]));
        $b = max(0, min(255, $rgb[2]));
        $allocated = imagecolorallocatealpha($img, $r, $g, $b, max(0, min(127, $alpha)));
        return $allocated !== false ? $allocated : 0;
    }

    private static function font(string $key): string
    {
        $root = dirname(__DIR__, 2);
        $festive = $root . '/resources/fonts/festive/';
        $mpdf = $root . '/vendor/mpdf/mpdf/ttfonts/';

        $candidates = match ($key) {
            'playfair' => [$festive . 'PlayfairDisplay.ttf', $mpdf . 'DejaVuSerif-Bold.ttf', $mpdf . 'DejaVuSerif.ttf'],
            'serif_bold' => [$mpdf . 'DejaVuSerif-Bold.ttf', $festive . 'PlayfairDisplay.ttf', $mpdf . 'DejaVuSerif.ttf'],
            'serif_italic' => [$mpdf . 'DejaVuSerif-Italic.ttf', $festive . 'CormorantGaramond-Italic.ttf'],
            'cormorant_italic' => [$festive . 'CormorantGaramond-Italic.ttf', $mpdf . 'DejaVuSerif-Italic.ttf'],
            'cinzel' => [$festive . 'Cinzel.ttf', $mpdf . 'DejaVuSerif-Bold.ttf'],
            'sans_bold' => [$mpdf . 'DejaVuSans-Bold.ttf', $mpdf . 'FreeSansBold.ttf'],
            default => [$mpdf . 'DejaVuSans.ttf', $mpdf . 'FreeSans.ttf', $festive . 'Montserrat.ttf'],
        };

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $mpdf . 'DejaVuSans.ttf';
    }

    private static function initials(string $name): string
    {
        $clean = (string)preg_replace("/(?:'s\s+)?(?:birthday|anniversary|wedding|celebration|milestone|gathering|event)\b.*$/iu", '', trim($name));
        $clean = trim($clean !== '' ? $clean : $name);
        $parts = preg_split('/[\s&]+/', $clean);
        if (!is_array($parts) || empty($parts)) {
            return 'FM';
        }
        $parts = array_values(array_filter($parts, static fn(string $p): bool => $p !== ''));
        if (empty($parts)) {
            return 'FM';
        }
        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';
        return mb_strtoupper($first . $last);
    }

    private static function truncateText(string $text, string $font, int $size, int $maxWidth): string
    {
        $bb = imagettfbbox($size, 0, $font, $text);
        if ($bb !== false && abs($bb[2] - $bb[0]) <= $maxWidth) {
            return $text;
        }
        $len = mb_strlen($text);
        while ($len > 3) {
            $len--;
            $cand = mb_substr($text, 0, $len) . '…';
            $cBb = imagettfbbox($size, 0, $font, $cand);
            if ($cBb !== false && abs($cBb[2] - $cBb[0]) <= $maxWidth) {
                return $cand;
            }
        }
        return $text;
    }

    /**
     * @return array<int, string>
     */
    private static function wrapText(string $text, string $font, int $size, int $maxWidth, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text));
        if (!is_array($words) || empty($words)) {
            return [];
        }
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $try = $current === '' ? $word : ($current . ' ' . $word);
            $bb = imagettfbbox($size, 0, $font, $try);
            $w = $bb !== false ? abs($bb[2] - $bb[0]) : 0;
            if ($w <= $maxWidth) {
                $current = $try;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                    if (count($lines) >= $maxLines) {
                        $lines[$maxLines - 1] = self::truncateText($lines[$maxLines - 1] . '…', $font, $size, $maxWidth);
                        return $lines;
                    }
                }
                $wordBb = imagettfbbox($size, 0, $font, $word);
                $wordW = $wordBb !== false ? abs($wordBb[2] - $wordBb[0]) : 0;
                $current = $wordW > $maxWidth ? self::truncateText($word, $font, $size, $maxWidth) : $word;
            }
        }
        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = self::truncateText($current, $font, $size, $maxWidth);
        }
        return $lines;
    }

    /**
     * Strip raw media paths, URLs, emojis, and HTML from spotlight excerpts before rendering.
     */
    private static function sanitiseExcerpt(string $raw): string
    {
        $clean = (string)preg_replace('#https?://[^\s]+#i', '', $raw);
        $clean = (string)preg_replace('#/?resources/(?:videos|images|audio)/[^\s]+#i', '', $clean);
        $clean = self::stripEmojis(strip_tags($clean));
        $clean = trim((string)preg_replace('/\s+/', ' ', $clean));
        return $clean !== '' ? $clean : 'A cherished family memory shared on the timeline.';
    }

    /**
     * Strip supplemental emoji and pictograph codepoints that lack glyphs in serif/sans TTF fonts
     * so GD FreeType never renders tofu boxes ("Ð▯▯").
     */
    private static function stripEmojis(string $text): string
    {
        $clean = (string)preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{200D}]+/u', '', $text);
        return trim((string)preg_replace('/\s{2,}/u', ' ', $clean));
    }
}
