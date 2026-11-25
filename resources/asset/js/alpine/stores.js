'use strict';

import { getPlatformStore } from '../apps/shared/platformStore';

/**
 * FamilyPlatform Enterprise Alpine Reactive Stores
 * Single source of truth for global UI state across desktop, tablet, and mobile.
 * Synchronised with Preact islands via the shared platformStore bridge.
 */

export function initAlpineStores(Alpine) {
  const platformStore = getPlatformStore();

  // 1. Notifications Reactive Store
  Alpine.store('notifications', {
    unreadCount: platformStore.notifications.unreadCount,
    get display() {
      return this.unreadCount > 0 ? String(this.unreadCount) : '';
    },
    get hasUnread() {
      return this.unreadCount > 0;
    },
    setCount(count) {
      const num = Math.max(0, parseInt(count, 10) || 0);
      this.unreadCount = num;
      platformStore.notifications.setCount(num);
    },
    increment(by = 1) {
      this.setCount(this.unreadCount + (parseInt(by, 10) || 1));
    },
    decrement(by = 1) {
      this.setCount(Math.max(0, this.unreadCount - (parseInt(by, 10) || 1)));
    },
    clear() {
      this.setCount(0);
    }
  });

  platformStore.notifications.subscribe((nextCount) => {
    const notifStore = Alpine.store('notifications');
    if (notifStore && notifStore.unreadCount !== nextCount) {
      notifStore.unreadCount = nextCount;
    }
  });

  // 2. Theme & Dark Mode Reactive Store
  Alpine.store('theme', {
    isDark: platformStore.theme.isDark,
    init() {
      try {
        const savedTheme = localStorage.getItem('familyPlatform_theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isPublicSurface = document.body?.classList.contains('public-layout') ||
                                document.body?.getAttribute('data-surface') === 'light' ||
                                document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';

        // On public landing & auth pages (data-surface="light" / .public-layout), always enforce light mode
        if (isPublicSurface) {
          this.isDark = false;
        } else {
          this.isDark = savedTheme === 'dark' || (savedTheme === null && prefersDark);
        }
      } catch (e) {
        this.isDark = false;
      }
      this.apply();

      // Listen for system changes if user hasn't explicitly overridden
      if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
          const isCurrentlyPublic = document.body?.classList.contains('public-layout') ||
                                    document.body?.getAttribute('data-surface') === 'light' ||
                                    document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';
          if (isCurrentlyPublic) return;
          if (!localStorage.getItem('familyPlatform_theme')) {
            this.isDark = e.matches;
            this.apply();
          }
        });
      }
    },
    toggle() {
      this.isDark = !this.isDark;
      try {
        localStorage.setItem('familyPlatform_theme', this.isDark ? 'dark' : 'light');
      } catch (e) {}
      this.apply();
    },
    apply() {
      platformStore.theme.setDark(this.isDark, { persist: false });
    }
  });

  platformStore.theme.subscribe((nextDark) => {
    const themeStore = Alpine.store('theme');
    if (themeStore && themeStore.isDark !== nextDark) {
      themeStore.isDark = nextDark;
    }
  });

  // 3. Network & PWA Connectivity Store
  Alpine.store('network', {
    isOnline: platformStore.network.isOnline,
    init() {
      if (typeof window === 'undefined') return;
      window.addEventListener('online', () => {
        this.isOnline = true;
        platformStore.network.setOnline(true);
        if (window.swalToast) {
          window.swalToast({ icon: 'success', title: 'Back online' });
        }
      });
      window.addEventListener('offline', () => {
        this.isOnline = false;
        platformStore.network.setOnline(false);
        if (window.swalToast) {
          window.swalToast({ icon: 'warning', title: 'You are currently offline' });
        }
      });
    }
  });

  platformStore.network.subscribe((nextOnline) => {
    const netStore = Alpine.store('network');
    if (netStore && netStore.isOnline !== nextOnline) {
      netStore.isOnline = nextOnline;
    }
  });

  // 4. Modal Manager Store (Focus & Body Scroll Protection)
  Alpine.store('modal', {
    activeModal: null,
    open(modalId) {
      this.activeModal = modalId;
      document.body?.classList.add('modal-open');
    },
    close(modalId = null) {
      if (!modalId || this.activeModal === modalId) {
        this.activeModal = null;
        document.body?.classList.remove('modal-open');
      }
    },
    isOpen(modalId) {
      return this.activeModal === modalId;
    }
  });
}
