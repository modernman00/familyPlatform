import { describe, expect, it, vi, afterEach } from 'vitest';
import { render, fireEvent, screen, waitFor } from '@testing-library/preact';
import { OrganogramShell } from './OrganogramShell';

describe('Family Tree (Organogram) Preact Island', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    const sampleNodes = [
        { id: '1', fullName: 'Adeola Olaogun', relationLabel: 'Parent', generation: -1, lineage: 'birth', avatar: '/bad-avatar.png' },
        { id: '2', fullName: 'Tunde Olaogun', relationLabel: 'Self', generation: 0, lineage: 'birth' },
        { id: '3', fullName: 'Chidinma Okafor', relationLabel: 'Spouse', generation: 0, lineage: 'married' },
    ];

    it('renders generations, filters by search and lineage, and handles broken avatar fallback', () => {
        render(
            <OrganogramShell
                bootstrap={{
                    familyCode: 'OLAO60446',
                    treeTitle: 'Olaogun Family Tree',
                    csrfToken: 'tok_tree',
                    nodes: sampleNodes,
                }}
            />
        );

        expect(screen.getByText('Adeola Olaogun')).toBeTruthy();
        expect(screen.getByText('Chidinma Okafor')).toBeTruthy();

        // Broken avatar falls back to initials "AO"
        const avatar = screen.getByAltText('Adeola Olaogun');
        fireEvent.error(avatar);
        expect(screen.getByText('AO')).toBeTruthy();

        // Filter by search
        const searchInput = screen.getByLabelText('Find a relative in this tree');
        fireEvent.input(searchInput, { target: { value: 'Chidinma' } });
        expect(screen.queryByText('Adeola Olaogun')).toBeNull();
        expect(screen.getByText('Chidinma Okafor')).toBeTruthy();

        // Clear search and filter by birth lineage
        fireEvent.input(searchInput, { target: { value: '' } });
        fireEvent.change(screen.getByLabelText('Lineage filter'), { target: { value: 'birth' } });
        expect(screen.queryByText('Chidinma Okafor')).toBeNull();
        expect(screen.getByText('Tunde Olaogun')).toBeTruthy();
    });

    it('opens BranchEditorModal, submits with CSRF, and appends new relative', async () => {
        globalThis.fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ status: 'success', node_id: '99' }),
        });

        render(
            <OrganogramShell
                bootstrap={{
                    familyCode: 'OLAO60446',
                    csrfToken: 'tok_csrf_99',
                    nodes: sampleNodes,
                }}
            />
        );

        fireEvent.click(screen.getByRole('button', { name: /Add relative/i }));
        expect(screen.getByRole('dialog')).toBeTruthy();

        fireEvent.input(screen.getByLabelText('First name'), { target: { value: 'Kemi' } });
        fireEvent.input(screen.getByLabelText('Surname'), { target: { value: 'Olaogun' } });
        fireEvent.click(screen.getByRole('button', { name: /Save relative/i }));

        await waitFor(() => {
            expect(screen.getByText('Kemi Olaogun')).toBeTruthy();
        });

        const [url, init] = globalThis.fetch.mock.calls[0];
        expect(url).toBe('/member/organogram/editor/child');
        expect(init.headers.get('X-CSRF-TOKEN')).toBe('tok_csrf_99');
    });

    it('enforces read-only mode on empty and populated trees and handles MemberDetailDrawer inspection', async () => {
        globalThis.fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({
                node: {
                    fullName: 'Chief'.repeat(18),
                    bio: 'Matriarch of the family.',
                    location: 'London, UK',
                },
            }),
        });

        const { unmount } = render(
            <OrganogramShell
                bootstrap={{
                    familyCode: 'OLAO60446',
                    isReadOnly: true,
                    nodes: [],
                }}
            />
        );

        expect(screen.getByText(/No relatives match the current filter/i)).toBeTruthy();
        expect(screen.queryByRole('button', { name: /Add first relative/i })).toBeNull();
        expect(screen.queryByRole('button', { name: /Add relative/i })).toBeNull();
        unmount();

        render(
            <OrganogramShell
                bootstrap={{
                    familyCode: 'OLAO60446',
                    isReadOnly: true,
                    nodes: [{ id: '77', fullName: 'Obi', relationLabel: 'Elder', generation: -2 }],
                }}
            />
        );

        expect(screen.queryByRole('button', { name: /Branch/i })).toBeNull();
        fireEvent.click(screen.getByRole('button', { name: 'View' }));

        await waitFor(() => {
            expect(screen.getByText('Matriarch of the family.')).toBeTruthy();
        });
    });
});
