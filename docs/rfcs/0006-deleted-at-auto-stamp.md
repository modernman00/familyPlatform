# RFC-0006: Stop `deleted_at` being stamped by every UPDATE

- **Author(s):** Ryan Mitchell, Daniel (Social squad), Engineering Council
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend)
- **Gatewatchers:** David / Gbenga Adebisi (DBRE) / Oladapo Salami & Marcus / Ayo Adeyemi
- **Status:** APPLIED TO DEV (2026-10-24) — production pending audit + approvals
- **Created Date:** 2026-10-24
- **Target Apps:** FamilyPlatform (Tier 2: schema change on core identity tables)

## 1. Problem & Value
`account.deleted_at`, `personal.deleted_at` and `contact.deleted_at` are declared `TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP`. MySQL therefore sets them to "now" whenever **any other column of a live row changes**. A stamp is indistinguishable from a deletion, and 13 queries treat `deleted_at IS NOT NULL` as "gone".

**Evidence (dev DB, 2026-10-24):**
- 10 of 21 live (`approved`/`active`) accounts carry a `deleted_at`, including the CEO's own account (stamped 2026-10-23). 0 accounts are genuinely deleted.
- 20 `personal` rows and 34 `contact` rows are stamped. Nothing in `app/`, `src/` or `scripts/` ever deliberately sets `personal.deleted_at` or `contact.deleted_at` (erasure hard-deletes those rows), so every such value is a stamp.
- Proven on a scratch copy: a plain `status` change stamps `deleted_at`; after `MODIFY COLUMN ... DEFAULT NULL` the same change leaves it NULL.

**Impact today**
- Valid family members cannot approve join/link requests (`findMatchingInviter`; worked around with a status check in RFC-0004).
- Stamped people vanish from Directory/Search (`AllMembersData` filters `p.deleted_at IS NULL`) while still showing in the family list, which has no filter.
- **GDPR Art. 17:** the 30-day grace period reads `account.deleted_at` while `status = 'pending_deletion'` (`Login.php:306`). Any UPDATE of that row (token bump, failed-login counter) re-stamps it and silently pushes the permanent-erasure deadline out.
- Onboarding follow-ups, tag lookup, member lookup and family-code rename also skip stamped accounts.

**Non-goals:** changing delete/erasure behaviour; the other seven tables with the same trap (phase 2, below); adding new "hide deleted members" filters (follow-up once this lands).

## 2. Design
Options: (A) **remove the auto-stamp from the column — chosen**; (B) switch every reader to `account.status` — rejected as a sole fix: leaves the grace-period bug and `personal`/`contact` readers; (C) triggers to undo stamps — rejected, hides the defect and adds hot-path cost.

**Phase 1 (this RFC), `migrations/2026_10_24_deleted_at_no_auto_stamp.sql`:**
0. **Safety guard (changes nothing, runs first).** Aborts the migration, so it is *not* recorded in `schema_migrations` and the deploy stops, when (a) an `erased`/`pending_deletion` account's profile row carries a `deleted_at` (possible deliberate soft-delete), or (b) a table has `deleted_at` values but no `ON UPDATE CURRENT_TIMESTAMP` on the column (stamps not explained by the known trap; production may differ from dev). The abort reason is the name of the missing table in the SQL error (`RFC0006_ABORT_...`). The pass path is `DO 0`: a result-returning statement would leave an open cursor in the runner's `PDO::exec()` and break the next statement.
1. Targeted backups: `_bak_20261024_{account,personal,contact}_deleted_at` (id + value; account also `status`).
2. `ALTER TABLE {account,personal,contact} MODIFY COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL, ALGORITHM=INPLACE, LOCK=NONE` — metadata-only, accepted on a scratch copy; MySQL refuses instead of locking if it cannot comply.
3. Clear stamps: `account` where `status NOT IN ('erased','pending_deletion')`; all of `personal` and `contact`. Genuine deletions (`status` erased / pending_deletion) keep their value.

**Phase 2 (separate RFC/PR):** same trap on `children`, `sibling`, `work`, `images`, `familyFriend` (NOT NULL), `personal_duplicates_backup`, and `code_mgt` (also `DEFAULT CURRENT_TIMESTAMP`: all 2,325 rows are "deleted" at birth). No code reads `deleted_at` on these, so no user impact; fix after an audit of `code_mgt` expiry logic.

**Readers:** none change. After the fix `deleted_at IS NULL` means "not deleted" for `account`. `pending_deletion` accounts stay excluded, which matches intent.

## 3. Blast radius
- Shared-lib: none. Only FamilyPlatform's database changes; the other portfolio apps have their own databases.
- **Visible behaviour change:** the 20 stamped `personal` rows (8 with a live account, 12 relatives without one) will **reappear** in Directory/Search. Expected and correct; review the list before applying.
- Backward compatible: no column/type change except removing the auto-update; old code keeps working.
- Any code that *relied* on the stamp as a "last changed" time is wrong by definition; the grep of `app/`, `src/`, `scripts/` found none.

## 4. Failure modes
- ALTER unsupported at INPLACE/NONE → statement errors, nothing changes; fall back to a maintenance-window `ALGORITHM=COPY` (tables are < 300 rows).
- Migration is re-runnable: backups use `IF NOT EXISTS`; ALTER to the same definition is a no-op; UPDATEs only touch non-NULL rows.
- Partial failure between steps: backups exist first; stamps remain (status quo), never new data loss.
- A genuine soft-delete done by some process I did not find would be un-hidden by step 3 for `personal`/`contact`. Mitigation: the step-0 guard aborts on exactly that evidence, before anything changes. A false positive is possible (a `pending_deletion` user whose profile row was also stamped by the trap); the abort is the safe direction: review the rows with the audit queries, then re-run.
- **`scripts/deploy.sh` runs `run_migrations.php` on every deploy** (lines 578-585), so this file applies to production automatically on the next deploy. A guard abort or any failure makes the script exit with **maintenance mode still ON**. Recovery: run the audit queries, fix or accept, then re-run the deploy (or `php scripts/run_migrations.php` on the server, then lift maintenance).

## 5. Security
- **GDPR:** removes the silent extension of the erasure grace period; pending-deletion timestamps stop moving.
- Erased / pending-deletion accounts remain excluded everywhere (`deleted_at` retained, `status` unchanged).
- No new inputs, endpoints or privileges. PoC for the defect (scratch table, no app data): change `status` on a live row → `deleted_at` becomes non-NULL; after the migration it stays NULL.

## 6. Observability
Schema guard test (PHPUnit): `information_schema.COLUMNS.EXTRA` for `account|personal|contact.deleted_at` must not contain `on update`; fails CI if the trap is reintroduced. Log the migration's row counts (cleared vs kept) in the deploy output.

## 7. Rollout / rollback
0. The deploy runs the guard automatically. Still do the parity check below first; the guard is the backstop, not the audit.
1. **Schema parity (Standard 12.1):** run `mysql -u<user> -p <db> < scripts/audit_rfc0006_deleted_at.sql` on production (read-only, SELECTs only; section 8 gives a one-row verdict using the guard's own rules) and compare with dev. The individual queries it contains: `SHOW CREATE TABLE account/personal/contact` and the audit below. Production may not have the same stamps — or the same trap.
   ```sql
   SELECT COLUMN_NAME, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'deleted_at' AND TABLE_NAME IN ('account','personal','contact');
   SELECT status, SUM(deleted_at IS NOT NULL) stamped, COUNT(*) n FROM account GROUP BY status;
   SELECT COUNT(*) FROM personal p JOIN account a ON a.id = p.id WHERE p.deleted_at IS NOT NULL AND a.status IN ('erased','pending_deletion');
   ```
2. Apply to dev (`mysql family < migrations/2026_10_24_deleted_at_no_auto_stamp.sql`); the runner's ledger is out of sync in dev, so do not use `run_migrations.php` there.
3. Verify: schema guard test, `BirthFamilyCodeTest`, `family_code_approval.cy.js`, directory spot check.
4. Apply to production through the normal pipeline (Oladele), outside peak hours.
5. **Rollback:** code is unaffected. To restore values: `UPDATE account a JOIN _bak_20261024_account_deleted_at b ON b.id = a.id SET a.deleted_at = b.deleted_at` (same for personal/contact). Re-adding `ON UPDATE` is **not** recommended.

## 8. Definition of Done
- [x] Migration written; ALTER proven on scratch copies (INPLACE/NONE accepted; before/after demonstrated)
- [x] Guard verified through the real runner's SQL executor on throwaway copies: prod-like data passes and clears; pending/erased conflict aborts; stamps-without-trap aborts; already-migrated and no-stamp databases pass; every abort leaves state unchanged
- [ ] Production schema/data audit reviewed (Gbenga)
- [ ] Peer review (Dami); David gate; Olutobi sign-off (Tier 2)
- [x] Schema guard test `DeletedAtSchemaGuardTest` added; full PHPUnit suite 322/322; dev migrated (stamps 10/20/34 -> 0)
- [ ] CHANGELOG; Phase 2 RFC opened
