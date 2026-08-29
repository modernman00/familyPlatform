/**
 * FamilyPlatform Push Notification Manager
 * - Manages PushManager subscription lifecycle
 * - Subscribes user using VAPID public key
 * - Encodes keys and syncs to /pushNotification/subscription
 * - Handles permission requests & deep links
 */

'use strict';

import axios from 'axios';

class PushManagerClient {
  constructor() {
    this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    this.vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content') || '';
  }

  /**
   * Convert URL-safe base64 string to Uint8Array for PushManager
   */
  static urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  /**
   * Request permission and subscribe to Web Push
   */
  async subscribe(vapidKey = null) {
    if (!this.isSupported) {
      console.warn('[PushManager] Web Push is not supported on this browser.');
      return false;
    }

    const key = vapidKey || this.vapidPublicKey;
    if (!key) {
      console.warn('[PushManager] No VAPID public key available.');
      return false;
    }

    try {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        console.log('[PushManager] Notification permission denied by user.');
        return false;
      }

      const reg = await navigator.serviceWorker.ready;
      let subscription = await reg.pushManager.getSubscription();

      if (!subscription) {
        const applicationServerKey = PushManagerClient.urlBase64ToUint8Array(key);
        subscription = await reg.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey
        });
      }

      // Send subscription to PHP backend
      await this.saveSubscription(subscription);
      console.log('[PushManager] Push subscription active and synced with server.');
      return true;
    } catch (err) {
      console.error('[PushManager] Error during push subscription:', err);
      return false;
    }
  }

  /**
   * Sync subscription object with PHP backend
   */
  async saveSubscription(subscription) {
    try {
      const subJSON = subscription.toJSON();
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const payload = {
        endpoint: subJSON.endpoint,
        keys: {
          p256dh: subJSON.keys?.p256dh,
          auth: subJSON.keys?.auth
        }
      };

      await axios.post('/pushNotification/subscription', payload, {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });
    } catch (err) {
      console.warn('[PushManager] Failed to sync subscription to backend:', err);
    }
  }

  /**
   * Unsubscribe from Web Push
   */
  async unsubscribe() {
    if (!this.isSupported) return false;

    try {
      const reg = await navigator.serviceWorker.ready;
      const subscription = await reg.pushManager.getSubscription();
      if (subscription) {
        await subscription.unsubscribe();
        console.log('[PushManager] Unsubscribed from Web Push successfully.');
        return true;
      }
      return false;
    } catch (err) {
      console.error('[PushManager] Error unsubscribing:', err);
      return false;
    }
  }
}

export default PushManagerClient;
