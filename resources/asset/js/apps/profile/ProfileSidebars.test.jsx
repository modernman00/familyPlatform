import { describe, expect, it, vi, afterEach } from 'vitest';
import { render, screen, waitFor, fireEvent } from '@testing-library/preact';

vi.mock('../../components/profilePage/sovereignRealtimeBridge', () => ({
    createHybridRealtimeClient: vi.fn(() => ({
        subscribe: vi.fn(() => ({
            bind: vi.fn(),
        })),
        disconnect: vi.fn(),
    })),
}));

import { KinRequestsIsland } from './KinRequestsIsland';
import { UpcomingEventsIsland } from './UpcomingEventsIsland';
import { applyProfileUpdateToDom } from './index';

describe('Profile Sidebars Preact Islands', () => {
    afterEach(() => {
        vi.restoreAllMocks();
        document.body.innerHTML = '';
    });

    it('KinRequestsIsland fetches and renders pending kin requests with Accept and Decline links', async () => {
        globalThis.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({
                message: [
                    {
                        id: 'u_99',
                        firstName: 'Bisi',
                        lastName: 'Adeyemi',
                        img: 'avatarF.png',
                        famCode: 'OLAO60446',
                        relationship_full_label: 'Cousin',
                        relationship_note: 'Happy to connect!',
                    },
                ],
            }),
        });

        render(<KinRequestsIsland bootstrap={{ id: 'u_1', firstName: 'Tunde', lastName: 'Olaogun', famCode: 'OLAO60446' }} />);

        await waitFor(() => {
            expect(screen.getByText('Bisi Adeyemi')).toBeTruthy();
        });
        expect(screen.getByText('Cousin')).toBeTruthy();
        expect(screen.getByText(/Happy to connect!/)).toBeTruthy();
        expect(screen.getByTitle('Accept as Connected Kin').getAttribute('href')).toContain('/member/request/u_99/u_1/50/OLAO60446/pp');
        expect(screen.getByTitle('Decline').getAttribute('href')).toContain('/member/request/u_99/u_1/10/OLAO60446/pp');
    });

    it('UpcomingEventsIsland renders empty state and reacts to event-created and event-updated', async () => {
        render(<UpcomingEventsIsland bootstrap={{ events: [], userId: 'u_1', famCode: 'OLAO60446' }} />);

        expect(screen.getByText('No Upcoming Events')).toBeTruthy();

        window.dispatchEvent(new CustomEvent('event-created', {
            detail: {
                no: '55',
                id: 'u_1',
                eventName: 'Annual Family Reunion',
                eventDate: '12 Dec 2026',
                eventDateRaw: '2026-12-12',
                eventType: 'Reunion',
                dateDifference: 'in 21 days',
            },
        }));

        await waitFor(() => {
            expect(screen.getByText('Annual Family Reunion')).toBeTruthy();
        });

        window.dispatchEvent(new CustomEvent('event-updated', {
            detail: { no: '55', eventName: 'Grand Family Reunion 2026' },
        }));

        await waitFor(() => {
            expect(screen.getByText('Grand Family Reunion 2026')).toBeTruthy();
        });
    });

    it('applyProfileUpdateToDom updates name, country, and avatar in place without page reload', () => {
        document.body.innerHTML = `
            <h4 id="leftColProfileName"><span class="js-profile-name-text">Old Name</span></h4>
            <span id="leftColProfileCountry">Global</span>
            <img id="leftColProfileAvatar" src="/resources/images/profile/avatarM.png" />
        `;

        applyProfileUpdateToDom({
            firstName: 'Tunde',
            lastName: 'Olaogun',
            country: 'United Kingdom',
            avatar: '/resources/images/profile/new.jpg',
        });

        expect(document.querySelector('#leftColProfileName .js-profile-name-text').textContent).toBe('Tunde Olaogun');
        expect(document.getElementById('leftColProfileCountry').textContent).toBe('United Kingdom');
        expect(document.getElementById('leftColProfileAvatar').getAttribute('src')).toBe('/resources/images/profile/new.jpg');
    });
});
