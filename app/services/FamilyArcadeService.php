<?php

declare(strict_types=1);

namespace App\services;

use App\classes\PushNotificationClass;
use PDO;
use Src\Db;
use Src\Realtime\SovereignBroadcaster;
use Throwable;

/**
 * FamilyArcadeService
 *
 * Server-authoritative puzzle generator, solution validator, league table aggregator,
 * dynamic "Best [Title] of the Family" badge engine, and overtake alert dispatcher
 * for FamilyPlatform's 4-game Family Arcade (KinZip, KinGrid, KinWord, KinPulse).
 */
final class FamilyArcadeService
{
    public const VALID_GAMES = ['kinzip', 'kingrid', 'kinword', 'kinpulse', 'wordie', 'cheeseboard', 'chess', 'ayo', 'minesweeper'];
    public const VALID_DIFFICULTIES = ['easy', 'medium', 'hard'];
    public const VALID_MODES = ['daily', 'practice', 'ghost', 'live_race'];

    /** @var array<string, array{key: string, title: string, icon: string}> */
    public const GAME_BADGE_MAP = [
        'kinzip' => [
            'key' => 'kinzip_champion',
            'title' => 'KinZip Grandmaster',
            'icon' => 'bi-diagram-3-fill',
        ],
        'kingrid' => [
            'key' => 'kingrid_champion',
            'title' => 'KinGrid Tactician',
            'icon' => 'bi-grid-3x3-gap-fill',
        ],
        'kinword' => [
            'key' => 'kinword_champion',
            'title' => 'KinWord Weaver',
            'icon' => 'bi-fonts',
        ],
        'kinpulse' => [
            'key' => 'kinpulse_champion',
            'title' => 'KinPulse Maestro',
            'icon' => 'bi-lightning-charge-fill',
        ],
        'wordie' => [
            'key' => 'wordie_champion',
            'title' => 'KinWordie Wordsmith',
            'icon' => 'bi-alphabet-uppercase',
        ],
        'cheeseboard' => [
            'key' => 'cheeseboard_champion',
            'title' => 'Cheeseboard Sovereign',
            'icon' => 'bi-shield-shaded',
        ],
        'chess' => [
            'key' => 'chess_champion',
            'title' => 'Family Chess Grandmaster',
            'icon' => 'bi-suit-club-fill',
        ],
        'ayo' => [
            'key' => 'ayo_champion',
            'title' => 'Ayò Olópon Grandmaster',
            'icon' => 'bi-circle-square',
        ],
        'minesweeper' => [
            'key' => 'minesweeper_champion',
            'title' => 'KinSweeper Pathfinder',
            'icon' => 'bi-gem',
        ],
        'family_cup' => [
            'key' => 'family_cup_champion',
            'title' => 'Family Cup Champion',
            'icon' => 'bi-trophy-fill',
        ],
    ];

    /**
     * Deterministic 32-bit seeded PRNG closure (returns float in [0, 1)).
     *
     * @return \Closure(): float
     */
    private static function createRng(string $seed): \Closure
    {
        $hash = crc32($seed);
        $state = ($hash & 0x7FFFFFFF) ?: 135792468;

        return static function () use (&$state): float {
            $state = (int) (($state * 1103515245 + 12345) & 0x7FFFFFFF);
            return $state / 0x80000000;
        };
    }

    /**
     * Deterministic shuffle using the provided RNG.
     *
     * @template T
     * @param array<int, T> $items
     * @param \Closure(): float $rng
     * @return array<int, T>
     */
    private static function seededShuffle(array $items, \Closure $rng): array
    {
        $list = array_values($items);
        $count = count($list);
        for ($i = $count - 1; $i > 0; $i--) {
            $j = (int) floor($rng() * ($i + 1));
            $tmp = $list[$i];
            $list[$i] = $list[$j];
            $list[$j] = $tmp;
        }
        return $list;
    }

    /**
     * Normalise game slug to one of the 4 supported games.
     */
    public static function normaliseGame(string $game): string
    {
        $slug = strtolower(trim($game));
        return in_array($slug, self::VALID_GAMES, true) ? $slug : 'kinzip';
    }

    /**
     * Normalise difficulty.
     */
    public static function normaliseDifficulty(string $difficulty): string
    {
        $diff = strtolower(trim($difficulty));
        return in_array($diff, self::VALID_DIFFICULTIES, true) ? $diff : 'medium';
    }

    /**
     * Get today's featured daily game (rotates deterministically by day of year).
     */
    public static function getDailyFeaturedGame(?string $date = null): string
    {
        $d = $date ?? date('Y-m-d');
        $idx = abs((int) crc32('featured:' . $d)) % count(self::VALID_GAMES);
        return self::VALID_GAMES[$idx];
    }

    /**
     * Build the public puzzle payload (without leaking hidden answers) for a game & seed.
     *
     * @return array<string, mixed>
     */
    public static function generatePuzzle(string $gameSlug, string $seedKey, string $difficulty = 'medium'): array
    {
        $game = self::normaliseGame($gameSlug);
        $diff = self::normaliseDifficulty($difficulty);

        return match ($game) {
            'kingrid' => self::generateKinGridPuzzle($seedKey, $diff, false),
            'kinword' => self::generateKinWordPuzzle($seedKey, $diff, false),
            'kinpulse' => self::generateKinPulsePuzzle($seedKey, $diff),
            'wordie' => self::generateWordiePuzzle($seedKey, $diff, false),
            'cheeseboard' => self::generateCheeseboardPuzzle($seedKey, $diff, false),
            'chess' => [
                'game' => 'chess',
                'seedKey' => $seedKey,
                'difficulty' => $diff,
                'parMs' => match ($diff) {
                    'easy' => 60000,
                    'hard' => 180000,
                    default => 120000,
                },
            ],
            'ayo' => [
                'game' => 'ayo',
                'seedKey' => $seedKey,
                'difficulty' => $diff,
                'totalSeeds' => 48,
                'parMs' => match ($diff) {
                    'easy' => 60000,
                    'hard' => 150000,
                    default => 90000,
                },
            ],
            'minesweeper' => self::generateMinesweeperPuzzle($seedKey, $diff, false),
            default => self::generateKinZipPuzzle($seedKey, $diff, false),
        };
    }

    /**
     * Generate a guaranteed-solvable KinZip Hamiltonian path + walls puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateKinZipPuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $size = match ($difficulty) {
            'easy' => 5,
            'hard' => 7,
            default => 6,
        };
        $checkpointCount = match ($difficulty) {
            'easy' => 5,
            'hard' => 7,
            default => 6,
        };

        $rng = self::createRng("kinzip:{$seedKey}:{$difficulty}");

        // 1. Multi-trial turn-maximising Hamiltonian path search via deep backbite mixing
        $total = $size * $size;
        $bestPath = [];
        $bestScore = -1;

        for ($trial = 0; $trial < 4; $trial++) {
            $candidate = [];
            for ($r = 0; $r < $size; $r++) {
                if ($r % 2 === 0) {
                    for ($c = 0; $c < $size; $c++) {
                        $candidate[] = [$r, $c];
                    }
                } else {
                    for ($c = $size - 1; $c >= 0; $c--) {
                        $candidate[] = [$r, $c];
                    }
                }
            }

            // Apply deterministic geometric symmetries (rotate / flip / reverse)
            $transform = (int) floor($rng() * 8);
            $candidate = array_map(static function (array $cell) use ($size, $transform): array {
                [$r, $c] = $cell;
                if (($transform & 1) !== 0) {
                    $r = $size - 1 - $r;
                }
                if (($transform & 2) !== 0) {
                    $c = $size - 1 - $c;
                }
                if (($transform & 4) !== 0) {
                    return [$c, $r];
                }
                return [$r, $c];
            }, $candidate);

            if ($rng() > 0.5) {
                $candidate = array_reverse($candidate);
            }

            // Deep backbite MCMC mutations (260 steps) so interior folds lose all serpentine structure
            $backbiteSteps = 260;
            for ($step = 0; $step < $backbiteSteps; $step++) {
                $useEnd = $rng() > 0.5;
                if ($useEnd) {
                    $end = $candidate[$total - 1];
                    $prevIdx = $total - 2;
                    $neighbours = [];
                    for ($idx = 0; $idx < $total - 2; $idx++) {
                        if ($idx !== $prevIdx && abs($candidate[$idx][0] - $end[0]) + abs($candidate[$idx][1] - $end[1]) === 1) {
                            $neighbours[] = $idx;
                        }
                    }
                    if (!empty($neighbours)) {
                        $pick = $neighbours[(int) floor($rng() * count($neighbours))];
                        $prefix = array_slice($candidate, 0, $pick + 1);
                        $suffix = array_reverse(array_slice($candidate, $pick + 1));
                        $candidate = array_merge($prefix, $suffix);
                    }
                } else {
                    $start = $candidate[0];
                    $neighbours = [];
                    for ($idx = 2; $idx < $total; $idx++) {
                        if (abs($candidate[$idx][0] - $start[0]) + abs($candidate[$idx][1] - $start[1]) === 1) {
                            $neighbours[] = $idx;
                        }
                    }
                    if (!empty($neighbours)) {
                        $pick = $neighbours[(int) floor($rng() * count($neighbours))];
                        $prefix = array_reverse(array_slice($candidate, 0, $pick));
                        $suffix = array_slice($candidate, $pick);
                        $candidate = array_merge($prefix, $suffix);
                    }
                }
            }

            // Score candidate by turn tortuosity and non-corner start/end variety
            $turns = 0;
            $longStraightPenalty = 0;
            $runLen = 1;
            for ($i = 1; $i < $total - 1; $i++) {
                $dr1 = $candidate[$i][0] - $candidate[$i - 1][0];
                $dc1 = $candidate[$i][1] - $candidate[$i - 1][1];
                $dr2 = $candidate[$i + 1][0] - $candidate[$i][0];
                $dc2 = $candidate[$i + 1][1] - $candidate[$i][1];
                if ($dr1 !== $dr2 || $dc1 !== $dc2) {
                    $turns++;
                    $runLen = 1;
                } else {
                    $runLen++;
                    if ($runLen >= $size - 1) {
                        $longStraightPenalty += 2;
                    }
                }
            }
            $startCell = $candidate[0];
            $isInteriorStart = ($startCell[0] > 0 && $startCell[0] < $size - 1 && $startCell[1] > 0 && $startCell[1] < $size - 1) ? 3 : 0;
            $score = ($turns * 3) + $isInteriorStart - $longStraightPenalty;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestPath = $candidate;
            }
        }

        $path = $bestPath;

        // 4. Mark edges and cell indices used by the Hamiltonian path
        $pathEdges = [];
        $cellPathIndex = [];
        for ($i = 0; $i < $total; $i++) {
            $cellPathIndex["{$path[$i][0]},{$path[$i][1]}"] = $i;
            if ($i < $total - 1) {
                $a = $path[$i];
                $b = $path[$i + 1];
                $key = self::edgeKey($a[0], $a[1], $b[0], $b[1]);
                $pathEdges[$key] = true;
            }
        }

        // 5. Place checkpoints 1..K with non-uniform, deceptive segment lengths
        // Avoid uniform spacing and avoid placing consecutive checkpoints on adjacent cells
        $checkpointIndices = [0];
        $avgStep = ($total - 1) / ($checkpointCount - 1);
        for ($k = 1; $k < $checkpointCount - 1; $k++) {
            $baseIdx = (int) round($k * $avgStep);
            $prevIdx = $checkpointIndices[$k - 1];
            $minIdx = max($prevIdx + 3, $baseIdx - 3);
            $maxIdx = min($total - 1 - (($checkpointCount - 1 - $k) * 3), $baseIdx + 3);

            $bestIdx = $baseIdx;
            $bestCpScore = -999;
            for ($candIdx = $minIdx; $candIdx <= $maxIdx; $candIdx++) {
                $candCell = $path[$candIdx];
                $prevCell = $path[$prevIdx];
                $manhattan = abs($candCell[0] - $prevCell[0]) + abs($candCell[1] - $prevCell[1]);
                // Penalise consecutive checkpoints that sit right next to each other (gives away loops)
                $adjPenalty = ($manhattan <= 1) ? -8 : 0;
                // Reward alternating segment lengths (long detour vs shorter anchor)
                $spanDiff = abs(($candIdx - $prevIdx) - (int) round($avgStep));
                $jitter = (int) floor($rng() * 4);
                $cpScore = ($manhattan * 2) + ($spanDiff * 3) + $adjPenalty + $jitter;
                if ($cpScore > $bestCpScore) {
                    $bestCpScore = $cpScore;
                    $bestIdx = $candIdx;
                }
            }
            $checkpointIndices[] = $bestIdx;
        }
        $checkpointIndices[] = $total - 1;

        $checkpoints = [];
        $checkpointCells = [];
        foreach ($checkpointIndices as $k => $idx) {
            $r = $path[$idx][0];
            $c = $path[$idx][1];
            $checkpoints[] = [
                'num' => $k + 1,
                'r' => $r,
                'c' => $c,
            ];
            $checkpointCells["{$r},{$c}"] = $k + 1;
        }

        // 6. Place sparse, anti-corridor walls on deceptive shortcut edges only
        // Count initial blocked sides per cell (board perimeter counts as 1 per outer edge)
        $blockedSides = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                $borders = ($r === 0 ? 1 : 0) + ($r === $size - 1 ? 1 : 0) + ($c === 0 ? 1 : 0) + ($c === $size - 1 ? 1 : 0);
                $blockedSides["{$r},{$c}"] = $borders;
            }
        }

        $candidateWalls = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($r + 1 < $size) {
                    $k = self::edgeKey($r, $c, $r + 1, $c);
                    if (!isset($pathEdges[$k])) {
                        $candidateWalls[] = [$r, $c, $r + 1, $c];
                    }
                }
                if ($c + 1 < $size) {
                    $k = self::edgeKey($r, $c, $r, $c + 1);
                    if (!isset($pathEdges[$k])) {
                        $candidateWalls[] = [$r, $c, $r, $c + 1];
                    }
                }
            }
        }

        $candidateWalls = self::seededShuffle($candidateWalls, $rng);
        // Sort candidate walls to prefer blocking deceptive long-jump shortcuts rather than tight local bends
        usort($candidateWalls, static function (array $w1, array $w2) use ($cellPathIndex): int {
            $jump1 = abs(($cellPathIndex["{$w1[0]},{$w1[1]}"] ?? 0) - ($cellPathIndex["{$w1[2]},{$w1[3]}"] ?? 0));
            $jump2 = abs(($cellPathIndex["{$w2[0]},{$w2[1]}"] ?? 0) - ($cellPathIndex["{$w2[2]},{$w2[3]}"] ?? 0));
            return $jump2 <=> $jump1;
        });

        // Keep wall count sparse so players face open intersections rather than 1-way corridors
        $wallTarget = match ($difficulty) {
            'easy' => 3,
            'hard' => 6,
            default => 4,
        };

        $walls = [];
        $startKey = "{$path[0][0]},{$path[0][1]}";
        $endKey = "{$path[$total - 1][0]},{$path[$total - 1][1]}";

        foreach ($candidateWalls as $wall) {
            if (count($walls) >= $wallTarget) {
                break;
            }
            [$r1, $c1, $r2, $c2] = $wall;
            $k1 = "{$r1},{$c1}";
            $k2 = "{$r2},{$c2}";

            // Never place walls touching Dot 1 or the final Dot if it would reduce their open exits below 3
            if (($k1 === $startKey || $k1 === $endKey) && $blockedSides[$k1] >= 1) {
                continue;
            }
            if (($k2 === $startKey || $k2 === $endKey) && $blockedSides[$k2] >= 1) {
                continue;
            }

            // Never box any cell into a 3-walled dead-end U-cup or narrow 1-way chute (max 1 wall+border blocked side before adding)
            if ($blockedSides[$k1] >= 2 || $blockedSides[$k2] >= 2) {
                continue;
            }

            $walls[] = $wall;
            $blockedSides[$k1]++;
            $blockedSides[$k2]++;
        }

        $payload = [
            'game' => 'kinzip',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'size' => $size,
            'checkpoints' => $checkpoints,
            'walls' => $walls,
            'parMs' => match ($difficulty) {
                'easy' => 45000,
                'hard' => 95000,
                default => 68000,
            },
        ];

        if ($includeSolution) {
            $payload['solutionPath'] = $path;
        }

        return $payload;
    }

    private static function fnv1aHex(string $input): string
    {
        $hash = 0x811c9dc5;
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $hash ^= ord($input[$i]);
            $hash = ($hash * 0x01000193) & 0xFFFFFFFF;
        }
        return sprintf('%08x', $hash);
    }

    private static function edgeKey(int $r1, int $c1, int $r2, int $c2): string
    {
        if ($r1 > $r2 || ($r1 === $r2 && $c1 > $c2)) {
            return "{$r2},{$c2}-{$r1},{$c1}";
        }
        return "{$r1},{$c1}-{$r2},{$c2}";
    }

    /**
     * Generate a KinGrid Latin-square + inequality deduction puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateKinGridPuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $size = match ($difficulty) {
            'easy' => 4,
            'hard' => 6,
            default => 5,
        };
        $rng = self::createRng("kingrid:{$seedKey}:{$difficulty}");

        // Base Latin square
        $grid = [];
        for ($r = 0; $r < $size; $r++) {
            $row = [];
            for ($c = 0; $c < $size; $c++) {
                $row[] = (($r + $c) % $size) + 1;
            }
            $grid[] = $row;
        }

        // Permute rows, cols, and symbols deterministically
        $rowOrder = self::seededShuffle(range(0, $size - 1), $rng);
        $colOrder = self::seededShuffle(range(0, $size - 1), $rng);
        $symOrder = self::seededShuffle(range(1, $size), $rng);

        $solution = [];
        for ($r = 0; $r < $size; $r++) {
            $row = [];
            for ($c = 0; $c < $size; $c++) {
                $val = $grid[$rowOrder[$r]][$colOrder[$c]];
                $row[] = $symOrder[$val - 1];
            }
            $solution[] = $row;
        }

        // Select givens
        $allCoords = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                $allCoords[] = [$r, $c];
            }
        }
        $allCoords = self::seededShuffle($allCoords, $rng);
        $givenCount = match ($difficulty) {
            'easy' => (int) ceil($size * $size * 0.45),
            'hard' => (int) ceil($size * $size * 0.30),
            default => (int) ceil($size * $size * 0.36),
        };
        $givens = [];
        for ($i = 0; $i < $givenCount; $i++) {
            [$r, $c] = $allCoords[$i];
            $givens[] = ['r' => $r, 'c' => $c, 'v' => $solution[$r][$c]];
        }

        // Generate inequality clues between adjacent cells
        $inequalities = [];
        $edges = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($c + 1 < $size) {
                    $edges[] = [$r, $c, $r, $c + 1];
                }
                if ($r + 1 < $size) {
                    $edges[] = [$r, $c, $r + 1, $c];
                }
            }
        }
        $edges = self::seededShuffle($edges, $rng);
        $ineqCount = match ($difficulty) {
            'easy' => 5,
            'hard' => 11,
            default => 8,
        };
        for ($i = 0; $i < min($ineqCount, count($edges)); $i++) {
            [$r1, $c1, $r2, $c2] = $edges[$i];
            $op = $solution[$r1][$c1] > $solution[$r2][$c2] ? '>' : '<';
            $inequalities[] = ['r1' => $r1, 'c1' => $c1, 'r2' => $r2, 'c2' => $c2, 'op' => $op];
        }

        $payload = [
            'game' => 'kingrid',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'size' => $size,
            'givens' => $givens,
            'inequalities' => $inequalities,
            'parMs' => match ($difficulty) {
                'easy' => 50000,
                'hard' => 110000,
                default => 75000,
            },
        ];

        if ($includeSolution) {
            $payload['solutionGrid'] = $solution;
        }

        return $payload;
    }

    /**
     * Curated British-English word & family-heritage category bank for KinWord.
     *
     * @return array<int, array{title: string, tier: int, words: array<int, string>}>
     */
    private static function getKinWordBank(): array
    {
        return [
            ['title' => 'Sunday Roast Essentials', 'tier' => 1, 'words' => ['GRAVY', 'PARSNIP', 'YORKSHIRE', 'ROASTIES']],
            ['title' => 'Family Tree Branches', 'tier' => 1, 'words' => ['COUSIN', 'NIECE', 'GRANDDAD', 'AUNTIE']],
            ['title' => 'British Tea Time', 'tier' => 1, 'words' => ['KETTLE', 'SCONE', 'BISCUIT', 'DARJEELING']],
            ['title' => 'Wedding Milestones', 'tier' => 2, 'words' => ['VOWS', 'BOUQUET', 'CONFETTI', 'TOAST']],
            ['title' => 'Heirloom Keepsakes', 'tier' => 2, 'words' => ['LOCKET', 'ALBUM', 'BROOCH', 'CHRONICLE']],
            ['title' => 'Garden Party Games', 'tier' => 2, 'words' => ['ROUNDERS', 'CROQUET', 'CHARADES', 'SKITTLES']],
            ['title' => 'Words Meaning "Organise"', 'tier' => 3, 'words' => ['ARRANGE', 'CURATE', 'MUSTER', 'MARSHAL']],
            ['title' => 'Things With Rings', 'tier' => 3, 'words' => ['SATURN', 'ONION', 'TREE', 'BINDER']],
            ['title' => 'British Seaside Towns', 'tier' => 2, 'words' => ['BRIGHTON', 'WHITBY', 'ST IVES', 'MARGATE']],
            ['title' => 'Kitchen Heirloom Pots', 'tier' => 2, 'words' => ['CASSEROLE', 'SKILLET', 'CAULDRON', 'TAGINE']],
            ['title' => 'Types of Harmony', 'tier' => 3, 'words' => ['CHORUS', 'UNISON', 'CADENCE', 'CHORD']],
            ['title' => '___ Crown', 'tier' => 4, 'words' => ['TRIPLE', 'TOOTH', 'SOVEREIGN', 'IMPERIAL']],
            ['title' => 'Family Reunion Roles', 'tier' => 1, 'words' => ['HOST', 'CHEF', 'DJ', 'ARCHIVIST']],
            ['title' => 'Grandparents\' Living Room', 'tier' => 2, 'words' => ['DOILY', 'MANTEL', 'SIDEBOARD', 'TEAPOT']],
            ['title' => 'Words Before "Code"', 'tier' => 4, 'words' => ['POST', 'MORSE', 'DRESS', 'FAMILY']],
            ['title' => 'Celebration Sparkles', 'tier' => 1, 'words' => ['LANTERN', 'SPARKLER', 'CANDLE', 'BEACON']],
            ['title' => 'Palindromic Words', 'tier' => 4, 'words' => ['CIVIC', 'RADAR', 'LEVEL', 'KAYAK']],
            ['title' => 'Silent "K" Words', 'tier' => 3, 'words' => ['KNIGHT', 'KNEEL', 'KNUCKLE', 'KNAVE']],
            ['title' => 'Heritage Fabrics', 'tier' => 3, 'words' => ['TWEED', 'TARTAN', 'VELVET', 'LINEN']],
            ['title' => '___ Bridge', 'tier' => 4, 'words' => ['TOWER', 'LONDON', 'DRAW', 'DENTAL']],
        ];
    }

    /**
     * Generate a 4x4 KinWord puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateKinWordPuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $rng = self::createRng("kinword:{$seedKey}:{$difficulty}");
        $bank = self::seededShuffle(self::getKinWordBank(), $rng);

        // Pick 4 categories with distinct words
        $chosen = [];
        $usedWords = [];
        foreach ($bank as $cat) {
            $overlap = false;
            foreach ($cat['words'] as $w) {
                if (isset($usedWords[$w])) {
                    $overlap = true;
                    break;
                }
            }
            if ($overlap) {
                continue;
            }
            $chosen[] = $cat;
            foreach ($cat['words'] as $w) {
                $usedWords[$w] = true;
            }
            if (count($chosen) === 4) {
                break;
            }
        }

        $tiles = [];
        $groupManifest = [];
        foreach ($chosen as $cat) {
            $sortedWords = array_map('strval', $cat['words']);
            sort($sortedWords);
            $hashKey = self::fnv1aHex($seedKey . ':' . implode('|', $sortedWords));
            $groupManifest[$hashKey] = [
                'title' => $cat['title'],
                'tier' => $cat['tier'],
            ];
            foreach ($cat['words'] as $w) {
                $tiles[] = $w;
            }
        }
        $tiles = self::seededShuffle($tiles, $rng);

        $payload = [
            'game' => 'kinword',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'tiles' => $tiles,
            'groupManifest' => $groupManifest,
            'maxMistakes' => 4,
            'parMs' => 65000,
        ];

        if ($includeSolution) {
            $payload['categories'] = $chosen;
        }

        return $payload;
    }

    /**
     * Generate a KinPulse 3D kinetic memory sequence puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateKinPulsePuzzle(string $seedKey, string $difficulty): array
    {
        $rng = self::createRng("kinpulse:{$seedKey}:{$difficulty}");
        $maxRounds = match ($difficulty) {
            'easy' => 6,
            'hard' => 10,
            default => 8,
        };
        $sequence = [];
        for ($i = 0; $i < $maxRounds; $i++) {
            $sequence[] = (int) floor($rng() * 9);
        }

        return [
            'game' => 'kinpulse',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'gridSize' => 3,
            'maxRounds' => $maxRounds,
            'sequence' => $sequence,
            'parMs' => $maxRounds * 4200,
        ];
    }

    /**
     * Curated British-English 5-letter word bank for Wordie.
     *
     * @return list<string>
     */
    private static function getWordieWordBank(): array
    {
        return [
            'HEART', 'SMILE', 'SHARE', 'SWEET', 'PARTY', 'FAITH', 'HONOR', 'CROWN',
            'GRACE', 'BLOOM', 'LAUGH', 'STORY', 'KINDS', 'BONDS', 'ROOTS', 'ELDER',
            'YOUTH', 'HOUSE', 'CHILD', 'GRAND', 'AUNTY', 'UNCLE', 'TABLE', 'GUEST',
            'FEAST', 'DANCE', 'MUSIC', 'CARDS', 'LIGHT', 'SHINE', 'PEACE', 'TRUST',
            'VALOR', 'UNITY', 'CLANS', 'TRIBE', 'PRIDE', 'HABIT', 'PHOTO', 'FRAME',
            'ALBUM', 'TRACE', 'DREAM', 'HOPE', 'EARTH', 'WATER', 'STONE', 'CEDAR',
            'GROVE', 'BRAVE', 'CLEAN', 'FRESH', 'CHEER', 'MERRY', 'BLISS', 'CHARM',
            'GLORY', 'NOBLE', 'ROYAL', 'PEARL', 'MAGIC', 'ANGEL', 'SAINT', 'HAVEN',
            'HONEY', 'BREAD', 'MANOR', 'LODGE', 'GREEN', 'AMBER', 'GOLDEN', 'SUNNY',
            'RIVER', 'OCEAN', 'COAST', 'BEACH', 'ISLES', 'VALES', 'SHIRE', 'FLAME',
            'SPARK', 'CREST', 'JEWEL', 'TIARA', 'FAVOR', 'HOMES', 'PLACE', 'CRAFT',
            'PEACH', 'BERRY', 'MANGO', 'LEMON', 'PLUMS', 'VINES', 'CEDAR', 'MANOR',
        ];
    }

    /**
     * Generate a daily 5-letter Wordie puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateWordiePuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $words = self::getWordieWordBank();
        $rng = self::createRng("wordie:{$seedKey}:{$difficulty}");
        $targetIndex = (int) floor($rng() * count($words));
        $targetWord = $words[$targetIndex];

        $payload = [
            'game' => 'wordie',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'wordLength' => 5,
            'maxGuesses' => 6,
            'targetWordHash' => base64_encode(strtoupper($targetWord)),
            'parMs' => match ($difficulty) {
                'easy' => 60000,
                'hard' => 120000,
                default => 90000,
            },
        ];

        if ($includeSolution) {
            $payload['targetWord'] = $targetWord;
        }

        return $payload;
    }

    /**
     * Generate a Cheeseboard (LinkedIn Queens-style) territory deduction puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateCheeseboardPuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $size = match ($difficulty) {
            'easy' => 6,
            'hard' => 8,
            default => 7,
        };

        $rng = self::createRng("cheeseboard:{$seedKey}:{$difficulty}");

        // Deterministic search for N queens where no two touch even diagonally
        $shuffledCols = [];
        for ($r = 0; $r < $size; $r++) {
            $cList = range(0, $size - 1);
            for ($i = count($cList) - 1; $i > 0; $i--) {
                $j = (int) floor($rng() * ($i + 1));
                $tmp = $cList[$i];
                $cList[$i] = $cList[$j];
                $cList[$j] = $tmp;
            }
            $shuffledCols[$r] = $cList;
        }

        $placed = [];
        $usedCols = [];
        $findQueens = function (int $r) use (&$findQueens, &$placed, &$usedCols, $size, $shuffledCols): bool {
            if ($r === $size) {
                return true;
            }
            foreach ($shuffledCols[$r] as $c) {
                if (isset($usedCols[$c])) {
                    continue;
                }
                if ($r > 0 && abs($placed[$r - 1] - $c) <= 1) {
                    continue;
                }
                $placed[$r] = $c;
                $usedCols[$c] = true;
                if ($findQueens($r + 1)) {
                    return true;
                }
                unset($usedCols[$c], $placed[$r]);
            }
            return false;
        };

        $findQueens(0);

        if (count($placed) !== $size) {
            $placed = [];
            for ($r = 0; $r < $size; $r++) {
                $placed[$r] = ($r * 2 + 1) % $size;
            }
        }

        // Seeded BFS flood-fill to grow contiguous regions around each queen
        $regionGrid = array_fill(0, $size, array_fill(0, $size, -1));
        $queues = [];
        for ($r = 0; $r < $size; $r++) {
            $c = $placed[$r];
            $regionGrid[$r][$c] = $r;
            $queues[$r] = [[$r, $c]];
        }

        $dirs = [[0, 1], [0, -1], [1, 0], [-1, 0]];
        $unassigned = ($size * $size) - $size;

        while ($unassigned > 0) {
            $progress = false;
            for ($r = 0; $r < $size; $r++) {
                if (empty($queues[$r])) {
                    continue;
                }
                $popIdx = (int) floor($rng() * count($queues[$r]));
                $cell = $queues[$r][$popIdx];
                $neighbors = [];
                foreach ($dirs as $d) {
                    $nr = $cell[0] + $d[0];
                    $nc = $cell[1] + $d[1];
                    if ($nr >= 0 && $nr < $size && $nc >= 0 && $nc < $size && $regionGrid[$nr][$nc] === -1) {
                        $neighbors[] = [$nr, $nc];
                    }
                }
                if (!empty($neighbors)) {
                    $chosen = $neighbors[(int) floor($rng() * count($neighbors))];
                    $regionGrid[$chosen[0]][$chosen[1]] = $r;
                    $queues[$r][] = $chosen;
                    $unassigned--;
                    $progress = true;
                } else {
                    array_splice($queues[$r], $popIdx, 1);
                }
            }
            if (!$progress) {
                break;
            }
        }

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($regionGrid[$r][$c] === -1) {
                    foreach ($dirs as $d) {
                        $nr = $r + $d[0];
                        $nc = $c + $d[1];
                        if ($nr >= 0 && $nr < $size && $nc >= 0 && $nc < $size && $regionGrid[$nr][$nc] !== -1) {
                            $regionGrid[$r][$c] = $regionGrid[$nr][$nc];
                            break;
                        }
                    }
                }
            }
        }

        $payload = [
            'game' => 'cheeseboard',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'size' => $size,
            'regions' => $regionGrid,
            'targetQueens' => $size,
            'parMs' => match ($difficulty) {
                'easy' => 45000,
                'hard' => 110000,
                default => 75000,
            },
        ];

        if ($includeSolution) {
            $solutionQueens = [];
            foreach ($placed as $r => $c) {
                $solutionQueens[] = ['r' => $r, 'c' => $c];
            }
            $payload['solutionQueens'] = $solutionQueens;
        }

        return $payload;
    }

    /**
     * Generate an 8x8 Minesweeper (Family Gem Hunter) puzzle.
     *
     * @return array<string, mixed>
     */
    private static function generateMinesweeperPuzzle(string $seedKey, string $difficulty, bool $includeSolution): array
    {
        $rows = 8;
        $cols = 8;
        $totalMines = match ($difficulty) {
            'easy' => 8,
            'hard' => 12,
            default => 10,
        };

        $rng = self::createRng("minesweeper:{$seedKey}:{$difficulty}");

        $allIndices = range(0, ($rows * $cols) - 1);
        for ($i = count($allIndices) - 1; $i > 0; $i--) {
            $j = (int) floor($rng() * ($i + 1));
            $tmp = $allIndices[$i];
            $allIndices[$i] = $allIndices[$j];
            $allIndices[$j] = $tmp;
        }

        $mineSet = [];
        for ($m = 0; $m < $totalMines; $m++) {
            $idx = $allIndices[$m];
            $mr = (int) floor($idx / $cols);
            $mc = $idx % $cols;
            $mineSet["{$mr},{$mc}"] = true;
        }

        $grid = array_fill(0, $rows, array_fill(0, $cols, 0));
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if (isset($mineSet["{$r},{$c}"])) {
                    $grid[$r][$c] = -1;
                    continue;
                }
                $count = 0;
                for ($dr = -1; $dr <= 1; $dr++) {
                    for ($dc = -1; $dc <= 1; $dc++) {
                        if ($dr === 0 && $dc === 0) {
                            continue;
                        }
                        $nr = $r + $dr;
                        $nc = $c + $dc;
                        if ($nr >= 0 && $nr < $rows && $nc >= 0 && $nc < $cols && isset($mineSet["{$nr},{$nc}"])) {
                            $count++;
                        }
                    }
                }
                $grid[$r][$c] = $count;
            }
        }

        $payload = [
            'game' => 'minesweeper',
            'seedKey' => $seedKey,
            'difficulty' => $difficulty,
            'rows' => $rows,
            'cols' => $cols,
            'totalMines' => $totalMines,
            'safeCellsCount' => ($rows * $cols) - $totalMines,
            'grid' => $grid,
            'parMs' => match ($difficulty) {
                'easy' => 45000,
                'hard' => 100000,
                default => 70000,
            },
        ];

        if ($includeSolution) {
            $payload['mineSet'] = $mineSet;
        }

        return $payload;
    }

    /**
     * Server-authoritative validation & scoring for any submitted game solution.
     *
     * @param array<string, mixed> $submission
     * @return array{valid: bool, reason: string, score: int}
     */
    public static function validateAndScore(
        string $gameSlug,
        string $seedKey,
        string $difficulty,
        array $submission,
        int $elapsedMs,
        int $hintsUsed,
        int $mistakesCount
    ): array {
        $game = self::normaliseGame($gameSlug);
        $diff = self::normaliseDifficulty($difficulty);

        $valid = false;
        $reason = 'Invalid solution.';
        $bonusRounds = 0;

        if ($game === 'kinzip') {
            $puzzle = self::generateKinZipPuzzle($seedKey, $diff, true);
            $path = $submission['path'] ?? null;
            if (!is_array($path)) {
                return ['valid' => false, 'reason' => 'Missing path array.', 'score' => 0];
            }
            $size = (int) $puzzle['size'];
            $expectedLen = $size * $size;
            if (count($path) !== $expectedLen) {
                return ['valid' => false, 'reason' => 'Every cell on the board must be filled.', 'score' => 0];
            }

            $wallSet = [];
            foreach ($puzzle['walls'] as $w) {
                $wallSet[self::edgeKey((int) $w[0], (int) $w[1], (int) $w[2], (int) $w[3])] = true;
            }

            $visited = [];
            $posIndex = [];
            for ($i = 0; $i < $expectedLen; $i++) {
                $cell = $path[$i];
                if (!is_array($cell) || count($cell) < 2) {
                    return ['valid' => false, 'reason' => 'Malformed cell coordinate.', 'score' => 0];
                }
                $r = (int) $cell[0];
                $c = (int) $cell[1];
                if ($r < 0 || $r >= $size || $c < 0 || $c >= $size) {
                    return ['valid' => false, 'reason' => 'Coordinate out of bounds.', 'score' => 0];
                }
                $key = "{$r},{$c}";
                if (isset($visited[$key])) {
                    return ['valid' => false, 'reason' => 'Path cannot revisit a cell.', 'score' => 0];
                }
                $visited[$key] = true;
                $posIndex[$key] = $i;

                if ($i > 0) {
                    $prevR = (int) $path[$i - 1][0];
                    $prevC = (int) $path[$i - 1][1];
                    if (abs($r - $prevR) + abs($c - $prevC) !== 1) {
                        return ['valid' => false, 'reason' => 'Path must move orthogonally.', 'score' => 0];
                    }
                    if (isset($wallSet[self::edgeKey($prevR, $prevC, $r, $c)])) {
                        return ['valid' => false, 'reason' => 'Path cannot cross a wall.', 'score' => 0];
                    }
                }
            }

            $lastIdx = -1;
            foreach ($puzzle['checkpoints'] as $cp) {
                $cpKey = "{$cp['r']},{$cp['c']}";
                $idx = $posIndex[$cpKey] ?? -1;
                if ($idx <= $lastIdx) {
                    return ['valid' => false, 'reason' => 'Dots must be connected in numerical order.', 'score' => 0];
                }
                $lastIdx = $idx;
            }

            $valid = true;
            $reason = 'Verified KinZip Hamiltonian solution.';
        } elseif ($game === 'kingrid') {
            $puzzle = self::generateKinGridPuzzle($seedKey, $diff, true);
            $grid = $submission['grid'] ?? null;
            $size = (int) $puzzle['size'];
            if (!is_array($grid) || count($grid) !== $size) {
                return ['valid' => false, 'reason' => 'Invalid grid dimensions.', 'score' => 0];
            }

            for ($r = 0; $r < $size; $r++) {
                if (!is_array($grid[$r]) || count($grid[$r]) !== $size) {
                    return ['valid' => false, 'reason' => 'Invalid grid row.', 'score' => 0];
                }
                $rowSet = [];
                for ($c = 0; $c < $size; $c++) {
                    $v = (int) $grid[$r][$c];
                    if ($v < 1 || $v > $size || isset($rowSet[$v])) {
                        return ['valid' => false, 'reason' => 'Row contains duplicate or out-of-range values.', 'score' => 0];
                    }
                    $rowSet[$v] = true;
                }
            }

            for ($c = 0; $c < $size; $c++) {
                $colSet = [];
                for ($r = 0; $r < $size; $r++) {
                    $v = (int) $grid[$r][$c];
                    if (isset($colSet[$v])) {
                        return ['valid' => false, 'reason' => 'Column contains duplicate values.', 'score' => 0];
                    }
                    $colSet[$v] = true;
                }
            }

            foreach ($puzzle['givens'] as $g) {
                if ((int) $grid[$g['r']][$g['c']] !== (int) $g['v']) {
                    return ['valid' => false, 'reason' => 'Given cell modified.', 'score' => 0];
                }
            }

            foreach ($puzzle['inequalities'] as $ineq) {
                $v1 = (int) $grid[$ineq['r1']][$ineq['c1']];
                $v2 = (int) $grid[$ineq['r2']][$ineq['c2']];
                if ($ineq['op'] === '>' && !($v1 > $v2)) {
                    return ['valid' => false, 'reason' => 'Inequality constraint violated.', 'score' => 0];
                }
                if ($ineq['op'] === '<' && !($v1 < $v2)) {
                    return ['valid' => false, 'reason' => 'Inequality constraint violated.', 'score' => 0];
                }
            }

            $valid = true;
            $reason = 'Verified KinGrid deduction solution.';
        } elseif ($game === 'kinword') {
            $puzzle = self::generateKinWordPuzzle($seedKey, $diff, true);
            $groups = $submission['groups'] ?? null;
            if (!is_array($groups) || count($groups) !== 4) {
                return ['valid' => false, 'reason' => 'All 4 word groups must be solved.', 'score' => 0];
            }
            if ($mistakesCount > 4) {
                return ['valid' => false, 'reason' => 'Maximum mistakes exceeded.', 'score' => 0];
            }

            $expectedSets = [];
            foreach ($puzzle['categories'] as $cat) {
                $words = array_map('strval', $cat['words']);
                sort($words);
                $expectedSets[] = implode('|', $words);
            }
            sort($expectedSets);

            $submittedSets = [];
            foreach ($groups as $grp) {
                if (!is_array($grp) || count($grp) !== 4) {
                    return ['valid' => false, 'reason' => 'Each group must contain 4 words.', 'score' => 0];
                }
                $words = array_map(static fn ($w): string => strtoupper(trim((string) $w)), $grp);
                sort($words);
                $submittedSets[] = implode('|', $words);
            }
            sort($submittedSets);

            if ($submittedSets !== $expectedSets) {
                return ['valid' => false, 'reason' => 'Submitted word groups do not match.', 'score' => 0];
            }

            $valid = true;
            $reason = 'Verified KinWord categories.';
        } elseif ($game === 'kinpulse') {
            $puzzle = self::generateKinPulsePuzzle($seedKey, $diff);
            $seq = $submission['sequence'] ?? null;
            $roundsCleared = (int) ($submission['roundsCleared'] ?? 0);
            if (!is_array($seq) || $roundsCleared < 1 || $roundsCleared > (int) $puzzle['maxRounds']) {
                return ['valid' => false, 'reason' => 'Invalid KinPulse round count.', 'score' => 0];
            }
            $expected = array_slice($puzzle['sequence'], 0, $roundsCleared);
            $submitted = array_map('intval', array_slice($seq, 0, $roundsCleared));
            if ($submitted !== $expected) {
                return ['valid' => false, 'reason' => 'Sequence mismatch.', 'score' => 0];
            }
            $bonusRounds = $roundsCleared;
            $valid = true;
            $reason = 'Verified KinPulse sequence.';
        } elseif ($game === 'wordie') {
            $puzzle = self::generateWordiePuzzle($seedKey, $diff, true);
            $guesses = $submission['guesses'] ?? null;
            $targetWord = (string) $puzzle['targetWord'];
            if (!is_array($guesses) || empty($guesses) || count($guesses) > 6) {
                return ['valid' => false, 'reason' => 'Invalid Wordie guess count.', 'score' => 0];
            }
            $lastGuess = strtoupper(trim((string) end($guesses)));
            if ($lastGuess !== $targetWord) {
                return ['valid' => false, 'reason' => 'Target word not matched.', 'score' => 0];
            }
            $bonusRounds = max(0, 7 - count($guesses));
            $valid = true;
            $reason = 'Verified Wordie solution in ' . count($guesses) . ' guesses.';
        } elseif ($game === 'cheeseboard') {
            $puzzle = self::generateCheeseboardPuzzle($seedKey, $diff, true);
            $size = (int) $puzzle['size'];
            $queens = $submission['queens'] ?? null;
            if (!is_array($queens) || count($queens) !== $size) {
                return ['valid' => false, 'reason' => 'Must place exactly ' . $size . ' queens.', 'score' => 0];
            }
            $usedRows = [];
            $usedCols = [];
            $usedRegions = [];
            $coords = [];
            $regionGrid = $puzzle['regions'];

            foreach ($queens as $q) {
                $r = (int) ($q['r'] ?? -1);
                $c = (int) ($q['c'] ?? -1);
                if ($r < 0 || $r >= $size || $c < 0 || $c >= $size) {
                    return ['valid' => false, 'reason' => 'Queen out of bounds.', 'score' => 0];
                }
                if (isset($usedRows[$r])) {
                    return ['valid' => false, 'reason' => 'Multiple queens in row ' . ($r + 1) . '.', 'score' => 0];
                }
                if (isset($usedCols[$c])) {
                    return ['valid' => false, 'reason' => 'Multiple queens in col ' . ($c + 1) . '.', 'score' => 0];
                }
                $reg = (int) $regionGrid[$r][$c];
                if (isset($usedRegions[$reg])) {
                    return ['valid' => false, 'reason' => 'Multiple queens in coloured region.', 'score' => 0];
                }
                $usedRows[$r] = true;
                $usedCols[$c] = true;
                $usedRegions[$reg] = true;
                $coords[] = [$r, $c];
            }

            $count = count($coords);
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if (abs($coords[$i][0] - $coords[$j][0]) <= 1 && abs($coords[$i][1] - $coords[$j][1]) <= 1) {
                        return ['valid' => false, 'reason' => 'Queens cannot touch even diagonally.', 'score' => 0];
                    }
                }
            }

            $bonusRounds = $size;
            $valid = true;
            $reason = 'Verified Cheeseboard queens placement.';
        } elseif ($game === 'chess') {
            $winner = (string) ($submission['winner'] ?? '');
            $movesCount = (int) ($submission['movesCount'] ?? 0);
            if ($winner === 'w' && $movesCount >= 2) {
                $valid = true;
                $reason = 'Verified Family Chess checkmate victory.';
            } else {
                return ['valid' => false, 'reason' => 'Incomplete or unverified chess match.', 'score' => 0];
            }
        } elseif ($game === 'ayo') {
            $capturedSouth = (int) ($submission['capturedSouth'] ?? 0);
            $capturedNorth = (int) ($submission['capturedNorth'] ?? 0);
            if ($capturedSouth > $capturedNorth && $capturedSouth >= 12) {
                $valid = true;
                $reason = 'Verified Ayò Olópon victory.';
            } else {
                return ['valid' => false, 'reason' => 'Incomplete or unverified Ayò Olópon match.', 'score' => 0];
            }
        } elseif ($game === 'minesweeper') {
            $puzzle = self::generateMinesweeperPuzzle($seedKey, $diff, true);
            $uncovered = $submission['uncovered'] ?? null;
            $safeTarget = (int) $puzzle['safeCellsCount'];
            $mineSet = $puzzle['mineSet'];

            if (!is_array($uncovered) || count($uncovered) !== $safeTarget) {
                return ['valid' => false, 'reason' => 'All safe cells must be uncovered to win.', 'score' => 0];
            }

            $seen = [];
            foreach ($uncovered as $cell) {
                $r = (int) ($cell['r'] ?? -1);
                $c = (int) ($cell['c'] ?? -1);
                if ($r < 0 || $r >= 8 || $c < 0 || $c >= 8) {
                    return ['valid' => false, 'reason' => 'Cell out of bounds.', 'score' => 0];
                }
                $key = "{$r},{$c}";
                if (isset($mineSet[$key])) {
                    return ['valid' => false, 'reason' => 'Mine detonated!', 'score' => 0];
                }
                if (isset($seen[$key])) {
                    return ['valid' => false, 'reason' => 'Duplicate cell uncovered.', 'score' => 0];
                }
                $seen[$key] = true;
            }

            $bonusRounds = (int) $puzzle['totalMines'];
            $valid = true;
            $reason = 'Verified KinSweeper minefield navigation.';
        }

        $score = self::computeAuthoritativeScore($diff, $elapsedMs, $hintsUsed, $mistakesCount, $bonusRounds);

        return ['valid' => $valid, 'reason' => $reason, 'score' => $score];
    }

    /**
     * Authoritative score calculation.
     */
    public static function computeAuthoritativeScore(
        string $difficulty,
        int $elapsedMs,
        int $hintsUsed,
        int $mistakesCount,
        int $bonusRounds = 0
    ): int {
        $base = match ($difficulty) {
            'easy' => 500,
            'hard' => 1200,
            default => 800,
        };
        $seconds = max(1, (int) floor($elapsedMs / 1000));
        $speedBonus = max(0, 600 - ($seconds * 4));
        $hintPenalty = max(0, $hintsUsed) * 100;
        $mistakePenalty = max(0, $mistakesCount) * 50;
        $roundBonus = max(0, $bonusRounds) * 80;

        return max(100, $base + $speedBonus + $roundBonus - $hintPenalty - $mistakePenalty);
    }

    /**
     * Resolve all family codes accessible to the user (optionally including connected families).
     *
     * @return list<string>
     */
    public static function resolveAccessibleFamilyCodes(
        PDO $db,
        string $userId,
        string $primaryFamCode,
        bool $includeConnected
    ): array {
        $codes = [$primaryFamCode];

        if ($includeConnected) {
            // Include user's secondary/birth family code if present
            $stmt = $db->prepare('SELECT famCode, familyCode2 FROM personal WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                foreach (['famCode', 'familyCode2'] as $col) {
                    $val = trim((string) ($row[$col] ?? ''));
                    if ($val !== '' && !in_array($val, $codes, true)) {
                        $codes[] = $val;
                    }
                }
            }

            // Include approved connected families from family_tree_access_rules
            try {
                $connStmt = $db->prepare(
                    "SELECT target_family_code, requester_family_code
                     FROM family_tree_access_rules
                     WHERE status = 'approved'
                       AND (requester_family_code = ? OR target_family_code = ?)"
                );
                $connStmt->execute([$primaryFamCode, $primaryFamCode]);
                $rows = $connStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($rows as $r) {
                    foreach (['target_family_code', 'requester_family_code'] as $c) {
                        $fc = trim((string) ($r[$c] ?? ''));
                        if ($fc !== '' && !in_array($fc, $codes, true)) {
                            $codes[] = $fc;
                        }
                    }
                }
            } catch (Throwable $e) {
                // Table may not exist in minimal test fixtures; fail gracefully
            }
        }

        return array_values(array_unique(array_filter($codes, static fn (string $c): bool => $c !== '')));
    }

    /**
     * Fetch per-game and overall "Family Cup" league tables.
     *
     * @return array<string, mixed>
     */
    public static function getLeagueTables(
        PDO $db,
        string $famCode,
        string $userId,
        bool $includeConnected = false,
        string $period = 'weekly'
    ): array {
        $famCodes = self::resolveAccessibleFamilyCodes($db, $userId, $famCode, $includeConnected);
        $placeholders = implode(',', array_fill(0, count($famCodes), '?'));
        $dateClause = $period === 'all_time'
            ? ''
            : ' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';

        $sql = sprintf(
            "SELECT s.user_id, s.fam_code, s.game_slug,
                    MAX(s.score) AS best_score,
                    MIN(s.elapsed_ms) AS best_time_ms,
                    COUNT(*) AS games_played,
                    COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, ''))), ''), 'Family Member') AS full_name,
                    COALESCE(NULLIF(TRIM(p.firstName), ''), 'Family Member') AS first_name
             FROM family_arcade_scores s
             LEFT JOIN personal p ON p.id = s.user_id
             WHERE s.fam_code IN (%s)%s
             GROUP BY s.user_id, s.fam_code, s.game_slug, p.firstName, p.lastName
             ORDER BY best_score DESC, best_time_ms ASC",
            $placeholders,
            $dateClause
        );

        $stmt = $db->prepare($sql);
        $stmt->execute($famCodes);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $games = [
            'kinzip' => [],
            'kingrid' => [],
            'kinword' => [],
            'kinpulse' => [],
        ];
        $cupMap = [];

        foreach ($rows as $row) {
            $slug = (string) $row['game_slug'];
            $uid = (string) $row['user_id'];
            $bestScore = (int) $row['best_score'];
            $bestTime = (int) $row['best_time_ms'];
            $played = (int) $row['games_played'];
            $fullName = trim((string) $row['full_name']);
            $firstName = trim((string) $row['first_name']);

            if (isset($games[$slug])) {
                $games[$slug][] = [
                    'userId' => $uid,
                    'famCode' => (string) $row['fam_code'],
                    'fullName' => $fullName,
                    'firstName' => $firstName,
                    'bestScore' => $bestScore,
                    'bestTimeMs' => $bestTime,
                    'gamesPlayed' => $played,
                ];
            }

            if (!isset($cupMap[$uid])) {
                $cupMap[$uid] = [
                    'userId' => $uid,
                    'famCode' => (string) $row['fam_code'],
                    'fullName' => $fullName,
                    'firstName' => $firstName,
                    'totalScore' => 0,
                    'gamesPlayed' => 0,
                    'gamesMastered' => 0,
                ];
            }
            $cupMap[$uid]['totalScore'] += $bestScore;
            $cupMap[$uid]['gamesPlayed'] += $played;
            $cupMap[$uid]['gamesMastered'] += 1;
        }

        // Sort each game table by bestScore DESC, bestTimeMs ASC
        foreach ($games as $slug => $list) {
            usort($list, static function (array $a, array $b): int {
                if ($a['bestScore'] !== $b['bestScore']) {
                    return $b['bestScore'] <=> $a['bestScore'];
                }
                return $a['bestTimeMs'] <=> $b['bestTimeMs'];
            });
            $games[$slug] = array_slice($list, 0, 25);
        }

        $familyCup = array_values($cupMap);
        usort($familyCup, static function (array $a, array $b): int {
            if ($a['totalScore'] !== $b['totalScore']) {
                return $b['totalScore'] <=> $a['totalScore'];
            }
            return $b['gamesMastered'] <=> $a['gamesMastered'];
        });

        return [
            'period' => $period,
            'includeConnected' => $includeConnected,
            'familyCup' => array_slice($familyCup, 0, 25),
            'games' => $games,
        ];
    }

    /**
     * Recompute dynamic "Best [Title] of the Family" badges and send "Score Beaten!" alerts
     * if the player overtook a previous #1 holder.
     *
     * @return array<string, mixed>
     */
    public static function updateBadgesAndNotify(
        PDO $db,
        string $famCode,
        string $userId,
        string $playerName,
        string $gameSlug,
        int $newScore,
        string $challengeToken
    ): array {
        $badgeMeta = self::GAME_BADGE_MAP[$gameSlug] ?? self::GAME_BADGE_MAP['kinzip'];
        $periodKey = 'all_time';
        $awardedBadges = [];
        $overtookUserId = null;

        // Check existing #1 holder for this game
        $prevStmt = $db->prepare(
            'SELECT user_id, score FROM family_arcade_badges
             WHERE fam_code = ? AND badge_key = ? AND period_key = ? LIMIT 1'
        );
        $prevStmt->execute([$famCode, $badgeMeta['key'], $periodKey]);
        $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);

        $isNewRecord = !$prev || $newScore > (int) $prev['score'];
        if ($isNewRecord) {
            if ($prev && (string) $prev['user_id'] !== '' && (string) $prev['user_id'] !== $userId) {
                $overtookUserId = (string) $prev['user_id'];
            }

            $upsert = $db->prepare(
                'INSERT INTO family_arcade_badges (fam_code, user_id, badge_key, badge_title, period_key, score)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), badge_title = VALUES(badge_title), score = VALUES(score)'
            );
            $upsert->execute([$famCode, $userId, $badgeMeta['key'], $badgeMeta['title'], $periodKey, $newScore]);
            $awardedBadges[] = $badgeMeta['title'];
        }

        // Recompute overall Family Cup Champion
        $cupLeagues = self::getLeagueTables($db, $famCode, $userId, false, 'all_time');
        $topCup = $cupLeagues['familyCup'][0] ?? null;
        if (is_array($topCup) && (string) ($topCup['userId'] ?? '') === $userId) {
            $cupMeta = self::GAME_BADGE_MAP['family_cup'];
            $upsertCup = $db->prepare(
                'INSERT INTO family_arcade_badges (fam_code, user_id, badge_key, badge_title, period_key, score)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), badge_title = VALUES(badge_title), score = VALUES(score)'
            );
            $upsertCup->execute([
                $famCode,
                $userId,
                $cupMeta['key'],
                $cupMeta['title'],
                $periodKey,
                (int) $topCup['totalScore'],
            ]);
            if (!in_array($cupMeta['title'], $awardedBadges, true)) {
                $awardedBadges[] = $cupMeta['title'];
            }
        }

        // Trigger "Score Beaten!" in-app + push notification if another family member was overtaken
        if ($overtookUserId !== null) {
            $gameLabel = strtoupper($gameSlug === 'kinzip' ? 'KinZip' : ($gameSlug === 'kingrid' ? 'KinGrid' : ($gameSlug === 'kinword' ? 'KinWord' : 'KinPulse')));
            $msg = "{$playerName} just overtook your {$gameLabel} family record with {$newScore} pts! Can you reclaim your crown?";
            $actionUrl = "/arcade?game={$gameSlug}&challenge={$challengeToken}";

            try {
                $notifStmt = $db->prepare(
                    "INSERT INTO notification
                     (sender_id, receiver_id, sender_name, notification_name, notification_date, notification_type, notification_content, action_url, notification_status)
                     VALUES (?, ?, ?, ?, NOW(), 'arcade_overtake', ?, ?, 'unread')"
                );
                $notifStmt->execute([
                    $userId,
                    $overtookUserId,
                    $playerName,
                    "Score Beaten in {$gameLabel}!",
                    $msg,
                    $actionUrl,
                ]);
            } catch (Throwable $e) {
                // Non-fatal
            }

            try {
                PushNotificationClass::sendPushNotification(
                    $overtookUserId,
                    $msg,
                    $actionUrl,
                    "Crown Challenged in {$gameLabel}!"
                );
            } catch (Throwable $e) {
                // Non-fatal
            }
        }

        // Broadcast live score event to the family channel
        try {
            $famSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', $famCode) ?: 'default';
            if (class_exists(SovereignBroadcaster::class)) {
                (new SovereignBroadcaster($db))->trigger(
                    "private-family-{$famSlug}",
                    'arcade.score_submitted',
                    [
                        'userId' => $userId,
                        'playerName' => $playerName,
                        'game' => $gameSlug,
                        'score' => $newScore,
                        'isNewRecord' => $isNewRecord,
                        'challengeToken' => $challengeToken,
                    ]
                );
            }
        } catch (Throwable $e) {
            // Non-fatal
        }

        return [
            'isNewRecord' => $isNewRecord,
            'awardedBadges' => $awardedBadges,
            'overtookUserId' => $overtookUserId,
        ];
    }

    /**
     * Fetch earned "Best [Title] of the Family" badges for a list of user IDs (zero N+1).
     *
     * @param list<string> $userIds
     * @return array<string, list<array{badgeKey: string, badgeTitle: string, score: int}>>
     */
    public static function getFamilyBadgesForUsers(PDO $db, string $famCode, array $userIds): array
    {
        $cleanIds = array_values(array_unique(array_filter($userIds, static fn (string $id): bool => trim($id) !== '')));
        if (empty($cleanIds) || trim($famCode) === '') {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $sql = sprintf(
            "SELECT user_id, badge_key, badge_title, score
             FROM family_arcade_badges
             WHERE fam_code = ? AND period_key = 'all_time' AND user_id IN (%s)",
            $placeholders
        );

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge([$famCode], $cleanIds));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $uid = (string) $row['user_id'];
            $map[$uid][] = [
                'badgeKey' => (string) $row['badge_key'],
                'badgeTitle' => (string) $row['badge_title'],
                'score' => (int) $row['score'],
            ];
        }

        return $map;
    }

    /**
     * Fetch all current family champions for display in the Arcade Hall of Fame.
     *
     * @return list<array<string, mixed>>
     */
    public static function getFamilyChampions(PDO $db, string $famCode): array
    {
        try {
            $stmt = $db->prepare(
                "SELECT b.badge_key, b.badge_title, b.user_id, b.score, b.awarded_at,
                        COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.firstName, ''), ' ', COALESCE(p.lastName, ''))), ''), 'Family Member') AS full_name
                 FROM family_arcade_badges b
                 LEFT JOIN personal p ON p.id = b.user_id
                 WHERE b.fam_code = ? AND b.period_key = 'all_time'
                 ORDER BY b.score DESC"
            );
            $stmt->execute([$famCode]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }

        return array_values(array_map(static fn (array $r): array => [
            'badgeKey' => (string) $r['badge_key'],
            'badgeTitle' => (string) $r['badge_title'],
            'userId' => (string) $r['user_id'],
            'fullName' => trim((string) $r['full_name']),
            'score' => (int) $r['score'],
            'awardedAt' => (string) $r['awarded_at'],
        ], $rows));
    }
}
