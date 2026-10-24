# RFC-0005: Change the Family Code by Family Vote

- **Author(s):** Ryan Mitchell, Daniel (Social squad), Engineering Council
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David / Gbenga Adebisi / Oladapo Salami & Marcus / Ayo Adeyemi
- **Status:** IMPLEMENTED (v1)
- **Created Date:** 2026-10-24
- **Target Apps:** FamilyPlatform

## 1. Problem & Value
A family code is fixed at creation. The creator wants to change it (typo, rebrand, married name) and have every member follow. Value: the code can evolve without anyone re-joining. **Non-goals:** renaming birth codes (RFC-0004 locks them), merging two families, undo.

## 2. Design
- **Who:** only the code's creator (`family_codes.created_by`, else the earliest approved `user_families` admin). `TEMP123` (shared placeholder) is refused.
- **Consent:** the process cannot start without the poll. `propose()` is the only entry point and it creates an ordinary feed post + `post_polls` (Yes / No) and a `family_code_rename_requests` row. The creator's proposal is their Yes vote.
- **Rule: majority of those who voted.** Eligible = approved members of the code with a live account. When the 7-day window closes the change applies if Yes > No among votes cast (a tie keeps the code); inactive members never count against it, but the creator's own Yes is not enough: at least one other member must have voted (unless the creator is the whole family). Two outcomes cannot be overturned, so they close early: Yes from more than half of *all* eligible members applies at once; No from half or more of all eligible members rejects at once. Closing is lazy (any vote, proposal or settings load) plus `scripts/close_family_code_votes.php` for cron. One open vote per code; 30-day cooldown after a change. Constants in `FamilyCodeRenameService`.
- **Vote path:** `/api/poll/vote` detects rename options and routes to `castConsentVote()` (members only, single choice, re-evaluates). Ordinary polls are untouched.
- **Apply (one transaction):** rewrites the code (case/`#`-insensitive match) in `personal` (famCode, familyCode2), `code_mgt`, `user_families`, `otherFamily`, `family_codes` (key swap, creator kept), `post`, `events`, journals, announcements, nodes, unions, time capsules, hashtags, relationship/approval/migration requests, invite tokens, kinship dismissals, notification orchestration, reels, recommendations and `api_keys.family_codes` JSON. Columns are checked against `information_schema` first so a schema gap cannot break the change. Writes `family_code_aliases(old → new)`.
- **After commit (best effort):** poll post text, current session, in-app + push notification to every member.
- **Old codes keep working:** `BaseController` follows the alias for live sessions (every 30 s); register links and join requests resolve aliases.
- **Alternatives rejected:** a new `family_id` surrogate key (right long-term, but a portfolio-wide migration); renaming only the creator's rows (splits the family).

## 3. Blast radius
Additive tables; small diffs to `SettingController`, `EngagementController::vote`, `BaseController` (one cached query per 30 s), `Register`, `accountSetting.blade.php`, `howToUse.blade.php`. No shared-lib change. Tables not listed above (`sibling.famCode` holds ids, not codes; `familyFriend`) are intentionally untouched.

## 4. Failure modes
- New code taken during the vote: unique registry/alias checks inside the transaction; rolled back, request marked `failed`, creator notified, old code intact (tested).
- Any statement fails: full rollback, nothing half-renamed.
- Notification/push/feed-text failure: logged, never undoes the change.
- Vote double-submit: single-choice delete+insert in a transaction.
- Alias table missing: `ensureSchema()` creates it; alias lookup failure never blocks a request.

## 5. Security
- **BOLA/IDOR:** the code to rename is derived from the session user's DB record, never from the request; only the creator may propose/cancel; voting requires current membership (server-side, not the client's `post_no`).
- Code format `^[A-Z0-9]{4,12}$`, prepared statements, identifiers from a constant whitelist, CSRF on the settings POST, `Limiter` on propose.
- **PoC (Marcus/Ghost):**
  ```bash
  # non-creator member proposes -> 403
  curl -b jar -H "X-CSRF-TOKEN: $T" -d 'action=proposeFamilyCodeRename&new_code=HIJACK1' https://olaogun.test/accountSetting
  # outsider votes on a rename poll -> 403
  curl -b jar -H "X-CSRF-TOKEN: $T" -d "option_id=$YES" https://olaogun.test/api/poll/vote
  # claim another family's code as the new code -> 409
  ```
  All covered by `FamilyCodeRenameTest`.

## 6. Observability
JSON events: `code_rename.proposed|applied|rejected|expired|cancelled|failed|rejected` with `user_id`, `request_id`, `code`. Alert on `apply_failed` and on a `rejected` (probing) spike.

## 7. Rollout / rollback
Additive: `migrations/2026_10_24_family_code_rename.sql`, then code. Roll back by redeploying the previous code; tables can stay. A completed rename is reversed by a new vote (old code is an alias, so it is *not* available for reuse; the creator picks a fresh code).

## 8. Definition of Done
- [x] `php -l`, PHPStan L8 clean on the new service; PHPUnit `FamilyCodeRenameTest` (13 tests incl. chaos)
- [x] CHANGELOG + Playbook 4c; Cypress `family_code_rename.cy.js`
- [x] Peer review (high-effort code review): 10 findings; 8 fixed (session `famCodes` string, `sibling` column, sargable updates + straggler check, DB-clock expiry, creator-Yes-alone minimum, named lock on propose, DDL only when tables missing, CLI bootstrap); 2 accepted (lazy settle on settings load - cron script provided; one alias lookup per 30 s per session)
- [ ] David gate
