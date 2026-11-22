import { useState, useEffect, useRef } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

const PAD_COLOURS = [
    '#ef4444', '#f59e0b', '#10b981',
    '#3b82f6', '#8b5cf6', '#ec4899',
    '#14b8a6', '#f97316', '#eab308',
];

export function KinPulseGame({ puzzle, onProgress, onComplete, submitting }) {
    const sequence = puzzle?.sequence || [];
    const maxRounds = puzzle?.maxRounds || sequence.length || 8;

    const [round, setRound] = useState(1);
    const [phase, setPhase] = useState('idle'); // 'idle' | 'showing' | 'input' | 'won'
    const [activePad, setActivePad] = useState(-1);
    const [userStep, setUserStep] = useState(0);
    const [mistakes, setMistakes] = useState(0);
    const [elapsedMs, setElapsedMs] = useState(0);
    const [tilt, setTilt] = useState({ rx: 8, ry: 0 });
    const [statusNote, setStatusNote] = useState('Press Start Sequence to watch and echo the 3D harmonic pattern.');

    const startTimeRef = useRef(Date.now());
    const replayRef = useRef([]);
    const completedRef = useRef(false);
    const timersRef = useRef([]);

    const clearTimers = () => {
        timersRef.current.forEach((id) => window.clearTimeout(id));
        timersRef.current = [];
    };

    useEffect(() => {
        clearTimers();
        setRound(1);
        setPhase('idle');
        setActivePad(-1);
        setUserStep(0);
        setMistakes(0);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        replayRef.current = [];
        completedRef.current = false;
        setStatusNote('Press Start Sequence to watch and echo the 3D harmonic pattern.');
        return clearTimers;
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current && phase !== 'idle') {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, [phase]);

    useEffect(() => {
        const pct = phase === 'won' ? 100 : Math.min(99, Math.round(((round - 1) / maxRounds) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [round, maxRounds, phase]);

    const playSequenceForRound = (targetRound) => {
        clearTimers();
        setPhase('showing');
        setUserStep(0);
        setStatusNote(`Round ${targetRound} of ${maxRounds}: Watch carefully...`);

        const slice = sequence.slice(0, targetRound);
        slice.forEach((padIdx, i) => {
            const tOn = window.setTimeout(() => {
                setActivePad(padIdx);
                playArcadeSfx('pulse', padIdx);
            }, 350 + i * 520);
            const tOff = window.setTimeout(() => {
                setActivePad(-1);
            }, 350 + i * 520 + 320);
            timersRef.current.push(tOn, tOff);
        });

        const tReady = window.setTimeout(() => {
            setPhase('input');
            setStatusNote(`Your turn! Repeat ${targetRound} ${targetRound === 1 ? 'note' : 'notes'}.`);
        }, 350 + slice.length * 520 + 120);
        timersRef.current.push(tReady);
    };

    const handleStart = () => {
        startTimeRef.current = Date.now();
        playSequenceForRound(1);
    };

    const handlePadTap = (idx) => {
        if (phase !== 'input' || completedRef.current || submitting) return;

        setActivePad(idx);
        window.setTimeout(() => setActivePad(-1), 220);

        const expected = sequence[userStep];
        if (idx === expected) {
            playArcadeSfx('pulse', idx);
            replayRef.current.push({ t: Date.now() - startTimeRef.current, pad: idx, round });
            const nextStep = userStep + 1;
            if (nextStep >= round) {
                if (round >= maxRounds) {
                    completedRef.current = true;
                    setPhase('won');
                    const finalMs = Date.now() - startTimeRef.current;
                    setElapsedMs(finalMs);
                    playArcadeSfx('complete');
                    setStatusNote('Virtuoso! Full harmonic sequence mastered!');
                    if (onComplete) {
                        onComplete({
                            submission: { sequence: sequence.slice(0, maxRounds), roundsCleared: maxRounds },
                            elapsedMs: finalMs,
                            movesCount: replayRef.current.length,
                            hintsUsed: 0,
                            mistakesCount: mistakes,
                            replay: replayRef.current,
                        });
                    }
                } else {
                    const nextRound = round + 1;
                    setRound(nextRound);
                    const tNext = window.setTimeout(() => playSequenceForRound(nextRound), 550);
                    timersRef.current.push(tNext);
                }
            } else {
                setUserStep(nextStep);
            }
        } else {
            playArcadeSfx('error');
            setMistakes((m) => m + 1);
            setStatusNote('Wrong pad! Replaying this round...');
            const tRetry = window.setTimeout(() => playSequenceForRound(round), 650);
            timersRef.current.push(tRetry);
        }
    };

    const handleBoardMove = (e) => {
        const rect = e.currentTarget.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        setTilt({ rx: 10 - y * 14, ry: x * 16 });
    };

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Round {round}/{maxRounds}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Mistakes: {mistakes}</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            <div
                className={styles.pulseStage}
                onPointerMove={handleBoardMove}
                onPointerLeave={() => setTilt({ rx: 8, ry: 0 })}
            >
                <div
                    className={styles.pulseGrid}
                    style={{
                        transform: `perspective(900px) rotateX(${tilt.rx}deg) rotateY(${tilt.ry}deg)`,
                    }}
                    role="group"
                    aria-label="KinPulse 3D kinetic memory pads"
                >
                    {PAD_COLOURS.map((colour, idx) => {
                        const isLit = activePad === idx;
                        return (
                            <button
                                key={idx}
                                type="button"
                                className={`${styles.pulsePad} ${isLit ? styles.pulsePadActive : ''}`}
                                style={{
                                    '--pad-colour': colour,
                                }}
                                onClick={() => handlePadTap(idx)}
                                disabled={phase !== 'input' || submitting}
                                aria-label={`Kinetic Pad ${idx + 1}`}
                            >
                                <span className={styles.pulsePadNum}>{idx + 1}</span>
                            </button>
                        );
                    })}
                </div>
            </div>

            <div className={styles.gameControls}>
                {phase === 'idle' ? (
                    <button type="button" className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary}`} onClick={handleStart}>
                        <i className="bi bi-play-fill" aria-hidden="true" />
                        <span>Start Sequence</span>
                    </button>
                ) : (
                    <button
                        type="button"
                        className={styles.ctrlBtn}
                        onClick={() => playSequenceForRound(round)}
                        disabled={phase === 'showing' || phase === 'won' || submitting}
                    >
                        <i className="bi bi-arrow-repeat" aria-hidden="true" />
                        <span>Replay Round</span>
                    </button>
                )}
            </div>
        </div>
    );
}
