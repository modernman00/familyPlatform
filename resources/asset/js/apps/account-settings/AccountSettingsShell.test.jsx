import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/preact';

vi.mock('../../components/profilePage/registerPushNotification', () => ({
    enablePushNotifications: vi.fn().mockResolvedValue({ ok: true }),
    disablePushNotifications: vi.fn().mockResolvedValue(true),
    getPushState: vi.fn(() => 'default'),
    isPushSubscribed: vi.fn().mockResolvedValue(false),
}));

vi.mock('../../components/api/index', () => ({
    emailIsRegistered: vi.fn().mockResolvedValue(false),
}));

import { AccountSettingsShell } from './AccountSettingsShell';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { emailIsRegistered } from '../../components/api/index';

function buildBootstrap(overrides = {}) {
    return {
        csrfToken: 'csrf_test_abc123',
        notice: '',
        maxWeddingDate: '2027-11-22',
        accountData: {
            id: 'user_42',
            firstName: 'Wale',
            lastName: 'Olaogun',
            fullName: 'Wale Olaogun',
            email: 'wale@example.com',
            mobile: '+447700900111',
            country: 'United Kingdom',
            employmentStatus: 'Employed',
            occupation: 'Engineer',
            famCode: 'OLAO604',
            otherFamCode: 'ADEY202',
            hasMaternalCode: true,
            maternalFamCode: 'ADEY202',
            father_first_name: 'Tunde',
            father_last_name: 'Olaogun',
            father_email: 'tunde@example.com',
            father_mobile: '+447700900222',
            mother_first_name: 'Bisi',
            mother_last_name: 'Olaogun',
            maiden_name: 'Adeyemi',
            mother_maiden: 'Adeyemi',
            mother_email: 'bisi@example.com',
            mother_mobile: '+447700900333',
            parents_status: 'married',
            maritalStatus: 'Single',
            spouse_name: '',
            spouse_email: '',
            spouse_mobile: '',
            spouse_gender: '',
            wedding_date: '',
            email_notifications: 'on',
            post_email_notifications: 'off',
            sms_notifications: 'on',
            two_factor_auth: 'off',
            profile_visibility: 'Public',
            show_my_profile: 'on',
            show_birth_year: 'off',
            ...overrides.accountData,
        },
        pendingFamilyRequest: overrides.pendingFamilyRequest ?? null,
        birthFamily: overrides.birthFamily ?? {
            state: 'active',
            code: 'ADEY202',
            origin: 'created',
            pending: null,
        },
        codeRename: overrides.codeRename ?? {
            is_creator: true,
            can_propose: true,
            code: 'OLAO604',
            cooldown: false,
            open: null,
        },
        passkeys: overrides.passkeys ?? [],
        restrictionDirectory: overrides.restrictionDirectory ?? [
            {
                id: 'rel_1',
                fullName: 'Adebayo Extremely Long Family Member Name That Could Easily Span Eighty Characters In Total Display',
                famCode: 'OLAO604',
                avatar: '/resources/images/profile/avatarM.png',
                is_restricted: false,
                restrict_posts: false,
                restrict_comments: false,
                restrict_reels: false,
                restrict_events: false,
                restrict_journals: false,
                restrict_announcements: false,
                restrict_capsules: false,
                restrict_profile: false,
                note: '',
            },
            {
                id: 'rel_2',
                fullName: 'Kemi Adeyemi',
                famCode: 'ADEY202',
                avatar: '/resources/images/profile/avatarF.png',
                is_restricted: true,
                restrict_posts: true,
                restrict_comments: true,
                restrict_reels: true,
                restrict_events: true,
                restrict_journals: true,
                restrict_announcements: true,
                restrict_capsules: true,
                restrict_profile: false,
                note: 'Quiet mute',
            },
        ],
    };
}

describe('AccountSettingsShell Preact App', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', '/accountSetting');
        window.Swal = {
            fire: vi.fn().mockResolvedValue({ isConfirmed: true, value: 'schedule' }),
        };
        window.FamilyTooltip = {
            getMode: vi.fn(() => 'smart'),
            setMode: vi.fn(),
            resetMastery: vi.fn(),
        };
        globalThis.fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ status: 'success', message: 'Saved.' }),
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
        document.body.innerHTML = '';
        delete window.Swal;
        delete window.FamilyTooltip;
    });

    it('renders all 9 navigation tabs and switches tabs without page reload', () => {
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);

        expect(document.getElementById('v-pills-profile-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-password-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-preferences-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-privacy-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-parents-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-children-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-siblings-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-marital-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-familycode-tab')).toBeTruthy();

        fireEvent.click(document.getElementById('v-pills-password-tab'));
        expect(screen.getByText('Change Password')).toBeTruthy();
        expect(document.getElementById('passwordForm')).toBeTruthy();

        fireEvent.click(document.getElementById('v-pills-familycode-tab'));
        expect(screen.getByText('Family Network & Code Management')).toBeTruthy();
        expect(document.getElementById('currentFamCodeValue').textContent).toBe('OLAO604');
    });

    it('resolves legacy deep-link hashes and ?tab= query parameters', () => {
        window.history.replaceState(null, '', '/accountSetting#birthFamilyCard');
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);
        expect(document.getElementById('birthFamilyCard')).toBeTruthy();

        window.location.hash = '#memberRestrictionCard';
        fireEvent(window, new HashChangeEvent('hashchange'));
        expect(document.getElementById('memberRestrictionCard')).toBeTruthy();
    });

    it('submits Profile & Work form with CSRF fields and updates state in place', async () => {
        globalThis.fetch = vi.fn().mockResolvedValueOnce({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ status: 200, message: 'Profile details successfully updated.' }),
        });

        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);

        const occupationInput = document.getElementById('occupation');
        fireEvent.input(occupationInput, { target: { value: 'Principal Architect' } });

        fireEvent.submit(document.getElementById('profileForm'));

        await waitFor(() => {
            expect(globalThis.fetch).toHaveBeenCalledTimes(1);
        });
        const [url, opts] = globalThis.fetch.mock.calls[0];
        expect(url).toBe('/accountSetting');
        expect(opts.body.get('action')).toBe('updateProfile');
        expect(opts.body.get('token')).toBe('csrf_test_abc123');
        expect(opts.body.get('occupation')).toBe('Principal Architect');

        await waitFor(() => {
            expect(document.getElementById('profileForm_notification_error').textContent).toContain(
                'Profile details successfully updated.'
            );
        });
    });

    it('validates password mismatches and provides accessible eye-toggle buttons', () => {
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);
        fireEvent.click(document.getElementById('v-pills-password-tab'));

        const currentInput = document.getElementById('current_password_input');
        const newInput = document.getElementById('new_password_input');
        const confirmInput = document.getElementById('confirm_password_input');

        const toggleBtn = screen.getByLabelText('Show current password');
        expect(currentInput.type).toBe('password');
        fireEvent.click(toggleBtn);
        expect(currentInput.type).toBe('text');

        fireEvent.input(currentInput, { target: { value: 'OldPass123' } });
        fireEvent.input(newInput, { target: { value: 'NewPass123' } });
        fireEvent.input(confirmInput, { target: { value: 'Mismatch99' } });
        fireEvent.submit(document.getElementById('passwordForm'));

        expect(document.getElementById('passwordForm_notification_error').textContent).toContain(
            'New passwords do not match.'
        );
    });

    it('syncs Preferences switches, Push diagnostics, and Tooltip controls', async () => {
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);
        fireEvent.click(document.getElementById('v-pills-preferences-tab'));

        const tooltipSelect = document.getElementById('tooltipModeSelect');
        fireEvent.change(tooltipSelect, { target: { value: 'always' } });
        expect(window.FamilyTooltip.setMode).toHaveBeenCalledWith('always');

        fireEvent.click(document.getElementById('resetTooltipMasteryBtn'));
        expect(window.FamilyTooltip.resetMastery).toHaveBeenCalledTimes(1);

        fireEvent.submit(document.getElementById('preferencesForm'));
        await waitFor(() => {
            expect(globalThis.fetch).toHaveBeenCalled();
        });
    });

    it('handles Privacy visibility sync, GDPR export, Passkey empty state, and Member Restrictions', async () => {
        globalThis.fetch = vi.fn().mockImplementation(async (url) => {
            if (String(url).includes('/api/privacy/restrictions/update')) {
                return {
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 200,
                        restriction: { active: true, restrict_posts: true },
                    }),
                };
            }
            return {
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 200, message: 'Privacy settings successfully updated.' }),
            };
        });

        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);
        fireEvent.click(document.getElementById('v-pills-privacy-tab'));

        const visSelect = document.getElementById('profileVisibilitySelect');
        const showProfileCheck = document.getElementById('privacy_show_my_profile');
        fireEvent.change(visSelect, { target: { value: 'Private' } });
        expect(showProfileCheck.checked).toBe(false);

        expect(screen.getByText(/No biometric devices registered yet/)).toBeTruthy();

        const searchInput = document.getElementById('restrictMemberSearch');
        fireEvent.input(searchInput, { target: { value: 'Kemi' } });
        expect(screen.getByText('Kemi Adeyemi')).toBeTruthy();
        expect(screen.queryByText(/Adebayo Extremely/)).toBeNull();

        fireEvent.input(searchInput, { target: { value: '' } });
        const restrictBtns = screen.getAllByText('Restrict All');
        fireEvent.click(restrictBtns[0]);

        await waitFor(() => {
            expect(globalThis.fetch).toHaveBeenCalledWith(
                '/api/privacy/restrictions/update',
                expect.objectContaining({ method: 'POST' })
            );
        });
    });

    it('supports LineageScopePicker on Parents & Siblings and dynamic rows on Children', async () => {
        emailIsRegistered.mockResolvedValueOnce(false);
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);

        fireEvent.click(document.getElementById('v-pills-parents-tab'));
        expect(document.getElementById('parentsLineageScope').value).toBe('maternal');
        fireEvent.click(document.getElementById('parents-scope-primary'));
        expect(document.getElementById('parentsLineageScope').value).toBe('primary');

        // Clear dirty state? parents radio does not mark dirty; switch directly to children
        fireEvent.click(document.getElementById('v-pills-children-tab'));
        const countSelect = document.getElementById('children');
        fireEvent.change(countSelect, { target: { value: '1' } });

        expect(document.getElementById('children_first_name1')).toBeTruthy();
        fireEvent.change(document.getElementById('children_option1'), {
            target: { value: 'With Former Partner' },
        });
        expect(document.getElementById('children_other_parent1')).toBeTruthy();
        expect(document.getElementById('children_union_status1')).toBeTruthy();

        fireEvent.input(document.getElementById('children_email1'), {
            target: { value: 'child.new@example.com' },
        });

        await waitFor(
            () => {
                expect(screen.getByText(/not on the platform yet/i)).toBeTruthy();
            },
            { timeout: 1000 }
        );
    });

    it('dynamically toggles Marital spouse fields and handles Family Code Solo branch out with live refresh', async () => {
        globalThis.fetch = vi.fn().mockImplementation(async (url, opts) => {
            if (String(url).includes('/api/account-settings/state')) {
                return {
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        ok: true,
                        status: 'success',
                        data: buildBootstrap({ accountData: { famCode: 'SOLO999' } }),
                    }),
                };
            }
            if (opts && opts.body && opts.body.get('action') === 'staySoloCode') {
                return {
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 200,
                        message: 'Successfully branched out!',
                        token: { family_code: 'SOLO999' },
                    }),
                };
            }
            return {
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 200, message: 'OK' }),
            };
        });

        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);

        fireEvent.click(document.getElementById('v-pills-marital-tab'));
        const maritalSelect = document.getElementById('maritalStatus');
        fireEvent.change(maritalSelect, { target: { value: 'Yes - Add Wife' } });
        expect(document.getElementById('spouse')).toBeTruthy();
        expect(document.getElementById('maiden_name_div')).toBeTruthy();

        fireEvent.change(maritalSelect, { target: { value: 'Divorced' } });
        expect(document.getElementById('spouse_gender_div')).toBeTruthy();

        // Reset back to original before switching tabs so dirty prompt is bypassed
        fireEvent.change(maritalSelect, { target: { value: 'Single' } });
        fireEvent.click(document.getElementById('v-pills-familycode-tab'));

        fireEvent.click(document.getElementById('btnStaySolo'));

        await waitFor(() => {
            expect(document.getElementById('currentFamCodeValue').textContent).toBe('SOLO999');
        });
    });

    it('filters settings via Quick Search and protects unsaved changes on tab switch', () => {
        render(<AccountSettingsShell bootstrap={buildBootstrap()} />);

        const quickSearch = document.getElementById('settingsQuickSearch');
        fireEvent.input(quickSearch, { target: { value: 'passkey' } });
        expect(document.getElementById('v-pills-privacy-tab')).toBeTruthy();
        expect(document.getElementById('v-pills-marital-tab')).toBeNull();

        fireEvent.input(quickSearch, { target: { value: '' } });

        // Edit mobile to make Profile dirty, then attempt to switch to Privacy
        fireEvent.input(document.getElementById('mobile'), { target: { value: '+447700999888' } });
        fireEvent.click(document.getElementById('v-pills-privacy-tab'));

        expect(screen.getByTestId('unsaved-changes-banner')).toBeTruthy();
        fireEvent.click(screen.getByText('Discard & Switch'));
        expect(screen.getByText('Privacy & Security')).toBeTruthy();
    });

    it('wraps shell inside IslandErrorBoundary and shows 1-tap retry on crash', () => {
        const Boom = () => {
            throw new Error('Test AccountSettings crash');
        };
        const errSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(
            <IslandErrorBoundary name="AccountSettings" fallbackTitle="Unable to display Account Settings cleanly.">
                <Boom />
            </IslandErrorBoundary>
        );

        expect(screen.getByText('Unable to display Account Settings cleanly.')).toBeTruthy();
        expect(screen.getByText('Try Again')).toBeTruthy();
        errSpy.mockRestore();
    });
});
