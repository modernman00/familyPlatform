import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs, kinZipEdgeKey, findKinZipHintStep } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

export function KinZipGame({ puzzle, onProgress, onComplete, submitting }) {
    const size = puzzle?.size || 6;
    const totalCells = size * size;
    const checkpoints = puzzle?.checkpoints || [];
    const walls = puzzle?.walls || [];

    const [path, setPath] = useState([]);
    const [hintsUsed, setHintsUsed] = useState(0);
    const [hintCell, setHintCell] = useState(null);
    const [elapsedMs, setElapsedMs] = useState(0);
    const [isDragging, setIsDragging] = useState(false);
    const [statusNote, setStatusNote] = useState('Tap dot 1 or drag across the grid to connect every cell in order.');

    const startTimeRef = useRef(Date.now());
    const replayRef = useRef([]);
    const completedRef = useRef(false);
    const gridRef = useRef(null);

    const wallSet = useMemo(() => {
        const s = new Set();
        walls.forEach(([r1, c1, r2, c2]) => {
            s.add(kinZipEdgeKey(r1, c1, r2, c2));
        });
        return s;
    }, [walls]);

    const cpMap = useMemo(() => {
        const m = new Map();
        checkpoints.forEach((cp) => m.set(`${cp.r},${cp.c}`, cp.num));
        return m;
    }, [checkpoints]);

    // Reset when puzzle changes
    useEffect(() => {
        setPath([]);
        setHintsUsed(0);
        setHintCell(null);
        setElapsedMs(0);
        setStatusNote('Tap dot 1 or drag across the grid to connect every cell in order.');
        startTimeRef.current = Date.now();
        replayRef.current = [];
        completedRef.current = false;
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    // Live timer
    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, []);

    // Report progress % to Ghost / SSE Race bar
    useEffect(() => {
        const pct = Math.min(100, Math.round((path.length / totalCells) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [path.length, totalCells]);

    const nextExpectedCheckpoint = useMemo(() => {
        let next = 1;
        for (const [r, c] of path) {
            if (cpMap.get(`${r},${c}`) === next) {
                next += 1;
            }
        }
        return next;
    }, [path, cpMap]);

    const checkCompletion = (nextPath) => {
        if (completedRef.current || nextPath.length !== totalCells) return;
        let expected = 1;
        for (const [r, c] of nextPath) {
            const num = cpMap.get(`${r},${c}`);
            if (num !== undefined) {
                if (num !== expected) return;
                expected += 1;
            }
        }
        if (expected <= checkpoints.length) return;

        completedRef.current = true;
        const finalMs = Date.now() - startTimeRef.current;
        setElapsedMs(finalMs);
        playArcadeSfx('complete');
        setStatusNote('Brilliant! Every cell and dot connected!');
        if (onComplete) {
            onComplete({
                submission: { path: nextPath },
                elapsedMs: finalMs,
                movesCount: replayRef.current.length,
                hintsUsed,
                mistakesCount: 0,
                replay: replayRef.current,
            });
        }
    };

    const tryStepTo = (r, c) => {
        if (completedRef.current || submitting) return;
        setHintCell(null);

        const existingIdx = path.findIndex(([pr, pc]) => pr === r && pc === c);
        if (existingIdx !== -1) {
            // Backtrack if user taps an earlier cell (not the current tip)
            if (existingIdx < path.length - 1) {
                const sliced = path.slice(0, existingIdx + 1);
                setPath(sliced);
                replayRef.current.push({ t: Date.now() - startTimeRef.current, op: 'back', len: sliced.length });
                playArcadeSfx('tick');
            }
            return;
        }

        if (path.length === 0) {
            if (cpMap.get(`${r},${c}`) !== 1) {
                setStatusNote('Start on dot 1 first.');
                playArcadeSfx('error');
                return;
            }
            const nextPath = [[r, c]];
            setPath(nextPath);
            replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c });
            playArcadeSfx('connect', 1);
            setStatusNote('Keep going — connect dot 2 next.');
            return;
        }

        const [lr, lc] = path[path.length - 1];
        if (Math.abs(r - lr) + Math.abs(c - lc) !== 1) {
            return; // Only orthogonal adjacent moves
        }
        if (wallSet.has(kinZipEdgeKey(lr, lc, r, c))) {
            setStatusNote('Blocked by a wall — route around it.');
            playArcadeSfx('error');
            return;
        }

        const cellCp = cpMap.get(`${r},${c}`);
        if (cellCp !== undefined && cellCp !== nextExpectedCheckpoint) {
            setStatusNote(`Visit dot ${nextExpectedCheckpoint} before dot ${cellCp}.`);
            playArcadeSfx('error');
            return;
        }

        const nextPath = [...path, [r, c]];
        setPath(nextPath);
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c });
        playArcadeSfx('connect', nextPath.length);
        setStatusNote(
            nextPath.length === totalCells
                ? 'Grid filled!'
                : `${nextPath.length}/${totalCells} cells filled`
        );
        checkCompletion(nextPath);
    };

    const handlePointerMove = (e) => {
        if (!isDragging || !gridRef.current) return;
        const rect = gridRef.current.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        if (x < 0 || y < 0 || x >= rect.width || y >= rect.height) return;
        const c = Math.floor((x / rect.width) * size);
        const r = Math.floor((y / rect.height) * size);
        if (r >= 0 && r < size && c >= 0 && c < size) {
            tryStepTo(r, c);
        }
    };

    const handleUndo = () => {
        if (path.length === 0 || completedRef.current) return;
        const next = path.slice(0, -1);
        setPath(next);
        playArcadeSfx('tick');
    };

    const handleReset = () => {
        if (completedRef.current) return;
        setPath([]);
        setHintCell(null);
        setStatusNote('Board reset. Start on dot 1.');
        playArcadeSfx('tick');
    };

    const handleHint = () => {
        if (completedRef.current) return;
        const nextStep = findKinZipHintStep(puzzle, path);
        if (nextStep) {
            setHintsUsed((h) => h + 1);
            setHintCell(`${nextStep[0]},${nextStep[1]}`);
            tryStepTo(nextStep[0], nextStep[1]);
        } else {
            setStatusNote('Try undoing a few steps — a dot or corner may be blocked.');
            playArcadeSfx('error');
        }
    };

    const pathIndexMap = useMemo(() => {
        const m = new Map();
        path.forEach(([r, c], idx) => m.set(`${r},${c}`, idx));
        return m;
    }, [path]);

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
                    <span>{path.length}/{totalCells} cells</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            <div
                ref={gridRef}
                className={styles.zipGrid}
                style={{ gridTemplateColumns: `repeat(${size}, minmax(0, 1fr))` }}
                onPointerUp={() => setIsDragging(false)}
                onPointerLeave={() => setIsDragging(false)}
                onPointerMove={handlePointerMove}
                role="grid"
                aria-label={`KinZip ${size} by ${size} puzzle grid`}
            >
                {/* SVG Path Overlay */}
                {path.length > 1 && (
                    <svg
                        className={styles.zipSvgOverlay}
                        viewBox={`0 0 ${size * 100} ${size * 100}`}
                        preserveAspectRatio="none"
                        aria-hidden="true"
                    >
                        <polyline
                            fill="none"
                            stroke="url(#kinZipGrad)"
                            strokeWidth="34"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            points={path.map(([r, c]) => `${c * 100 + 50},${r * 100 + 50}`).join(' ')}
                        />
                        <defs>
                            <linearGradient id="kinZipGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stopColor="#f59e0b" />
                                <stop offset="100%" stopColor="#dc2626" />
                            </linearGradient>
                        </defs>
                    </svg>
                )}

                {Array.from({ length: size }).map((_, r) =>
                    Array.from({ length: size }).map((__, c) => {
                        const key = `${r},${c}`;
                        const cpNum = cpMap.get(key);
                        const pIdx = pathIndexMap.get(key);
                        const isVisited = pIdx !== undefined;
                        const isHead = pIdx === path.length - 1 && path.length > 0;
                        const isHint = hintCell === key;

                        const wallTop = r > 0 && wallSet.has(kinZipEdgeKey(r, c, r - 1, c));
                        const wallBottom = r + 1 < size && wallSet.has(kinZipEdgeKey(r, c, r + 1, c));
                        const wallLeft = c > 0 && wallSet.has(kinZipEdgeKey(r, c, r, c - 1));
                        const wallRight = c + 1 < size && wallSet.has(kinZipEdgeKey(r, c, r, c + 1));

                        return (
                            <button
                                key={key}
                                type="button"
                                className={[
                                    styles.zipCell,
                                    isVisited ? styles.zipCellVisited : '',
                                    isHead ? styles.zipCellHead : '',
                                    isHint ? styles.zipCellHint : '',
                                    wallTop ? styles.wallTop : '',
                                    wallBottom ? styles.wallBottom : '',
                                    wallLeft ? styles.wallLeft : '',
                                    wallRight ? styles.wallRight : '',
                                ].filter(Boolean).join(' ')}
                                onPointerDown={(e) => {
                                    e.preventDefault();
                                    setIsDragging(true);
                                    tryStepTo(r, c);
                                }}
                                aria-label={`Row ${r + 1} Column ${c + 1}${cpNum ? `, Dot ${cpNum}` : ''}${isVisited ? ', visited' : ''}`}
                            >
                                {cpNum !== undefined && (
                                    <span className={`${styles.zipDot} ${isVisited ? styles.zipDotActive : ''}`}>
                                        {cpNum}
                                    </span>
                                )}
                            </button>
                        );
                    })
                )}
            </div>

            <div className={styles.gameControls}>
                <button type="button" className={styles.ctrlBtn} onClick={handleUndo} disabled={path.length === 0 || submitting}>
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Undo</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={handleHint} disabled={submitting}>
                    <i className="bi bi-lightbulb-fill" aria-hidden="true" />
                    <span>Hint ({hintsUsed})</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={handleReset} disabled={path.length === 0 || submitting}>
                    <i className="bi bi-arrow-repeat" aria-hidden="true" />
                    <span>Reset</span>
                </button>
            </div>
        </div>
    );
}
