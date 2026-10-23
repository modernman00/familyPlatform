# Changelog

## 2026-10-23
### Added
- Half-siblings (same mum / same dad only), step and adopted siblings, former partners (divorced / separated / widowed) with children from each relationship — Account Settings, Family Studio and the tree (RFC-0001).
- `FamilyGraphService`, placeholder "Not yet added" parent cards, DIVORCED / SEPARATED / WIDOWED stamps, "Half-brother (same mum)" labels.
- Relationship confirmations: adding a half/step-sibling or a former partner asks the person on the other side (shared parent, the relative's own account, the former partner) to confirm in Family Studio; declining removes the link. Migration `2026_10_23_family_relationship_requests.sql`.
- Studio child form: "Who is the other parent?" picker (current spouse, each former partner, or a new/unknown partner).
### Fixed
- Organogram and Family Studio returned HTTP 500 for any member whose profile gender is NULL (`FamilyClaimService` inserted '' into the `family_nodes.gender` enum). 216 dev accounts had NULL gender.
- Account Settings re-saves no longer duplicate siblings, children or spouses; sibling/child gender is honoured (was hard-coded Male); `union_type 'other'` (invalid enum) replaced by `partner`.
- Legacy graph sync no longer re-links half-siblings into the main parent union.
