# RFC-0003: "Then & Now" v2

- **Author(s):** Ryan Mitchell, Daniel, Engineering Council
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewers:** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David / Gbenga Adebisi / Oladapo Salami & Marcus / Ayo Adeyemi
- **Status:** IMPLEMENTED
- **Created Date:** 2026-10-23
- **Target Apps:** FamilyPlatform
- **Builds on:** RFC-0002

## 1. Scope (the five items parked in RFC-0002)
1. **Ghost-overlay camera** — live camera with the Then photo faintly overlaid so the Now photo can match the pose.
2. **Milestone prompts** — a "Then & Now" button on today's birthday/anniversary in the Memories & Milestones widget; opens the composer with a ready caption.
3. **Photo dating** — suggest the year from the photo's EXIF date.
4. **Person timeline** — a Then & Now strip on a member's profile and on your own page.
5. ~~Share card~~ — **dropped by the CEO (2026-10-23): FamilyPlatform is a private family network, so nothing in Then & Now is shareable outside it.**

## 2. Design decisions
- **No schema change.** Everything reuses `post_comparisons` and `post_tags` from RFC-0002.
- **Camera (1):** `getUserMedia`, ghost is the in-memory Then `File` (blob URL), stage takes the video's aspect ratio so the overlay lines up with the captured frame. The capture becomes a normal `File` and goes through the existing upload path. Nothing is uploaded until the member posts. Permission denied or no camera: an inline message and the shutter is disabled; the file picker still works. Esc closes; tracks are stopped on every exit path.
- **Prompts (2):** only when `days_remaining === 0` and type is birthday/anniversary. The caption is applied after the modal's own form reset and never overwrites typed text. No push notification in v2 (the daily push job is unchanged).
- **Dating (3):** pure-JS EXIF reader (DateTimeOriginal, then DateTimeDigitized), JPEG only, first 256 KB. Read from the original file *before* compression, which strips EXIF. Implausible years (< 1900, future) and zeroed camera clocks are rejected. It only fills an empty label. No filename or `lastModified` guessing: both are unreliable and would put wrong years on family photos.
- **Timeline (4):** `GET /api/members/{id}/then-now` → `PostComparison::forMember`, one query. A comparison is listed when the member authored it or is tagged in it, and only if the post is published, not deleted, and in one of the *viewer's* family codes (or is the viewer's own post). Limit 12, max 24.
- **Share card (5):** removed. A feature that exports family photos out of the private network contradicts the product's privacy promise.

## 3. Blast radius
Additive: one new route, one controller, one model method, JS modules, CSS. Feed payload unchanged from RFC-0002 except the pusher broadcast now carries `comparison`.

## 4. Failure modes
- Timeline endpoint error → JSON 500 with a generic message; the strip stays hidden.
- Camera blocked / missing → message + picker fallback.
- EXIF unreadable / truncated → no suggestion, never throws.

## 5. Security
- **IDOR:** the viewer's family scope is enforced in SQL, using the session's family codes, not anything from the request. `userId` is validated against `^[A-Za-z0-9_\-]{1,64}$` and bound as a parameter.
- Timeline strip renders with `textContent` / `alt` only, never `innerHTML`.
- **PoC (Marcus/Dapo):**
  ```bash
  # another family's member timeline must come back empty, anonymous must be refused
  curl -s -b "$VIEWER_COOKIE" https://olaogun.test/api/members/<member-of-other-family>/then-now   # -> items: []
  curl -s -o /dev/null -w "%{http_code}\n" https://olaogun.test/api/members/anyone/then-now        # -> 401/302, no data
  curl -s -b "$VIEWER_COOKIE" "https://olaogun.test/api/members/x'%20OR%20'1'='1/then-now"          # -> 404/422
  ```
  Automated as PHPUnit `testTimelineNeverLeaksAnotherFamilyDeletedOrDraftPosts`, `testTimelineEndpointRejectsAnonymousAndMalformedIds`.

## 6. Observability
`then_now.timeline_viewed` (viewer, member, count). `then_now.created` from RFC-0002 is unchanged.

## 7. Rollout / rollback
No migration. Rollback = redeploy the previous build; nothing stored depends on v2.

## 8. Definition of Done
- [x] PHPUnit `ThenAndNowComparisonTest` (14 tests), node `tests/js/photoDate.test.mjs` (8 checks)
- [x] Cypress `then_and_now.cy.js` (3) and `then_and_now_v2.cy.js` (5)
- [x] CHANGELOG + Playbook updated
- [ ] Peer review (Dami / Kenny), David's gate, Olutobi's sign-off
