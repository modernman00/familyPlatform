# RFC-0007: Comment replies, @mentions and WhatsApp sharing (instead of private messaging)

- **Author(s):** Ryan Mitchell, Daniel (Social squad)
- **Peer reviewer:** Damilola Ogunleye (backend), Kehinde Akindele (frontend)
- **Status:** IMPLEMENTED IN DEV (2026-10-25) — production migration pending
- **Target Apps:** FamilyPlatform (Tier 2: schema change on `comment`)

## 1. Decision
Do not build 1-1 / 1-many messaging. WhatsApp already holds every family's group chat, DMs add no viral loop, and chat brings realtime infrastructure (the old Ratchet stub needs a persistent daemon), moderation, minors-safeguarding and GDPR retention duties. Revisit only if D30 retention shows members leaving to discuss platform content; then scope a per-event/per-memory "family room", not an inbox.

## 2. Design
1. `comment.parent_comment_no INT NULL` + index, added in place (`ALGORITHM=INPLACE, LOCK=NONE`). NULL = top-level. Always points at a top-level comment (reply-to-reply is flattened), so threads are one level deep and no recursion is needed.
2. `CommentThreadService::resolveParent` — parent must exist, not be deleted, and be on the same `post_no`; runs before the insert so a rejected reply leaves no orphan.
3. Mentions are extracted server-side from `@First Last` against `AllMembersEmailByFamCode(post family)`; the client never supplies ids. Cap 10, author excluded.
4. Notifications (in-app row + push) go to the parent author and mentioned members, one each; a mention wins over a reply. Failures are logged, never thrown.
5. Feed renders `threadedComments(post)` (roots in feed order, each followed by replies; orphans show as top-level). Realtime and reload use the same normaliser.
6. WhatsApp: `api.whatsapp.com/send?text=` link on announcement cards, fully `rawurlencode`d (title and message capped at 600 chars). Posts, journals, reels, the tree and the flyer already had it.
7. `FamilyAnnouncementController` stays the 1-many channel (one-way admin broadcast).

## 3. Blast radius & rollback
Additive nullable column; old code ignores it, new code treats NULL as top-level. Deleting a top-level comment leaves its replies, which then render as top-level. Rollback: drop the index and column (no data outside replies is lost). Dev backup of `comment` taken before the ALTER.

## 4. Tests
PHPUnit: `CommentMentionTest`, `CommentThreadTest` (including a simulated DB outage during notify), `AnnouncementWhatsappShareTest` (hostile markup), `SocialFeedTest` reply cases. Cypress: `comment_replies.cy.js`.

## 5. Follow-ups (Implemented 2026-10-25)
1. **@mention autocomplete in the composer and highlighted mention chips:**
   - Client-side typeahead in the comment composer triggered on `@`, filtering `opts.familyRoster` (with asynchronous fallback to `/allMembers/processApiData`). Supports full keyboard navigation (Up/Down/Enter/Tab/Esc) and touch-friendly selection.
   - Mentions render as highlighted mention chips (`.mention-chip`) across both post content and comments with strict HTML escaping prior to token replacement to prevent XSS.
2. **WhatsApp share for Flashback memories:**
   - Dedicated 1-tap WhatsApp sharing link (`whatsappMemoryUrl`) on Nostalgia Flashback cards in `memoriesMilestonesWidget.blade.php` and `mobileHighlightsTray.blade.php`, plus instant WhatsApp forwarding in the memory reflection modal.
3. **Collapse long threads:**
   - Progressive disclosure for threads containing $> 2$ replies. Initial 2 replies are displayed with a "View X more replies" toggle, expandable to reveal all replies with a "Collapse replies" toggle.
   - Automatically expands whenever replying to a thread or posting a new comment.
