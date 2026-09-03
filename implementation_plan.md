# Implementation Plan — Readiness Review actions

**Source:** https://claude.ai/code/artifact/a5cb9525-4241-4433-92a3-b121f155536e
**Coordinator:** Jumoke · **Tier:** 2 (program) · **Started:** 2026-09-02

Legend: ✅ done + verified · ~ next up · ⛔ needs human/legal · ⏸ staged (risk)

---

## Security

| ID | Action | Status | Notes |
|----|--------|--------|-------|
| SEC-1 | Family-ownership check on notification read/dismiss | ✅ | `NotificationController::notificationById` + `notificationRead` now derive the receiver allow-list from the session (`$_SESSION['id']` + `famCodes`), URL segments ignored for auth. Verified: swapping another family's id/code returns only the caller's own feed, no victim rows. |
| SEC-1b | `/getEmails` leaked **every** member email, unauthenticated | ✅ | **Live PII breach found during execution.** Rewritten to a session-gated single `?email=` existence check returning `{exists:bool}`. Client (`accountSetting.js`, `kidsAndSiblings.js`, `mobileNameCheck.js`, `api/index.js`) rewired to per-email lookup with a local cache. |
| SEC-1c | `/checking` — unauthenticated `while(true)` SSE loop (worker-pinning DoS) | ✅ | Route + method removed. Superseded by Pusher. |
| SEC-3 | Complete security headers | ✅ | `.htaccess`: `X-XSS-Protection` → `0` (was deprecated `1; mode=block`), added `Permissions-Policy`, HSTS → `includeSubDomains; preload`, re-asserted `Referrer-Policy`/`X-Frame-Options`/`nosniff` on static assets. Verified on `/login` + static CSS. |
| SEC-4 | Test / schema routes gated for prod | ✅ | `router.php`: `dbTable.php` (`/createTable`, `/alterTable`, …) and `/clearcache` now only routed when `APP_ENV ∈ {local,development,testing}`. `/tests/clear-rate-limit` already env-gated. |
| SEC-2 | Flip CSP from Report-Only → enforced | ◑ audit done | **`innerHTML` sink audit complete.** Added `esc()` helper (`components/global.js`) and applied it to every user-controlled interpolation in: `familyTree/index.js` (search), `familyTree/showModal.js` (person modal — name/bio/occupation/spouse), `reels/reelsPlayer.js` (reel card + comments), `allMembers/html.js` (member directory cards), `allMembers/handleInput.js` (search query), plus the profile-feed legacy path (`profilePage/html.js`, `comment.js`, `nameImageTiming.js`, `eventHTML.js`, `engagementHtml.js`, `showPostImages.js`). Deleted dead `profilePage/newPage.js` + `components/alert.blade.php`. The **primary Alpine feed already uses `x-text`** (safe) — verified by a new cypress stored-XSS test in `social_feed.cy.js` (payload renders as text, `onerror` never fires). `blog/show.blade.php:121` stays latent (no `blogs` table / writer). **Remaining to actually flip CSP:** a canary route enforced first, and the `report_only` default lives in `vendor/shared-lib` — flip via the `init.php` `header_register_callback` rewriting `-Report-Only` → enforced. |
| SEC-5 | Rate-limit sweep on unauth/session POSTs | ◑ partial | Done: resend-code, push-subscribe (s1), `/register/checkContact` (s3, 30/5min/IP → 429), and `ProfilePage::postComment` (s5, 30/5min/user via the 'post' profile). Remaining for Marcus: OAuth callbacks (lower risk — need a valid provider `code`), `addReaction` (has the `reacted_at` bug — touch with care), invite-token guessing. |
| SEC-6 | Pusher channels were all **public** — cross-family realtime leak | ✅ (s9) | Every feed/event channel (`posts-channel`, `comments-channel`, `likes-channel`, `notification-channel`, `events-channel`) was a public Pusher channel — anyone with the (public, bundled) app key could `subscribe()` from a console and read **every family's** posts/comments/likes/events in real time. Migrated to per-family `private-family-<code>` + per-user `private-user-<id>` with a server-authorised `POST /pusher/auth` (`App\classes\Pusher::authoriseChannel` — signs only channels the session's `famCode`/`famCodes`/`user_families` prove membership of). Server broadcasts (`PostMessage` ×6, `PostLikeController`, `Event` ×3) now scope by the post's/event's `postFamCode`/`eventCode`. Client (`feedComponent.js`, `sidebarComponents.js`) subscribes the private channel with a CSRF-header auth callback; dead `loadPost.js` deleted. Cypress: own-family channel → 200+auth, other family → 403, own vs other user channel → 200/403. **`FamilyRequestController` still broadcasts friend-request PII to public `friend-request-channel`** (no live consumer, file owned by kin-request squad) — flagged for them to switch to `private-user-<approverId>`. |
| SEC-7 | Invite-token signing secret was a **hard-coded string** | ✅ (s9) | `FamilyClaimService` signed/verified invite tokens with `$_ENV['APP_KEY'] ?? 'familyPlatformSecurityTokenSecretKey2026'` — and `APP_KEY` was never set, so the fallback (readable in source) was the live secret → anyone could forge an invite into any family. Now `inviteSigningSecret()` derives an HMAC from `APP_KEY`/`JWT_KEY` with domain separation and **throws** if no real key is set; `verify` fails closed. Added a real `APP_KEY` to `.env`. Round-trip verified. **Invalidates any invite links already issued** — re-issue. |
| SEC-8 | IDOR sweep — cross-family / cross-user access | ✅ (s9) | Fixed: `AllMembersController::getProfile` (any member's profile+relatives+DOB by id → now `BaseController::sessionCanViewMember`: own family OR approved connection, mirrors directory-search rule); `removeProfile` (deleted arbitrary approver/requester rows, no CSRF → now `CheckToken` + caller must be a party); `Organogram::index`/`familyStudio`/`getGraphData` (any family's 6-gen tree by id → `sessionSharesFamily`); `Organogram::getNodeDetails` (numeric-id branch dumped any `family_nodes` row incl. contact + unions → family-scoped); `EngagementController::react`/`vote` (react/vote on any post/poll by id, no CSRF → `CheckToken` + post-family check, `engagement.js` fetch sends the token). Checked-clean: `PostMessage` post/comment edit-delete (ownership), `Event` update/delete (ownership), `OrganogramEditorController` (all `WHERE family_code = session`), `ReelsController::deleteReel` (`Reel::deleteReel($id,$userId)`), notifications (SEC-1). New `cypress/e2e/idor_sweep.cy.js` — 4/4. |
| SEC-* | Full authenticated red-team engagement | ⛔ | Owner: **Marcus**, independent, no dev sign-off. Scope in the report. Remaining live-attack items: `friend-request-channel` PII (above), full `/member/*` sweep beyond what's listed, Pusher presence/webhook config, `/getSingleMemberData` (a model method wired as a route — errors on missing args, likely dead). |

## Push notifications

| ID | Action | Status | Notes |
|----|--------|--------|-------|
| PUSH-2 | `pushNotification.endpoint` → `TEXT` | ✅ | Backup `storage/backups/pushNotification_2026-09-02.json` (49 rows, longest endpoint 186 chars so nothing was truncated *yet*). `ALTER … MODIFY endpoint TEXT NOT NULL` + `INDEX idx_push_endpoint (endpoint(191))` applied. Recorded in `database_deltas.sql` with rollback. |
| PUSH-1 | Push opt-in toggle in Settings → wire `enablePushNotifications()` | ✅ | "Browser & Mobile Notifications" switch added to the Preferences tab; `accountSetting.js` calls `enablePushNotifications()` / `disablePushNotifications()` in the change handler, reflects granted / denied / unsupported (with iPhone "add to Home Screen" hint). New `POST /pushNotification/unsubscribe` → `NotificationController::deleteSubscriberData` (session-scoped). Cypress `events_and_profiles` 3/3, `social_feed` 5/5, `reels` 7/7. Isla still to eyeball the toggle copy/states in a browser. |
| PUSH-4 | Consolidate push clients + dead code | ✅ | `pwa/pushManager.js` deleted; `pwaManager.js` no longer references it; `registerPushNotification.js` rewritten as the single module (`enable/disablePushNotifications`, `getPushState`, `isPushSubscribed`, import-time resync, CSRF header). Typo route + `VapidClass::index()` already gone in session 1. |
| PUSH-5 / GDPR-5 | Make email/SMS preference toggles actually gate sends | ⛔ **decision needed** | Not a clean code fix. `contact.email_notifications` / `sms_notifications` **default to `'off'`** and the toggle has never been read, so ~every user's stored value is `'off'` by default, not by choice. Enforcing "send only if `'on'`" would silently stop all activity email for the whole base. Needs: (1) product/legal call — are family-activity emails opt-in or opt-out? (Noah / Chloe / Olutobi); (2) if opt-out: flip the column default + settings UI to checked, backfill existing rows to `'on'` (Tier 2 data change, TAT); (3) then filter recipients in `Event::sendBulkNotification` + friend-request sends. Distinguish transactional mail (approval, 2FA, password reset — never suppressed) from activity mail. |
| PUSH-3 | iOS install → enable-alerts flow | ✅ | The Settings push toggle now detects iPhone-not-installed (`window.pwaManager.isIOS && !isStandalone`) and shows a "Show me how" link that opens the existing Add-to-Home-Screen walkthrough. Once installed on iOS 16.4+, `PushManager` becomes available and the toggle works normally. Isla to review the copy. |

## GDPR / Compliance

| ID | Action | Status | Notes |
|----|--------|--------|-------|
| GDPR-3 | Cookie-consent notice (partial was 0 bytes) | ✅ | `partials/cookie-banner.blade.php` built — "Necessary only" and "Accept all" equally prominent, choice stored in a 180-day cookie, never re-prompts, `window.cookieConsent.allows('analytics')` API + `gtag('consent','update')` hook for when analytics is added. Included in `landing_layout` + `profileBase`. `pointer-events`-inert except the buttons (also fixed it overlaying Bootstrap modals — z-index 1040). No non-essential cookies are set today, so this is "inform + be ready"; a full category-toggle centre pairs with GDPR-4. |
| GDPR-1 | Data export + erasure | ◑ partial | **Export (Art. 15/20) — done & tested.** `DataExportService` collects the member's rows from 23 tables (allowlisted `{table => key}`, no caller input), redacts `password`/`token`/push keys; `POST /account/data-export` streams it as a dated JSON download; "Download my data" button on the Privacy tab. Cypress asserts the shape + redaction. **Erasure (Art. 17) — request channel done, pipeline not.** `POST /account/request-deletion` logs the request (durable), emails ops, tells the member "actioned within 30 days". The automated wipe (with retention carve-outs for shared family content / legal hold) still needs Emily + Rachel + the retention policy. |
| GDPR-2 | Minors: age gate + parental consent + DPIA | ◑ **age-gate code done** (s9) | **DOB now captured at registration** — `birthday` field (shared-lib) on `register.blade.php` → `personal.day/month/year` (existing "Mar" format). `Register::assertOldEnoughToRegister()` rejects <13 (UK GDPR Art. 8), invalid/future dates → HTTP 400 with a "ask a parent/guardian" message. Verified: under-13 / Feb-30 / no-DOB / future-year all 400; valid adult stores DOB correctly. **Still Olutobi/Helena: the DPIA document, the 13-vs-16 decision, guardian-managed child profiles.** |
| GDPR-4 | Rewrite privacy policy against the real data map | ◑ **DeepSeek transfer resolved** (s9) | **Member biographies cancelled by product** → deleted `app/services/AIService.php` (the only DeepSeek/China integration — was dead code, no callers), deleted orphan `layouts/achive/organogramGemini.blade.php` (client-side Google Gemini w/ family PII), removed the `ai_consent` registration checkbox + collection + `DEEPSEEK_API_KEY` from `.env`. **Rotate/revoke** the exposed `DEEPSEEK_API_KEY` + `GEMINI_API_KEY` + LinkedIn token (plaintext in `.env`/`.env_production`). Policy exists at `/privacy` (`privacy.blade.php`) — still needs Olutobi's review (esp. the line-67 "by submitting you agree" transfer-consent wording, weak post-Schrems II) + the retention periods + DPO contact from `docs/privacy-policy-draft.md`. |
| GDPR-5 | Preference toggles must be honoured (see PUSH-5) | ~ next up | A user cannot currently object to a processing category. |

## Lighthouse / Performance

| ID | Action | Status | Notes |
|----|--------|--------|-------|
| LH-1 | Kill `?v={{ time() }}` cache-busting | ✅ | Mix isn't run with `.version()` (manifest is identity-mapped), so added `assetVersion('js/index.js')` helper (`optimiser.php`) = file mtime — URL changes only when the file does. Applied in `profileBase.blade.php` (the live member layout; `w3s_member` is archive-only). |
| LH-4 | One manifest file, not two | ✅ | `PWA_Manifest.json` deleted; the 4 layouts that linked it now point at `/manifest.json` (also fixed a broken `<link … > />` and a `theme-color: var(--brand-primary)` → `#00bfa5` in the meta tag). |
| LH-2 | Image upload pipeline (resize + WebP/AVIF) | ⛔ | Owner: Kieran. Bigger infra change; source photos up to 9.5 MB in `resources/images/`. |
| LH-3 | Drop one CSS framework on public pages | ~ next up | Bulma + Bootstrap both fully loaded. Move public pages to one. |
| LH-5 | a11y pass on the Stitch palette | ⛔ | Owner: Isla — focus-visible, labels, contrast, alt text. |

---

## Session 1 log (2026-09-02)

**Executed & verified:** SEC-1, SEC-1b, SEC-1c, SEC-3, SEC-4, PUSH-2, PUSH-4 (partial), LH-1, LH-4
(+ earlier this session: 2FA resend rate-limit, loader Swal bridge, `/verify-email` page, `--brand-primary` fix).

**Verification:**
- Curl: IDOR closed (cross-family notification read returns only caller's feed); `/getEmails` unauth → 401, authed existence check works; `/checking` → 404; response headers correct on pages + static assets.
- DB: `pushNotification` 49 rows intact after ALTER; backup at `storage/backups/`.
- Cypress: `auth.cy.js` ✅ (updated for Swal), `events_and_profiles.cy.js` ✅ 3/3.
- `npm run dev` clean; `php -l` clean on all touched files.

**Files touched (mine):**
`.htaccess`, `PWA_Manifest.json` (deleted), `app/controller/{NotificationController,Index}.php`,
`app/router/{router,notification}.php`, `app/classes/VapidClass.php`, `app/function/helper/optimiser.php`,
`resources/views/layouts/{landing_layout,profileBase}.blade.php`,
`resources/views/{baseBulma,baseError,layouts/baseTail}.blade.php` (manifest link only),
`resources/asset/js/components/{api/index,kidsAndSiblings,accountSetting}.js`,
`resources/asset/js/components/register/mobileNameCheck.js`,
`cypress/e2e/auth.cy.js`, `database_deltas.sql`, `storage/backups/pushNotification_2026-09-02.json`.

**⚠ Concurrent work by another session:** `FamilyRequestController.php`, `kinshipRadar.js`,
`member/includes/{leftColumn,kinshipSuggestions}.blade.php`, `getProfile.blade.php`,
`msg/requestApprovalSuccess.blade.php` are modified/added and NOT mine. `network_growth.cy.js`
"displays incoming friend requests" now fails because that work made the "Kin Requests" sidebar
card conditional on there being pending requests — the test needs updating by whoever owns that
refactor. Not touched here.

**Not yet through the TAT gate.** David's four structural checks + Olutobi sign-off still required before any of this deploys to production.

## Session 2 log (2026-09-02)

**Executed & verified:** GDPR-3, PUSH-1, PUSH-4.
- New: `partials/cookie-banner.blade.php`; `registerPushNotification.js` rewritten; `NotificationController::deleteSubscriberData` + `POST /pushNotification/unsubscribe`; push toggle in `accountSetting.blade.php` + `accountSetting.js`.
- Deleted: `pwa/pushManager.js`.
- Fixed in passing: cookie banner overlapped modal action buttons (z-index + pointer-events).
- Cypress: `auth` ✅, `events_and_profiles` ✅ 3/3, `social_feed` ✅ 5/5, `reels` ✅ 7/7. `php -l` + `npm run dev` clean.

## Session 9 log (2026-09-02) — GDPR-2 age gate, biography kill, Pusher auth, IDOR sweep

Driven by user asks: *"age-gate (GDPR-2 code half), Pusher private-channel auth, invite-secret hardening and the IDOR sweep"* + *"I have cancelled member biographies"*.

- **GDPR-2 age gate** — `birthday` field on the register form → `personal.day/month/year`; `Register::assertOldEnoughToRegister()` (13, UK GDPR Art. 8) → HTTP 400. Verified end-to-end.
- **Biographies cancelled** — deleted `AIService.php` (DeepSeek, dead), `organogramGemini.blade.php` (Gemini archive), `ai_consent` UI+collection, `DEEPSEEK_API_KEY`. Resolves the GDPR-4 China-transfer flag. **Exposed keys need rotating.**
- **SEC-6 Pusher** — public channels → `private-family-*` / `private-user-*` + `POST /pusher/auth`. Session-authoritative. Full suite + 3 new auth tests green.
- **SEC-7 invite secret** — hard-coded fallback removed; HMAC from `APP_KEY`/`JWT_KEY`, fail-closed; `APP_KEY` added to `.env`.
- **SEC-8 IDOR sweep** — 6 endpoints fixed (getProfile, removeProfile, Organogram ×4, EngagementController ×2), `idor_sweep.cy.js` 4/4.
- PHPStan L8 clean. `npm run dev` clean. Cypress **43/43** (39 main + 4 idor_sweep).
- New files: `cypress/e2e/idor_sweep.cy.js`. New helpers: `BaseController::sessionSharesFamily` / `sessionCanViewMember`, `Pusher::familyChannel` / `userChannel` / `broadcastToFamily` / `authoriseChannel`.

## Session 8 log (2026-09-02) — post-review: owed gate items

**Marcus — authenticated pentest (executed, not just filed):**
| # | Fix |
|---|-----|
| M-1 🟠 High | OAuth `state` bypass — callback with no session state was accepted (login-CSRF). `assertOauthState()` now requires session state present + `hash_equals`, single-use. Verified `?state=forged` → 400. |
| M-2 🟠 | OAuth session fixation — `session_regenerate_id(true)` added to `OAuthController::loginUser()`. |
| M-4 🟠 | OAuth account-takeover via unverified provider email — existing account + unverified → 409. |
| M-3 🟢 | OAuth callbacks leaked exception text — generic messages + `error_log`. |
| M-6 🟡 | `addReaction` had no CSRF / rate limit — `CheckToken::tokenCheck()` + `Limiter` (60/5min). |
- `FileUploadProcess`/`FileUploader` assessed — solid (MIME-sniff vs extension, size cap, allowlist). `reacted_at` bug already fixed upstream.
- **M-5 open (policy):** OAuth skips mandatory 2FA — Victor/CEO to rule.

**`network_growth.cy.js`** — updated for the Social squad's `leftColumn.blade.php` change (Kin
Requests card is now `x-show`-gated); asserts the section is wired, not the visible heading.
Spec **3/3 green** → full suite green.

**Not code tasks — assigned to their owners as *launch* gates (per the CEO directive):**
GDPR-2 minors/age-gate model + DPIA (Olutobi/Helena — needs the legal decision on self-attest vs.
verified DOB vs. guardian-managed before any code), GDPR-4 publish the reviewed policy + resolve
the DeepSeek-China transfer (Olutobi), dark-launch 1%→10%→100% (David Chen). Registration
**does not currently capture DOB** (the `personal.day/month/year` columns are filled later in the
family-tree flow), so the age gate is a genuine feature + policy decision, not a one-line check.

## Session 7 log (2026-09-02) — TAT board review + red team

Installed PHPStan (dev deps — the "Machine Checks First" gate had never run). Ran the full TAT
board review (`tat_review.md`) + an independent red-team probe, in **two loop iterations**.
**9 findings raised and fixed in the review:**

| # | Sev | Finding |
|---|-----|---------|
| R-1 | 🔴 | ~35 unauth web-executable PHP scripts at the repo root (`diagnostics*`, `fix_schema`, `migrate*`, `test_*`, `clearcache`, `get_count`, `composer-setup`) — `test_db.php` dumped posts, `fix_schema.php` ran `ALTER TABLE`, `migrate.php` re-ran a migration, all over plain HTTP. Fixed: `.htaccess` denies every `.php` but `index.php`; scratch scripts deleted; `.rsync-filter` allowlist (needs `git add`). |
| R-5 | 🔴 | **`index.php` hard-coded-token backdoor** — `?token=diagnose123` ran `diagnostics.php` (users-table dump), bypassing all routing/auth. Block deleted. |
| R-8 | 🟠 | Reel upload MIME check bypassable via client `$_FILES['type']`; stored extension from client filename → `.php` in webroot. Fixed: `finfo`-only, `mime=>ext` map; thumbnail now `getimagesizefromstring`-validated. |
| R-2 | 🟠 | GDPR export leaked other members' ids via `notification.sender_id`. Scoped to receiver-only (Art. 15(4)). |
| R-3 | 🟡 | Unmatched routes served the 404 page with HTTP 200. `RouteDispatch` now sets 404. |
| R-4 | 🟡 | PHPStan L8 was never installed — the "Machine Checks First" gate had never run. Now installed + green; fixed 4 real type issues it caught in the new code. |
| R-6 | 🟢 | `/clearcache` route pattern (`/?`) never matched. Fixed + kept `$isNonProd`-gated. |
| R-7 | 🟡 | Reworked `/getEmails` had no rate limit (enumeration oracle). `Limiter::limit(30/5min/user)`. |
| R-8 | 🟠 | Reel upload MIME check bypassable via client `$_FILES['type']`; stored ext from client filename. → `finfo`-sniffed MIME only + `mime→ext` map; base64 thumbnail now `getimagesizefromstring`-validated. |
| R-9 | 🟠 | New state-changing endpoints (`data-export`, `request-deletion`, `pushNotification/subscribe`+`/unsubscribe`) had **no CSRF check** — and the JWT login cookie is set with no explicit SameSite. → `CheckToken::tokenCheck()` on all four; new `getCsrfToken()` (cookie-first, meta-fallback) in the JS; cypress asserts tokenless POST → 401. |

**Final gates:** PHPStan L8 `[OK] No errors` (full `app/`) · full Cypress ~37/38 (the 1 =
concurrent squad's kin-request WIP) · red-team probe 9/9 · new regression tests for stored-XSS,
GDPR export/deletion, and CSRF rejection.

**Board verdict:** unanimous-with-conditions. David's 4 gates CLEARED. Olutobi's Final Executive
Sign-Off applied, contingent on the 8 deploy conditions in `tat_review.md` (commit `.rsync-filter`,
nginx-vs-Apache config, `.env_production` mail fix, Isla in-browser a11y, Marcus full pentest,
merge the concurrent kin-request branch, dark-launch flag, shared-lib `JwtHandler` SameSite bump,
and the legal items GDPR-2/4).

*(Ops note: the local Apache stopped mid-review with a stale pidfile — unrelated to the change
set, likely the concurrent partyplatform session. Cleared `/opt/homebrew/var/run/httpd/httpd.pid`
and restarted; all suites re-run green after.)*

## Session 6 log (2026-09-02)

- Fixed `.env` line 156: `ADMIN_EMAIL=${ADMIN_MAIL}` (undefined var) → `general@myfamilyplatform.com`.
  Admin/ops email works again; deletion-request email verified sending. **Check `.env_production` for the same.**
- A11y (LH-5, scoped to pages built this program): `/login/code` — "Resend" `<a>` → `<button>`
  (was not keyboard-operable), added `:focus-visible` outlines to OTP boxes + buttons, `role="group"`
  on the OTP container, `aria-live="assertive"` on the error banner. The loader + cookie-banner
  partials already carry `role`/`aria-live`. **A full a11y sweep (contrast, all pages, screen-reader) is still Isla's.**
- CSP flip (SEC-2): checked — `bootstrap/csp/` is empty and no `/csp-report-log` route exists, so
  there's no violation telemetry to fix against. Flipping is now a supervised in-browser task, not blind.

## Session 5 log (2026-09-02)

**Executed & verified:** SEC-2 innerHTML audit, SEC-5 (postComment), GDPR-4 draft.
- SEC-2: `esc()` helper in `components/global.js`; applied across `familyTree/{index,showModal}.js`,
  `reels/reelsPlayer.js`, `allMembers/{html,handleInput}.js`, and the profile-feed legacy path
  (`profilePage/{html,comment,eventHTML}.js`, `htmlFolder/{nameImageTiming,engagementHtml,showPostImages}.js`).
  Deleted dead `profilePage/newPage.js` + `components/alert.blade.php`. New stored-XSS test in
  `social_feed.cy.js`. Full regression: 28/28 across auth/social_feed/reels/events_and_profiles/kinship.
- SEC-5: `ProfilePage::postComment` rate-limited (30/5min/user).
- GDPR-4: `docs/privacy-policy-draft.md` — full draft for Olutobi.

**New file:** `docs/privacy-policy-draft.md`.

## Session 4 log (2026-09-02)
GDPR-1 export + deletion-request (see rows). New: `DataExportService`, `DataPrivacyController`,
`msg/dataDeletionRequest.blade.php`. Found the `.env` `ADMIN_EMAIL=${ADMIN_MAIL}` bug.

## Session 3 log (2026-09-02)

**Executed & verified:** PUSH-3, SEC-5 (partial), SEC-2 prep, GDPR-1 (export + deletion request).
- PUSH-3: iOS-not-installed detection + "Show me how" install prompt in the push toggle.
- SEC-5: `/register/checkContact` rate-limited (30/5min per IP → 429, verified); legit check still works.
- SEC-2 prep: deleted dead `components/alert.blade.php`.
- GDPR-1: `DataExportService` + `DataPrivacyController` (`exportData` / `requestDeletion`), routes, Privacy-tab UI, email template. New cypress tests for both (in `events_and_profiles.cy.js`) — 5/5 green.
- **PUSH-5 / GDPR-5 escalated to a decision** — the `'off'` default makes blind enforcement a base-wide email outage.
- Cypress: `auth` ✅, `events_and_profiles` ✅ 5/5. Build + `php -l` clean.

**New files:** `app/services/DataExportService.php`, `app/controller/members/DataPrivacyController.php`, `resources/views/msg/dataDeletionRequest.blade.php`.

**⚠ Pre-existing config bug found:** `.env` defines `ADMIN_EMAIL` twice — line 100 = a real address, line 156 = `${ADMIN_MAIL}` (an unresolved variable). phpdotenv keeps the last, so **all admin/ops email is currently broken.** The deletion-request feature guards against it (logs + degrades), but `.env` line 156 should be fixed to a real address (and check `.env_production` for the same). Not touched — it's environment config.

## What's left for humans / a dedicated pass

| Item | Owner | Why not code-now |
|------|-------|------------------|
| PUSH-5 / GDPR-5 | Noah / Chloe / Olutobi | opt-in vs opt-out policy + Tier-2 data backfill |
| SEC-2 enforce | Marcus + a canary rollout | live `innerHTML` sink audit first |
| SEC-* pentest | Marcus | manual, independent |
| GDPR-1 export/erasure | Emily + Rachel | new feature + retention policy |
| GDPR-2 minors / DPIA | Olutobi / Helena | legal position first |
| GDPR-4 privacy policy | Olutobi | legal writing against the real data map |
| LH-2 image pipeline | Kieran | infra |
| LH-3 drop a framework | Kieran + Isla | high visual-regression risk, needs full QA pass |
| LH-5 a11y pass | Isla | manual |
