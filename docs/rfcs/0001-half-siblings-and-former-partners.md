# RFC-0001: Half-siblings, former partners & blended families

- **Author(s):** Ryan Mitchell, Daniel (Squad 2)
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Reviewers:** Dami Ogunleye (Backend) / Kenny Akindele (Frontend)
- **Gatewatchers:** David, Gbenga Adebisi (DBRE), Marcus, Ayo Adeyemi
- **Status:** IMPLEMENTED (dev) — production migration pending CEO deploy authorisation
- **Created:** 2026-10-23
- **Target Apps:** FamilyPlatform

## 1. Problem
Siblings entered in Account Settings were forced into the user's single parent union, so a sibling with the same mother but a different father (or vice versa) rendered as a full sibling. Former partners with shared children could not coexist with a current spouse, and the Studio/tree layout assumed one union per person.

## 2. Design
- Keep the union graph (`family_unions` / `family_node_children`). Half-siblings = children of two unions that share exactly one parent node; former partners = unions with `is_current = 0` and `union_type` divorced / separated / widowed.
- Unknown other parent = placeholder node (`family_nodes.is_placeholder = 1`), rendered as a greyed "Not yet added" card so the half-sibling hangs from the known parent.
- `App\services\FamilyGraphService` is the single writer (Account Settings + Studio editor). Idempotent: re-saving re-homes an existing sibling/child (matched among the node's own relatives, never family-wide by name).
- Layout: `kinshiptree.js` explicit-union layout (chains `[former … person … current]`, children grouped per union). Legacy layout is untouched when no person has >1 couple union.
- Labels: "Half-brother (same mum)", "Former Wife", "Unknown Father".

## 3. Data & migration (Aisha / Gbenga)
`migrations/2026_10_23_half_siblings_and_former_partners.sql` — additive and idempotent: baseline `CREATE TABLE IF NOT EXISTS` for the graph tables, `family_nodes.is_placeholder`, `family_unions.ordinal`, two indexes. `ALGORITHM=INSTANT` where supported; no table rewrites. Back-up taken before run (`scratch/backup_family_graph_2026_10_23.sql`).
Existing trees are NOT auto-converted: the legacy `sibling` table never stored parentage. Users re-save Siblings in Account Settings (or use the Studio) and the sibling is re-homed in place.

## 4. Security
Every Studio endpoint still checks `family_code` ownership (IDOR gauntlet: 33/33 pass). Service queries are parameterised and family-scoped; enums are whitelisted (`normaliseParentage`, `normaliseUnionType`).

## 5. Rollout
Deploy migration, then code. Rollback: code is backward compatible with the old schema except `is_placeholder`; the new column is nullable-safe (default 0).

## 6. Tests
`tests/Feature/FamilyGraphHalfSiblingTest.php` (12 tests: half/full routing, idempotent re-save, re-homing, ex + current coexist, placeholder parents, foreign-family base node, enum fuzzing) + existing `OrganogramEditorTest` regression.
