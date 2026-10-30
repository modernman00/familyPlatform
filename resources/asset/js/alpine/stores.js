'use strict';

/**
 * FamilyPlatform Enterprise Alpine Reactive Stores
 * Single source of truth for global UI state across desktop, tablet, and mobile.
 */

export function initAlpineStores(Alpine) {
  // 1. Notifications Reactive Store
  Alpine.store('notifications', {
    unreadCount: 0,
    get display() {
      if (this.unreadCount > 99) return '99+';
      return this.unreadCount > 0 ? String(this.unreadCount) : '';
    },
    get hasUnread() {
      return this.unreadCount > 0;
    },
    setCount(count) {
      const num = Math.max(0, parseInt(count, 10) || 0);
      this.unreadCount = num;
      this._syncAppBadge(num);
      this._syncLegacyDom(num);
    },
    increment(by = 1) {
      this.setCount(this.unreadCount + (parseInt(by, 10) || 1));
    },
    decrement(by = 1) {
      this.setCount(Math.max(0, this.unreadCount - (parseInt(by, 10) || 1)));
    },
    clear() {
      this.setCount(0);
    },
    _syncAppBadge(num) {
      if (typeof navigator !== 'undefined' && 'setAppBadge' in navigator) {
        if (num > 0) {
          navigator.setAppBadge(num).catch(() => {});
        } else {
          navigator.clearAppBadge().catch(() => {});
        }
      }
    },
    _syncLegacyDom(num) {
      const displayStr = num > 99 ? '99+' : (num > 0 ? String(num) : '');
      const badgeIds = ['notification_count', 'header_notif_count', 'pwa_bottom_badge'];
      badgeIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
          if (num <= 0) {
            el.style.display = 'none';
            el.textContent = '';
          } else {
            el.textContent = displayStr;
            el.style.display = (id === 'pwa_bottom_badge') ? 'inline-flex' : 'flex';
          }
        }
      });
    }
  });

  // 2. Theme & Dark Mode Reactive Store
  Alpine.store('theme', {
    isDark: false,
    init() {
      try {
        const savedTheme = localStorage.getItem('familyPlatform_theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isPublicSurface = document.body?.classList.contains('public-layout') ||
                                document.body?.getAttribute('data-surface') === 'light' ||
                                document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';

        // On public landing & auth pages, default to light mode unless explicitly saved as dark by user
        if (isPublicSurface) {
          this.isDark = savedTheme === 'dark';
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
      if (typeof document === 'undefined') return;
      if (this.isDark) {
        document.body?.classList.add('dark-mode');
        document.documentElement?.classList.add('dark');
      } else {
        document.body?.classList.remove('dark-mode');
        document.documentElement?.classList.remove('dark');
      }

      const themeBadge = document.getElementById('themeStatusBadge');
      if (themeBadge) {
        if (this.isDark) {
          themeBadge.textContent = 'Dark';
          themeBadge.className = 'badge bg-warning text-dark';
        } else {
          themeBadge.textContent = 'Light';
          themeBadge.className = 'badge bg-secondary-subtle text-secondary';
        }
      }
    }
  });

  // 3. Network & PWA Connectivity Store
  Alpine.store('network', {
    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
    init() {
      if (typeof window === 'undefined') return;
      window.addEventListener('online', () => {
        this.isOnline = true;
        if (window.swalToast) {
          window.swalToast({ icon: 'success', title: 'Back online' });
        }
      });
      window.addEventListener('offline', () => {
        this.isOnline = false;
        if (window.swalToast) {
          window.swalToast({ icon: 'warning', title: 'You are currently offline' });
        }
      });
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
