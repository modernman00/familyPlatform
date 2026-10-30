{{-- 
  FamilyPlatform Push Notification & Standalone PWA Activation Legend
  Prompts users to activate push notifications and handles auto-sync for users who installed the app.
--}}
@php
    // Push notifications are strictly for authenticated family members with an active session.
    // Anonymous visitors on the landing page do not have accounts or family events to receive,
    // and prompting unauthenticated guests triggers browser anti-spam / Quiet UI restrictions.
    $authUserId = $_SESSION['id'] ?? null;
@endphp
@if(!empty($authUserId))
<style>
    /* WCAG AA: explicit surface + text colours so the banner never inherits washed-out theme text */
    #push-notification-banner { display: none; max-width: 620px; position: fixed; top: 20px; left: 16px; right: 16px; z-index: 1060; border-radius: 16px; border: 1.5px solid rgba(163, 22, 33, 0.35); background: #ffffff; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.25); font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif); }
    #push-notification-banner .push-banner-title { color: #0f172a; font-size: 1.125rem; font-weight: 700; line-height: 1.3; letter-spacing: -0.01em; }
    #push-notification-banner .push-banner-text { color: #334155; font-size: 0.875rem; font-weight: 500; line-height: 1.5; }
    #push-notification-banner .push-banner-icon { background: rgba(163, 22, 33, 0.1); color: #a31621; }
    #push-notification-banner #enable-push-btn { background: linear-gradient(135deg, #a31621 0%, #7a0e16 100%); color: #ffffff; border: none; min-height: 44px; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
    #push-notification-banner #dismiss-push-btn { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 600; min-height: 44px; white-space: nowrap; flex-shrink: 0; }
    #push-notification-banner #close-push-banner { opacity: 0.8; }
    body.dark-mode #push-notification-banner, body.dark-theme #push-notification-banner, [data-bs-theme="dark"] #push-notification-banner, html.dark #push-notification-banner, html.dark-mode #push-notification-banner { background: #1e293b; border-color: rgba(239, 107, 115, 0.5); }
    body.dark-mode #push-notification-banner .push-banner-title, body.dark-theme #push-notification-banner .push-banner-title, [data-bs-theme="dark"] #push-notification-banner .push-banner-title, html.dark #push-notification-banner .push-banner-title, html.dark-mode #push-notification-banner .push-banner-title { color: #f8fafc; }
    body.dark-mode #push-notification-banner .push-banner-text, body.dark-theme #push-notification-banner .push-banner-text, [data-bs-theme="dark"] #push-notification-banner .push-banner-text, html.dark #push-notification-banner .push-banner-text, html.dark-mode #push-notification-banner .push-banner-text { color: #e2e8f0; }
    body.dark-mode #push-notification-banner .push-banner-icon, body.dark-theme #push-notification-banner .push-banner-icon, [data-bs-theme="dark"] #push-notification-banner .push-banner-icon, html.dark #push-notification-banner .push-banner-icon, html.dark-mode #push-notification-banner .push-banner-icon { background: rgba(239, 107, 115, 0.18); color: #fca5a5; }
    body.dark-mode #push-notification-banner #dismiss-push-btn, body.dark-theme #push-notification-banner #dismiss-push-btn, [data-bs-theme="dark"] #push-notification-banner #dismiss-push-btn, html.dark #push-notification-banner #dismiss-push-btn, html.dark-mode #push-notification-banner #dismiss-push-btn { background: #334155; color: #f1f5f9; border-color: #475569; }
    body.dark-mode #push-notification-banner #close-push-banner, body.dark-theme #push-notification-banner #close-push-banner, [data-bs-theme="dark"] #push-notification-banner #close-push-banner, html.dark #push-notification-banner #close-push-banner, html.dark-mode #push-notification-banner #close-push-banner { filter: invert(1) grayscale(1) brightness(2); opacity: 1; }
</style>
<div id="push-notification-banner" class="alert alert-dismissible fade shadow-lg mx-auto" role="alert">
    <div class="d-flex align-items-start gap-3">
        <div class="push-banner-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px;">
            <i class="bi bi-bell-fill fs-4" style="line-height: 1;"></i>
        </div>
        <div class="flex-grow-1 pe-2">
            <h6 class="push-banner-title mb-1">Stay Connected with Your Family</h6>
            <p class="push-banner-text mb-0">
                Enable push notifications to receive instant alerts when family members post updates, celebrate milestones, or share memories.
            </p>
            <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                <button id="enable-push-btn" type="button" class="btn btn-sm text-white rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center" style="transition: transform 0.15s ease, background 0.2s ease;">
                    <i class="bi bi-bell-fill me-1.5" style="font-size: 0.85rem;"></i>
                    <span class="btn-text">Enable Notifications</span>
                </button>
                <button type="button" id="dismiss-push-btn" class="btn btn-sm border rounded-pill px-3">
                    Not Now
                </button>
            </div>
        </div>
        <button type="button" id="close-push-banner" class="btn-close flex-shrink-0" style="min-width: 44px; min-height: 44px; padding: 12px;" aria-label="Close"></button>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
(function () {
    'use strict';

    const DISMISS_KEY = 'fp_push_banner_dismissed';
    const SUBSCRIBED_KEY = 'fp_push_subscribed';
    const DISMISS_COOLDOWN_MS = 3 * 24 * 60 * 60 * 1000; // 3 days (72 hours)

    function isDismissedWithinCooldown() {
        try {
            const raw = localStorage.getItem(DISMISS_KEY);
            if (!raw) return false;
            if (raw === 'true') {
                // Migrate legacy boolean dismissal to a 3-day timestamp window
                localStorage.setItem(DISMISS_KEY, String(Date.now()));
                return true;
            }
            const dismissedAt = parseInt(raw, 10);
            if (isNaN(dismissedAt) || dismissedAt <= 0) {
                localStorage.removeItem(DISMISS_KEY);
                return false;
            }
            if ((Date.now() - dismissedAt) < DISMISS_COOLDOWN_MS) {
                return true;
            }
            localStorage.removeItem(DISMISS_KEY);
            return false;
        } catch (e) {
            return false;
        }
    }

    function recordDismissal() {
        try {
            localStorage.setItem(DISMISS_KEY, String(Date.now()));
        } catch (e) {}
    }

    const isIos = function() {
        const userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent);
    };

    const isInStandaloneMode = function() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
               (window.navigator.standalone === true);
    };

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function getCsrfToken() {
        const fromCookie = (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1];
        if (fromCookie) return decodeURIComponent(fromCookie);
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function getVapidPublicKey() {
        return document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content')
            || "{{ $_ENV['VAPID_PUBLIC_KEY'] ?? $_SERVER['VAPID_PUBLIC_KEY'] ?? (getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['MIX_VAPID_PUBLIC_KEY'] ?? '')) }}";
    }

    // Exposed globally so other components / settings can trigger subscription
    window.sendSubscriptionToBackEnd = function(subscription) {
        if (!subscription || !subscription.endpoint) {
            return Promise.resolve({ status: 'skipped' });
        }

        const token = getCsrfToken();
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '';
        if (!userId) {
            console.warn('[Push] Unauthenticated session - push subscription deferred');
            return Promise.resolve({ status: 'unauthenticated' });
        }
        const p256dh = subscription.getKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))) : '';
        const auth = subscription.getKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))) : '';

        const payload = {
            endpoint: subscription.endpoint,
            id: userId,
            token: token,
            keys: {
                p256dh: p256dh,
                auth: auth
            }
        };

        return fetch('/pushNotification/subscription', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
                'X-XSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        }).then(function(res) {
            if (res.status === 401) {
                console.warn('[Push] Server returned 401: Unauthenticated session');
                return { status: 'unauthorized' };
            }
            return res.json().catch(function() { return { status: 'ok' }; });
        }).then(function(data) {
            if (data && (data.status === 'success' || data.code === 200 || data.status === 'ok')) {
                localStorage.setItem(SUBSCRIBED_KEY, 'true');
                console.log('[Push] Subscription synchronized and registered in database:', data);
            }
            return data;
        });
    };

    function withTimeout(promise, ms, timeoutMessage) {
        return new Promise(function(resolve, reject) {
            const timer = setTimeout(function() {
                reject(new Error(timeoutMessage || 'Operation timed out'));
            }, ms);
            Promise.resolve(promise).then(
                function(val) { clearTimeout(timer); resolve(val); },
                function(err) { clearTimeout(timer); reject(err); }
            );
        });
    }

    function ensureServiceWorkerReady() {
        if (!('serviceWorker' in navigator)) {
            return Promise.reject(new Error('Service Workers are not supported in this browser.'));
        }

        return navigator.serviceWorker.getRegistration('/').then(function(reg) {
            if (reg) return reg;
            return navigator.serviceWorker.register('/sw.js', { scope: '/' });
        }).then(function(reg) {
            if (reg && reg.active) {
                return reg;
            }
            return withTimeout(
                navigator.serviceWorker.ready,
                8000,
                'Service Worker activation timed out. Please reload the page and try again.'
            );
        });
    }

    window.subscribeUserToPush = function() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return Promise.reject(new Error('Push not supported'));
        }

        const vapidKey = getVapidPublicKey();
        if (!vapidKey) {
            console.warn('[Push] VAPID key not configured in environment');
            return Promise.reject(new Error('VAPID key missing'));
        }

        return ensureServiceWorkerReady().then(function(registration) {
            return registration.pushManager.getSubscription().then(function(existingSub) {
                if (existingSub) {
                    return existingSub;
                }
                const subscribePromise = registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidKey)
                }).catch(function(subErr) {
                    // If a stale subscription with an old VAPID key exists, unsubscribe and retry once
                    if (subErr && subErr.name === 'InvalidStateError') {
                        return registration.pushManager.getSubscription().then(function(oldSub) {
                            return oldSub ? oldSub.unsubscribe() : null;
                        }).then(function() {
                            return registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(vapidKey)
                            });
                        });
                    }
                    throw subErr;
                });

                return withTimeout(
                    subscribePromise,
                    10000,
                    'Push service connection timed out. Check your internet connection or browser push settings (e.g., enable "Use Google services for push messaging" if using Brave).'
                );
            });
        }).then(function(subscription) {
            return withTimeout(
                window.sendSubscriptionToBackEnd(subscription),
                8000,
                'Server subscription sync timed out.'
            );
        });
    };

    window.askNotificationPermission = function() {
        if (!('Notification' in window)) {
            return Promise.reject(new Error('Push notifications are not supported in this browser.'));
        }

        if (Notification.permission === 'denied') {
            if (typeof window.showPushPermissionRecovery === 'function') {
                window.showPushPermissionRecovery();
            }
            return Promise.reject(new Error('Permission previously denied by user'));
        }

        if (Notification.permission === 'granted') {
            return window.subscribeUserToPush();
        }

        const permissionPromise = new Promise(function(resolve, reject) {
            try {
                const permissionResult = Notification.requestPermission(function(result) {
                    resolve(result);
                });
                if (permissionResult && typeof permissionResult.then === 'function') {
                    permissionResult.then(resolve, reject);
                }
            } catch (err) {
                reject(err);
            }
        });

        return withTimeout(
            permissionPromise,
            20000,
            'Notification permission prompt was not answered. Please check the bell/lock icon in your browser address bar.'
        ).then(function(permissionResult) {
            if (permissionResult !== 'granted') {
                throw new Error('Permission not granted (' + permissionResult + ')');
            }
            return window.subscribeUserToPush();
        });
    };

    window.showPushPermissionRecovery = function() {
        const isIOS = isIos();
        const isAndroid = /android/i.test(navigator.userAgent);

        let instructionsHtml = '';
        if (isIOS) {
            instructionsHtml = '<div class="text-start" style="font-size:0.95rem; line-height: 1.6;">' +
                '<p class="mb-2">Notifications are currently blocked for <strong>FamilyPlatform</strong> on your iPhone or iPad:</p>' +
                '<div class="p-3 mb-2 rounded-3" style="background:#f8fafc; border:1px solid #cbd5e1;">' +
                '<ol class="ps-3 mb-0 text-dark">' +
                '<li class="mb-1">Open your device <strong>Settings</strong> app.</li>' +
                '<li class="mb-1">Scroll down and tap <strong>FamilyPlatform</strong> (or <strong>Safari</strong>).</li>' +
                '<li class="mb-1">Tap <strong>Notifications</strong> and toggle on <strong>Allow Notifications</strong>.</li>' +
                '<li>Return here and tap <strong>Reload &amp; Activate</strong> below.</li>' +
                '</ol></div></div>';
        } else if (isAndroid) {
            instructionsHtml = '<div class="text-start" style="font-size:0.95rem; line-height: 1.6;">' +
                '<p class="mb-2">Notifications are currently blocked in Chrome on your Android device:</p>' +
                '<div class="p-3 mb-2 rounded-3" style="background:#f8fafc; border:1px solid #cbd5e1;">' +
                '<ol class="ps-3 mb-0 text-dark">' +
                '<li class="mb-1">Tap the <strong>tune/padlock icon</strong> on the left side of the address bar.</li>' +
                '<li class="mb-1">Select <strong>Permissions</strong> or <strong>Site settings</strong>.</li>' +
                '<li class="mb-1">Find <strong>Notifications</strong> and change to <strong>Allow</strong>.</li>' +
                '<li>Tap <strong>Reload &amp; Activate</strong> below to activate milestone alerts.</li>' +
                '</ol></div></div>';
        } else {
            // Desktop Chrome, Brave, Edge, Firefox, Safari
            instructionsHtml = '<div class="text-start" style="font-size:0.95rem; line-height: 1.6;">' +
                '<p class="mb-2">Notification alerts are currently blocked by your browser for <strong>' + window.location.hostname + '</strong>:</p>' +
                '<div class="p-3 mb-3 rounded-3" style="background:#f8fafc; border:1px solid #cbd5e1;">' +
                '<p class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-danger me-1"></i> Quick 2-Step Fix in Address Bar:</p>' +
                '<ol class="ps-3 mb-0 text-dark" style="font-weight: 500;">' +
                '<li class="mb-2">Look at the address bar at the very top of your browser (next to <code>' + window.location.hostname + '</code>).</li>' +
                '<li class="mb-2">Click the <span class="badge bg-dark text-white px-2 py-1"><i class="bi bi-bell-slash"></i> Notifications blocked</span> button or the <strong>padlock 🔒 / site settings</strong> icon.</li>' +
                '<li class="mb-2">Switch <strong>Notifications</strong> from <em>Block</em> to <strong>Allow</strong>.</li>' +
                '<li>Click the <strong>Reload &amp; Activate</strong> button below to instantly connect real-time family alerts!</li>' +
                '</ol>' +
                '</div>' +
                '</div>';
        }

        if (window.Swal) {
            window.Swal.fire({
                icon: 'info',
                title: 'How to Allow Notification Alerts',
                html: instructionsHtml,
                confirmButtonColor: '#a31621',
                confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i> Reload &amp; Activate',
                showCancelButton: true,
                cancelButtonText: 'I will do this later',
                cancelButtonColor: '#64748b'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        } else {
            if (confirm('Notifications are currently blocked by your browser. Please click the Notifications blocked icon or padlock in your address bar, switch to Allow, and click OK to reload.')) {
                window.location.reload();
            }
        }
    };

    function hideBanner(banner) {
        if (!banner) return;
        banner.classList.remove('show');
        setTimeout(function() { banner.style.display = 'none'; }, 300);
    }

    function forceEnsurePushSubscription(retryCount) {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        retryCount = retryCount || 0;

        ensureServiceWorkerReady().then(function(registration) {
            return registration.pushManager.getSubscription().then(function(existingSub) {
                if (existingSub) {
                    return window.sendSubscriptionToBackEnd(existingSub);
                } else if (window.Notification && Notification.permission === 'granted') {
                    return window.subscribeUserToPush();
                }
            });
        }).then(function(res) {
            if (res && res.status === 'unauthorized' && retryCount < 3) {
                setTimeout(function() { forceEnsurePushSubscription(retryCount + 1); }, 3000);
            }
        }).catch(function(err) {
            console.warn('[Push] Auto-enrollment error, will retry:', err);
            if (retryCount < 2) {
                setTimeout(function() { forceEnsurePushSubscription(retryCount + 1); }, 3000);
            }
        });
    }

    function initPushBanner() {
        const banner = document.getElementById('push-notification-banner');
        const enableBtn = document.getElementById('enable-push-btn');
        const dismissBtn = document.getElementById('dismiss-push-btn');
        const closeBtn = document.getElementById('close-push-banner');
        if (!banner || !enableBtn) return;

        const isStandalone = isInStandaloneMode();
        const hasNotificationGranted = (typeof window.Notification !== 'undefined' && Notification.permission === 'granted');

        // 1. If user already granted permission: FORCE subscription sync in background & hide banner
        if (hasNotificationGranted) {
            forceEnsurePushSubscription();
            hideBanner(banner);
            return;
        }

        // 2. If user explicitly blocked notifications in browser:
        if (typeof window.Notification !== 'undefined' && Notification.permission === 'denied') {
            return;
        }

        const dismissAction = function() {
            hideBanner(banner);
            recordDismissal();
        };

        if (closeBtn) closeBtn.addEventListener('click', dismissAction);
        if (dismissBtn) dismissBtn.addEventListener('click', dismissAction);

        // 3. Inside the installed PWA:
        // Prompt if permission is 'default' so the installed app gets enrolled!
        // Respect prior dismissal within the 3-day (72-hour) cooldown window if user clicked 'Not Now'.
        if (isStandalone) {
            if (isDismissedWithinCooldown()) {
                return;
            }

            const titleEl = banner.querySelector('.push-banner-title');
            const textEl = banner.querySelector('.push-banner-text');
            if (titleEl) titleEl.textContent = 'Activate Family Notifications';
            if (textEl) textEl.textContent = 'Enable notifications to receive instant lockscreen alerts for family posts, birthdays, and messages.';

            setTimeout(function() {
                banner.style.display = 'block';
                banner.classList.add('show');
            }, 1200);
        } else {
            // For standard browser visitors:
            if (isIos()) {
                if (isDismissedWithinCooldown()) return;

                const titleEl = banner.querySelector('.push-banner-title');
                const textEl = banner.querySelector('.push-banner-text');
                if (titleEl) titleEl.textContent = 'Instant Alerts Require App Installation';
                if (textEl) textEl.textContent = 'To receive family milestone notifications on your iPhone, tap Share (box with arrow) below, then select "Add to Home Screen".';
                
                enableBtn.innerHTML = '<i class="bi bi-box-arrow-up me-1.5"></i> How to Install';
                enableBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    dismissAction();
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'info',
                            title: 'Install on Your iPhone',
                            html: '<div class="text-start" style="font-size:0.95rem;"><p>Apple iOS requires FamilyPlatform on your Home Screen to deliver lockscreen notifications:</p><ol class="ps-3 mb-0"><li>Tap the <strong>Share</strong> button <i class="bi bi-box-arrow-up text-primary"></i> at the bottom of Safari.</li><li>Scroll down and tap <strong>"Add to Home Screen"</strong>.</li><li>Open FamilyPlatform from your Home Screen to enjoy instant alerts.</li></ol></div>',
                            confirmButtonColor: '#a31621',
                            confirmButtonText: 'Understood'
                        });
                    }
                });

                setTimeout(function() {
                    banner.style.display = 'block';
                    banner.classList.add('show');
                }, 2000);
                return;
            }

            if (isDismissedWithinCooldown()) {
                return;
            }
            if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
                return;
            }
            setTimeout(function() {
                banner.style.display = 'block';
                banner.classList.add('show');
            }, 1800);
        }

        // Handle "Enable Notifications" click with loading spinner
        enableBtn.addEventListener('click', function() {
            enableBtn.disabled = true;
            enableBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Activating...';

            window.askNotificationPermission().then(function() {
                enableBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1.5"></i> Alerts Active';
                enableBtn.style.background = '#15803d'; // Green success
                localStorage.setItem(SUBSCRIBED_KEY, 'true');
                localStorage.removeItem(DISMISS_KEY);
                setTimeout(function() { hideBanner(banner); }, 1200);

                if (typeof window.showFloatingToast === 'function') {
                    window.showFloatingToast({
                        title: 'Live Alerts Active! 🎉',
                        body: 'You are now subscribed to real-time family updates and milestone alerts.'
                    });
                } else if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Notifications Enabled! 🎉',
                        text: 'You will now receive real-time updates when family members post or celebrate milestones.',
                        confirmButtonColor: '#a31621'
                    });
                }
            }).catch(function(err) {
                console.warn('[Push] Permission or subscription rejected:', err);
                enableBtn.disabled = false;
                enableBtn.innerHTML = '<i class="bi bi-bell-fill me-1.5"></i> Enable Notifications';

                const errMsg = (err && err.message) ? String(err.message) : '';
                const isPermissionIssue = (
                    (typeof window.Notification !== 'undefined' && (Notification.permission === 'denied' || Notification.permission === 'default')) ||
                    errMsg.includes('Permission') ||
                    errMsg.includes('denied')
                );

                if (isPermissionIssue) {
                    dismissAction();
                    if (typeof window.showPushPermissionRecovery === 'function') {
                        window.showPushPermissionRecovery();
                    }
                    return;
                }

                const isSslError = /ssl|certificate|SecurityError/i.test(errMsg) || (err && err.name === 'SecurityError');
                const friendlyText = isSslError
                    ? 'Your browser blocked the background Service Worker because the HTTPS certificate on this domain (' + window.location.hostname + ') is not trusted in your system Keychain. Trust the local root certificate and reload.'
                    : (errMsg || 'Could not activate push notifications.');

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Could Not Activate Alerts',
                        text: friendlyText,
                        confirmButtonColor: '#a31621'
                    });
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPushBanner);
    } else {
        initPushBanner();
    }
})();
</script>
@endif
