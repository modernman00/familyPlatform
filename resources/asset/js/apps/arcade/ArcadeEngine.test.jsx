import { describe, it, expect } from 'vitest';
import { render, fireEvent } from '@testing-library/preact';
import {
    formatElapsedMs,
    kinZipEdgeKey,
    computeGhostProgress,
    findKinZipHintStep,
} from './arcadeEngine';
import { KinZipGame } from './KinZipGame';
import { KinGridGame } from './KinGridGame';
import { KinWordGame } from './KinWordGame';
import { triggerHaptic, useSoundEffects } from './hooks/useSoundEffects';
import { WordieGame } from './WordieGame';
import { CheeseboardGame } from './CheeseboardGame';
import { AyoGame } from './AyoGame';
import { FamilyChessGame } from './FamilyChessGame';
import { MinesweeperGame } from './MinesweeperGame';
import { ArcadeShell } from './ArcadeShell';
import { LeagueTablesPanel } from './LeagueTablesPanel';
import { HowToPlayModal } from './HowToPlayModal';

describe('Family Arcade Engine & Components', () => {
    it('formats elapsed milliseconds cleanly', () => {
        expect(formatElapsedMs(0)).toBe('00:00');
        expect(formatElapsedMs(65000)).toBe('01:05');
    });

    it('normalises KinZip wall edge keys symmetrically', () => {
        expect(kinZipEdgeKey(0, 1, 0, 2)).toBe(kinZipEdgeKey(0, 2, 0, 1));
        expect(kinZipEdgeKey(1, 0, 2, 0)).toBe(kinZipEdgeKey(2, 0, 1, 0));
    });

    it('computes ghost progress percentage clamped to 0..100', () => {
        expect(computeGhostProgress({ elapsedMs: 50000 }, 60000, 25000)).toBe(50);
        expect(computeGhostProgress(null, 40000, 60000)).toBe(100);
    });

    it('finds a valid KinZip hint step from dot 1', () => {
        const puzzle = {
            size: 3,
            checkpoints: [
                { num: 1, r: 0, c: 0 },
                { num: 2, r: 2, c: 2 },
            ],
            walls: [[0, 0, 1, 0]],
        };
        expect(findKinZipHintStep(puzzle, [])).toEqual([0, 0]);
        const next = findKinZipHintStep(puzzle, [[0, 0]]);
        expect(next).toEqual([0, 1]);
    });

    it('renders KinZipGame board, handles dot 1 click + undo, and caps hints at 3', () => {
        const puzzle = {
            seedKey: '2026-11-22',
            difficulty: 'easy',
            size: 5,
            checkpoints: [
                { num: 1, r: 0, c: 0 },
                { num: 2, r: 0, c: 4 },
            ],
            walls: [],
        };
        const { getByRole, getByText } = render(
            <KinZipGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        expect(getByRole('grid')).toBeTruthy();
        expect(getByText(/Hint \(3\)/i)).toBeTruthy();

        const firstCell = getByRole('button', { name: /Row 1 Column 1, Dot 1/i });
        fireEvent.pointerDown(firstCell);
        expect(getByText(/1\/25 cells/i)).toBeTruthy();
        fireEvent.click(getByRole('button', { name: /Undo/i }));
        expect(getByText(/0\/25 cells/i)).toBeTruthy();

        // Use 3 hints
        const hintBtn = getByRole('button', { name: /Hint/i });
        fireEvent.click(hintBtn);
        expect(getByText(/Hint \(2\)/i)).toBeTruthy();
        fireEvent.click(hintBtn);
        expect(getByText(/Hint \(1\)/i)).toBeTruthy();
        fireEvent.click(hintBtn);
        expect(getByText(/Hint \(0\)/i)).toBeTruthy();
        expect(hintBtn.hasAttribute('disabled')).toBe(true);
    });

    it('renders LeagueTablesPanel with Family Cup and game tabs', () => {
        const leagues = {
            familyCup: [
                { userId: 'u1', fullName: 'Amina Ade', totalScore: 2400, gamesMastered: 2, gamesPlayed: 4 },
            ],
            games: { kinzip: [], kingrid: [], kinword: [], kinpulse: [] },
        };
        const { getByText } = render(
            <LeagueTablesPanel
                leagues={leagues}
                champions={[]}
                period="weekly"
                includeConnected={false}
                onChangePeriod={() => {}}
                onToggleConnected={() => {}}
                currentUserId="u1"
            />
        );
        expect(getByText(/Amina Ade/i)).toBeTruthy();
        expect(getByText(/2,400 pts/i)).toBeTruthy();
    });

    it('renders HowToPlayModal with live demo and switches games', () => {
        let closed = false;
        const { getByRole, getByText } = render(
            <HowToPlayModal initialGame="kinzip" onClose={() => { closed = true; }} />
        );
        expect(getByRole('dialog')).toBeTruthy();
        expect(getByText(/How to Play KinZip/i)).toBeTruthy();
        expect(getByText(/Watch Platform Play/i)).toBeTruthy();

        fireEvent.click(getByRole('tab', { name: 'KinWord' }));
        expect(getByText(/How to Play KinWord/i)).toBeTruthy();

        fireEvent.click(getByRole('button', { name: /Close How to Play guide/i }));
        expect(closed).toBe(true);
    });

    it('KinGridGame Hint respects inequalities and triggers onComplete on final cell', () => {
        let completedPayload = null;
        // 2x2 Latin square: (0,0) < (0,1), so row 0 must be [1, 2] and row 1 must be [2, 1]
        const puzzle = {
            seedKey: '2026-11-22',
            difficulty: 'easy',
            size: 2,
            givens: [{ r: 1, c: 0, v: 2 }, { r: 1, c: 1, v: 1 }],
            inequalities: [{ r1: 0, c1: 0, r2: 0, c2: 1, op: '<' }],
        };
        const { getByRole, getByText } = render(
            <KinGridGame
                puzzle={puzzle}
                onProgress={() => {}}
                onComplete={(payload) => { completedPayload = payload; }}
                submitting={false}
            />
        );
        // Hint 1 places 1 at (0,0) respecting 1 < 2
        fireEvent.click(getByRole('button', { name: /Hint/i }));
        expect(getByText(/placed 1 at Row 1, Col 1/i)).toBeTruthy();

        // Hint 2 places 2 at (0,1) and completes the board
        fireEvent.click(getByRole('button', { name: /Hint/i }));
        expect(completedPayload).toBeTruthy();
        expect(completedPayload.submission.grid).toEqual([[1, 2], [2, 1]]);
    });

    it('renders ArcadeShell in guest challenge mode with challenger target and join CTA', () => {
        const { getByText } = render(
            <ArcadeShell
                bootstrap={{
                    guestMode: true,
                    initialGame: 'kinzip',
                    initialDifficulty: 'medium',
                    challengeToken: 'abc123',
                    challenger: { name: 'Wale', score: 1250, elapsedMs: 32000 },
                    initialPuzzle: {
                        seedKey: '2026-11-22',
                        difficulty: 'medium',
                        size: 5,
                        checkpoints: [{ num: 1, r: 0, c: 0 }, { num: 2, r: 4, c: 4 }],
                        walls: [],
                    },
                    registerUrl: '/register?arcade_challenge=abc123',
                }}
            />
        );
        expect(getByText(/Can You Beat Wale’s 1250 pts in KinZip/i)).toBeTruthy();
        expect(getByText(/Join Free & Challenge Back/i)).toBeTruthy();
    });

    it('renders WordieGame and accepts virtual keyboard letters', () => {
        let completed = null;
        const puzzle = {
            game: 'wordie',
            seedKey: 'test',
            difficulty: 'medium',
            wordLength: 5,
            maxGuesses: 6,
            targetWord: 'HEART',
        };
        const { getByRole } = render(
            <WordieGame puzzle={puzzle} onProgress={() => {}} onComplete={(res) => { completed = res; }} submitting={false} />
        );
        expect(getByRole('group', { name: /Wordie 5-letter grid/i })).toBeTruthy();
        // Type H E A R T and enter
        fireEvent.click(getByRole('button', { name: /^H$/i }));
        fireEvent.click(getByRole('button', { name: /^E$/i }));
        fireEvent.click(getByRole('button', { name: /^A$/i }));
        fireEvent.click(getByRole('button', { name: /^R$/i }));
        fireEvent.click(getByRole('button', { name: /^T$/i }));
        fireEvent.click(getByRole('button', { name: /^ENTER$/i }));

        expect(completed).toBeTruthy();
        expect(completed.submission.solved).toBe(true);
        expect(completed.submission.guesses).toEqual(['HEART']);
    });

    it('renders CheeseboardGame and cycles cell states', () => {
        const puzzle = {
            game: 'cheeseboard',
            size: 6,
            regions: [
                [0, 0, 1, 1, 2, 2],
                [0, 0, 1, 1, 2, 2],
                [3, 3, 4, 4, 5, 5],
                [3, 3, 4, 4, 5, 5],
                [0, 1, 2, 3, 4, 5],
                [0, 1, 2, 3, 4, 5],
            ],
            targetQueens: 6,
        };
        const { getByRole } = render(
            <CheeseboardGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        expect(getByRole('grid')).toBeTruthy();
        const cell = getByRole('button', { name: /Row 1, Column 1/i });
        // Click 1: cross
        fireEvent.click(cell);
        expect(cell.textContent).toContain('✕');
        // Click 2: queen
        fireEvent.click(cell);
        expect(cell.textContent).toContain('👑');
    });

    it('renders AyoGame with 12 hollows and 2 iles', () => {
        const puzzle = {
            game: 'ayo',
            seedKey: '2026-11-23',
            difficulty: 'medium',
        };
        const { getByRole, getAllByRole } = render(
            <AyoGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        expect(getByRole('region', { name: /Yoruba Ayò Olópon Board/i })).toBeTruthy();
        const southPits = getAllByRole('button', { name: /South/i });
        expect(southPits.length).toBe(6);
        expect(southPits[0].textContent).toContain('4');
    });

    it('renders FamilyChessGame and selects pieces with legal destinations', () => {
        const puzzle = {
            game: 'chess',
            seedKey: '2026-11-23',
            difficulty: 'medium',
        };
        const { getByRole, getAllByRole } = render(
            <FamilyChessGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        expect(getByRole('region', { name: /Family Chess Board/i })).toBeTruthy();
        const pawnE2 = getByRole('button', { name: /Square e2: White Pawn/i });
        fireEvent.click(pawnE2);
        // Destination e3 or e4 should be highlighted
        const destSquares = getAllByRole('button', { name: /Legal Destination/i });
        expect(destSquares.length).toBeGreaterThanOrEqual(1);
    });

    it('renders MinesweeperGame and reveals safe cells', () => {
        const puzzle = {
            game: 'minesweeper',
            rows: 8,
            cols: 8,
            totalMines: 10,
            safeCellsCount: 54,
            grid: Array.from({ length: 8 }, () => Array(8).fill(0)),
        };
        const { getByRole } = render(
            <MinesweeperGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        expect(getByRole('grid')).toBeTruthy();
        const cell = getByRole('button', { name: /Row 1, Col 1/i });
        fireEvent.click(cell);
        expect(cell.className).toContain('mineCellRevealed');
    });

    it('renders KinWordGame with 16 tiles, native tooltips, and long-word sizing class', () => {
        const tiles = [
            'YORKSHIRE', 'ARCHIVIST', 'DARJEELING', 'GRAVY',
            'PARSNIP', 'ROASTIES', 'KETTLE', 'SCONE',
            'BISCUIT', 'CIVIC', 'RADAR', 'LEVEL',
            'KAYAK', 'HOST', 'CHEF', 'DJ',
        ];
        const puzzle = {
            game: 'kinword',
            seedKey: '2026-11-22',
            difficulty: 'medium',
            tiles,
            groupManifest: {},
            maxMistakes: 4,
        };
        const { getByRole } = render(
            <KinWordGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        const group = getByRole('group', { name: /KinWord 4 by 4 tiles/i });
        const buttons = group.querySelectorAll('button');
        expect(buttons.length).toBe(16);

        const yorkshireBtn = getByRole('button', { name: 'YORKSHIRE' });
        const archivistBtn = getByRole('button', { name: 'ARCHIVIST' });
        const darjeelingBtn = getByRole('button', { name: 'DARJEELING' });
        const hostBtn = getByRole('button', { name: 'HOST' });

        expect(yorkshireBtn.getAttribute('title')).toBe('YORKSHIRE');
        expect(yorkshireBtn.className).toContain('wordTileLong');
        expect(archivistBtn.className).toContain('wordTileLong');
        expect(darjeelingBtn.className).toContain('wordTileLong');
        expect(hostBtn.getAttribute('title')).toBe('HOST');
        expect(hostBtn.className).not.toContain('wordTileLong');
    });

    it('sets --mine-cols on Minesweeper board and renders contrast-safe mineNum class on revealed cells', () => {
        const grid = Array.from({ length: 8 }, () => Array(8).fill(0));
        grid[0][0] = 2;
        grid[0][1] = 7;
        grid[0][2] = -1;
        const puzzle = {
            game: 'minesweeper',
            rows: 8,
            cols: 8,
            totalMines: 1,
            safeCellsCount: 63,
            grid,
        };
        const { getByRole } = render(
            <MinesweeperGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} />
        );
        const board = getByRole('grid', { name: /Minesweeper 8 by 8 grid/i });
        expect(board.style.getPropertyValue('--mine-cols')).toBe('8');

        const cell2 = getByRole('button', { name: /Row 1, Col 1/i });
        fireEvent.click(cell2);
        const span2 = cell2.querySelector('span');
        expect(span2).toBeTruthy();
        expect(span2.className).toContain('mineNum2');

        const cell7 = getByRole('button', { name: /Row 1, Col 2/i });
        fireEvent.click(cell7);
        const span7 = cell7.querySelector('span');
        expect(span7).toBeTruthy();
        expect(span7.className).toContain('mineNum7');
    });

    it('opens the Share Game modal with difficulty-aware v=3 preview card image', () => {
        const { getByRole, getByAltText } = render(
            <ArcadeShell
                bootstrap={{
                    guestMode: true,
                    initialGame: 'kinzip',
                    initialDifficulty: 'medium',
                    initialPuzzle: {
                        seedKey: '2026-11-22',
                        difficulty: 'medium',
                        size: 5,
                        checkpoints: [{ num: 1, r: 0, c: 0 }, { num: 2, r: 4, c: 4 }],
                        walls: [],
                    },
                }}
            />
        );
        const shareBtn = getByRole('button', { name: /Share KinZip/i });
        fireEvent.click(shareBtn);

        const previewImg = getByAltText(/KinZip \(Connect the Dots\) — medium difficulty share card preview/i);
        expect(previewImg).toBeTruthy();
        expect(previewImg.getAttribute('src')).toContain('/arcade/card/game-kinzip.jpg?v=3&difficulty=medium');
    });

    it('renders KinZip node ripple on dot 1 and flashes gold wall on collision', () => {
        const puzzle = {
            seedKey: 'zip-wall-ripple',
            difficulty: 'easy',
            size: 3,
            checkpoints: [{ num: 1, r: 0, c: 0 }, { num: 2, r: 2, c: 2 }],
            walls: [[0, 0, 0, 1]],
        };
        const soundMock = { playTap: vi.fn(), playConnect: vi.fn(), playError: vi.fn(), playVictory: vi.fn() };
        const { getByRole, container } = render(
            <KinZipGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} sound={soundMock} />
        );

        const cell00 = getByRole('button', { name: /Row 1 Column 1, Dot 1/i });
        fireEvent.pointerDown(cell00);
        expect(soundMock.playConnect).toHaveBeenCalledWith(1);
        expect(container.querySelector('[class*="zipNodeRipple"]')).toBeTruthy();

        const cell01 = getByRole('button', { name: /Row 1 Column 2/i });
        fireEvent.pointerDown(cell01);
        expect(soundMock.playError).toHaveBeenCalled();
        expect(cell00.className).toMatch(/wallHitRight/);
    });

    it('validates KinGrid inequalities live and persists useSoundEffects mute state', () => {
        const puzzle = {
            seedKey: 'grid-ineq-live',
            difficulty: 'easy',
            size: 3,
            givens: [{ r: 0, c: 0, v: 2 }],
            inequalities: [{ r1: 0, c1: 0, op: '>', r2: 0, c2: 1 }],
        };
        const soundMock = { playTap: vi.fn(), playConnect: vi.fn(), playError: vi.fn(), playRowComplete: vi.fn(), playVictory: vi.fn() };
        const { getByRole, container } = render(
            <KinGridGame puzzle={puzzle} onProgress={() => {}} onComplete={() => {}} submitting={false} sound={soundMock} />
        );

        const cell01 = getByRole('button', { name: /Row 1 Column 2/i });
        fireEvent.click(cell01);
        fireEvent.click(getByRole('button', { name: '3' })); // 2 > 3 is invalid
        expect(soundMock.playError).toHaveBeenCalled();
        expect(container.querySelector('[class*="ineqInvalid"]')).toBeTruthy();

        fireEvent.click(getByRole('button', { name: '1' })); // 2 > 1 is satisfied
        expect(container.querySelector('[class*="ineqSatisfied"]')).toBeTruthy();

        function SoundHarness() {
            const s = useSoundEffects();
            return (
                <button type="button" onClick={s.toggleSound}>
                    {s.soundEnabled ? 'SOUND_ON' : 'SOUND_OFF'}
                </button>
            );
        }
        const { getByText } = render(<SoundHarness />);
        expect(getByText('SOUND_ON')).toBeTruthy();
        fireEvent.click(getByText('SOUND_ON'));
        expect(getByText('SOUND_OFF')).toBeTruthy();
        expect(localStorage.getItem('fp_arcade_sound_enabled')).toBe('false');
        expect(() => triggerHaptic(12)).not.toThrow();
    });
});
