# Changelog

## 2026-10-23
### Added
- **Then & Now v2** (RFC-0003): ghost-overlay camera to match the old pose, "Then & Now" prompt on today's birthdays/anniversaries, year suggested from the photo's EXIF date, a Then & Now timeline on profiles (`GET /api/members/{id}/then-now`, family-scoped). No schema change. (A share-image button was built and then removed: family photos stay inside the private network.)
- **Then & Now** photo comparison (RFC-0002): composer button, drag-to-compare slider with side-by-side toggle, "Add your Then & Now" answers and a joined-in count. New `post_comparisons` table (`2026_10_23_post_comparisons.sql`); `post` is untouched.
- Half-siblings (same mum / same dad only), step and adopted siblings, former partners (divorced / separated / widowed) with children from each relationship — Account Settings, Family Studio and the tree (RFC-0001).
- `FamilyGraphService`, placeholder "Not yet added" parent cards, DIVORCED / SEPARATED / WIDOWED stamps, "Half-brother (same mum)" labels.
- Relationship confirmations: adding a half/step-sibling or a former partner asks the person on the other side (shared parent, the relative's own account, the former partner) to confirm in Family Studio; declining removes the link. Migration `2026_10_23_family_relationship_requests.sql`.
- Studio child form: "Who is the other parent?" picker (current spouse, each former partner, or a new/unknown partner).
- Click a "Father / Mother - tap to add" card on the tree to name that parent in place.
- Maiden name ("née X") on cards; birth-family code badge for women when the viewer shares that family.
- Legend rebuilt (people + relationships); dashed half-sibling / dotted step branches; amber "?" badge for relatives awaiting confirmation; "Unknown Father/Mother" replaced by "Father / Mother / Eniola's father".
- Adding a relative in Studio checks whether the email is already a registered member (masked answer, rate-limited) and, after saving, links the card (confirmation request) or invites them to join your family. Migration `2026_10_24_relationship_request_link_account.sql`.
### Fixed
- Organogram and Family Studio returned HTTP 500 for any member whose profile gender is NULL (`FamilyClaimService` inserted '' into the `family_nodes.gender` enum). 216 dev accounts had NULL gender.
- Account Settings re-saves no longer duplicate siblings, children or spouses; sibling/child gender is honoured (was hard-coded Male); `union_type 'other'` (invalid enum) replaced by `partner`.
- Legacy graph sync no longer re-links half-siblings into the main parent union.
