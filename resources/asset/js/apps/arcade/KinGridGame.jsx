import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

export function KinGridGame({ puzzle, onProgress, onComplete, submitting }) {
    const size = puzzle?.size || 5;
    const givens = puzzle?.givens || [];
    const inequalities = puzzle?.inequalities || [];

    const givenMap = useMemo(() => {
        const m = new Map();
        givens.forEach((g) => m.set(`${g.r},${g.c}`, g.v));
        return m;
    }, [givens]);

    const [grid, setGrid] = useState([]);
    const [selected, setSelected] = useState(null);
    const [history, setHistory] = useState([]);
    const [hintsUsed, setHintsUsed] = useState(0);
    const [elapsedMs, setElapsedMs] = useState(0);
    const [statusNote, setStatusNote] = useState(`Fill every row and column with 1–${size} without repeats, obeying > and < clues.`);

    const startTimeRef = useRef(Date.now());
    const replayRef = useRef([]);
    const completedRef = useRef(false);

    const initGrid = () => {
        const next = Array.from({ length: size }, (_, r) =>
            Array.from({ length: size }, (__, c) => givenMap.get(`${r},${c}`) || 0)
        );
        setGrid(next);
        setHistory([]);
        setSelected(null);
    };

    useEffect(() => {
        initGrid();
        setHintsUsed(0);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        replayRef.current = [];
        completedRef.current = false;
        setStatusNote(`Fill every row and column with 1–${size} without repeats, obeying > and < clues.`);
    }, [puzzle?.seedKey, puzzle?.difficulty, size]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, []);

    const filledCount = useMemo(() => {
        let count = 0;
        grid.forEach((row) => row.forEach((v) => { if (v > 0) count += 1; }));
        return count;
    }, [grid]);

    useEffect(() => {
        const pct = Math.min(100, Math.round((filledCount / (size * size)) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [filledCount, size]);

    const validateBoard = (candidate) => {
        for (let r = 0; r < size; r++) {
            const seen = new Set();
            for (let c = 0; c < size; c++) {
                const v = candidate[r]?.[c] || 0;
                if (v < 1 || v > size || seen.has(v)) return 'Each row must contain 1–' + size + ' with no duplicates.';
                seen.add(v);
            }
        }
        for (let c = 0; c < size; c++) {
            const seen = new Set();
            for (let r = 0; r < size; r++) {
                const v = candidate[r]?.[c] || 0;
                if (seen.has(v)) return 'Each column must contain 1–' + size + ' with no duplicates.';
                seen.add(v);
            }
        }
        for (const ineq of inequalities) {
            const v1 = candidate[ineq.r1]?.[ineq.c1] || 0;
            const v2 = candidate[ineq.r2]?.[ineq.c2] || 0;
            if (ineq.op === '>' && !(v1 > v2)) return 'Check the > inequality clues.';
            if (ineq.op === '<' && !(v1 < v2)) return 'Check the < inequality clues.';
        }
        return null;
    };

    const setCellValue = (val) => {
        if (!selected || completedRef.current || submitting) return;
        const [r, c] = selected;
        if (givenMap.has(`${r},${c}`)) {
            playArcadeSfx('error');
            return;
        }
        const prevVal = grid[r][c];
        if (prevVal === val) return;

        const next = grid.map((row) => [...row]);
        next[r][c] = val;
        setHistory((h) => [...h, { r, c, prevVal }]);
        setGrid(next);
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c, v: val });
        playArcadeSfx('connect', r * size + c);

        // Check if board is full
        const allFilled = next.every((row) => row.every((cell) => cell > 0));
        if (allFilled) {
            const err = validateBoard(next);
            if (!err) {
                completedRef.current = true;
                const finalMs = Date.now() - startTimeRef.current;
                setElapsedMs(finalMs);
                playArcadeSfx('complete');
                setStatusNote('Spot on! Logic grid solved!');
                if (onComplete) {
                    onComplete({
                        submission: { grid: next },
                        elapsedMs: finalMs,
                        movesCount: replayRef.current.length,
                        hintsUsed,
                        mistakesCount: 0,
                        replay: replayRef.current,
                    });
                }
            } else {
                setStatusNote(err);
                playArcadeSfx('error');
            }
        }
    };

    const handleUndo = () => {
        if (history.length === 0 || completedRef.current) return;
        const last = history[history.length - 1];
        const next = grid.map((row) => [...row]);
        next[last.r][last.c] = last.prevVal;
        setGrid(next);
        setHistory((h) => h.slice(0, -1));
        playArcadeSfx('tick');
    };

    const handleHint = () => {
        if (completedRef.current) return;
        // Find an empty cell and suggest a valid candidate based on row/col constraints
        for (let r = 0; r < size; r++) {
            for (let c = 0; c < size; c++) {
                if (grid[r]?.[c] === 0) {
                    const used = new Set();
                    for (let k = 0; k < size; k++) {
                        if (grid[r][k]) used.add(grid[r][k]);
                        if (grid[k][c]) used.add(grid[k][c]);
                    }
                    const candidate = Array.from({ length: size }, (_, i) => i + 1).find((n) => !used.has(n));
                    if (candidate) {
                        setHintsUsed((h) => h + 1);
                        setSelected([r, c]);
                        const next = grid.map((row) => [...row]);
                        next[r][c] = candidate;
                        setGrid(next);
                        playArcadeSfx('connect', candidate);
                        setStatusNote(`Hint: placed ${candidate} at Row ${r + 1}, Col ${c + 1}.`);
                        return;
                    }
                }
            }
        }
    };

    const ineqBetween = (r1, c1, r2, c2) => {
        const found = inequalities.find(
            (i) => (i.r1 === r1 && i.c1 === c1 && i.r2 === r2 && i.c2 === c2) ||
                   (i.r1 === r2 && i.c1 === c2 && i.r2 === r1 && i.c2 === c1)
        );
        if (!found) return null;
        if (found.r1 === r1 && found.c1 === c1) return found.op;
        return found.op === '>' ? '<' : '>';
    };

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>{(puzzle?.difficulty || 'medium').toUpperCase()}</span>
                    <span>&bull;</span>
                    <span>{size}&times;{size}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>{filledCount}/{size * size} filled</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            <div
                className={styles.logicGrid}
                style={{ gridTemplateColumns: `repeat(${size}, minmax(0, 1fr))` }}
                role="grid"
                aria-label={`KinGrid ${size} by ${size} deduction grid`}
            >
                {grid.map((row, r) =>
                    row.map((val, c) => {
                        const isGiven = givenMap.has(`${r},${c}`);
                        const isSel = selected && selected[0] === r && selected[1] === c;
                        const rightIneq = c + 1 < size ? ineqBetween(r, c, r, c + 1) : null;
                        const downIneq = r + 1 < size ? ineqBetween(r, c, r + 1, c) : null;

                        return (
                            <div key={`${r}-${c}`} className={styles.logicCellWrap}>
                                <button
                                    type="button"
                                    className={[
                                        styles.logicCell,
                                        isGiven ? styles.logicCellGiven : '',
                                        isSel ? styles.logicCellSelected : '',
                                    ].filter(Boolean).join(' ')}
                                    onClick={() => setSelected([r, c])}
                                    aria-label={`Row ${r + 1} Column ${c + 1}, ${val > 0 ? `value ${val}` : 'empty'}${isGiven ? ', fixed clue' : ''}`}
                                >
                                    {val > 0 ? val : ''}
                                </button>
                                {rightIneq && <span className={styles.ineqRight} aria-hidden="true">{rightIneq}</span>}
                                {downIneq && (
                                    <span className={styles.ineqDown} aria-hidden="true">
                                        {downIneq === '>' ? 'v' : '^'}
                                    </span>
                                )}
                            </div>
                        );
                    })
                )}
            </div>

            <div className={styles.numPad} role="group" aria-label="Number input pad">
                {Array.from({ length: size }, (_, i) => i + 1).map((n) => (
                    <button
                        key={n}
                        type="button"
                        className={styles.numBtn}
                        onClick={() => setCellValue(n)}
                        disabled={!selected || submitting}
                    >
                        {n}
                    </button>
                ))}
                <button
                    type="button"
                    className={styles.numBtn}
                    onClick={() => setCellValue(0)}
                    disabled={!selected || submitting}
                    aria-label="Clear selected cell"
                >
                    <i className="bi bi-eraser-fill" aria-hidden="true" />
                </button>
            </div>

            <div className={styles.gameControls}>
                <button type="button" className={styles.ctrlBtn} onClick={handleUndo} disabled={history.length === 0 || submitting}>
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Undo</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={handleHint} disabled={submitting}>
                    <i className="bi bi-lightbulb-fill" aria-hidden="true" />
                    <span>Hint ({hintsUsed})</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={initGrid} disabled={submitting}>
                    <i className="bi bi-arrow-repeat" aria-hidden="true" />
                    <span>Reset</span>
                </button>
            </div>
        </div>
    );
}
