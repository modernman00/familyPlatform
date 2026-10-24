# RFC-0004: Birth / Maiden Family Code (create or link, verified)

- **Author(s):** Ryan Mitchell, Daniel (Social squad), Engineering Council
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David / Gbenga Adebisi / Oladapo Salami & Marcus / Ayo Adeyemi
- **Status:** IMPLEMENTED (v1)
- **Created Date:** 2026-10-24
- **Target Apps:** FamilyPlatform

## 1. Problem & Value
Settings → Family Network only *saves a string* into `otherFamily.otherFamCode`. A married woman cannot **create** her maiden/birth family code, and the string is trusted as proof of ownership:
- `SettingController` routes parent/sibling nodes into whatever tree the string names (no approval).
- `General::sendInvite` mints invite tokens for it.
- A code only "exists" if someone has it as `personal.famCode`, so a freshly created birth code would be invisible to her own mother.

**Value:** a wife creates (or links) her birth family once; invitations to her parents/siblings use that code; her primary household code never changes.
**Non-goals:** moving the primary code (existing join/migration flow), changing "maternal for everyone" into a writable scope, merging two trees.

## 2. Design
Options weighed: (A) keep free text + add an "exists" check — rejected, still lets anyone claim someone else's tree; (B) **registry + verified ownership via `user_families` — chosen**; (C) new `birth_families` table — rejected, duplicates `user_families`.

- **Registry:** `family_codes(code PK, kind primary|birth, created_by, created_at)`. `familyCodeExists()` also checks it. Backfilled from `personal.famCode`; `otherFamily.otherFamCode` upper-cased.
- **Ownership:** `BirthFamilyCodeService::userHoldsCode()` = approved `user_families` row (or `personal.famCode`). `otherFamCode` alone grants nothing.
- **Create (path A):** one transaction — registry row, `user_families` (admin, approved), `otherFamCode`, maiden name; then root tree node (last name = maiden name). Code = 3 letters of the maiden name + 3 digits. One birth code per user; **locked once created**.
- **Link (path B):** `family_approval_requests.purpose = 'birth_link'` goes through the existing signed-link approval by a real member of that family. On approval: `user_families` (member) + `otherFamCode`; `personal.famCode`, `code_mgt` and the session `famCode` are **not** touched.
- **Settings actions:** `createBirthFamilyCode`, `requestBirthFamilyLink`, `updateSecondaryFamilyCode` (now: clear, or re-select a code the user already holds). Anything else → 422.
- **Legacy rows:** existing `otherFamCode` values with no `user_families` link show as "Unverified — re-link" and stop routing writes/invites until verified.

## 3. Blast radius
No shared-lib change. Touches `SettingController`, `General::sendInvite`, `FamilyCodeApprovalService` (small diffs), `accountSetting.blade.php`. Kinship engine and organogram read `otherFamCode` unchanged. 15 dev rows have a legacy code (test data).

## 4. Failure modes
- Unique-code collision: PK on registry, up to 5 retries.
- Any step fails: transaction rolls back, no registry/`user_families` row left.
- Node init fails: logged; code still valid (node auto-claims on first use).
- Double submit / retry: second create → 409 "already have a birth code".
- Registry table missing (pre-migration): `ensureSchema()` creates it.

## 5. Security
- **IDOR/BOLA:** a user can no longer point `otherFamCode` at another family (must be held). Invite-token and node-write paths re-check `userHoldsCode`.
- Code format `^[A-Z0-9]{4,12}$`; maiden name `\p{L}` + space/apostrophe/hyphen, 2–60 chars; prepared statements; CSRF on all forms.
- **PoC (Marcus/Ghost):**
  ```bash
  # Claim someone else's family as "my maiden code"
  curl -b jar -H 'X-CSRF-TOKEN: $T' -d 'action=updateSecondaryFamilyCode&otherFamCode=DOE123' https://familyplatform.test/accountSetting
  # Expected: 422, otherFamily unchanged
  # Mint an invite for an unheld code
  curl -b jar -d 'familyCode=DOE123&...' https://familyplatform.test/sendInvite   # falls back to primary code
  ```

## 6. Observability
JSON log events: `birth_code.created`, `birth_code.link_requested`, `birth_code.linked`, `birth_code.cleared`, `birth_code.rejected` (fields: `user_id`, `code`, `reason`). Alert on `rejected` spike (probing).

## 7. Rollout / rollback
Additive: `migrations/2026_10_24_family_codes_registry.sql`, then code. Rollback: redeploy previous code; registry table and `purpose` column can stay unused.

## 8. Definition of Done
- [x] `php -l`; PHPUnit `BirthFamilyCodeTest`; Cypress `birth_family_code.cy.js`
- [x] CHANGELOG + Playbook updated
- [ ] Peer review (Dami / Kenny); David gate
