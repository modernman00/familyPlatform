import { describe, it, expect } from 'vitest';
import { render, fireEvent } from '@testing-library/preact';
import {
    formatElapsedMs,
    kinZipEdgeKey,
    computeGhostProgress,
    findKinZipHintStep,
} from './arcadeEngine';
import { KinZipGame } from './KinZipGame';
import { LeagueTablesPanel } from './LeagueTablesPanel';

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
});
