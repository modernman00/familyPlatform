import { describe, it, expect, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/preact';
import {
    getPlatformStore,
    usePlatformNotifications,
    usePlatformTheme,
    usePlatformNetwork,
    usePlatformCsrf,
} from './platformStore';
import { initAlpineStores } from '../../alpine/stores';
import { initAlpineMagics } from '../../alpine/magics';
import { syncCsrfTokenDom } from './bootstrap';

function createMockAlpine() {
    const stores = {};
    const magics = {};
    return {
        stores,
        magics,
        store(name, value) {
            if (value !== undefined) {
                stores[name] = value;
            }
            return stores[name];
        },
        magic(name, fn) {
            magics[name] = fn;
        },
    };
}

function PlatformStatusHarness() {
    const notif = usePlatformNotifications();
    const theme = usePlatformTheme();
    const net = usePlatformNetwork();
    const csrf = usePlatformCsrf();

    return (
        <div>
            <span data-testid="notif-display">{notif.display || '0'}</span>
            <span data-testid="theme-state">{theme.isDark ? 'dark' : 'light'}</span>
            <span data-testid="net-state">{net.isOnline ? 'online' : 'offline'}</span>
            <span data-testid="csrf-state">{csrf.csrfToken}</span>
            <button type="button" onClick={() => notif.increment(2)}>
                AddNotif
            </button>
            <button type="button" onClick={() => theme.toggle()}>
                ToggleTheme
            </button>
        </div>
    );
}

describe('platformStore (Alpine $store <-> Preact Island Bridge)', () => {
    let mockAlpine;

    beforeEach(() => {
        delete window.__FP_PLATFORM_STORE__;
        window.sessionStorage.clear();
        window.localStorage.clear();
        document.body.className = '';
        document.body.removeAttribute('data-surface');
        document.documentElement.className = '';
        document.head.innerHTML = '<meta name="csrf-token" content="initial_csrf_123">';

        mockAlpine = createMockAlpine();
        window.Alpine = mockAlpine;
        initAlpineStores(mockAlpine);
        initAlpineMagics(mockAlpine);
    });

    it('synchronises notification unread counts bi-directionally between Alpine.store and Preact hooks', async () => {
        render(<PlatformStatusHarness />);
        expect(screen.getByTestId('notif-display').textContent).toBe('0');
        expect(mockAlpine.store('notifications').hasUnread).toBe(false);

        // 1. Update from Alpine store -> Preact updates immediately
        mockAlpine.store('notifications').setCount(5);
        expect(mockAlpine.store('notifications').display).toBe('5');
        expect(await screen.findByText('5')).toBeTruthy();

        // 2. Update from Preact hook -> Alpine store & sessionStorage update immediately
        fireEvent.click(screen.getByText('AddNotif'));
        expect(mockAlpine.store('notifications').unreadCount).toBe(7);
        expect(window.sessionStorage.getItem('notificationCount')).toBe('7');

        // 3. pwa:badge-updated CustomEvent updates both
        window.dispatchEvent(new CustomEvent('pwa:badge-updated', { detail: { count: 1 } }));
        expect(mockAlpine.store('notifications').unreadCount).toBe(1);
    });

    it('synchronises dark mode across Alpine.store, Preact hooks, and DOM classes while honouring isPublicSurface', () => {
        render(<PlatformStatusHarness />);
        expect(screen.getByTestId('theme-state').textContent).toBe('light');

        // Toggle from Preact -> updates Alpine store and body.dark-mode
        fireEvent.click(screen.getByText('ToggleTheme'));
        expect(mockAlpine.store('theme').isDark).toBe(true);
        expect(document.body.classList.contains('dark-mode')).toBe(true);

        // Toggle from Alpine -> updates Preact store and removes body.dark-mode
        mockAlpine.store('theme').toggle();
        expect(getPlatformStore().theme.isDark).toBe(false);
        expect(document.body.classList.contains('dark-mode')).toBe(false);

        // Public surface guard prevents dark mode
        document.body.classList.add('public-layout');
        getPlatformStore().theme.setDark(true);
        expect(getPlatformStore().theme.isDark).toBe(false);
        expect(document.body.classList.contains('dark-mode')).toBe(false);
    });

    it('unifies CSRF token access and rotation between Alpine $csrf magic and Preact platformStore', () => {
        const csrfGetter = mockAlpine.magics.csrf();
        expect(csrfGetter()).toBe('initial_csrf_123');

        syncCsrfTokenDom('rotated_csrf_456');
        expect(csrfGetter()).toBe('rotated_csrf_456');
        expect(getPlatformStore().csrf.token).toBe('rotated_csrf_456');
    });
});
