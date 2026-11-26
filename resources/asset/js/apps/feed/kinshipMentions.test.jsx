import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/preact';
import { RichTextTokens } from './RichTextTokens';
import { PostCard } from './PostCard';
import { renderKinshipMentionsHtml } from './kinshipMentions';
import { PreferencesTab } from '../account-settings/tabs/PreferencesTab';

vi.mock('../../components/profilePage/registerPushNotification', () => ({
    enablePushNotifications: vi.fn().mockResolvedValue({ ok: true }),
    disablePushNotifications: vi.fn().mockResolvedValue(true),
    getPushState: vi.fn(() => 'default'),
    isPushSubscribed: vi.fn().mockResolvedValue(false),
}));

describe('Viewer-Relative Cultural Kinship Honorifics (@Mentions & Post Tags)', () => {
    const canonicalPostText = 'Wishing a joyful birthday to @Oluyomi Olaogun and @Funke Olaogun!';

    it('renders the exact same @mention differently for Grandchild, Son, Nephew, Spouse, and Self', () => {
        // 1. Grandchild viewer -> @Grandpa Oluyomi Olaogun & @Aunty Funke Olaogun
        const grandchildBoot = {
            userId: 'u_tobi',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_oluyomi', firstName: 'Oluyomi', lastName: 'Olaogun', kinship: 'Paternal Grandfather', honorific: 'Grandpa' },
                { id: 'u_funke', firstName: 'Funke', lastName: 'Olaogun', kinship: 'Paternal Aunt', honorific: 'Aunty' },
            ],
        };
        const { unmount: u1 } = render(<RichTextTokens text={canonicalPostText} bootstrap={grandchildBoot} />);
        const grandpaChip = screen.getByText('@Grandpa Oluyomi Olaogun');
        expect(grandpaChip.tagName).toBe('A');
        expect(grandpaChip.getAttribute('href')).toBe('/allMembers/seeProfile/u_oluyomi');
        expect(grandpaChip.getAttribute('title')).toBe('Paternal Grandfather · Oluyomi Olaogun');
        expect(screen.getByText('@Aunty Funke Olaogun')).toBeTruthy();
        u1();

        // 2. Son viewer -> @Dad Oluyomi Olaogun & @Funke Olaogun (Sister-in-law = peer)
        const sonBoot = {
            userId: 'u_wale',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_oluyomi', firstName: 'Oluyomi', lastName: 'Olaogun', kinship: 'Father', honorific: 'Dad' },
                { id: 'u_funke', firstName: 'Funke', lastName: 'Olaogun', kinship: 'Sister-in-law', honorific: null },
            ],
        };
        const { unmount: u2 } = render(<RichTextTokens text={canonicalPostText} bootstrap={sonBoot} />);
        expect(screen.getByText('@Dad Oluyomi Olaogun')).toBeTruthy();
        expect(screen.getByText('@Funke Olaogun')).toBeTruthy();
        u2();

        // 3. Nephew viewer -> @Uncle Oluyomi Olaogun
        const nephewBoot = {
            userId: 'u_nephew',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_oluyomi', firstName: 'Oluyomi', lastName: 'Olaogun', kinship: 'Paternal Uncle', honorific: 'Uncle' },
            ],
        };
        const { unmount: u3 } = render(<RichTextTokens text={canonicalPostText} bootstrap={nephewBoot} />);
        expect(screen.getByText('@Uncle Oluyomi Olaogun')).toBeTruthy();
        u3();

        // 4. Oluyomi viewing a post mentioning himself -> plain @Oluyomi Olaogun linking to /profilePage
        const selfBoot = {
            userId: 'u_oluyomi',
            firstName: 'Oluyomi',
            lastName: 'Olaogun',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_funke', firstName: 'Funke', lastName: 'Olaogun', kinship: 'Daughter-in-law', honorific: null },
            ],
        };
        const { unmount: u4 } = render(<RichTextTokens text={canonicalPostText} bootstrap={selfBoot} />);
        const selfChip = screen.getByText('@Oluyomi Olaogun');
        expect(selfChip.getAttribute('href')).toBe('/profilePage');
        expect(selfChip.getAttribute('title')).toContain('Mentioned you');
        u4();
    });

    it('absorbs redundant vocative prefixes (e.g. "Grandpa @Oluyomi Olaogun") so no viewer sees duplicate titles', () => {
        const sonBoot = {
            userId: 'u_wale',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_oluyomi', firstName: 'Oluyomi', lastName: 'Olaogun', kinship: 'Father', honorific: 'Dad' },
            ],
        };
        const { container } = render(
            <div>
                <RichTextTokens text="Thank you Grandpa @Oluyomi Olaogun for the blessing" bootstrap={sonBoot} />
            </div>
        );
        expect(container.textContent).toBe('Thank you @Dad Oluyomi Olaogun for the blessing');
    });

    it('respects viewer personal setting kinshipHonorifics="off" across both @mentions and "is with …" post tags', () => {
        const postWithTag = {
            post_no: 777,
            id: 'u_author',
            fullName: 'Tobi Olaogun',
            postMessage: 'Sunday lunch with @Oluyomi Olaogun',
            postFamCode: 'OLAOGUN',
            post_likes: 0,
            views_count: 1,
            images: [],
            videos: [],
            comments: [],
            post_tags: [
                {
                    user_id: 'u_oluyomi',
                    firstName: 'Oluyomi',
                    lastName: 'Olaogun',
                    fullName: 'Oluyomi Olaogun',
                    kinship: 'Paternal Grandfather',
                    honorific: 'Grandpa',
                    displayFullName: 'Grandpa Oluyomi Olaogun',
                },
            ],
        };

        const bootOn = {
            userId: 'u_tobi',
            kinshipHonorifics: 'on',
            familyRoster: [
                { id: 'u_oluyomi', firstName: 'Oluyomi', lastName: 'Olaogun', kinship: 'Paternal Grandfather', honorific: 'Grandpa' },
            ],
        };
        const { unmount: uOn } = render(<PostCard post={postWithTag} bootstrap={bootOn} />);
        expect(screen.getByText('Grandpa Oluyomi Olaogun')).toBeTruthy();
        expect(screen.getByText('@Grandpa Oluyomi Olaogun')).toBeTruthy();
        uOn();

        const bootOff = {
            ...bootOn,
            kinshipHonorifics: 'off',
        };
        const { unmount: uOff } = render(<PostCard post={postWithTag} bootstrap={bootOff} />);
        expect(screen.getByText('Oluyomi Olaogun')).toBeTruthy();
        expect(screen.getByText('@Oluyomi Olaogun')).toBeTruthy();
        expect(screen.queryByText('@Grandpa Oluyomi Olaogun')).toBeNull();
        uOff();
    });

    it('escapes HTML safely in renderKinshipMentionsHtml for Reels & Daily Spark surfaces', () => {
        const directory = {
            'oluyomi olaogun': {
                id: 'u_oluyomi',
                fullName: 'Oluyomi Olaogun',
                kinship: 'Paternal Grandfather"><img src=x onerror=alert(1)>',
                honorific: 'Grandpa',
                displayFullName: 'Grandpa Oluyomi Olaogun',
            },
        };
        const html = renderKinshipMentionsHtml(
            'Love you Grandpa @Oluyomi Olaogun &lt;script&gt;alert(1)&lt;/script&gt;',
            { enabled: true, directory, currentUserId: 'u_tobi', isDark: true }
        );
        expect(html).toContain('>@Grandpa Oluyomi Olaogun</a>');
        expect(html).not.toContain('Grandpa <a');
        expect(html).not.toContain('<script>');
        expect(html).not.toContain('<img src=x');
    });

    describe('PreferencesTab personal toggle', () => {
        beforeEach(() => {
            globalThis.fetch = vi.fn().mockResolvedValue({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', message: 'Preferences successfully updated.' }),
            });
            window.localStorage.setItem('fp_feed_swr_v1_u1_FAM', '{"posts":[]}');
        });

        afterEach(() => {
            vi.restoreAllMocks();
            window.localStorage.clear();
        });

        it('renders Respectful Kinship Titles switch on by default, persists changes, and clears SWR feed cache', async () => {
            const onSaved = vi.fn();
            render(
                <PreferencesTab
                    accountData={{ email_notifications: 'on', sms_notifications: 'on', kinship_honorifics: 'on' }}
                    csrfToken="csrf_tok"
                    bootstrap={{ csrfToken: 'csrf_tok' }}
                    onSavedAccountData={onSaved}
                />
            );

            const toggle = document.getElementById('pref_kinship_honorifics');
            expect(toggle).toBeTruthy();
            expect(toggle.checked).toBe(true);

            fireEvent.click(toggle);
            expect(toggle.checked).toBe(false);

            fireEvent.submit(document.getElementById('preferencesForm'));

            await waitFor(() => {
                expect(onSaved).toHaveBeenCalledWith(
                    expect.objectContaining({ kinship_honorifics: 'off' })
                );
            });
            expect(window.localStorage.getItem('fp_feed_swr_v1_u1_FAM')).toBeNull();
        });
    });
});
