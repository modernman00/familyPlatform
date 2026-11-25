{{-- Daily Family Arcade Banner on the Family Feed (with Live-Play Motion Preview across all 9 games) --}}
@php
    $arcadeDate = $arcadeDateOverride ?? date('Y-m-d');
    $arcadeDateFormatted = date('D, j M Y', strtotime($arcadeDate));
    $rawArcadeGame = isset($arcadeGameOverride) && is_string($arcadeGameOverride) && $arcadeGameOverride !== ''
        ? $arcadeGameOverride
        : \App\services\FamilyArcadeService::getDailyFeaturedGame($arcadeDate);
    $arcadeGame = \App\services\FamilyArcadeService::normaliseGame($rawArcadeGame);
    $arcadeProfile = \App\services\FamilyArcadeCardRenderer::gameProfile($arcadeGame);
    $arcadeGameLabel = match ($arcadeGame) {
        'kingrid' => 'KinGrid',
        'kinword' => 'KinWord',
        'kinpulse' => 'KinPulse',
        'wordie' => 'Wordie',
        'cheeseboard' => 'Cheeseboard (Queens)',
        'chess' => 'Family Chess',
        'ayo' => 'Ayò Olópon',
        'minesweeper' => 'Minesweeper',
        default => 'KinZip',
    };
    $initialCaption = match ($arcadeGame) {
        'wordie' => 'SHARE → 2 green, 2 gold!',
        'kingrid' => 'Row 1 clue: 1 < 2 deduced!',
        'kinword' => 'Afternoon Tea quartet matched!',
        'kinpulse' => 'Watch & echo the 4-pad rhythm',
        'cheeseboard' => '1 crown per colour, zero touch',
        'chess' => 'Queen slides in — Checkmate!',
        'ayo' => 'Sow anti-clockwise: jẹ mẹ́rin!',
        'minesweeper' => 'Cascade safe tiles & flag mines',
        default => 'Trace 1 → 2 → 3 through every cell',
    };
@endphp

@if (empty($GLOBALS['__fp_daily_arcade_banner_assets']))
@php $GLOBALS['__fp_daily_arcade_banner_assets'] = true; @endphp
<style nonce="{{ csp_nonce($nonce ?? null) }}">
    [x-cloak] { display: none !important; }

    .fp-daily-arcade-banner {
        border-radius: var(--radius-lg, 16px);
        background-color: #0f172a;
        background-image: linear-gradient(135deg, #0f172a 0%, #3b0712 56%, #1e1b4b 100%);
        color: #ffffff;
        border: 1px solid rgba(251, 191, 36, 0.45) !important;
        overflow: hidden;
    }

    .fp-arcade-banner-body {
        padding: var(--space-4, 16px);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-4, 16px);
    }

    .fp-arcade-copy-col {
        flex: 1 1 256px;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: var(--space-3, 12px);
    }

    .fp-arcade-header-row {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3, 12px);
        min-width: 0;
    }

    .fp-arcade-trophy-orb {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #1e293b;
        border: 1.5px solid #fbbf24;
        color: #fde68a;
        font-size: var(--text-lg, 1.25rem);
        box-shadow: 0 0 16px rgba(251, 191, 36, 0.2);
    }

    .fp-arcade-eyebrow-pill {
        background-color: #291d0e;
        color: #fef3c7;
        border: 1px solid rgba(251, 191, 36, 0.55);
        font-size: var(--text-xs, 0.75rem);
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: var(--space-1, 4px) var(--space-2, 8px);
        border-radius: 999px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .fp-arcade-date-label {
        font-size: var(--text-xs, 0.75rem);
        color: #fde68a;
        font-weight: 600;
        white-space: nowrap;
    }

    .fp-arcade-title {
        font-size: var(--text-base, 1rem);
        font-weight: 800;
        line-height: 1.3;
        color: #ffffff;
        margin-top: var(--space-1, 4px);
    }

    .fp-arcade-subtitle {
        font-size: var(--text-sm, 0.875rem);
        line-height: 1.45;
        color: #e2e8f0;
        margin-top: var(--space-1, 4px);
    }

    .fp-arcade-actions-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: var(--space-2, 8px);
    }

    .fp-arcade-cta-btn {
        background-color: #fbbf24 !important;
        color: #0f172a !important;
        border: 1px solid #fbbf24 !important;
        border-radius: var(--radius-md, 12px) !important;
        min-height: var(--tap-min, 44px);
        min-width: var(--tap-min, 44px);
        padding: var(--space-2, 8px) var(--space-4, 16px) !important;
        font-size: var(--text-sm, 0.875rem) !important;
        font-weight: 800 !important;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2, 8px);
        text-decoration: none;
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), filter var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-arcade-cta-btn span,
    .fp-arcade-cta-btn i {
        color: #0f172a !important;
    }

    @media (hover: hover) {
        .fp-arcade-cta-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
    }

    .fp-arcade-cta-btn:active,
    .fp-arcade-motion-btn:active,
    .fp-arcade-stage-card:active {
        transform: scale(0.98);
    }

    .fp-arcade-cta-btn:focus-visible,
    .fp-arcade-motion-btn:focus-visible,
    .fp-arcade-stage-card:focus-visible {
        outline: 2px solid #fde68a;
        outline-offset: 2px;
    }

    .fp-arcade-motion-btn {
        background-color: #1e293b;
        color: #fef3c7;
        border: 1px solid rgba(251, 191, 36, 0.42);
        border-radius: var(--radius-md, 12px);
        min-height: var(--tap-min, 44px);
        min-width: var(--tap-min, 44px);
        padding: var(--space-2, 8px) var(--space-3, 12px);
        font-size: var(--text-xs, 0.75rem);
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-1, 4px);
        cursor: pointer;
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), border-color var(--motion-fast, 150ms) var(--ease, ease);
    }

    @media (hover: hover) {
        .fp-arcade-motion-btn:hover {
            border-color: #fbbf24;
            background-color: #26354d;
        }
    }

    /* Live-Play Miniature Stage */
    .fp-arcade-stage-card {
        flex: 0 0 232px;
        width: 232px;
        background-color: #0f172a;
        border: 1px solid rgba(251, 191, 36, 0.42);
        border-radius: var(--radius-md, 12px);
        padding: var(--space-3, 12px);
        display: flex;
        flex-direction: column;
        gap: var(--space-2, 8px);
        text-decoration: none !important;
        color: #f8fafc !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 8px 20px rgba(0, 0, 0, 0.35);
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), border-color var(--motion-base, 250ms) var(--ease, ease);
    }

    @media (hover: hover) {
        .fp-arcade-stage-card:hover {
            border-color: #fbbf24;
            transform: translateY(-1px);
        }
    }

    @media (max-width: 575.98px) {
        .fp-arcade-stage-card {
            flex: 1 1 100%;
            width: 100%;
        }
    }

    .fp-arcade-stage-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-2, 8px);
    }

    .fp-arcade-live-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-1, 4px);
        font-size: var(--text-xs, 0.75rem);
        font-weight: 700;
        color: #fde68a;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    .fp-arcade-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #34d399;
        box-shadow: 0 0 8px rgba(52, 211, 153, 0.85);
        animation: fpArcadeLivePulse 1.6s ease-in-out infinite;
    }

    .fp-arcade-live-dot.is-paused {
        background-color: #fbbf24;
        box-shadow: none;
        animation: none;
    }

    .fp-arcade-tag-chip {
        font-size: var(--text-xs, 0.75rem);
        font-weight: 600;
        color: #cbd5e1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
    }

    .fp-arcade-board-box {
        min-height: 104px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: var(--space-1, 4px);
        background-color: #162033;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: var(--space-2, 8px);
        padding: var(--space-2, 8px);
        position: relative;
    }

    .fp-arcade-caption {
        font-size: var(--text-xs, 0.75rem);
        font-weight: 600;
        color: #e2e8f0;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Wordie 3D Flip Tiles */
    .fp-wordie-row {
        display: flex;
        gap: var(--space-1, 4px);
        justify-content: center;
    }

    .fp-wordie-tile {
        width: 28px;
        height: 28px;
        border-radius: var(--space-1, 4px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 800;
        background-color: #1e293b;
        color: #f8fafc;
        border: 1.5px solid #475569;
        transition: transform var(--motion-base, 250ms) var(--ease, ease), background-color var(--motion-base, 250ms) var(--ease, ease), border-color var(--motion-base, 250ms) var(--ease, ease);
    }

    .fp-wordie-tile.is-typing {
        border-color: #fbbf24;
        transform: scale(1.08);
    }

    .fp-wordie-tile.is-green {
        background-color: #15803d;
        border-color: #22c55e;
        color: #ffffff;
        transform: rotateX(360deg);
    }

    .fp-wordie-tile.is-gold {
        background-color: #fcd34d;
        border-color: #fbbf24;
        color: #1c1917;
        transform: rotateX(360deg);
    }

    .fp-wordie-tile.is-miss {
        background-color: #475569;
        border-color: #64748b;
        color: #ffffff;
        transform: rotateX(360deg);
    }

    .fp-wordie-tile.is-won {
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.55);
    }

    /* KinZip 3x3 Snake Path */
    .fp-kinzip-grid {
        display: grid;
        grid-template-columns: repeat(3, 28px);
        gap: var(--space-1, 4px);
    }

    .fp-kinzip-cell {
        width: 28px;
        height: 28px;
        border-radius: var(--space-1, 4px);
        background-color: #1e293b;
        border: 1.5px solid #334155;
        color: #f8fafc;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-kinzip-cell.wall-bottom {
        border-bottom: 3px solid #fbbf24;
    }

    .fp-kinzip-cell.is-visited {
        background-color: #059669;
        border-color: #34d399;
        color: #ffffff;
    }

    .fp-kinzip-cell.is-head {
        background-color: #fcd34d;
        border-color: #fef08a;
        color: #1c1917;
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(251, 191, 36, 0.65);
    }

    /* KinGrid 3x3 Inequality Grid */
    .fp-kingrid-grid {
        display: grid;
        grid-template-columns: repeat(3, 28px);
        gap: var(--space-1, 4px);
    }

    .fp-kingrid-cell {
        width: 28px;
        height: 28px;
        border-radius: var(--space-1, 4px);
        background-color: #1e293b;
        border: 1.5px solid #475569;
        color: #f8fafc;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-kingrid-cell.is-given {
        background-color: #334155;
        color: #fde68a;
    }

    .fp-kingrid-cell.is-focus {
        background-color: #fcd34d;
        border-color: #fbbf24;
        color: #1c1917;
        transform: scale(1.08);
    }

    .fp-kingrid-cell.is-solved {
        background-color: #15803d;
        border-color: #22c55e;
        color: #ffffff;
    }

    /* KinWord 2x4 Quartet Tiles */
    .fp-kinword-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-1, 4px);
        width: 100%;
    }

    .fp-kinword-tile {
        padding: var(--space-1, 4px);
        border-radius: var(--space-1, 4px);
        background-color: #1e293b;
        border: 1px solid #475569;
        color: #f8fafc;
        font-size: 0.65rem;
        font-weight: 700;
        text-align: center;
        transition: all var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-kinword-tile.is-picked {
        background-color: #fcd34d;
        border-color: #fbbf24;
        color: #1c1917;
        transform: translateY(-2px);
    }

    .fp-kinword-solved {
        width: 100%;
        padding: var(--space-1, 4px) var(--space-2, 8px);
        border-radius: var(--space-1, 4px);
        background-color: #15803d;
        color: #ffffff;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 700;
        text-align: center;
    }

    /* KinPulse 2x2 Harmonic Pads */
    .fp-kinpulse-grid {
        display: grid;
        grid-template-columns: repeat(2, 38px);
        gap: var(--space-2, 8px);
    }

    .fp-kinpulse-pad {
        width: 38px;
        height: 38px;
        border-radius: var(--space-2, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-sm, 0.875rem);
        font-weight: 800;
        color: #ffffff;
        opacity: 0.55;
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), opacity var(--motion-fast, 150ms) var(--ease, ease), box-shadow var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-kinpulse-pad.is-lit {
        opacity: 1;
        transform: scale(1.14);
        box-shadow: 0 0 14px rgba(255, 255, 255, 0.7);
    }

    /* Cheeseboard / Chess / Minesweeper 4x4 Compact Grids */
    .fp-compact-4x4 {
        display: grid;
        grid-template-columns: repeat(4, 22px);
        gap: 3px;
    }

    .fp-compact-cell {
        width: 22px;
        height: 22px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 800;
        color: #1c1917;
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), background-color var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-compact-cell.is-pop {
        transform: scale(1.14);
    }

    /* Ayo Olopon 2x6 Hollows */
    .fp-ayo-board {
        background-color: #78350f;
        border: 1.5px solid #b45309;
        border-radius: var(--radius-md, 12px);
        padding: var(--space-2, 8px);
        display: flex;
        flex-direction: column;
        gap: var(--space-1, 4px);
    }

    .fp-ayo-row {
        display: flex;
        gap: var(--space-1, 4px);
    }

    .fp-ayo-pit {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #fff7ed;
        border: 1.5px solid #d6c3a5;
        color: #1c1917;
        font-size: 0.7rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform var(--motion-fast, 150ms) var(--ease, ease), background-color var(--motion-fast, 150ms) var(--ease, ease);
    }

    .fp-ayo-pit.is-sowing {
        background-color: #fcd34d;
        border-color: #f59e0b;
        transform: scale(1.12);
    }

    .fp-ayo-pit.is-captured {
        background-color: #15803d;
        border-color: #4ade80;
        color: #ffffff;
        transform: scale(1.14);
    }

    @keyframes fpArcadeLivePulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.45; transform: scale(0.85); }
    }

    @media (prefers-reduced-motion: reduce) {
        .fp-arcade-live-dot,
        .fp-wordie-tile,
        .fp-kinzip-cell,
        .fp-kingrid-cell,
        .fp-kinword-tile,
        .fp-kinpulse-pad,
        .fp-compact-cell,
        .fp-ayo-pit {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
    window.dailyArcadePreview = function (gameSlug) {
        const SCRIPTS = {
            wordie: {
                intervalMs: 850,
                reducedStep: 4,
                frames: [
                    {
                        caption: 'Typing opener: C L O U D…',
                        rows: [
                            { letters: ['C', 'L', 'O', '', ''], states: ['t', 't', 't', '', ''] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] }
                        ]
                    },
                    {
                        caption: 'CLOUD — 0 matches, try new vowels',
                        rows: [
                            { letters: ['C', 'L', 'O', 'U', 'D'], states: ['x', 'x', 'x', 'x', 'x'] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] }
                        ]
                    },
                    {
                        caption: 'Testing S H A R E…',
                        rows: [
                            { letters: ['C', 'L', 'O', 'U', 'D'], states: ['x', 'x', 'x', 'x', 'x'] },
                            { letters: ['S', 'H', 'A', 'R', 'E'], states: ['t', 't', 't', 't', 't'] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] }
                        ]
                    },
                    {
                        caption: 'SHARE → A, R green; H, E gold!',
                        rows: [
                            { letters: ['C', 'L', 'O', 'U', 'D'], states: ['x', 'x', 'x', 'x', 'x'] },
                            { letters: ['S', 'H', 'A', 'R', 'E'], states: ['x', 'y', 'g', 'g', 'y'] },
                            { letters: ['', '', '', '', ''], states: ['', '', '', '', ''] }
                        ]
                    },
                    {
                        caption: 'Locking H E A R T…',
                        rows: [
                            { letters: ['C', 'L', 'O', 'U', 'D'], states: ['x', 'x', 'x', 'x', 'x'] },
                            { letters: ['S', 'H', 'A', 'R', 'E'], states: ['x', 'y', 'g', 'g', 'y'] },
                            { letters: ['H', 'E', 'A', 'R', 'T'], states: ['t', 't', 't', 't', 't'] }
                        ]
                    },
                    {
                        caption: 'HEART — Solved in 3/6! 🏆',
                        won: true,
                        rows: [
                            { letters: ['C', 'L', 'O', 'U', 'D'], states: ['x', 'x', 'x', 'x', 'x'] },
                            { letters: ['S', 'H', 'A', 'R', 'E'], states: ['x', 'y', 'g', 'g', 'y'] },
                            { letters: ['H', 'E', 'A', 'R', 'T'], states: ['g', 'g', 'g', 'g', 'g'] }
                        ]
                    }
                ]
            },
            kinzip: {
                intervalMs: 650,
                reducedStep: 5,
                path: ['0,0', '0,1', '0,2', '1,2', '1,1', '1,0', '2,0', '2,1', '2,2'],
                frames: [
                    { len: 1, caption: 'Start at dot 1 in top-left' },
                    { len: 3, caption: 'Route around the gold wall →' },
                    { len: 5, caption: 'Connect checkpoint 2 in centre!' },
                    { len: 7, caption: 'Sweep left & down to fill every cell' },
                    { len: 8, caption: 'Closing in on checkpoint 3…' },
                    { len: 9, caption: 'All 9 cells connected — Solved! 🏆' }
                ]
            },
            kingrid: {
                intervalMs: 850,
                reducedStep: 3,
                frames: [
                    { grid: [[3, 0, 0], [0, 2, 0], [0, 0, 1]], focus: '0,1', solved: false, caption: 'Clue says middle < right → place 1' },
                    { grid: [[3, 1, 2], [0, 2, 0], [0, 0, 1]], focus: '0,2', solved: false, caption: 'Row 1 completed: 3, 1, 2' },
                    { grid: [[3, 1, 2], [1, 2, 3], [0, 0, 1]], focus: '1,2', solved: false, caption: 'Row 2 deduced with zero repeats' },
                    { grid: [[3, 1, 2], [1, 2, 3], [2, 3, 1]], focus: '', solved: true, caption: 'Every row & column solved! 🏆' }
                ]
            },
            kinword: {
                intervalMs: 900,
                reducedStep: 2,
                frames: [
                    { picked: ['TEA', 'SCONE'], solvedCount: 0, caption: 'Spotting Afternoon Tea words…' },
                    { picked: ['TEA', 'SCONE', 'JAM', 'CAKE'], solvedCount: 0, caption: '4 matching words selected!' },
                    { picked: ['CROWN', 'ROYAL'], solvedCount: 1, caption: 'Afternoon Tea ✓ — next quartet…' },
                    { picked: [], solvedCount: 2, caption: 'Both quartets solved! 🏆' }
                ]
            },
            kinpulse: {
                intervalMs: 700,
                reducedStep: 2,
                frames: [
                    { lit: 0, caption: 'Watch sequence: Pad 1 ♪' },
                    { lit: 2, caption: 'Watch sequence: Pad 3 ♫' },
                    { lit: 1, caption: 'Your turn — echoing Pad 2!' },
                    { lit: 3, caption: 'Sequence 4/4 echoed — Round Clear! 🏆' }
                ]
            },
            cheeseboard: {
                intervalMs: 850,
                reducedStep: 3,
                frames: [
                    { crowns: ['0,1'], crosses: ['0,0', '1,0', '1,1', '1,2'], caption: 'Place 👑 in pink & mark × halo' },
                    { crowns: ['0,1', '2,0'], crosses: ['0,0', '1,0', '1,1', '1,2', '2,1', '3,1'], caption: 'Only 1 spot left in gold territory' },
                    { crowns: ['0,1', '2,0', '1,3'], crosses: ['0,0', '1,0', '1,1', '1,2', '2,1', '2,2', '2,3'], caption: 'Sky territory crowned on right' },
                    { crowns: ['0,1', '2,0', '1,3', '3,2'], crosses: [], caption: '4 crowns, zero touching — Solved! 🏆' }
                ]
            },
            chess: {
                intervalMs: 900,
                reducedStep: 3,
                frames: [
                    { pieces: { '2,1': '♘', '3,2': '♕', '0,3': '♚', '3,0': '♔' }, hl: ['1,3', '0,2'], move: '2,1', caption: 'White Knight eyes royal fork…' },
                    { pieces: { '1,2': '♘', '3,2': '♕', '0,3': '♚', '3,0': '♔' }, hl: ['1,2'], move: '1,2', caption: 'Knight leaps to f6 — Check!' },
                    { pieces: { '1,2': '♘', '3,2': '♕', '0,2': '♚', '3,0': '♔' }, hl: ['1,2', '1,1', '1,0'], move: '3,2', caption: 'Black King forced left — Queen slides…' },
                    { pieces: { '1,2': '♘', '1,2_q': '', '0,2': '♚', '1,1': '♕', '3,0': '♔' }, hl: ['0,2', '1,1'], move: '1,1', caption: 'Queen to g7 — Checkmate! ♔' }
                ]
            },
            ayo: {
                intervalMs: 850,
                reducedStep: 3,
                frames: [
                    { north: [4, 4, 5, 3, 3, 6], south: [6, 4, 4, 3, 6, 2], sowing: [11], captured: [], caption: 'Pick up 2 seeds from South-right pit' },
                    { north: [4, 4, 5, 3, 4, 6], south: [6, 4, 4, 3, 6, 0], sowing: [5], captured: [], caption: 'Sow anti-clockwise into North row…' },
                    { north: [4, 4, 5, 4, 4, 6], south: [6, 4, 4, 3, 6, 0], sowing: [4, 5], captured: [], caption: 'Last seed makes 4 — jẹ mẹ́rin!' },
                    { north: [4, 4, 5, 0, 0, 6], south: [6, 4, 4, 3, 6, 0], sowing: [], captured: [3, 4], caption: 'Chain capture: +8 seeds harvested! 🏆' }
                ]
            },
            minesweeper: {
                intervalMs: 800,
                reducedStep: 3,
                frames: [
                    { open: ['0,0', '0,1', '1,0', '1,1'], flags: [], pop: '0,0', caption: 'First tap cascades safe corner open' },
                    { open: ['0,0', '0,1', '1,0', '1,1', '2,1', '2,2', '3,2', '3,3'], flags: ['0,2'], pop: '0,2', caption: 'Clue 1 isolates corner hazard → 🚩' },
                    { open: ['0,0', '0,1', '1,0', '1,1', '1,2', '2,1', '2,2', '3,1', '3,2', '3,3'], flags: ['0,2', '2,0'], pop: '2,0', caption: 'Second hazard flagged safely → 🚩' },
                    { open: ['0,0', '0,1', '0,3', '1,0', '1,1', '1,2', '1,3', '2,1', '2,2', '2,3', '3,0', '3,1', '3,2', '3,3'], flags: ['0,2', '2,0'], pop: '1,3', caption: 'All safe squares cleared! 💎' }
                ]
            }
        };

        const cfg = SCRIPTS[gameSlug] ? SCRIPTS[gameSlug] : SCRIPTS.kinzip;
        const prefersReduced = typeof window !== 'undefined'
            && window.matchMedia
            && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        return {
            game: gameSlug,
            step: prefersReduced ? cfg.reducedStep : 3,
            playing: !prefersReduced,
            inViewport: true,
            timerId: null,
            observer: null,
            visibilityHandler: null,

            init() {
                if (!prefersReduced) {
                    this.step = 0;
                    this.startLoop();
                }

                if (typeof IntersectionObserver !== 'undefined' && this.$el) {
                    this.observer = new IntersectionObserver((entries) => {
                        const entry = entries[0];
                        if (!entry) return;
                        this.inViewport = Boolean(entry.isIntersecting);
                    }, { threshold: 0.15 });
                    this.observer.observe(this.$el);
                }

                this.visibilityHandler = () => {
                    if (document.hidden) {
                        this.stopLoop();
                    } else if (this.playing) {
                        this.startLoop();
                    }
                };
                document.addEventListener('visibilitychange', this.visibilityHandler);
            },

            destroy() {
                this.stopLoop();
                if (this.observer) {
                    this.observer.disconnect();
                    this.observer = null;
                }
                if (this.visibilityHandler) {
                    document.removeEventListener('visibilitychange', this.visibilityHandler);
                    this.visibilityHandler = null;
                }
            },

            startLoop() {
                this.stopLoop();
                this.timerId = window.setInterval(() => {
                    if (!this.playing || !this.inViewport || document.hidden) return;
                    this.step = (this.step + 1) % cfg.frames.length;
                }, cfg.intervalMs);
            },

            stopLoop() {
                if (this.timerId !== null) {
                    window.clearInterval(this.timerId);
                    this.timerId = null;
                }
            },

            togglePlay() {
                this.playing = !this.playing;
                if (this.playing) {
                    this.step = (this.step + 1) % cfg.frames.length;
                    this.startLoop();
                } else {
                    this.stopLoop();
                }
            },

            get frame() {
                return cfg.frames[this.step] ? cfg.frames[this.step] : cfg.frames[0];
            },

            get statusText() {
                return this.frame.caption;
            },

            wordieLetter(r, c) {
                const row = this.frame.rows && this.frame.rows[r];
                return row && row.letters[c] ? row.letters[c] : '';
            },

            wordieClass(r, c) {
                const row = this.frame.rows && this.frame.rows[r];
                const st = row && row.states ? row.states[c] : '';
                return {
                    'is-typing': st === 't',
                    'is-green': st === 'g',
                    'is-gold': st === 'y',
                    'is-miss': st === 'x',
                    'is-won': Boolean(this.frame.won && r === 2)
                };
            },

            kinzipVisited(key) {
                const active = (cfg.path ? cfg.path : []).slice(0, this.frame.len);
                return active.indexOf(key) !== -1;
            },

            kinzipHead(key) {
                const active = (cfg.path ? cfg.path : []).slice(0, this.frame.len);
                return active.length > 0 && active[active.length - 1] === key;
            },

            kingridVal(r, c) {
                const g = this.frame.grid;
                return g && g[r] && g[r][c] > 0 ? String(g[r][c]) : '';
            },

            kingridFocus(key) {
                return this.frame.focus === key;
            },

            kinwordPicked(word) {
                return Array.isArray(this.frame.picked) && this.frame.picked.indexOf(word) !== -1;
            },

            cheeseSymbol(key) {
                if (Array.isArray(this.frame.crowns) && this.frame.crowns.indexOf(key) !== -1) return '👑';
                if (Array.isArray(this.frame.crosses) && this.frame.crosses.indexOf(key) !== -1) return '×';
                return '';
            },

            cheesePop(key) {
                const crowns = this.frame.crowns;
                return Array.isArray(crowns) && crowns.length > 0 && crowns[crowns.length - 1] === key;
            },

            chessPiece(key) {
                return this.frame.pieces && this.frame.pieces[key] ? this.frame.pieces[key] : '';
            },

            chessBg(r, c, key) {
                if (Array.isArray(this.frame.hl) && this.frame.hl.indexOf(key) !== -1) {
                    return '#fcd34d';
                }
                return (r + c) % 2 === 1 ? '#d6b98c' : '#fff7ed';
            },

            ayoVal(rowName, idx) {
                const arr = this.frame[rowName];
                return Array.isArray(arr) && arr[idx] !== undefined ? String(arr[idx]) : '4';
            },

            ayoSowing(globalIdx) {
                return Array.isArray(this.frame.sowing) && this.frame.sowing.indexOf(globalIdx) !== -1;
            },

            ayoCaptured(northIdx) {
                return Array.isArray(this.frame.captured) && this.frame.captured.indexOf(northIdx) !== -1;
            },

            mineOpen(key) {
                return Array.isArray(this.frame.open) && this.frame.open.indexOf(key) !== -1;
            },

            mineSymbol(key, fallbackNum) {
                if (Array.isArray(this.frame.flags) && this.frame.flags.indexOf(key) !== -1) return '🚩';
                if (this.mineOpen(key)) return fallbackNum > 0 ? String(fallbackNum) : '';
                return '';
            }
        };
    };
</script>
@endif

<div class="card border-0 shadow-sm mb-3 fp-daily-arcade-banner"
     x-data="dailyArcadePreview('{{ $arcadeGame }}')"
     data-featured-game="{{ $arcadeGame }}">
    <div class="fp-arcade-banner-body">
        {{-- Left Column: Title, Challenge Copy & Tactile Actions --}}
        <div class="fp-arcade-copy-col">
            <div class="fp-arcade-header-row">
                <div class="fp-arcade-trophy-orb" aria-hidden="true">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fp-arcade-eyebrow-pill">DAILY FAMILY ARCADE</span>
                        <span class="fp-arcade-date-label">{{ $arcadeDateFormatted }}</span>
                    </div>
                    <div class="fp-arcade-title">
                        Today’s Featured Challenge: {{ $arcadeGameLabel }}
                    </div>
                    <div class="fp-arcade-subtitle">
                        Race the ATG Bot or beat your family’s best time to claim the crown!
                    </div>
                </div>
            </div>

            <div class="fp-arcade-actions-row">
                <a href="/arcade?game={{ $arcadeGame }}"
                   data-spin="true"
                   class="btn fp-arcade-cta-btn">
                    <i class="bi bi-controller" aria-hidden="true"></i>
                    <span>Play {{ $arcadeGameLabel }}</span>
                </a>

                <button type="button"
                        class="fp-arcade-motion-btn"
                        x-on:click="togglePlay()"
                        :aria-label="playing ? 'Pause live gameplay preview' : 'Play live gameplay preview'"
                        :title="playing ? 'Pause preview' : 'Play preview'"
                        aria-label="Pause live gameplay preview">
                    <i class="bi" :class="playing ? 'bi-pause-fill' : 'bi-play-fill'" aria-hidden="true"></i>
                    <span x-text="playing ? 'Pause Demo' : 'Watch Demo'">Pause Demo</span>
                </button>
            </div>
        </div>

        {{-- Right Column: Animated Live-Play Stage for Today's Featured Game --}}
        <a href="/arcade?game={{ $arcadeGame }}"
           data-spin="true"
           class="fp-arcade-stage-card"
           aria-label="Watch live gameplay preview of {{ $arcadeGameLabel }} and tap to play">
            <div class="fp-arcade-stage-top">
                <span class="fp-arcade-live-badge">
                    <span class="fp-arcade-live-dot" :class="{ 'is-paused': !playing }" aria-hidden="true"></span>
                    <span>LIVE PLAY</span>
                </span>
                <span class="fp-arcade-tag-chip" title="{{ $arcadeProfile['tag'] }}">{{ $arcadeProfile['tag'] }}</span>
            </div>

            <div class="fp-arcade-board-box" aria-hidden="true">
                @switch($arcadeGame)
                    @case('wordie')
                        @php
                            $ssrWordie = [
                                [['C', 'is-miss'], ['L', 'is-miss'], ['O', 'is-miss'], ['U', 'is-miss'], ['D', 'is-miss']],
                                [['S', 'is-miss'], ['H', 'is-gold'], ['A', 'is-green'], ['R', 'is-green'], ['E', 'is-gold']],
                                [['', ''], ['', ''], ['', ''], ['', ''], ['', '']],
                            ];
                        @endphp
                        @for ($r = 0; $r < 3; $r++)
                            <div class="fp-wordie-row">
                                @for ($c = 0; $c < 5; $c++)
                                    <div class="fp-wordie-tile {{ $ssrWordie[$r][$c][1] }}"
                                         style="transition-delay: {{ $c * 45 }}ms;"
                                         :class="wordieClass({{ $r }}, {{ $c }})"
                                         x-text="wordieLetter({{ $r }}, {{ $c }})">{{ $ssrWordie[$r][$c][0] }}</div>
                                @endfor
                            </div>
                        @endfor
                        @break

                    @case('kingrid')
                        @php
                            $ssrGrid = [[3, 1, 2], [0, 2, 0], [0, 0, 1]];
                        @endphp
                        <div class="fp-kingrid-grid">
                            @for ($r = 0; $r < 3; $r++)
                                @for ($c = 0; $c < 3; $c++)
                                    @php
                                        $isGiven = ($r === 0 && $c === 0) || ($r === 1 && $c === 1) || ($r === 2 && $c === 2);
                                        $cellKey = $r . ',' . $c;
                                    @endphp
                                    <div class="fp-kingrid-cell {{ $isGiven ? 'is-given' : '' }}"
                                         :class="{ 'is-focus': kingridFocus('{{ $cellKey }}'), 'is-solved': Boolean(frame.solved) }"
                                         x-text="kingridVal({{ $r }}, {{ $c }})">{{ $ssrGrid[$r][$c] > 0 ? $ssrGrid[$r][$c] : '' }}</div>
                                @endfor
                            @endfor
                        </div>
                        @break

                    @case('kinword')
                        <div class="w-100 d-flex flex-column gap-1">
                            <div class="fp-kinword-solved" x-show="frame.solvedCount >= 1" x-cloak>
                                Afternoon Tea: TEA, SCONE, JAM, CAKE
                            </div>
                            <div class="fp-kinword-solved" style="background-color: #b45309;" x-show="frame.solvedCount >= 2" x-cloak>
                                Royal Regalia: CROWN, ROYAL, THRONE, PALACE
                            </div>
                            <div class="fp-kinword-grid" x-show="frame.solvedCount < 2">
                                @foreach (['TEA', 'CROWN', 'SCONE', 'ROYAL', 'JAM', 'THRONE', 'CAKE', 'PALACE'] as $kwIdx => $kwWord)
                                    @php $isTeaWord = in_array($kwWord, ['TEA', 'SCONE', 'JAM', 'CAKE'], true); @endphp
                                    <div class="fp-kinword-tile {{ $isTeaWord && $kwIdx < 4 ? 'is-picked' : '' }}"
                                         x-show="{{ $isTeaWord ? 'frame.solvedCount < 1' : 'true' }}"
                                         :class="{ 'is-picked': kinwordPicked('{{ $kwWord }}') }">{{ $kwWord }}</div>
                                @endforeach
                            </div>
                        </div>
                        @break

                    @case('kinpulse')
                        <div class="fp-kinpulse-grid">
                            @foreach (['#dc2626', '#d97706', '#059669', '#2563eb'] as $pIdx => $pCol)
                                <div class="fp-kinpulse-pad {{ $pIdx === 0 ? 'is-lit' : '' }}"
                                     style="background-color: {{ $pCol }};"
                                     :class="{ 'is-lit': frame.lit === {{ $pIdx }} }">
                                    {{ $pIdx + 1 }}
                                </div>
                            @endforeach
                        </div>
                        @break

                    @case('cheeseboard')
                        @php
                            $cheeseMap = [
                                ['#fecdd3', '#fecdd3', '#bae6fd', '#bae6fd'],
                                ['#fde68a', '#fde68a', '#bae6fd', '#bae6fd'],
                                ['#fde68a', '#bbf7d0', '#bbf7d0', '#bbf7d0'],
                                ['#bbf7d0', '#bbf7d0', '#bbf7d0', '#bbf7d0'],
                            ];
                        @endphp
                        <div class="fp-compact-4x4">
                            @for ($r = 0; $r < 4; $r++)
                                @for ($c = 0; $c < 4; $c++)
                                    @php $ck = $r . ',' . $c; @endphp
                                    <div class="fp-compact-cell"
                                         style="background-color: {{ $cheeseMap[$r][$c] }};"
                                         :class="{ 'is-pop': cheesePop('{{ $ck }}') }"
                                         x-text="cheeseSymbol('{{ $ck }}')">{{ $ck === '0,1' ? '👑' : '' }}</div>
                                @endfor
                            @endfor
                        </div>
                        @break

                    @case('chess')
                        <div class="fp-compact-4x4" style="border: 1.5px solid #b45309; border-radius: 4px; padding: 1px;">
                            @for ($r = 0; $r < 4; $r++)
                                @for ($c = 0; $c < 4; $c++)
                                    @php
                                        $sqKey = $r . ',' . $c;
                                        $defaultBg = ($r + $c) % 2 === 1 ? '#d6b98c' : '#fff7ed';
                                    @endphp
                                    <div class="fp-compact-cell"
                                         style="background-color: {{ $defaultBg }};"
                                         :style="{ backgroundColor: chessBg({{ $r }}, {{ $c }}, '{{ $sqKey }}') }"
                                         :class="{ 'is-pop': frame.move === '{{ $sqKey }}' }"
                                         x-text="chessPiece('{{ $sqKey }}')">{{ $sqKey === '0,3' ? '♚' : ($sqKey === '2,1' ? '♘' : '') }}</div>
                                @endfor
                            @endfor
                        </div>
                        @break

                    @case('ayo')
                        <div class="fp-ayo-board">
                            <div class="fp-ayo-row">
                                @for ($n = 0; $n < 6; $n++)
                                    <div class="fp-ayo-pit"
                                         :class="{ 'is-sowing': ayoSowing({{ $n }}), 'is-captured': ayoCaptured({{ $n }}) }"
                                         x-text="ayoVal('north', {{ $n }})">4</div>
                                @endfor
                            </div>
                            <div class="fp-ayo-row">
                                @for ($s = 0; $s < 6; $s++)
                                    <div class="fp-ayo-pit"
                                         :class="{ 'is-sowing': ayoSowing({{ $s + 6 }}) }"
                                         x-text="ayoVal('south', {{ $s }})">4</div>
                                @endfor
                            </div>
                        </div>
                        @break

                    @case('minesweeper')
                        @php
                            $mineNums = [
                                [0, 1, 9, 1],
                                [1, 2, 1, 1],
                                [9, 1, 0, 0],
                                [1, 1, 0, 0],
                            ];
                        @endphp
                        <div class="fp-compact-4x4">
                            @for ($r = 0; $r < 4; $r++)
                                @for ($c = 0; $c < 4; $c++)
                                    @php
                                        $mk = $r . ',' . $c;
                                        $mVal = $mineNums[$r][$c];
                                    @endphp
                                    <div class="fp-compact-cell"
                                         style="background-color: #475569; color: #1c1917;"
                                         :style="{ backgroundColor: mineOpen('{{ $mk }}') ? '#fff7ed' : '#475569', color: mineOpen('{{ $mk }}') ? '#1c1917' : '#ffffff' }"
                                         :class="{ 'is-pop': frame.pop === '{{ $mk }}' }"
                                         x-text="mineSymbol('{{ $mk }}', {{ $mVal }})"></div>
                                @endfor
                            @endfor
                        </div>
                        @break

                    @default
                        {{-- KinZip 3x3 Labyrinth --}}
                        @php
                            $cpMap = ['0,0' => '1', '1,1' => '2', '2,2' => '3'];
                        @endphp
                        <div class="fp-kinzip-grid">
                            @for ($r = 0; $r < 3; $r++)
                                @for ($c = 0; $c < 3; $c++)
                                    @php
                                        $zk = $r . ',' . $c;
                                        $cp = $cpMap[$zk] ?? '';
                                    @endphp
                                    <div class="fp-kinzip-cell {{ $zk === '0,1' ? 'wall-bottom' : '' }} {{ $zk === '0,0' ? 'is-visited' : '' }}"
                                         :class="{ 'is-visited': kinzipVisited('{{ $zk }}'), 'is-head': kinzipHead('{{ $zk }}') }">{{ $cp }}</div>
                                @endfor
                            @endfor
                        </div>
                @endswitch
            </div>

            <div class="fp-arcade-caption" x-text="statusText">{{ $initialCaption }}</div>
        </a>
    </div>
</div>
