/**
 * Web Push — the single client module (PUSH-1 / PUSH-4).
 *
 * Side effect on import: registers the service worker and, if the browser
 * permission is already `granted`, re-syncs the subscription so the server's
 * record stays fresh.
 *
 * Exports for a Settings toggle (must be called from a real user gesture):
 *   enablePushNotifications()  -> Promise<{ok, reason?}>
 *   disablePushNotifications() -> Promise<{ok}>
 *   getPushState()             -> 'unsupported' | 'default' | 'granted' | 'denied'
 *   isPushSubscribed()         -> Promise<boolean>
 */
import axios from 'axios';
import { getCsrfToken } from '../global';

const VAPID_PUBLIC_KEY = process.env.MIX_VAPID_PUBLIC_KEY || '';
const PUSH_PROMPT_DISMISSED_KEY = 'push_prompt_dismissed';

const pushSupported = () =>
  'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const raw = window.atob(base64);
  const out = new Uint8Array(raw.length);
  for (let i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
  return out;
}

function keyToBase64(subscription, name) {
  return btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey(name))));
}

async function syncToServer(subscription) {
  const payload = {
    endpoint: subscription.endpoint,
    keys: {
      p256dh: keyToBase64(subscription, 'p256dh'),
      auth: keyToBase64(subscription, 'auth'),
    },
  };
  await axios.post('/pushNotification/subscription', payload, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-XSRF-TOKEN': getCsrfToken(),
          'X-CSRF-TOKEN': getCsrfToken(),
    },
  });
}

// ---- import-time bootstrap: keep an existing grant in sync -----------------
if (pushSupported()) {
  navigator.serviceWorker
    .register('/service-worker.js')
    .then((swReg) => swReg.pushManager.getSubscription().then((sub) => {
      if (sub) return syncToServer(sub).catch((e) => console.warn('[push] resync failed', e));
      if (Notification.permission === 'granted' && VAPID_PUBLIC_KEY) {
        return doSubscribe(swReg);
      }
    }))
    .catch((err) => console.warn('[push] SW registration failed', err));
}

async function doSubscribe(swReg) {
  const sub = await swReg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
  });
  await syncToServer(sub);
  return sub;
}

function showPushPrompt() {
  if (!VAPID_PUBLIC_KEY || Notification.permission !== 'default') return;
  if (localStorage.getItem(PUSH_PROMPT_DISMISSED_KEY)) return;
  if (document.getElementById('push-permission-banner')) return;

  const banner = document.createElement('div');
  banner.id = 'push-permission-banner';
  banner.innerHTML = `
    <div class="pwa-banner-card">
      <div class="pwa-banner-header">
        <div class="pwa-banner-text">
          <h6>Stay connected with your family</h6>
          <p>Allow notifications for new friend requests, events, and family updates.</p>
        </div>
        <button type="button" id="push-prompt-close" class="pwa-btn-close" aria-label="Dismiss">&times;</button>
      </div>
      <div class="pwa-banner-actions">
        <button type="button" id="push-prompt-enable" class="pwa-btn pwa-btn-primary">Allow notifications</button>
      </div>
    </div>
  `;

  document.body.appendChild(banner);
  setTimeout(() => banner.classList.add('show'), 100);

  const dismiss = () => {
    localStorage.setItem(PUSH_PROMPT_DISMISSED_KEY, String(Date.now()));
    banner.remove();
  };

  document.getElementById('push-prompt-close')?.addEventListener('click', dismiss);
  document.getElementById('push-prompt-enable')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'Enabling...';
    const result = await enablePushNotifications();
    if (result.ok) {
      banner.remove();
      return;
    }
    button.disabled = false;
    button.textContent = 'Allow notifications';
    if (result.reason === 'denied') dismiss();
  });
}

if (pushSupported()) {
  window.addEventListener('load', showPushPrompt, { once: true });
}

// ---- public API ----------------------------------------------------------
export function getPushState() {
  if (!pushSupported()) return 'unsupported';
  return Notification.permission; // 'default' | 'granted' | 'denied'
}

export async function isPushSubscribed() {
  if (!pushSupported()) return false;
  try {
    const swReg = await navigator.serviceWorker.ready;
    return (await swReg.pushManager.getSubscription()) !== null;
  } catch {
    return false;
  }
}

export async function enablePushNotifications() {
  if (!pushSupported()) return { ok: false, reason: 'unsupported' };
  if (!VAPID_PUBLIC_KEY) return { ok: false, reason: 'misconfigured' };

  let permission = Notification.permission;
  if (permission === 'default') permission = await Notification.requestPermission();
  if (permission !== 'granted') return { ok: false, reason: permission }; // 'denied'

  try {
    const swReg = await navigator.serviceWorker.ready;
    const existing = await swReg.pushManager.getSubscription();
    if (existing) {
      await syncToServer(existing);
    } else {
      await doSubscribe(swReg);
    }
    return { ok: true };
  } catch (err) {
    console.error('[push] enable failed', err);
    return { ok: false, reason: 'error' };
  }
}

export async function disablePushNotifications() {
  if (!pushSupported()) return { ok: true };
  try {
    const swReg = await navigator.serviceWorker.ready;
    const sub = await swReg.pushManager.getSubscription();
    if (sub) {
      const endpoint = sub.endpoint;
      await sub.unsubscribe();
      await axios.post('/pushNotification/unsubscribe', { endpoint }, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': getCsrfToken(),
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      }).catch((e) => console.warn('[push] server unsubscribe failed', e));
    }
    return { ok: true };
  } catch (err) {
    console.error('[push] disable failed', err);
    return { ok: false };
  }
}
