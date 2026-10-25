# RFC-0007: Event Notification Integrity (create, remind, deliver once)

- **Author(s):** Claude Code (agent), for Wally
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David (Architecture) / Gbenga Adebisi (DBRE) / Oladapo Salami & Marcus (SecOps) / Ayo Adeyemi (SRE)
- **Status:** IMPLEMENTED
- **Created Date:** 2026-10-25
- **Target Apps:** FamilyPlatform

---

## 1. Problem Statement (the "why")

An audit of the event notification flow on 2026-10-25 found:

| # | Defect | Evidence |
|---|---|---|
| 1 | Scheduled reminders (7 days, 1 day, on the day) never fire on their own. `/sendEventReminder` is behind the login gate (`Event` is not a public controller), so a server cron gets a 302 to login; no `cron/` script calls it. | `RouteDispatch.php`, `router/Event.php` |
| 2 | `POST /member/notification/event` has no CSRF check, no rate limit and no length validation. Any member can push, toast and email arbitrary text to the whole family, repeatedly. | `Event::PostEventNotificationBar` |
| 3 | `GET /member/notification/event?notificationNo=N` loads **any** notification row by number (no ownership check) and broadcasts it to the caller's own Pusher channel: cross-family data leak. | `Event::GetEventNotificationBar` |
| 4 | Reminders are sent for soft-deleted events (`getAllEventData()` has no `deleted_at IS NULL`). | `AllMembersData::getAllEventData` |
| 5 | Each recipient sees two bell entries per event: a family-wide row (`receiver_id = famCode`) plus their own row. | `PostEventNotificationBar`, `NotificationController::sessionReceiverIds` |
| 6 | WebPush is sent twice per recipient: the shared-lib dispatcher sends it, then the app wrapper sends it again. | `services/NotificationOrchestrator::dispatch` |
| 7 | The event creator is notified about their own event (push, toast, email). | `filterMemberByFamCode` |
| 8 | The event save and the notification are two parallel requests, so a failed save still notifies the family about an event that does not exist. | `createEvent.js` |
| 9 | Reminders go to the creator's primary family (`personal.famCode`), not the event's family (`events.eventCode`); running the reminder twice a day sends duplicates. | `getAllEventData`, `sendReminder` |
| 10 | The family-channel `new-notification` broadcast has no listener (dead code). | `GetEventNotificationBar` |
| 11 | `GET /member/getEventData` returns every family's events for the next 7 days to any logged-in member. | `Event::getEventData` |

**Non-goals:** changing the email template, reminder copy, the shared-lib dispatcher (immutable per Mandate 5), or how recurring events roll their date forward.

## 2. Design (the "how")

**Creation (fixes 2, 3, 5, 7, 8, 10).** `submitEvent` becomes the single entry point. After `SubmitPostData` inserts the row (it already enforces CSRF, length rules and the per-person `Limiter`), the controller sends its JSON response, releases the connection when the SAPI supports it (`fastcgi_finish_request` / `litespeed_finish_request`), then calls `notifyFamilyOfNewEvent()`. That method re-reads the saved row (`events` by `no`) so only persisted, validated data is sent, and dispatches one notification per member **excluding the creator**, plus one bulk email. A notification failure is logged and never fails the save. No family-wide legacy row is written.

`PostEventNotificationBar` and `GetEventNotificationBar` stay routed for one release as **deprecated no-ops** (CSRF-checked POST; GET returns nothing sensitive), because a service-worker-cached old `createEvent.js` still calls both and a 404 on the awaited POST would show users an error for an event that saved. `createEvent.js` now makes one request.

**Reminders (fixes 1, 4, 9, 11).**
- New `cron/event_reminder_cron.php` (CLI, or HTTP with `CRON_SECRET`, same guard as the other cron scripts) calls `Event::runReminders()`, which returns counts. Suggested crontab: `5 8 * * * php /path/to/cron/event_reminder_cron.php`.
- `getAllEventData()` takes explicit dates computed in PHP (`eventDate IN (today, +1, +7)`), so PHP and MySQL time zones cannot disagree. It filters `events.deleted_at IS NULL`, selects `events.id` and `events.eventCode`, and the family used is `COALESCE(NULLIF(eventCode,''), personal.famCode)`.
- Idempotency: before sending, the job claims `(event_no, reminder_kind, sent_on)` with `INSERT IGNORE` into `event_reminder_log` (UNIQUE key). Only the run that inserts the row sends, so reruns and overlapping runs are race-safe.
- The login-gated GET routes `/sendEventReminder` and `/member/sendReminders` are removed (they never worked from cron, and let any member trigger a platform-wide send). `/member/getEventData` is scoped to the caller's families.

**Delivery (fix 6).** The app wrapper's second WebPush becomes a fallback: it runs only if the central dispatcher did not log a `web_push`/`sent` row for that notification id. The wrapper also accepts `MIX_VAPID_*` key names that the central path does not, so removing it outright could silently stop push if production only sets those names.

**Schema (DDL), created lazily with `CREATE TABLE IF NOT EXISTS` (the pattern `rate_limiter` and `LinkPreviewService` use; no lock on existing tables):**
```sql
CREATE TABLE IF NOT EXISTS event_reminder_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_no INT NOT NULL,
  reminder_kind VARCHAR(10) NOT NULL,      -- 'week' | 'day' | 'today'
  sent_on DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_reminder (event_no, reminder_kind, sent_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 3. Blast radius

- shared-lib: untouched.
- `services/NotificationOrchestrator` has 16 callers; the only change is skipping a duplicate push when the central one is logged as sent. If the log table is missing or the lookup fails, behaviour is exactly as today.
- Old cached JS keeps working (deprecated endpoints answer 200).

**Deployment steps:**
1. Rebuild assets with your usual toolchain (`npm run production`). The committed `public/js/profilePage.js` still contains the old two-request code. It stays safe until rebuilt (the deprecated POST returns `message: null`, so the old client skips the GET), at the cost of one wasted request per event. A clean `npm ci` from the current lockfile cannot build: laravel-mix 6.0.49 requires `webpack/lib/SizeFormatHelpers`, which webpack 5.112.2 no longer ships.
2. Add the crontab line: `5 8 * * * php /path/to/familyPlatform/cron/event_reminder_cron.php`.
3. No migration step: `event_reminder_log` is created on the first run.

## 4. Failure modes

- DB down while claiming a reminder: that event is skipped and logged; the next run retries (the claim is not written).
- SMTP or WebPush down: logged, the event is still saved, the in-app row is still written.
- The cron runs twice: the second run claims nothing and sends nothing.

## 5. Security

- Attack 2 (`curl -X POST /member/notification/event -d eventName=spam` without a token) → 401 (`CheckToken`), and the endpoint no longer sends anything.
- Attack 3 (`curl '/member/notification/event?notificationNo=1'`) → no row read, no broadcast.
- Attack 11 (`curl /member/getEventData`) → only the caller's families' events.

Each is covered by a PHPUnit regression test in `tests/Feature/EventNotificationFlowTest.php`.

## 6. Tests

PHPUnit feature tests (written failing first, then made to pass): creator excluded; no family-wide row; deprecated endpoints send nothing; CSRF on the deprecated POST; deleted events not reminded; reminders use `eventCode`; reminders are idempotent; chaos test (claim fails because the DB is unavailable → no send, no crash); `getEventData` scoped; push fallback skipped when the central push was logged as sent.
