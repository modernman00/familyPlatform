# RFC-0008: Family Celebrations (birthdays, wedding anniversaries, private wishes)

- **Author(s):** Claude Code (agent), for Wally
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David (Architecture) / Gbenga Adebisi (DBRE) / Oladapo Salami & Marcus (SecOps) / Ayo Adeyemi (SRE)
- **Status:** IMPLEMENTED
- **Created Date:** 2026-10-25
- **Target Apps:** FamilyPlatform

---

## 1. Problem (the "why")

- Birthdays are collected at signup but nobody is ever notified. `memory_milestone_cron.php` says it covers birthdays and anniversaries; it only sends "memory flashback" pushes.
- Wedding anniversaries can never appear: the family tree only stores the marriage **year**, and nothing fills `family_unions.marriage_date`.
- There is no way to send wishes. The only action on the day is a "Then & Now" button.
- The "Upcoming Milestones" widget and Sunday digest show every member's age ("Turning 60", "50th Golden Jubilee") without asking.

**Decisions (Wally, 2026-10-25):** private wishes only (no feed post); age shown only if the person allows it; married members can give a wedding date and choose whether the family is told; notices go out the day before and on the day; the person celebrating also gets a private greeting from Family Platform.

**Non-goals:** custom special dates, opting out of birthday announcements, email for these notices (the existing Sunday digest already emails the week's milestones).

## 2. Design

**Who is told.** The person's families: `personal.famCode` plus approved `user_families` codes. Members come from `AllMembersData::AllMembersEmailByFamCode`, deduplicated, never including the person being celebrated. Only live accounts count: `account.status` not `erased`/`pending_deletion`. `deleted_at` is not used, because RFC-0006 found it auto-stamped on live rows.

**What they get (bell + real-time toast + push, via `NotificationOrchestrator`):**

| When | Birthday | Anniversary (only if a partner opted in) |
|---|---|---|
| Day before | "🎂 Wale's birthday is tomorrow" (or "Wale turns 60 tomorrow" if allowed) | "💞 Wale & Ade's wedding anniversary is tomorrow" |
| On the day | "🎂 It's Wale's birthday today! Send your wishes" | "💞 Wale & Ade have been married 25 years today! Send your wishes" |
| On the day, to the person | "🎉 Happy birthday Wale, from all of us at Family Platform" | "💞 Happy anniversary Wale & Ade, from all of us at Family Platform" |

29 February birthdays and weddings are celebrated on 28 February in non-leap years.

**Wishes.** The "Send wishes" button opens a small form. `POST /api/celebrations/wish` stores the wish privately for the celebrant (both partners with accounts, for an anniversary) and notifies them: "🎂 Ade sent you birthday wishes". The server re-derives the occasion from the database and accepts a wish only if:
- the sender shares a family with the celebrant;
- the sender is not the celebrant;
- the occasion is today or up to 2 days ago;
- the message is 1–500 characters.

It is CSRF-checked and rate-limited. One wish per sender per occasion; sending again replaces the message. `GET /api/celebrations` returns the caller's family occasions in that window (with "already wished") and the wishes the caller received in the last 14 days, which marks them as read.

**Idempotency.** `celebration_notice_log` with UNIQUE `(occasion_key, notice_kind, sent_on)`, claimed with `INSERT IGNORE` before sending, so a rerun sends nothing twice. Occasion keys: `birthday:{userId}:{year}`, `anniversary:{unionId}:{year}`.

**Settings (Account Settings).**
- Preferences tab: "Show my age on my birthday" (off by default).
- Marital Status tab, for married members: "Wedding date", plus "Let my family congratulate us each year" (on by default). The date is saved to the existing `family_unions.marriage_date` of the current union (`marriage_year` kept in sync).

**Existing widget and digest.** `MemoryMilestoneService::getUpcomingMilestones` now hides age-based text unless the person allows it, and only lists anniversaries a partner opted into.

**New tables** (created lazily with `CREATE TABLE IF NOT EXISTS`, like `rate_limiter`; no change to existing tables):
```sql
celebration_prefs (user_id VARCHAR(255) PK, show_age TINYINT(1) NOT NULL DEFAULT 0,
                   announce_anniversary TINYINT(1) NOT NULL DEFAULT 0, updated_at TIMESTAMP)
celebration_wishes (id PK, occasion_key VARCHAR(120), occasion_type VARCHAR(20), recipient_id VARCHAR(255),
                    sender_id VARCHAR(255), message VARCHAR(500), created_at, read_at NULL,
                    UNIQUE (occasion_key, sender_id, recipient_id), INDEX (recipient_id, created_at))
celebration_notice_log (id PK, occasion_key VARCHAR(120), notice_kind VARCHAR(10), sent_on DATE,
                        UNIQUE (occasion_key, notice_kind, sent_on))
```

**Running it.** New `cron/celebration_cron.php` (CLI, or HTTP with `CRON_SECRET`). Suggested crontab: `0 8 * * * php /path/to/cron/celebration_cron.php`.

## 3. Blast radius

shared-lib untouched. `SettingController` gains three optional fields and saves them through their own allowlisted path. The milestone widget and Sunday digest show less information (privacy), never more.

**As built:**
- The Settings show/hide for the wedding fields is an inline script in `accountSetting.blade.php`, and the profile widget and modal scripts are inline too, so the feature needs **no asset rebuild**.
- `CelebrationController::index()` calls `releaseSessionLock()` unqualified, so `tests/Support/shims.php` can make it a no-op under PHPUnit (the same pattern the repo uses for `showError()`).

## 4. Failure modes

- Notification failure for one occasion is logged and does not stop the others.
- If the claim cannot be written, that notice is skipped and retried by the next run (never a duplicate).
- The wishes API returns JSON errors (401, 403, 404, 422, 429), never stack traces; the widget hides itself if the API fails.

## 5. Security

| Threat | Defence |
|---|---|
| Wishing someone outside your families (IDOR) | 403 |
| Wishing yourself | 422 |
| Wishing for a non-existent or past occasion | 404 |
| Missing CSRF token | 401 |
| Flooding | `Limiter` per user |
| Script in a wish | Stored as plain text (`strip_tags`), rendered with `x-text` |

## 6. Tests

PHPUnit `CelebrationTest`, 29 cases (written failing first):
- day-before and on-the-day notices, sent to family only and never to the celebrant
- age hidden or shown by preference
- 29 February in a non-leap year
- anniversary only when opted in, with years married
- greeting to the celebrant
- idempotent reruns, and a chaos case (claim store fails, so nothing is sent)
- wish rules (IDOR 403, self 422, out-of-window 404, CSRF 401, length 422, replace)
- received wishes listed and marked read
- settings save the wedding date and preferences
- widget privacy filters

Cypress: `celebrations.cy.js`.
