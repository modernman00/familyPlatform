# Changelog

## 2026-10-25 (conversation, not chat)
Decision: FamilyPlatform does **not** get 1-1 / 1-many private messaging (WhatsApp already owns it; no viral loop; realtime, moderation and GDPR cost). Conversation happens in the feed and shares out to WhatsApp instead.
### Added
- **Threaded replies on comments**: a Reply button on every comment; replies are indented under the top-level comment (a reply to a reply attaches to the top-level one, so threads are one level deep) and survive reloads. The replied-to author is notified. New nullable `comment.parent_comment_no` (migration `2026_10_25_comment_replies.sql`); the parent must be a live comment on the same post or the reply is refused before anything is saved. `CommentThreadService`.
- **@mentions in comments**: type `@First Last`; the server matches it against the post's family roster (never the client), notifies each mentioned member once (max 10, never yourself). Replying pre-fills the @mention.
- **Share on WhatsApp** button on every family announcement card.
### Fixed
- The comment send button stayed locked for up to 15s after posting (global submit spinner never released), blocking an immediate follow-up comment or reply.
### Removed
- Dead `/chat` route and the unfinished `App\classes\Chat` Ratchet stub (the controller it pointed at never existed). `NotificationWebSocketHandler` and the Ratchet dependency stay.
Tests: PHPUnit `CommentMentionTest`, `CommentThreadTest`, `AnnouncementWhatsappShareTest`, two reply cases in `SocialFeedTest`; Cypress `comment_replies.cy.js`.

## 2026-10-25 (one brand)
### Changed
- **One brand: the red "family" icon, in crimson.** The purple house mark, indigo/blue/violet UI and teal accents are gone. App icon, favicon, nav logo, wordmark ("Platform" crimson, "Family" ink), `theme_color`, buttons, links, focus rings, tree page, directory, auth, landing, static pages and email templates use crimson `#a31621` (white-on-crimson 7.8:1, AA). The raw icon red `#ff0000` is only 4.0:1 and was too loud for buttons and covers. Solid buttons are one setting: `--cta-bg` in `standards-v21.css`. Green stays for success, blue for male nodes in the family tree and Facebook/LinkedIn buttons.
- Tablet (768-1199px) now lays the profile page out in two columns instead of one long stack; phones get safe-area padding and a gutter on auth cards; coarse-pointer controls are at least 44px; headlines balance their wrapping.
- Service worker cache bumped to `v2.0.6` so installed apps drop the cached purple logo.
- **One typeface: Inter everywhere** (loaded once from `standards-v21.css`, and on every standalone page). Cinzel, Plus Jakarta Sans, Playfair, Georgia and system-first stacks are gone from pages, canvas exports (family tree flyer, WhatsApp flyer, avatars) and email templates. Top bar no longer wraps or overflows at 992-1199px.
- Landing nav and footer now show the same two-tone wordmark as the app (they said "Family Platform" in one colour).
- **App icon, favicons, PWA icons (Android, iOS, Windows), install banner and the wordmark tile are crimson `#a31621`**, matching the UI instead of the louder `#ff0000`.
### Removed
- `public/img/logo/logo-mark.svg` (the purple house; nothing referenced it).

## 2026-10-25
### Changed
- Feed load fetches comments for all posts in one query (`AllMembersData::commentProfilePicByPostNos`) instead of one per post: about 120 database queries per load down to 88, identical output.
### Added
- **GIFs in comments**: a GIF button sits in front of the emoji button in the comment box and opens the same curated family GIF catalog as the post composer (shared `gifCatalog.js`). A comment can be a GIF alone or text plus a GIF; the GIF renders in the bubble and reaches other members in real time. New nullable `comment.gif_url` (migration `2026_10_25_comment_gif_url.sql`); the server accepts only https Giphy hosts (`ProfilePage::sanitizeCommentGifUrl`), stricter than the post path because comments render in every member's feed. PHPUnit `CommentGifUrlTest`, Cypress `comment_gif.cy.js`, Playbook Stage 4.
## 2026-10-24 (family code rename)
### Added
- **Change the family code by family vote** (RFC-0005): the creator of a family code can propose a new one from Settings → Family Network. A poll opens in the family feed and everyone is notified; when voting closes with more Yes than No votes among those who voted (members who never vote do not count against it, but at least one member besides the creator must vote; over half of the whole family saying Yes applies it at once) the code changes for every member automatically (tree, posts, events, journals, invites, API key scopes) in one transaction and everyone is notified. Voting closes after 7 days, one change per 30 days. Old codes are kept as aliases so shared links, invites and live sessions keep working. Migration `2026_10_24_family_code_rename.sql`, `FamilyCodeRenameService`, Playbook section 4c, PHPUnit `FamilyCodeRenameTest`, Cypress `family_code_rename.cy.js`.
### Changed
- The family-code poll vote goes through `FamilyCodeRenameService` (members only, one choice, majority rule); ordinary polls are unchanged.

## 2026-10-24 (composer)
### Fixed
- Create Post: once photos were attached the **Post button was hidden** on phones (the PWA tab bar, z-index 9990, covered the modal's bottom edge and the button sat at the end of the scroll area). Post now lives in a sticky footer. The PWA tab bar is now hidden while **any** modal is open (`body.modal-open`), which also stops it covering the bottom of Edit Profile (Save button), Invite Family and Create Announcement.
### Changed
- Create Post redesigned as a bottom sheet on phones (dialog on desktop): swipeable topic chips, photo thumbnails in a horizontal strip with count, "Remove all" and a "+" tile, labelled 4-column "Add to your post" tool grid (all targets >= 44px), camera-roll prompt that hides once photos are attached. Element IDs unchanged. Cypress `post_composer_mobile.cy.js`.

## 2026-10-24
### Added
- **Birth / Maiden Family Code** (RFC-0004): Settings → Family Network lets a member **create** her birth family code from her maiden name (permanent, one per user) or **link** to an existing family via approval by one of its members. Her primary household code is never changed. New `family_codes` registry (migration `2026_10_24_family_codes_registry.sql`), `BirthFamilyCodeService`, `family_approval_requests.purpose`, Playbook section 4b, PHPUnit `BirthFamilyCodeTest`, Cypress `birth_family_code.cy.js`.
### Fixed
- `deleted_at` on `account`, `personal` and `contact` no longer auto-stamps on every UPDATE (RFC-0006, migration `2026_10_24_deleted_at_no_auto_stamp.sql`). Live members stamped as "deleted" could not approve requests and were hidden from Directory/Search; the 30-day erasure grace period could be silently extended. Join/link approver lookup now also checks `account.status`. Guard test `DeletedAtSchemaGuardTest`.
### Security
- `otherFamCode` was free text trusted as proof of membership: it routed parent/sibling writes into, and minted invite tokens for, any family whose code a user typed. It is now honoured only while the user holds an approved membership of that code (`user_families`); legacy unverified values show "Unverified" and are ignored.
### Changed
- `updateSecondaryFamilyCode` now only clears the code or re-selects a family the user already holds.

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
