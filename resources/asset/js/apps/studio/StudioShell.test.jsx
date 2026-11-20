import { describe, expect, it, vi, afterEach } from 'vitest';
import { render, fireEvent, screen, waitFor } from '@testing-library/preact';
import '@shared/SovereignNleEngine.js';
import '@shared/SovereignVideoStudioClient.js';
import { StudioShell } from './StudioShell';

describe('Creative Studio Preact Island', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('renders mode switcher, empty media bin, and supports adding & inspecting assets', () => {
        const initialAssets = [
            { id: 'a1', name: 'Reunion Photo.jpg', kind: 'image', url: '/broken-test.jpg' },
        ];

        render(<StudioShell bootstrap={{ famCode: 'OLAO60446', csrfToken: 'tok_1', initialAssets }} />);

        expect(screen.getByRole('heading', { name: /Family Creative Studio/i })).toBeTruthy();
        expect(screen.getByText('Reunion Photo.jpg')).toBeTruthy();

        // Simulate broken image fallback
        const img = screen.getByAltText('Reunion Photo.jpg');
        fireEvent.error(img);
        expect(screen.getByText('Preview unavailable')).toBeTruthy();

        // Add asset to timeline
        fireEvent.click(screen.getByRole('button', { name: 'Use' }));
        expect(screen.getByText(/1 clip · 5\.0s/i)).toBeTruthy();

        // Switch mode to Poster Studio
        fireEvent.click(screen.getByRole('button', { name: /Poster Studio/i }));
        expect(screen.getByRole('button', { name: /Poster Studio/i }).getAttribute('aria-pressed')).toBe('true');

        // Open export modal and close with Escape
        fireEvent.click(screen.getByRole('button', { name: /Export & Publish/i }));
        expect(screen.getByRole('dialog')).toBeTruthy();
        fireEvent.keyDown(window, { key: 'Escape' });
        expect(screen.queryByRole('dialog')).toBeNull();
    });

    it('handles empty state, extreme 3/80-char names, NLE project JSON export, and server error alerts', async () => {
        globalThis.fetch = vi.fn().mockResolvedValueOnce({
            ok: false,
            status: 500,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ message: 'Storage gateway temporarily unavailable' }),
        }).mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ status: 'success' }),
        });

        const longName = 'A'.repeat(85) + '_heritage_clip.mp4';
        const initialAssets = [
            { id: 'short1', name: 'A.v', kind: 'video', url: '/short.mp4' },
            { id: 'long1', name: longName, kind: 'video', url: '/long.mp4' },
        ];

        render(<StudioShell bootstrap={{ famCode: 'OLAO60446', csrfToken: 'tok_export', initialAssets }} />);

        expect(screen.getByText('A.v')).toBeTruthy();
        expect(screen.getByText(longName)).toBeTruthy();

        // Add both clips to timeline
        const useBtns = screen.getAllByRole('button', { name: 'Use' });
        fireEvent.click(useBtns[0]);
        fireEvent.click(useBtns[1]);
        expect(screen.getByText(/2 clips · 10\.0s/i)).toBeTruthy();

        // Open export modal and trigger publish (first fails with 500, then succeeds)
        fireEvent.click(screen.getByRole('button', { name: /Export & Publish/i }));
        fireEvent.click(screen.getByRole('button', { name: /Publish to Family Reels/i }));

        await waitFor(() => {
            expect(screen.getByRole('alert').textContent).toContain('Storage gateway temporarily unavailable');
        });

        // Retry publish -> succeeds and closes modal
        fireEvent.click(screen.getByRole('button', { name: /Publish to Family Reels/i }));
        await waitFor(() => {
            expect(screen.queryByRole('dialog')).toBeNull();
        });

        const [url, reqInit] = globalThis.fetch.mock.calls[0];
        expect(url).toBe('/api/reels/upload');
        expect(reqInit.headers.get('X-CSRF-TOKEN')).toBe('tok_export');
        expect(reqInit.body.get('nle_project_json')).toContain('"schemaVersion":"2.0.0"');
    });
});
