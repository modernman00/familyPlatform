<?php

declare(strict_types=1);

namespace App\services;

use GdImage;

/**
 * FamilyArcadeCardRenderer
 *
 * 2x-supersampled (2400x1260 -> 1200x630) PHP GD + FreeType Open Graph and
 * Share Modal preview card synthesiser for all 7 Family Arcade games:
 * - KinZip (Connect the Dots)
 * - KinGrid (Number Puzzle)
 * - KinWord (Word Groups)
 * - KinPulse (Memory Pattern)
 * - Wordie (5-Letter Word)
 * - Cheeseboard (Crown Deduction)
 * - Minesweeper (Safe Path)
 *
 * Eliminates legacy GD bitmap `imagestring()` mojibake (`â€”`), renders crisp
 * anti-aliased typography using workspace-bundled TrueType fonts, and draws an
 * authentic vector-style game board showcase on the right half of every card.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyArcadeCardRenderer
{
    public const WIDTH = 1200;
    public const HEIGHT = 630;
    private const SS = 2;

    /**
     * Metadata & visual accent palette for each of the 7 Family Arcade games.
     *
     * @return array{
     *   slug: string,
     *   name: string,
     *   tag: string,
     *   hook: string,
     *   summary: string,
     *   gridLabel: array<string, string>,
     *   accent: array{0:int,1:int,2:int},
     *   accentSoft: array{0:int,1:int,2:int},
     *   badgeFill: array{0:int,1:int,2:int}
     * }
     */
    public static function gameProfile(string $gameSlug): array
    {
        $slug = FamilyArcadeService::normaliseGame($gameSlug);

        return match ($slug) {
            'kingrid' => [
                'slug' => 'kingrid',
                'name' => 'KinGrid',
                'tag' => 'Number Puzzle',
                'hook' => 'Fill every row & column without repeats',
                'summary' => 'Use pure logic and inequality clues (< and >) to place every digit once per row and column.',
                'gridLabel' => ['easy' => '4x4 LOGIC GRID', 'medium' => '5x5 LOGIC GRID', 'hard' => '6x6 LOGIC GRID'],
                'accent' => [56, 189, 248],
                'accentSoft' => [14, 116, 144],
                'badgeFill' => [12, 74, 110],
            ],
            'kinword' => [
                'slug' => 'kinword',
                'name' => 'KinWord',
                'tag' => 'Word Groups',
                'hook' => 'Group 16 words into 4 matching sets of 4',
                'summary' => 'Spot the hidden British heritage, family, and wordplay connections before losing your 4 hearts.',
                'gridLabel' => ['easy' => '4x4 WORD BOARD', 'medium' => '4x4 WORD BOARD', 'hard' => '4x4 WORD BOARD'],
                'accent' => [251, 191, 36],
                'accentSoft' => [180, 83, 9],
                'badgeFill' => [120, 53, 15],
            ],
            'kinpulse' => [
                'slug' => 'kinpulse',
                'name' => 'KinPulse',
                'tag' => 'Memory Pattern',
                'hook' => 'Watch, listen & echo the glowing sequence',
                'summary' => 'Follow the musical synth pads as each round adds a new step to the rhythmic memory pattern.',
                'gridLabel' => ['easy' => '6-ROUND MATRIX', 'medium' => '8-ROUND MATRIX', 'hard' => '10-ROUND MATRIX'],
                'accent' => [244, 114, 182],
                'accentSoft' => [190, 24, 93],
                'badgeFill' => [131, 24, 67],
            ],
            'wordie' => [
                'slug' => 'wordie',
                'name' => 'Wordie',
                'tag' => '5-Letter Word',
                'hook' => 'Deduce the 5-letter family word in 6 tries',
                'summary' => 'Green tiles lock the exact spot and gold tiles reveal misplaced letters in today\'s daily word.',
                'gridLabel' => ['easy' => '5-LETTER DAILY', 'medium' => '5-LETTER DAILY', 'hard' => '5-LETTER DAILY'],
                'accent' => [52, 211, 153],
                'accentSoft' => [5, 150, 105],
                'badgeFill' => [6, 78, 59],
            ],
            'cheeseboard' => [
                'slug' => 'cheeseboard',
                'name' => 'Cheeseboard',
                'tag' => 'Queens Territory (Not Chess)',
                'hook' => 'Place 1 crown in every row, column & region',
                'summary' => 'Queens territory logic puzzle—not traditional chess! Place exactly one crown per row, col and colour region without touching.',
                'gridLabel' => ['easy' => '6x6 TERRITORIES', 'medium' => '7x7 TERRITORIES', 'hard' => '8x8 TERRITORIES'],
                'accent' => [192, 132, 252],
                'accentSoft' => [126, 34, 206],
                'badgeFill' => [88, 28, 135],
            ],
            'chess' => [
                'slug' => 'chess',
                'name' => 'Family Chess',
                'tag' => 'Classic 8x8 Strategy',
                'hook' => 'Checkmate the opposing King on the classical 8x8 board',
                'summary' => 'Play the classic game of kings against the ATG Chess Bot or family relatives with checkmate detection and pawn promotion crowns.',
                'gridLabel' => ['easy' => '8x8 CASUAL', 'medium' => '8x8 TACTICAL', 'hard' => '8x8 GRANDMASTER'],
                'accent' => [251, 191, 36],
                'accentSoft' => [217, 119, 6],
                'badgeFill' => [30, 27, 75],
            ],
            'ayo' => [
                'slug' => 'ayo',
                'name' => 'Ayò Olópon',
                'tag' => 'Yorùbá Board of Kings',
                'hook' => 'Sow 48 seeds across 12 hollows & capture sets of four',
                'summary' => 'The revered Yorùbá intellectual board game: anti-clockwise sowing, chain captures (jẹ mẹ́rin), and compassionate starvation rules.',
                'gridLabel' => ['easy' => '12 HOLLOWS • 48 SEEDS', 'medium' => '12 HOLLOWS • 48 SEEDS', 'hard' => '12 HOLLOWS • 48 SEEDS'],
                'accent' => [245, 158, 11],
                'accentSoft' => [180, 83, 9],
                'badgeFill' => [69, 26, 3],
            ],
            'minesweeper' => [
                'slug' => 'minesweeper',
                'name' => 'Minesweeper',
                'tag' => 'Safe Path',
                'hook' => 'Uncover every safe square across the 8x8 board',
                'summary' => 'Read the numeric proximity clues, flag hidden hazards, and clear the safe path for your family.',
                'gridLabel' => ['easy' => '8x8 • 8 MINES', 'medium' => '8x8 • 10 MINES', 'hard' => '8x8 • 12 MINES'],
                'accent' => [45, 212, 191],
                'accentSoft' => [15, 118, 110],
                'badgeFill' => [19, 78, 74],
            ],
            default => [
                'slug' => 'kinzip',
                'name' => 'KinZip',
                'tag' => 'Connect the Dots',
                'hook' => 'Draw one continuous path through every cell',
                'summary' => 'Connect the numbered checkpoints 1 to N in order, navigate around gold walls, and fill the grid.',
                'gridLabel' => ['easy' => '5x5 LABYRINTH', 'medium' => '6x6 LABYRINTH', 'hard' => '7x7 LABYRINTH'],
                'accent' => [251, 191, 36],
                'accentSoft' => [217, 119, 6],
                'badgeFill' => [127, 29, 29],
            ],
        };
    }

    /**
     * Render a 1200x630 progressive JPEG (< 200 KB) for a game invite or score challenge.
     *
     * @param array{
     *   game?: string,
     *   difficulty?: string,
     *   score?: int|null,
     *   elapsedMs?: int|null,
     *   challengerName?: string|null
     * } $context
     */
    public static function renderCardJpeg(array $context = []): string
    {
        if (!extension_loaded('gd')) {
            return '';
        }

        $gameSlug = FamilyArcadeService::normaliseGame((string) ($context['game'] ?? 'kinzip'));
        $difficulty = FamilyArcadeService::normaliseDifficulty((string) ($context['difficulty'] ?? 'medium'));
        $score = isset($context['score']) && (int) $context['score'] > 0 ? (int) $context['score'] : null;
        $elapsedMs = isset($context['elapsedMs']) && (int) $context['elapsedMs'] > 0 ? (int) $context['elapsedMs'] : null;
        $challengerName = trim((string) ($context['challengerName'] ?? ''));

        $profile = self::gameProfile($gameSlug);

        $w = self::WIDTH * self::SS;
        $h = self::HEIGHT * self::SS;

        $canvas = imagecreatetruecolor($w, $h);
        if ($canvas === false) {
            return '';
        }
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // 1. Deep Obsidian-to-Royal-Crimson Background Gradient
        self::drawBackground($canvas, $w, $h, $profile['accent'], $profile['accentSoft']);

        // 2. Outer Glassmorphic Heritage Frame
        self::drawFilledRoundedRect($canvas, 52, 52, $w - 52, $h - 52, 42, [15, 23, 42], 32);
        self::drawRoundedRectOutline($canvas, 52, 52, $w - 52, $h - 52, 42, [251, 191, 36], 62, 4);
        self::drawRoundedRectOutline($canvas, 68, 68, $w - 68, $h - 68, 34, [255, 255, 255], 112, 2);

        // 3. Left Stage: Typography, Badges, Challenge Details & CTA
        self::drawLeftStage(
            $canvas,
            $profile,
            $difficulty,
            $score,
            $elapsedMs,
            $challengerName
        );

        // 4. Right Stage: Bespoke 7-Game Visual Board Showcase
        self::drawRightStage($canvas, $profile, $difficulty, $score);

        // 5. Downsample 2x -> 1x (1200x630) for razor-sharp anti-aliasing
        $out = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($out === false) {
            unset($canvas);
            return '';
        }
        imagecopyresampled($out, $canvas, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, $w, $h);
        unset($canvas);

        imageinterlace($out, true);
        ob_start();
        imagejpeg($out, null, 88);
        $jpeg = (string) ob_get_clean();
        unset($out);

        return $jpeg;
    }

    /**
     * @param array{0:int,1:int,2:int} $accent
     * @param array{0:int,1:int,2:int} $accentSoft
     */
    private static function drawBackground(GdImage $img, int $w, int $h, array $accent, array $accentSoft): void
    {
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $r = max(0, min(255, (int) round(11 * (1 - $t) + 28 * $t)));
            $g = max(0, min(255, (int) round(16 * (1 - $t) + 12 * $t)));
            $b = max(0, min(255, (int) round(33 * (1 - $t) + 26 * $t)));
            $col = imagecolorallocate($img, $r, $g, $b);
            if ($col !== false) {
                imageline($img, 0, $y, $w, $y, $col);
            }
        }

        // Ambient glow behind right board & top-left header
        for ($rad = 1100; $rad > 120; $rad -= 70) {
            $alpha = (int) min(124, 114 + ($rad / 1100) * 10);
            imagefilledellipse($img, 1840, 620, $rad, $rad, self::col($img, $accent, $alpha));
            imagefilledellipse($img, 360, 240, (int) ($rad * 0.75), (int) ($rad * 0.75), self::col($img, $accentSoft, min(125, $alpha + 2)));
        }

        // Subtle architectural grid lines
        $gridCol = self::col($img, [255, 255, 255], 122);
        for ($x = 120; $x < $w; $x += 120) {
            imageline($img, $x, 56, $x, $h - 56, $gridCol);
        }
        for ($y = 120; $y < $h; $y += 120) {
            imageline($img, 56, $y, $w - 56, $y, $gridCol);
        }
    }

    /**
     * @param array{
     *   slug: string,
     *   name: string,
     *   tag: string,
     *   hook: string,
     *   summary: string,
     *   gridLabel: array<string, string>,
     *   accent: array{0:int,1:int,2:int},
     *   accentSoft: array{0:int,1:int,2:int},
     *   badgeFill: array{0:int,1:int,2:int}
     * } $profile
     */
    private static function drawLeftStage(
        GdImage $img,
        array $profile,
        string $difficulty,
        ?int $score,
        ?int $elapsedMs,
        string $challengerName
    ): void {
        $fontBold = self::font('sans_bold');
        $fontReg = self::font('sans');

        $leftX = 124;
        $isChallenge = $score !== null && $score > 0;

        // 1. Top Eyebrow Pills (Category Pill + Difficulty/Grid Pill)
        $eyebrowText = $isChallenge
            ? 'LIVE SCORE CHALLENGE'
            : 'FAMILY ARCADE  •  DAILY PUZZLE';
        $eyebrowW = self::measureTextWidth($fontBold, 23, $eyebrowText) + 56;
        self::drawFilledRoundedRect($img, $leftX, 118, $leftX + $eyebrowW, 182, 32, $profile['badgeFill'], 18);
        self::drawRoundedRectOutline($img, $leftX, 118, $leftX + $eyebrowW, 182, 32, $profile['accent'], 35, 3);
        self::drawText($img, $fontBold, 23, $leftX + 28, 160, [254, 243, 199], $eyebrowText);

        $diffLabel = strtoupper($difficulty) . '  •  ' . ($profile['gridLabel'][$difficulty] ?? '6x6 GRID');
        $diffX = $leftX + $eyebrowW + 20;
        $diffW = self::measureTextWidth($fontBold, 22, $diffLabel) + 48;
        self::drawFilledRoundedRect($img, $diffX, 118, $diffX + $diffW, 182, 32, [30, 41, 59], 22);
        self::drawRoundedRectOutline($img, $diffX, 118, $diffX + $diffW, 182, 32, [148, 163, 184], 75, 2);
        self::drawText($img, $fontBold, 22, $diffX + 24, 159, [226, 232, 240], $diffLabel);

        // 2. Game Title + Subtitle Tag
        if ($isChallenge) {
            $secs = $elapsedMs !== null ? number_format($elapsedMs / 1000, 1) . 's' : 'Fast Time';
            $who = $challengerName !== '' && strtolower($challengerName) !== 'a family challenger'
                ? $challengerName . "'s"
                : 'My';
            $headline = "Beat {$who} " . number_format($score) . ' pts!';
            $headSize = mb_strlen($headline) > 22 ? 64 : 72;
            self::drawText($img, $fontBold, $headSize, $leftX, 292, [255, 255, 255], $headline);

            $subHeadline = "{$profile['name']} ({$profile['tag']})  •  Solved in {$secs}";
            self::drawText($img, $fontBold, 33, $leftX, 362, $profile['accent'], $subHeadline);

            $challengeCopy = "Think you can beat this score? Tap to open the exact same {$profile['name']} board and race the live ghost pace bar.";
            $lines = self::wrapText($challengeCopy, $fontReg, 28, 1220, 2);
            $cy = 440;
            foreach ($lines as $line) {
                self::drawText($img, $fontReg, 28, $leftX, $cy, [203, 213, 225], $line);
                $cy += 46;
            }
        } else {
            // Game Title + Tag pill on same baseline (scale title smoothly for longer names)
            $titleSize = mb_strlen($profile['name']) >= 11 ? 74 : 84;
            self::drawText($img, $fontBold, $titleSize, $leftX, 300, [255, 255, 255], $profile['name']);
            $nameW = self::measureTextWidth($fontBold, $titleSize, $profile['name']);

            $tagText = strtoupper($profile['tag']);
            $tagX = $leftX + $nameW + 24;
            $tagW = self::measureTextWidth($fontBold, 23, $tagText) + 44;
            self::drawFilledRoundedRect($img, $tagX, 240, $tagX + $tagW, 296, 18, [251, 191, 36], 108);
            self::drawRoundedRectOutline($img, $tagX, 240, $tagX + $tagW, 296, 18, $profile['accent'], 40, 3);
            self::drawText($img, $fontBold, 23, $tagX + 22, 278, $profile['accent'], $tagText);

            // Action Hook Headline
            self::drawText($img, $fontBold, 35, $leftX, 390, [254, 243, 199], $profile['hook']);

            // Wrapped Description
            $lines = self::wrapText($profile['summary'], $fontReg, 28, 1220, 2);
            $cy = 464;
            foreach ($lines as $line) {
                self::drawText($img, $fontReg, 28, $leftX, $cy, [203, 213, 225], $line);
                $cy += 46;
            }
        }

        // 3. Three Feature Value Pills
        $features = [
            'Instant Browser Play',
            'No Sign-Up Needed',
            'Live Ghost Race',
        ];
        $fx = $leftX;
        $fy = 610;
        foreach ($features as $feat) {
            $fw = self::measureTextWidth($fontBold, 22, $feat) + 60;
            self::drawFilledRoundedRect($img, $fx, $fy, $fx + $fw, $fy + 60, 18, [30, 41, 59], 28);
            self::drawRoundedRectOutline($img, $fx, $fy, $fx + $fw, $fy + 60, 18, [148, 163, 184], 88, 2);
            imagefilledellipse($img, $fx + 24, $fy + 30, 13, 13, self::col($img, $profile['accent'], 0));
            self::drawText($img, $fontBold, 22, $fx + 40, $fy + 39, [241, 245, 249], $feat);
            $fx += $fw + 16;
        }

        // 4. Primary High-Contrast Gold Call-to-Action Button
        $ctaText = $isChallenge
            ? 'ACCEPT CHALLENGE & PLAY FREE   >'
            : 'TAP TO PLAY ' . strtoupper($profile['name']) . ' FREE   >';
        $ctaW = max(620, self::measureTextWidth($fontBold, 30, $ctaText) + 96);
        $ctaY1 = 748;
        $ctaY2 = 858;

        // Warm glow shadow under CTA
        self::drawFilledRoundedRect($img, $leftX + 4, $ctaY1 + 8, $leftX + $ctaW + 4, $ctaY2 + 8, 28, [180, 83, 9], 55);
        self::drawFilledRoundedRect($img, $leftX, $ctaY1, $leftX + $ctaW, $ctaY2, 28, [251, 191, 36], 0);
        self::drawRoundedRectOutline($img, $leftX, $ctaY1, $leftX + $ctaW, $ctaY2, 28, [254, 243, 199], 20, 3);
        self::drawCenteredText($img, $ctaText, $fontBold, 30, $leftX + intdiv($ctaW, 2), $ctaY1 + 68, [15, 23, 42]);

        // 5. Divider & Brand Footer Crest
        imageline($img, $leftX, 954, 1310, 954, self::col($img, [255, 255, 255], 108));

        // Gold crest emblem circle
        imagefilledellipse($img, $leftX + 26, 1030, 52, 52, self::col($img, [251, 191, 36], 96));
        imageellipse($img, $leftX + 26, 1030, 52, 52, self::col($img, [251, 191, 36], 25));
        self::drawCenteredText($img, 'FP', $fontBold, 22, $leftX + 26, 1039, [251, 191, 36]);

        self::drawText($img, $fontBold, 27, $leftX + 68, 1024, [254, 243, 199], 'FamilyPlatform  •  Family Arcade');
        self::drawText($img, $fontReg, 22, $leftX + 68, 1062, [148, 163, 184], '7 Daily Brain Games, Live Ghost Races & Weekly Family Cup Leagues');
    }

    /**
     * Draw the elevated right-hand showcase card with bespoke game board art.
     *
     * @param array{
     *   slug: string,
     *   name: string,
     *   tag: string,
     *   hook: string,
     *   summary: string,
     *   gridLabel: array<string, string>,
     *   accent: array{0:int,1:int,2:int},
     *   accentSoft: array{0:int,1:int,2:int},
     *   badgeFill: array{0:int,1:int,2:int}
     * } $profile
     */
    private static function drawRightStage(GdImage $img, array $profile, string $difficulty, ?int $score): void
    {
        $fontBold = self::font('sans_bold');

        $sx1 = 1428;
        $sy1 = 114;
        $sx2 = 2286;
        $sy2 = 1146;

        // Pedestal container
        self::drawFilledRoundedRect($img, $sx1, $sy1, $sx2, $sy2, 38, [9, 14, 28], 14);
        self::drawRoundedRectOutline($img, $sx1, $sy1, $sx2, $sy2, 38, $profile['accent'], 45, 3);

        // Top mini header inside pedestal
        $cx = intdiv($sx1 + $sx2, 2);
        $topLabel = $score !== null
            ? strtoupper($profile['name']) . '  •  ' . number_format($score) . ' PTS TARGET'
            : strtoupper($profile['name']) . '  •  LIVE BOARD PREVIEW';
        self::drawCenteredText($img, $topLabel, $fontBold, 22, $cx, $sy1 + 58, [254, 243, 199]);

        // Inner 720x720 square board viewport
        $bx = $sx1 + 69;
        $by = $sy1 + 96;
        $bSize = 720;

        match ($profile['slug']) {
            'kingrid' => self::drawKinGridArtwork($img, $bx, $by, $bSize, $fontBold),
            'kinword' => self::drawKinWordArtwork($img, $bx, $by, $bSize, $fontBold),
            'kinpulse' => self::drawKinPulseArtwork($img, $bx, $by, $bSize, $fontBold),
            'wordie' => self::drawWordieArtwork($img, $bx, $by, $bSize, $fontBold),
            'cheeseboard' => self::drawCheeseboardArtwork($img, $bx, $by, $bSize, $fontBold),
            'chess' => self::drawChessArtwork($img, $bx, $by, $bSize, $fontBold),
            'ayo' => self::drawAyoArtwork($img, $bx, $by, $bSize, $fontBold),
            'minesweeper' => self::drawMinesweeperArtwork($img, $bx, $by, $bSize, $fontBold),
            default => self::drawKinZipArtwork($img, $bx, $by, $bSize, $fontBold),
        };

        // Bottom caption strip inside pedestal
        $footerPillY1 = $sy2 - 96;
        $footerPillY2 = $sy2 - 34;
        self::drawFilledRoundedRect($img, $bx, $footerPillY1, $bx + $bSize, $footerPillY2, 20, [30, 41, 59], 30);
        self::drawRoundedRectOutline($img, $bx, $footerPillY1, $bx + $bSize, $footerPillY2, 20, [148, 163, 184], 90, 2);

        $bottomText = '100% LOGIC-SOLVABLE  •  ' . strtoupper($difficulty) . ' SEED';
        self::drawCenteredText($img, $bottomText, $fontBold, 21, $cx, $footerPillY1 + 40, [226, 232, 240]);
    }

    /**
     * 1. KinZip Artwork: 5x5 grid, glowing Hamiltonian serpentine path, thick gold walls, and numbered dots 1..5.
     */
    private static function drawKinZipArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [251, 191, 36], 30, 4);

        $n = 5;
        $pad = 26;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner, $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        // Visited path cells
        $path = [
            [0, 0], [0, 1], [0, 2], [1, 2], [1, 1],
            [1, 0], [2, 0], [2, 1], [2, 2], [2, 3],
            [1, 3], [0, 3], [0, 4], [1, 4], [2, 4],
            [3, 4], [3, 3], [3, 2], [3, 1],
        ];
        $visited = [];
        foreach ($path as [$r, $c]) {
            $visited["{$r},{$c}"] = true;
        }
        $head = $path[count($path) - 1];

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $x1 = $ox + $c * $cell + 5;
                $y1 = $oy + $r * $cell + 5;
                $x2 = $ox + ($c + 1) * $cell - 5;
                $y2 = $oy + ($r + 1) * $cell - 5;
                $isVis = isset($visited["{$r},{$c}"]);
                $isHead = ($head[0] === $r && $head[1] === $c);

                $fill = $isHead ? [185, 28, 28] : ($isVis ? [69, 26, 3] : [30, 41, 59]);
                $border = $isHead ? [251, 191, 36] : ($isVis ? [217, 119, 6] : [71, 85, 105]);
                self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, $fill, $isVis ? 15 : 35);
                self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 16, $border, $isVis ? 25 : 75, $isHead ? 4 : 2);
            }
        }

        // Glowing continuous path ribbon connecting visited cells
        $glowCol = self::col($img, [245, 158, 11], 70);
        $coreCol = self::col($img, [251, 191, 36], 0);
        for ($i = 0; $i < count($path) - 1; $i++) {
            [$r1, $c1] = $path[$i];
            [$r2, $c2] = $path[$i + 1];
            $cx1 = $ox + $c1 * $cell + intdiv($cell, 2);
            $cy1 = $oy + $r1 * $cell + intdiv($cell, 2);
            $cx2 = $ox + $c2 * $cell + intdiv($cell, 2);
            $cy2 = $oy + $r2 * $cell + intdiv($cell, 2);

            imagesetthickness($img, 28);
            imageline($img, $cx1, $cy1, $cx2, $cy2, $glowCol);
            imagesetthickness($img, 14);
            imageline($img, $cx1, $cy1, $cx2, $cy2, $coreCol);
            imagefilledellipse($img, $cx1, $cy1, 18, 18, $coreCol);
        }
        imagesetthickness($img, 1);

        // Thick gold obstacle walls
        $wallCol = self::col($img, [251, 191, 36], 0);
        imagesetthickness($img, 10);
        // Horizontal wall under (0,1) and (0,2)
        imageline($img, $ox + 1 * $cell + 8, $oy + 1 * $cell, $ox + 2 * $cell - 8, $oy + 1 * $cell, $wallCol);
        // Vertical wall between (1,2) and (1,3)
        imageline($img, $ox + 3 * $cell, $oy + 1 * $cell + 8, $ox + 3 * $cell, $oy + 2 * $cell - 8, $wallCol);
        // Horizontal wall under (2,1) and (2,2)
        imageline($img, $ox + 1 * $cell + 8, $oy + 3 * $cell, $ox + 3 * $cell - 8, $oy + 3 * $cell, $wallCol);
        imagesetthickness($img, 1);

        // Checkpoints 1..5
        $checkpoints = [
            ['num' => 1, 'r' => 0, 'c' => 0, 'active' => true],
            ['num' => 2, 'r' => 1, 'c' => 1, 'active' => true],
            ['num' => 3, 'r' => 2, 'c' => 3, 'active' => true],
            ['num' => 4, 'r' => 3, 'c' => 4, 'active' => true],
            ['num' => 5, 'r' => 4, 'c' => 4, 'active' => false],
        ];
        foreach ($checkpoints as $cp) {
            $cx = $ox + $cp['c'] * $cell + intdiv($cell, 2);
            $cy = $oy + $cp['r'] * $cell + intdiv($cell, 2);
            $fill = $cp['active'] ? [185, 28, 28] : [15, 23, 42];
            imagefilledellipse($img, $cx, $cy, 76, 76, self::col($img, [251, 191, 36], 0));
            imagefilledellipse($img, $cx, $cy, 64, 64, self::col($img, $fill, 0));
            self::drawCenteredText($img, (string) $cp['num'], $fontBold, 30, $cx, $cy + 11, [255, 255, 255]);
        }
    }

    /**
     * 2. KinGrid Artwork: 4x4 Latin square + inequality badges (<, >).
     */
    private static function drawKinGridArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [56, 189, 248], 35, 4);

        $n = 4;
        $pad = 34;
        $gap = 22;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner - ($gap * ($n - 1)), $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        $grid = [
            [['v' => '4', 'given' => true], ['v' => '1', 'given' => false], ['v' => '3', 'given' => false], ['v' => '2', 'given' => true]],
            [['v' => '2', 'given' => false], ['v' => '3', 'given' => true], ['v' => '?', 'select' => true], ['v' => '1', 'given' => false]],
            [['v' => '1', 'given' => false], ['v' => '4', 'given' => false], ['v' => '2', 'given' => true], ['v' => '3', 'given' => false]],
            [['v' => '3', 'given' => true], ['v' => '2', 'given' => false], ['v' => '1', 'given' => false], ['v' => '4', 'given' => true]],
        ];

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $tile = $grid[$r][$c];
                $x1 = $ox + $c * ($cell + $gap);
                $y1 = $oy + $r * ($cell + $gap);
                $x2 = $x1 + $cell;
                $y2 = $y1 + $cell;

                $isGiven = !empty($tile['given']);
                $isSelect = !empty($tile['select']);

                $fill = $isGiven ? [12, 74, 110] : ($isSelect ? [127, 29, 29] : [30, 41, 59]);
                $border = $isGiven ? [56, 189, 248] : ($isSelect ? [251, 191, 36] : [100, 116, 139]);
                self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 20, $fill, 10);
                self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 20, $border, 20, $isSelect || $isGiven ? 4 : 2);

                $textCol = $isGiven ? [254, 243, 199] : ($isSelect ? [251, 191, 36] : [255, 255, 255]);
                self::drawCenteredText(
                    $img,
                    $tile['v'],
                    $fontBold,
                    50,
                    $x1 + intdiv($cell, 2),
                    $y1 + intdiv($cell, 2) + 18,
                    $textCol
                );
            }
        }

        // Inequality pills between cells
        $ineqs = [
            ['r' => 0, 'c' => 0, 'dir' => 'h', 'sym' => '>'],
            ['r' => 0, 'c' => 1, 'dir' => 'h', 'sym' => '<'],
            ['r' => 1, 'c' => 1, 'dir' => 'h', 'sym' => '<'],
            ['r' => 2, 'c' => 2, 'dir' => 'h', 'sym' => '<'],
        ];
        foreach ($ineqs as $iq) {
            $cx = $ox + $iq['c'] * ($cell + $gap) + $cell + intdiv($gap, 2);
            $cy = $oy + $iq['r'] * ($cell + $gap) + intdiv($cell, 2);
            imagefilledellipse($img, $cx, $cy, 38, 38, self::col($img, [251, 191, 36], 0));
            self::drawCenteredText($img, $iq['sym'], $fontBold, 24, $cx, $cy + 9, [15, 23, 42]);
        }
    }

    /**
     * 3. KinWord Artwork: Solved category banner + 4x3 word tiles with 4 gold-highlighted selections.
     */
    private static function drawKinWordArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [251, 191, 36], 35, 4);

        $pad = 28;
        $ox = $bx + $pad;
        $oy = $by + $pad;
        $innerW = $bSize - ($pad * 2);

        // Solved Tier-1 Category Banner
        self::drawFilledRoundedRect($img, $ox, $oy, $ox + $innerW, $oy + 146, 22, [6, 95, 70], 10);
        self::drawRoundedRectOutline($img, $ox, $oy, $ox + $innerW, $oy + 146, 22, [52, 211, 153], 30, 3);
        self::drawCenteredText($img, 'SUNDAY ROAST ESSENTIALS', $fontBold, 24, $bx + intdiv($bSize, 2), $oy + 58, [254, 243, 199]);
        self::drawCenteredText($img, 'GRAVY • PARSNIP • ROASTIES • YORKSHIRE', $fontBold, 18, $bx + intdiv($bSize, 2), $oy + 106, [255, 255, 255]);

        // 3 rows x 4 cols of remaining word tiles
        $words = [
            ['w' => 'CROWN', 'sel' => true],
            ['w' => 'SCONE', 'sel' => false],
            ['w' => 'LOCKET', 'sel' => false],
            ['w' => 'THRONE', 'sel' => true],
            ['w' => 'KETTLE', 'sel' => false],
            ['w' => 'PALACE', 'sel' => true],
            ['w' => 'ALBUM', 'sel' => false],
            ['w' => 'BROOCH', 'sel' => false],
            ['w' => 'SCEPTRE', 'sel' => true],
            ['w' => 'BISCUIT', 'sel' => false],
            ['w' => 'TEAPOT', 'sel' => false],
            ['w' => 'MANTEL', 'sel' => false],
        ];

        $cols = 4;
        $rows = 3;
        $gap = 16;
        $gridTop = $oy + 172;
        $gridH = $bSize - ($pad * 2) - 172;
        $tileW = intdiv($innerW - ($gap * ($cols - 1)), $cols);
        $tileH = intdiv($gridH - ($gap * ($rows - 1)), $rows);

        foreach ($words as $idx => $item) {
            $r = intdiv($idx, $cols);
            $c = $idx % $cols;
            $x1 = $ox + $c * ($tileW + $gap);
            $y1 = $gridTop + $r * ($tileH + $gap);
            $x2 = $x1 + $tileW;
            $y2 = $y1 + $tileH;

            $isSel = $item['sel'];
            $fill = $isSel ? [180, 83, 9] : [30, 41, 59];
            $border = $isSel ? [251, 191, 36] : [100, 116, 139];
            self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 18, $fill, 10);
            self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 18, $border, 20, $isSel ? 4 : 2);
            self::drawCenteredText(
                $img,
                $item['w'],
                $fontBold,
                22,
                $x1 + intdiv($tileW, 2),
                $y1 + intdiv($tileH, 2) + 8,
                $isSel ? [254, 243, 199] : [241, 245, 249]
            );
        }
    }

    /**
     * 4. KinPulse Artwork: 3x3 glowing neon acoustic pads with shockwave rings.
     */
    private static function drawKinPulseArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [244, 114, 182], 35, 4);

        $n = 3;
        $pad = 34;
        $gap = 24;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner - ($gap * ($n - 1)), $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        $colours = [
            [220, 38, 38],   [217, 119, 6],  [5, 150, 105],
            [37, 99, 235],   [124, 58, 237], [13, 148, 136],
            [225, 29, 72],   [79, 70, 229],  [202, 138, 4],
        ];
        $litOrder = [0 => '1', 4 => '2', 2 => '3'];

        for ($i = 0; $i < 9; $i++) {
            $r = intdiv($i, 3);
            $c = $i % 3;
            $x1 = $ox + $c * ($cell + $gap);
            $y1 = $oy + $r * ($cell + $gap);
            $x2 = $x1 + $cell;
            $y2 = $y1 + $cell;
            $cx = $x1 + intdiv($cell, 2);
            $cy = $y1 + intdiv($cell, 2);

            $isLit = isset($litOrder[$i]);
            $rgb = $colours[$i];

            self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 24, $rgb, $isLit ? 5 : 65);
            self::drawRoundedRectOutline(
                $img,
                $x1,
                $y1,
                $x2,
                $y2,
                24,
                $isLit ? [255, 255, 255] : $rgb,
                $isLit ? 0 : 30,
                $isLit ? 5 : 2
            );

            if ($isLit) {
                imageellipse($img, $cx, $cy, $cell - 26, $cell - 26, self::col($img, [255, 255, 255], 45));
                imageellipse($img, $cx, $cy, $cell - 54, $cell - 54, self::col($img, [254, 243, 199], 30));
                self::drawCenteredText($img, 'STEP ' . $litOrder[$i], $fontBold, 26, $cx, $cy + 10, [255, 255, 255]);
            } else {
                self::drawCenteredText($img, (string) ($i + 1), $fontBold, 30, $cx, $cy + 11, [226, 232, 240]);
            }
        }
    }

    /**
     * 5. Wordie Artwork: 5x5 letter deduction grid showing green, gold, and slate tiles.
     */
    private static function drawWordieArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [52, 211, 153], 35, 4);

        $n = 5;
        $pad = 32;
        $gap = 16;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner - ($gap * ($n - 1)), $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        // 'G' = exact (green), 'Y' = present (amber), 'S' = miss (slate), 'E' = empty
        $rows = [
            [['S', 'G'], ['T', 'S'], ['O', 'Y'], ['R', 'Y'], ['Y', 'S']],
            [['S', 'G'], ['H', 'G'], ['I', 'S'], ['N', 'S'], ['E', 'G']],
            [['S', 'G'], ['H', 'G'], ['A', 'G'], ['R', 'G'], ['E', 'G']],
            [['', 'E'], ['', 'E'], ['', 'E'], ['', 'E'], ['', 'E']],
            [['', 'E'], ['', 'E'], ['', 'E'], ['', 'E'], ['', 'E']],
        ];

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                [$letter, $state] = $rows[$r][$c];
                $x1 = $ox + $c * ($cell + $gap);
                $y1 = $oy + $r * ($cell + $gap);
                $x2 = $x1 + $cell;
                $y2 = $y1 + $cell;

                $fill = match ($state) {
                    'G' => [5, 150, 105],
                    'Y' => [217, 119, 6],
                    'S' => [51, 65, 85],
                    default => [30, 41, 59],
                };
                $border = match ($state) {
                    'G' => [52, 211, 153],
                    'Y' => [251, 191, 36],
                    'S' => [100, 116, 139],
                    default => [71, 85, 105],
                };

                self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, $fill, $state === 'E' ? 55 : 5);
                self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 16, $border, 25, $r === 2 ? 4 : 2);

                if ($letter !== '') {
                    self::drawCenteredText(
                        $img,
                        $letter,
                        $fontBold,
                        46,
                        $x1 + intdiv($cell, 2),
                        $y1 + intdiv($cell, 2) + 17,
                        [255, 255, 255]
                    );
                }
            }
        }
    }

    /**
     * 6. Cheeseboard Artwork: 5x5 coloured territory regions with vector crowns and cross markers.
     */
    private static function drawCheeseboardArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [192, 132, 252], 35, 4);

        $n = 5;
        $pad = 30;
        $gap = 12;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner - ($gap * ($n - 1)), $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        $regions = [
            [0, 0, 1, 1, 1],
            [0, 2, 2, 1, 3],
            [0, 2, 2, 3, 3],
            [4, 4, 2, 3, 3],
            [4, 4, 4, 3, 3],
        ];
        $palette = [
            0 => [153, 27, 27],
            1 => [30, 64, 175],
            2 => [6, 95, 70],
            3 => [107, 33, 168],
            4 => [180, 83, 9],
        ];
        $crowns = ['0,1' => true, '1,3' => true, '2,0' => true, '3,2' => true, '4,4' => true];
        $crosses = ['0,0' => true, '0,2' => true, '1,0' => true, '1,1' => true, '1,2' => true, '2,1' => true];

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $reg = $regions[$r][$c];
                $rgb = $palette[$reg];
                $x1 = $ox + $c * ($cell + $gap);
                $y1 = $oy + $r * ($cell + $gap);
                $x2 = $x1 + $cell;
                $y2 = $y1 + $cell;
                $cx = $x1 + intdiv($cell, 2);
                $cy = $y1 + intdiv($cell, 2);
                $key = "{$r},{$c}";

                $hasCrown = isset($crowns[$key]);
                self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, $rgb, 18);
                self::drawRoundedRectOutline(
                    $img,
                    $x1,
                    $y1,
                    $x2,
                    $y2,
                    16,
                    $hasCrown ? [251, 191, 36] : [255, 255, 255],
                    $hasCrown ? 0 : 75,
                    $hasCrown ? 4 : 2
                );

                if ($hasCrown) {
                    self::drawCrownEmblem($img, $cx, $cy);
                } elseif (isset($crosses[$key])) {
                    self::drawCenteredText($img, '×', $fontBold, 38, $cx, $cy + 13, [226, 232, 240]);
                }
            }
        }
    }

    /**
     * Draw a crisp vector crown polygon inside a Cheeseboard cell.
     */
    private static function drawCrownEmblem(GdImage $img, int $cx, int $cy): void
    {
        $gold = self::col($img, [251, 191, 36], 0);
        $cream = self::col($img, [254, 243, 199], 0);
        $points = [
            $cx - 32, $cy + 20,
            $cx - 38, $cy - 14,
            $cx - 16, $cy + 2,
            $cx,      $cy - 22,
            $cx + 16, $cy + 2,
            $cx + 38, $cy - 14,
            $cx + 32, $cy + 20,
        ];
        imagefilledpolygon($img, $points, $gold);
        imagefilledellipse($img, $cx - 38, $cy - 16, 10, 10, $cream);
        imagefilledellipse($img, $cx, $cy - 24, 12, 12, $cream);
        imagefilledellipse($img, $cx + 38, $cy - 16, 10, 10, $cream);
    }

    /**
     * Draw Classical 8x8 Chess Showcase with checkered board, gold pieces and check indicator.
     */
    private static function drawChessArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [251, 191, 36], 35, 4);

        $n = 8;
        $pad = 24;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner, $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $isLight = ($r + $c) % 2 === 0;
                $x1 = $ox + $c * $cell;
                $y1 = $oy + $r * $cell;
                $x2 = $x1 + $cell;
                $y2 = $y1 + $cell;
                $cx = $x1 + intdiv($cell, 2);
                $cy = $y1 + intdiv($cell, 2);

                $fill = $isLight ? [241, 245, 249] : [51, 65, 85];
                self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 4, $fill, 5);

                // Sample pieces on board
                if ($r === 0 && ($c === 0 || $c === 7)) {
                    self::drawCenteredText($img, 'R', $fontBold, 28, $cx, $cy + 10, [15, 23, 42]);
                } elseif ($r === 0 && $c === 4) {
                    self::drawCenteredText($img, 'K', $fontBold, 30, $cx, $cy + 10, [15, 23, 42]);
                } elseif ($r === 7 && $c === 3) {
                    self::drawCrownEmblem($img, $cx, $cy);
                } elseif ($r === 7 && ($c === 1 || $c === 6)) {
                    self::drawCenteredText($img, 'N', $fontBold, 28, $cx, $cy + 10, [251, 191, 36]);
                }
            }
        }
    }

    /**
     * Draw authentic Yoruba Ayò Olópon mahogany board with 12 hollows and 2 treasury chambers.
     */
    private static function drawAyoArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [26, 8, 2], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [245, 158, 11], 30, 4);

        $pad = 32;
        $ox = $bx + $pad;
        $oy = $by + $pad;
        $innerW = $bSize - ($pad * 2);
        $innerH = $bSize - ($pad * 2);

        // Carved wooden board body
        self::drawFilledRoundedRect($img, $ox, $oy + 120, $ox + $innerW, $oy + $innerH - 120, 36, [69, 26, 3], 0);
        self::drawRoundedRectOutline($img, $ox, $oy + 120, $ox + $innerW, $oy + $innerH - 120, 36, [180, 83, 9], 20, 3);

        // North & South Pits (6 columns x 2 rows)
        $pitW = intdiv($innerW - 120, 6);
        for ($col = 0; $col < 6; $col++) {
            $cx = $ox + 60 + $col * $pitW + intdiv($pitW, 2);
            $cyTop = $oy + 195;
            $cyBottom = $oy + $innerH - 195;

            // Top pit (North)
            imagefilledellipse($img, $cx, $cyTop, 72, 72, self::col($img, [21, 6, 1], 0));
            imageellipse($img, $cx, $cyTop, 72, 72, self::col($img, [120, 53, 15], 0));
            self::drawCenteredText($img, '4', $fontBold, 24, $cx, $cyTop + 8, [254, 243, 199]);

            // Bottom pit (South)
            imagefilledellipse($img, $cx, $cyBottom, 72, 72, self::col($img, [21, 6, 1], 0));
            imageellipse($img, $cx, $cyBottom, 72, 72, self::col($img, [245, 158, 11], 0));
            self::drawCenteredText($img, '4', $fontBold, 24, $cx, $cyBottom + 8, [254, 243, 199]);
        }

        // Center spine banner
        self::drawCenteredText($img, 'OPON AYO  •  48 SEEDS', $fontBold, 20, $bx + intdiv($bSize, 2), $oy + intdiv($innerH, 2) + 7, [251, 191, 36]);
    }

    /**
     * 7. Minesweeper Artwork: 5x5 tactical grid with revealed numbers, flagged tiles, and safe squares.
     */
    private static function drawMinesweeperArtwork(GdImage $img, int $bx, int $by, int $bSize, string $fontBold): void
    {
        self::drawFilledRoundedRect($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [15, 23, 42], 0);
        self::drawRoundedRectOutline($img, $bx, $by, $bx + $bSize, $by + $bSize, 28, [45, 212, 191], 35, 4);

        $n = 5;
        $pad = 30;
        $gap = 14;
        $inner = $bSize - ($pad * 2);
        $cell = intdiv($inner - ($gap * ($n - 1)), $n);
        $ox = $bx + $pad;
        $oy = $by + $pad;

        // '0'=cleared, '1'/'2'/'3'=clue, 'F'=flagged hazard, 'U'=unopened
        $board = [
            ['0', '1', 'F', '1', '0'],
            ['0', '1', '2', '2', '1'],
            ['1', '1', '1', 'F', '1'],
            ['F', '2', '2', '2', '1'],
            ['U', 'U', '1', '0', '0'],
        ];

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $v = $board[$r][$c];
                $x1 = $ox + $c * ($cell + $gap);
                $y1 = $oy + $r * ($cell + $gap);
                $x2 = $x1 + $cell;
                $y2 = $y1 + $cell;
                $cx = $x1 + intdiv($cell, 2);
                $cy = $y1 + intdiv($cell, 2);

                if ($v === 'F') {
                    self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, [127, 29, 29], 10);
                    self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 16, [251, 191, 36], 15, 3);
                    // Draw crisp vector flag
                    $pole = self::col($img, [254, 243, 199], 0);
                    $flag = self::col($img, [251, 191, 36], 0);
                    imagesetthickness($img, 5);
                    imageline($img, $cx - 10, $cy - 24, $cx - 10, $cy + 24, $pole);
                    imagesetthickness($img, 1);
                    imagefilledpolygon($img, [$cx - 8, $cy - 24, $cx + 22, $cy - 10, $cx - 8, $cy + 4], $flag);
                } elseif ($v === 'U') {
                    self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, [51, 65, 85], 15);
                    self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 16, [148, 163, 184], 55, 2);
                } else {
                    self::drawFilledRoundedRect($img, $x1, $y1, $x2, $y2, 16, [30, 41, 59], 35);
                    self::drawRoundedRectOutline($img, $x1, $y1, $x2, $y2, 16, [71, 85, 105], 80, 2);
                    if ($v !== '0') {
                        $numCol = $v === '1' ? [56, 189, 248] : [52, 211, 153];
                        self::drawCenteredText($img, $v, $fontBold, 44, $cx, $cy + 16, $numCol);
                    }
                }
            }
        }
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function drawFilledRoundedRect(
        GdImage $img,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $r,
        array $rgb,
        int $alpha
    ): void {
        $c = self::col($img, $rgb, $alpha);
        $r = max(1, min($r, intdiv(max(2, $x2 - $x1), 2), intdiv(max(2, $y2 - $y1), 2)));
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
    private static function drawRoundedRectOutline(
        GdImage $img,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $r,
        array $rgb,
        int $alpha,
        int $thickness
    ): void {
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
    private static function drawText(
        GdImage $img,
        string $font,
        int $size,
        int $x,
        int $baselineY,
        array $rgb,
        string $text
    ): void {
        $col = self::col($img, $rgb, 0);
        if (function_exists('imagettftext') && is_file($font)) {
            imagettftext($img, $size, 0, $x, $baselineY, $col, $font, $text);
            return;
        }
        $ascii = str_replace(['•', '—', '–', '…'], ['-', '--', '-', '...'], $text);
        imagestring($img, 5, $x, max(0, $baselineY - 14), $ascii, $col);
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function drawCenteredText(
        GdImage $img,
        string $text,
        string $font,
        int $size,
        int $cx,
        int $baselineY,
        array $rgb
    ): void {
        $tw = self::measureTextWidth($font, $size, $text);
        $x = $cx - intdiv($tw, 2);
        self::drawText($img, $font, $size, $x, $baselineY, $rgb, $text);
    }

    private static function measureTextWidth(string $font, int $size, string $text): int
    {
        if (function_exists('imagettfbbox') && is_file($font)) {
            $bb = imagettfbbox($size, 0, $font, $text);
            if ($bb !== false) {
                return abs($bb[2] - $bb[0]);
            }
        }
        return strlen($text) * 14;
    }

    /**
     * @return list<string>
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
            $w = self::measureTextWidth($font, $size, $try);
            if ($w <= $maxWidth) {
                $current = $try;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                    if (count($lines) >= $maxLines) {
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

    /**
     * Resolve workspace-bundled TrueType font path so text rendering never falls back to Latin-2 bitmap fonts.
     */
    public static function font(string $key = 'sans_bold'): string
    {
        $root = dirname(__DIR__, 2);
        $mpdf = $root . '/vendor/mpdf/mpdf/ttfonts/';
        $dompdf = $root . '/vendor/dompdf/dompdf/lib/fonts/';
        $festive = $root . '/resources/fonts/festive/';

        $candidates = match ($key) {
            'sans_bold' => [
                $mpdf . 'DejaVuSans-Bold.ttf',
                $dompdf . 'DejaVuSans-Bold.ttf',
                $mpdf . 'FreeSansBold.ttf',
                '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            ],
            default => [
                $mpdf . 'DejaVuSans.ttf',
                $dompdf . 'DejaVuSans.ttf',
                $festive . 'Montserrat.ttf',
                $mpdf . 'FreeSans.ttf',
                '/System/Library/Fonts/Supplemental/Arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            ],
        };

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return $mpdf . 'DejaVuSans-Bold.ttf';
    }
}
