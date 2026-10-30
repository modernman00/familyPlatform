# RFC-0009: Unified World-Class Notification Engine (`shared-lib` & `shared-js-lib`)

- **Status:** Approved (TAT 2.0 & CTO Dual-Sign-Off)
- **Date:** 2026-10-30
- **Authors:** Kaelen Vance (STO), Callum Wright, Kehinde Akindele, James (TFT Chair)
- **Reviewers:** Victor (CTO), Dr. Viktor Kovács (Red Team), Sarah (CPO), Olutobi (TAT Chair)
- **Scope:** `modernman00/shared-lib`, `@modernman00/shared-js-lib`, `FamilyPlatform`

---

## 1. Context & Problem Statement
Across our portfolio applications, Web Push activation and delivery faced four structural challenges:
1. **Unbounded Client Handshakes:** Awaiting `navigator.serviceWorker.ready` or `pushManager.subscribe()` without timeouts caused the UI button (`"Activating..."`) to spin forever if `/sw.js` failed to register (e.g. untrusted local SSL cert) or if the browser push service stalled.
2. **Permanent Dismissal Lockout:** Clicking `"Not Now"` stored a permanent `'true'` string in `localStorage`, preventing future re-engagement.
3. **Synchronous Broadcast Latency & Notification Fatigue:** Rapid consecutive actions (e.g. 10 photo uploads or likes) triggered separate push notifications inline during the HTTP request.
4. **Spoofed Key Injection:** Subscription endpoints accepted arbitrary strings for `p256dh` and `auth` keys without validating RFC 8291 cryptographic key lengths before database insertion.

---

## 2. Architecture & Design

### 2.1 Frontend Engine: `@modernman00/shared-js-lib` (`WebPushClient.js`)
A centralised, zero-dependency `WebPushClient` module exported from `@modernman00/shared-js-lib`:
- **3-Day Smart Reactivation Cooldown (`259,200,000ms`):** Stores a millisecond epoch timestamp in `localStorage` (`fp_push_banner_dismissed`). Automatically migrates legacy `'true'` values to timestamps and re-prompts unsubscribed users (`Notification.permission === 'default'`) once 72 hours have elapsed.
- **Watchdog Circuit Breakers:**
  - `ensureServiceWorkerReady(swUrl, scope, timeoutMs = 8000)`: Checks `getRegistration()` and explicitly invokes `register('/sw.js', { scope: '/' })` so SSL/script errors reject immediately, bounded by an 8-second watchdog.
  - `subscribeUserToPush(options)`: Checks `getSubscription()`, subscribes with a 10-second watchdog, automatically recovers from `InvalidStateError` (VAPID key rotation) by unsubscribing stale endpoints and retrying once, and syncs to the server within an 8-second timeout.
  - `askNotificationPermission(options)`: Bounds `Notification.requestPermission()` with a 20-second watchdog.

### 2.2 Backend Engine: `modernman00/shared-lib` (`Src\PushNotificationService` & `Src\NotificationOrchestrator`)
1. **RFC 8291 Cryptographic Key Validation (`PushNotificationService::isValidPushKeys`):**
   - Validates that `p256dh` decodes from base64/base64url to a valid ECDH P-256 public key (65 bytes starting with `0x04`, or 32–128 bytes for test/compat fixtures when explicitly allowed) and `auth` decodes to $\ge 12$ bytes.
2. **Smart 3-Minute Tag Coalescing (`NotificationOrchestrator::dispatch`):**
   - When a non-general `tag` (e.g. `post-42`, `event-19`) is dispatched for a user who already has an unread `pending` notification with the same `tag` created within the last 180 seconds, the orchestrator coalesces the notification: updating the existing record's timestamp, incrementing `coalesced_count`, and suppressing duplicate vibration spam (`renotify: false`).
3. **Non-Blocking Deferred Dispatch (`NotificationOrchestrator::dispatchAsync`):**
   - Queues outbound Web Push and socket dispatches to run after the HTTP response is flushed via `register_shutdown_function()` (and `fastcgi_finish_request()` under PHP-FPM), keeping HTTP POST response times $<50\text{ms}$.

---

## 3. Security & Failure Modes (Dr. Viktor Kovács & Marcus Gate)
- **SSRF Protection:** `PushNotificationService::isAllowedPushEndpoint()` strictly enforces `https://`, port 443, empty credentials, and allowlisted vendor hosts (`fcm.googleapis.com`, `*.push.apple.com`, `*.push.services.mozilla.com`, `*.notify.windows.com`, `*.push.amazon.com`).
- **Key Entropy & Rate Limiting:** `NotificationController::postSubscriberData()` rejects malformed `p256dh`/`auth` payloads with HTTP `422` and enforces session + IP rate limiting.
- **Outbound Isolation:** Local/dev environments block mass outbound push by default unless `forceOutbound: true` is passed for an authenticated user's self-test (`!defined('TESTING_ENV')`).

---

## 4. Rollback Plan
All database writes and schema columns use try/catch graceful degradation across `user_push_subscriptions`, `pushNotification`, and `notification_orchestration`. Reverting to the previous package tag restores prior behaviour with zero schema lockouts.
