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
import { WordieGame } from './WordieGame';
import { CheeseboardGame } from './CheeseboardGame';
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

    it('renders KinZipGame board and handles dot 1 click + undo', () => {
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
        const firstCell = getByRole('button', { name: /Row 1 Column 1, Dot 1/i });
        fireEvent.pointerDown(firstCell);
        expect(getByText(/1\/25 cells/i)).toBeTruthy();
        fireEvent.click(getByRole('button', { name: /Undo/i }));
        expect(getByText(/0\/25 cells/i)).toBeTruthy();
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
});
