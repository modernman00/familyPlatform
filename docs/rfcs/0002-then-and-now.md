# RFC-0002: "Then & Now" Photo Comparison

- **Author(s):** Ryan Mitchell, Daniel (Social squad), Engineering Council
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele (Frontend)
- **Gatewatchers:** David / Gbenga Adebisi / Oladapo Salami & Marcus / Ayo Adeyemi
- **Status:** IMPLEMENTED (v1)
- **Created Date:** 2026-10-23
- **Target Apps:** FamilyPlatform

## 1. Problem & Value
Families love "then vs now" photos (childhood vs adult, old house vs today). Today they post two loose photos and lose the effect. A drag-to-compare post gives a shareable, emotional format and a reply loop ("Add your Then & Now").
- **Metrics:** share of posts that are Then & Now; answers per Then & Now; feed render with no console errors.
- **Non-goals (v2):** ghost-overlay camera to re-create the old pose (Silas), auto prompts on birthdays/anniversaries, EXIF dating.

## 2. Design
Options weighed by the Council:
- A. New columns on `post` — rejected: ALTER on the hottest table, timestamp-default hazards (Gbenga, David).
- B. **New `post_comparisons` table linked to a normal post — chosen.** The post still stores both photos in its first two `post_img*` columns (Then first, Now second), so comments, reactions, tags, notifications and old clients keep working and degrade to a two-photo post.
- C. Standalone gallery feature — rejected: no feed loop (Isabella, London).

**Schema** (`migrations/2026_10_23_post_comparisons.sql`, create-only): `post_no` UNIQUE, `before_img`, `after_img`, `before_label`/`after_label` (<=40), `parent_post_no` (answers), `created_at`.

**Flow:** composer sends the normal multipart post plus `then_now=1`, `then_label`, `now_label`, `reply_to_post`, with exactly two `post_img[]` files (Then first). `ProfilePage::post` rejects anything but two photos, then `PostComparison::createForPost` copies the **server-stored** filenames from the post row. `FeedRepository::hydrateEngagement` batch-loads comparisons (one query) and attaches `comparison` with `answer_count`.

**UI:** stage with Then clipped over Now, native `<input type="range">` overlay (keyboard, touch and screen-reader support for free), 44px handle, "Side by side" toggle, "Add your Then & Now" button. Vanilla JS/Alpine, no new npm packages.

## 3. Blast radius
No shared-lib change. `post` untouched. New key `comparison` on feed items is additive. If the table is missing, hydration catches the error and the post renders as a normal two-photo post.

## 4. Failure modes
- One photo / three photos / video-only: 422-style `ValidationException`, nothing inserted.
- Comparison insert fails after the post is saved: logged, post stays as a normal two-photo post.
- Duplicate comparison for a post: UNIQUE key refuses it.
- Bogus `reply_to_post` (not a comparison): silently dropped to NULL.

## 5. Security
- **IDOR / path traversal:** the client never supplies image paths; filenames come from the post row written by the existing upload pipeline.
- Labels: `strip_tags`, trimmed, capped at 40, rendered with `x-text`.
- Visibility: comparison rides on the post, so feed family scoping applies unchanged.
- **PoC (Marcus/Dapo):**
  ```bash
  # Attempt to point a Then & Now at someone else's private photo
  curl -b cookies.txt -X POST https://familyplatform.test/member/profilePage/post \
    -F then_now=1 -F 'before_img=victim.jpg' -F 'post_img[]=@a.png' -F 'post_img[]=@b.png'
  # Expected: before_img is ignored (not a known field); row uses a.png/b.png as uploaded.
  # One file only -> error response, no post_comparisons row.
  ```

## 6. Observability
Structured log `then_now.created` (`post_no`, `user_id`, `linked`, `is_reply`). Alert if `linked=false` rate > 1% (Ayo).

## 7. Rollout / rollback
Additive migration, then deploy code. Rollback: redeploy previous code; table can stay (unused). Feature-flag not required: the entry point is a new opt-in composer button.

## 8. Definition of Done
- [x] `php -l` on touched PHP; PHPUnit `ThenAndNowComparisonTest` 7 tests / 20 assertions
- [x] Cypress `then_and_now.cy.js` written
- [ ] Peer review (Dami / Kenny); David gate; CHANGELOG + Playbook updated (see below)
