{{-- 
  PWA Service Worker Registration & Push Notification Self-Healing Partial: FamilyPlatform
  Registers the canonical root-scoped /sw.js worker and initializes continuous self-healing push subscription (ExecMind Gold Standard).
--}}
<script nonce="{{ $nonce ?? '' }}">
(function() {
  'use strict';

  // Converts VAPID public key (base64 URL-safe) to Uint8Array required by PushManager
  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  function getCsrfToken() {
    var fromCookie = (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1];
    if (fromCookie) return decodeURIComponent(fromCookie);
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  async function getVapidKey() {
    var metaKey = document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content');
    if (metaKey) return metaKey;
    try {
      var res = await fetch('/api/notifications/vapid-public-key');
      if (res.ok) {
        var json = await res.json();
        return json.publicKey || (json.message && json.message.publicKey) || '';
      }
    } catch (e) {
      console.warn('[PWA Push] Failed to fetch VAPID key from API:', e);
    }
    return "{{ $_ENV['VAPID_PUBLIC_KEY'] ?? $_SERVER['VAPID_PUBLIC_KEY'] ?? (getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['MIX_VAPID_PUBLIC_KEY'] ?? '')) }}";
  }

  // Global idempotent push subscription synchronizer (ExecMind Gold Standard)
  window.syncPushSubscription = async function() {
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
      return false;
    }
    if (Notification.permission !== 'granted') {
      return false;
    }
    try {
      var registration = await navigator.serviceWorker.ready;
      var subscription = await registration.pushManager.getSubscription();

      if (!subscription) {
        var publicKey = await getVapidKey();
        if (!publicKey) return false;

        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(publicKey)
        }).catch(async function(err) {
          // If previous key was invalid or mismatched, unsubscribe and retry once
          if (err && err.name === 'InvalidStateError') {
            var oldSub = await registration.pushManager.getSubscription();
            if (oldSub) await oldSub.unsubscribe();
            return await registration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array(publicKey)
            });
          }
          throw err;
        });
      }

      if (subscription) {
        var currentEndpoint = subscription.endpoint;
        var now = Date.now();
        var syncKey = 'fp_last_push_sync';
        try {
          var cachedSync = JSON.parse(localStorage.getItem(syncKey) || 'null');
          if (cachedSync && cachedSync.endpoint === currentEndpoint && (now - cachedSync.time) < 12 * 60 * 60 * 1000) {
            return true;
          }
        } catch (_) {}

        var subJson = subscription.toJSON ? subscription.toJSON() : subscription;
        var token = getCsrfToken();
        var userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '';
        var p256dh = subJson.keys ? subJson.keys.p256dh : '';
        var auth = subJson.keys ? subJson.keys.auth : '';

        var payload = {
          endpoint: currentEndpoint,
          id: userId,
          token: token,
          keys: {
            p256dh: p256dh,
            auth: auth
          }
        };

        var res = await fetch('/pushNotification/subscription', {
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
        });

        if (res.ok) {
          try {
            localStorage.setItem(syncKey, JSON.stringify({ endpoint: currentEndpoint, time: now }));
            localStorage.setItem('fp_push_subscribed', 'true');
          } catch (e) {}
          return true;
        }
      }
    } catch (e) {
      console.warn('[PWA Push] Sync error:', e);
    }
    return false;
  };

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then(function(reg) {
          if (reg.installing) {
            console.log('[PWA] Service Worker installing:', reg.scope);
          } else if (reg.waiting) {
            console.log('[PWA] Service Worker installed/waiting:', reg.scope);
          } else if (reg.active) {
            console.log('[PWA] Service Worker active:', reg.scope);
            try { reg.update(); } catch (_) {}
          }

          reg.addEventListener('updatefound', function() {
            var installingWorker = reg.installing;
            if (installingWorker) {
              installingWorker.addEventListener('statechange', function() {
                if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  console.log('[PWA] New version available.');
                  if (typeof window !== 'undefined' && window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('pwa:update-available', { detail: { worker: installingWorker } }));
                  }
                }
              });
            }
          });
        })
        .catch(function(err) {
          console.warn('[PWA] Service Worker registration failed:', err);
        });

      // Auto-Self-Healing: If permission was already granted, ensure this device's token is registered
      if ('Notification' in window && Notification.permission === 'granted') {
        setTimeout(function() {
          window.syncPushSubscription().catch(function() {});
        }, 1500);
      }
    });

    // Re-sync push subscription whenever tab regains visibility/focus (throttled to at most once per 60s)
    var lastVisibilitySync = 0;
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible' && 'Notification' in window && Notification.permission === 'granted') {
        var now = Date.now();
        if (now - lastVisibilitySync > 60000) {
          lastVisibilitySync = now;
          window.syncPushSubscription().catch(function() {});
        }
      }
    });
  }
})();
</script>
{!! function_exists('renderBiometricPasskeyPrompt') ? renderBiometricPasskeyPrompt($nonce ?? null) : '' !!}
