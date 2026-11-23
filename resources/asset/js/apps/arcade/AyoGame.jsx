import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

/**
 * Authentic Yoruba Ayò Olópon Engine & Board Component
 *
 * Board layout:
 * - 2 rows of 6 pits (12 ojú ayò)
 * - North: Pits 11, 10, 9, 8, 7, 6 (opponent / Bot / Player 2)
 * - South: Pits 0, 1, 2, 3, 4, 5 (Player 1 / You)
 * - Sowing direction: strictly anti-clockwise:
 *   0 -> 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7 -> 8 -> 9 -> 10 -> 11 -> 0...
 * - North captures on South pits (0..5) when ending seed makes exactly 4.
 * - South captures on North pits (6..11) when ending seed makes exactly 4.
 * - Chain captures apply backwards consecutively on opponent territory.
 * - Starvation rule: You must feed an empty opponent if possible.
 */

const YORUBA_PROVERBS = [
    'Ọlọ́gbọ́n l’ó ń ta Ayò — Wisdom and patience guide the true champion.',
    'Ayò kì í bínú — Ayò bears no grudges; every game brings renewed joy.',
    'Ẹni tó gbọ́n l’ó ń jẹ́ mẹ́rin — The sharp eye spots the four seeds waiting.',
    'Ọpọ́n ayò kò ní dánu — The sacred board never empties in vain.',
    'Bí a bá ta ayò, a ó mọ ọlọ́gbọ́n — In the rhythm of the seeds, masters emerge.',
];

export function AyoGame({ puzzle, onProgress, onComplete, submitting }) {
    // 12 pits starting with 4 seeds each (48 total)
    const initialPits = useMemo(() => Array(12).fill(4), [puzzle?.seedKey]);

    const [pits, setPits] = useState(initialPits);
    const [capturedSouth, setCapturedSouth] = useState(0); // You / Player 1
    const [capturedNorth, setCapturedNorth] = useState(0); // Bot / Player 2
    const [turn, setTurn] = useState('south'); // 'south' | 'north'
    const [animating, setAnimating] = useState(false);
    const [highlightedPit, setHighlightedPit] = useState(null);
    const [lastCaptureNote, setLastCaptureNote] = useState('');
    const [statusNote, setStatusNote] = useState('Ẹ káàbọ̀! Tap any pit on your side (South) with seeds to sow anti-clockwise.');
    const [proverb, setProverb] = useState(YORUBA_PROVERBS[0]);
    const [elapsedMs, setElapsedMs] = useState(0);

    const difficulty = puzzle?.difficulty || 'medium';
    const isBotGame = true; // Playing against ATG Ayò Olópon Bot
    const startTimeRef = useRef(Date.now());
    const completedRef = useRef(false);
    const replayRef = useRef([]);

    // Reset when puzzle or seed changes
    useEffect(() => {
        setPits(Array(12).fill(4));
        setCapturedSouth(0);
        setCapturedNorth(0);
        setTurn('south');
        setAnimating(false);
        setHighlightedPit(null);
        setLastCaptureNote('');
        setStatusNote('Ẹ káàbọ̀! Tap any pit on your side (South) with seeds to sow anti-clockwise.');
        setProverb(YORUBA_PROVERBS[Math.floor(Math.random() * YORUBA_PROVERBS.length)]);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    // Timer loop
    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, []);

    // Check game over condition
    useEffect(() => {
        const totalCaptured = capturedSouth + capturedNorth;
        const southSeeds = pits.slice(0, 6).reduce((a, b) => a + b, 0);
        const northSeeds = pits.slice(6, 12).reduce((a, b) => a + b, 0);

        // Victory if one player captures 25+ seeds
        if ((capturedSouth >= 25 || capturedNorth >= 25 || totalCaptured >= 40 || (southSeeds === 0 && northSeeds === 0)) && !completedRef.current) {
            completedRef.current = true;
            const finalMs = Date.now() - startTimeRef.current;
            setElapsedMs(finalMs);
            playArcadeSfx('complete');

            const won = capturedSouth > capturedNorth;
            const finalStatus = won
                ? `🏆 Oríire! Ayò Grandmaster Victory! You captured ${capturedSouth} seeds to ${capturedNorth}!`
                : `A great contest! The ATG Bot captured ${capturedNorth} seeds to ${capturedSouth}.`;
            setStatusNote(finalStatus);

            if (onComplete) {
                onComplete({
                    submission: {
                        capturedSouth,
                        capturedNorth,
                        won,
                        finalPits: pits,
                    },
                    elapsedMs: finalMs,
                    movesCount: replayRef.current.length,
                    hintsUsed: 0,
                    mistakesCount: 0,
                    replay: replayRef.current,
                });
            }
        }
    }, [capturedSouth, capturedNorth, pits]);

    // Progress updates based on captured seeds (out of 25 needed for win)
    useEffect(() => {
        const pct = Math.min(100, Math.round((capturedSouth / 25) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [capturedSouth, elapsedMs]);

    // Helper: Valid pits a player can sow from
    const getValidMoves = (currentPits, currentTurn) => {
        const range = currentTurn === 'south' ? [0, 1, 2, 3, 4, 5] : [6, 7, 8, 9, 10, 11];
        const opponentRange = currentTurn === 'south' ? [6, 7, 8, 9, 10, 11] : [0, 1, 2, 3, 4, 5];
        const opponentHasSeeds = opponentRange.some((idx) => currentPits[idx] > 0);

        // Basic candidates: non-empty pits
        const candidates = range.filter((idx) => currentPits[idx] > 0);
        if (candidates.length === 0) return [];

        // If opponent is starving, we MUST feed them if any move reaches their side
        if (!opponentHasSeeds) {
            const feedingMoves = candidates.filter((idx) => {
                const count = currentPits[idx];
                for (let step = 1; step <= count; step++) {
                    const target = (idx + step) % 12;
                    if (opponentRange.includes(target)) return true;
                }
                return false;
            });
            if (feedingMoves.length > 0) return feedingMoves;
        }

        return candidates;
    };

    // Execute move simulation for AI or Player
    const executeSow = async (startPit, playerSide) => {
        if (animating || completedRef.current || submitting) return;
        setAnimating(true);
        setHighlightedPit(startPit);

        const newPits = [...pits];
        let seedsInHand = newPits[startPit];
        newPits[startPit] = 0;
        setPits([...newPits]);
        playArcadeSfx('tick');

        replayRef.current.push({
            t: Date.now() - startTimeRef.current,
            player: playerSide,
            pit: startPit,
            seeds: seedsInHand,
        });

        let currentPit = startPit;

        // Anti-clockwise distribution with skip for >=12 seeds
        while (seedsInHand > 0) {
            currentPit = (currentPit + 1) % 12;
            if (currentPit === startPit) {
                // Skip the originating pit if seeds >= 12
                continue;
            }

            newPits[currentPit] += 1;
            seedsInHand -= 1;
            setPits([...newPits]);
            setHighlightedPit(currentPit);
            playArcadeSfx('connect', currentPit);

            // Small delay for natural visual sowing cadence
            await new Promise((r) => window.setTimeout(r, 120));
        }

        // Capture check: Did the final seed land in opponent territory with count == 4?
        const opponentPits = playerSide === 'south' ? [6, 7, 8, 9, 10, 11] : [0, 1, 2, 3, 4, 5];
        let capturedSeedsThisTurn = 0;

        let checkPit = currentPit;
        while (opponentPits.includes(checkPit) && newPits[checkPit] === 4) {
            capturedSeedsThisTurn += 4;
            newPits[checkPit] = 0;
            setPits([...newPits]);
            playArcadeSfx('connect', 11);

            // Step backward for chain capture
            checkPit = (checkPit - 1 + 12) % 12;
        }

        if (capturedSeedsThisTurn > 0) {
            playArcadeSfx('pulse', 2);
            if (playerSide === 'south') {
                setCapturedSouth((prev) => prev + capturedSeedsThisTurn);
                setLastCaptureNote(`Ẹ̀bùn! You captured ${capturedSeedsThisTurn} seeds! (Jẹ mẹ́rin!)`);
                setStatusNote(`Omo ayò! You captured ${capturedSeedsThisTurn} seeds!`);
            } else {
                setCapturedNorth((prev) => prev + capturedSeedsThisTurn);
                setLastCaptureNote(`ATG Bot captured ${capturedSeedsThisTurn} seeds.`);
                setStatusNote(`ATG Bot seized ${capturedSeedsThisTurn} seeds.`);
            }
        } else {
            setLastCaptureNote('');
        }

        setHighlightedPit(null);
        setAnimating(false);

        // Switch turns
        const nextTurn = playerSide === 'south' ? 'north' : 'south';
        setTurn(nextTurn);
    };

    // Bot move trigger
    useEffect(() => {
        if (turn === 'north' && !animating && !completedRef.current && isBotGame) {
            const validMoves = getValidMoves(pits, 'north');
            if (validMoves.length === 0) {
                // North has no valid moves; south claims remaining seeds
                const remaining = pits.slice(0, 6).reduce((a, b) => a + b, 0);
                setCapturedSouth((prev) => prev + remaining);
                setPits(Array(12).fill(0));
                return;
            }

            // AI Decision based on difficulty
            let chosenPit = validMoves[0];

            if (difficulty === 'easy') {
                // Random move
                chosenPit = validMoves[Math.floor(Math.random() * validMoves.length)];
            } else {
                // Evaluate 1-ply moves to prioritize captures and avoid giving 4s
                let bestScore = -999;
                for (const pitIdx of validMoves) {
                    const count = pits[pitIdx];
                    const endPit = (pitIdx + count) % 12;
                    let moveScore = 0;

                    // If it lands on South and makes 4
                    if (endPit >= 0 && endPit <= 5 && pits[endPit] + 1 === 4) {
                        moveScore += 12;
                    }
                    // Avoid leaving pit with 3 seeds where player could easily land
                    if (pits[pitIdx] === 3) {
                        moveScore += 3;
                    }
                    // Depth enhancement for Grandmaster/Hard
                    if (difficulty === 'hard') {
                        moveScore += (count % 12);
                    }

                    if (moveScore > bestScore) {
                        bestScore = moveScore;
                        chosenPit = pitIdx;
                    }
                }
            }

            const timer = window.setTimeout(() => {
                executeSow(chosenPit, 'north');
            }, 600);

            return () => window.clearTimeout(timer);
        }
    }, [turn, animating, pits, difficulty]);

    const handlePitClick = (pitIdx) => {
        if (turn !== 'south' || animating || completedRef.current || submitting) return;
        const validMoves = getValidMoves(pits, 'south');
        if (!validMoves.includes(pitIdx)) {
            playArcadeSfx('error');
            return;
        }
        executeSow(pitIdx, 'south');
    };

    const validSouthMoves = useMemo(() => getValidMoves(pits, 'south'), [pits, turn]);

    return (
        <div className={styles.gameBoardWrap}>
            {/* Top Game Meta Row */}
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge} style={{ background: '#78350f', color: '#fef3c7', borderColor: '#b45309' }}>
                    <i className="bi bi-gem" aria-hidden="true" />
                    <span>Your Seeds: <strong>{capturedSouth}</strong> / 25 to Win</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>ATG Bot: <strong>{capturedNorth}</strong></span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Turn: <strong>{turn === 'south' ? 'Your Turn (South)' : 'ATG Bot Thinking...'}</strong></span>
                </div>
            </div>

            {/* Instruction Banner & Proverb */}
            <p className={styles.gameInstruction} aria-live="polite">
                {lastCaptureNote ? <strong>{lastCaptureNote} — </strong> : null}
                {statusNote}
            </p>

            {/* Authentic Carved Mahogany Ọpọ́n Ayò Board */}
            <div
                className={styles.ayoBoardStage}
                role="region"
                aria-label="Yoruba Ayò Olópon Board"
            >
                {/* West Ilé (Bot / North Treasury) */}
                <div className={styles.ayoIle} title={`North Treasury: ${capturedNorth} seeds`}>
                    <span className={styles.ayoIleLabel}>BOT ILÉ</span>
                    <div className={styles.ayoIleCount}>{capturedNorth}</div>
                    <div className={styles.ayoSeedCluster} aria-hidden="true">
                        {Array.from({ length: Math.min(18, capturedNorth) }).map((_, i) => (
                            <span key={i} className={styles.ayoSeedSmall} />
                        ))}
                    </div>
                </div>

                {/* 12 Hollows (2 rows of 6) */}
                <div className={styles.ayoHollowsGrid}>
                    {/* North Row: Pits 11 downto 6 (Left to Right from South's perspective) */}
                    <div className={styles.ayoRow} role="row" aria-label="North Player Pits (Bot)">
                        {[11, 10, 9, 8, 7, 6].map((idx) => {
                            const count = pits[idx];
                            const isHigh = highlightedPit === idx;
                            return (
                                <div
                                    key={idx}
                                    className={`${styles.ayoPit} ${styles.ayoPitNorth} ${isHigh ? styles.ayoPitHighlight : ''}`}
                                    aria-label={`Pit ${idx + 1} (North): ${count} seeds`}
                                >
                                    <span className={styles.ayoPitNumber}>{idx + 1}</span>
                                    <span className={styles.ayoPitCount}>{count}</span>
                                    <div className={styles.ayoSeedCluster} aria-hidden="true">
                                        {Array.from({ length: Math.min(12, count) }).map((_, i) => (
                                            <span key={i} className={styles.ayoSeed} />
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Dividing Carved Spine */}
                    <div className={styles.ayoBoardSpine} aria-hidden="true">
                        <span>ỌPỌ́N AYÒ • YORÙBÁLAND</span>
                    </div>

                    {/* South Row: Pits 0 to 5 (Left to Right) */}
                    <div className={styles.ayoRow} role="row" aria-label="South Player Pits (You)">
                        {[0, 1, 2, 3, 4, 5].map((idx) => {
                            const count = pits[idx];
                            const isValid = validSouthMoves.includes(idx);
                            const isHigh = highlightedPit === idx;
                            const isClickable = turn === 'south' && isValid && !animating && !completedRef.current;

                            return (
                                <button
                                    key={idx}
                                    type="button"
                                    className={`${styles.ayoPit} ${styles.ayoPitSouth} ${isValid && turn === 'south' ? styles.ayoPitValid : ''} ${isHigh ? styles.ayoPitHighlight : ''}`}
                                    onClick={() => handlePitClick(idx)}
                                    disabled={!isClickable}
                                    aria-label={`Pit ${idx + 1} (South): ${count} seeds${isValid && turn === 'south' ? ' (Tap to sow)' : ''}`}
                                >
                                    <span className={styles.ayoPitNumber}>{idx + 1}</span>
                                    <span className={styles.ayoPitCount}>{count}</span>
                                    <div className={styles.ayoSeedCluster} aria-hidden="true">
                                        {Array.from({ length: Math.min(12, count) }).map((_, i) => (
                                            <span key={i} className={styles.ayoSeed} />
                                        ))}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* East Ilé (You / South Treasury) */}
                <div className={styles.ayoIle} title={`South Treasury: ${capturedSouth} seeds`}>
                    <span className={styles.ayoIleLabel}>YOUR ILÉ</span>
                    <div className={styles.ayoIleCount}>{capturedSouth}</div>
                    <div className={styles.ayoSeedCluster} aria-hidden="true">
                        {Array.from({ length: Math.min(18, capturedSouth) }).map((_, i) => (
                            <span key={i} className={styles.ayoSeedSmall} />
                        ))}
                    </div>
                </div>
            </div>

            {/* Cultural Proverb Bar */}
            <div className={styles.ayoProverbStrip}>
                <i className="bi bi-quote text-warning me-1" aria-hidden="true" />
                <span><em>{proverb}</em></span>
            </div>

            {/* Action Controls */}
            <div className={styles.gameControls}>
                <button
                    type="button"
                    className={styles.ctrlBtn}
                    onClick={() => {
                        setPits(Array(12).fill(4));
                        setCapturedSouth(0);
                        setCapturedNorth(0);
                        setTurn('south');
                        setAnimating(false);
                        completedRef.current = false;
                        setStatusNote('New game restarted! Sow any pit on your side.');
                        playArcadeSfx('error');
                    }}
                    disabled={animating}
                    title="Restart Ayò match"
                >
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Restart Ayò</span>
                </button>
            </div>
        </div>
    );
}
