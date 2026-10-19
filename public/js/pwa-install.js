/**
 * FamilyPlatform PWA Install Engine & Smart A2HS Manager (N3 / Item #16)
 *
 * Prompts eligible mobile and desktop users to install FamilyPlatform
 * with zero annoying popups and smart persistence:
 * - Native `beforeinstallprompt` interception on Chromium (Android/Chrome/Edge)
 * - iOS Safari standalone install instructions walkthrough
 * - Respects dismissals for 14 days
 * - 44px minimum tap target compliance
 */

(function () {
    'use strict';

    if (window.__familyPwaInstall) return;

    let deferredPrompt = null;
    const DISMISS_STORAGE_KEY = 'fp_pwa_install_dismissed';
    const DISMISS_DURATION_MS = 14 * 24 * 60 * 60 * 1000; // 14 days

    // Check if running in standalone mode (already installed)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                      || window.navigator.standalone === true;

    if (isStandalone) {
        return; // Already running as PWA
    }

    // Check if user recently dismissed
    const dismissedAt = localStorage.getItem(DISMISS_STORAGE_KEY);
    if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10) < DISMISS_DURATION_MS)) {
        return;
    }

    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    // 1. Android / Chrome: Capture beforeinstallprompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallBanner('native');
    });

    // 2. iOS Safari: Show gentle Add-to-Home guide if mobile
    if (isIOS && isSafari && !isStandalone) {
        window.addEventListener('DOMContentLoaded', () => {
            // Delay 3 seconds on load to prevent friction
            setTimeout(() => {
                showInstallBanner('ios');
            }, 3000);
        });
    }

    function showInstallBanner(type) {
        if (document.getElementById('pwaInstallBanner')) return;

        const banner = document.createElement('div');
        banner.id = 'pwaInstallBanner';
        banner.className = 'pwa-install-toast';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-label', 'Install FamilyPlatform Application');

        if (type === 'native') {
            banner.innerHTML = `
                <div class="pwa-install-content">
                    <img src="/public/img/favicon/android-chrome-192x192.png" alt="FamilyPlatform Logo" class="pwa-install-icon" width="40" height="40">
                    <div class="pwa-install-text">
                        <strong class="pwa-install-title">Install Family Platform</strong>
                        <p class="pwa-install-subtitle">Get instant family milestone notifications on your home screen</p>
                    </div>
                    <div class="pwa-install-actions">
                        <button type="button" class="btn btn-sm btn-primary pwa-btn-install" id="btnPwaInstallAction">
                            Install App
                        </button>
                        <button type="button" class="btn-close btn-close-white pwa-btn-dismiss" id="btnPwaDismissAction" aria-label="Dismiss"></button>
                    </div>
                </div>
            `;
        } else {
            banner.innerHTML = `
                <div class="pwa-install-content">
                    <img src="/public/img/favicon/apple-touch-icon.png" alt="FamilyPlatform Logo" class="pwa-install-icon" width="40" height="40">
                    <div class="pwa-install-text">
                        <strong class="pwa-install-title">Install on iPhone</strong>
                        <p class="pwa-install-subtitle">Tap <i class="bi bi-box-arrow-up text-primary"></i> Share, then <strong>"Add to Home Screen"</strong></p>
                    </div>
                    <div class="pwa-install-actions">
                        <button type="button" class="btn-close btn-close-white pwa-btn-dismiss" id="btnPwaDismissAction" aria-label="Dismiss"></button>
                    </div>
                </div>
            `;
        }

        document.body.appendChild(banner);

        // Attach action handlers
        const installBtn = document.getElementById('btnPwaInstallAction');
        if (installBtn && deferredPrompt) {
            installBtn.addEventListener('click', async () => {
                banner.style.display = 'none';
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    if (window.familyTelemetry) window.familyTelemetry.track('pwa_installed');
                }
                deferredPrompt = null;
            });
        }

        const dismissBtn = document.getElementById('btnPwaDismissAction');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                banner.style.opacity = '0';
                setTimeout(() => banner.remove(), 300);
                localStorage.setItem(DISMISS_STORAGE_KEY, Date.now().toString());
                if (window.familyTelemetry) window.familyTelemetry.track('pwa_install_dismissed');
            });
        }
    }

    window.__familyPwaInstall = true;
})();
