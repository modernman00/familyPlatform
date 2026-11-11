<?php

declare(strict_types=1);

namespace App\services;

use GdImage;
use Intervention\Gif\Builder;
use InvalidArgumentException;
use RuntimeException;

/**
 * AtgFamilyGifRenderer — Sovereign Animated Brand GIF Synthesis Engine (ATG Pods 3 & 4)
 *
 * Produces high-fidelity, looping 400x400 animated GIFs reflecting the FamilyPlatform
 * brand identity (roots, kinship, reunion celebrations, legacy, warm embraces).
 *
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 * Authored by: Kaelen Vance (Interactive Systems) & Callum Wright (Micro-Libraries)
 * Compliant with: Universal British English, Inviolable Red Surface Rule, Zero External SaaS.
 */
final class AtgFamilyGifRenderer
{
    public const WIDTH = 400;
    public const HEIGHT = 400;
    public const FRAMES = 16;
    public const FRAME_DELAY = 0.08; // ~12.5 fps smooth loop

    /**
     * Render an official FamilyPlatform brand GIF by key.
     *
     * @param string $key An allowlisted official GIF key
     * @return string Raw GIF89a binary data
     * @throws InvalidArgumentException When key is unknown
     */
    public static function renderOfficial(string $key): string
    {
        if (!preg_match('/^[a-z0-9_]+$/', $key)) {
            throw new InvalidArgumentException('Invalid brand GIF key specification.');
        }

        $builder = Builder::canvas(self::WIDTH, self::HEIGHT);

        for ($f = 0; $f < self::FRAMES; $f++) {
            $im = match ($key) {
                'atg_heritage_tree_bloom'   => self::renderFrameHeritageTree($f, self::FRAMES),
                'atg_kinship_pulse'          => self::renderFrameKinshipPulse($f, self::FRAMES),
                'atg_family_reunion_toast'   => self::renderFrameReunionToast($f, self::FRAMES),
                'atg_welcome_home_circle'    => self::renderFrameWelcomeHome($f, self::FRAMES),
                'atg_family_love_hugs'       => self::renderFrameFamilyLove($f, self::FRAMES),
                'atg_proud_family_applause'  => self::renderFrameProudMoments($f, self::FRAMES),
                'atg_legacy_vault_spark'     => self::renderFrameLegacyVault($f, self::FRAMES),
                'atg_sunday_family_banter'   => self::renderFrameSundayBanter($f, self::FRAMES),
                default => throw new InvalidArgumentException("Unknown brand GIF key: {$key}"),
            };

            // Quantise to 128 colours for mobile data optimization and smooth playback
            imagetruecolortopalette($im, true, 128);

            ob_start();
            imagegif($im);
            $frameBinary = (string)ob_get_clean();
            imagedestroy($im);

            $builder->addFrame($frameBinary, self::FRAME_DELAY);
        }

        $builder->setLoops(0);
        return $builder->encode();
    }

    /**
     * Render a bespoke dynamic Dynasty GIF with custom family surname and caption.
     *
     * @param string $familyName Custom family surname (e.g. "The Olaogun Family")
     * @param string $caption Custom subtitle or motto (e.g. "Generations of Love")
     * @param string $theme Colorway ('crimson_gold', 'midnight_sapphire', 'emerald_heirloom')
     * @return string Raw GIF89a binary
     */
    public static function renderCustomDynasty(
        string $familyName,
        string $caption = '',
        string $theme = 'crimson_gold'
    ): string {
        $cleanName = trim(strip_tags($familyName));
        if ($cleanName === '') {
            $cleanName = 'Our Family Circle';
        }
        if (mb_strlen($cleanName) > 40) {
            $cleanName = mb_substr($cleanName, 0, 40);
        }

        $cleanCaption = trim(strip_tags($caption));
        if ($cleanCaption === '') {
            $cleanCaption = 'Connected Forever & Always';
        }
        if (mb_strlen($cleanCaption) > 50) {
            $cleanCaption = mb_substr($cleanCaption, 0, 50);
        }

        $allowedThemes = ['crimson_gold', 'midnight_sapphire', 'emerald_heirloom'];
        if (!in_array($theme, $allowedThemes, true)) {
            $theme = 'crimson_gold';
        }

        $builder = Builder::canvas(self::WIDTH, self::HEIGHT);

        for ($f = 0; $f < self::FRAMES; $f++) {
            $im = self::renderFrameCustomDynasty($cleanName, $cleanCaption, $theme, $f, self::FRAMES);

            // Quantise to 128 colours for mobile data optimization and smooth playback
            imagetruecolortopalette($im, true, 128);

            ob_start();
            imagegif($im);
            $frameBinary = (string)ob_get_clean();
            imagedestroy($im);

            $builder->addFrame($frameBinary, self::FRAME_DELAY);
        }

        $builder->setLoops(0);
        return $builder->encode();
    }

    // =========================================================================
    // FRAME RENDERING: OFFICIAL BRAND GIFS
    // =========================================================================

    /**
     * Frame: atg_heritage_tree_bloom
     * "Family Heritage Tree — Roots Deep, Love Strong"
     */
    private static function renderFrameHeritageTree(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [24, 4, 8], [90, 10, 20]);

        $phase = ($frame / $total) * 2 * M_PI;

        // Tree trunk and branching roots
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $brightGold = imagecolorallocate($im, 255, 235, 120) ?: 0;

        // Draw ornamental decorative border
        self::drawBrandBorder($im, $gold, $frame, $total);

        // Header typography
        self::drawBrandHeader($im, 'FAMILYPLATFORM', 'ROOTS DEEP • LOVE STRONG');

        // Draw Tree Roots spreading outwards into ground
        for ($r = -4; $r <= 4; $r++) {
            $rootEnd = 200 + ($r * 28);
            $rootY = 280 + abs($r) * 12;
            $pulseR = 2 * sin($phase + $r);
            imagesetthickness($im, 2);
            imageline($im, 200, 240, (int)($rootEnd + $pulseR), (int)$rootY, $amber);
            // Little root tip nodules
            imagefilledellipse($im, (int)($rootEnd + $pulseR), (int)$rootY, 4, 4, $gold);
        }

        // Draw Tree Trunk
        imagefilledpolygon($im, [
            185, 245,
            215, 245,
            208, 185,
            192, 185
        ], $gold);

        // Main canopy boughs
        imagesetthickness($im, 3);
        imageline($im, 192, 185, 130, 140, $amber);
        imageline($im, 200, 185, 200, 120, $amber);
        imageline($im, 208, 185, 270, 140, $amber);

        // Canopy golden leaves / foliage nodules (pulsating with light)
        $leafNodes = [
            [200, 115, 1.0], [175, 125, 0.8], [225, 125, 0.8],
            [150, 135, 1.2], [250, 135, 1.2], [130, 155, 0.9],
            [270, 155, 0.9], [170, 150, 1.1], [230, 150, 1.1],
            [190, 135, 1.0], [210, 135, 1.0], [160, 170, 0.7],
            [240, 170, 0.7], [200, 155, 1.3]
        ];

        foreach ($leafNodes as $idx => [$lx, $ly, $scale]) {
            $leafPhase = $phase + ($idx * 0.4);
            $r = (int)((8 * $scale) + (3 * sin($leafPhase)));
            imagefilledellipse($im, $lx, $ly, $r * 2, $r * 2, $brightGold);
            imageellipse($im, $lx, $ly, ($r * 2) + 2, ($r * 2) + 2, $champagne);
        }

        // Central beating heart at the trunk fork
        $heartScale = 0.85 + (0.15 * sin($phase * 2));
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;
        self::drawHeart($im, 200, 192, (float)$heartScale, $crimson, $gold);

        // Floating golden stardust rising from roots
        for ($p = 0; $p < 14; $p++) {
            $px = (int)(110 + (($p * 23 + $frame * 9) % 180));
            $py = (int)(290 - (($frame * 8 + $p * 21) % 180));
            $starR = 1.5 + sin($phase + $p);
            self::drawStarSparkle($im, $px, $py, (float)$starR, $champagne);
        }

        // Bottom Brand Signature Badge
        self::drawBrandFooter($im, '✦ OFFICIAL FAMILY BRAND ✦');

        return $im;
    }

    /**
     * Frame: atg_kinship_pulse
     * "Generations Connected — Kinship Pulse"
     */
    private static function renderFrameKinshipPulse(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [12, 16, 28], [55, 10, 22]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $cyanPulse = imagecolorallocate($im, 180, 230, 255) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'KINSHIP PULSE', 'GENERATIONS CONNECTED');

        $cx = 200;
        $cy = 205;

        // 3 Concentric Orbital Kinship Rings
        $radii = [42, 70, 98];
        foreach ($radii as $k => $rad) {
            $ringColor = ($k === 1) ? $gold : $amber;
            imagesetthickness($im, 1);
            imageellipse($im, $cx, $cy, $rad * 2, $rad * 2, $ringColor);

            // Shimmering node on each ring rotating
            $dir = ($k % 2 === 0) ? 1 : -1;
            $ang = $phase * $dir + ($k * 1.5);
            $nx = (int)($cx + ($rad * cos($ang)));
            $ny = (int)($cy + ($rad * sin($ang)));

            imagefilledellipse($im, $nx, $ny, 9, 9, $champagne);
            imageellipse($im, $nx, $ny, 13, 13, $gold);

            // Connecting laser beam to center
            imagesetthickness($im, 1);
            imageline($im, $cx, $cy, $nx, $ny, $amber);
        }

        // Central Kinship Energy Core with radiating waves
        $pulseSize = 22 + (int)(6 * sin($phase * 2));
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;
        imagefilledellipse($im, $cx, $cy, $pulseSize * 2, $pulseSize * 2, $crimson);
        imageellipse($im, $cx, $cy, ($pulseSize * 2) + 4, ($pulseSize * 2) + 4, $gold);

        // Core icon/monogram
        self::drawHeart($im, $cx, $cy, 0.7 + (float)(0.1 * sin($phase * 2)), $gold, $champagne);

        // Surrounding orbital stars
        for ($s = 0; $s < 8; $s++) {
            $sAng = ($s / 8) * 2 * M_PI + ($phase * 0.5);
            $sx = (int)($cx + (120 * cos($sAng)));
            $sy = (int)($cy + (120 * sin($sAng)));
            self::drawStarSparkle($im, $sx, $sy, 2.5, $cyanPulse);
        }

        self::drawBrandFooter($im, '✦ FAMILY FOREVER & ALWAYS ✦');

        return $im;
    }

    /**
     * Frame: atg_family_reunion_toast
     * "Family Reunion Toast — Reunited & Celebrating"
     */
    private static function renderFrameReunionToast(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [28, 4, 10], [95, 12, 25]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'FAMILY REUNION', 'REUNITED & CELEBRATING');

        // Confetti flutter background (falling down)
        for ($c = 0; $c < 24; $c++) {
            $confX = (int)(30 + (($c * 17 + $frame * 5) % 340));
            $confY = (int)(80 + (($c * 23 + $frame * 14) % 240));
            $cCol = ($c % 3 === 0) ? $gold : (($c % 3 === 1) ? $champagne : $amber);
            $tilt = (int)(sin($phase + $c) * 4);
            imagefilledrectangle($im, $confX, $confY, $confX + 6 + $tilt, $confY + 3, $cCol);
        }

        // Two Champagne Flutes Clinking
        $clinkProgress = sin($phase);
        // Tilt angles oscillate
        $tiltLeft = 14 - (int)($clinkProgress * 6);
        $tiltRight = -14 + (int)($clinkProgress * 6);

        // Flute Left
        $lx = 175 - (int)($clinkProgress * 5);
        $ly = 205;
        self::drawChampagneFlute($im, $lx, $ly, $tiltLeft, $gold, $champagne);

        // Flute Right
        $rx = 225 + (int)($clinkProgress * 5);
        $ry = 205;
        self::drawChampagneFlute($im, $rx, $ry, $tiltRight, $gold, $champagne);

        // Toast Contact Spark Burst at (200, 185)
        $burstScale = (float)(abs(sin($phase)) * 12);
        if ($burstScale > 4) {
            self::drawStarSparkle($im, 200, 185, $burstScale, $champagne);
            self::drawStarSparkle($im, 200, 185, $burstScale * 0.6, $gold);
        }

        // Effervescent rising bubbles
        for ($b = 0; $b < 10; $b++) {
            $bx = 180 + ($b % 2 === 0 ? -12 : 12) + (int)(sin($phase + $b) * 6);
            $by = (int)(220 - (($frame * 7 + $b * 16) % 80));
            imageellipse($im, $bx, $by, 4, 4, $champagne);
        }

        self::drawBrandFooter($im, '✦ CHERISHED MOMENTS ✦');

        return $im;
    }

    /**
     * Frame: atg_welcome_home_circle
     * "Welcome Home — Family Circle"
     */
    private static function renderFrameWelcomeHome(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [26, 6, 8], [80, 16, 20]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $warmAmber = imagecolorallocate($im, 251, 191, 36) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'WELCOME HOME', 'TO OUR FAMILY CIRCLE');

        $cx = 200;
        $cy = 205;

        // Radiating sunburst / portal rays (16 rays)
        for ($ray = 0; $ray < 16; $ray++) {
            $rayAng = ($ray / 16) * 2 * M_PI + ($phase * 0.25);
            $len = 80 + (int)(15 * sin($phase * 2 + $ray));
            $rx = (int)($cx + ($len * cos($rayAng)));
            $ry = (int)($cy + ($len * sin($rayAng)));
            imagesetthickness($im, 1);
            imageline($im, $cx, $cy, $rx, $ry, $warmAmber);
        }

        // Concentric glowing halo portal
        $haloR = 56 + (int)(6 * sin($phase));
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;
        imagefilledellipse($im, $cx, $cy, $haloR * 2, $haloR * 2, $crimson);
        imageellipse($im, $cx, $cy, ($haloR * 2) + 4, ($haloR * 2) + 4, $gold);
        imageellipse($im, $cx, $cy, ($haloR * 2) + 10, ($haloR * 2) + 10, $champagne);

        // Welcoming Family Emblem (Two adults + child silhouette in gold)
        self::drawFamilySilhouettes($im, $cx, $cy, $gold, $champagne);

        // Floating welcoming warm embers
        for ($m = 0; $m < 12; $m++) {
            $mx = (int)(60 + (($m * 27 + $frame * 6) % 280));
            $my = (int)(270 - (($frame * 7 + $m * 22) % 150));
            imagefilledellipse($im, $mx, $my, 3, 3, $champagne);
        }

        self::drawBrandFooter($im, '✦ BELONGING & LOVE ✦');

        return $im;
    }

    /**
     * Frame: atg_family_love_hugs
     * "Sending Family Love & Warm Hugs"
     */
    private static function renderFrameFamilyLove(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [30, 4, 10], [90, 8, 20]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $crimson = imagecolorallocate($im, 185, 28, 42) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'SENDING FAMILY LOVE', 'WARMEST HUGS ACROSS EVERY MILE');

        $cx = 200;
        $cy = 200;

        // Double heartbeat pulse simulation (systole / diastole)
        $pulse = sin($phase);
        $heartScale = 1.25 + (0.22 * pow(max(0.0, (float)$pulse), 2));

        // Expanding concentric love ripples
        for ($rip = 1; $rip <= 3; $rip++) {
            $ripPhase = fmod($phase + ($rip * 0.7), 2 * M_PI);
            $ripR = (int)(50 + ($ripPhase * 18));
            imagesetthickness($im, 2);
            imageellipse($im, $cx, $cy, $ripR * 2, (int)($ripR * 1.8), $gold);
        }

        // Primary Beating Heart
        self::drawHeart($im, $cx, $cy, (float)$heartScale, $crimson, $gold);

        // Mini floating heart bubbles
        for ($h = 0; $h < 7; $h++) {
            $hx = (int)(90 + ($h * 32) + (sin($phase + $h) * 10));
            $hy = (int)(290 - (($frame * 8 + $h * 26) % 180));
            $hScale = 0.35 + (0.1 * sin($phase + $h));
            self::drawHeart($im, $hx, $hy, (float)$hScale, $gold, $champagne);
        }

        self::drawBrandFooter($im, '✦ ALL OUR LOVE ✦');

        return $im;
    }

    /**
     * Frame: atg_proud_family_applause
     * "Proud Family Moments — Standing Ovation"
     */
    private static function renderFrameProudMoments(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [24, 6, 12], [85, 14, 25]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'SO PROUD OF YOU!', 'FAMILY CHAMPION • WELL DONE!');

        $cx = 200;
        $cy = 205;

        // Celebratory Fireworks bursts in top corners
        $fw1Scale = (float)(abs(sin($phase)) * 8);
        $fw2Scale = (float)(abs(cos($phase)) * 8);
        self::drawStarSparkle($im, 80, 120, $fw1Scale, $gold);
        self::drawStarSparkle($im, 320, 120, $fw2Scale, $champagne);

        // Laurel Wreath Leaves (Left & Right arches)
        for ($w = 0; $w < 8; $w++) {
            $ang = -M_PI_2 + ($w * 0.28);
            // Left branch
            $lwx = (int)($cx - 52 + (15 * cos($ang)));
            $lwy = (int)($cy - 20 + ($w * 10));
            imagefilledellipse($im, $lwx, $lwy, 10, 6, $gold);

            // Right branch
            $rwx = (int)($cx + 52 - (15 * cos($ang)));
            $rwy = (int)($cy - 20 + ($w * 10));
            imagefilledellipse($im, $rwx, $rwy, 10, 6, $gold);
        }

        // Bouncing Champion Star & Medallion in center
        $bounce = (int)(sin($phase * 2) * 6);
        $starY = $cy + $bounce;
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;
        imagefilledellipse($im, $cx, $starY, 68, 68, $crimson);
        imageellipse($im, $cx, $starY, 72, 72, $gold);

        // Star inside
        self::drawStarSparkle($im, $cx, $starY, 16.0, $gold, (float)($phase * 0.5));
        self::drawStarSparkle($im, $cx, $starY, 8.0, $champagne, (float)($phase * 0.5));

        // Floating celebratory ribbons
        for ($r = 0; $r < 12; $r++) {
            $rx = (int)(50 + (($r * 29 + $frame * 6) % 300));
            $ry = (int)(150 + (($frame * 8 + $r * 20) % 150));
            imagefilledrectangle($im, $rx, $ry, $rx + 5, $ry + 2, ($r % 2 === 0) ? $gold : $champagne);
        }

        self::drawBrandFooter($im, '✦ PROUD FAMILY MOMENTS ✦');

        return $im;
    }

    /**
     * Frame: atg_legacy_vault_spark
     * "Family Legacy — Cherished Forever"
     */
    private static function renderFrameLegacyVault(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [18, 12, 22], [70, 10, 24]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'OUR FAMILY LEGACY', 'CHERISHED TODAY • PRESERVED FOREVER');

        $cx = 200;
        $cy = 205;

        // Ornate Medallion / Vault Crest
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;
        imagefilledellipse($im, $cx, $cy, 110, 110, $crimson);
        imageellipse($im, $cx, $cy, 116, 116, $gold);
        imageellipse($im, $cx, $cy, 122, 122, $amber);

        // Monogram / Crest inside
        self::drawHeart($im, $cx, $cy, 0.9, $gold, $champagne);

        // Sweeping glint / laser sheen across medallion (left-to-right)
        $glintX = (int)(140 + (($frame / $total) * 120));
        imagesetthickness($im, 3);
        imageline($im, $glintX - 15, $cy - 45, $glintX + 15, $cy + 45, $champagne);
        imagesetthickness($im, 1);
        imageline($im, $glintX - 10, $cy - 45, $glintX + 20, $cy + 45, $gold);

        // 4 Corner Constellation Stars
        $cornerCoords = [[65, 130], [335, 130], [65, 290], [335, 290]];
        foreach ($cornerCoords as $idx => [$ccx, $ccy]) {
            $starR = (float)(3.0 + 2.0 * sin($phase + $idx));
            self::drawStarSparkle($im, $ccx, $ccy, $starR, $champagne);
        }

        self::drawBrandFooter($im, '✦ DYNASTY & HERITAGE ✦');

        return $im;
    }

    /**
     * Frame: atg_sunday_family_banter
     * "Sunday Banter — Laughter & Joy"
     */
    private static function renderFrameSundayBanter(int $frame, int $total): GdImage
    {
        $im = self::createCanvas();
        self::drawVignetteBackground($im, [28, 4, 10], [92, 14, 28]);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $crimson = imagecolorallocate($im, 163, 22, 33) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);
        self::drawBrandHeader($im, 'FAMILY BANTER', 'LAUGHTER THAT KEEPS US TOGETHER');

        $cx = 200;
        $cy = 205;

        // Bouncing dialogue bubble Left
        $b1Y = (int)(185 + (sin($phase) * 6));
        imagefilledellipse($im, 160, $b1Y, 75, 55, $crimson);
        imageellipse($im, 160, $b1Y, 77, 57, $gold);
        self::drawHeart($im, 160, $b1Y, 0.45, $champagne, $gold);

        // Bouncing dialogue bubble Right
        $b2Y = (int)(225 + (cos($phase) * 6));
        imagefilledellipse($im, 240, $b2Y, 75, 55, $gold);
        imageellipse($im, 240, $b2Y, 77, 57, $champagne);
        // Smiling eyes in right bubble
        $black = imagecolorallocate($im, 30, 5, 10) ?: 0;
        imagefilledellipse($im, 230, $b2Y - 2, 4, 4, $black);
        imagefilledellipse($im, 250, $b2Y - 2, 4, 4, $black);
        imagearc($im, 240, $b2Y + 2, 14, 10, 0, 180, $black);

        // Floating musical & laugh sparkles
        for ($m = 0; $m < 8; $m++) {
            $mx = (int)(70 + (($m * 37 + $frame * 8) % 260));
            $my = (int)(290 - (($frame * 7 + $m * 24) % 170));
            self::drawStarSparkle($im, $mx, $my, 2.5, $champagne);
        }

        self::drawBrandFooter($im, '✦ SUNDAY BANTER ✦');

        return $im;
    }

    /**
     * Frame: custom_dynasty
     * Bespoke Dynasty animated GIF for custom family surnames.
     */
    private static function renderFrameCustomDynasty(
        string $familyName,
        string $caption,
        string $theme,
        int $frame,
        int $total
    ): GdImage {
        $im = self::createCanvas();

        [$bgCenter, $bgEdge] = match ($theme) {
            'midnight_sapphire' => [[10, 18, 36], [16, 32, 65]],
            'emerald_heirloom'  => [[6, 24, 14], [12, 54, 30]],
            default             => [[26, 4, 8], [92, 12, 22]],
        };

        self::drawVignetteBackground($im, $bgCenter, $bgEdge);

        $phase = ($frame / $total) * 2 * M_PI;
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;
        $amber = imagecolorallocate($im, 245, 158, 11) ?: 0;

        self::drawBrandBorder($im, $gold, $frame, $total);

        // Brand header
        self::drawBrandHeader($im, 'FAMILYPLATFORM', 'DYNASTY ARCHIVE');

        $cx = 200;
        $cy = 195;

        // Animated swirling particle vortex
        for ($p = 0; $p < 20; $p++) {
            $ang = ($p / 20) * 2 * M_PI + ($phase);
            $dist = 70 + (int)(15 * sin($phase * 2 + $p));
            $px = (int)($cx + ($dist * cos($ang)));
            $py = (int)($cy + ($dist * sin($ang) * 0.7));
            $sparkCol = ($p % 2 === 0) ? $gold : $champagne;
            imagefilledellipse($im, $px, $py, 3, 3, $sparkCol);
        }

        // Central Emblem Badge
        $pulseR = 42 + (int)(4 * sin($phase * 2));
        $crestBg = imagecolorallocate($im, 163, 22, 33) ?: 0;
        imagefilledellipse($im, $cx, $cy, $pulseR * 2, $pulseR * 2, $crestBg);
        imageellipse($im, $cx, $cy, ($pulseR * 2) + 4, ($pulseR * 2) + 4, $gold);
        self::drawHeart($im, $cx, $cy, 0.65, $gold, $champagne);

        // Dynamic Custom Family Surname Typography (Cinzel font)
        $fontCinzel = self::resolveFont('Cinzel.ttf');
        $fontMont = self::resolveFont('Montserrat.ttf');

        // Choose font size dynamically based on length
        $nameLen = mb_strlen($familyName);
        $nameSize = $nameLen > 24 ? 13 : ($nameLen > 16 ? 15 : 18);
        self::drawCentredText($im, $familyName, 275, $nameSize, $gold, $fontCinzel);

        // Custom Caption
        $capLen = mb_strlen($caption);
        $capSize = $capLen > 30 ? 9 : 10;
        self::drawCentredText($im, $caption, 302, $capSize, $champagne, $fontMont);

        self::drawBrandFooter($im, '✦ OFFICIAL FAMILY BRAND ✦');

        return $im;
    }

    // =========================================================================
    // GEOMETRIC & ARTISTIC DRAWING HELPERS
    // =========================================================================

    private static function createCanvas(): GdImage
    {
        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if (!$im) {
            throw new RuntimeException('Failed to allocate GD image buffer.');
        }
        imagealphablending($im, true);
        imagesavealpha($im, false);
        return $im;
    }

    /**
     * Radial vignette background from center to corners.
     *
     * @param array{0:int,1:int,2:int} $centerRgb
     * @param array{0:int,1:int,2:int} $edgeRgb
     */
    private static function drawVignetteBackground(
        GdImage $im,
        array $centerRgb,
        array $edgeRgb
    ): void {
        $cx = self::WIDTH / 2;
        $cy = self::HEIGHT / 2;
        $maxDist = sqrt(($cx * $cx) + ($cy * $cy));

        // Stepped rings to build radial gradient efficiently (20 concentric bands)
        $steps = 22;
        for ($s = $steps; $s >= 0; $s--) {
            $t = $s / (float)$steps;
            $r = (int)($centerRgb[0] + $t * ($edgeRgb[0] - $centerRgb[0]));
            $g = (int)($centerRgb[1] + $t * ($edgeRgb[1] - $centerRgb[1]));
            $b = (int)($centerRgb[2] + $t * ($edgeRgb[2] - $centerRgb[2]));

            $color = imagecolorallocate($im, max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b))) ?: 0;
            $radius = (int)($t * $maxDist * 1.15);
            imagefilledellipse($im, (int)$cx, (int)$cy, $radius * 2, $radius * 2, $color);
        }
    }

    /**
     * Outer ornamental gold border with corner rosettes.
     */
    private static function drawBrandBorder(GdImage $im, int $gold, int $frame, int $total): void
    {
        imagesetthickness($im, 2);
        imagerectangle($im, 10, 10, self::WIDTH - 11, self::HEIGHT - 11, $gold);
        imagesetthickness($im, 1);
        imagerectangle($im, 14, 14, self::WIDTH - 15, self::HEIGHT - 15, $gold);

        // Corner rosette diamonds
        $corners = [
            [12, 12],
            [self::WIDTH - 13, 12],
            [12, self::HEIGHT - 13],
            [self::WIDTH - 13, self::HEIGHT - 13]
        ];
        foreach ($corners as [$cx, $cy]) {
            imagefilledellipse($im, $cx, $cy, 6, 6, $gold);
        }
    }

    /**
     * Top header with FamilyPlatform branding.
     */
    private static function drawBrandHeader(GdImage $im, string $title, string $subtitle): void
    {
        $fontCinzel = self::resolveFont('Cinzel.ttf');
        $fontMont = self::resolveFont('Montserrat.ttf');

        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        $champagne = imagecolorallocate($im, 255, 247, 237) ?: 0;

        self::drawCentredText($im, $title, 46, 15, $gold, $fontCinzel);
        self::drawCentredText($im, $subtitle, 66, 8, $champagne, $fontMont);
    }

    /**
     * Discrete bottom brand signature.
     */
    private static function drawBrandFooter(GdImage $im, string $tagline): void
    {
        $fontMont = self::resolveFont('Montserrat.ttf');
        $gold = imagecolorallocate($im, 255, 215, 0) ?: 0;
        self::drawCentredText($im, $tagline, self::HEIGHT - 22, 8, $gold, $fontMont);
    }

    /**
     * Draw text centered horizontally at a specific Y baseline.
     */
    private static function drawCentredText(
        GdImage $im,
        string $text,
        int $y,
        int $fontSize,
        int $color,
        string $fontPath
    ): void {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        if ($bbox === false) {
            return;
        }
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = (int)((self::WIDTH - $textWidth) / 2);
        imagettftext($im, $fontSize, 0, $x, $y, $color, $fontPath, $text);
    }

    /**
     * Draw a mathematical parametric vector heart.
     */
    private static function drawHeart(
        GdImage $im,
        int $cx,
        int $cy,
        float $scale,
        int $fillColor,
        int $borderColor
    ): void {
        $points = [];
        $steps = 36;
        for ($i = 0; $i < $steps; $i++) {
            $t = ($i / $steps) * 2 * M_PI;
            // Standard cardioid parametric curve:
            // x = 16 sin^3(t)
            // y = -(13 cos(t) - 5 cos(2t) - 2 cos(3t) - cos(4t))
            $hx = 16 * pow(sin($t), 3);
            $hy = -(13 * cos($t) - 5 * cos(2 * $t) - 2 * cos(3 * $t) - cos(4 * $t));

            $points[] = (int)($cx + ($hx * $scale));
            $points[] = (int)($cy + ($hy * $scale));
        }

        imagefilledpolygon($im, $points, $fillColor);
        imagesetthickness($im, 2);
        imagepolygon($im, $points, $borderColor);
    }

    /**
     * Draw a 4-point radiant star sparkle.
     */
    private static function drawStarSparkle(
        GdImage $im,
        int $cx,
        int $cy,
        float $radius,
        int $color,
        float $rotation = 0.0
    ): void {
        if ($radius <= 0.5) {
            return;
        }

        $points = [];
        for ($i = 0; $i < 8; $i++) {
            $angle = $rotation + ($i * M_PI / 4);
            $r = ($i % 2 === 0) ? $radius * 2.2 : $radius * 0.45;
            $points[] = (int)($cx + ($r * cos($angle)));
            $points[] = (int)($cy + ($r * sin($angle)));
        }

        imagefilledpolygon($im, $points, $color);
    }

    /**
     * Draw a champagne flute with liquid and bubbles.
     */
    private static function drawChampagneFlute(
        GdImage $im,
        int $cx,
        int $cy,
        int $tiltDegrees,
        int $glassColor,
        int $liquidColor
    ): void {
        $rad = deg2rad($tiltDegrees);
        $cos = cos($rad);
        $sin = sin($rad);

        // Flute body: bowl polygon, stem line, base line
        $rotate = static function (float $x, float $y) use ($cx, $cy, $cos, $sin): array {
            $rx = ($x * $cos) - ($y * $sin);
            $ry = ($x * $sin) + ($y * $cos);
            return [(int)($cx + $rx), (int)($cy + $ry)];
        };

        // Bowl polygon
        $bowlPoints = [];
        $localBowl = [
            [-10, -35], [10, -35], [7, 5], [0, 18], [-7, 5]
        ];
        foreach ($localBowl as [$lx, $ly]) {
            [$px, $py] = $rotate($lx, $ly);
            $bowlPoints[] = $px;
            $bowlPoints[] = $py;
        }

        // Fill champagne liquid inside bowl
        imagefilledpolygon($im, $bowlPoints, $liquidColor);
        imagesetthickness($im, 2);
        imagepolygon($im, $bowlPoints, $glassColor);

        // Stem
        [$s1x, $s1y] = $rotate(0, 18);
        [$s2x, $s2y] = $rotate(0, 48);
        imageline($im, $s1x, $s1y, $s2x, $s2y, $glassColor);

        // Base foot
        [$b1x, $b1y] = $rotate(-14, 48);
        [$b2x, $b2y] = $rotate(14, 48);
        imageline($im, $b1x, $b1y, $b2x, $b2y, $glassColor);
    }

    /**
     * Draw family silhouettes (two adults holding hands with child).
     */
    private static function drawFamilySilhouettes(
        GdImage $im,
        int $cx,
        int $cy,
        int $figureColor,
        int $accentColor
    ): void {
        // Adult Left (Mother/Parent)
        imagefilledellipse($im, $cx - 18, $cy - 14, 12, 12, $figureColor);
        imagefilledellipse($im, $cx - 18, $cy + 8, 16, 26, $figureColor);

        // Adult Right (Father/Parent)
        imagefilledellipse($im, $cx + 18, $cy - 16, 13, 13, $figureColor);
        imagefilledellipse($im, $cx + 18, $cy + 8, 18, 28, $figureColor);

        // Child Center
        imagefilledellipse($im, $cx, $cy - 2, 9, 9, $accentColor);
        imagefilledellipse($im, $cx, $cy + 14, 12, 18, $accentColor);

        // Golden kinship bond arc over their heads
        imagesetthickness($im, 2);
        imagearc($im, $cx, $cy - 8, 64, 44, 200, 340, $accentColor);
    }

    /**
     * Resolve font path with defensive fallbacks.
     */
    private static function resolveFont(string $fontName): string
    {
        $primary = dirname(__DIR__, 2) . '/resources/fonts/festive/' . $fontName;
        if (file_exists($primary)) {
            return $primary;
        }

        $fallback = dirname(__DIR__, 2) . '/resources/fonts/festive/Montserrat.ttf';
        if (file_exists($fallback)) {
            return $fallback;
        }

        throw new RuntimeException("TrueType font not found: {$fontName}");
    }
}
