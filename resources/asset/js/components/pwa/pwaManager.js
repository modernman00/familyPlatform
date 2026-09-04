/**
 * FamilyPlatform PWA Manager
 * World-Class Progressive Web App Engine:
 * - In-app installation banner (Android/Chrome/Edge)
 * - iOS Safari animated 'Add to Home Screen' visual tutorial
 * - Service worker lifecycle & seamless update notification
 * - Real-time Online/Offline connection HUD
 * - Native Badging API integration
 */

'use strict';

import OfflineSyncManager from './offlineSync';
import { triggerHaptic } from './haptics';

class PWAManager {
  constructor() {
    this.deferredPrompt = null;
    this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    this.offlineSync = new OfflineSyncManager();
    window.offlineSync = this.offlineSync;
    window.triggerHaptic = triggerHaptic;
    // Web Push lives in components/profilePage/registerPushNotification.js — one
    // module, imported by the profile-page bundle and driven by the Settings toggle.
    this.init();
  }

  init() {
    this.registerServiceWorker();
    this.setupInstallPrompt();
    this.setupIOSPrompt();
    this.setupNetworkHUD();
    this.setupLogoutCachePurge();
    this.setupBottomNav();
    this.setupHaptics();
  }

  /**
   * Setup Logout Listener to purge dynamic user cache
   */
  setupLogoutCachePurge() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href*="signout"], a[href*="logout"]');
      if (link && navigator.serviceWorker?.controller) {
        navigator.serviceWorker.controller.postMessage({ action: 'clearUserCache' });
      }
    });
  }

  /**
   * 1. Register Service Worker & Handle Updates
   */
  registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
      navigator.serviceWorker
        .register('/service-worker.js', { scope: '/' })
        .then((registration) => {
          // Check for SW updates
          registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            if (!newWorker) return;

            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                this.showUpdateToast(newWorker);
              }
            });
          });
        })
        .catch((err) => {
          console.warn('[PWA] Service Worker registration failed:', err);
        });

      // Handle controllerchange to auto-reload upon skipWaiting.
      // controllerchange also fires the very first time a service worker
      // ever activates for this origin (no prior controller) - reloading
      // then would blow away whatever a first-time visitor is mid-doing
      // (e.g. an in-flight login). Only reload when a controller already
      // existed, i.e. this is an actual update taking over, not an install.
      let hadController = !!navigator.serviceWorker.controller;
      let refreshing = false;
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!hadController) {
          hadController = true;
          return;
        }
        if (!refreshing) {
          refreshing = true;
          window.location.reload();
        }
      });
    });
  }

  /**
   * Show a non-intrusive Update Notification Toast
   */
  showUpdateToast(newWorker) {
    if (document.getElementById('pwa-update-toast')) return;

    const toast = document.createElement('div');
    toast.id = 'pwa-update-toast';
    toast.innerHTML = `
      <div class="pwa-toast-card">
        <div class="pwa-toast-icon">⚡</div>
        <div class="pwa-toast-content">
          <h6>Update Available</h6>
          <p>A fresh version of FamilyPlatform is ready.</p>
        </div>
        <button id="pwa-update-btn" class="pwa-btn pwa-btn-primary">Update</button>
        <button id="pwa-update-close" class="pwa-btn-close" aria-label="Close">&times;</button>
      </div>
    `;

    document.body.appendChild(toast);

    document.getElementById('pwa-update-btn')?.addEventListener('click', () => {
      newWorker.postMessage({ action: 'skipWaiting' });
      toast.remove();
    });

    document.getElementById('pwa-update-close')?.addEventListener('click', () => {
      toast.remove();
    });
  }

  /**
   * 2. Android / Chromium Custom Install Banner
   */
  setupInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (e) => {
      // Prevent standard browser mini-infobar
      e.preventDefault();
      this.deferredPrompt = e;

      // Don't show if user dismissed within last 7 days or already installed
      const dismissed = localStorage.getItem('pwa_install_dismissed');
      if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
        return;
      }

      this.showInstallBanner();
    });

    window.addEventListener('appinstalled', () => {
      this.deferredPrompt = null;
      document.getElementById('pwa-install-banner')?.remove();
      console.log('[PWA] FamilyPlatform successfully installed!');
    });
  }

  showInstallBanner() {
    if (document.getElementById('pwa-install-banner') || this.isStandalone) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.innerHTML = `
      <div class="pwa-banner-card">
        <div class="pwa-banner-header">
          <img src="/public/img/favicon/android-chrome-192x192.png" alt="FamilyPlatform" class="pwa-banner-logo" style="width: 46px; height: 46px; border-radius: 12px; object-fit: cover;" onerror="this.src='/resources/images/avatarM.png'">
          <div class="pwa-banner-text">
            <h6>Install FamilyPlatform</h6>
            <p>Get instant alerts, fast family updates & offline access.</p>
          </div>
          <button id="pwa-banner-close" class="pwa-btn-close" aria-label="Dismiss">&times;</button>
        </div>
        <div class="pwa-banner-actions">
          <button id="pwa-install-btn" class="pwa-btn pwa-btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Add to Home Screen
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(banner);

    // Trigger slide-up animation
    setTimeout(() => banner.classList.add('show'), 100);

    document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
      if (!this.deferredPrompt) return;
      banner.remove();
      this.deferredPrompt.prompt();
      const { outcome } = await this.deferredPrompt.userChoice;
      console.log(`[PWA] Install prompt outcome: ${outcome}`);
      this.deferredPrompt = null;
    });

    document.getElementById('pwa-banner-close')?.addEventListener('click', () => {
      localStorage.setItem('pwa_install_dismissed', Date.now().toString());
      banner.classList.remove('show');
      setTimeout(() => banner.remove(), 400);
    });
  }

  /**
   * 3. iOS Safari "Add to Home Screen" Visual Overlay Tutorial
   */
  setupIOSPrompt() {
    if (!this.isIOS || this.isStandalone) return;

    const dismissed = localStorage.getItem('pwa_ios_dismissed');
    if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
      return;
    }

    // Delay prompt to not overwhelm user on initial landing
    setTimeout(() => {
      this.showIOSOverlay();
    }, 4000);
  }

  showIOSOverlay() {
    if (document.getElementById('pwa-ios-overlay') || this.isStandalone) return;

    const overlay = document.createElement('div');
    overlay.id = 'pwa-ios-overlay';
    overlay.innerHTML = `
      <div class="pwa-ios-card">
        <button id="pwa-ios-close" class="pwa-btn-close" aria-label="Close">&times;</button>
        <div class="pwa-ios-header">
          <img src="/public/img/favicon/apple-touch-icon.png" alt="FamilyPlatform" class="pwa-ios-logo" onerror="this.src='/resources/images/avatarM.png'">
          <div>
            <h6>Install FamilyPlatform on iOS</h6>
            <p>Install this app on your iPhone for the full native experience.</p>
          </div>
        </div>
        <div class="pwa-ios-steps">
          <div class="pwa-step">
            <span class="step-num">1</span>
            <span>Tap the <strong>Share</strong> button <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#007aff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> at the bottom of Safari.</span>
          </div>
          <div class="pwa-step">
            <span class="step-num">2</span>
            <span>Scroll down and tap <strong>Add to Home Screen</strong> <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>.</span>
          </div>
        </div>
        <div class="pwa-ios-arrow-pointer">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#007aff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    setTimeout(() => overlay.classList.add('show'), 100);

    document.getElementById('pwa-ios-close')?.addEventListener('click', () => {
      localStorage.setItem('pwa_ios_dismissed', Date.now().toString());
      overlay.classList.remove('show');
      setTimeout(() => overlay.remove(), 400);
    });
  }

  /**
   * 4. Network Online/Offline HUD Banner
   */
  setupNetworkHUD() {
    const updateNetworkStatus = (online) => {
      let hud = document.getElementById('pwa-network-hud');
      if (!hud) {
        hud = document.createElement('div');
        hud.id = 'pwa-network-hud';
        document.body.appendChild(hud);
      }

      if (online) {
        hud.className = 'pwa-hud online show';
        hud.innerHTML = `<span>🟢 Connected — Real-time family updates active</span>`;
        setTimeout(() => {
          hud.classList.remove('show');
        }, 3000);
      } else {
        hud.className = 'pwa-hud offline show';
        hud.innerHTML = `<span>⚠️ Offline Mode — Changes are queued and will sync automatically</span>`;
      }
    };

    window.addEventListener('online', () => updateNetworkStatus(true));
    window.addEventListener('offline', () => updateNetworkStatus(false));

    // Initial check
    if (!navigator.onLine) {
      updateNetworkStatus(false);
    }
  }

  /**
   * 5. App Badging API
   */
  static setBadge(count = 0) {
    if ('setAppBadge' in navigator) {
      if (count > 0) {
        navigator.setAppBadge(count).catch(() => {});
      } else {
        navigator.clearAppBadge().catch(() => {});
      }
    }
  }

  /**
   * 6. Mobile Bottom Navigation Shell & Badge Sync
   */
  setupBottomNav() {
    const nav = document.getElementById('pwaBottomNav');
    if (!nav) return;

    // Highlight active tab based on current path
    const path = window.location.pathname.toLowerCase();
    const tabs = nav.querySelectorAll('.pwa-tab-item');
    tabs.forEach((tab) => {
      const href = (tab.getAttribute('href') || '').toLowerCase();
      const tabType = tab.dataset.tab;

      if (tabType === 'feed' && (path === '/profilepage' || path === '/' || path === '')) {
        tab.classList.add('active');
      } else if (tabType === 'tree' && path.includes('/organogram')) {
        tab.classList.add('active');
      } else if (tabType === 'reels' && (path.includes('/reels') || path.includes('/familystudio'))) {
        tab.classList.add('active');
      } else if (tabType === 'members' && path.includes('/allmembers')) {
        tab.classList.add('active');
      } else if (href && href !== 'javascript:void(0)' && path === href) {
        tab.classList.add('active');
      } else {
        tab.classList.remove('active');
      }

      tab.addEventListener('click', () => {
        triggerHaptic('selection');
      });
    });

    // Wire Alerts Tab click
    const notifTab = document.getElementById('pwaNotifTab');
    if (notifTab) {
      notifTab.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        triggerHaptic('selection');
        
        const desktopNotifBtn = document.getElementById('notificationBtn');
        const dropdown = document.getElementById('notificationDropdown');
        
        if (dropdown && window.innerWidth < 992) {
            // On mobile, append to body so it's not hidden by collapsed navbar
            if (dropdown.parentNode !== document.body) {
                document.body.appendChild(dropdown);
                dropdown.classList.add('mobile-pwa-dropdown');
            }
            dropdown.classList.toggle('show');
        } else if (desktopNotifBtn) {
            desktopNotifBtn.click();
        } else {
            window.location.href = '/notifications';
        }
      });
    }

    // Two-way synchronization of notification badge count
    const syncBadge = () => {
      const desktopBadge = document.getElementById('notification_count');
      const mobileBadge = document.getElementById('pwa_bottom_badge');
      if (!desktopBadge || !mobileBadge) return;

      const rawCount = desktopBadge.textContent.trim();
      const count = parseInt(rawCount, 10);

      if (!isNaN(count) && count > 0) {
        mobileBadge.textContent = count > 99 ? '99+' : count;
        mobileBadge.style.display = 'inline-flex';
        PWAManager.setBadge(count);
      } else {
        mobileBadge.textContent = '';
        mobileBadge.style.display = 'none';
        PWAManager.setBadge(0);
      }
    };

    // Initial sync
    syncBadge();

    // Observe changes to desktop badge
    const desktopBadge = document.getElementById('notification_count');
    if (desktopBadge) {
      const observer = new MutationObserver(syncBadge);
      observer.observe(desktopBadge, { childList: true, characterData: true, subtree: true });
    }
  }

  /**
   * 7. Native Haptics Delegation
   */
  setupHaptics() {
    document.addEventListener('click', (e) => {
      // Like / Reactions
      if (e.target.closest('.likeBtn, .like-btn, .reaction-btn, [data-reaction], .btn-like')) {
        triggerHaptic('impact');
      }
      // Interactive pill buttons
      else if (e.target.closest('.btn-primary, .btn-user-stitch, .btn-icon-stitch')) {
        triggerHaptic('selection');
      }
    }, { passive: true });
  }
}

export default PWAManager;
