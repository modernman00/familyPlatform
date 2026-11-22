import { useState, useEffect, useRef } from 'preact/hooks';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

export function MinesweeperGame({ puzzle, onProgress, onComplete, submitting }) {
    const rows = puzzle?.rows || 8;
    const cols = puzzle?.cols || 8;
    const totalMines = puzzle?.totalMines || 10;
    const safeCellsCount = puzzle?.safeCellsCount || (rows * cols - totalMines);
    const gridNumbers = puzzle?.grid || Array.from({ length: rows }, () => Array(cols).fill(0));

    // revealed[r][c]: boolean
    const [revealed, setRevealed] = useState(() => Array.from({ length: rows }, () => Array(cols).fill(false)));
    // flagged[r][c]: boolean
    const [flagged, setFlagged] = useState(() => Array.from({ length: rows }, () => Array(cols).fill(false)));
    const [flagMode, setFlagMode] = useState(false); // toggle between Dig and Flag mode
    const [gameOver, setGameOver] = useState(false);
    const [elapsedMs, setElapsedMs] = useState(0);
    const [statusNote, setStatusNote] = useState(`Uncover all ${safeCellsCount} safe squares. Avoid the ${totalMines} hidden mines!`);

    const startTimeRef = useRef(Date.now());
    const completedRef = useRef(false);
    const replayRef = useRef([]);

    useEffect(() => {
        setRevealed(Array.from({ length: rows }, () => Array(cols).fill(false)));
        setFlagged(Array.from({ length: rows }, () => Array(cols).fill(false)));
        setGameOver(false);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
        setStatusNote(`Uncover all ${safeCellsCount} safe squares. Avoid the ${totalMines} hidden mines!`);
    }, [puzzle?.seedKey, puzzle?.difficulty, rows, cols]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current && !gameOver) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, [gameOver]);

    // Count revealed safe cells
    let safeRevealedCount = 0;
    let flagsCount = 0;
    const uncoveredList = [];

    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            if (revealed[r]?.[c]) {
                if (gridNumbers[r]?.[c] !== -1) {
                    safeRevealedCount++;
                    uncoveredList.push({ r, c });
                }
            }
            if (flagged[r]?.[c]) {
                flagsCount++;
            }
        }
    }

    useEffect(() => {
        const pct = Math.min(100, Math.round((safeRevealedCount / safeCellsCount) * 100));
        if (onProgress) onProgress(pct, elapsedMs);

        if (safeRevealedCount === safeCellsCount && !completedRef.current && !gameOver) {
            completedRef.current = true;
            const finalMs = Date.now() - startTimeRef.current;
            setElapsedMs(finalMs);
            playArcadeSfx('complete');
            setStatusNote('Minefield cleared! Superb tactical pathfinding!');

            if (onComplete) {
                onComplete({
                    submission: {
                        uncovered: uncoveredList,
                    },
                    elapsedMs: finalMs,
                    movesCount: replayRef.current.length,
                    hintsUsed: 0,
                    mistakesCount: 0,
                    replay: replayRef.current,
                });
            }
        }
    }, [safeRevealedCount, safeCellsCount, gameOver]);

    const revealCellAndCascade = (startR, startC) => {
        if (revealed[startR][startC] || flagged[startR][startC]) return;

        const val = gridNumbers[startR][startC];
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r: startR, c: startC, action: 'reveal' });

        if (val === -1) {
            // Detonated a mine!
            setGameOver(true);
            playArcadeSfx('error');
            setStatusNote('💥 Mine detonated! Tap Retry to try again.');
            // Reveal all mines
            setRevealed((prev) =>
                prev.map((rArr, r) =>
                    rArr.map((v, c) => (gridNumbers[r][c] === -1 ? true : v))
                )
            );
            return;
        }

        // BFS Cascade if val === 0
        const newRevealed = revealed.map((rArr) => [...rArr]);
        const queue = [[startR, startC]];
        newRevealed[startR][startC] = true;

        let cascadeCount = 1;
        while (queue.length > 0) {
            const [curR, curC] = queue.shift();
            const curVal = gridNumbers[curR][curC];

            if (curVal === 0) {
                for (let dr = -1; dr <= 1; dr++) {
                    for (let dc = -1; dc <= 1; dc++) {
                        if (dr === 0 && dc === 0) continue;
                        const nr = curR + dr;
                        const nc = curC + dc;
                        if (
                            nr >= 0 &&
                            nr < rows &&
                            nc >= 0 &&
                            nc < cols &&
                            !newRevealed[nr][nc] &&
                            !flagged[nr][nc]
                        ) {
                            newRevealed[nr][nc] = true;
                            cascadeCount++;
                            if (gridNumbers[nr][nc] === 0) {
                                queue.push([nr, nc]);
                            }
                        }
                    }
                }
            }
        }

        setRevealed(newRevealed);
        playArcadeSfx('connect', cascadeCount);
    };

    const toggleFlag = (r, c) => {
        if (revealed[r][c] || completedRef.current || gameOver || submitting) return;
        playArcadeSfx('tick');
        const nextFlagged = flagged.map((rArr, rIdx) => {
            if (rIdx !== r) return rArr;
            const copy = [...rArr];
            copy[c] = !copy[c];
            return copy;
        });
        setFlagged(nextFlagged);
        replayRef.current.push({ t: Date.now() - startTimeRef.current, r, c, action: 'flag' });
    };

    const handleCellClick = (r, c) => {
        if (completedRef.current || gameOver || submitting) return;
        if (flagMode) {
            toggleFlag(r, c);
        } else {
            revealCellAndCascade(r, c);
        }
    };

    const handleCellContextMenu = (e, r, c) => {
        e.preventDefault();
        toggleFlag(r, c);
    };

    const handleReset = () => {
        setRevealed(Array.from({ length: rows }, () => Array(cols).fill(false)));
        setFlagged(Array.from({ length: rows }, () => Array(cols).fill(false)));
        setGameOver(false);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
        playArcadeSfx('error');
        setStatusNote(`Uncover all ${safeCellsCount} safe squares. Avoid the ${totalMines} hidden mines!`);
    };

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>🚩 {totalMines - flagsCount}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Safe: {safeRevealedCount}/{safeCellsCount}</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            <div
                className={styles.minesweeperBoard}
                style={{ '--mine-cols': cols }}
                role="grid"
                aria-label={`Minesweeper ${rows} by ${cols} grid`}
            >
                {revealed.map((rArr, r) => (
                    <div key={r} className={styles.minesweeperRow} role="row">
                        {rArr.map((isRev, c) => {
                            const val = gridNumbers[r][c];
                            const isFlag = flagged[r][c];
                            const isMine = val === -1;

                            return (
                                <button
                                    key={c}
                                    type="button"
                                    className={`${styles.mineCell} ${isRev ? (isMine ? styles.mineCellExploded : styles.mineCellRevealed) : ''} ${isFlag ? styles.mineCellFlagged : ''}`}
                                    onClick={() => handleCellClick(r, c)}
                                    onContextMenu={(e) => handleCellContextMenu(e, r, c)}
                                    disabled={completedRef.current || submitting}
                                    aria-label={`Row ${r + 1}, Col ${c + 1}: ${isRev ? (isMine ? 'Mine' : val) : isFlag ? 'Flagged' : 'Unopened'}`}
                                >
                                    {isRev && isMine && <span aria-hidden="true">💣</span>}
                                    {isRev && !isMine && val > 0 && (
                                        <span className={styles[`mineNum${val}`] || ''}>
                                            {val}
                                        </span>
                                    )}
                                    {!isRev && isFlag && <span aria-hidden="true">🚩</span>}
                                </button>
                            );
                        })}
                    </div>
                ))}
            </div>

            <div className={styles.gameControls}>
                <button
                    type="button"
                    className={`${styles.ctrlBtn} ${flagMode ? styles.ctrlBtnPrimary : ''}`}
                    onClick={() => setFlagMode(!flagMode)}
                    title={flagMode ? 'Switch to Dig Mode' : 'Switch to Flag Mode'}
                >
                    <i className={flagMode ? 'bi bi-flag-fill' : 'bi bi-crosshair'} aria-hidden="true" />
                    <span>{flagMode ? 'Mode: Flag 🚩' : 'Mode: Reveal ⛏️'}</span>
                </button>
                <button
                    type="button"
                    className={styles.ctrlBtn}
                    onClick={handleReset}
                    disabled={submitting}
                    title="Retry this puzzle"
                >
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Retry</span>
                </button>
            </div>
        </div>
    );
}
