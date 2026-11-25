import { createSignal, computed, useSignalValue } from './signals';
import { getCsrfToken, syncCsrfTokenDom } from './bootstrap';

/**
 * Shared Reactive Platform Store Bridge (Alpine.js $store <-> Preact Islands).
 * Anchored on window.__FP_PLATFORM_STORE__ so separate Webpack/Vite bundles
 * (index.js, feed-app.js, profile-app.js, etc.) share one canonical reactive instance.
 */

export function detectPublicSurface() {
    if (typeof document === 'undefined') return false;
    const isPublicSurface =
        document.body?.classList.contains('public-layout') ||
        document.body?.getAttribute('data-surface') === 'light' ||
        document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';
    return Boolean(isPublicSurface);
}

function readInitialUnreadCount() {
    if (typeof window === 'undefined') return 0;
    try {
        const fromSession = parseInt(window.sessionStorage?.getItem('notificationCount') || '', 10);
        if (!Number.isNaN(fromSession) && fromSession >= 0) {
            return fromSession;
        }
    } catch {
        // Ignore sessionStorage errors in restricted contexts
    }
    if (typeof document !== 'undefined') {
        const fromServerAttr = parseInt(
            document.getElementById('notification_count')?.dataset?.serverCount || '0',
            10
        );
        if (!Number.isNaN(fromServerAttr) && fromServerAttr >= 0) {
            return fromServerAttr;
        }
    }
    return 0;
}

function readInitialDarkMode() {
    if (typeof window === 'undefined') return false;
    if (detectPublicSurface()) return false;
    try {
        const savedTheme = window.localStorage?.getItem('familyPlatform_theme');
        const prefersDark =
            typeof window.matchMedia === 'function' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches;
        return savedTheme === 'dark' || (savedTheme === null && prefersDark);
    } catch {
        return false;
    }
}

function syncNavigatorAppBadge(num) {
    if (typeof navigator !== 'undefined' && 'setAppBadge' in navigator) {
        if (num > 0) {
            navigator.setAppBadge(num).catch(() => {});
        } else if ('clearAppBadge' in navigator) {
            navigator.clearAppBadge().catch(() => {});
        }
    }
}

function dispatchPlatformEvent(name, detail) {
    if (typeof window !== 'undefined' && typeof window.CustomEvent === 'function') {
        window.dispatchEvent(new window.CustomEvent(name, { detail }));
    }
}

export function createPlatformStore() {
    const unreadCountSignal = createSignal(readInitialUnreadCount());
    const displayComputed = computed(() => {
        const count = unreadCountSignal.value;
        return count > 0 ? String(count) : '';
    });
    const hasUnreadComputed = computed(() => unreadCountSignal.value > 0);

    const isDarkSignal = createSignal(readInitialDarkMode());
    const isOnlineSignal = createSignal(
        typeof navigator !== 'undefined' && typeof navigator.onLine === 'boolean'
            ? navigator.onLine
            : true
    );
    const csrfSignal = createSignal(getCsrfToken());

    let listenersAttached = false;

    const notifications = {
        unreadCountSignal,
        displayComputed,
        hasUnreadComputed,
        get unreadCount() {
            return unreadCountSignal.value;
        },
        get display() {
            return displayComputed.value;
        },
        get hasUnread() {
            return hasUnreadComputed.value;
        },
        setCount(count, { persist = true, emit = true } = {}) {
            const num = Math.max(0, parseInt(count, 10) || 0);
            unreadCountSignal.value = num;
            if (persist && typeof window !== 'undefined') {
                try {
                    window.sessionStorage?.setItem('notificationCount', String(num));
                } catch {
                    // Ignore quota errors
                }
            }
            syncNavigatorAppBadge(num);
            if (typeof window !== 'undefined' && window.Alpine && typeof window.Alpine.store === 'function') {
                const alpineNotif = window.Alpine.store('notifications');
                if (alpineNotif && alpineNotif.unreadCount !== num) {
                    alpineNotif.unreadCount = num;
                }
            }
            if (emit) {
                dispatchPlatformEvent('fp:notifications-changed', { unreadCount: num });
            }
            return num;
        },
        increment(by = 1) {
            return this.setCount(unreadCountSignal.peek() + (parseInt(by, 10) || 1));
        },
        decrement(by = 1) {
            return this.setCount(Math.max(0, unreadCountSignal.peek() - (parseInt(by, 10) || 1)));
        },
        clear() {
            return this.setCount(0);
        },
        subscribe(fn) {
            return unreadCountSignal.subscribe(fn);
        },
    };

    const theme = {
        isDarkSignal,
        get isDark() {
            return isDarkSignal.value;
        },
        applyDom(dark = isDarkSignal.peek()) {
            if (typeof document === 'undefined') return;
            const effectiveDark = detectPublicSurface() ? false : Boolean(dark);
            if (effectiveDark) {
                document.body?.classList.add('dark-mode');
                document.documentElement?.classList.add('dark', 'dark-mode');
            } else {
                document.body?.classList.remove('dark-mode', 'dark-theme');
                document.documentElement?.classList.remove('dark', 'dark-mode');
            }
        },
        setDark(dark, { persist = true, emit = true } = {}) {
            const effectiveDark = detectPublicSurface() ? false : Boolean(dark);
            isDarkSignal.value = effectiveDark;
            if (persist && !detectPublicSurface() && typeof window !== 'undefined') {
                try {
                    window.localStorage?.setItem('familyPlatform_theme', effectiveDark ? 'dark' : 'light');
                } catch {
                    // Ignore storage errors
                }
            }
            this.applyDom(effectiveDark);
            if (typeof window !== 'undefined' && window.Alpine && typeof window.Alpine.store === 'function') {
                const alpineTheme = window.Alpine.store('theme');
                if (alpineTheme && alpineTheme.isDark !== effectiveDark) {
                    alpineTheme.isDark = effectiveDark;
                }
            }
            if (emit) {
                dispatchPlatformEvent('fp:theme-changed', { isDark: effectiveDark });
            }
            return effectiveDark;
        },
        toggle() {
            return this.setDark(!isDarkSignal.peek());
        },
        init() {
            const initialDark = readInitialDarkMode();
            this.setDark(initialDark, { persist: false, emit: false });
            return initialDark;
        },
        subscribe(fn) {
            return isDarkSignal.subscribe(fn);
        },
    };

    const network = {
        isOnlineSignal,
        get isOnline() {
            return isOnlineSignal.value;
        },
        setOnline(online, { emit = true } = {}) {
            const next = Boolean(online);
            isOnlineSignal.value = next;
            if (typeof window !== 'undefined' && window.Alpine && typeof window.Alpine.store === 'function') {
                const alpineNet = window.Alpine.store('network');
                if (alpineNet && alpineNet.isOnline !== next) {
                    alpineNet.isOnline = next;
                }
            }
            if (emit) {
                dispatchPlatformEvent('fp:network-changed', { isOnline: next });
            }
            return next;
        },
        subscribe(fn) {
            return isOnlineSignal.subscribe(fn);
        },
    };

    const csrf = {
        csrfSignal,
        get token() {
            const current = csrfSignal.value;
            if (current) return current;
            const resolved = getCsrfToken();
            if (resolved && resolved !== csrfSignal.peek()) {
                csrfSignal.value = resolved;
            }
            return resolved;
        },
        get(bootstrap = {}) {
            return getCsrfToken(bootstrap) || csrfSignal.peek() || '';
        },
        set(token, { syncDom = true, emit = true } = {}) {
            const clean = String(token || '').trim();
            if (!clean) return '';
            csrfSignal.value = clean;
            if (syncDom) {
                syncCsrfTokenDom(clean);
            }
            if (emit) {
                dispatchPlatformEvent('fp:csrf-changed', { csrfToken: clean });
            }
            return clean;
        },
        subscribe(fn) {
            return csrfSignal.subscribe(fn);
        },
    };

    function attachGlobalListeners() {
        if (listenersAttached || typeof window === 'undefined') return;
        listenersAttached = true;

        window.addEventListener('pwa:badge-updated', (e) => {
            const nextCount = e?.detail?.count;
            if (nextCount !== undefined) {
                notifications.setCount(nextCount, { emit: false });
            }
        });

        window.addEventListener('storage', (e) => {
            if (e?.key === 'notification_sync_trigger') {
                notifications.setCount(readInitialUnreadCount(), { persist: false, emit: true });
            } else if (e?.key === 'familyPlatform_theme' && e.newValue) {
                theme.setDark(e.newValue === 'dark', { persist: false, emit: true });
            }
        });
    }

    attachGlobalListeners();

    return {
        notifications,
        theme,
        network,
        csrf,
    };
}

export function getPlatformStore() {
    if (typeof window !== 'undefined') {
        if (!window.__FP_PLATFORM_STORE__) {
            window.__FP_PLATFORM_STORE__ = createPlatformStore();
        }
        return window.__FP_PLATFORM_STORE__;
    }
    return createPlatformStore();
}

export function usePlatformNotifications() {
    const store = getPlatformStore();
    const unreadCount = useSignalValue(store.notifications.unreadCountSignal);
    return {
        unreadCount,
        display: unreadCount > 0 ? String(unreadCount) : '',
        hasUnread: unreadCount > 0,
        setCount: (c) => store.notifications.setCount(c),
        increment: (by) => store.notifications.increment(by),
        decrement: (by) => store.notifications.decrement(by),
        clear: () => store.notifications.clear(),
    };
}

export function usePlatformTheme() {
    const store = getPlatformStore();
    const isDark = useSignalValue(store.theme.isDarkSignal);
    return {
        isDark,
        toggle: () => store.theme.toggle(),
        setDark: (dark) => store.theme.setDark(dark),
    };
}

export function usePlatformNetwork() {
    const store = getPlatformStore();
    const isOnline = useSignalValue(store.network.isOnlineSignal);
    return {
        isOnline,
        setOnline: (online) => store.network.setOnline(online),
    };
}

export function usePlatformCsrf() {
    const store = getPlatformStore();
    const csrfToken = useSignalValue(store.csrf.csrfSignal);
    return {
        csrfToken: csrfToken || store.csrf.get(),
        setCsrfToken: (token) => store.csrf.set(token),
    };
}
