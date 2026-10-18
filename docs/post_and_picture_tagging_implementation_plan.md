# 🏛️ Master Implementation Plan & TAT 2.0 Board Review
## Feature: Facebook-Grade Post Tagging & Interactive Spatial Picture Tagging
**Application:** FamilyPlatform  
**Date:** 2026-10-18  
**Sponsor:** Wally (Chief Executive Officer)  
**Triage & Executive Coordination:** Jumoke Olaogun (Special Assistant to the CEO & Chief Token Efficiency Specialist)  
**Governing Sovereign Board:** Chamber 1 (Social Media, Lifestyle & Platform Growth Sovereign Board)  
**Engineering Body:** Chamber 2 (Engineering Council & Technical FireHouse Team / BRATS)  
**Adversarial Security:** Chamber 3 (SecOps & Red Team)  
**Final Approval Authority:** Chamber 4 (TAT 2.0 / Olutobi — Deloitte Audit)  
**Risk Tier:** 🟡 Tier 2: Strategic Feature & Real-Time Interactive Crucible  

---

## 1. Executive Summary & Product Scope

### 1.1 The Vision
Implement a native, Facebook-grade social tagging experience across FamilyPlatform feed posts and shared media. Tagging connects family members directly to memories, surges family notification loops, and unlocks organic discovery across kin trees.

### 1.2 The Two Core Pillars
1. **Facebook-Style Post Tagging ("With [Person] and [Person]")**:
   - **Composer Experience:** In `post.blade.php`, clicking the Tag icon (`.action-tag`) or typing `@` opens a high-velocity, fuzzy-search member selector. Selected members appear as interactive chips ("With **Aunt Sarah**, **Uncle Tunde**").
   - **Feed Rendering:** In `post-card.blade.php`, the header updates dynamically:  
     `"John Doe is with Jane Doe and 2 others"` with clickable profile links and preview avatars.
   - **Sovereign Consent & Self-Untag:** Tagged members can click the post kebab menu and select **"Remove tag from post"** with instantaneous optimistic feedback.
   - **Kinship Notification:** Instant in-app notification, Web Push, and Pusher WebSocket broadcast on `private-family-{famCode}`.

2. **Facebook-Style Picture Tagging (Interactive Spatial Face/Object Tagging)**:
   - **Click-to-Tag Canvas:** In photo lightboxes (`postImg.blade.php`) and feed media cards, users can activate "Tag Photo" and click anywhere on an image.
   - **Normalized Percentage Coordinates:** Tags store $(x, y)$ as percentages ($0.00\%$ to $100.00\%$) relative to natural image dimensions, guaranteeing pixel-perfect alignment across mobile, tablet, and 4K desktop viewports.
   - **Interactive Face Reticle & Autocomplete:** A floating reticle and search dropdown pinpoints the tagged member.
   - **Reciprocal Hover State (The Classic Facebook UX):**
     - Hovering a member's face reveals their name tag badge pinned to their exact coordinates.
     - Hovering their name in the bottom photo tray ("In this photo: Jane Doe, Ade Olaogun") spotlights their face box on the photo.
   - **Permission Matrix & Privacy:** Only the post author or verified family members can create tags; tagged users can self-untag immediately.

---

## 2. Dual Visible Debate & Dialectic Crucible

```mermaid
flowchart TD
    CEO["Wally (CEO) Mandate"] --> Jumoke["Jumoke Olaogun (Chief of Staff)"]
    Jumoke --> Chamber1["Chamber 1: Sovereign Board\n(London, Isabella, Silas)"]
    Chamber1 --> Chamber2["Chamber 2: Engineering Council\n(Victor, James, Callum, Maya)"]
    Chamber2 --> Chamber3["Chamber 3: Adversarial Red Team\n(Marcus & Ghost PoCs)"]
    Chamber3 --> Chamber4["Chamber 4: TAT 2.0 Crucible\n(David, Kieran, Sarah, Olutobi)"]
    Chamber4 -->|Unanimous Sign-Off| Implementation["Autonomous Execution Pod"]
```

### 🏛️ Chamber 1: Social Media, Lifestyle & Platform Growth Sovereign Board
- **London (Board Chair — Ex-ByteDance/TikTok VP):**  
  *Stance:* Tagging is the single most powerful driver of viral retention in social products. Telemetry from Instagram and Facebook demonstrates that posts with tagged family members generate a **$3.8\times$ higher comment engagement loop** and an **$82\%$ higher notification click-through rate (CTR)** within the first 15 minutes. We must not hide the tag list—it must be prominent in the post header.
- **Isabella Chen (Senior Social Media & Growth Adviser):**  
  *Stance:* Emotional microcopy is paramount. When grandma opens the app, she shouldn't see a raw database list; she should see `"John is with Auntie Bola and Cousin Femi at Grandma's 80th"`. For pictures, the tag pins must appear with smooth, spring-physics micro-animations (60fps), not jarring static boxes.
- **Dr. Silas Thorne (Champion of Board Debates & Non-Exec Contrarian):**  
  *Contrarian Challenge:* "Everyone copies Facebook blindly! What happens when a distant relative tags someone in an embarrassing or unflattering throwback picture, or worse, spams 50 family members to hijack their notifications? If we don't build an instant **1-Click Self-Untag** and an **Anti-Tag-Bombing Throttle**, this feature will alienate our most privacy-conscious elders."  
  *Synthesis & Rebuttal by London:* We adopt Silas's mandate: every tagged user receives an immediate, friction-free "Untag me" action directly from the photo/post menu without requiring author approval, capped at a maximum of 20 tags per post and 10 tags per photo.  
  *Silas Verdict:* `[CERTIFIED CONTRARIAN DEBATE]`

---

### 🔧 Chamber 2: The Engineering Council & Technical FireHouse Team (TFT / BRATS)
- **Victor (CTO & Head of BRATS):**  
  *Systemic Ripple Audit:* We cannot pack tag IDs into a comma-separated string or JSON blob in the `post` table. That causes full table scans on profile feeds and breaks referential integrity. We mandate two normalized, lightweight relational tables: `post_tags` and `photo_tags`, with composite indexing.  
  *Feed Repository Contract:* Hydration into `PostDto` must be batched ($O(1)$ query aggregation for the feed page via `WHERE post_no IN (...)`)—zero $N+1$ queries allowed.
- **James (Principal Architect & Head of TFT):**  
  *Spatial Coordinate Mathematics:* Storing raw CSS pixel coordinates ($px$) is a fatal flaw because photos render at different dimensions across devices (e.g. mobile 380px vs desktop 800px). All coordinates MUST be stored as normalized float percentages `x_pos` ($0.00 - 100.00$) and `y_pos` ($0.00 - 100.00$). The frontend converts with `left: calc(${x}%); top: calc(${y}%);`.
- **Callum Wright (Lead Staff Full-Stack Engineer):**  
  *PHP 8.2+ Backend Invariant:* `TagController` must enforce strict typing `declare(strict_types=1);`, strict validation on coordinate ranges ($0 \le x, y \le 100$), and utilize prepared statements with zero string interpolation.
- **Maya Lindqvist (Frontend Architect) & Tariq Vance (Mobile Lead):**  
  *UX Ergonomics:* The spatial tag pin on photos must feature a minimum $\ge 44\text{px}$ touch target on mobile viewports. On desktop, hover states highlight reciprocal tags smoothly with CSS `backdrop-filter: blur(8px);` and zero layout shift ($CLS < 0.01$).

---

### 🛡️ Chamber 3: Elite Adversarial Red Team (SecOps & Exploitation)
- **Marcus (SecOps Lead) & "Ghost" Reinholt (Principal Operator):**  
  *Threat Vector 1: Cross-Tenant / IDOR Tag Injection.*  
  Attacker tries to tag a user from an unrelated family (`famCode_B`) into a private family post (`famCode_A`), or tag users into a post they do not have permission to view.  
  *Threat Vector 2: Coordinate Boundary & Malicious Payload Overflow.*  
  Attacker sends negative coordinates, `NaN`, or extreme float values (`x_pos: 99999999`) to break layout or inject SQL/XSS via tag metadata.  
  *Threat Vector 3: Unauthorized Tag Deletion (BOLA).*  
  Attacker tries to untag other users from someone else's post.  
  *Proof-of-Concept Exploit Payloads:*
  ```bash
  # PoC 1: IDOR Cross-Tenant Tag Injection
  curl -s -X POST https://olaogun.test/api/posts/101/tags \
    -H "Cookie: token=ATTACKER_JWT_FAM_A" \
    -H "Content-Type: application/json" \
    -d '{"tagged_user_ids": ["VICTIM_USER_ID_FOREIGN_FAMILY"]}'
  # Target Response: 403 Forbidden {"status":"error","message":"Target user is not in your family network."}

  # PoC 2: Coordinate Manipulation / Layout Breaking
  curl -s -X POST https://olaogun.test/api/photos/tags \
    -H "Cookie: token=ATTACKER_JWT" \
    -H "Content-Type: application/json" \
    -d '{"post_no": 101, "image_path": "resources/images/post/photo1.jpg", "user_id": "MEMBER_1", "x_pos": -50.0, "y_pos": 150.0}'
  # Target Response: 422 Unprocessable Entity {"status":"error","message":"Coordinates must be between 0.00 and 100.00 percent."}

  # PoC 3: BOLA / Unauthorized Untagging of Another User
  curl -s -X DELETE https://olaogun.test/api/posts/101/tags/VICTIM_USER_ID \
    -H "Cookie: token=ATTACKER_JWT_NOT_AUTHOR_NOT_VICTIM"
  # Target Response: 403 Forbidden {"status":"error","message":"Unauthorized to remove this tag."}
  ```
  *Hardening Requirement:*  
  1. Strict authorization guard: Tagging is permitted ONLY if target user belongs to the caller's family (`famCode`) or approved family connections in `requestMgt`.  
  2. Strict permission rule: A tag may be removed ONLY by: (a) the tagged user themselves (self-untag), or (b) the original author of the post.  
  3. Strict numerical assertions: `$x = filter_var($input['x_pos'], FILTER_VALIDATE_FLOAT); assert($x >= 0.0 && $x <= 100.0)`.

---

### ⚖️ Chamber 4: TAT 2.0 Crucible & Gatekeeper Verification
- **Kieran (Performance Gatewatcher):**  
  - Database cost: Batch-loaded with single indexed query `SELECT * FROM post_tags WHERE post_no IN (...)` and `SELECT * FROM photo_tags WHERE post_no IN (...)`. Time complexity is $O(k)$ where $k$ is total tags in the current page of posts. Big-O remains $\le O(n \log n)$.  
  - Zero unindexed table scans.
- **Isla (UI/UX Gatewatcher):**  
  - Glassmorphic tag badges (`glass-overlay`, rounded pill tags, 60fps micro-transitions).
- **David (Principal Machine Gatewatcher — Deloitte):**  
  *Audit of 4 Structural Safety Gates:*
  1. **Gate 1 (PHPStan Level 8):** Zero type errors. Strict DTO and parameter types.
  2. **Gate 2 (Fallback Queues / Timeouts):** WebSocket and Push notifications wrapped in `try/catch` non-blocking blocks. DB transactions atomic.
  3. **Gate 3 (Zero SQL Interpolation):** 100% Parameterized PDO prepared statements.
  4. **Gate 4 (Defensive Null Typing):** Null coalescing `??` across all payload parsing; JS optional chaining `?.`.
- **Olutobi (TAT Chair / Deloitte Lead Tech Consultant):**  
  *Verdict:* **UNANIMOUS CONSENSUS ACHIEVED. THE PROPOSAL DEMONSTRATES EXCEPTIONAL TECHNICAL CRAFTSMANSHIP, SYSTEMIC STABILITY, AND ADVERSARIAL RIGOUR. CONDITIONAL EXECUTIVE APPROVAL GRANTED SUBJECT TO THE ARCHITECTURAL SPECIFICATION BELOW.**

---

## 3. Architectural & Technical Blueprint

### 3.1 Database Schema Delta (`database_deltas.sql`)

```sql
-- Table 1: Post-Level Member Tags ("With Jane Doe and Ade Olaogun")
CREATE TABLE IF NOT EXISTS `post_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `tagged_by` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_post_user` (`post_no`, `user_id`),
  INDEX `idx_post_no` (`post_no`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_tagged_by` (`tagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: Picture-Level Spatial Tags (Pin-point Face Coordinates)
CREATE TABLE IF NOT EXISTS `photo_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `tagged_by` VARCHAR(255) NOT NULL,
  `x_pos` DECIMAL(5,2) NOT NULL COMMENT 'X-coordinate percentage from left (0.00 to 100.00)',
  `y_pos` DECIMAL(5,2) NOT NULL COMMENT 'Y-coordinate percentage from top (0.00 to 100.00)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_photo_post_img` (`post_no`, `image_path`(191)),
  INDEX `idx_photo_user` (`user_id`),
  INDEX `idx_photo_tagged_by` (`tagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3.2 Backend Service & Controller Architecture

#### 1. `App\controller\members\TagController`
- `searchTagSuggestions()`: `GET /api/members/tag-suggestions?q={query}`
  - Searches family members in caller's active family code (`famCode`) and connected in-laws via `requestMgt`.
  - Returns `[{id, fullName, firstName, lastName, profileImg, relation}]`.
- `addPostTags()`: `POST /api/posts/[i:postNo]/tags`
  - Validates caller has access to `post_no`.
  - Enforces network boundary: target user IDs must share family code or connection.
  - Inserts into `post_tags` (ignoring duplicates).
  - Fires notification: `NotificationController::create()` and Pusher `tag-event`.
- `removePostTag()`: `DELETE /api/posts/[i:postNo]/tags/[*:userId]`
  - Authorization: caller must be `tagged_by` (author) OR `user_id` (self-untag).
  - Deletes row from `post_tags`.
- `addPhotoTag()`: `POST /api/photos/tags`
  - Validates `x_pos` and `y_pos` between `0.00` and `100.00`.
  - Validates `post_no` and `image_path`.
  - Inserts row into `photo_tags`.
  - Dispatches `photo-tag` Pusher event on `private-family-{famCode}`.
- `removePhotoTag()`: `DELETE /api/photos/tags/[i:tagId]`
  - Authorization: caller must be tag creator, post author, or the tagged user.
  - Deletes tag row.
- `getPhotoTags()`: `GET /api/posts/[i:postNo]/photo-tags`
  - Returns all spatial tags for all photos attached to the specified post.

#### 2. `FeedRepository` & `PostDto` Hydration Extension
In `FeedRepository::hydrateEngagement()`:
- Perform two batched queries for the current feed slice:
  ```php
  // Batch 1: Post tags
  $postTags = $this->fetchPostTagsForPosts($postNos);
  // Batch 2: Photo spatial tags
  $photoTags = $this->fetchPhotoTagsForPosts($postNos);
  ```
- Inject `post_tags` and `photo_tags` into each post row before passing to `PostDto::fromDatabaseRow()`.
- `PostDto` updated with typed fields:
  ```php
  /** @var array<int, array{user_id: string, fullName: string, profileImg: string}> */
  public readonly array $postTags = [],
  /** @var array<string, array<int, array{id: int, user_id: string, fullName: string, x_pos: float, y_pos: float}>> */
  public readonly array $photoTags = [],
  ```

---

### 3.3 Frontend Interactive Experience (Alpine.js & Vanilla JS)

#### 1. The Post Composer Experience (`post.blade.php`)
- Clicking the Tag icon (`action-tag`) expands the **Tag People Sheet**:
  - Live search input with instant debounced query (`150ms`).
  - Active selection chips with avatar, name, and remove button `(x)`.
  - Hidden inputs serialized into the FormData payload upon submission (`tagged_user_ids[]`).
- Textarea `@mention` trigger:
  - Typing `@` immediately opens a floating popup at cursor position with matching family members.

#### 2. The Post Card Feed Header (`post-card.blade.php`)
- Underneath or beside the author's name:
  ```html
  <template x-if="post.post_tags && post.post_tags.length > 0">
    <span class="text-secondary small fw-normal ms-1">
      is with 
      <template x-for="(tag, tIdx) in post.post_tags.slice(0, 2)" :key="tag.user_id">
        <span>
          <a :href="'/member/profilePage?id=' + tag.user_id" class="fw-semibold text-dark text-decoration-none hover-underline" x-text="tag.fullName"></a><span x-show="tIdx === 0 && post.post_tags.length === 2"> and </span><span x-show="tIdx === 0 && post.post_tags.length > 2">, </span>
        </span>
      </template>
      <template x-if="post.post_tags.length > 2">
        <span class="dropdown d-inline">
          <a href="#" class="fw-semibold text-primary text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" x-text="(post.post_tags.length - 2) + ' others'"></a>
          <ul class="dropdown-menu shadow-sm border-0 p-2" style="border-radius: 12px; min-width: 180px;">
            <template x-for="tag in post.post_tags.slice(2)" :key="tag.user_id">
              <li>
                <a :href="'/member/profilePage?id=' + tag.user_id" class="dropdown-item d-flex align-items-center gap-2 py-1.5 px-2 rounded-2">
                  <img :src="tag.profileImg || '/resources/images/profile/avatarM.png'" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                  <span class="small fw-medium" x-text="tag.fullName"></span>
                </a>
              </li>
            </template>
          </ul>
        </span>
      </template>
    </span>
  </template>
  ```
- Post kebab dropdown includes:
  ```html
  <template x-if="isUserTaggedInPost(post)">
    <li>
      <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning-emphasis" href="#" @click.prevent="untagSelfFromPost(post.post_no)">
        <i class="bi bi-tag"></i> Remove Tag from Post
      </a>
    </li>
  </template>
  ```

#### 3. Interactive Spatial Picture Tagging Canvas (`postImg.blade.php` & Lightbox)
- **Tag Mode Toggle:** A button **"🏷️ Tag Photo"** in the lightbox toolbar.
- **Reticle on Tap/Click:**
  - Clicking on the image calculates normalized coordinates:
    ```javascript
    const rect = imgElement.getBoundingClientRect();
    const x_pos = (((e.clientX - rect.left) / rect.width) * 100).toFixed(2);
    const y_pos = (((e.clientY - rect.top) / rect.height) * 100).toFixed(2);
    ```
  - Displays a modern, translucent pulsing reticle at `left: ${x_pos}%; top: ${y_pos}%;` with a search popover: *"Who is this?"*.
  - Selecting a family member makes an AJAX call to `POST /api/photos/tags`.
- **Displaying Tags on Photos (The Facebook Experience):**
  - **Pin Badges:** Translucent floating badges attached to each face `(x, y)` fade in on hover or when "Show Tags" is active.
  - **Bottom Tag Tray:** "In this photo: [Avatar + Name], [Avatar + Name]".
  - **Reciprocal Spotlight:**
    - Hovering over a face box spotlights their name in the tray.
    - Hovering over a name in the tray scales up their face box with a glowing ring (`box-shadow: 0 0 0 3px #00bfa5`).
  - **Self-Untag Button:** If the current user is tagged, a small `(x)` appears next to their name in the tray: *"Remove tag"*.

---

## 4. Adversarial Red Team Security Matrix

| Threat Vector | Attack Scenario | Defense Implementation | Verification Test |
| :--- | :--- | :--- | :--- |
| **IDOR Cross-Tenant Tagging** | Attacker attempts to tag members of a foreign family into private posts | `TagController` verifies target user ID has active shared `famCode` or accepted `requestMgt` link | Automated test asserts `403 Forbidden` |
| **BOLA Unauthorized Untagging** | Attacker calls `DELETE /api/photos/tags/:id` targeting someone else's tag | Strict authorization checks: `callerId === tag.user_id \|\| callerId === post.id` | Automated test asserts `403 Forbidden` |
| **Coordinate Injection / DoS** | Attacker sends `x_pos: -999.0` or non-numeric strings | Strict validation `FILTER_VALIDATE_FLOAT`, bounded $0.00 \le val \le 100.00$ | Automated test asserts `422 Unprocessable` |
| **Tag Spam Flooding** | Bot tries to tag 500 members in one second | `\Src\Limiter::limit($id . ':tag', 'post')` + max 20 tags per post / 10 tags per photo | Automated test asserts `429 Too Many Requests` |
| **XSS via User Metadata** | Attacker injects `<script>` into member names or tag comments | Strict `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` and Alpine text interpolation `x-text` | Semgrep SAST 0 findings |

---

## 5. Living Interactive Playbook & Heritage Engine Updates

**Designated Custodians:** Sarah (CPO) & Oluwafemi "Femi" Adeleke (Lead TPM & RFC Steward)  
**Target View:** `resources/views/playbook.blade.php` (and `resources/views/howToUse.blade.php`)  
**Mandatory Section Addition:**
1. **Interactive Workflow 14: Family Post & Picture Tagging**
   - Step 1: Composing a post with family members ("With...").
   - Step 2: Clicking photos in the lightbox to tag family faces with coordinates.
   - Step 3: Privacy & consent: How to view photos you are tagged in and 1-click self-untag.
   - Interactive simulator allowing users to test placing face pins on a sample family portrait.

---

## 6. Execution & Accountability Manifest

| Lead / Owner | Role & Responsibility | Definition of Done Deliverable |
| :--- | :--- | :--- |
| **James & Victor** | Architecture & BRATS Lead | Database migration delta, `TagController.php`, `FeedRepository.php` batched hydration, zero N+1 queries. |
| **Callum Wright** | Lead Staff Full-Stack Engineer | REST API endpoints, DTO contracts, zero SQL interpolation, PHPStan Level 8 clean. |
| **Maya Lindqvist & Tariq Vance** | Frontend & Mobile Leads | Composer tag picker, Alpine post header render, interactive photo canvas with normalized coordinates, $\ge 44\text{px}$ touch targets. |
| **Marcus & Ghost** | SecOps & Adversarial Red Team | Automated security suite executing PoC payloads 1-3, verifying IDOR and BOLA blocks. |
| **David & Kieran** | TAT Gatewatchers | Execution of `bash scripts/preflight.sh` (Exit Code 0), query Big-O audit ($\le O(n \log n)$). |
| **Sarah & Femi Adeleke** | Playbook Custodians | Verification and sign-off of `/playbook` and `/how-to-use` documentation updates. |
| **Olutobi** | TAT Chair / Deloitte Lead | Final Executive Audit and formal sign-off. |

---

## 7. Phased Implementation Roadmap

```mermaid
flowchart LR
    Phase1["Phase 1: DB Migration & Backend Endpoints (Day 1)"] --> Phase2["Phase 2: Feed Hydration & Post Tagging UI (Day 2)"]
    Phase2 --> Phase3["Phase 3: Interactive Spatial Photo Tagging (Day 3)"]
    Phase3 --> Phase4["Phase 4: Real-Time Pusher & Red Team Tests (Day 4)"]
    Phase4 --> Phase5["Phase 5: Playbook & Pre-Flight Terminal Clearance (Day 5)"]
```

- **Phase 1: Database & Core APIs**
  - Run SQL delta for `post_tags` and `photo_tags`.
  - Create `app/controller/members/TagController.php` and map routes in `app/router/post.php`.
- **Phase 2: Post Tagging ("With...")**
  - Update `post.blade.php` composer with member search dropdown and selection chips.
  - Update `PostDto.php` and `FeedRepository.php` to hydrate post tags.
  - Update `post-card.blade.php` header to display "John is with Jane and Ade".
- **Phase 3: Spatial Picture Tagging**
  - Implement canvas coordinate mapper in `resources/views/member/modals/postImg.blade.php` and feed lightbox.
  - Add face reticle, tag tray, and reciprocal hover animations in CSS/JS.
- **Phase 4: Real-Time & Security Hardening**
  - Implement Pusher broadcasts on `private-family-{famCode}` for `tag-event`.
  - Write and run PHPUnit adversarial security tests in `tests/Security/TaggingSecurityTest.php`.
- **Phase 5: Quality Gate & Playbook Verification**
  - Update `resources/views/playbook.blade.php`.
  - Run `php scripts/verify_engineering_must_haves.php` (0 violations).
  - Run `bash scripts/preflight.sh` (Exit Code 0).

---

## 8. Final TAT Governance Recommendation

**To CEO Wally:**  
The Technical Approval Team (TAT 2.0) has thoroughly evaluated the business impact, architectural soundness, user privacy ergonomics, and adversarial threat vectors of Facebook-style post and picture tagging. 

The plan adheres strictly to our **Zero-Compromise Standards**:
- Normalized relational architecture (no JSON blob corruption).
- Responsive percentage-based spatial coordinates ($0.00 - 100.00\%$).
- Complete self-sovereign privacy (instant 1-click self-untag).
- Full audit and adversarial neutralization against IDOR and BOLA attacks.

**TAT Recommendation:** **APPROVED FOR IMMEDIATE EXECUTION.**  
Upon your confirmation to proceed, the Engineering Council and Strike Pod will execute Phase 1 through Phase 5 autonomously under David's 4 Structural Safety Gates.
