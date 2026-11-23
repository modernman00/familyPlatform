import { useState, useEffect, useRef } from 'preact/hooks';
import { playArcadeSfx } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

const GAME_GUIDES = {
    kinzip: {
        name: 'KinZip',
        subtitle: 'Draw one path through every square',
        rules: [
            'Start at dot 1, then tap or drag to the next square (up, down, left or right — no diagonals).',
            'Visit the numbered dots in order (1 → 2 → 3…) and go around the thick gold walls.',
            'Fill every single square on the board to win!',
        ],
        strategyTips: [
            { icon: 'bi-box-arrow-in-down-left', title: 'Corner Traps First', desc: 'Any cell in a corner has only two valid exits. If a wall blocks one, the path is 100% forced.' },
            { icon: 'bi-border-style', title: 'Route Around Thick Walls', desc: 'Gold walls block straight lines. Always plan corridor turns before wandering into the open centre.' },
            { icon: 'bi-slash-circle', title: 'No Isolated Islands', desc: 'Never draw a segment that leaves a solitary empty square surrounded by walls and lines — it becomes unvisitable.' },
            { icon: 'bi-arrow-left-right', title: 'Work Backwards from Dots', desc: 'If the forward path seems ambiguous, trace backwards from the next target dot to spot where it must arrive.' },
        ],
        proTip: 'Think of KinZip as a snake filling a labyrinth: the walls are your guides, not obstacles.',
        totalSteps: 9,
        intervalMs: 650,
    },
    kingrid: {
        name: 'KinGrid',
        subtitle: 'Put 1 to N in every row and column',
        rules: [
            'Tap an empty square, then tap a number (or type it on your keyboard).',
            'Every row and column must have each number once — no repeats allowed!',
            'Follow the > (bigger than) and < (smaller than) signs between squares.',
        ],
        strategyTips: [
            { icon: 'bi-bullseye', title: 'Hunt Bounded Clues First', desc: 'Look for 2 > [?] (forces 1) or [?] < 4 (forces 5). These give immediate guaranteed numbers without guessing.' },
            { icon: 'bi-123', title: 'The Extreme Digits (1 & 5)', desc: '1 can never be on the greater (open) side of an inequality; 5 can never be on the smaller (pointed) side.' },
            { icon: 'bi-grid-3x3', title: 'Naked Singles in Rows/Cols', desc: 'Check any row or column with 4 numbers filled — the 5th number is free and determined automatically.' },
            { icon: 'bi-arrows-angle-expand', title: 'Inequality Chains', desc: 'In chains like A < B < C, A can never be 4 or 5, C can never be 1 or 2, and B is trapped in the middle.' },
        ],
        proTip: 'In a 5×5 grid, numbers 1 and 5 have the tightest geometric rules. Eliminate their candidate spots first!',
        totalSteps: 7,
        intervalMs: 950,
    },
    kinword: {
        name: 'KinWord',
        subtitle: 'Group 16 words into 4 matching sets of 4',
        rules: [
            'Look for 4 words that belong together (for example: 4 Sunday roast items, or 4 seaside towns).',
            'Tap those 4 words to highlight them in gold, then tap Submit.',
            'Find all 4 groups before you lose your 4 hearts! (Tip: if 3 of 4 match, we will tell you!)',
        ],
        strategyTips: [
            { icon: 'bi-intersect', title: 'Spot Multiple Overlaps', desc: 'Certain words are deliberate decoys that fit two groups. Identify at least 3 candidates per theme before submitting.' },
            { icon: 'bi-heart-half', title: 'Three-of-Four Alerts', desc: 'If the banner says "One away!", don\'t throw away all 4 words — swap out only the single weakest link.' },
            { icon: 'bi-puzzle', title: 'Compound Word Patterns', desc: 'Watch for words that share a prefix or suffix (for example, words that follow "TEA": Tea bag, Tea cup, Tea pot, Tea spoon).' },
            { icon: 'bi-funnel', title: 'Eliminate the Obvious', desc: 'Solving just one straightforward group shrinks the remaining board from 16 to 12 words, making tough connections obvious.' },
        ],
        proTip: 'Never guess four random words on turn 1. Take 20 seconds to survey the whole grid for subtle wordplay themes.',
        totalSteps: 10,
        intervalMs: 750,
    },
    kinpulse: {
        name: 'KinPulse',
        subtitle: 'Watch, listen & copy the pattern',
        rules: [
            'Tap Start Sequence (or any square) and watch the coloured squares light up.',
            'Copy the exact same pattern by tapping the squares in the same order.',
            'Each round adds one more square to remember — finish all rounds to win!',
        ],
        strategyTips: [
            { icon: 'bi-music-note-beamed', title: 'Rhythmic Chunking', desc: 'Group the sequence into pairs or triplets (like a phone number) instead of trying to hold 8 disconnected numbers.' },
            { icon: 'bi-soundwave', title: 'Pitch & Height Mapping', desc: 'High musical tones belong to the upper rows, while low resonant tones belong to the bottom pads.' },
            { icon: 'bi-hand-index-thumb', title: 'Spatial Staging', desc: 'Keep your thumb or pointer hovering over the quadrant where the current pattern sequence starts.' },
            { icon: 'bi-lightning-charge', title: 'Watch the Spring Surge', desc: 'The new multi-stage kinetic pulse and shockwave ring give unmistakable visual cues for exact timing.' },
        ],
        proTip: 'Sing or say the numbers or colours out loud as they flash — dual audio-visual memory boosts recall by 40%!',
        totalSteps: 10,
        intervalMs: 800,
    },
    wordie: {
        name: 'Wordie',
        subtitle: 'Guess the 5-letter family word in 6 tries',
        rules: [
            'Type a 5-letter word and tap Enter.',
            'Green tiles mean the letter is correct and in the right spot.',
            'Gold tiles mean the letter is in the word, but in a different position.',
            'Grey tiles mean the letter is not in the word at all.',
        ],
        strategyTips: [
            { icon: 'bi-alphabet', title: 'Start with 3 Vowels', desc: 'Use strong opener words like SHARE, HEART, or CLOUD to test the most common vowels and consonants right away.' },
            { icon: 'bi-keyboard', title: 'Track On-Screen Keys', desc: 'The keyboard highlights letters in real-time. Never reuse a grey letter you have already eliminated!' },
            { icon: 'bi-lightning', title: 'Information Over Guessing', desc: 'On turn 2, don\'t rush to solve. Test 5 completely new letters to cut down possibilities.' },
            { icon: 'bi-gem', title: 'Family Themes', desc: 'Many daily words celebrate family life, celebrations, heirlooms, and British heritage.' },
        ],
        proTip: 'A green letter is anchored; use your next turns to probe the remaining unknown spots!',
        totalSteps: 6,
        intervalMs: 800,
    },
    cheeseboard: {
        name: 'Cheeseboard (Queens)',
        subtitle: 'Crown Territory Puzzle (Not Chess!)',
        rules: [
            'Tap a square once for × (empty marker), twice for 👑 (Crown), and three times to clear.',
            'Place exactly 1 Crown in every row and 1 Crown in every column.',
            'Place exactly 1 Crown in every coloured territory region.',
            'Crowns CANNOT touch each other in any direction — not even diagonally!',
        ],
        strategyTips: [
            { icon: 'bi-x-circle', title: 'Mark × Early and Often', desc: 'Crossing out impossible squares instantly narrows down where crowns MUST be placed.' },
            { icon: 'bi-cone-striped', title: 'The Diagonal Halo Rule', desc: 'Whenever you place a Crown, immediately place × in all 8 surrounding squares.' },
            { icon: 'bi-palette', title: 'Hunt Tiny Colour Regions', desc: 'If a colour region has only 1 or 2 squares, focus there first — the crown is almost forced!' },
            { icon: 'bi-shield-shaded', title: 'Row & Col Scans', desc: 'If a row has only one uncrossed square left, the crown must go there!' },
        ],
        proTip: 'Cheeseboard is a Queens territorial deduction game, not traditional chess. Corner every crown into its own space.',
        totalSteps: 7,
        intervalMs: 800,
    },
    chess: {
        name: 'Family Chess',
        subtitle: 'Checkmate the opposing King on the classical 8×8 board',
        rules: [
            'White moves first. Tap any piece to see its valid legal destination squares.',
            'Pawns move forward 1 square (or 2 on their first move) and capture diagonally.',
            'Knights jump in an L-shape; Bishops slide diagonally; Rooks slide straight; Queens slide in any direction.',
            'Deliver Checkmate to the enemy King while protecting your own from capture!',
        ],
        strategyTips: [
            { icon: 'bi-bullseye', title: 'Control the Centre (d4, e4, d5, e5)', desc: 'Pieces in the centre command the most squares and launch attacks into enemy lines.' },
            { icon: 'bi-shield-check', title: 'Develop Knights & Bishops Early', desc: 'Bring your minor pieces out before launching forward Queen attacks.' },
            { icon: 'bi-crown', title: 'Pawn Promotion Sovereignty', desc: 'Marching a pawn to the 8th rank promotes it directly to a Queen with a crown.' },
            { icon: 'bi-eye', title: 'Look for Checks & Forks', desc: 'Knights excel at simultaneous attacks on the enemy King and Queen.' },
        ],
        proTip: 'Always check if your opponent has placed your King in check before planning counter-strikes.',
        totalSteps: 8,
        intervalMs: 800,
    },
    ayo: {
        name: 'Ayò Olópon',
        subtitle: 'The Revered Yorùbá Board Game of Kings',
        rules: [
            'The board has 12 pits (6 South, 6 North) with 4 seeds each (48 seeds total).',
            'On your turn, choose any pit on your side and sow seeds anti-clockwise, dropping 1 per pit.',
            'If a pit holds 12 or more seeds, skip your starting pit as you lap around.',
            'Capture seeds when your final seed lands on the opponent’s side and makes EXACTLY 4 seeds (jẹ mẹ́rin)!',
            'Chain captures: Any consecutive pits immediately behind it with 4 seeds are also captured.',
            'Do not starve your opponent: You must sow seeds to feed them if their side is empty.',
        ],
        strategyTips: [
            { icon: 'bi-gem', title: 'Hunt 3-Seed Pits (Ojú Mẹ́ta)', desc: 'Pits with 3 seeds are primed for capture — calculate when your sowing hand will land on them.' },
            { icon: 'bi-arrow-left-right', title: 'Build Accumulator Pits (Ọlọ́kọ)', desc: 'Hoard 10 to 14 seeds in a home pit to lap the board and trigger mass chain captures.' },
            { icon: 'bi-shield-shaded', title: 'Guard Against Chain Raids', desc: 'Avoid leaving multiple consecutive pits on your side with 3 seeds where an opponent can harvest 8 or 12 seeds in one turn.' },
            { icon: 'bi-heart-fill', title: 'Honor the Starvation Rule', desc: 'In Yorùbá custom, starving an opponent is illegal. Always leave an open seed pathway.' },
        ],
        proTip: 'Ọlọ́gbọ́n l’ó ń ta Ayò — Wisdom and foresight guide the true champion. 25 seeds guarantees victory!',
        totalSteps: 8,
        intervalMs: 800,
    },
    minesweeper: {
        name: 'Minesweeper',
        subtitle: 'Uncover all safe squares without hitting a mine',
        rules: [
            'Tap a square to reveal what is underneath.',
            'Numbers tell you how many hidden mines touch that square (1 to 8).',
            'Toggle to Flag Mode (🚩) or right-click to protect squares you suspect contain mines.',
            'Clear all safe squares across the 8×8 grid to win!',
        ],
        strategyTips: [
            { icon: 'bi-shield-check', title: 'The "1-1" Flat Wall Pattern', desc: 'When two 1s sit against a flat unopened wall, the 3rd square out is 100% safe to click.' },
            { icon: 'bi-diagram-2', title: 'The "1-2" Edge Clue', desc: 'When a 1 and a 2 sit side-by-side on an edge, the mine is always under the square touching the 2.' },
            { icon: 'bi-bullseye', title: 'Corner Freebies', desc: 'A 1 in an outer corner touching only one single unrevealed tile means that tile is definitely a mine!' },
            { icon: 'bi-water', title: 'Zero Cascade Rewards', desc: 'Hitting an empty 0 square clears a whole cluster automatically — use that open ground to expand safely.' },
        ],
        proTip: 'Never guess unless mathematically forced. Scan numbers that touch their exact quota of flags.',
        totalSteps: 8,
        intervalMs: 700,
    },
};

const KINZIP_DEMO_PATH = [
    [0, 0], [0, 1], [0, 2],
    [1, 2], [1, 1], [1, 0],
    [2, 0], [2, 1], [2, 2],
];

const KINZIP_NARRATIONS = [
    'Start at dot 1 in the top-left corner.',
    'Move right to (1, 2) — a gold wall blocks going straight down.',
    'Continue right to the corner so no cell is left behind.',
    'Step down into the middle row.',
    'Move left to reach dot 2 in order!',
    'Continue left to cover the rest of the middle row.',
    'Step down into the bottom-left corner.',
    'Move right along the bottom row.',
    'Reach dot 3! All 9 cells filled — puzzle complete!',
];

const KINGRID_STEPS = [
    { selected: null, grid: [[3, 0, 0], [0, 2, 0], [0, 0, 1]], note: 'Starting board: givens (3, 2, 1) are locked in place.' },
    { selected: [0, 1], grid: [[3, 1, 0], [0, 2, 0], [0, 0, 1]], note: 'Row 1 has 3 and clue says middle < right, so place 1.' },
    { selected: [0, 2], grid: [[3, 1, 2], [0, 2, 0], [0, 0, 1]], note: 'Only 2 remains in Row 1 — place 2 (1 < 2 satisfied!).' },
    { selected: [1, 0], grid: [[3, 1, 2], [1, 2, 0], [0, 0, 1]], note: 'Row 2 has 2 and clue says left < bottom-left, so place 1.' },
    { selected: [1, 2], grid: [[3, 1, 2], [1, 2, 3], [0, 0, 1]], note: 'Only 3 remains in Row 2 — place 3.' },
    { selected: [2, 0], grid: [[3, 1, 2], [1, 2, 3], [2, 0, 1]], note: 'Column 1 already has 3 and 1, so place 2.' },
    { selected: [2, 1], grid: [[3, 1, 2], [1, 2, 3], [2, 3, 1]], note: 'Place 3 in the final cell. Every row, column & clue solved!' },
];

const KINWORD_TILES = ['TEA', 'CROWN', 'SCONE', 'PALACE', 'BISCUIT', 'THRONE', 'JAM', 'SCEPTRE'];
const KINWORD_STEPS = [
    { selected: [], solved: [], note: 'Scan the board for 4 words that share a hidden theme.' },
    { selected: ['TEA'], solved: [], note: 'Tap "TEA"…' },
    { selected: ['TEA', 'SCONE'], solved: [], note: 'Tap "SCONE"…' },
    { selected: ['TEA', 'SCONE', 'BISCUIT'], solved: [], note: 'Tap "BISCUIT"…' },
    { selected: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], solved: [], note: 'All 4 selected — press Submit Group!' },
    { selected: [], solved: [{ title: 'British Afternoon Tea', words: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], tier: 1 }], note: 'Group 1 solved: British Afternoon Tea!' },
    { selected: ['CROWN', 'PALACE'], solved: [{ title: 'British Afternoon Tea', words: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], tier: 1 }], note: 'Now group the remaining royal words…' },
    { selected: ['CROWN', 'PALACE', 'THRONE', 'SCEPTRE'], solved: [{ title: 'British Afternoon Tea', words: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], tier: 1 }], note: '4 words selected — press Submit Group!' },
    { selected: [], solved: [{ title: 'British Afternoon Tea', words: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], tier: 1 }, { title: 'Royal Regalia', words: ['CROWN', 'PALACE', 'THRONE', 'SCEPTRE'], tier: 2 }], note: 'All groups solved! You win!' },
    { selected: [], solved: [{ title: 'British Afternoon Tea', words: ['TEA', 'SCONE', 'BISCUIT', 'JAM'], tier: 1 }, { title: 'Royal Regalia', words: ['CROWN', 'PALACE', 'THRONE', 'SCEPTRE'], tier: 2 }], note: 'All groups solved! Ready to play the real board.' },
];

const KINPULSE_STEPS = [
    { phase: 'Watch', active: -1, note: 'Press Start Sequence to watch the platform play.' },
    { phase: 'Watch', active: 0, note: 'Round 1 (Watch): Pad 1 lights up & plays a tone.' },
    { phase: 'Echo', active: 0, note: 'Round 1 (Your turn): Tap Pad 1 to echo it back.' },
    { phase: 'Watch', active: 0, note: 'Round 2 (Watch): Pad 1 lights up first…' },
    { phase: 'Watch', active: 2, note: 'Round 2 (Watch): …followed by Pad 3!' },
    { phase: 'Echo', active: 0, note: 'Round 2 (Your turn): Tap Pad 1…' },
    { phase: 'Echo', active: 2, note: 'Round 2 (Your turn): …then tap Pad 3!' },
    { phase: 'Watch', active: 1, note: 'Round 3 adds Pad 2 to the end (1 → 3 → 2).' },
    { phase: 'Echo', active: 1, note: 'Echo all 3 pads in order to clear the round!' },
    { phase: 'Won', active: -1, note: 'Sequence mastered! Ready to play KinPulse.' },
];

export function HowToPlayModal({ initialGame = 'kinzip', onClose }) {
    const [demoGame, setDemoGame] = useState(GAME_GUIDES[initialGame] ? initialGame : 'kinzip');
    const [activeView, setActiveView] = useState('demo'); // 'demo' | 'strategy'
    const [step, setStep] = useState(0);
    const [playing, setPlaying] = useState(true);
    const [soundOn, setSoundOn] = useState(false);
    const timerRef = useRef(null);

    const guide = GAME_GUIDES[demoGame] || GAME_GUIDES.kinzip;

    useEffect(() => {
        setStep(0);
        setPlaying(true);
    }, [demoGame]);

    useEffect(() => {
        const onKeyDown = (e) => {
            if (e.key === 'Escape' && onClose) {
                onClose();
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [onClose]);

    useEffect(() => {
        if (timerRef.current) {
            window.clearInterval(timerRef.current);
            timerRef.current = null;
        }
        if (!playing) return undefined;

        timerRef.current = window.setInterval(() => {
            setStep((prev) => {
                const next = prev + 1;
                if (next >= guide.totalSteps) {
                    setPlaying(false);
                    if (soundOn) playArcadeSfx('complete');
                    return guide.totalSteps - 1;
                }
                if (soundOn) {
                    if (demoGame === 'kinpulse') {
                        const pulseStep = KINPULSE_STEPS[next];
                        if (pulseStep && pulseStep.active >= 0) {
                            playArcadeSfx('pulse', pulseStep.active);
                        }
                    } else {
                        playArcadeSfx('connect', next);
                    }
                }
                return next;
            });
        }, guide.intervalMs);

        return () => {
            if (timerRef.current) {
                window.clearInterval(timerRef.current);
                timerRef.current = null;
            }
        };
    }, [playing, demoGame, guide.totalSteps, guide.intervalMs, soundOn]);

    const handleReplay = () => {
        setStep(0);
        setPlaying(true);
        setSoundOn(true);
    };

    const renderDemoBoard = () => {
        if (demoGame === 'kinzip') {
            const visited = KINZIP_DEMO_PATH.slice(0, step + 1);
            const visitedSet = new Set(visited.map(([r, c]) => `${r},${c}`));
            const head = visited[visited.length - 1];
            const cpLookup = { '0,0': 1, '1,1': 2, '2,2': 3 };

            return (
                <div className={styles.demoBoardCenter}>
                    <div className={styles.demoZipGrid} role="img" aria-label="KinZip live platform demonstration">
                        {[0, 1, 2].map((r) => [0, 1, 2].map((c) => {
                            const key = `${r},${c}`;
                            const isVisited = visitedSet.has(key);
                            const isHead = head && head[0] === r && head[1] === c;
                            const hasBottomWall = r === 0 && c === 1;
                            const hasTopWall = r === 1 && c === 1;
                            const cp = cpLookup[key];
                            return (
                                <div
                                    key={key}
                                    className={`${styles.demoZipCell} ${isVisited ? styles.demoZipVisited : ''} ${isHead ? styles.demoZipHead : ''} ${hasBottomWall ? styles.wallBottom : ''} ${hasTopWall ? styles.wallTop : ''}`}
                                >
                                    {cp !== undefined && (
                                        <span className={`${styles.demoZipDot} ${isVisited ? styles.zipDotActive : ''}`}>
                                            {cp}
                                        </span>
                                    )}
                                </div>
                            );
                        }))}
                    </div>
                    <p className={styles.demoNarration} aria-live="polite">
                        <strong>Step {step + 1}/{guide.totalSteps}:</strong> {KINZIP_NARRATIONS[step] || ''}
                    </p>
                </div>
            );
        }

        if (demoGame === 'kingrid') {
            const current = KINGRID_STEPS[step] || KINGRID_STEPS[0];
            return (
                <div className={styles.demoBoardCenter}>
                    <div className={styles.demoLogicGrid} role="img" aria-label="KinGrid live platform demonstration">
                        {current.grid.map((row, r) => row.map((val, c) => {
                            const isGiven = (r === 0 && c === 0) || (r === 1 && c === 1) || (r === 2 && c === 2);
                            const isSelected = current.selected && current.selected[0] === r && current.selected[1] === c;
                            return (
                                <div key={`${r}-${c}`} className={styles.logicCellWrap}>
                                    <div className={`${styles.demoLogicCell} ${isGiven ? styles.logicCellGiven : ''} ${isSelected ? styles.logicCellSelected : ''}`}>
                                        {val > 0 ? val : ''}
                                    </div>
                                    {r === 0 && c === 1 && <span className={styles.ineqRight} aria-hidden="true">&lt;</span>}
                                    {r === 1 && c === 0 && <span className={styles.ineqDown} aria-hidden="true">&lt;</span>}
                                </div>
                            );
                        }))}
                    </div>
                    <p className={styles.demoNarration} aria-live="polite">
                        <strong>Step {step + 1}/{guide.totalSteps}:</strong> {current.note}
                    </p>
                </div>
            );
        }

        if (demoGame === 'kinword') {
            const current = KINWORD_STEPS[step] || KINWORD_STEPS[0];
            const solvedWords = new Set(current.solved.flatMap((g) => g.words));
            const remaining = KINWORD_TILES.filter((w) => !solvedWords.has(w));
            return (
                <div className={styles.demoBoardCenter}>
                    {current.solved.length > 0 && (
                        <div className={styles.demoSolvedWrap}>
                            {current.solved.map((g) => (
                                <div key={g.title} className={`${styles.demoSolvedPill} ${g.tier === 1 ? styles.wordTier1 : styles.wordTier2}`}>
                                    <strong>{g.title}:</strong> {g.words.join(', ')}
                                </div>
                            ))}
                        </div>
                    )}
                    {remaining.length > 0 && (
                        <div className={styles.demoWordGrid} role="img" aria-label="KinWord live platform demonstration">
                            {remaining.map((w) => {
                                const isSel = current.selected.includes(w);
                                return (
                                    <div key={w} className={`${styles.demoWordTile} ${isSel ? styles.wordTileSelected : ''}`}>
                                        {w}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                    <p className={styles.demoNarration} aria-live="polite">
                        <strong>Step {step + 1}/{guide.totalSteps}:</strong> {current.note}
                    </p>
                </div>
            );
        }

        // KinPulse
        const current = KINPULSE_STEPS[step] || KINPULSE_STEPS[0];
        const padColours = ['#dc2626', '#b45309', '#059669', '#2563eb'];
        return (
            <div className={styles.demoBoardCenter}>
                <span className={styles.demoPhaseBadge}>
                    {current.phase === 'Watch' ? '👀 Platform Watching' : current.phase === 'Echo' ? '👆 Platform Echoing' : '🏆 Round Cleared'}
                </span>
                <div className={styles.demoPulseGrid} role="img" aria-label="KinPulse live platform demonstration">
                    {padColours.map((col, idx) => {
                        const isLit = current.active === idx;
                        return (
                            <div
                                key={idx}
                                className={`${styles.demoPulsePad} ${isLit ? styles.pulsePadActive : ''}`}
                                style={{ '--pad-colour': col }}
                            >
                                <span>{idx + 1}</span>
                            </div>
                        );
                    })}
                </div>
                <p className={styles.demoNarration} aria-live="polite">
                    <strong>Step {step + 1}/{guide.totalSteps}:</strong> {current.note}
                </p>
            </div>
        );
    };

    return (
        <div className={styles.modalBackdrop} role="dialog" aria-modal="true" aria-labelledby="how-to-play-title" onClick={onClose}>
            <div className={`${styles.modalCard} ${styles.howToPlayCard}`} onClick={(e) => e.stopPropagation()}>
                <div className={styles.modalHeader}>
                    <div>
                        <span className={styles.heroEyebrow} style={{ color: '#b45309' }}>INTERACTIVE GUIDE &amp; LIVE DEMO</span>
                        <h2 id="how-to-play-title" className={styles.modalTitle}>How to Play {guide.name}</h2>
                    </div>
                    <button type="button" className={styles.closeBtn} onClick={onClose} aria-label="Close How to Play guide">
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                {/* Game Switcher Pills inside Modal */}
                <div className={styles.demoTabs} data-scroll-affordance="true" role="tablist" aria-label="Choose a game guide">
                    {Object.entries(GAME_GUIDES).map(([slug, info]) => (
                        <button
                            key={slug}
                            type="button"
                            role="tab"
                            aria-selected={demoGame === slug}
                            className={`${styles.demoTabBtn} ${demoGame === slug ? styles.demoTabActive : ''}`}
                            onClick={() => setDemoGame(slug)}
                        >
                            {info.name}
                        </button>
                    ))}
                </div>

                {/* Segmented View Switcher: Rules & Live Demo vs. How to Think */}
                <div className={styles.howToViewToggle} role="tablist" aria-label="Guide view mode">
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeView === 'demo'}
                        className={`${styles.howToViewBtn} ${activeView === 'demo' ? styles.howToViewBtnActive : ''}`}
                        onClick={() => setActiveView('demo')}
                    >
                        <i className="bi bi-play-circle-fill" aria-hidden="true" />
                        <span>Rules &amp; Live Demo</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeView === 'strategy'}
                        className={`${styles.howToViewBtn} ${activeView === 'strategy' ? styles.howToViewBtnActive : ''}`}
                        onClick={() => setActiveView('strategy')}
                    >
                        <i className="bi bi-lightbulb-fill" aria-hidden="true" />
                        <span>How to Think</span>
                    </button>
                </div>

                {activeView === 'demo' ? (
                    <div className={styles.howToPlayBody}>
                        {/* Left: Rules */}
                        <div className={styles.howToRulesCol}>
                            <p className={styles.demoSubtitle}>{guide.subtitle}</p>
                            <ol className={styles.howToSteps}>
                                {guide.rules.map((rule, idx) => (
                                    <li key={idx} className={styles.howToStepItem}>
                                        <span className={styles.howToStepNum}>{idx + 1}</span>
                                        <span>{rule}</span>
                                    </li>
                                ))}
                            </ol>
                        </div>

                        {/* Right: Live Platform Auto-Play Demo */}
                        <div className={styles.howToDemoCol}>
                            <div className={styles.demoColHeader}>
                                <span className={styles.demoLivePill}>
                                    <i className="bi bi-robot" aria-hidden="true" /> Watch Platform Play
                                </span>
                                <div className={styles.demoControls}>
                                    <button
                                        type="button"
                                        className={styles.demoCtrlBtn}
                                        onClick={() => {
                                            setSoundOn(true);
                                            if (step >= guide.totalSteps - 1) {
                                                setStep(0);
                                                setPlaying(true);
                                            } else {
                                                setPlaying((p) => !p);
                                            }
                                        }}
                                    >
                                        <i className={`bi ${playing ? 'bi-pause-fill' : 'bi-play-fill'}`} aria-hidden="true" />
                                        <span>{playing ? 'Pause' : 'Play'}</span>
                                    </button>
                                    <button type="button" className={styles.demoCtrlBtn} onClick={handleReplay}>
                                        <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                                        <span>Replay</span>
                                    </button>
                                </div>
                            </div>

                            {renderDemoBoard()}
                        </div>
                    </div>
                ) : (
                    <div className={styles.strategyCodexWrap} role="region" aria-label={`${guide.name} deduction strategy`}>
                        <div className={styles.strategyIntro}>
                            <span className={styles.strategyIntroBadge}>
                                <i className="bi bi-compass-fill me-1" aria-hidden="true" />
                                DEDUCTION PLAYBOOK &amp; MENTAL HEURISTICS
                            </span>
                            <p className={styles.strategyIntroText}>
                                Every puzzle in <strong>{guide.name}</strong> is deterministically constructed and 100% solvable through pure logic. Never guess—use this inspection checklist to spot free moves instantly.
                            </p>
                        </div>

                        <div className={styles.strategyGrid}>
                            {(guide.strategyTips || []).map((tip, idx) => (
                                <div key={idx} className={styles.strategyCard}>
                                    <div className={styles.strategyCardHead}>
                                        <span className={styles.strategyCardIcon}>
                                            <i className={`bi ${tip.icon}`} aria-hidden="true" />
                                        </span>
                                        <strong className={styles.strategyCardTitle}>{tip.title}</strong>
                                    </div>
                                    <p className={styles.strategyCardDesc}>{tip.desc}</p>
                                </div>
                            ))}
                        </div>

                        {guide.proTip && (
                            <div className={styles.strategyProTip}>
                                <i className="bi bi-star-fill text-warning me-2" aria-hidden="true" />
                                <span><strong>Golden Rule:</strong> {guide.proTip}</span>
                            </div>
                        )}
                    </div>
                )}

                <div className={styles.modalActions} style={{ marginTop: '14px', justifyContent: 'flex-end' }}>
                    <button type="button" className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary}`} onClick={onClose}>
                        <i className="bi bi-controller" aria-hidden="true" /> Got It — Play {guide.name} Now
                    </button>
                </div>
            </div>
        </div>
    );
}
