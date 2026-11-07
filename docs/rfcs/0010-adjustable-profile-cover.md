# RFC-0010: Adjustable profile cover photo (focal point)

- **Author(s):** Claude (Victor/James lane)
- **Red team:** Claude (Marcus lane): checks listed in section 4
- **Status:** APPLIED TO DEV (2026-11-06). Production needs CTO pre-approval and executive sign-off.
- **Created Date:** 2026-11-06
- **Target Apps:** FamilyPlatform only. No change to `shared-lib`.
- **Tier:** 🔴 Tier 3, because it changes the schema. The change is two additive, nullable columns.

## 1. Problem & Value
Members can now upload a cover photo for their profile header (stored as `images` rows with `where_from = 'cover'`). The banner is about 3:1, so most photos get cropped, and a centred crop often cuts off faces. Members need to drag the photo to choose which part of it shows, as on Facebook or LinkedIn.

**Non-goals:** zoom or scale, cropping on the server, showing covers on other members' profile views.

## 2. Design
Store a focal point per cover as CSS `background-position` percentages, so the original file is never modified.

- **Schema** (`migrations/2026_11_06_images_cover_position.sql`): `images.cover_pos_x` and `images.cover_pos_y`, both `TINYINT UNSIGNED NULL`. NULL means centred (50/50).
  - **No backfill and no data rewrite.** The migration checks `information_schema` before each change, so running it twice is safe. MySQL has no `ADD COLUMN IF NOT EXISTS`.
  - **Rollback:** `ALTER TABLE images DROP COLUMN cover_pos_x, DROP COLUMN cover_pos_y;`
- **Read** (`CoverPhotoService::getCurrent`): returns the URL plus x and y, clamped to 0–100. If the columns are missing (the code deployed before the migration ran), it falls back to a centred cover instead of failing.
- **Write** (`POST /member/profilePage/coverImg/position`): requires a CSRF token. x and y must be numeric and are clamped to 0–100. The update is limited by both the row number and the session user's ID.
- **UI:** a reposition button on the banner turns on adjust mode. The member drags (mouse or touch) or uses the arrow keys (Shift for bigger steps), then presses Enter or Save, or Escape or Cancel. Adjust mode opens automatically after a new upload.

Other options I rejected:
- **Storing the position in `images.caption`.** The gallery shows captions, so the position would appear there.
- **Cropping on the server.** It loses the original photo, and the crop would be wrong when the banner is shown at a different width.

## 3. Rollout
1. Run the migration on production. It's safe before or after the code deploys.
2. Deploy the code.
3. No feature flag is needed. Members without a saved position see a centred cover.

## 4. Adversarial checks (run in dev 2026-11-06)
| Attack | Result |
|---|---|
| Non-numeric `x` | 422 "Invalid cover position." |
| `x=500, y=-40` | Stored clamped to 100 and 0 |
| Wrong CSRF token | 401, nothing written |
| Moving another member's cover | Not possible: the owner comes from the session and the UPDATE is also filtered by `id` |
| Saving a position with no cover | 422 "Add a cover photo before adjusting it." |
| Running the migration twice | No error, and nothing changes the second time |
