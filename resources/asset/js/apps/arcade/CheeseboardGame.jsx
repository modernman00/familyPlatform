import { useState, useEffect, useRef } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

const REGION_COLOURS = [
    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6',
    '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#eab308',
];

export function CheeseboardGame({ puzzle, onProgress, onComplete, submitting }) {
    const size = puzzle?.size || 7;
    const regions = puzzle?.regions || [];
    const targetQueens = puzzle?.targetQueens || size;

    // cellStates: 'empty' | 'cross' | 'queen'
    const [cellStates, setCellStates] = useState(() => Array.from({ length: size }, () => Array(size).fill('empty')));
    const [elapsedMs, setElapsedMs] = useState(0);
    const [statusNote, setStatusNote] = useState('Territory Deduction (Queens puzzle — not Chess): Place 1 Crown in every row, column, and colour region. Crowns cannot touch even diagonally!');

    const startTimeRef = useRef(Date.now());
    const completedRef = useRef(false);
    const replayRef = useRef([]);

    useEffect(() => {
        setCellStates(Array.from({ length: size }, () => Array(size).fill('empty')));
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
        setStatusNote('Territory Deduction (Queens puzzle — not Chess): Place 1 Crown in every row, column, and colour region. Crowns cannot touch even diagonally!');
    }, [puzzle?.seedKey, puzzle?.difficulty, size]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, []);

    // Find all placed queens
    const queens = [];
    for (let r = 0; r < size; r++) {
        for (let c = 0; c < size; c++) {
            if (cellStates[r]?.[c] === 'queen') {
                queens.push({ r, c, region: regions[r]?.[c] });
            }
        }
    }

    // Find clashes
    const clashedCells = new Set();
    for (let i = 0; i < queens.length; i++) {
        for (let j = i + 1; j < queens.length; j++) {
            const q1 = queens[i];
            const q2 = queens[j];
            const sameRow = q1.r === q2.r;
            const sameCol = q1.c === q2.c;
            const sameReg = q1.region !== undefined && q1.region === q2.region;
            const touches = Math.abs(q1.r - q2.r) <= 1 && Math.abs(q1.c - q2.c) <= 1;

            if (sameRow || sameCol || sameReg || touches) {
                clashedCells.add(`${q1.r},${q1.c}`);
                clashedCells.add(`${q2.r},${q2.c}`);
            }
        }
    }

    useEffect(() => {
        const pct = Math.min(100, Math.round((queens.length / targetQueens) * 100));
        if (onProgress) onProgress(pct, elapsedMs);

        if (queens.length === targetQueens && clashedCells.size === 0 && !completedRef.current) {
            completedRef.current = true;
            const finalMs = Date.now() - startTimeRef.current;
            setElapsedMs(finalMs);
            playArcadeSfx('complete');
            setStatusNote('Sovereignty achieved! All crowns placed in harmony.');

            if (onComplete) {
                onComplete({
                    submission: {
                        queens: queens.map((q) => ({ r: q.r, c: q.c })),
                    },
                    elapsedMs: finalMs,
                    movesCount: replayRef.current.length,
                    hintsUsed: 0,
                    mistakesCount: 0,
                    replay: replayRef.current,
                });
            }
        }
    }, [queens.length, clashedCells.size, targetQueens]);

    const handleCellClick = (r, c) => {
        if (completedRef.current || submitting) return;

        const current = cellStates[r][c];
        let next = 'cross';
        if (current === 'empty') {
            next = 'cross';
            playArcadeSfx('tick');
        } else if (current === 'cross') {
            next = 'queen';
            playArcadeSfx('connect', r * size + c);
        } else {
            next = 'empty';
            playArcadeSfx('tick');
        }

        const nextGrid = cellStates.map((rowArr, rowIdx) => {
            if (rowIdx !== r) return rowArr;
            const copy = [...rowArr];
            copy[c] = next;
            return copy;
        });

        setCellStates(nextGrid);
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c, state: next });
    };

    const handleClearCrosses = () => {
        const nextGrid = cellStates.map((rowArr) =>
            rowArr.map((v) => (v === 'cross' ? 'empty' : v))
        );
        setCellStates(nextGrid);
        playArcadeSfx('tick');
    };

    const handleResetAll = () => {
        setCellStates(Array.from({ length: size }, () => Array(size).fill('empty')));
        playArcadeSfx('error');
    };

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Crowns: {queens.length}/{targetQueens}</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">
                {clashedCells.size > 0
                    ? '⚠️ Clashing crowns detected! Crowns must not share a row, column, region or touch diagonally.'
                    : statusNote}
            </p>

            <div
                className={styles.cheeseboardStage}
                style={{ '--board-size': size }}
                role="grid"
                aria-label={`Cheeseboard ${size} by ${size} territory grid`}
            >
                {cellStates.map((rowArr, r) => (
                    <div key={r} className={styles.cheeseboardRow} role="row">
                        {rowArr.map((state, c) => {
                            const regId = regions[r]?.[c] ?? 0;
                            const bgColour = REGION_COLOURS[regId % REGION_COLOURS.length];
                            const isClashed = clashedCells.has(`${r},${c}`);

                            return (
                                <button
                                    key={c}
                                    type="button"
                                    className={`${styles.cheeseboardCell} ${isClashed ? styles.cheeseboardCellClash : ''}`}
                                    style={{
                                        '--region-bg': bgColour,
                                    }}
                                    onClick={() => handleCellClick(r, c)}
                                    disabled={completedRef.current || submitting}
                                    aria-label={`Row ${r + 1}, Column ${c + 1}, Region ${regId + 1}: ${state}`}
                                >
                                    {state === 'queen' && (
                                        <span className={styles.queenCrown} aria-hidden="true">👑</span>
                                    )}
                                    {state === 'cross' && (
                                        <span className={styles.cellCross} aria-hidden="true">✕</span>
                                    )}
                                </button>
                            );
                        })}
                    </div>
                ))}
            </div>

            <div className={styles.gameControls}>
                <button
                    type="button"
                    className={styles.ctrlBtn}
                    onClick={handleClearCrosses}
                    disabled={completedRef.current || submitting}
                    title="Clear all × markers from board"
                >
                    <i className="bi bi-eraser" aria-hidden="true" />
                    <span>Clear ×</span>
                </button>
                <button
                    type="button"
                    className={styles.ctrlBtn}
                    onClick={handleResetAll}
                    disabled={completedRef.current || submitting}
                    title="Reset entire board"
                >
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Reset</span>
                </button>
            </div>
        </div>
    );
}
