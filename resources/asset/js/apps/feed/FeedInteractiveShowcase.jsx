import { useState, useMemo } from 'preact/hooks';
import { memo } from 'preact/compat';
import styles from './FeedInteractiveShowcase.module.css';

/**
 * Catalogue of 12 Live-Playable In-Feed Advert Experiences:
 * - 6 Playable Family Arcade Mini-Games (category: 'game')
 * - 6 Live Interactive App Functionality Sandboxes (category: 'feature')
 *
 * All copy strictly follows British English (en-GB) and WCAG AA contrast rules.
 */
export const SHOWCASE_ITEMS = [
    // 1. GAME: Ayò Olópon
    {
        id: 'game_ayo',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'Ayò Olópon — Yoruba Mancala Strategy',
        description: 'Sow seeds anti-clockwise across the carved wooden pits right here on your feed! Tap a glowing southern pit below to capture houses of 4 seeds.',
        chips: ['Live Multiplayer & ATG Bot', 'Yoruba Cultural Heritage', 'Daily Crown Leaderboard'],
        ctaLabel: 'Play Full Ayò Olópon',
        ctaUrl: '/arcade?game=ayo',
        ctaIcon: 'bi-controller',
        shortLabel: 'Ayò Olópon',
    },
    // 2. FEATURE: Respectful Kinship @Mentions
    {
        id: 'feat_kinship_mentions',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: 'Respectful Kinship @Mentions & Elder Titles',
        description: 'FamilyPlatform automatically honours elders in posts and comments. Tap any family reader perspective below to watch @mentions adapt live!',
        chips: ['Auto @Dad / @Mum / @Grandpa', '7-Gen Tree Aware', 'Personal Toggle in Settings'],
        ctaLabel: 'Explore Kinship Calculator',
        ctaUrl: '/kinship-calculator',
        ctaIcon: 'bi-at',
        shortLabel: 'Kinship @Mentions',
    },
    // 3. GAME: KinWordie
    {
        id: 'game_wordie',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'KinWordie — 5-Letter Family Word Duel',
        description: 'Can you crack today’s 5-letter heritage word in one move? Tap a candidate word below to flip the 3D tiles green and gold!',
        chips: ['60-Second Word Sprint', 'Zero-Spoiler Share Card', 'Family Streak Badges'],
        ctaLabel: 'Play Full KinWordie',
        ctaUrl: '/arcade?game=wordie',
        ctaIcon: 'bi-alphabet-uppercase',
        shortLabel: 'KinWordie',
    },
    // 4. FEATURE: 7-Generation Dynasty Tree Explorer
    {
        id: 'feat_dynasty_tree',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: '7-Generation Dynasty Tree & Lineage Explorer',
        description: 'Navigate 7 generations upwards (-7) and downwards (+7). Tap any relative node in the live mini-tree below to inspect your exact kinship bond!',
        chips: ['Ego-Centric Canvas', 'Dual Married & Birth Codes', 'PDF Poster Export'],
        ctaLabel: 'Open 7-Gen Family Tree',
        ctaUrl: '/organogram',
        ctaIcon: 'bi-diagram-3-fill',
        shortLabel: '7-Gen Family Tree',
    },
    // 5. GAME: KinPulse
    {
        id: 'game_kinpulse',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'KinPulse — 4-Pad Rhythm & Memory Echo',
        description: 'Echo the family sequence: Pad 1 → Pad 3 → Pad 2 → Pad 4! Tap the 4 harmonic colour pads below in order to build your live combo streak.',
        chips: ['Harmonic Web Audio', 'Reflex & Memory Training', 'All-Generation Fun'],
        ctaLabel: 'Play Full KinPulse',
        ctaUrl: '/arcade?game=kinpulse',
        ctaIcon: 'bi-lightning-charge-fill',
        shortLabel: 'KinPulse',
    },
    // 6. FEATURE: Secret Milestone Group Card & Goodwill Video
    {
        id: 'feat_secret_card',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: 'Secret Milestone Group Cards & Video Drops',
        description: 'Co-sign surprise birthday & anniversary cards right from the feed. Tap a blessing below and test the Celebrant Privacy Shield live!',
        chips: ['Hidden From Celebrant', 'Group Signatures & Voice', 'Auto-Reveal on Milestone Day'],
        ctaLabel: 'Explore Milestones & Wishlists',
        ctaUrl: '/wishlists',
        ctaIcon: 'bi-envelope-heart-fill',
        shortLabel: 'Secret Group Cards',
    },
    // 7. GAME: KinZip
    {
        id: 'game_kinzip',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'KinZip — Golden Circuit Path Labyrinth',
        description: 'Connect checkpoints 1 → 2 → 3 → 4 without crossing walls! Tap the next checkpoint in sequence below to complete the circuit.',
        chips: ['Hamiltonian Logic Puzzle', 'Ghost Replay Races', 'KinZip Grandmaster Badge'],
        ctaLabel: 'Play Full KinZip',
        ctaUrl: '/arcade?game=kinzip',
        ctaIcon: 'bi-bezier2',
        shortLabel: 'KinZip',
    },
    // 8. FEATURE: Living Heirloom AI & Ancestral Calibration
    {
        id: 'feat_heirloom_ai',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: 'Living Heirloom AI & Age Progression Studio',
        description: 'Animate treasured ancestor portraits, preview +20/+40 year age progression, or calibrate unphotographed ancestors. Test the live modes below!',
        chips: ['Talking Heritage Portraits', '+10 to +40 Yr Progression', 'Ancestral Face Synthesis'],
        ctaLabel: 'Launch Heirloom AI Studio',
        ctaUrl: '/family/living-heirloom',
        ctaIcon: 'bi-magic',
        shortLabel: 'Heirloom AI',
    },
    // 9. GAME: Cheeseboard Queens
    {
        id: 'game_cheeseboard',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'Cheeseboard — Royal Queens Dynasty Logic',
        description: 'Place 1 Royal Crown per coloured region so no two crowns share a row, column, or diagonal. Tap a region below to crown your dynasty!',
        chips: ['Zero-Touch Queen Placement', 'Tactile Region Deduction', 'Cheeseboard Sovereign Title'],
        ctaLabel: 'Play Full Cheeseboard',
        ctaUrl: '/arcade?game=cheeseboard',
        ctaIcon: 'bi-shield-shaded',
        shortLabel: 'Cheeseboard',
    },
    // 10. FEATURE: Family RoundTable & Consensus Polls
    {
        id: 'feat_roundtable',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: 'Family RoundTable & Live Consensus Voting',
        description: 'Organise family reunions, heritage projects, and household decisions transparently. Cast a live test vote below to watch the quorum tally update!',
        chips: ['Encrypted Family Proposals', 'Instant Quorum Tallies', 'Action Commitments'],
        ctaLabel: 'Open Family RoundTable',
        ctaUrl: '/family/roundtable',
        ctaIcon: 'bi-shield-check',
        shortLabel: 'Family RoundTable',
    },
    // 11. GAME: Family Chess
    {
        id: 'game_chess',
        category: 'game',
        eyebrow: 'PLAYABLE ARCADE MINI-GAME',
        title: 'Family Chess — Mate-in-1 Tactical Challenge',
        description: 'White to move and deliver checkmate in 1 move! Tap your chosen chess move below to execute it on the board.',
        chips: ['Live 2-Player & ATG Engine', 'Tactical Endgame Puzzles', 'Chess Grandmaster Crown'],
        ctaLabel: 'Play Full Family Chess',
        ctaUrl: '/arcade?game=chess',
        ctaIcon: 'bi-suit-club-fill',
        shortLabel: 'Family Chess',
    },
    // 12. FEATURE: 4-Quadrant Video Studio & Director Mode
    {
        id: 'feat_director_mode',
        category: 'feature',
        eyebrow: 'LIVE APP FUNCTIONALITY',
        title: 'Director Mode & 4-Quadrant Family Video Studio',
        description: 'Build multi-shot heirloom films in your browser at zero cost. Tap story tags below (@grandparents, @reunion, @homestead) to assemble a live scene!',
        chips: ['60fps Browser NLE', 'H.264 MP4 & .SRT Export', '1-Click Magic Recipes'],
        ctaLabel: 'Open Director Mode Studio',
        ctaUrl: '/director-mode',
        ctaIcon: 'bi-film',
        shortLabel: 'Director Mode',
    },
];

export const FeedInteractiveShowcase = memo(function FeedInteractiveShowcase({ slotIndex = 0, initialCategory = 'all' }) {
    const startIdx = Math.abs(Number(slotIndex) || 0) % SHOWCASE_ITEMS.length;
    const [activeIndex, setActiveIndex] = useState(startIdx);
    const [categoryFilter, setCategoryFilter] = useState(initialCategory);

    // Interactive state for the 6 playable mini-games & 6 live feature sandboxes
    const [ayoPits, setAyoPits] = useState([4, 4, 4, 3, 4, 4]);
    const [ayoCaptured, setAyoCaptured] = useState(12);
    const [ayoFeedback, setAyoFeedback] = useState('Tap any pit above to sow seeds anti-clockwise & capture!');

    const [wordieGuess, setWordieGuess] = useState('SHARE');
    const [wordieSolved, setWordieSolved] = useState(false);

    const [pulseProgress, setPulseProgress] = useState(0);
    const [pulseStatus, setPulseStatus] = useState('Target sequence: Pad 1 → Pad 3 → Pad 2 → Pad 4');

    const [kinzipStep, setKinzipStep] = useState(1);

    const [crownsPlaced, setCrownsPlaced] = useState(['Ruby Region']);

    const [chessMove, setChessMove] = useState('♛h7#');

    const [kinshipPerspective, setKinshipPerspective] = useState('grandchild');
    const [selectedTreeNode, setSelectedTreeNode] = useState('grandpa');
    const [cardStamp, setCardStamp] = useState('Happy 70th Birthday, Grandpa!');
    const [privacyShieldViewer, setPrivacyShieldViewer] = useState('relative');
    const [heirloomMode, setHeirloomMode] = useState('motion');
    const [roundtableVote, setRoundtableVote] = useState('lagos');
    const [directorTags, setDirectorTags] = useState(['@grandparents', '@reunion']);

    const filteredItems = useMemo(() => {
        if (categoryFilter === 'game') {
            return SHOWCASE_ITEMS.filter((item) => item.category === 'game');
        }
        if (categoryFilter === 'feature') {
            return SHOWCASE_ITEMS.filter((item) => item.category === 'feature');
        }
        return SHOWCASE_ITEMS;
    }, [categoryFilter]);

    const currentItem = useMemo(() => {
        const direct = SHOWCASE_ITEMS[activeIndex] || SHOWCASE_ITEMS[0];
        if (categoryFilter === 'all' || direct.category === categoryFilter) {
            return direct;
        }
        return filteredItems[0] || direct;
    }, [activeIndex, categoryFilter, filteredItems]);

    const handleSelectCategory = (nextCat) => {
        setCategoryFilter(nextCat);
        if (nextCat !== 'all') {
            const firstMatchIdx = SHOWCASE_ITEMS.findIndex((i) => i.category === nextCat);
            if (firstMatchIdx !== -1) {
                setActiveIndex(firstMatchIdx);
            }
        }
    };

    const handleCycleNext = () => {
        if (categoryFilter === 'all') {
            setActiveIndex((prev) => (prev + 1) % SHOWCASE_ITEMS.length);
            return;
        }
        const currentPos = filteredItems.findIndex((i) => i.id === currentItem.id);
        const nextItem = filteredItems[(currentPos + 1) % filteredItems.length] || SHOWCASE_ITEMS[0];
        const globalIdx = SHOWCASE_ITEMS.findIndex((i) => i.id === nextItem.id);
        setActiveIndex(globalIdx !== -1 ? globalIdx : 0);
    };

    // Game 1 handler: Ayò Olópon pit tap
    const handleSowAyoPit = (pitIdx) => {
        setAyoPits((prev) => {
            const seeds = prev[pitIdx] || 4;
            const next = prev.map((v, idx) => (idx === pitIdx ? 0 : v + (idx > pitIdx && idx <= pitIdx + seeds ? 1 : 0)));
            return next;
        });
        setAyoCaptured((c) => c + 4);
        setAyoFeedback(`Jẹ mẹ́rin! You sowed Pit ${pitIdx + 1} and captured +4 seeds into your treasury!`);
    };

    // Game 3 handler: KinPulse pad tap
    const pulseSequence = [0, 2, 1, 3]; // Pad 1 -> Pad 3 -> Pad 2 -> Pad 4
    const handleTapPulsePad = (padIdx) => {
        const expected = pulseSequence[pulseProgress];
        if (padIdx === expected) {
            const nextStep = pulseProgress + 1;
            if (nextStep >= pulseSequence.length) {
                setPulseProgress(0);
                setPulseStatus('Perfect 4-Pad Harmonic Echo! +250 Rhythm Points unlocked!');
            } else {
                setPulseProgress(nextStep);
                setPulseStatus(`Spot on! Next in sequence: Pad ${pulseSequence[nextStep] + 1}`);
            }
        } else {
            setPulseProgress(0);
            setPulseStatus(`Try again! Start with Pad 1 → Pad 3 → Pad 2 → Pad 4`);
        }
    };

    const renderInteractiveStage = (item) => {
        switch (item.id) {
            case 'game_ayo':
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_ayo">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-hand-index-thumb-fill" aria-hidden="true" />
                                <span>LIVE PLAY: Tap a Pit to Sow &amp; Capture</span>
                            </span>
                            <span className={styles.stageScorePill}>Treasury: {ayoCaptured} Seeds</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Ayò Olópon interactive pits">
                                {ayoPits.map((seeds, idx) => (
                                    <button
                                        key={`ayo-pit-${idx}`}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${seeds === 0 ? styles.tileGreen : styles.tileGold}`}
                                        onClick={() => handleSowAyoPit(idx)}
                                        aria-label={`Sow Ayò Pit ${idx + 1} containing ${seeds} seeds`}
                                    >
                                        <span>Pit {idx + 1}:</span>
                                        <strong>{seeds}</strong>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{ayoFeedback}</div>
                    </div>
                );

            case 'game_wordie': {
                const wordOptions = [
                    { word: 'SHARE', states: ['miss', 'gold', 'green', 'green', 'gold'], msg: '2 Green + 2 Gold! Tap CROWN to solve it!' },
                    { word: 'CROWN', states: ['green', 'green', 'green', 'green', 'green'], msg: 'Solved in 1 Tap! CROWN is the winning heritage word!' },
                    { word: 'FEAST', states: ['miss', 'gold', 'green', 'miss', 'miss'], msg: 'A is in the right spot! Tap CROWN to win!' },
                ];
                const activeWordObj = wordOptions.find((w) => w.word === wordieGuess) || wordOptions[0];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_wordie">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-hand-index-thumb-fill" aria-hidden="true" />
                                <span>LIVE PLAY: Tap a 5-Letter Guess</span>
                            </span>
                            <span className={styles.stageScorePill}>{wordieSolved ? 'Solved! ★' : '1 Move Left'}</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} aria-label="Wordie letter tiles">
                                {activeWordObj.word.split('').map((letter, idx) => {
                                    const st = activeWordObj.states[idx];
                                    const tileTone = st === 'green' ? styles.tileGreen : (st === 'gold' ? styles.tileGold : '');
                                    return (
                                        <span
                                            key={`w-tile-${idx}`}
                                            className={`${styles.tactileTileBtn} ${tileTone}`}
                                            style={{ pointerEvents: 'none' }}
                                        >
                                            {letter}
                                        </span>
                                    );
                                })}
                            </div>
                            <div className={styles.playBtnGrid} role="group" aria-label="Choose a 5-letter word guess">
                                {wordOptions.map((opt) => (
                                    <button
                                        key={opt.word}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${wordieGuess === opt.word ? styles.tileGold : ''}`}
                                        onClick={() => {
                                            setWordieGuess(opt.word);
                                            if (opt.word === 'CROWN') setWordieSolved(true);
                                        }}
                                    >
                                        <span>Try &ldquo;{opt.word}&rdquo;</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{activeWordObj.msg}</div>
                    </div>
                );
            }

            case 'game_kinpulse': {
                const pads = [
                    { idx: 0, label: 'Pad 1 (Crimson)', cls: styles.tileCrimson },
                    { idx: 1, label: 'Pad 2 (Gold)', cls: styles.tileGold },
                    { idx: 2, label: 'Pad 3 (Emerald)', cls: styles.tileGreen },
                    { idx: 3, label: 'Pad 4 (Sapphire)', cls: '' },
                ];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_kinpulse">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-music-note-beamed" aria-hidden="true" />
                                <span>LIVE PLAY: Tap Pads in Sequence (1 → 3 → 2 → 4)</span>
                            </span>
                            <span className={styles.stageScorePill}>Combo: {pulseProgress}/4</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="KinPulse rhythm pads">
                                {pads.map((pad) => (
                                    <button
                                        key={`pulse-pad-${pad.idx}`}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${pad.cls}`}
                                        onClick={() => handleTapPulsePad(pad.idx)}
                                    >
                                        <i className="bi bi-soundwave" aria-hidden="true" />
                                        <span>{pad.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{pulseStatus}</div>
                    </div>
                );
            }

            case 'game_kinzip':
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_kinzip">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-bezier2" aria-hidden="true" />
                                <span>LIVE PLAY: Tap Checkpoints 1 → 2 → 3 → 4</span>
                            </span>
                            <span className={styles.stageScorePill}>Node {kinzipStep}/4 Linked</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="KinZip circuit checkpoints">
                                {[1, 2, 3, 4].map((nodeNum) => {
                                    const isLinked = nodeNum <= kinzipStep;
                                    const isNext = nodeNum === kinzipStep + 1 || (kinzipStep === 4 && nodeNum === 1);
                                    return (
                                        <button
                                            key={`kz-${nodeNum}`}
                                            type="button"
                                            className={`${styles.tactileTileBtn} ${isLinked ? styles.tileGreen : (isNext ? styles.tileGold : '')}`}
                                            onClick={() => setKinzipStep(nodeNum)}
                                        >
                                            <i className={`bi ${isLinked ? 'bi-check-circle-fill' : 'bi-circle'}`} aria-hidden="true" />
                                            <span>Node {nodeNum}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">
                            {kinzipStep >= 4
                                ? 'Circuit Complete! All 4 family checkpoints connected without crossing walls!'
                                : `Tap Node ${kinzipStep + 1} next to extend your golden Hamiltonian path.`}
                        </div>
                    </div>
                );

            case 'game_cheeseboard': {
                const regions = ['Ruby Region', 'Gold Region', 'Emerald Region', 'Sapphire Region'];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_cheeseboard">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-shield-shaded" aria-hidden="true" />
                                <span>LIVE PLAY: Crown All 4 Dynasty Regions</span>
                            </span>
                            <span className={styles.stageScorePill}>{crownsPlaced.length}/4 Crowns</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Cheeseboard dynasty regions">
                                {regions.map((region) => {
                                    const hasCrown = crownsPlaced.includes(region);
                                    return (
                                        <button
                                            key={region}
                                            type="button"
                                            className={`${styles.tactileTileBtn} ${hasCrown ? styles.tileGold : ''}`}
                                            onClick={() => {
                                                setCrownsPlaced((prev) => (
                                                    prev.includes(region)
                                                        ? prev.filter((r) => r !== region)
                                                        : [...prev, region]
                                                ));
                                            }}
                                            aria-pressed={hasCrown}
                                        >
                                            <span>{hasCrown ? '👑' : '➕'}</span>
                                            <span>{region}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">
                            {crownsPlaced.length === 4
                                ? 'All 4 Royal Crowns placed with zero row, column, or diagonal clashes!'
                                : `Tap the remaining ${4 - crownsPlaced.length} region(s) to place your non-attacking Queens.`}
                        </div>
                    </div>
                );
            }

            case 'game_chess': {
                const moves = [
                    { code: '♛h7#', label: '1. ♛h7# (Queen Checkmate)', win: true, note: 'Checkmate! The Queen supported by the Bishop seals the royal victory!' },
                    { code: '♘f6+', label: '1. ♘f6+ (Knight Fork)', win: false, note: 'Royal check, but the black King escapes to h8. Try 1. ♛h7#!' },
                    { code: '♖d8+', label: '1. ♖d8+ (Back-Rank Check)', win: false, note: 'Blocked by the Bishop on f8. Tap 1. ♛h7# for instant checkmate!' },
                ];
                const currentChess = moves.find((m) => m.code === chessMove) || moves[0];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-game_chess">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-trophy-fill" aria-hidden="true" />
                                <span>LIVE PLAY: Choose the Winning Chess Move</span>
                            </span>
                            <span className={styles.stageScorePill}>{currentChess.win ? 'Checkmate! ★' : 'In Play'}</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Chess tactical candidate moves">
                                {moves.map((m) => (
                                    <button
                                        key={m.code}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${chessMove === m.code ? (m.win ? styles.tileGreen : styles.tileGold) : ''}`}
                                        onClick={() => setChessMove(m.code)}
                                    >
                                        <span>{m.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{currentChess.note}</div>
                    </div>
                );
            }

            case 'feat_kinship_mentions': {
                const perspectives = [
                    { key: 'grandchild', label: 'Grandchild Reader', rendered: 'Sunday lunch at @Grandpa’s house with @Aunty Funke!', rule: 'Grandchildren (-2 gen) automatically see @Grandpa instead of raw first names.' },
                    { key: 'child', label: 'Son / Daughter Reader', rendered: 'Sunday lunch at @Dad’s house with @Sister Funke!', rule: 'Children (-1 gen) automatically read @Dad or @Mum on the exact same post.' },
                    { key: 'nephew', label: 'Nephew / Niece Reader', rendered: 'Sunday lunch at @Uncle Ade’s house with @Mum!', rule: 'Nephews & nieces see @Uncle Ade while their mother renders as @Mum.' },
                    { key: 'peer', label: 'Cousin / Peer Reader', rendered: 'Sunday lunch at @Adebowale Olaogun’s house with @Funke!', rule: 'Same-generation peers or users who toggle off honorifics see the full name.' },
                ];
                const activePersp = perspectives.find((p) => p.key === kinshipPerspective) || perspectives[0];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_kinship_mentions">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-sliders" aria-hidden="true" />
                                <span>TRY LIVE: Switch Family Reader Perspective</span>
                            </span>
                            <span className={styles.stageScorePill}>Tree-Aware AI</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Select family reader perspective">
                                {perspectives.map((p) => (
                                    <button
                                        key={p.key}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${kinshipPerspective === p.key ? styles.tileGold : ''}`}
                                        onClick={() => setKinshipPerspective(p.key)}
                                        aria-pressed={kinshipPerspective === p.key}
                                    >
                                        <span>{p.label}</span>
                                    </button>
                                ))}
                            </div>
                            <div className={styles.feedbackBanner}>
                                <span>Live Post Preview: &ldquo;{activePersp.rendered}&rdquo;</span>
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{activePersp.rule}</div>
                    </div>
                );
            }

            case 'feat_dynasty_tree': {
                const nodes = [
                    { key: 'grandpa', label: '👴 Grandpa Ade (-2 Gen)', bond: 'Paternal Grandfather • 2 Steps Up • Lineage Custodian' },
                    { key: 'mum', label: '👩🏽 Mum Folake (-1 Gen)', bond: 'Mother • 1 Step Up • Bridges Birth & Married Family Codes' },
                    { key: 'aunty', label: '👩🏽‍🦱 Aunty Funke (-1 Gen)', bond: 'Paternal Aunt • Collateral Branch • Linked via Grandpa Ade' },
                    { key: 'child', label: '🧒🏽 Maya (+1 Gen)', bond: 'Daughter • 1 Step Down • Supervised Youth Sanctuary Ready' },
                ];
                const currentNode = nodes.find((n) => n.key === selectedTreeNode) || nodes[0];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_dynasty_tree">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-diagram-3-fill" aria-hidden="true" />
                                <span>TRY LIVE: Tap Any Relative Node to Trace Lineage</span>
                            </span>
                            <span className={styles.stageScorePill}>7-Gen Canvas</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Interactive family tree nodes">
                                {nodes.map((n) => (
                                    <button
                                        key={n.key}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${selectedTreeNode === n.key ? styles.tileGreen : ''}`}
                                        onClick={() => setSelectedTreeNode(n.key)}
                                        aria-pressed={selectedTreeNode === n.key}
                                    >
                                        <span>{n.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{currentNode.bond}</div>
                    </div>
                );
            }

            case 'feat_secret_card': {
                const stamps = [
                    'Happy 70th Birthday, Grandpa!',
                    'Happy Ruby Wedding Anniversary!',
                    'Congratulations on Graduation!',
                ];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_secret_card">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-shield-lock-fill" aria-hidden="true" />
                                <span>TRY LIVE: Sign a Card &amp; Test Celebrant Privacy Shield</span>
                            </span>
                            <span className={styles.stageScorePill}>
                                {privacyShieldViewer === 'celebrant' ? 'Hidden (Surprise Safe!)' : '14 Relatives Signed'}
                            </span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Choose greeting card blessing">
                                {stamps.map((s) => (
                                    <button
                                        key={s}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${cardStamp === s ? styles.tileGold : ''}`}
                                        onClick={() => setCardStamp(s)}
                                    >
                                        <span>✍🏽 {s}</span>
                                    </button>
                                ))}
                            </div>
                            <div className={styles.playBtnGrid} role="group" aria-label="Toggle viewer privacy perspective">
                                <button
                                    type="button"
                                    className={`${styles.tactileTileBtn} ${privacyShieldViewer === 'relative' ? styles.tileGreen : ''}`}
                                    onClick={() => setPrivacyShieldViewer('relative')}
                                >
                                    <span>👀 View as Co-Signer Relative</span>
                                </button>
                                <button
                                    type="button"
                                    className={`${styles.tactileTileBtn} ${privacyShieldViewer === 'celebrant' ? styles.tileCrimson : ''}`}
                                    onClick={() => setPrivacyShieldViewer('celebrant')}
                                >
                                    <span>🙈 View as Celebrant (Surprise Shield)</span>
                                </button>
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">
                            {privacyShieldViewer === 'celebrant'
                                ? '🔒 Celebrant Shield Active: The birthday celebrant or anniversary couple sees ZERO buttons or spoilers until delivery day!'
                                : `Unlocked for Co-Signers: "${cardStamp}" queued with 14 family signatures & 3 goodwill videos.`}
                        </div>
                    </div>
                );
            }

            case 'feat_heirloom_ai': {
                const modes = [
                    { key: 'motion', label: '✨ Living Portrait Motion', status: '60fps Natural Breathing, Warm Smile & Lip-Sync Voice Tribute ready.' },
                    { key: 'age40', label: '⏳ +30 Yr Age Progression', status: 'Cranial-invariant age forecast (+30 Years) computed with 96.4% structural fidelity.' },
                    { key: 'ancestral', label: '🧬 Reverse Ancestor Synthesis', status: 'Tree Calibration complete: Synthesising Great-Grandparent portrait from 4 descendants.' },
                ];
                const activeMode = modes.find((m) => m.key === heirloomMode) || modes[0];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_heirloom_ai">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-stars" aria-hidden="true" />
                                <span>TRY LIVE: Switch Heirloom AI Engine Mode</span>
                            </span>
                            <span className={styles.stageScorePill}>Zero SaaS Cost</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Heirloom AI engine modes">
                                {modes.map((m) => (
                                    <button
                                        key={m.key}
                                        type="button"
                                        className={`${styles.tactileTileBtn} ${heirloomMode === m.key ? styles.tileGold : ''}`}
                                        onClick={() => setHeirloomMode(m.key)}
                                        aria-pressed={heirloomMode === m.key}
                                    >
                                        <span>{m.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">{activeMode.status}</div>
                    </div>
                );
            }

            case 'feat_roundtable': {
                const options = [
                    { key: 'lagos', label: '🏖️ Lagos Beach Resort', votes: 18 },
                    { key: 'london', label: '🏰 Cotswolds Country Manor', votes: 14 },
                    { key: 'abuja', label: '🌴 Abuja Garden Villa', votes: 11 },
                ];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_roundtable">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-check2-square" aria-hidden="true" />
                                <span>TRY LIVE: Cast a Vote in the Family Reunion Poll</span>
                            </span>
                            <span className={styles.stageScorePill}>44 Relatives Voted</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Family RoundTable poll options">
                                {options.map((opt) => {
                                    const isPicked = roundtableVote === opt.key;
                                    const count = opt.votes + (isPicked ? 1 : 0);
                                    return (
                                        <button
                                            key={opt.key}
                                            type="button"
                                            className={`${styles.tactileTileBtn} ${isPicked ? styles.tileGreen : ''}`}
                                            onClick={() => setRoundtableVote(opt.key)}
                                            aria-pressed={isPicked}
                                        >
                                            <span>{opt.label}</span>
                                            <strong>({count})</strong>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">
                            Quorum Reached (88% participation)! Your live vote is tallied and synced to the Family Calendar.
                        </div>
                    </div>
                );
            }

            case 'feat_director_mode':
            default: {
                const storyBeats = ['@grandparents', '@reunion', '@homestead', '@elders', '@graduation'];
                return (
                    <div className={styles.stageBox} data-testid="interactive-stage-feat_director_mode">
                        <div className={styles.stageHeader}>
                            <span className={styles.stagePrompt}>
                                <i className="bi bi-camera-reels-fill" aria-hidden="true" />
                                <span>TRY LIVE: Toggle Story Beats for Your MP4 Film</span>
                            </span>
                            <span className={styles.stageScorePill}>{directorTags.length} Shots Queued</span>
                        </div>
                        <div className={styles.interactiveCanvas}>
                            <div className={styles.playBtnGrid} role="group" aria-label="Director Mode story beat tags">
                                {storyBeats.map((beat) => {
                                    const active = directorTags.includes(beat);
                                    return (
                                        <button
                                            key={beat}
                                            type="button"
                                            className={`${styles.tactileTileBtn} ${active ? styles.tileGold : ''}`}
                                            onClick={() => {
                                                setDirectorTags((prev) => (
                                                    prev.includes(beat)
                                                        ? (prev.length > 1 ? prev.filter((b) => b !== beat) : prev)
                                                        : [...prev, beat]
                                                ));
                                            }}
                                            aria-pressed={active}
                                        >
                                            <span>{active ? '🎬' : '➕'} {beat}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                        <div className={styles.feedbackBanner} role="status">
                            Storyboard Ready: {directorTags.join(' → ')} with Golden-Hour Ken Burns Pan &amp; auto-generated .SRT subtitles!
                        </div>
                    </div>
                );
            }
        }
    };

    const itemPosition = SHOWCASE_ITEMS.findIndex((i) => i.id === currentItem.id) + 1;

    return (
        <aside
            className={styles.showcaseCard}
            aria-label={`Interactive FamilyPlatform Showcase: ${currentItem.title}`}
            data-testid={`feed-interactive-showcase-${slotIndex}`}
            data-showcase-id={currentItem.id}
        >
            {/* Top Bar: Continuous Live Discovery Indicator + Category Filter Pills */}
            <div className={styles.topBar}>
                <div className={styles.badgeCluster}>
                    <span className={styles.livePill}>
                        <span className={styles.pulseDot} aria-hidden="true" />
                        <span>INTERACTIVE FEED SPOTLIGHT</span>
                    </span>
                    <span className={styles.counterLabel}>
                        Experience {itemPosition} of {SHOWCASE_ITEMS.length}
                    </span>
                </div>

                <div className={styles.categoryTabs} role="tablist" aria-label="Filter interactive showcase by games or app features">
                    <button
                        type="button"
                        role="tab"
                        aria-selected={categoryFilter === 'all'}
                        className={`${styles.modeTabBtn} ${categoryFilter === 'all' ? styles.modeTabBtnActive : ''}`}
                        onClick={() => handleSelectCategory('all')}
                    >
                        <i className="bi bi-stars" aria-hidden="true" />
                        <span>All ({SHOWCASE_ITEMS.length})</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={categoryFilter === 'game'}
                        className={`${styles.modeTabBtn} ${categoryFilter === 'game' ? styles.modeTabBtnActive : ''}`}
                        onClick={() => handleSelectCategory('game')}
                    >
                        <i className="bi bi-controller" aria-hidden="true" />
                        <span>Playable Games (6)</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={categoryFilter === 'feature'}
                        className={`${styles.modeTabBtn} ${categoryFilter === 'feature' ? styles.modeTabBtnActive : ''}`}
                        onClick={() => handleSelectCategory('feature')}
                    >
                        <i className="bi bi-grid-fill" aria-hidden="true" />
                        <span>App Functions (6)</span>
                    </button>
                </div>
            </div>

            {/* Main 2-Column Body: Left Copy & CTA + Right Live Interactive Playable Stage */}
            <div className={styles.bodyGrid}>
                <div className={styles.copyCol}>
                    <div>
                        <div className={styles.eyebrowRow}>
                            <span className={styles.typeTag}>{currentItem.eyebrow}</span>
                        </div>
                        <h3 className={styles.title}>{currentItem.title}</h3>
                        <p className={styles.description}>{currentItem.description}</p>
                    </div>

                    <div className={styles.valueRow}>
                        {currentItem.chips.map((chip) => (
                            <span key={chip} className={styles.valueChip}>
                                <i className="bi bi-check2-circle me-1" aria-hidden="true" />
                                {chip}
                            </span>
                        ))}
                    </div>

                    <div className={styles.ctaCluster}>
                        <a
                            href={currentItem.ctaUrl}
                            data-spin="true"
                            className={styles.primaryCta}
                        >
                            <i className={`bi ${currentItem.ctaIcon}`} aria-hidden="true" />
                            <span>{currentItem.ctaLabel}</span>
                            <i className="bi bi-arrow-right" aria-hidden="true" />
                        </a>

                        <button
                            type="button"
                            className={styles.cycleBtn}
                            onClick={handleCycleNext}
                            aria-label="Next interactive game or app feature"
                        >
                            <i className="bi bi-shuffle" aria-hidden="true" />
                            <span>Try Next Experience</span>
                        </button>
                    </div>
                </div>

                {/* Right Column: Live Playable Mini-Game or App Function Sandbox */}
                {renderInteractiveStage(currentItem)}
            </div>

            {/* Bottom Quick-Switch Ribbon so users can jump straight to any game or app function */}
            <div className={styles.quickStrip} role="toolbar" aria-label="Quick-switch interactive games and app functionalities">
                {filteredItems.map((item) => {
                    const isCurrent = item.id === currentItem.id;
                    const globalIdx = SHOWCASE_ITEMS.findIndex((i) => i.id === item.id);
                    return (
                        <button
                            key={item.id}
                            type="button"
                            className={`${styles.quickPill} ${isCurrent ? styles.quickPillActive : ''}`}
                            onClick={() => setActiveIndex(globalIdx !== -1 ? globalIdx : 0)}
                            aria-pressed={isCurrent}
                        >
                            <i className={`bi ${item.category === 'game' ? 'bi-controller' : 'bi-lightning-charge-fill'}`} aria-hidden="true" />
                            <span>{item.shortLabel}</span>
                        </button>
                    );
                })}
            </div>
        </aside>
    );
});
