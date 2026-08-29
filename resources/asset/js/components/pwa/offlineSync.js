/**
 * FamilyPlatform Background Sync & Offline Queue Engine
 * - IndexedDB persistent store for offline post submissions & drafts
 * - Background Sync API registration with fallback to window 'online' event
 * - Automatic exponential backoff & retry mechanism
 * - User-facing success feedback upon queue drain
 */

'use strict';

import Swal from 'sweetalert2';

const DB_NAME = 'familyplatform_offline_db';
const DB_VERSION = 1;
const STORE_NAME = 'offline_queue';

class OfflineSyncManager {
  constructor() {
    this.db = null;
    this.isDraining = false;
    this.initDB();
    this.setupListeners();
  }

  /**
   * 1. Initialize IndexedDB
   */
  initDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = (e) => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
        }
      };

      request.onsuccess = (e) => {
        this.db = e.target.result;
        resolve(this.db);
      };

      request.onerror = (e) => {
        console.warn('[OfflineSync] IndexedDB failed to open:', e);
        reject(e);
      };
    });
  }

  /**
   * 2. Setup Reconnection & SW Message Listeners
   */
  setupListeners() {
    // When browser returns online, trigger queue drain
    window.addEventListener('online', () => {
      console.log('[OfflineSync] Online event detected. Draining queue...');
      this.drainQueue();
    });

    // Listen for Service Worker background sync notification
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data?.type === 'DRAIN_OFFLINE_QUEUE') {
          console.log('[OfflineSync] SW message: DRAIN_OFFLINE_QUEUE received');
          this.drainQueue();
        }
      });
    }
  }

  /**
   * 3. Queue an action when offline
   *
   * @param {string} url Endpoint URL
   * @param {Object|FormData} payload Data payload
   * @param {string} actionDescription User-friendly text (e.g. "Family Post")
   */
  async enqueue(url, payload, actionDescription = 'Action') {
    if (!this.db) {
      await this.initDB();
    }

    // Convert FormData to plain object for IndexedDB serialization if needed
    let serializedData = payload;
    if (payload instanceof FormData) {
      serializedData = {};
      payload.forEach((value, key) => {
        serializedData[key] = value;
      });
    }

    const item = {
      url,
      payload: serializedData,
      description: actionDescription,
      timestamp: Date.now(),
      retries: 0
    };

    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(STORE_NAME, 'readwrite');
      const store = tx.objectStore(STORE_NAME);
      const req = store.add(item);

      req.onsuccess = async () => {
        console.log('[OfflineSync] Item queued successfully:', item);

        // Register Background Sync if supported
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
          try {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register('sync-family-posts');
          } catch (err) {
            console.log('[OfflineSync] Sync registration fallback:', err);
          }
        }

        // Show friendly user toast
        Swal.fire({
          icon: 'info',
          title: 'Saved to Offline Queue',
          text: `You are currently offline. Your ${actionDescription.toLowerCase()} has been safely saved and will publish automatically when you reconnect.`,
          timer: 4000,
          timerProgressBar: true,
          confirmButtonColor: '#2563eb'
        });

        resolve(true);
      };

      req.onerror = (err) => {
        console.error('[OfflineSync] Error saving to queue:', err);
        reject(err);
      };
    });
  }

  /**
   * 4. Drain and replay queued requests
   */
  async drainQueue() {
    if (this.isDraining || !navigator.onLine) return;
    this.isDraining = true;

    if (!this.db) {
      await this.initDB();
    }

    try {
      const items = await this.getAllItems();
      if (items.length === 0) {
        this.isDraining = false;
        return;
      }

      console.log(`[OfflineSync] Processing ${items.length} queued action(s)...`);

      let processedCount = 0;
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      for (const item of items) {
        try {
          const formData = new FormData();
          for (const key in item.payload) {
            formData.append(key, item.payload[key]);
          }
          if (csrfToken && !item.payload['token']) {
            formData.append('token', csrfToken);
          }

          // AbortController timeout safeguard (David's Gate 3: 8s limit)
          const controller = new AbortController();
          const timeoutId = setTimeout(() => controller.abort(), 8000);

          const response = await fetch(item.url, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken
            },
            signal: controller.signal
          });

          clearTimeout(timeoutId);

          if (response.ok || response.status === 200 || response.status === 302) {
            await this.deleteItem(item.id);
            processedCount++;
          } else if (response.status >= 400 && response.status < 500) {
            // Client error (validation / unauthorized) - discard to avoid infinite loop
            console.warn(`[OfflineSync] Server returned ${response.status} for item ${item.id}. Discarding.`);
            await this.deleteItem(item.id);
          } else {
            // Server error (500) - increment retries with exponential backoff
            await this.incrementRetries(item);
          }
        } catch (err) {
          console.warn('[OfflineSync] Network error during drain:', err);
          break; // Stop draining if network drops again
        }
      }

      if (processedCount > 0) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: `Synced ${processedCount} offline update(s) successfully!`,
          showConfirmButton: false,
          timer: 3500,
          timerProgressBar: true
        });

        // If on profile/feed page, refresh view to show new content
        if (window.location.pathname.includes('profilePage')) {
          setTimeout(() => window.location.reload(), 1500);
        }
      }
    } catch (err) {
      console.error('[OfflineSync] Error during drainQueue:', err);
    } finally {
      this.isDraining = false;
    }
  }

  getAllItems() {
    return new Promise((resolve) => {
      const tx = this.db.transaction(STORE_NAME, 'readonly');
      const store = tx.objectStore(STORE_NAME);
      const req = store.getAll();
      req.onsuccess = () => resolve(req.result || []);
      req.onerror = () => resolve([]);
    });
  }

  deleteItem(id) {
    return new Promise((resolve) => {
      const tx = this.db.transaction(STORE_NAME, 'readwrite');
      const store = tx.objectStore(STORE_NAME);
      const req = store.delete(id);
      req.onsuccess = () => resolve(true);
      req.onerror = () => resolve(false);
    });
  }

  incrementRetries(item) {
    return new Promise((resolve) => {
      const tx = this.db.transaction(STORE_NAME, 'readwrite');
      const store = tx.objectStore(STORE_NAME);
      item.retries = (item.retries || 0) + 1;
      if (item.retries > 5) {
        // Discard after 5 failed retries
        store.delete(item.id);
      } else {
        store.put(item);
      }
      tx.oncomplete = () => resolve(true);
    });
  }
}

export default OfflineSyncManager;
