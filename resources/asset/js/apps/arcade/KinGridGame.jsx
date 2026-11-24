import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import { formatElapsedMs } from './arcadeEngine';
import { triggerHaptic } from './hooks/useSoundEffects';
import styles from './ArcadeShell.module.css';

export function KinGridGame({ puzzle, onProgress, onComplete, submitting, sound }) {
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
    const [statusNote, setStatusNote] = useState(`Fill every row and column with numbers 1–${size} (no repeats) and follow the > and < signs.`);
    const [showStrategy, setShowStrategy] = useState(false);
    const [lastFilledCell, setLastFilledCell] = useState(null);
    const [sweepingCells, setSweepingCells] = useState(() => new Set());

    const startTimeRef = useRef(Date.now());
    const replayRef = useRef([]);
    const completedRef = useRef(false);
    const completedLinesRef = useRef(new Set());

    const initGrid = () => {
        const next = Array.from({ length: size }, (_, r) =>
            Array.from({ length: size }, (__, c) => givenMap.get(`${r},${c}`) || 0)
        );
        setGrid(next);
        setHistory([]);
        setSelected(null);
        setLastFilledCell(null);
        setSweepingCells(new Set());
        completedLinesRef.current = new Set();
    };

    useEffect(() => {
        initGrid();
        setHintsUsed(0);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        replayRef.current = [];
        completedRef.current = false;
        setStatusNote(`Fill every row and column with numbers 1–${size} (no repeats) and follow the > and < signs.`);
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

    // Live inequality validation status (neutral | satisfied | invalid)
    const ineqStatusMap = useMemo(() => {
        const map = new Map();
        for (const ineq of inequalities) {
            const v1 = grid[ineq.r1]?.[ineq.c1] || 0;
            const v2 = grid[ineq.r2]?.[ineq.c2] || 0;
            const edgeKey = `${ineq.r1},${ineq.c1}-${ineq.r2},${ineq.c2}`;
            if (v1 === 0 || v2 === 0) {
                map.set(edgeKey, 'neutral');
            } else {
                const ok = ineq.op === '>' ? v1 > v2 : v1 < v2;
                map.set(edgeKey, ok ? 'satisfied' : 'invalid');
            }
        }
        return map;
    }, [grid, inequalities]);

    // Detect newly completed valid rows or columns and sweep gold sheen
    useEffect(() => {
        if (!grid.length) return;
        const newlyCompleted = [];
        for (let r = 0; r < size; r++) {
            const rowKey = `r:${r}`;
            const seen = new Set();
            let valid = true;
            for (let c = 0; c < size; c++) {
                const v = grid[r]?.[c] || 0;
                if (v < 1 || v > size || seen.has(v)) { valid = false; break; }
                seen.add(v);
            }
            if (valid && !completedLinesRef.current.has(rowKey)) {
                completedLinesRef.current.add(rowKey);
                for (let c = 0; c < size; c++) newlyCompleted.push(`${r},${c}`);
            } else if (!valid && completedLinesRef.current.has(rowKey)) {
                completedLinesRef.current.delete(rowKey);
            }
        }
        for (let c = 0; c < size; c++) {
            const colKey = `c:${c}`;
            const seen = new Set();
            let valid = true;
            for (let r = 0; r < size; r++) {
                const v = grid[r]?.[c] || 0;
                if (v < 1 || v > size || seen.has(v)) { valid = false; break; }
                seen.add(v);
            }
            if (valid && !completedLinesRef.current.has(colKey)) {
                completedLinesRef.current.add(colKey);
                for (let r = 0; r < size; r++) newlyCompleted.push(`${r},${c}`);
            } else if (!valid && completedLinesRef.current.has(colKey)) {
                completedLinesRef.current.delete(colKey);
            }
        }
        if (newlyCompleted.length > 0) {
            triggerHaptic(16);
            sound?.playRowComplete?.();
            setSweepingCells((prev) => {
                const next = new Set(prev);
                newlyCompleted.forEach((k) => next.add(k));
                return next;
            });
        }
    }, [grid, size]);

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

    const applyMove = (r, c, val, { recordHistory = true } = {}) => {
        if (completedRef.current || submitting) return;
        if (givenMap.has(`${r},${c}`)) {
            triggerHaptic(16);
            sound?.playError?.();
            return;
        }
        const prevVal = grid[r]?.[c] ?? 0;
        if (prevVal === val) return;

        const next = grid.map((row) => [...row]);
        next[r][c] = val;
        if (recordHistory) {
            setHistory((h) => [...h, { r, c, prevVal }]);
        }
        setGrid(next);
        if (val > 0) {
            setLastFilledCell(`${r},${c}`);
        }
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c, v: val });

        const hasIneqViolation = val > 0 && inequalities.some((ineq) => {
            if (ineq.r1 === r && ineq.c1 === c) {
                const other = next[ineq.r2]?.[ineq.c2] || 0;
                return other > 0 && (ineq.op === '>' ? !(val > other) : !(val < other));
            }
            if (ineq.r2 === r && ineq.c2 === c) {
                const other = next[ineq.r1]?.[ineq.c1] || 0;
                return other > 0 && (ineq.op === '>' ? !(other > val) : !(other < val));
            }
            return false;
        });

        if (hasIneqViolation) {
            triggerHaptic(18);
            sound?.playError?.();
        } else {
            triggerHaptic(12);
            if (val > 0) {
                sound?.playConnect?.(filledCount + 1);
            } else {
                sound?.playTap?.();
            }
        }

        const allFilled = next.every((row) => row.every((cell) => cell > 0));
        if (allFilled) {
            const err = validateBoard(next);
            if (!err) {
                completedRef.current = true;
                const finalMs = Date.now() - startTimeRef.current;
                setElapsedMs(finalMs);
                triggerHaptic(20);
                sound?.playVictory?.();
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
                triggerHaptic(18);
                sound?.playError?.();
            }
        }
    };

    const setCellValue = (val) => {
        if (!selected) return;
        applyMove(selected[0], selected[1], val);
    };

    // Keyboard navigation & number entry for desktop players
    useEffect(() => {
        const onKeyDown = (e) => {
            if (!selected || completedRef.current || submitting) return;
            const tag = (e.target?.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            const num = Number(e.key);
            if (Number.isInteger(num) && num >= 1 && num <= size) {
                e.preventDefault();
                applyMove(selected[0], selected[1], num);
                return;
            }
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === '0') {
                e.preventDefault();
                applyMove(selected[0], selected[1], 0);
                return;
            }
            const [r, c] = selected;
            if (e.key === 'ArrowUp') { e.preventDefault(); setSelected([Math.max(0, r - 1), c]); }
            if (e.key === 'ArrowDown') { e.preventDefault(); setSelected([Math.min(size - 1, r + 1), c]); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); setSelected([r, Math.max(0, c - 1)]); }
            if (e.key === 'ArrowRight') { e.preventDefault(); setSelected([r, Math.min(size - 1, c + 1)]); }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [selected, grid, size, submitting]);

    const handleUndo = () => {
        if (history.length === 0 || completedRef.current) return;
        const last = history[history.length - 1];
        const next = grid.map((row) => [...row]);
        next[last.r][last.c] = last.prevVal;
        setGrid(next);
        setHistory((h) => h.slice(0, -1));
        triggerHaptic(10);
        sound?.playTap?.();
    };

    const solveFromBoard = (baseBoard) => {
        const board = baseBoard.map((row) => [...row]);
        const isSafe = (r, c, val) => {
            for (let k = 0; k < size; k++) {
                if (board[r][k] === val || board[k][c] === val) return false;
            }
            for (const ineq of inequalities) {
                if (ineq.r1 === r && ineq.c1 === c) {
                    const other = board[ineq.r2]?.[ineq.c2] || 0;
                    if (other > 0 && ((ineq.op === '>' && !(val > other)) || (ineq.op === '<' && !(val < other)))) return false;
                } else if (ineq.r2 === r && ineq.c2 === c) {
                    const other = board[ineq.r1]?.[ineq.c1] || 0;
                    if (other > 0 && ((ineq.op === '>' && !(other > val)) || (ineq.op === '<' && !(other < val)))) return false;
                }
            }
            return true;
        };

        let steps = 0;
        const backtrack = () => {
            if (++steps > 8000) return false;
            for (let r = 0; r < size; r++) {
                for (let c = 0; c < size; c++) {
                    if (board[r][c] === 0) {
                        for (let v = 1; v <= size; v++) {
                            if (isSafe(r, c, v)) {
                                board[r][c] = v;
                                if (backtrack()) return true;
                                board[r][c] = 0;
                            }
                        }
                        return false;
                    }
                }
            }
            return true;
        };

        return backtrack() ? board : null;
    };

    const findSmartDeductionHint = (currentGrid, solvedGrid) => {
        // 1. Look for a row with exactly 1 missing number (Naked Single)
        for (let r = 0; r < size; r++) {
            const empties = [];
            for (let c = 0; c < size; c++) {
                if (currentGrid[r][c] === 0) empties.push(c);
            }
            if (empties.length === 1) {
                const c = empties[0];
                const val = solvedGrid[r][c];
                return {
                    r, c, val,
                    rationale: `Row ${r + 1} has ${size - 1} numbers placed — ${val} is the only remaining number.`
                };
            }
        }

        // 2. Look for a column with exactly 1 missing number
        for (let c = 0; c < size; c++) {
            const empties = [];
            for (let r = 0; r < size; r++) {
                if (currentGrid[r][c] === 0) empties.push(r);
            }
            if (empties.length === 1) {
                const r = empties[0];
                const val = solvedGrid[r][c];
                return {
                    r, c, val,
                    rationale: `Column ${c + 1} has ${size - 1} numbers placed — ${val} is the only remaining number.`
                };
            }
        }

        // 3. Look for direct inequality clues bounded by an existing placed neighbour
        for (const ineq of inequalities) {
            const v1 = currentGrid[ineq.r1]?.[ineq.c1] || 0;
            const v2 = currentGrid[ineq.r2]?.[ineq.c2] || 0;
            if (v1 === 0 && v2 > 0) {
                if (ineq.op === '<' && v2 === 2) {
                    return {
                        r: ineq.r1, c: ineq.c1, val: 1,
                        rationale: `Bounded by clue: ${v2} is placed and Row ${ineq.r1 + 1}, Col ${ineq.c1 + 1} is smaller (< 2). Only 1 is less than 2!`
                    };
                }
                if (ineq.op === '>' && v2 === size - 1) {
                    return {
                        r: ineq.r1, c: ineq.c1, val: size,
                        rationale: `Bounded by clue: ${v2} is placed and Row ${ineq.r1 + 1}, Col ${ineq.c1 + 1} is greater (> ${size - 1}). Only ${size} can go here!`
                    };
                }
            } else if (v2 === 0 && v1 > 0) {
                if (ineq.op === '>' && v1 === 2) {
                    return {
                        r: ineq.r2, c: ineq.c2, val: 1,
                        rationale: `Bounded by clue: ${v1} is placed and Row ${ineq.r2 + 1}, Col ${ineq.c2 + 1} is pointed to by 2. Only 1 is less than 2!`
                    };
                }
                if (ineq.op === '<' && v1 === size - 1) {
                    return {
                        r: ineq.r2, c: ineq.c2, val: size,
                        rationale: `Bounded by clue: ${v1} is placed and Row ${ineq.r2 + 1}, Col ${ineq.c2 + 1} is greater than ${size - 1}. Only ${size} can go here!`
                    };
                }
            }
        }

        // 4. Extreme digit elimination (1 and size)
        for (let r = 0; r < size; r++) {
            for (let c = 0; c < size; c++) {
                if (currentGrid[r][c] === 0) {
                    const val = solvedGrid[r][c];
                    if (val === size) {
                        return {
                            r, c, val,
                            rationale: `Extreme digit rule: ${size} is the maximum digit and cannot be pointed to as smaller. Placed ${size}.`
                        };
                    }
                    if (val === 1) {
                        return {
                            r, c, val,
                            rationale: `Extreme digit rule: 1 is the minimum digit and cannot point to a smaller number. Placed 1.`
                        };
                    }
                }
            }
        }

        // 5. Fallback first empty
        for (let r = 0; r < size; r++) {
            for (let c = 0; c < size; c++) {
                if (currentGrid[r][c] === 0) {
                    const val = solvedGrid[r][c];
                    return {
                        r, c, val,
                        rationale: `Row, column, and inequality elimination forces ${val} here.`
                    };
                }
            }
        }
        return null;
    };

    const handleHint = () => {
        if (completedRef.current || submitting) return;
        const solved = solveFromBoard(grid);
        if (!solved) {
            setStatusNote('One of your current entries conflicts with the clues — try Undo or Reset.');
            triggerHaptic(18);
            sound?.playError?.();
            return;
        }
        const hint = findSmartDeductionHint(grid, solved);
        if (hint) {
            const { r, c, val, rationale } = hint;
            setHintsUsed((h) => h + 1);
            setSelected([r, c]);
            setStatusNote(`Hint: placed ${val} at Row ${r + 1}, Col ${c + 1}. ${rationale}`);
            applyMove(r, c, val);
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
                        const cellKey = `${r},${c}`;
                        const isPopping = lastFilledCell === cellKey;
                        const isSweeping = sweepingCells.has(cellKey);
                        const rightIneq = c + 1 < size ? ineqBetween(r, c, r, c + 1) : null;
                        const downIneq = r + 1 < size ? ineqBetween(r, c, r + 1, c) : null;
                        const rightStatus = rightIneq ? (ineqStatusMap.get(`${r},${c}-${r},${c + 1}`) || 'neutral') : 'neutral';
                        const downStatus = downIneq ? (ineqStatusMap.get(`${r},${c}-${r + 1},${c}`) || 'neutral') : 'neutral';

                        return (
                            <div key={`${r}-${c}`} className={styles.logicCellWrap}>
                                <button
                                    type="button"
                                    className={[
                                        styles.logicCell,
                                        isGiven ? styles.logicCellGiven : '',
                                        isSel ? styles.logicCellSelected : '',
                                        isPopping ? styles.logicCellPop : '',
                                        isSweeping ? styles.logicCellSweep : '',
                                    ].filter(Boolean).join(' ')}
                                    onClick={() => { triggerHaptic(10); sound?.playTap?.(); setSelected([r, c]); }}
                                    onAnimationEnd={() => {
                                        if (isPopping) setLastFilledCell(null);
                                        if (isSweeping) {
                                            setSweepingCells((prev) => {
                                                if (!prev.has(cellKey)) return prev;
                                                const next = new Set(prev);
                                                next.delete(cellKey);
                                                return next;
                                            });
                                        }
                                    }}
                                    aria-label={`Row ${r + 1} Column ${c + 1}, ${val > 0 ? `value ${val}` : 'empty'}${isGiven ? ', fixed clue' : ''}`}
                                >
                                    {val > 0 ? val : ''}
                                </button>
                                {rightIneq && (
                                    <span
                                        className={[
                                            styles.ineqRight,
                                            rightStatus === 'satisfied' ? styles.ineqSatisfied : '',
                                            rightStatus === 'invalid' ? styles.ineqInvalid : '',
                                        ].filter(Boolean).join(' ')}
                                        aria-hidden="true"
                                    >
                                        {rightIneq}
                                    </span>
                                )}
                                {downIneq && (
                                    <span
                                        className={[
                                            styles.ineqDown,
                                            downStatus === 'satisfied' ? styles.ineqSatisfied : '',
                                            downStatus === 'invalid' ? styles.ineqInvalid : '',
                                        ].filter(Boolean).join(' ')}
                                        aria-hidden="true"
                                    >
                                        {downIneq}
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
                <button
                    type="button"
                    className={`${styles.ctrlBtn} ${showStrategy ? styles.ctrlBtnPrimary : ''}`}
                    onClick={() => setShowStrategy((s) => !s)}
                    disabled={submitting}
                    aria-expanded={showStrategy}
                    title="View pro deduction checklist & mental model"
                >
                    <i className="bi bi-patch-question-fill" aria-hidden="true" />
                    <span>How to Think</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={initGrid} disabled={submitting}>
                    <i className="bi bi-arrow-repeat" aria-hidden="true" />
                    <span>Reset</span>
                </button>
            </div>

            {showStrategy && (
                <div className={styles.howToThinkInline} role="region" aria-label="KinGrid deduction checklist">
                    <div className={styles.howToThinkHeader}>
                        <i className="bi bi-compass text-warning" aria-hidden="true" />
                        <strong>KinGrid Mental Deduction Playbook</strong>
                    </div>
                    <div className={styles.howToThinkGrid}>
                        <div className={styles.howToThinkItem}>
                            <span className={styles.howToThinkIcon}><i className="bi bi-bullseye" aria-hidden="true" /></span>
                            <div>
                                <strong>1. Hunt Bounded Clues First</strong>
                                <p>Look for <code>2 &gt; [?]</code> (forces 1) or <code>[?] &lt; 4</code> (forces 5) for instant freebies without guessing.</p>
                            </div>
                        </div>
                        <div className={styles.howToThinkItem}>
                            <span className={styles.howToThinkIcon}><i className="bi bi-123" aria-hidden="true" /></span>
                            <div>
                                <strong>2. The Extreme Digits Rule</strong>
                                <p><strong>1</strong> can never point away to a smaller square. <strong>5</strong> can never be pointed at as smaller.</p>
                            </div>
                        </div>
                        <div className={styles.howToThinkItem}>
                            <span className={styles.howToThinkIcon}><i className="bi bi-grid-3x3" aria-hidden="true" /></span>
                            <div>
                                <strong>3. Naked Singles in Rows/Cols</strong>
                                <p>Scan for any row or column with 4 numbers filled — the 5th number is free and determined automatically.</p>
                            </div>
                        </div>
                        <div className={styles.howToThinkItem}>
                            <span className={styles.howToThinkIcon}><i className="bi bi-arrows-angle-expand" aria-hidden="true" /></span>
                            <div>
                                <strong>4. Inequality Chains</strong>
                                <p>In chains like <code>A &lt; B &lt; C</code>, A can never be 4 or 5, C can never be 1 or 2, and B is trapped in between.</p>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
