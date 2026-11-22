import { useState, useEffect, useRef } from 'preact/hooks';
import gsap from 'gsap';
import { playArcadeSfx, formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

function fnv1aHex(input) {
    let hash = 0x811c9dc5;
    for (let i = 0; i < input.length; i++) {
        hash ^= input.charCodeAt(i);
        hash = Math.imul(hash, 0x01000193) >>> 0;
    }
    return hash.toString(16).padStart(8, '0');
}

export function KinWordGame({ puzzle, onProgress, onComplete, submitting }) {
    const seedKey = puzzle?.seedKey || '';
    const manifest = puzzle?.groupManifest || {};
    const maxMistakes = puzzle?.maxMistakes || 4;

    const [remainingTiles, setRemainingTiles] = useState([]);
    const [selected, setSelected] = useState([]);
    const [solvedGroups, setSolvedGroups] = useState([]);
    const [mistakes, setMistakes] = useState(0);
    const [hintsUsed, setHintsUsed] = useState(0);
    const [elapsedMs, setElapsedMs] = useState(0);
    const [statusNote, setStatusNote] = useState('Pick 4 words that go together (like 4 foods or 4 places), then tap Submit.');

    const startTimeRef = useRef(Date.now());
    const replayRef = useRef([]);
    const completedRef = useRef(false);
    const solvedWrapRef = useRef(null);

    useEffect(() => {
        setRemainingTiles(Array.isArray(puzzle?.tiles) ? [...puzzle.tiles] : []);
        setSelected([]);
        setSolvedGroups([]);
        setMistakes(0);
        setHintsUsed(0);
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        replayRef.current = [];
        completedRef.current = false;
        setStatusNote('Pick 4 words that go together (like 4 foods or 4 places), then tap Submit.');
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, []);

    useEffect(() => {
        const pct = Math.min(100, Math.round((solvedGroups.length / 4) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [solvedGroups.length]);

    const toggleTile = (word) => {
        if (completedRef.current || submitting) return;
        playArcadeSfx('tick');
        setSelected((prev) => {
            if (prev.includes(word)) {
                return prev.filter((w) => w !== word);
            }
            if (prev.length >= 4) return prev;
            return [...prev, word];
        });
    };

    const handleShuffle = () => {
        setRemainingTiles((prev) => {
            const copy = [...prev];
            for (let i = copy.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [copy[i], copy[j]] = [copy[j], copy[i]];
            }
            return copy;
        });
        playArcadeSfx('tick');
    };

    const handleSubmitGroup = () => {
        if (selected.length !== 4 || completedRef.current || submitting) return;

        const sorted = [...selected].map((w) => String(w).toUpperCase()).sort();
        const key = fnv1aHex(`${seedKey}:${sorted.join('|')}`);
        const match = manifest[key];

        if (match) {
            const newGroup = {
                title: match.title,
                tier: match.tier || 1,
                words: sorted,
            };
            const nextSolved = [...solvedGroups, newGroup];
            setSolvedGroups(nextSolved);
            setRemainingTiles((prev) => prev.filter((w) => !selected.includes(w)));
            setSelected([]);
            replayRef.current.push({ t: Date.now() - startTimeRef.current, group: match.title });
            playArcadeSfx('connect', nextSolved.length * 4);
            setStatusNote(`Found "${match.title}"!`);

            // GSAP spring reveal
            if (solvedWrapRef.current && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                window.requestAnimationFrame(() => {
                    const lastBanner = solvedWrapRef.current?.lastElementChild;
                    if (lastBanner) {
                        gsap.fromTo(lastBanner, { scale: 0.9, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.4, ease: 'back.out(1.7)' });
                    }
                });
            }

            if (nextSolved.length === 4) {
                completedRef.current = true;
                const finalMs = Date.now() - startTimeRef.current;
                setElapsedMs(finalMs);
                playArcadeSfx('complete');
                setStatusNote('Splendid! All 4 word groups solved!');
                if (onComplete) {
                    onComplete({
                        submission: { groups: nextSolved.map((g) => g.words) },
                        elapsedMs: finalMs,
                        movesCount: replayRef.current.length,
                        hintsUsed,
                        mistakesCount: mistakes,
                        replay: replayRef.current,
                    });
                }
            }
        } else {
            // Check if 3 of the 4 selected words belong to a valid group ("one away")
            const selUpper = selected.map((w) => String(w).toUpperCase());
            const others = remainingTiles.filter((w) => !selected.includes(w));
            let isOneAway = false;
            for (let skip = 0; skip < 4 && !isOneAway; skip++) {
                const three = selUpper.filter((_, idx) => idx !== skip);
                for (const cand of others) {
                    const testSet = [...three, String(cand).toUpperCase()].sort();
                    if (manifest[fnv1aHex(`${seedKey}:${testSet.join('|')}`)]) {
                        isOneAway = true;
                        break;
                    }
                }
            }

            const nextMistakes = mistakes + 1;
            setMistakes(nextMistakes);
            playArcadeSfx('error');
            if (nextMistakes >= maxMistakes) {
                setStatusNote('Out of hearts! Board reset so you can try again.');
                setRemainingTiles(Array.isArray(puzzle?.tiles) ? [...puzzle.tiles] : []);
                setSolvedGroups([]);
                setSelected([]);
                setMistakes(0);
            } else if (isOneAway) {
                setStatusNote(`So close! 3 of those 4 words go together — swap 1 word! (${maxMistakes - nextMistakes} hearts left)`);
            } else {
                setStatusNote(`Those 4 don't share the same theme — ${maxMistakes - nextMistakes} hearts left.`);
            }
        }
    };

    const handleHint = () => {
        if (completedRef.current || remainingTiles.length < 4) return;
        // Brute-force check combinations of 4 remaining tiles against manifest (<= C(16,4) = 1820 checks, <1ms!)
        const n = remainingTiles.length;
        for (let a = 0; a < n - 3; a++) {
            for (let b = a + 1; b < n - 2; b++) {
                for (let c = b + 1; c < n - 1; c++) {
                    for (let d = c + 1; d < n; d++) {
                        const quartet = [remainingTiles[a], remainingTiles[b], remainingTiles[c], remainingTiles[d]];
                        const sorted = quartet.map((w) => String(w).toUpperCase()).sort();
                        const key = fnv1aHex(`${seedKey}:${sorted.join('|')}`);
                        if (manifest[key]) {
                            setHintsUsed((h) => h + 1);
                            setSelected(quartet.slice(0, 2));
                            setStatusNote(`Hint: highlighted 2 words from "${manifest[key].title}".`);
                            playArcadeSfx('tick');
                            return;
                        }
                    }
                }
            }
        }
    };

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Hearts:</span>
                    {Array.from({ length: maxMistakes }).map((_, idx) => (
                        <i
                            key={idx}
                            className={`bi ${idx < maxMistakes - mistakes ? 'bi-heart-fill text-danger' : 'bi-heart text-muted'}`}
                            aria-hidden="true"
                        />
                    ))}
                </div>
                <div className={styles.metaBadge}>
                    <span>{solvedGroups.length}/4 groups</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            {solvedGroups.length > 0 && (
                <div ref={solvedWrapRef} className={styles.wordSolvedList}>
                    {solvedGroups.map((g) => (
                        <div key={g.title} className={`${styles.wordSolvedBanner} ${styles[`wordTier${g.tier}`] || ''}`}>
                            <div className={styles.wordSolvedTitle}>{g.title}</div>
                            <div className={styles.wordSolvedItems}>{g.words.join(' • ')}</div>
                        </div>
                    ))}
                </div>
            )}

            <div className={styles.wordGrid} role="group" aria-label="KinWord 4 by 4 tiles">
                {remainingTiles.map((word) => {
                    const isSel = selected.includes(word);
                    const isLong = String(word).length >= 9;
                    return (
                        <button
                            key={word}
                            type="button"
                            title={word}
                            className={`${styles.wordTile} ${isLong ? styles.wordTileLong : ''} ${isSel ? styles.wordTileSelected : ''}`}
                            onClick={() => toggleTile(word)}
                            aria-pressed={isSel}
                        >
                            {word}
                        </button>
                    );
                })}
            </div>

            <div className={styles.gameControls}>
                <button type="button" className={styles.ctrlBtn} onClick={handleShuffle} disabled={submitting}>
                    <i className="bi bi-shuffle" aria-hidden="true" />
                    <span>Shuffle</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={() => setSelected([])} disabled={selected.length === 0 || submitting}>
                    <i className="bi bi-x-circle" aria-hidden="true" />
                    <span>Deselect</span>
                </button>
                <button type="button" className={styles.ctrlBtn} onClick={handleHint} disabled={submitting}>
                    <i className="bi bi-lightbulb-fill" aria-hidden="true" />
                    <span>Hint ({hintsUsed})</span>
                </button>
                <button
                    type="button"
                    className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary}`}
                    onClick={handleSubmitGroup}
                    disabled={selected.length !== 4 || submitting}
                >
                    <i className="bi bi-check2-circle" aria-hidden="true" />
                    <span>Submit</span>
                </button>
            </div>
        </div>
    );
}
