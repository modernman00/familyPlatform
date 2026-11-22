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
    public const HEIGHT = 750;
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
            $fullPath = $outDir . '/' . $filename;

            $canvas = self::compose($pillar, $data, $famCode, $root);
            imageinterlace($canvas, true);
            ob_start();
            imagejpeg($canvas, null, 92);
            $jpeg = (string)ob_get_clean();
            imagedestroy($canvas);

            if ($jpeg === '' || @file_put_contents($fullPath, $jpeg) === false) {
                return null;
            }

            return $filename;
        } catch (\Throwable $e) {
            error_log('[FamilyHistorianCardRenderer] Render error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Compose the 2x supersampled canvas and downsample to 1200x750.
     *
     * @param array<string, mixed> $data
     */
    private static function compose(string $pillar, array $data, string $famCode, string $root): GdImage
    {
        $w = self::WIDTH * self::SS;
        $h = self::HEIGHT * self::SS;
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
                'activity_milestone' => self::drawActivityBody($img, $w, $h, $data, $palette),
                default => self::drawFamilyTreeBody($img, $w, $h, $data, $palette),
            };

            // Footer watermark
            self::drawFooter($img, $w, $h, $famCode, $palette);
        }

        // Downsample 2x -> 1x for crisp anti-aliased edges and typography
        $out = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($out === false) {
            imagedestroy($img);
            throw new RuntimeException('Failed to allocate output GD canvas');
        }
        imagecopyresampled($out, $img, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, $w, $h);
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
        $sansBold = self::font('sans');
        $pillFontSize = 24;
        $bb = imagettfbbox($pillFontSize, 0, $sansBold, $pillLabel);
        $textW = $bb !== false ? abs($bb[2] - $bb[0]) : 500;
        $pillW = $textW + 72;
        $pillH = 60;
        $pillX = 110;
        $pillY = 100;

        self::drawFilledRoundedRect($img, $pillX, $pillY, $pillX + $pillW, $pillY + $pillH, 30, $accent, 0);
        imagettftext($img, $pillFontSize, 0, $pillX + 36, $pillY + 40, self::col($img, $accentText, 0), $sansBold, $pillLabel);

        // Family code badge on top right
        $cleanCode = '#' . ltrim(strtoupper(trim($famCode)), '#');
        $codeBb = imagettfbbox(24, 0, $sansBold, $cleanCode);
        $codeW = $codeBb !== false ? abs($codeBb[2] - $codeBb[0]) : 140;
        $rightX = $w - 110 - ($codeW + 48);
        self::drawFilledRoundedRect($img, $rightX, $pillY, $w - 110, $pillY + $pillH, 30, [255, 255, 255], 108);
        imagettftext($img, 24, 0, $rightX + 24, $pillY + 40, self::col($img, [255, 255, 255], 0), $sansBold, $cleanCode);
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
        imagettftext($img, 68, 0, 110, 275, self::col($img, [255, 255, 255], 0), $serif, $headline);

        $sub = 'Celebrating our wonderful family members this month';
        imagettftext($img, 30, 0, 110, 338, self::col($img, [226, 232, 240], 15), $sans, $sub);

        // Render up to 6 member cards in a 2-column x 3-row grid
        $shown = array_slice($members, 0, 6);
        $startX = 110;
        $startY = 400;
        $colW = 1040;
        $rowH = 260;
        $gapX = 100;
        $gapY = 34;

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

            imagettftext($img, 36, 0, $cx + 225, $cy + 112, self::col($img, [255, 255, 255], 0), $sans, $name);
            imagettftext($img, 28, 0, $cx + 225, $cy + 170, self::col($img, $palette['accent'], 0), $sans, $dayLabel);
        }

        if ($count > 6) {
            $more = '+' . ($count - 6) . ' more birthday' . (($count - 6) > 1 ? 's' : '') . ' this month';
            imagettftext($img, 26, 0, 110, $h - 155, self::col($img, $palette['accent'], 0), $sans, $more);
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

        imagettftext($img, 66, 0, 110, 275, self::col($img, [255, 255, 255], 0), $serif, 'What Our Family Builds & Leads');

        $total = (int)($data['total_members'] ?? 0);
        $distinct = (int)($data['distinct_count'] ?? 0);
        $sub = "{$total} family members represented across {$distinct} professions & skills";
        imagettftext($img, 30, 0, 110, 338, self::col($img, [203, 213, 225], 10), $sans, $sub);

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

        $y = 430;
        $trackX = 110;
        $trackW = $w - 220;
        foreach ($items as $idx => $item) {
            $label = self::truncateText((string)$item['label'], $sans, 32, 1400);
            $cnt = (int)$item['count'];
            $pct = (int)$item['pct'];
            $countLabel = $cnt . ' ' . ($cnt === 1 ? 'member' : 'members') . " ({$pct}%)";

            imagettftext($img, 32, 0, $trackX, $y, self::col($img, [255, 255, 255], 0), $sans, $label);
            $cBb = imagettfbbox(28, 0, $sans, $countLabel);
            $cW = $cBb !== false ? abs($cBb[2] - $cBb[0]) : 200;
            imagettftext($img, 28, 0, $trackX + $trackW - $cW, $y, self::col($img, [226, 232, 240], 10), $sans, $countLabel);

            $barY = $y + 24;
            $barH = 44;
            self::drawFilledRoundedRect($img, $trackX, $barY, $trackX + $trackW, $barY + $barH, 22, [255, 255, 255], 110);

            $fillW = max(60, (int)round(($cnt / $maxCount) * $trackW));
            self::drawFilledRoundedRect($img, $trackX, $barY, $trackX + $fillW, $barY + $barH, 22, $barColors[$idx % count($barColors)], 0);

            $y += 165;
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

        imagettftext($img, 68, 0, 110, 275, self::col($img, [255, 255, 255], 0), $serif, 'Our Living Family Heritage');
        imagettftext($img, 30, 0, 110, 338, self::col($img, [209, 250, 229], 15), $sans, 'A snapshot of our growing family tree and global roots');

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
        $cardH = 420;
        $gap = 70;
        $startX = 110;
        $cardY = 440;

        foreach ($cards as $i => $c) {
            $cx = $startX + $i * ($cardW + $gap);
            self::drawFilledRoundedRect($img, $cx, $cardY, $cx + $cardW, $cardY + $cardH, 36, [255, 255, 255], 112);
            self::drawRoundedRectOutline($img, $cx, $cardY, $cx + $cardW, $cardY + $cardH, 36, $palette['accent'], 50, 3);

            self::drawCenteredText($img, $c['value'], $serif, 128, $cx + (int)($cardW / 2), $cardY + 230, $palette['accent']);
            self::drawCenteredText($img, strtoupper($c['label']), $sans, 30, $cx + (int)($cardW / 2), $cardY + 335, [255, 255, 255]);
        }

        if ($countryCount > 0) {
            $list = 'Roots & Homes: ' . implode('  •  ', array_slice($countries, 0, 5));
            if ($countryCount > 5) {
                $list .= '  +' . ($countryCount - 5) . ' more';
            }
            $list = self::truncateText($list, $sans, 28, $w - 220);
            imagettftext($img, 28, 0, 110, 1020, self::col($img, [226, 232, 240], 10), $sans, $list);
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

        imagettftext($img, 68, 0, 110, 275, self::col($img, [255, 255, 255], 0), $serif, 'Celebrating Enduring Unions');
        $totalUnions = (int)($data['total_unions'] ?? 0);
        $sub = "Honouring {$totalUnions} cherished " . ($totalUnions === 1 ? 'marriage & partnership' : 'marriages & partnerships') . ' in our family tree';
        imagettftext($img, 30, 0, 110, 338, self::col($img, [253, 230, 138], 10), $sans, $sub);

        /** @var array<int, array<string, mixed>> $unions */
        $unions = array_slice(is_array($data['unions'] ?? null) ? $data['unions'] : [], 0, 4);
        $y = 425;
        $cardW = $w - 220;
        $cardH = 195;

        foreach ($unions as $u) {
            self::drawFilledRoundedRect($img, 110, $y, 110 + $cardW, $y + $cardH, 30, [255, 255, 255], 112);
            self::drawRoundedRectOutline($img, 110, $y, 110 + $cardW, $y + $cardH, 30, $palette['accent'], 60, 2);

            $couple = self::truncateText((string)($u['couple_label'] ?? 'Family Couple'), $serif, 40, $cardW - 520);
            $milestone = (string)($u['milestone_label'] ?? 'Cherished Union');

            imagettftext($img, 40, 0, 160, $y + 112, self::col($img, [255, 255, 255], 0), $serif, $couple);

            $mBb = imagettfbbox(28, 0, $sans, $milestone);
            $mW = $mBb !== false ? abs($mBb[2] - $mBb[0]) : 240;
            $badgeX = 110 + $cardW - $mW - 90;
            self::drawFilledRoundedRect($img, $badgeX, $y + 62, 110 + $cardW - 45, $y + 132, 35, $palette['accent'], 0);
            imagettftext($img, 28, 0, $badgeX + 24, $y + 107, self::col($img, $palette['accent_text'], 0), $sans, $milestone);

            $y += $cardH + 34;
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

        imagettftext($img, 68, 0, 110, 275, self::col($img, [255, 255, 255], 0), $serif, 'Most-Loved Family Moment');

        $peakDay = (string)($data['peak_day'] ?? 'Sunday');
        $totalPosts = (int)($data['total_posts'] ?? 0);
        $sub = "{$totalPosts} family stories shared recently  •  Peak day: {$peakDay}";
        imagettftext($img, 30, 0, 110, 338, self::col($img, [191, 219, 254], 10), $sans, $sub);

        // Spotlight quote card
        $cardX = 110;
        $cardY = 420;
        $cardW = $w - 220;
        $cardH = 740;
        self::drawFilledRoundedRect($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 36, [255, 255, 255], 112);
        self::drawRoundedRectOutline($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 36, $palette['accent'], 50, 3);

        $author = (string)($data['top_author'] ?? 'Family Member');
        $score = (int)($data['top_score'] ?? 0);
        $excerpt = trim((string)($data['top_excerpt'] ?? 'A cherished family memory shared on the timeline.'));

        // Wrap excerpt into max 4 lines
        $lines = self::wrapText($excerpt, $serif, 44, $cardW - 140, 4);
        $lineY = $cardY + 180;
        foreach ($lines as $line) {
            imagettftext($img, 44, 0, $cardX + 70, $lineY, self::col($img, [255, 255, 255], 0), $serif, $line);
            $lineY += 78;
        }

        // Author + love count
        $meta = "Shared by {$author}  •  {$score} " . ($score === 1 ? 'reaction' : 'reactions');
        imagettftext($img, 32, 0, $cardX + 70, $cardY + $cardH - 95, self::col($img, $palette['accent'], 0), $sans, $meta);
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

        $title = trim((string)($data['title'] ?? 'Family Milestone'));
        $subtitle = trim((string)($data['subtitle'] ?? 'Celebration'));
        $daysRemaining = (int)($data['days_remaining'] ?? 0);
        $targetDate = trim((string)($data['target_date'] ?? ''));
        $avatarName = trim((string)($data['avatar'] ?? ''));
        $meaningfulMsg = trim((string)($data['meaningful_message'] ?? 'Honouring this cherished milestone in our family.'));
        $cleanCode = '#' . ltrim(strtoupper(trim($famCode !== '' ? $famCode : 'FAMILY')), '#');

        // 1. Rich Royal Bordeaux Velvet Outer Mat (#4a0e1e -> #24060e)
        self::drawGradientBackground($img, $w, $h, [74, 14, 30], [36, 6, 14]);

        // Outer fine gold frame on velvet mat
        imagerectangle($img, 36, 36, $w - 36, $h - 36, self::col($img, [184, 146, 68], 40));

        // 2. Main Warm Heritage Ivory Stationery Card (#fdfaf3) with 24k Gold Bezel
        $cardX = 76;
        $cardY = 76;
        $cardW = $w - 152;
        $cardH = $h - 152;

        // Soft drop shadow on velvet mat
        self::drawFilledRoundedRect($img, $cardX + 10, $cardY + 14, $cardX + $cardW + 10, $cardY + $cardH + 14, 42, [14, 2, 6], 45);
        // 24k Champagne Gold outer bezel (6px)
        self::drawFilledRoundedRect($img, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, 40, [206, 166, 72], 0);
        // Warm Heritage Ivory Parchment surface (solid, 0 alpha -> razor-sharp contrast)
        self::drawFilledRoundedRect($img, $cardX + 6, $cardY + 6, $cardX + $cardW - 6, $cardY + $cardH - 6, 36, [253, 250, 243], 0);

        // Inner fine engraved double-rule border with corner diamonds
        $inX1 = $cardX + 32;
        $inY1 = $cardY + 32;
        $inX2 = $cardX + $cardW - 32;
        $inY2 = $cardY + $cardH - 32;
        imagerectangle($img, $inX1, $inY1, $inX2, $inY2, self::col($img, [212, 184, 128], 0));
        imagerectangle($img, $inX1 + 6, $inY1 + 6, $inX2 - 6, $inY2 - 6, self::col($img, [232, 217, 184], 0));

        $diamondCol = self::col($img, [184, 138, 44], 0);
        foreach ([[$inX1, $inY1], [$inX2, $inY1], [$inX1, $inY2], [$inX2, $inY2]] as [$dx, $dy]) {
            imagefilledpolygon($img, [$dx, $dy - 10, $dx + 10, $dy, $dx, $dy + 10, $dx - 10, $dy], $diamondCol);
        }

        // 3. Top Editorial Header Bar
        $headerY = $cardY + 68;
        $pillH = 58;
        $leftPillText = 'FAMILY MILESTONE SPOTLIGHT';
        $bbLeft = imagettfbbox(21, 0, $sansBold, $leftPillText);
        $leftTextW = $bbLeft !== false ? (int)abs($bbLeft[2] - $bbLeft[0]) : 460;
        $leftPillW = $leftTextW + 64;
        $leftPillX = $cardX + 74;

        // Solid Royal Bordeaux pill with gold border and crisp white bold text (11.4:1 contrast)
        self::drawFilledRoundedRect($img, $leftPillX - 2, $headerY - 2, $leftPillX + $leftPillW + 2, $headerY + $pillH + 2, 29, [206, 166, 72], 0);
        self::drawFilledRoundedRect($img, $leftPillX, $headerY, $leftPillX + $leftPillW, $headerY + $pillH, 28, [107, 18, 38], 0);
        imagettftext($img, 21, 0, $leftPillX + 32, $headerY + 38, self::col($img, [255, 255, 255], 0), $sansBold, $leftPillText);

        // Family Code Crest Pill on Top Right
        $bbCode = imagettfbbox(22, 0, $sansBold, $cleanCode);
        $codeW = $bbCode !== false ? (int)abs($bbCode[2] - $bbCode[0]) : 180;
        $rightPillW = $codeW + 60;
        $rightPillX = $cardX + $cardW - 74 - $rightPillW;
        self::drawFilledRoundedRect($img, $rightPillX - 2, $headerY - 2, $rightPillX + $rightPillW + 2, $headerY + $pillH + 2, 29, [196, 158, 78], 0);
        self::drawFilledRoundedRect($img, $rightPillX, $headerY, $rightPillX + $rightPillW, $headerY + $pillH, 28, [246, 238, 220], 0);
        imagettftext($img, 22, 0, $rightPillX + 30, $headerY + 38, self::col($img, [107, 18, 38], 0), $sansBold, $cleanCode);

        // 4. Left Column — Celebrant Portrait Medallion
        $avatarCx = $cardX + 345;
        $avatarCy = $cardY + 555;
        $avatarR = 215;

        // Soft warm shadow beneath medallion
        imagefilledellipse($img, $avatarCx + 4, $avatarCy + 12, ($avatarR + 30) * 2, ($avatarR + 30) * 2, self::col($img, [226, 214, 190], 0));
        // Outer 24k Gold Bezel Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 24) * 2, ($avatarR + 24) * 2, self::col($img, [202, 160, 52], 0));
        // Middle Royal Bordeaux Inlay Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 14) * 2, ($avatarR + 14) * 2, self::col($img, [107, 18, 38], 0));
        // Inner Warm Ivory Mat Ring
        imagefilledellipse($img, $avatarCx, $avatarCy, ($avatarR + 8) * 2, ($avatarR + 8) * 2, self::col($img, [253, 250, 243], 0));
        // Portrait disc backing
        imagefilledellipse($img, $avatarCx, $avatarCy, $avatarR * 2, $avatarR * 2, self::col($img, [107, 18, 38], 0));

        $drawn = self::drawCircularAvatar($img, $avatarName, $avatarCx, $avatarCy, $avatarR, $root);
        if (!$drawn) {
            $initials = self::initials($title);
            self::drawCenteredText($img, $initials, $cinzel, 108, $avatarCx, $avatarCy + 38, [253, 230, 138]);
        }

        // Date Ribbon Seal overlapping bottom of Portrait Medallion
        $dateSealText = $targetDate !== '' ? strtoupper($targetDate) : 'SPECIAL DATE';
        $bbDate = imagettfbbox(22, 0, $sansBold, $dateSealText);
        $dateTextW = $bbDate !== false ? (int)abs($bbDate[2] - $bbDate[0]) : 220;
        $sealW = (int)max(290, $dateTextW + 76);
        $sealH = 66;
        $sealX = $avatarCx - intdiv($sealW, 2);
        $sealY = $avatarCy + $avatarR - 18;

        self::drawFilledRoundedRect($img, $sealX - 4, $sealY - 4, $sealX + $sealW + 4, $sealY + $sealH + 4, 35, [202, 160, 52], 0);
        self::drawFilledRoundedRect($img, $sealX, $sealY, $sealX + $sealW, $sealY + $sealH, 33, [107, 18, 38], 0);
        self::drawCenteredText($img, $dateSealText, $sansBold, 22, $avatarCx, $sealY + 42, [255, 255, 255]);

        // 5. Right Column — High-Contrast Editorial Typography
        $textX = $cardX + 650;
        $textMaxW = $cardW - 730;

        // Countdown Eyebrow Pill (Warm Gold Fill + Deep Espresso Bold Text -> 9.8:1 contrast)
        $badgeText = match ($daysRemaining) {
            0 => 'CELEBRATING TODAY  •  HAPPY BIRTHDAY',
            1 => 'CELEBRATING TOMORROW  •  SAVE THE DATE',
            default => 'UPCOMING IN ' . $daysRemaining . ' DAYS  •  SAVE THE DATE',
        };
        $bbBadge = imagettfbbox(22, 0, $sansBold, $badgeText);
        $badgeW = $bbBadge !== false ? (int)abs($bbBadge[2] - $bbBadge[0]) : 420;
        $badgeY = $cardY + 235;
        self::drawFilledRoundedRect($img, $textX - 3, $badgeY - 3, $textX + $badgeW + 59, $badgeY + 61, 30, [217, 119, 6], 0);
        self::drawFilledRoundedRect($img, $textX, $badgeY, $textX + $badgeW + 56, $badgeY + 58, 28, [254, 243, 199], 0);
        imagettftext($img, 22, 0, $textX + 28, $badgeY + 38, self::col($img, [120, 53, 15], 0), $sansBold, $badgeText);

        // Celebrant Display Headline in Playfair Display (Deep Espresso Ink #1c1917 -> 16.5:1 contrast)
        $cleanTitle = self::truncateText($title, $playfair, 76, $textMaxW);
        $inkCol = self::col($img, [28, 25, 23], 0);
        imagettftext($img, 76, 0, $textX, $cardY + 402, $inkCol, $playfair, $cleanTitle);
        imagettftext($img, 76, 0, $textX + 1, $cardY + 402, $inkCol, $playfair, $cleanTitle);

        // Engraved Occasion Subtitle (Royal Bordeaux #881337 -> 8.9:1 contrast)
        $subUpper = strtoupper($subtitle);
        if (!str_contains($subUpper, 'CELEBRATION') && !str_contains($subUpper, 'MILESTONE') && !str_contains($subUpper, 'YEARS')) {
            $subUpper .= '  •  FAMILY MILESTONE CELEBRATION';
        }
        $cleanSub = self::truncateText($subUpper, $cinzel, 27, $textMaxW);
        $crimsonCol = self::col($img, [136, 19, 55], 0);
        imagettftext($img, 27, 0, $textX, $cardY + 468, $crimsonCol, $cinzel, $cleanSub);
        imagettftext($img, 27, 0, $textX + 1, $cardY + 468, $crimsonCol, $cinzel, $cleanSub);

        // Ornamental Burnished Gold Divider Rule
        $ruleY = $cardY + 506;
        imagefilledrectangle($img, $textX, $ruleY, $textX + $textMaxW, $ruleY + 3, self::col($img, [202, 160, 52], 0));
        imagefilledpolygon($img, [$textX + 60, $ruleY - 7, $textX + 69, $ruleY + 1, $textX + 60, $ruleY + 9, $textX + 51, $ruleY + 1], $diamondCol);

        // Editorial Tribute Quote Panel (#f5eede warm surface + 10px Royal Bordeaux left accent bar)
        $quoteBoxY = $cardY + 542;
        $quoteBoxH = 240;
        self::drawFilledRoundedRect($img, $textX, $quoteBoxY, $textX + $textMaxW, $quoteBoxY + $quoteBoxH, 20, [245, 238, 222], 0);
        imagefilledrectangle($img, $textX, $quoteBoxY + 16, $textX + 10, $quoteBoxY + $quoteBoxH - 16, $crimsonCol);

        $quoteLines = self::wrapText('"' . $meaningfulMsg . '"', $cormorantItalic, 43, $textMaxW - 84, 3);
        $qY = $quoteBoxY + 76;
        $quoteCol = self::col($img, [41, 37, 36], 0);
        foreach ($quoteLines as $qLine) {
            imagettftext($img, 43, 0, $textX + 42, $qY, $quoteCol, $cormorantItalic, $qLine);
            imagettftext($img, 43, 0, $textX + 43, $qY, $quoteCol, $cormorantItalic, $qLine);
            $qY += 64;
        }

        // Call-to-Action Footer Banner (Pure typographical text, zero broken emoji glyphs)
        $ctaY = $cardY + 820;
        $ctaH = 74;
        self::drawFilledRoundedRect($img, $textX - 2, $ctaY - 2, $textX + $textMaxW + 2, $ctaY + $ctaH + 2, 22, [206, 166, 72], 0);
        self::drawFilledRoundedRect($img, $textX, $ctaY, $textX + $textMaxW, $ctaY + $ctaH, 20, [250, 243, 224], 0);

        $callToAction = match ($daysRemaining) {
            0 => 'Shower with warm wishes, favourite memories, and blessings in the comments below!',
            1 => 'Get your heartfelt wishes and celebration messages ready for tomorrow!',
            default => 'Mark your calendar! Share a warm family memory or celebration wish below.',
        };
        $cleanCta = self::truncateText($callToAction, $sansBold, 24, $textMaxW - 56);
        imagettftext($img, 24, 0, $textX + 28, $ctaY + 46, self::col($img, [107, 18, 38], 0), $sansBold, $cleanCta);

        // Engraved Family Dynasty Sign-Off
        $signOff = 'WITH WARMEST BLESSINGS  •  THE ' . $cleanCode . ' FAMILY DYNASTY';
        $signCol = self::col($img, [146, 102, 32], 0);
        imagettftext($img, 21, 0, $textX + 4, $cardY + 952, $signCol, $cinzel, $signOff);
        imagettftext($img, 21, 0, $textX + 5, $cardY + 952, $signCol, $cinzel, $signOff);

        // 6. Card Bottom Heritage Watermark
        $footY = $cardY + $cardH - 54;
        $footLeft = 'FamilyPlatform  •  Family Historian';
        $footRight = 'Private Family Timeline  •  ' . $cleanCode;
        imagettftext($img, 21, 0, $cardX + 74, $footY, self::col($img, [120, 100, 74], 0), $sans, $footLeft);
        $bbFootR = imagettfbbox(21, 0, $sans, $footRight);
        $footRW = $bbFootR !== false ? (int)abs($bbFootR[2] - $bbFootR[0]) : 340;
        imagettftext($img, 21, 0, $cardX + $cardW - 74 - $footRW, $footY, self::col($img, [120, 100, 74], 0), $sans, $footRight);
    }

    /**
     * @param array<string, mixed> $palette
     */
    private static function drawFooter(GdImage $img, int $w, int $h, string $famCode, array $palette): void
    {
        $sans = self::font('sans');
        $left = 'FamilyPlatform  •  Family Historian';
        $right = 'Private Family Timeline';

        imagettftext($img, 24, 0, 110, $h - 86, self::col($img, [226, 232, 240], 45), $sans, $left);
        $bb = imagettfbbox(24, 0, $sans, $right);
        $rw = $bb !== false ? abs($bb[2] - $bb[0]) : 250;
        imagettftext($img, 24, 0, $w - 110 - $rw, $h - 86, self::col($img, [226, 232, 240], 45), $sans, $right);
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
        $parts = preg_split('/\s+/', trim($name));
        if (!is_array($parts) || empty($parts)) {
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
                $current = $word;
            }
        }
        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }
        return $lines;
    }
}
