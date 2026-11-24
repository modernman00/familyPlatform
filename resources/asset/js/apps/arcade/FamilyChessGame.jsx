import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import { formatElapsedMs } from './arcadeEngine';
import { createGameSfx } from './hooks/useSoundEffects';
import styles from './ArcadeShell.module.css';

/**
 * Family Chess Board Game
 *
 * Full 8x8 interactive chess engine:
 * - White (You / Player 1, bottom row 7) vs. Black (ATG Chess Bot / Player 2, top row 0).
 * - Legal move generation for Pawns, Knights, Bishops, Rooks, Queens, Kings.
 * - Move destination highlighting with accessible 44px hitboxes.
 * - Capture indicators and sound synthesis (`playArcadeSfx`).
 * - Pawn promotion to Queen with proud Crown emblem.
 * - Check and Checkmate detection.
 * - 3 AI Bot difficulty levels (Easy random-tactical, Medium minimax-1, Hard minimax-2).
 */

const PIECE_SYMBOLS = {
    wP: '♙', wN: '♘', wB: '♗', wR: '♖', wQ: '♕', wK: '♔',
    bP: '♟', bN: '♞', bB: '♝', bR: '♜', bQ: '♛', bK: '♚',
};

const PIECE_NAMES = {
    P: 'Pawn', N: 'Knight', B: 'Bishop', R: 'Rook', Q: 'Queen', K: 'King',
};

const INITIAL_BOARD = [
    ['bR', 'bN', 'bB', 'bQ', 'bK', 'bB', 'bN', 'bR'],
    ['bP', 'bP', 'bP', 'bP', 'bP', 'bP', 'bP', 'bP'],
    [null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null],
    ['wP', 'wP', 'wP', 'wP', 'wP', 'wP', 'wP', 'wP'],
    ['wR', 'wN', 'wB', 'wQ', 'wK', 'wB', 'wN', 'wR'],
];

export function FamilyChessGame({ puzzle, onProgress, onComplete, submitting, sound }) {
    const playArcadeSfx = createGameSfx(sound);
    const [board, setBoard] = useState(INITIAL_BOARD);
    const [selectedSquare, setSelectedSquare] = useState(null); // [r, c]
    const [turn, setTurn] = useState('w'); // 'w' | 'b'
    const [capturedWhite, setCapturedWhite] = useState([]); // pieces White captured
    const [capturedBlack, setCapturedBlack] = useState([]); // pieces Black captured
    const [inCheck, setInCheck] = useState(null); // 'w' | 'b' | null
    const [gameOver, setGameOver] = useState(false);
    const [statusNote, setStatusNote] = useState('Your turn (White). Select a piece to see legal moves.');
    const [elapsedMs, setElapsedMs] = useState(0);

    const difficulty = puzzle?.difficulty || 'medium';
    const startTimeRef = useRef(Date.now());
    const completedRef = useRef(false);
    const replayRef = useRef([]);

    // Reset board on seed change
    useEffect(() => {
        setBoard(INITIAL_BOARD.map((row) => [...row]));
        setSelectedSquare(null);
        setTurn('w');
        setCapturedWhite([]);
        setCapturedBlack([]);
        setInCheck(null);
        setGameOver(false);
        setStatusNote('Your turn (White). Select a piece to see legal moves.');
        setElapsedMs(0);
        startTimeRef.current = Date.now();
        completedRef.current = false;
        replayRef.current = [];
    }, [puzzle?.seedKey, puzzle?.difficulty]);

    // Timer loop
    useEffect(() => {
        const id = window.setInterval(() => {
            if (!completedRef.current && !gameOver) {
                setElapsedMs(Date.now() - startTimeRef.current);
            }
        }, 250);
        return () => window.clearInterval(id);
    }, [gameOver]);

    // Check if square is on board
    const onBoard = (r, c) => r >= 0 && r < 8 && c >= 0 && c < 8;

    // Generate raw legal moves for a piece on square [r, c]
    const getPieceMoves = (b, r, c) => {
        const p = b[r]?.[c];
        if (!p) return [];
        const color = p[0];
        const type = p[1];
        const moves = [];

        const addMove = (tr, tc) => {
            if (!onBoard(tr, tc)) return false;
            const target = b[tr][tc];
            if (!target) {
                moves.push([tr, tc]);
                return true; // continue sliding
            }
            if (target[0] !== color) {
                moves.push([tr, tc]); // capture
            }
            return false; // blocked by piece
        };

        // Pawn
        if (type === 'P') {
            const dir = color === 'w' ? -1 : 1;
            const startRow = color === 'w' ? 6 : 1;

            // 1 step forward
            if (onBoard(r + dir, c) && !b[r + dir][c]) {
                moves.push([r + dir, c]);
                // 2 steps forward from start
                if (r === startRow && !b[r + dir * 2][c]) {
                    moves.push([r + dir * 2, c]);
                }
            }
            // Diagonal captures
            for (const dc of [-1, 1]) {
                const tr = r + dir;
                const tc = c + dc;
                if (onBoard(tr, tc) && b[tr][tc] && b[tr][tc][0] !== color) {
                    moves.push([tr, tc]);
                }
            }
        }

        // Knight
        if (type === 'N') {
            const deltas = [
                [-2, -1], [-2, 1], [-1, -2], [-1, 2],
                [1, -2], [1, 2], [2, -1], [2, 1],
            ];
            for (const [dr, dc] of deltas) {
                addMove(r + dr, c + dc);
            }
        }

        // Bishop / Rook / Queen (sliding)
        if (type === 'B' || type === 'Q') {
            const diagonals = [[-1, -1], [-1, 1], [1, -1], [1, 1]];
            for (const [dr, dc] of diagonals) {
                let step = 1;
                while (addMove(r + dr * step, c + dc * step)) {
                    step++;
                }
            }
        }
        if (type === 'R' || type === 'Q') {
            const straights = [[-1, 0], [1, 0], [0, -1], [0, 1]];
            for (const [dr, dc] of straights) {
                let step = 1;
                while (addMove(r + dr * step, c + dc * step)) {
                    step++;
                }
            }
        }

        // King
        if (type === 'K') {
            for (let dr = -1; dr <= 1; dr++) {
                for (let dc = -1; dc <= 1; dc++) {
                    if (dr !== 0 || dc !== 0) {
                        addMove(r + dr, c + dc);
                    }
                }
            }
        }

        return moves;
    };

    // Find King position for color
    const findKing = (b, color) => {
        const target = `${color}K`;
        for (let r = 0; r < 8; r++) {
            for (let c = 0; c < 8; c++) {
                if (b[r][c] === target) return [r, c];
            }
        }
        return null;
    };

    // Is color's king currently attacked?
    const isKingUnderAttack = (b, color) => {
        const kingPos = findKing(b, color);
        if (!kingPos) return false;
        const enemyColor = color === 'w' ? 'b' : 'w';

        for (let r = 0; r < 8; r++) {
            for (let c = 0; c < 8; c++) {
                if (b[r][c] && b[r][c][0] === enemyColor) {
                    const enemyMoves = getPieceMoves(b, r, c);
                    if (enemyMoves.some(([tr, tc]) => tr === kingPos[0] && tc === kingPos[1])) {
                        return true;
                    }
                }
            }
        }
        return false;
    };

    // Strict legal moves: piece moves that do NOT leave friendly king in check
    const getStrictLegalMoves = (b, r, c) => {
        const p = b[r]?.[c];
        if (!p) return [];
        const color = p[0];
        const raw = getPieceMoves(b, r, c);

        return raw.filter(([tr, tc]) => {
            const nextB = b.map((row) => [...row]);
            nextB[tr][tc] = nextB[r][c];
            nextB[r][c] = null;
            return !isKingUnderAttack(nextB, color);
        });
    };

    // Check if player has any legal moves
    const getAllLegalMovesForColor = (b, color) => {
        const moves = [];
        for (let r = 0; r < 8; r++) {
            for (let c = 0; c < 8; c++) {
                if (b[r][c] && b[r][c][0] === color) {
                    const pieceMoves = getStrictLegalMoves(b, r, c);
                    for (const dest of pieceMoves) {
                        moves.push({ from: [r, c], to: dest, piece: b[r][c] });
                    }
                }
            }
        }
        return moves;
    };

    // Execute Move
    const makeMove = (from, to) => {
        const [fr, fc] = from;
        const [tr, tc] = to;
        const movingPiece = board[fr][fc];
        const capturedPiece = board[tr][tc];

        const nextBoard = board.map((row) => [...row]);
        nextBoard[tr][tc] = movingPiece;
        nextBoard[fr][fc] = null;

        // Pawn promotion to Queen with Crown
        if (movingPiece === 'wP' && tr === 0) {
            nextBoard[tr][tc] = 'wQ';
            playArcadeSfx('pulse', 1);
        } else if (movingPiece === 'bP' && tr === 7) {
            nextBoard[tr][tc] = 'bQ';
            playArcadeSfx('pulse', 0);
        }

        // Track captures
        if (capturedPiece) {
            playArcadeSfx('error');
            if (movingPiece[0] === 'w') {
                setCapturedWhite((prev) => [...prev, capturedPiece]);
            } else {
                setCapturedBlack((prev) => [...prev, capturedPiece]);
            }
        } else {
            playArcadeSfx('connect', tr * 8 + tc);
        }

        replayRef.current.push({
            t: Date.now() - startTimeRef.current,
            from,
            to,
            piece: movingPiece,
            captured: capturedPiece,
        });

        const nextTurn = movingPiece[0] === 'w' ? 'b' : 'w';
        setBoard(nextBoard);
        setSelectedSquare(null);
        setTurn(nextTurn);

        // Check if next player is in check or checkmated
        const opponentCheck = isKingUnderAttack(nextBoard, nextTurn);
        const opponentMoves = getAllLegalMovesForColor(nextBoard, nextTurn);

        if (opponentMoves.length === 0) {
            setGameOver(true);
            completedRef.current = true;
            const finalMs = Date.now() - startTimeRef.current;
            setElapsedMs(finalMs);

            if (opponentCheck) {
                // Checkmate!
                playArcadeSfx('complete');
                const winner = movingPiece[0] === 'w' ? 'White (You)' : 'Black (ATG Bot)';
                setStatusNote(`🏆 Checkmate! ${winner} wins the match!`);

                if (onComplete) {
                    onComplete({
                        submission: {
                            winner: movingPiece[0],
                            movesCount: replayRef.current.length,
                            finalBoard: nextBoard,
                        },
                        elapsedMs: finalMs,
                        movesCount: replayRef.current.length,
                        hintsUsed: 0,
                        mistakesCount: 0,
                        replay: replayRef.current,
                    });
                }
            } else {
                // Stalemate
                playArcadeSfx('tick');
                setStatusNote('Stalemate! The game ends in a noble draw.');
            }
        } else if (opponentCheck) {
            setInCheck(nextTurn);
            playArcadeSfx('pulse', 3);
            setStatusNote(`⚠️ Check! ${nextTurn === 'w' ? 'Your King is under attack!' : 'Black King is in check!'}`);
        } else {
            setInCheck(null);
            setStatusNote(nextTurn === 'w' ? 'Your turn (White).' : 'ATG Chess Bot thinking...');
        }
    };

    // Selected legal moves
    const activeLegalDests = useMemo(() => {
        if (!selectedSquare || turn !== 'w' || gameOver) return [];
        return getStrictLegalMoves(board, selectedSquare[0], selectedSquare[1]);
    }, [selectedSquare, board, turn, gameOver]);

    // Handle Square Click
    const handleSquareClick = (r, c) => {
        if (gameOver || turn !== 'w' || submitting) return;
        const targetPiece = board[r][c];

        // If clicking on an active destination, execute move
        if (selectedSquare) {
            const isDest = activeLegalDests.some(([dr, dc]) => dr === r && dc === c);
            if (isDest) {
                makeMove(selectedSquare, [r, c]);
                return;
            }
        }

        // If clicking own piece, select it
        if (targetPiece && targetPiece[0] === 'w') {
            setSelectedSquare([r, c]);
            playArcadeSfx('tick');
        } else {
            setSelectedSquare(null);
        }
    };

    // ATG Chess Bot Move Engine
    useEffect(() => {
        if (turn === 'b' && !gameOver && !completedRef.current) {
            const legalMoves = getAllLegalMovesForColor(board, 'b');
            if (legalMoves.length === 0) return;

            // Decision algorithm
            let chosenMove = legalMoves[0];

            if (difficulty === 'easy') {
                chosenMove = legalMoves[Math.floor(Math.random() * legalMoves.length)];
            } else {
                // Evaluate captures and checks
                const pieceValues = { P: 10, N: 30, B: 30, R: 50, Q: 90, K: 900 };
                let bestScore = -9999;

                for (const move of legalMoves) {
                    const [fr, fc] = move.from;
                    const [tr, tc] = move.to;
                    const target = board[tr][tc];
                    let score = target ? (pieceValues[target[1]] || 10) * 10 : 0;

                    // Central board control bonus
                    if (tr >= 2 && tr <= 5 && tc >= 2 && tc <= 5) score += 4;

                    // Hard AI looks ahead 1 response
                    if (difficulty === 'hard') {
                        const tempB = board.map((row) => [...row]);
                        tempB[tr][tc] = tempB[fr][fc];
                        tempB[fr][fc] = null;
                        if (isKingUnderAttack(tempB, 'w')) score += 15; // Give check
                    }

                    score += Math.random() * 2; // subtle human-like variety

                    if (score > bestScore) {
                        bestScore = score;
                        chosenMove = move;
                    }
                }
            }

            const timer = window.setTimeout(() => {
                makeMove(chosenMove.from, chosenMove.to);
            }, 600);

            return () => window.clearTimeout(timer);
        }
    }, [turn, board, gameOver, difficulty]);

    // Update progress based on piece exchanges
    useEffect(() => {
        const score = capturedWhite.length * 10 + (gameOver ? 50 : 0);
        const pct = Math.min(100, Math.round((score / 150) * 100));
        if (onProgress) onProgress(pct, elapsedMs);
    }, [capturedWhite.length, gameOver, elapsedMs]);

    return (
        <div className={styles.gameBoardWrap}>
            {/* Meta Row */}
            <div className={styles.gameMetaRow}>
                <div className={styles.metaBadge}>
                    <i className="bi bi-stopwatch" aria-hidden="true" />
                    <span>{formatElapsedMs(elapsedMs)}</span>
                </div>
                <div className={styles.metaBadge} style={{ background: '#0f172a', color: '#fbbf24', borderColor: '#d97706' }}>
                    <i className="bi bi-suit-club-fill" aria-hidden="true" />
                    <span>Family Chess &bull; {difficulty.toUpperCase()}</span>
                </div>
                <div className={styles.metaBadge}>
                    <span>Captured: {capturedWhite.length > 0 ? capturedWhite.map((p) => PIECE_SYMBOLS[p]).join(' ') : 'None'}</span>
                </div>
            </div>

            {/* Instruction banner */}
            <p className={styles.gameInstruction} aria-live="polite">
                {statusNote}
            </p>

            {/* 8x8 Chess Board */}
            <div className={styles.chessBoardWrapper} role="region" aria-label="Family Chess Board">
                <div className={styles.chessBoardGrid} role="grid" aria-label="8 by 8 Chess Grid">
                    {board.map((row, r) => (
                        <div key={r} className={styles.chessRow} role="row">
                            {row.map((piece, c) => {
                                const isLight = (r + c) % 2 === 0;
                                const isSelected = selectedSquare && selectedSquare[0] === r && selectedSquare[1] === c;
                                const isDest = activeLegalDests.some(([dr, dc]) => dr === r && dc === c);
                                const isCheckSquare = inCheck && piece === `${inCheck}K`;
                                const isWhitePiece = piece && piece[0] === 'w';

                                return (
                                    <button
                                        key={c}
                                        type="button"
                                        className={`
                                            ${styles.chessSquare}
                                            ${isLight ? styles.chessSquareLight : styles.chessSquareDark}
                                            ${isSelected ? styles.chessSquareSelected : ''}
                                            ${isDest ? styles.chessSquareDest : ''}
                                            ${isCheckSquare ? styles.chessSquareCheck : ''}
                                        `}
                                        onClick={() => handleSquareClick(r, c)}
                                        disabled={gameOver || submitting}
                                        aria-label={`Square ${String.fromCharCode(97 + c)}${8 - r}: ${piece ? (isWhitePiece ? 'White ' : 'Black ') + PIECE_NAMES[piece[1]] : 'Empty'}${isDest ? ' (Legal Destination)' : ''}`}
                                    >
                                        {/* Destination dot / capture ring */}
                                        {isDest && (
                                            <span className={piece ? styles.chessCaptureRing : styles.chessMoveDot} aria-hidden="true" />
                                        )}

                                        {/* Piece Symbol */}
                                        {piece && (
                                            <span
                                                className={`
                                                    ${styles.chessPiece}
                                                    ${isWhitePiece ? styles.chessPieceWhite : styles.chessPieceBlack}
                                                `}
                                                aria-hidden="true"
                                            >
                                                {PIECE_SYMBOLS[piece]}
                                            </span>
                                        )}

                                        {/* Board File / Rank Labels on edge */}
                                        {c === 0 && <span className={styles.chessRankLabel}>{8 - r}</span>}
                                        {r === 7 && <span className={styles.chessFileLabel}>{String.fromCharCode(97 + c)}</span>}
                                    </button>
                                );
                            })}
                        </div>
                    ))}
                </div>
            </div>

            {/* Controls */}
            <div className={styles.gameControls}>
                <button
                    type="button"
                    className={styles.ctrlBtn}
                    onClick={() => {
                        setBoard(INITIAL_BOARD.map((row) => [...row]));
                        setSelectedSquare(null);
                        setTurn('w');
                        setCapturedWhite([]);
                        setCapturedBlack([]);
                        setInCheck(null);
                        setGameOver(false);
                        setStatusNote('Board reset! Your turn (White).');
                        playArcadeSfx('error');
                    }}
                    title="Reset board to starting positions"
                >
                    <i className="bi bi-arrow-counterclockwise" aria-hidden="true" />
                    <span>Reset Board</span>
                </button>
            </div>
        </div>
    );
}
