# 🎨 Design System: Dual-Form Architecture (Instagram Mobile & Facebook Desktop)

> **Status:** Live Specification & Active Architecture  
> **Target Applications:** FamilyPlatform PWA (Mobile) & Web Platform (Desktop/Tablet)  
> **Governing Standards:** Global Design & UI/UX Standards v2.1, WCAG AA 2.1, British English (en-GB) Invariant

---

## 1. Executive Summary & Dual-Form Philosophy

FamilyPlatform connects generations through a high-frequency, private family social network. To bridge younger, mobile-first family members with multi-generational desktop users, the design system implements a **Dual-Form Architecture**:

1. **Mobile / PWA Experience (Instagram Mirror):**  
   An ultra-clean, immersive, photo-centric, single-column feed. Prioritises tactile gesture control, edge-to-edge photography, top moments/stories reels, floating micro-interactions, and a thumb-friendly bottom navigation bar.
2. **Desktop / Web Experience (Facebook Mirror):**  
   A structured, information-dense 3-column layout. Prioritises family lineage navigation, universal top masthead with search, a prominent multimedia post composer, sticky family milestone/birthday sidebars, and active family member presence.

Both experiences share a **unified tokenised design engine**, ensuring consistent typography, iconography, elevation, colours, and interaction states across all viewports.

---

## 2. Atmosphere, Colour System & Design Tokens

### 2.1 Dual-Theme Support
- **Dark Mode (Default & Preferred for PWA):** Immersive True Black (`#000000`) for OLED power savings, cinematic media contrast, and premium tactile feel.
- **Light Mode (Default for Desktop / Facebook-style):** Crisp, warm surface tones (`#F0F2F5` canvas with `#FFFFFF` cards) that reduce eye fatigue during extended reading and family tree exploration.

### 2.2 Core Colour Palette & CSS Variables

| Token Name | Dark Mode (Instagram Mirror) | Light Mode (Facebook Mirror) | WCAG AA Ratio | Role / Usage |
| :--- | :--- | :--- | :--- | :--- |
| `--bg-canvas` | `#000000` (True Black) | `#F0F2F5` (Subtle Warm Grey) | — | Main window/body backdrop |
| `--bg-surface` | `#121212` / `#161616` | `#FFFFFF` (Pure White) | — | Post cards, sidebars, modals |
| `--bg-surface-elevated` | `#262626` | `#E4E6EB` | — | Input pills, dropdowns, hover states |
| `--text-primary` | `#F5F5F5` | `#050505` | 14.8:1 (AAA) | Headings, usernames, primary copy |
| `--text-secondary` | `#A8A8A8` | `#65676B` | 5.8:1 (AA) | Timestamps, metadata, captions, icons |
| `--text-muted-aa` | `#9E9E9E` | `#475569` | 7.6:1 (AA) | Tooltips, secondary helper text |
| `--border-subtle` | `#262626` | `#CED0D4` / `#E4E6EB` | 3.2:1 | Card borders, dividers, top/bottom bars |
| `--border-strong` | `#363636` | `#BEC3C9` | 4.8:1 | Input borders, active outlines |
| `--accent-blue` | `#0095F6` | `#1877F2` | 4.6:1 (AA) | Primary links, verified ticks, action pills |
| `--accent-heart` | `#ED4956` / `#FF3040` | `#E41E3F` | 4.5:1 (AA) | Likes, love reactions, urgent alerts |
| `--badge-gold` | `#FFD700` | `#B45309` | 4.6:1 (AA) | Elders, Family Head, Milestones |

> [!IMPORTANT]
> **Inviolable Red Surface Rule:**
> Black or dark text (`#000000`, `#111827`, `text-dark`) must **never** be placed over a red or crimson surface (e.g. notification badges, like buttons). All red surfaces strictly use crisp white (`#FFFFFF`) or high-contrast metallic gold (`#FFD700`).

---

## 3. Typography & Defensive Text Layouts

### 3.1 Font Stack
```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
```

### 3.2 Strict 5-Step Type Scale
All typography adheres to the disciplined type scale defined in `public/css/standards-v21.css`:
- **Display / Milestones (`--text-display`):** `2.5rem` (`40px`), `font-weight: 800`, `line-height: 1.15`. Emotional milestones and celebrations.
- **Section Headers (`--text-2xl`):** `1.75rem` (`28px`), `font-weight: 700`, `line-height: 1.25`. Modal titles, lineage tree headers.
- **Card Titles & Usernames (`--text-lg`):** `1.125rem` (`18px`) on Desktop, `0.9375rem` (`15px`) on Mobile, `font-weight: 600`, `line-height: 1.3`.
- **Standard Body & Captions (`--text-base` / `--text-sm`):** `0.875rem` (`14px`) to `1rem` (`16px`), `font-weight: 400`, `line-height: 1.45`.
- **Timestamps, Badges & Captions (`--text-xs`):** `0.75rem` (`12px`), `font-weight: 500`, `line-height: 1.4`.

### 3.3 Text Layout Defensive Invariants
- **Zero Mid-Word Truncation:** Usernames and captions must never be awkwardly truncated. On mobile touch viewports, use `line-clamp-2` or `line-clamp-3` with a native "more" tap-to-expand trigger. On desktop pointer devices, use `text-overflow: ellipsis` accompanied by a native `title="..."` attribute.
- **Button Label Integrity:** Interactive pill buttons and tabs must enforce `white-space: nowrap; flex-shrink: 0;`. Parent containers support horizontal swipe/scroll (`overflow-x-auto scrollbar-none`).
- **Universal British English (en-GB):** All copy, tooltips, placeholders, and error messages strictly use British English (e.g. *organise*, *optimise*, *customise*, *favour*, *honour*, *colour*, *behaviour*, *centre*).

---

## 4. Layout Architecture & Responsive Breakpoints

```mermaid
graph TD
  Viewport{Screen Width}
  Viewport -->|< 768px (Mobile/PWA)| MobileLayout["Instagram Mirror: Fixed Bottom Bar + 470px Centered Feed + Stories Reel"]
  Viewport -->|768px - 1024px (Tablet)| TabletLayout["Hybrid Mode: Slim Left Rail (72px) + 540px Feed + Top Masthead"]
  Viewport -->|> 1024px (Desktop/Web)| DesktopLayout["Facebook Mirror: Universal Masthead + Left Sidebar (280px) + Center Feed (590px) + Right Sidebar (320px)"]
```

### 4.1 Responsive Breakpoints
- **Mobile (`< 768px`):** Single column layout (`max-w-[470px]` centered or 100% full-bleed on small devices). Bottom tab navigation.
- **Tablet (`768px – 1024px`):** 2-column layout. Collapsed icon-only navigation rail (`72px`) + centered feed (`540px`).
- **Desktop (`> 1024px`):** Full 3-column Facebook-style architecture:
  - **Left Rail (Navigation & Family Circles):** `280px` fixed/sticky.
  - **Center Column (Main Stream):** `590px – 640px` max-width.
  - **Right Rail (Birthdays, Milestones & Active Members):** `300px – 340px` sticky.

---

## 5. Mobile / PWA Specifications (Instagram Mirror)

### 5.1 Top Navigation Bar (Mobile Header)
- **Height:** `44px` + `env(safe-area-inset-top)`.
- **Left:** FamilyPlatform Wordmark / Crest logo (`height: 28px`).
- **Right:** Action cluster with `gap-2` (minimum 8px separation):
  - Add / Create Post (`+` icon).
  - Activity / Heart notification badge (with unread count badge in `--accent-heart`).
  - Direct Messages / Family Chat paper plane icon.

### 5.2 Family Moments & Stories Reel
- **Container:** Horizontal scroll container with momentum scrolling (`-webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory;`).
- **Padding:** `px-4 py-2`.
- **Avatar Sizing:** `56x56px` circular avatar (`rounded-full`).
- **Border Rings:**
  - *New Family Story:* Multi-colour gradient ring (`2px` border with `linear-gradient(45deg, #FF3040, #F77737, #FCAF45)`).
  - *Birthday / Special Milestone:* Radiant gold gradient ring (`linear-gradient(45deg, #FFD700, #FFA500)`).
  - *Viewed Story:* Subtle outline (`1.5px solid var(--border-subtle)`).
- **Label:** First name truncated at 8 characters with full name on tap; font-size `11px`.

### 5.3 Post Card Component (Feed)
- **Container:** Borderless on full-bleed mobile or `border-b border-[var(--border-subtle)]` with zero excess padding.
- **Card Header (Height: `54px`):**
  - Left: `36x36px` circular avatar + User Display Name (`font-semibold text-sm`) + Generation / Lineage Badge (e.g. `Gen 2` pill) + Location / Timestamp (`text-xs text-[var(--text-secondary)]`).
  - Right: Contextual options menu trigger (`...` icon, touch target expanded to `44x44px`).
- **Media Container:**
  - **Aspect Ratios:** Native `1:1` (square), `4:5` (portrait), or `16:9` (landscape). Edge-to-edge full width.
  - **Carousel Indicators:** Subtle pill dots overlaid at the bottom-center with smooth fade.
  - **Double-Tap Heart Micro-Interaction:** Double tapping media triggers an instant heart burst with spring scaling (`cubic-bezier(0.175, 0.885, 0.32, 1.275)`), haptic vibration (`navigator.vibrate?.(40)`), and optimistic like increment.
- **Action Bar (Height: `44px`):**
  - Left Cluster: Heart (Like), Speech Bubble (Comment), Paper Plane (Share to Family Chat).
  - Right: Bookmark / Save to Family Archive.
  - Touch targets: Each icon maintains a `44x44px` invisible tap target (`::after { inset: -8px; }`).
- **Details Block:**
  - Likes Counter: `font-semibold text-sm` (e.g. `Liked by Mum and 14 others`).
  - Caption: Bold username inline with text. Clamped to 2 lines with a distinct "... more" link.
  - Comments Preview: "View all 8 comments" muted link (`text-[var(--text-secondary)]`).
  - Timestamp: Relative time (e.g. "3 hours ago") in `text-xs uppercase tracking-wide`.

### 5.4 Fixed Bottom Tab Bar (Mobile)
- **Height:** `50px` + `env(safe-area-inset-bottom)`.
- **Surface:** Glassmorphic translucent surface (`backdrop-blur-md bg-[var(--bg-canvas)]/90 border-t border-[var(--border-subtle)]`).
- **5 Core Touch Points (Fitts's Law):**
  1. **Home / Feed** (Home icon).
  2. **Family Tree / Lineage** (Interactive Tree / Kinship nodes icon).
  3. **Create (+) Action** (Elevated center button with tactile press feedback).
  4. **Calendar / Milestones** (Calendar icon with dot indicator for birthdays).
  5. **Profile / Branch** (User avatar thumbnail `24x24px` with active ring).

---

## 6. Desktop / Web Specifications (Facebook Mirror)

### 6.1 Top Universal Masthead (Fixed 56px App Bar)
- **Height:** `56px`, sticky top with `z-50`, `bg-[var(--bg-surface)] border-b border-[var(--border-subtle)] shadow-sm`.
- **Left Section:**
  - Family crest / logo link (`height: 36px`).
  - Global Search Bar: Pill input (`bg-[var(--bg-canvas)] border border-transparent focus:border-[var(--accent-blue)] rounded-full px-4 py-2 text-sm w-[260px]`).
- **Center Section:**
  - 4 Primary Navigation Tabs with active bottom indicator border:
    1. **Feed / Home** (`active:border-b-2 border-[var(--accent-blue)]`)
    2. **Family Tree** (Interactive Genealogy)
    3. **Gatherings & Events** (RSVP & Milestones)
    4. **Memories & Vault** (Photos, Audio & Archives)
- **Right Section (Utility Controls):**
  - "What's New" notification bell with counter badge.
  - Family Messenger chat dropdown.
  - User profile menu trigger with avatar (`36x36px`).

### 6.2 Left Navigation Rail (Sticky 280px)
- **User Profile Shortcut:** Displays current user name and branch (e.g. "Adeleke Branch").
- **Quick-Access Navigation Links:**
  - 🌳 **Interactive Family Tree**
  - 🎂 **Family Birthdays & Anniversaries**
  - 🍲 **Family Recipes & Traditions**
  - 🎙️ **Oral Histories & Audio Memories**
  - 📜 **Family Code & House Rules**
  - ⚙️ **Branch & Privacy Settings**
- **Family Circles / Branches:** Collapsible list of distinct lineage branches (e.g. "Lagos Branch", "London Branch", "Youth Circle").

### 6.3 Center Stream (Main Feed & Composer)

#### A. Stories / Memories Carousel (Desktop View)
- Horizontal tray of vertical aspect-ratio story cards (`110px width x 190px height`, rounded `12px`).
- First card: "Add Memory / Story" with user's avatar and prominent `+` badge.
- Subsequent cards: Background image preview of family post with avatar bubble in top-left and family member's name at the bottom.

#### B. Rich Family Post Composer
- **Surface Card:** `bg-[var(--bg-surface)] rounded-2xl border border-[var(--border-subtle)] p-4 shadow-sm mb-4`.
- **Top Row:** User avatar (`40x40px`) + Full-width pill button placeholder ("What's happening in your branch today, Wally?").
- **Divider:** Subtle 1px line (`border-t border-[var(--border-subtle)] my-3`).
- **Action Pills Bar (4 Multi-Action Triggers):**
  1. 📷 **Photo / Video** (Green icon)
  2. 🌟 **Family Milestone** (Gold icon)
  3. 🍲 **Family Recipe** (Orange icon)
  4. 🎙️ **Voice Memory** (Red icon)

#### C. Structured Desktop Post Card
- **Surface:** `bg-[var(--bg-surface)] rounded-2xl border border-[var(--border-subtle)] p-4 shadow-sm mb-4`.
- **Header:** Profile avatar (`40x40px`), full name with kinship relation (e.g. "Grandpa Wally • Head of House"), relative timestamp, and privacy pill ("Shared with: Entire Family").
- **Media Gallery:** Flexible CSS Grid supporting single photos, side-by-side comparisons, 3-image hero collages, or 4+ photo mosaic with "+X more" overlay.
- **Reaction & Action Bar:**
  - Facebook-style reaction trigger on hover (Like, Love, Celebrate, Care, Laugh, Respect).
  - Comment count and share metrics.
- **Inline Comment Box:** Pill-shaped input (`.comment-composer__field`) with instant attachment, emoji picker, and submit button equipped with `ScoreTenantSpinner`.

### 6.4 Right Sidebar (Sticky 320px)
- **Card 1: Upcoming Birthdays & Milestones**
  - Gift icon with highlighted today/this-week family birthdays.
  - 1-click "Send Birthday Blessing" action button.
- **Card 2: Upcoming Gatherings**
  - Next scheduled family reunion, Zoom catch-up, or dinner.
  - Quick RSVP toggle (Going / Maybe / Can't make it).
- **Card 3: Active Lineage Members**
  - List of family members online now with green status dot.
  - 1-click chat initiation.

---

## 7. FamilyPlatform Domain-Specific Components

### 7.1 Kinship & Lineage Badges
Subtle, high-contrast status chips displaying generational context:
- `Gen 1 • Eldest / Patriarch / Matriarch` (Champagne Gold border with dark or cream fill).
- `Gen 2 • Branch Lead` (Slate/Blue border).
- `Gen 3 • Youth Circle` (Emerald border).

### 7.2 Family Milestone / Life Event Post
Special card layout for weddings, graduations, new arrivals, and memorials:
- Decorative celebration banner with celebratory icon.
- High-resolution hero image with date and location metadata.
- Interactive "Congratulate" / "Share a Blessing" dedicated action button.

### 7.3 Family Recipe Card
- Food photo header with tag badges (e.g. "Mum's Jollof Rice", "Prep: 25 mins", "Cook: 45 mins").
- Collapsible "Ingredients" checklist toggle.
- "Cooked this!" family endorsement count.

### 7.4 Audio Memory / Oral History Player
- Inline waveforms for voice notes, stories from elders, and songs.
- Scrub bar with 15s skip back/forward controls.

---

## 8. Interaction States & Engineering Invariants

### 8.1 The 4 Tactile States
Every interactive element across both mobile and desktop must implement:
1. **Hover:** Subtle brightness elevation (`hover:brightness-105 hover:-translate-y-0.5 transition-all duration-150`).
2. **Active (Press):** Immediate tactile compression (`active:scale-[0.98]`).
3. **Focus-Visible:** Accessible outline (`outline: 2px solid var(--accent-blue); outline-offset: 2px;`).
4. **Disabled / Loading:** `opacity-50 cursor-not-allowed` with integrated `ScoreTenantSpinner`.

### 8.2 The 6-State Engineering Verification
Before shipping any component or view:
1. **Happy Path:** Clean, visually balanced layout guiding the user directly to the primary action.
2. **Extreme Lengths:** Resilient to long names (e.g. 80-character usernames) and minimal data without overflow or mid-word break.
3. **Empty State:** Illustrated empty card with clear, welcoming Call-to-Action (e.g. "No memories shared in this branch yet. Be the first to post!").
4. **Submitting State:** Instant spinner feedback via `ScoreTenantSpinner` to prevent duplicate submissions.
5. **Error / Offline State:** Resilient offline notice with retry action and cached IndexedDB / Service Worker content.
6. **Loading Skeleton:** Glassmorphic shimmer placeholders matching exact dimensions of media and text.

### 8.3 Fitts's Law & Safe Touch Targets
- **Minimum 44x44px Hit Target:** All buttons, icon triggers, tabs, and avatar links must provide at least a `44x44px` interactive touch area.
- **Minimum 8px Gap:** Adjacent touch targets maintain at least `8px` physical separation (`gap-2`) to eliminate accidental miss-taps.

---

## 9. Outcome Metrics & Performance Budgets

- **Interaction to Next Paint (INP):** `< 200ms` across all interactions.
- **Largest Contentful Paint (LCP):** `< 1.8s` on mobile 4G networks.
- **Publish Memory Velocity:** Post a photo or memory in `≤ 2 taps` from the home screen.
- **Accessibility:** Zero axe-core "serious" or "critical" violations; 100% WCAG AA contrast compliance.
- **Animation Performance:** 60fps micro-animations with automatic disabling under `prefers-reduced-motion: reduce`.
