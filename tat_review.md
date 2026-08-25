# Technical Approval Team (TAT) Audit - Organogram Profile Image Bug

## Background
The user reported that their uploaded profile image displays correctly on the Profile Page but defaults to the generic `avatarM.png` on the FamilyTreeJS Organogram, despite recent backend SQL updates. 

## Agentic Forensic Analysis
The previous agent modified the `buildSixGenGraphData` query in `Organogram.php` to `LEFT JOIN` the `profilePics` table and construct a valid `/resources/images/profile/` path.

However, the SRE/DBRE investigation reveals a potential data or caching mismatch:
1. **Frontend Verification**: `profilePage.blade.php` correctly resolves `/resources/images/profile/{img_filename}`. This means the frontend path is exactly correct.
2. **Backend Query Verification**: The SQL in `Organogram.php` uses `fn.user_id = pp.id`.
3. **The Root Cause**: If the query is logically sound but the result is missing, the `user_id` in `family_nodes` must be missing, mismatched, or `profilePics` is not strictly matched. OR, `graphJson` is being statically cached/rendered on the initial page load, and the user's browser is caching the HTML.

## Personas Debate

**Quality Team / Victor (CTO)**
- **Fatal Flaw / Concern**: If `user_id` in `family_nodes` does not exactly match `id` in `profilePics` (e.g. collation, leading/trailing spaces, or the root node was inserted with a completely different session ID), the `LEFT JOIN` will silently fail and return NULL. 
- **Impact**: The UI degrades to the default avatar, frustrating the user and creating an inconsistent experience across pages.
- **Mandatory Fix / Recommendation**: We must write a fallback debug script to dump the exact `graphJson` for this user, OR explicitly fix the frontend fallback to force fetch from `AllMembersData` if `avatar_url` is broken.

**Usability Team / Chloe (CMO)**
- **Fatal Flaw / Concern**: The user expects a "first-time-right" experience. Telling them to refresh repeatedly when the backend data linkage is broken is poor UX.
- **Impact**: Loss of trust in the Family Tree USP.
- **Mandatory Fix / Recommendation**: The node object should just use a consistent global endpoint for member data, or we ensure the SQL data injection is completely bulletproof.

**Security / Marcus (SecOps)**
- **Fatal Flaw / Concern**: No security risk in fetching public profile images, but injecting raw SQL strings using `CONCAT` should be carefully checked.
- **Impact**: Negligible.
- **Mandatory Fix / Recommendation**: Approve the SQL logic, but verify data integrity.

## David (Principal Gatewatcher)
The structural safety gates (PHPStan, fallback defaults) are intact. The JS uses `img: node.avatar_url || fallback`, so it safely degrades. The problem is purely a data linkage issue in MySQL.

**Gate Status:** CLEAR.

## Final Executive Sign-Off (Olutobi)
Execute the fix. We need to inspect the `graphJson` rendering or forcefully patch the linkage by updating `family_nodes` `user_id` to ensure it matches the session ID exactly, OR we modify the PHP to fetch the image directly via PHP models instead of a raw SQL JOIN which might have collation issues.
