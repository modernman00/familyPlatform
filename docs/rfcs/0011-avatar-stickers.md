# RFC-0011: Avatar stickers ("Me-stickers") in posts and comments

- **Author(s):** Claude (Victor/James lane)
- **Red team:** Claude (Marcus lane): checks listed in section 4
- **Status:** APPLIED TO DEV (2026-11-08). Production needs CTO pre-approval and executive sign-off.
- **Created Date:** 2026-11-08
- **Target Apps:** FamilyPlatform only. No change to `shared-lib`.
- **Tier:** 🔴 Tier 3. It adds a schema table and a new endpoint that serves content generated for each member.

## 1. Problem & Value
Members build a personal avatar in `/member/emoji-studio`, and it can perform reactions (hello, laugh, wow, love, nod, dance, point, clap). Today the avatar can only be used as a profile picture. Members want to drop *themselves* into a post or comment doing one of those reactions ("me clapping for Grandma's birthday"). Living emojis already work this way through `:id_3d:` tokens.

**Blocker:** `AtgEmojiService::saveAvatar()` stores only the rendered file (`profilePics.img`). The **traits** behind the avatar (skin, hair, face, outfit…) are not stored, so the server cannot redraw a member's avatar performing a different reaction.

**Non-goals:** stickers of the Living Photo avatar (they wrap a real portrait PNG, so they're heavier and raise more privacy questions; that is phase 2), using another member's avatar in your own post, and animated GIF or video export.

## 2. Design

### 2.1 Schema: `migrations/2026_11_08_member_avatar_traits.sql`
```sql
CREATE TABLE IF NOT EXISTS member_avatar_traits (
  user_id     VARCHAR(64)  NOT NULL PRIMARY KEY,   -- string IDs (Standard 21)
  traits      JSON         NOT NULL,               -- output of sanitiseTraits()
  mode        VARCHAR(20)  NOT NULL DEFAULT 'procedural',
  version     INT UNSIGNED NOT NULL DEFAULT 1,     -- bumps on every save → cache-busting
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
- The change is additive only; no existing table is touched. **Rollback:** `DROP TABLE member_avatar_traits;`
- No backfill is needed. If a member has no row, `AvatarStickerService` reads the traits embedded in their current procedural studio SVG (`data-atg-traits`). Living Photo SVGs (which contain `<image`) are skipped.

### 2.2 Write path
`saveAvatar()` also upserts `member_avatar_traits`, but only when `mode === 'procedural'`. It stores the traits after `sanitiseTraits()` and increments `version`. The extra write is wrapped in try/catch, so a missing table can never break the existing save.

### 2.3 Token & rendering
- **Token in text:** `:me_<reaction>@<userId>:`, for example `:me_clap@abc123:`.
  - `<reaction>` must be on the allowlist `hello|laugh|wow|love|nod|dance|point|clap`.
  - `<userId>` must match `[A-Za-z0-9_-]{1,64}` (dev has IDs containing `_` and `-`).
  - The **composer only ever writes the author's own ID**, and the server rejects a post whose `me_` token names anyone else (§4).
- **Endpoint:** `GET /api/emoji/avatar-sticker/[:userId]/[a:reaction]?v=<version>`
  - Requires a logged-in member (JWT session). It must **not** be in `$publicControllers` (Standard 22).
  - The requester must share a family code with `userId`. The check compares the owner's `personal.famCode` and approved `user_families` codes against `BaseController::sessionSharesFamily()`. Otherwise it returns 404, not 403, so it doesn't reveal who has an avatar.
  - The renderer calls `AtgAvatarRenderer::render($traits, 160, animated: true)` and adds an `<style>` block **inside the SVG**: the `.atg-av-react` keyframes for that one reaction, looping with a pause. That is needed because it's served as an `<img>` and the page CSS doesn't reach it.
  - Responses get `Cache-Control: private, max-age=86400`, an `ETag` of `userId:version:reaction`, and the existing CSP/SVG hardening (`Content-Type: image/svg+xml`, `X-Content-Type-Options: nosniff`).
- **Feed:** `renderHashtags()` maps a valid token to `<span class="atg-live-emoji atg-me-sticker"><img src="/api/emoji/avatar-sticker/...">`. The page never puts the SVG markup inline, so the SVG cannot run script in the page.

### 2.4 UI
- **Post composer and comment picker:** add a "🙋 Me" tab after "✨ Living". It shows the 8 reactions using the member's own avatar. If no traits are stored, it shows one card instead: "Save your avatar in Emoji Studio to unlock Me-stickers".
- **Emoji Studio:** add a "Use in a post" button next to Save. It opens the post composer with `:me_<current reaction>@<me>:` already in the text, so there's one path from the studio to the feed.
- Tapping a sticker in the feed uses `tapLive` (pop + burst). There's no AR viewer step for stickers.

Alternatives I rejected:
- **Inlining the avatar SVG in the post body.** It's large (about 30 KB per sticker), would grow with every post, would need a full SVG sanitiser, and old posts wouldn't update when the member changes their look.
- **Rendering a PNG per reaction when the avatar is saved.** That's 8 files per save, and there would be no animation.

## 3. Rollout
1. Migration on dev → PHPUnit `AvatarStickerTest` (access control, allowlist, ETag) → Cypress `avatar_sticker.cy.js`.
2. Production: run the migration first (it's safe ahead of the code), then deploy. Members who haven't saved since the deploy see the unlock prompt.
3. Update the playbook (`howToUse.blade.php`) in the same release (Standard 19).

## 4. Red team (Marcus) checks
| Attack | Mitigation |
| :--- | :--- |
| Post an `:me_*@<victim>:` token to put words in another member's mouth | Server-side `PostMessage`/comment save rejects or strips `me_` tokens whose ID ≠ session user ID |
| Find out which users exist or have avatars through the endpoint | Requires login and the same family; every other case returns 404 |
| Path traversal or injection through `reaction` or `userId` | Strict route regex plus the allowlist; the ID is only used in a prepared statement |
| Run script through the SVG | Served as `<img>` with `nosniff`; the renderer already escapes trait values (existing XSS suite) |
| Make the server render repeatedly (DoS) | ETag/304 and the 24-hour private cache; reuse the existing per-user rate limiter |
| Show stale or someone else's look after a change | `version` in the URL is bumped on every save |

## 5. Open questions for the CTO
1. Should stickers also be allowed in **public share cards** (`social-share.blade.php`)? Proposed: no, keep them family-only.
2. Should Living Photo stickers be included in phase 2? They need a separate consent step.

## 6. As built (2026-11-08)
- Migration `migrations/2026_11_08_member_avatar_traits.sql` has been applied to dev.
- **Service:** `app/services/AvatarStickerService.php`. Endpoints `meStickers` and `avatarSticker` are in `AtgEmojiController`, and the routes are in `app/router/emojiRoute.php`.
- **Ownership check:** enforced in `LinkPreviewService::sanitizePostMessage()`. Every post, comment, edit and reflection write passes through it, so one check covers them all.
- **Tests:** `tests/Feature/AvatarStickerServiceTest.php` (8 tests) and `cypress/e2e/avatar_sticker.cy.js` (3 tests).
- **Plain-text surfaces:** `EmojiPlainText::humanize()` turns sticker tokens into their glyph (e.g. `:me_clap@ID:` → 👏) and living-emoji tokens into ✨. It is applied in `NotificationOrchestrator::dispatch()`, `PushNotificationClass::sendPushNotification()` and `PostDto::deriveHookFromMessage()`.

## Amendment (2026-11-08): sticker look without changing the profile photo

Members who keep a real profile photo had no traits row, so "Use in a post" landed on the unlock card.
`POST /api/emoji/me-stickers/look` (CSRF, own user only) now stores the on-screen studio traits in
`member_avatar_traits` without touching `profilePics`; the studio's *Use in a post* button calls it before
opening the composer. The unlock card spans the full picker grid.

## Amendment (2026-11-08): photo Me-stickers (the member's real portrait)

The cartoon avatar did not look like the member, so stickers can now be built from the profile photo itself.
- `AvatarStickerService::getStickerSource()` picks the look. If `member_avatar_traits.mode` is `procedural`, the cartoon is used. In every other case a real profile photo (jpg/png/webp, or the PNG inside a Living Photo) is used, and the cartoon is the fallback.
- `renderPhotoSticker()` embeds a square JPEG crop of the photo as a data URI. It honours EXIF orientation and is cached in `storage/cache/me-stickers/`. **The pixels are never stylised.** The reaction is acted out around the photo: hands tinted with the member's own skin tone (taken from the photo with a YCbCr skin median) clap, wave, point or go up to the cheeks for *wow*, and there are tears of joy, hearts, a thumbs-up and music notes.
- Studio: a *📸 My photo / 🎨 Cartoon* switch above the stage. In photo mode the reaction buttons show the real sticker art (`?look=photo`, owner only). *Use in a post* saves `mode=photo`.
- Feed and composer sticker URLs moved from `?v=live` to `?v=live2`. Browsers had cached `?v=live` for 24 hours under the old `max-age`, which is why the feed showed an old look.
- Living Photo privacy (§5 Q2): a photo sticker is served only under the same family gate as the profile photo, which the same family can already see.

## Amendment (2026-11-08): face zoom for photo Me-stickers
- **Detection runs in the member's browser** in Emoji Studio, using MediaPipe Tasks Vision `@mediapipe/tasks-vision@0.10.14` with the BlazeFace short-range model on the CPU delegate. The photo never leaves the device for this step. Only the face box (four fractions) is POSTed to `/api/emoji/me-stickers/face` (CSRF-checked, own photo only).
- If the first pass finds nothing, a second pass looks at the top-centre crop, which catches small faces in full-length shots. If there is still no face, nothing is stored and the sticker keeps the full-photo crop. Books, backs and screenshots return no face.
- The box is stored at `storage/cache/me-stickers/faces/<md5(path:mtime)>.json`, so a new photo automatically needs fresh detection. It is folded into the sticker `version` so caches refresh.
- **CSP:** on `/member/emoji-studio` only, `script-src` gains `'wasm-unsafe-eval'` (it allows WebAssembly compilation, not JS `eval`) and `connect-src` gains `https://storage.googleapis.com` (the model). The rest of the site is unchanged. This is done in `app/config/init.php`, not in shared-lib.
- **Cost:** the first detection downloads about 9.4 MB of WASM plus a 230 KB model from jsDelivr and Google. The browser caches them, and detection runs only once per photo.
- A pure-PHP skin-colour heuristic was tried first and rejected: it cropped to beige trousers and hands.
