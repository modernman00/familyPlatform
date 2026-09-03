# TAT Board Review — Readiness Programme (sessions 1–7)

**Date:** 2026-09-02 · **Chair:** Olutobi (CEO representative) · **Coordinator:** Jumoke
**Change set:** ~55 source files across security, push, GDPR and performance work.
See `implementation_plan.md` for the item-by-item log and `docs/privacy-policy-draft.md`.

**Tier:** 2 (schema change, auth-layer changes, GDPR). Full board convened. Two loop iterations
of scrutiny + red team; **10 findings raised and fixed inside the review** before sign-off —
including **two unauthenticated backdoors** (R-5 token bypass in `index.php`, R-10 forged-JWT in
`public/set_cookie.php`) and ~35 web-executable dev scripts (R-1).

---

## Proof of execution (Mandate 10.3 / 10.4)  — final pass 2026-09-02

| Gate | Result |
|---|---|
| PHPStan **level 8**, full `app/` (`vendor/bin/phpstan analyse`) | **`[OK] No errors`** — incl. the 5 type issues this review found & fixed (array-to-string casts, redundant null-coalesce, always-true `is_string`) |
| `php -l` — every touched file | clean |
| `npm run dev` | Compiled Successfully |
| Cypress e2e — full suite (final run) | **43 / 43 — all green** · `auth` 1/1 · `chaos_tests` 4/4 · `events_and_profiles` 6/6 · `kinship` 9/9 · `network_growth` 3/3 · `reels` 7/7 · `social_feed` 9/9 (+3 Pusher-auth) · `idor_sweep` 4/4 |
| New regression tests added | stored-XSS (`social_feed.cy.js`); GDPR export + deletion **+ CSRF-token rejection** (`events_and_profiles.cy.js`); login-flow Swal (`auth.cy.js`) |
| Red-team probe — re-run independently after every fix | **11 / 11** assertions + 3 GDPR checks (backdoors dead · dev scripts 403 · IDOR blocked · enumeration 401 · headers · CSRF 401 without token · export redaction + own-only notification scope) |

---

## Findings raised **in** this review and resolved before sign-off

*(10 findings across two loop iterations of board scrutiny + red-team. Each was fixed, then
PHPStan L8 + the affected Cypress specs + `redteam.sh` were re-run green before moving on.)*

| # | Severity | Finding | Fix |
|---|---|---|---|
| R-1 | 🔴 **Critical (dev/staging) / 🟠 Medium (prod)** | **~35 loose PHP scripts at the web root** (`diagnostics*.php`, `fix_schema.php`, `migrate*.php`, `test_*.php`, `clearcache.php`, `get_count.php`, `composer-setup.php` …) **executed on direct request, unauthenticated** — verified live on `familyplatform.test`: `test_db.php` dumped post data, `fix_schema.php` ran `ALTER TABLE`, `migrate.php` re-ran a migration. *Prod via `scripts/deploy.sh` is partly shielded by the `.rsync-filter` `+`-only allowlist — but that file is **untracked** (`?? .rsync-filter`) and only covers the rsync path, not a git-pull / cPanel deploy.* | Three layers now: (1) `.htaccess` `Require all denied` for every `.php` except `index.php`; (2) the dev/test/diagnostic scripts deleted from the repo; (3) the existing `.rsync-filter` allowlist. Migration scripts kept (CLI-only, now 403 over HTTP). |
| R-2 | 🟠 Medium | Data export (`DataExportService`) returned `notification` rows where the user was the **sender**, exposing other members' ids — GDPR Art. 15(4). | Scoped to `receiver_id IN (own id + own family codes)` — verified: export now shows only the caller's own id/code. |
| R-3 | 🟡 Low | Unmatched routes (e.g. `/checking`) served the 404 page with **HTTP 200**. | `RouteDispatch` now sets `http_response_code(404)` on no-match. |
| R-4 | 🟡 Low | PHPStan L8 not previously installed, so the "Machine Checks First" gate had never actually run on this code. | `composer install` (dev deps); L8 now green and part of the proof block. |
| R-5 | 🔴 **Critical** | **`index.php` contained a hard-coded-token backdoor:** `if ($_GET['token'] === 'diagnose123') { require 'diagnostics.php'; exit; }` — bypassed all routing and auth, ran the users-table dump for anyone who knew the token (which is in the git history). | Block deleted from `index.php`. `/?token=diagnose123` is now just the homepage. Codebase grep-swept for other hard-coded token bypasses — none found. |
| R-10 | 🔴 **Critical** | **`public/set_cookie.php` — unauthenticated login backdoor.** Hitting `/public/set_cookie.php` forged a valid JWT login cookie for a **hard-coded user** (`117540OLAWALE`, family `MODERNMAN`) and redirected to `/profilePage`. Anyone with the URL was logged in as that member. `bootstrap/testUnit/submitform.php` (a broken stub) was also web-reachable. | Both blocked by the R-1 `.htaccess` `.php` deny (verified 403) **and deleted from the repo.** `public/` now contains zero `.php`; `bootstrap/` only `cache/ csp/ log/`. |
| R-6 | 🟢 Trivial | `/clearcache` route was `'/clearcache/?'` — AltoRouter treats `?` literally, so it never matched; a dev cache-reset was silently unavailable. | Pattern fixed to `/clearcache`, still `$isNonProd`-gated. |
| R-7 | 🟡 Low | The reworked `/getEmails` (email-existence check) was session-gated but had **no rate limit** — a logged-in user could sweep it to enumerate registered emails. | `Limiter::limit($userId . ':emailcheck', 'post')` (30 / 5 min per user). Legit use is a handful of debounced, client-cached checks. |
| R-9 | 🟠 Medium | **The new state-changing endpoints had no CSRF token check** — `data-export`, `request-deletion` (queues account deletion), `pushNotification/subscribe` + `/unsubscribe`. Only mitigation was `SameSite=Lax`, and the **JWT login cookie is set with the 7-arg `setcookie()` — no explicit SameSite at all** (relies on the browser default). | `CheckToken::tokenCheck()` on all four; the JS sends `X-XSRF-TOKEN` via a new shared `getCsrfToken()` (`global.js`) that reads the **`XSRF-TOKEN` cookie first** (kept in sync by `init.php` every request) rather than the `<meta>` tag (frozen at render, goes stale right after a 2FA session-token regen). Verified both ways: Cypress asserts the tokenless POST → 401/403; curl confirms token → 200 + `password`/`token` redacted + notification scope = own id only. *Vendor `JwtHandler` still needs `samesite` on its `setcookie` — shared-lib bump.* |
| R-8 | 🟠 Medium | **Reel upload MIME check was bypassable** — it passed if the sniffed `finfo` type **or** the client-supplied `$_FILES['type']` matched the allowlist, and the stored extension came from the client filename. An uploaded `.php` could land in `public/resources/videos/reels/` (only blocked from executing by the new R-1 `.htaccess`, and not on nginx). The base64 thumbnail was written with no image validation. | Video: `finfo`-sniffed MIME only, against a `mime => ext` map; stored extension derived from the sniffed type, never the filename. Thumbnail: `getimagesizefromstring()` must confirm JPEG/PNG/WebP and ≤5 MB before write. |

---

## Board votes

### James — Squad Lead / implementation
**APPROVE.** Every change is behind a test or an empirical curl check. Backups taken before the one
schema change (`storage/backups/pushNotification_2026-09-02.json`). Dead code removed, not left to rot.

### Sarah — CPO / business value
**APPROVE.** Data export + erasure request and the push opt-in are table stakes for a family product
that holds children's data. No scope creep; the legal-blocked items were escalated, not bodged.

### Chloe — CMO / copy & UX voice
**APPROVE.** Cookie-consent, push-toggle state messages, deletion-request wording and the Swal
titles are plain, honest and on the British-spelling house style. "Necessary only" is as prominent
as "Accept all" (ePrivacy).

### Marcus — SecOps  *(independent inspection, Mandate 3)*
**APPROVE.** Independently verified the change set (IDOR, email dump, SSE DoS, headers, rate limits,
XSS sinks, R-1 root scripts, R-5/R-10 backdoors — `redteam.sh` 11/11). **Then ran the authenticated
pentest that was owed** — findings below, all fixed:

| # | Sev | Finding | Fix |
|---|-----|---------|-----|
| M-1 | 🟠 High | **OAuth `state` bypass** — the callback only rejected on a *mismatch when a session state existed*, so a callback with **no prior redirect** (`/auth/google/callback?state=forged&code=<attacker's own>`) was accepted → login-CSRF: the victim ends up in the attacker's account. | `assertOauthState()` — session state must be **present** and `hash_equals`-match, single-use. Verified: `?state=forged` → `400`. |
| M-2 | 🟠 Med | OAuth login never regenerated the session id (the password path does) → session fixation. | `session_regenerate_id(true)` in `loginUser()`. |
| M-4 | 🟠 Med | An **unverified** provider email that matched an existing password account **took it over** silently. | Existing account + unverified email → `409` "sign in with your password". Google emails are provider-verified; Facebook checked via `verified`/`is_verified`. |
| M-3 | 🟢 Low | OAuth callbacks did `exit('Something went wrong: '.$e->getMessage())` — leaked exception text. | Generic user message; real error to `error_log`; proper 400/502 codes. |
| M-6 | 🟡 Med | `addReaction` had **no CSRF check and no rate limit** (IDOR was already handled via `postFamCode`). | `CheckToken::tokenCheck()` + `Limiter::limit(60/5min, 'comment_reactions')`. |

**Assessed, no change needed:**
- `FileUploadProcess` → `FileUploader` (vendor) — content-sniffs every allowed type against a
  `MIME_MAP[$extension]`, size-caps at 10 MB, allowlists extensions. A `.php` renamed `.jpg` is
  rejected. One edge for the shared-lib backlog: if `finfo` **and** `getimagesize` both fail
  the sniff is skipped — needs `ext-fileinfo` guaranteed in prod.
- `addReaction`'s `reacted_at` bug (old memory note) — **already fixed** upstream; current code
  uses `reaction`/`label`/`created_at`/`updated_at`, all of which exist.

**M-5 (open, policy):** OAuth login bypasses the mandatory emailed-2FA step. Common design
(the provider does MFA) but if the org's policy is "2FA always", this is a gap — **Victor / CEO to rule.**

**Still owed (needs a running attack lab, not a code read):** invite-token brute-force timing,
Pusher channel authorization, and a full IDOR sweep of every `/member/*` route.

### Helena — Board / regressions & quality
**APPROVE.** Full Cypress suite **green** after `network_growth.cy.js` was updated for the Social
squad's concurrent `leftColumn.blade.php` change (the "Kin Requests" card is now `x-show`-gated on
`friendRequests.length`; the test now asserts the section is *wired* rather than that the heading
text is visible for a request-less account). The concurrent kin-request work is in the working
tree and must still be **committed** by its owner — it is not part of this change set's diff review.

### Ajibike — AI Governance / agentic compliance
**APPROVE.** Amnesia Defense held (scratch files, no log dumps in chat). Anti-hallucination:
schemas checked with `DESCRIBE`, methods verified in `vendor/`, routes verified with `curl`.
Rollback artifact created before the `ALTER`. `--dry-run` used before `composer require`. The
DeepSeek (China) data-transfer was surfaced to Legal, not buried.

### Isla — Frontend Gatewatcher
**CONDITIONAL APPROVE.** Code-level a11y is in: `/login/code` focus-visible rings, `role="group"`
on the OTP field, the resend control changed from a non-focusable `<a>` to a `<button>`,
`aria-live` on error banners; cookie banner `role="dialog"`; loader `role="alert"`.
**Condition — I have not seen it in a real browser.** Before deploy I need to check: contrast on
the Stitch palette (light + dark), the cookie banner and push-toggle states, and keyboard flow
through the OTP page. Headless Cypress does not cover this.

### Kieran — Performance Gatewatcher
**APPROVE with note.** LH-1 (mtime cache-busting) and LH-4 (single manifest) land real wins.
`esc()` is 5 short `.replace()` calls — negligible in render loops. The loader `MutationObserver`
only runs on non-feed pages and its callback is O(depth-to-`_notification`). **Note:** LH-2
(multi-MB source images) and LH-3 (Bulma+Bootstrap both loaded) are still open and mine.

### David — Gatewatcher / 4 structural gates  *(Hands-On Audit, Mandate 10.2)*
Ran the gates in my own sandbox, did not read pasted output:

| Gate | Verdict |
|---|---|
| 1. The Machine Checks First — PHPStan L8 | ✅ `vendor/bin/phpstan analyse` → `[OK] No errors` (full run, replicated) |
| 2. Never Trust the Data — defensive frontend | ✅ `esc()` on all user-content sinks; `?.`, `\|\| fallback`, `.catch(() => ({}))` throughout the new JS; `DataExportService` per-table `try/catch` → degrades to `__error`, never aborts |
| 3. Plan for the Worst — async failure paths | ✅ deletion-request survives a dead mailer (try/catch + `error_log` backstop); push subscribe/unsubscribe `.catch`; `chaos_tests` 4/4. ⚠️ *minor:* no explicit chaos test forcing a DB outage mid-export — the code handles it but it isn't asserted |
| 4. Final Boss — is the deploy bulletproof | ✅ **conditional.** The `.php` lockdown is `.htaccess` (Apache). If prod runs nginx, `.htaccess` is inert — **mitigated** by deleting the scripts from the repo so a clean deploy carries nothing to expose, but Oladele must confirm the prod server + port the header/`.php` rules if it is nginx. |

**David's gate: CLEARED, with the deploy conditions below.**

### Olutobi — Deloitte / Final Executive Sign-Off
Consensus is **unanimous-with-conditions**. Per Mandate 7, I apply the **Final Executive Sign-Off
on behalf of the CEO** for the code in this change set, **contingent on every condition below being
met before Oladele deploys.**

---

## Post-review addendum (2026-09-02, later) — owed items executed

Following the CEO directive, the **code-owned** gate items were worked:

- **Marcus's authenticated pentest** — done (M-1…M-6 above). OAuth `state` bypass, session
  fixation, unverified-email takeover, error-message leak, and `addReaction` CSRF/rate-limit all
  fixed and verified. `FileUploadProcess` assessed and cleared. Remaining items need a live attack
  lab, not a code read — stay with Marcus.
- **`network_growth.cy.js`** — brought green (see Helena). **Full Cypress suite now passes.**
- **GDPR-2 / GDPR-4 / dark-launch** — these are *launch* gates owned by Legal (Olutobi/Helena) and
  SRE (David Chen), not code deliverables. GDPR-2 in particular needs the legal model decided
  (self-attestation vs. verified DOB vs. guardian-managed) before any age-gate code is meaningful —
  and registration doesn't capture DOB today, so it's a feature, not a check. Left with the owners.

### Session 9 addendum (2026-09-02, later still) — user-directed follow-ups

- **GDPR-2 age-gate code half — done.** DOB captured at registration (`birthday` field → `personal.day/month/year`); `Register::assertOldEnoughToRegister()` rejects <13 / invalid / future → HTTP 400. Verified end-to-end. DPIA + 13-vs-16 + guardian model still Olutobi/Helena.
- **GDPR-4 DeepSeek/China transfer — resolved.** Product cancelled member biographies → `AIService.php` (the only DeepSeek call, dead code) deleted, `organogramGemini.blade.php` (client Gemini + PII) deleted, `ai_consent` + `DEEPSEEK_API_KEY` removed. **Exposed `DEEPSEEK_API_KEY` / `GEMINI_API_KEY` / LinkedIn token must be rotated** (plaintext in `.env` / `.env_production`). Policy at `/privacy` still needs Olutobi's review.
- **SEC-6 Pusher channels were public** — every family's feed/events readable by anyone with the bundled app key. Migrated to `private-family-*` / `private-user-*` + server-authorised `POST /pusher/auth`. `friend-request-channel` PII leak flagged for the kin-request squad (their file).
- **SEC-7 invite-token secret was hard-coded** (`APP_KEY` never set → source-readable fallback → forgeable invites). Now derived from `APP_KEY`/`JWT_KEY`, fail-closed. `APP_KEY` added to `.env`. **Re-issue any live invite links.**
- **SEC-8 IDOR sweep** — 6 endpoints fixed (getProfile, removeProfile, Organogram ×4, EngagementController ×2 + CSRF). `idor_sweep.cy.js` 4/4. `PostMessage`/`Event`/`OrganogramEditor`/`deleteReel`/notifications checked clean.
- PHPStan L8 clean · Cypress **43/43**.

---

## Conditions of deployment (all must be satisfied)

1. **Oladele:** (a) `git add .rsync-filter` — it is currently untracked, so the deploy allowlist
   only works on machines that happen to have the local copy. (b) Confirm the production web server:
   Apache → the new `.htaccess` rules apply as-is; nginx → port the `.php`-deny, `Permissions-Policy`,
   `X-XSS-Protection: 0` and HSTS rules to the nginx config. (c) Confirm no `diagnostics*.php` /
   `migrate*.php` / `test_*.php` is present in the live docroot.
2. **Oladele:** fix `ADMIN_EMAIL` in `.env_production` (same `${ADMIN_MAIL}` typo as the dev `.env`,
   which broke all ops email — fixed in dev this session).
3. **Isla:** in-browser visual + a11y pass (her condition above).
4. **Marcus:** the full authenticated pentest is still outstanding and is a separate gate.
5. **Concurrent work:** the Social squad's kin-request branch must be merged and
   `network_growth.cy.js` brought green by its owner.
6. **Legal (Olutobi + Helena):** GDPR-2 age-gate *code* is done (13, DOB captured); the **DPIA**,
   the 13-vs-16 decision and guardian-managed child profiles still gate launch. GDPR-4: the
   DeepSeek-China transfer is **resolved** (biographies cancelled, integration deleted) — remaining
   is Olutobi's review + publish of the `/privacy` policy.
6a. **Oladele:** rotate/revoke the API keys that sat in plaintext in `.env` / `.env_production`
   (`DEEPSEEK_API_KEY`, `GEMINI_API_KEY`, `LINKEDIN_ACCESS_TOKEN`); set `APP_KEY` in
   `.env_production` (added to dev `.env` this session — invite tokens now depend on it).
6b. **Kin-request squad:** `FamilyRequestController` still broadcasts friend-request PII to the
   public `friend-request-channel` — switch to `private-user-<approverId>` when merging.
7. **Dark-launch (Mandate 9.7):** roll the changed member-facing surface (push toggle, cookie
   banner, data-privacy tab) behind David Chen's phased flag — 1% → 10% → 100%.
8. **Shared-lib bump:** `Src\JwtHandler` sets the login cookie with no `samesite` — add
   `samesite=Lax` (or `Strict`) to its `setcookie()` calls, publish, and Composer-bump. Do **not**
   patch `vendor/` in place (Mandate 5).

## Not in this change set (tracked, owned elsewhere)
PUSH-5 / GDPR-5 policy decision · GDPR-1 automated erasure pipeline · SEC-2 CSP enforce (audit done,
needs supervised rollout) · LH-2 / LH-3 / LH-5.
