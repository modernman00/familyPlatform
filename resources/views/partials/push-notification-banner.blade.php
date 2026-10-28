{{-- 
  FamilyPlatform Push Notification & Standalone PWA Activation Legend
  Prompts users to activate push notifications and handles auto-sync for users who installed the app.
--}}
<div id="push-notification-banner" class="alert alert-dismissible fade shadow-lg mx-auto" role="alert" style="display: none; max-width: 620px; position: fixed; top: 20px; left: 16px; right: 16px; z-index: 1060; border-radius: 16px; border: 1.5px solid rgba(163, 22, 33, 0.25); background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);">
    <div class="d-flex align-items-start gap-3">
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px; background: rgba(163, 22, 33, 0.1); color: #a31621;">
            <i class="bi bi-bell-fill fs-4" style="line-height: 1;"></i>
        </div>
        <div class="flex-grow-1 pe-2">
            <h6 class="push-banner-title fw-bold mb-1" style="color: #0f172a; font-size: 0.98rem; letter-spacing: -0.01em;">Stay Connected with Your Family</h6>
            <p class="push-banner-text mb-0 text-muted" style="font-size: 0.86rem; line-height: 1.45;">
                Enable push notifications to receive instant alerts when family members post updates, celebrate milestones, or share memories.
            </p>
            <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                <button id="enable-push-btn" type="button" class="btn btn-sm text-white rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center" style="background: #a31621; border: none; min-height: 44px; transition: transform 0.15s ease, background 0.2s ease;">
                    <i class="bi bi-bell-fill me-1.5" style="font-size: 0.85rem;"></i>
                    <span class="btn-text">Enable Notifications</span>
                </button>
                <button type="button" id="dismiss-push-btn" class="btn btn-sm btn-light border rounded-pill px-3 fw-medium text-secondary" style="min-height: 44px;">
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

    window.subscribeUserToPush = function() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return Promise.reject(new Error('Push not supported'));
        }

        const vapidKey = getVapidPublicKey();
        if (!vapidKey) {
            console.warn('[Push] VAPID key not configured in environment');
            return Promise.reject(new Error('VAPID key missing'));
        }

        return navigator.serviceWorker.ready.then(function(registration) {
            return registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey)
            });
        }).then(function(subscription) {
            return window.sendSubscriptionToBackEnd(subscription);
        });
    };

    window.askNotificationPermission = function() {
        return new Promise(function(resolve, reject) {
            const permissionResult = Notification.requestPermission(function(result) {
                resolve(result);
            });
            if (permissionResult) {
                permissionResult.then(resolve, reject);
            }
        }).then(function(permissionResult) {
            if (permissionResult !== 'granted') {
                throw new Error('Permission not granted');
            }
            return window.subscribeUserToPush();
        });
    };

    function hideBanner(banner) {
        if (!banner) return;
        banner.classList.remove('show');
        setTimeout(function() { banner.style.display = 'none'; }, 300);
    }

    function forceEnsurePushSubscription(retryCount) {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        retryCount = retryCount || 0;

        navigator.serviceWorker.ready.then(function(registration) {
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
            if (retryCount < 3) {
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
            localStorage.setItem(DISMISS_KEY, 'true');
        };

        if (closeBtn) closeBtn.addEventListener('click', dismissAction);
        if (dismissBtn) dismissBtn.addEventListener('click', dismissAction);

        // 3. Inside the installed PWA:
        // FORCE the prompt if permission is 'default' so the installed app gets enrolled!
        if (isStandalone) {
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
                if (localStorage.getItem(DISMISS_KEY) === 'true') return;

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

            if (localStorage.getItem(DISMISS_KEY) === 'true') {
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
                if (Notification.permission === 'denied') {
                    dismissAction();
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
