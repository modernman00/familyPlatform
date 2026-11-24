import { useState, useEffect, useRef } from 'preact/hooks';
import { formatElapsedMs } from './arcadeEngine';
import { createGameSfx } from './hooks/useSoundEffects';
import styles from './ArcadeShell.module.css';

const KEYBOARD_ROWS = [
    ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
    ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
    ['ENTER', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', 'DEL'],
];

export function WordieGame({ puzzle, onProgress, onComplete, submitting, sound }) {
    const playArcadeSfx = createGameSfx(sound);
    const wordLength = puzzle?.wordLength || 5;
    const maxGuesses = puzzle?.maxGuesses || 6;
    const targetWord = (puzzle?.targetWord || (puzzle?.targetWordHash ? atob(puzzle.targetWordHash) : 'HEART')).toUpperCase();

    const [guesses, setGuesses] = useState([]);
    const [currentGuess, setCurrentGuess] = useState('');
    const [gameStatus, setGameStatus] = useState('playing'); // 'playing' | 'won' | 'lost'
    const [elapsedMs, setElapsedMs] = useState(0);
    const [statusNote, setStatusNote] = useState('Guess the 5-letter family word in 6 tries. Green is correct, gold is misplaced.');

    const startTimeRef = useRef(Date.now());
    const completedRef = useRef(false);
    const replayRef = useRef([]);

    useEffect(() => {
        setGuesses([]);
        setCurrentGuess('');
        setGameStatus('playing');
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
        setStatusNote('Guess the 5-letter family word in 6 tries. Green is correct, gold is misplaced.');
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current && gameStatus === 'playing') {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, [gameStatus]);

    useEffect(() => {
        const pct = gameStatus === 'won' ? 100 : Math.min(95, Math.round((guesses.length / maxGuesses) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [guesses.length, gameStatus]);

    // Handle physical keyboard typing
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (completedRef.current || gameStatus !== 'playing' || submitting) return;
            const key = e.key.toUpperCase();
            if (key === 'ENTER') {
                handleSubmitGuess();
            } else if (key === 'BACKSPACE') {
                handleDeleteKey();
            } else if (/^[A-Z]$/.test(key) && key.length === 1) {
                handleLetterKey(key);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [currentGuess, guesses, gameStatus, submitting]);

    const handleLetterKey = (letter) => {
        if (currentGuess.length < wordLength) {
            playArcadeSfx('tick');
            setCurrentGuess((prev) => prev + letter);
        }
    };

    const handleDeleteKey = () => {
        if (currentGuess.length > 0) {
            playArcadeSfx('tick');
            setCurrentGuess((prev) => prev.slice(0, -1));
        }
    };

    const evaluateGuess = (guess) => {
        const targetChars = targetWord.split('');
        const guessChars = guess.split('');
        const res = arrayFill(wordLength, 'absent');
        const letterCounts = {};

        for (let i = 0; i < wordLength; i++) {
            const ch = targetChars[i];
            letterCounts[ch] = (letterCounts[ch] || 0) + 1;
        }

        // Exact matches first
        for (let i = 0; i < wordLength; i++) {
            if (guessChars[i] === targetChars[i]) {
                res[i] = 'correct';
                letterCounts[guessChars[i]]--;
            }
        }

        // Misplaced matches
        for (let i = 0; i < wordLength; i++) {
            if (res[i] !== 'correct') {
                const ch = guessChars[i];
                if (letterCounts[ch] > 0) {
                    res[i] = 'present';
                    letterCounts[ch]--;
                }
            }
        }

        return res;
    };

    const handleSubmitGuess = () => {
        if (currentGuess.length < wordLength) {
            playArcadeSfx('error');
            setStatusNote(`Too short! Need ${wordLength} letters.`);
            return;
        }

        const newGuesses = [...guesses, currentGuess];
        setGuesses(newGuesses);
        setCurrentGuess('');

        replayRef.current.push({
            t: Date.now() - startTimeRef.current,
            guess: currentGuess,
        });

        if (currentGuess === targetWord) {
            completedRef.current = true;
            setGameStatus('won');
            const finalMs = Date.now() - startTimeRef.current;
            setElapsedMs(finalMs);
            playArcadeSfx('complete');
            setStatusNote(`Brilliant! Solved in ${newGuesses.length} ${newGuesses.length === 1 ? 'guess' : 'guesses'}!`);

            if (onComplete) {
                onComplete({
                    submission: { guesses: newGuesses, solved: true },
                    elapsedMs: finalMs,
                    movesCount: newGuesses.length,
                    hintsUsed: 0,
                    mistakesCount: Math.max(0, newGuesses.length - 1),
                    replay: replayRef.current,
                });
            }
        } else if (newGuesses.length >= maxGuesses) {
            setGameStatus('lost');
            playArcadeSfx('error');
            setStatusNote(`The word was ${targetWord}! Try again.`);
        } else {
            playArcadeSfx('connect', newGuesses.length);
            setStatusNote(`Guess ${newGuesses.length + 1} of ${maxGuesses}`);
        }
    };

    // Calculate keyboard key letter status
    const letterStatuses = {};
    guesses.forEach((g) => {
        const ev = evaluateGuess(g);
        for (let i = 0; i < wordLength; i++) {
            const letter = g[i];
            const state = ev[i];
            const current = letterStatuses[letter];
            if (state === 'correct') {
                letterStatuses[letter] = 'correct';
            } else if (state === 'present' && current !== 'correct') {
                letterStatuses[letter] = 'present';
            } else if (state === 'absent' && !current) {
                letterStatuses[letter] = 'absent';
            }
        }
    });

    const rows = [];
    for (let r = 0; r < maxGuesses; r++) {
        if (r < guesses.length) {
            const g = guesses[r];
            const ev = evaluateGuess(g);
            rows.push({ guess: g, ev, submitted: true });
        } else if (r === guesses.length) {
            rows.push({ guess: currentGuess, ev: [], submitted: false });
        } else {
            rows.push({ guess: '', ev: [], submitted: false });
        }
    }

    return (
        <div className={styles.gameBoardWrap}>
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Guess {Math.min(maxGuesses, guesses.length + (gameStatus === 'playing' ? 1 : 0))}/{maxGuesses}</span>
                </div>
            </div>

            <p className={styles.gameInstruction} aria-live="polite">{statusNote}</p>

            <div className={styles.wordieBoard} role="group" aria-label="Wordie 5-letter grid">
                {rows.map((row, rIdx) => (
                    <div key={rIdx} className={styles.wordieRow}>
                        {Array.from({ length: wordLength }).map((_, cIdx) => {
                            const letter = row.guess[cIdx] || '';
                            const status = row.submitted ? row.ev[cIdx] : '';
                            return (
                                <div
                                    key={cIdx}
                                    className={`${styles.wordieTile} ${status ? styles[`wordieTile_${status}`] : ''} ${!row.submitted && letter ? styles.wordieTilePop : ''}`}
                                    style={{ '--flip-delay': `${cIdx * 0.08}s` }}
                                >
                                    <span>{letter}</span>
                                </div>
                            );
                        })}
                    </div>
                ))}
            </div>

            <div className={styles.wordieKeyboard} role="group" aria-label="Virtual on-screen keyboard">
                {KEYBOARD_ROWS.map((kRow, rowIdx) => (
                    <div key={rowIdx} className={`${styles.wordieKeyRow} ${rowIdx === 1 ? styles.wordieKeyRowMid : ''}`}>
                        {kRow.map((key) => {
                            const isSpecial = key === 'ENTER' || key === 'DEL';
                            const state = letterStatuses[key] || '';
                            return (
                                <button
                                    key={key}
                                    type="button"
                                    className={`${styles.wordieKey} ${isSpecial ? styles.wordieKeySpecial : ''} ${state ? styles[`wordieKey_${state}`] : ''}`}
                                    onClick={() => {
                                        if (key === 'ENTER') handleSubmitGuess();
                                        else if (key === 'DEL') handleDeleteKey();
                                        else handleLetterKey(key);
                                    }}
                                    disabled={gameStatus !== 'playing' || submitting}
                                    aria-label={key === 'DEL' ? 'Delete letter' : key}
                                >
                                    {key === 'DEL' ? <i className="bi bi-backspace" aria-hidden="true" /> : key}
                                </button>
                            );
                        })}
                    </div>
                ))}
            </div>
        </div>
    );
}

function arrayFill(len, val) {
    const arr = [];
    for (let i = 0; i < len; i++) arr.push(val);
    return arr;
}
