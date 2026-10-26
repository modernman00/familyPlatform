# RFC-0008: FamilyPlatform <-> PartyPlatform Event Management & Kinship Bridge

- **Author(s):** Damilola "Dami" Ogunleye (Principal Core Systems Architect) & Kaelen Vance (Senior Staff Full-Stack Engineer)
- **Lead TPM:** Oluwafemi "Femi" Adeleke
- **Technical Reviewer(s):** Damilola Ogunleye (Backend) / Kehinde Akindele & Segun (Frontend)
- **Gatewatchers:** David (Architecture) / Gbenga Adebisi (DBRE) / Dr. Viktor Kovács, Oladapo Salami & Marcus (SecOps) / Ayo Adeyemi (SRE) / Oladele (Deployment Manager)
- **Status:** APPROVED
- **Created Date:** 2026-10-26
- **Target Apps:** [FamilyPlatform | PartyPlatform]

---

## 1. Problem Statement & User Value (The "Why")
- **Context & Motivation:** When a family member creates an event on `FamilyPlatform` (`Event::submitEvent()`), it currently creates only a 5-field passive calendar reminder (`eventName`, `eventDate`, `eventType`, `eventDescription`, `eventFrequency`). It lacks RSVPs, guest priority scoring, seating charts, menus, gift wishlists, and celebratory group cards—all of which already exist in `PartyPlatform`. Conversely, forcing every passive annual reminder (e.g. *Grandad's Memorial*) into `PartyPlatform` would create ghost events.
- **User & Commercial Impact:** By introducing a **3-Gear Progressive Escalation & Kinship Bridge**, hosts can seamlessly enable `PartyPlatform` Studio at event creation time (Gear 1), upgrade any existing `FamilyPlatform` event in 1 click (Gear 2), or jump directly into `PartyPlatform` Studio via a 1-click cryptographic SSO handoff with their entire ego-centric Family Tree pre-seeded and priority-scored (`Close Family`, `Family`, `Extended Family`) in `PartyPlatform`'s Guest Builder (`guest_prospects`).
- **Expected Outcome Metrics:** Local event creation latency `< 20ms`; S2S bridge P95 `< 250ms` with a hard `2500ms` circuit breaker; £0 CAC host acquisition into `PartyPlatform`.
- **Explicit Non-Goals:** We do not force passive recurring reminders (`Memorial`, `Meeting`) to auto-create `PartyPlatform` events unless the host explicitly toggles *"Power with PartyPlatform Studio"*. We do not automatically blast SMS/Email invites to the family tree upon provisioning; relatives are staged in `guest_prospects` (`GuestBuilderController`) so the host controls final dispatch.

---

## 2. Proposed Architecture & Technical Design (The "How")
- **System Overview & Component Diagram:**
  ```mermaid
  flowchart TD
      Host[Family Member Host] -->|Gear 1: Create Modal Toggle<br/>Gear 2: 1-Click Sidebar Upgrade| FP_Event[FamilyPlatform Event Controller]
      FP_Event -->|1. Local Commit| FP_DB[(FamilyPlatform DB: events)]
      FP_Event -->|2. Ego Kinship BFS| Kinship[KinshipCalculatorService]
      FP_Event -->|3. HMAC-SHA256 S2S Bridge| PP_Bridge[PartyPlatform EcosystemEventBridgeController]
      PP_Bridge -->|Resolve/Provision Manager| PP_Acct[(PartyPlatform DB: account)]
      PP_Bridge -->|Create Private Event| PP_Ev[(PartyPlatform DB: events)]
      PP_Bridge -->|Seed Scored Relatives| PP_Prospects[(PartyPlatform DB: guest_prospects)]
      Host -->|Click Manage Studio / Group Card| FP_SSO[Event::ssoHandoff]
      FP_SSO -->|60s Signed HMAC Redirect| PP_SSO[PartyPlatform EcosystemEventBridgeController::ssoLogin]
  ```
- **Data Models & Schema Changes (DDL):**
  - **FamilyPlatform Migration (`migrations/2026_10_26_party_platform_event_bridge.sql`):**
    - Adds nullable, non-blocking columns to `events`:
      - `party_event_id VARCHAR(20) NULL DEFAULT NULL`
      - `party_event_slug VARCHAR(150) NULL DEFAULT NULL`
      - `party_rsvp_url VARCHAR(255) NULL DEFAULT NULL`
      - `party_card_url VARCHAR(255) NULL DEFAULT NULL`
      - `party_sync_status VARCHAR(20) NULL DEFAULT NULL`
    - Composite index `idx_events_party_event_id (party_event_id)` for $O(1)$ lookup.
- **API Contracts & DTOs:**
  - **S2S Provision Endpoint (`POST /api/v1/ecosystem/provision-family-event` on PartyPlatform):**
    - Headers: `X-Ecosystem-Timestamp`, `X-Ecosystem-Signature` (HMAC-SHA256 over `timestamp . "." . rawBody`).
    - Request JSON: `host_email`, `host_first_name`, `host_last_name`, `host_mobile`, `family_code`, `family_event_no`, `event_name`, `event_date`, `event_type`, `event_description`, `kinship_prospects[]`.
    - Response JSON: `status: "success"`, `party_event_id`, `slug`, `rsvp_url`, `card_url`, `studio_url`, `seeded_prospects_count`.
  - **FamilyPlatform Endpoints:**
    - `POST /member/profilePage/event` — accepts optional `enable_party_platform=1`.
    - `POST /member/profilePage/event/[i:eventNo]/upgrade-party` — upgrades an existing `FamilyPlatform` event to `PartyPlatform` (author + `famCode` gated).
    - `GET /member/profilePage/event/[i:eventNo]/party-sso` — generates a 60-second HMAC-signed SSO redirect URL to `PartyPlatform` Studio for the event author.
- **Core Interfaces & Classes:**
  - `FamilyPlatform`: `App\services\PartyPlatformBridgeService`, `App\controller\members\Event`.
  - `PartyPlatform`: `App\controller\EcosystemEventBridgeController`.

---

## 3. Blast Radius & Cross-App Ripple Effects
- **Shared Library Impact:** None (`modernman00/shared-lib` and `@modernman00/shared-js-lib` remain untouched; uses existing `\Src\ApiResponse`, `\Src\CheckToken`, and `ScoreTenantSpinner`).
- **Portfolio Blast Radius:** Strictly isolated to `FamilyPlatform` and `PartyPlatform`.
- **Backward Compatibility:** All new columns on `FamilyPlatform.events` default to `NULL`. Existing events render seamlessly and gain a 1-click *"✨ Enable RSVPs & Party Studio"* upgrade button for their author.

---

## 4. Failure Modes, Timeouts & Chaos Resilience
- **Downstream Failures & Network Timeouts:** If `PartyPlatform` is unreachable over HTTP or times out (`> 2500ms`), `PartyPlatformBridgeService` catches the failure, logs a structured warning, and falls back gracefully without failing `Event::submitEvent()`. In same-host/VPC environments where the local `partyplatform` database is reachable, `PartyPlatformBridgeService` can also execute a deterministic local-VPC fallback transaction so zero user actions fail.
- **Idempotency:** If an event already has a non-empty `party_event_id`, calling `upgradeToPartyPlatform` returns the existing `PartyPlatform` metadata without creating duplicate events or duplicate `guest_prospects` rows (enforced via `existingEmailSet` and `identityKeys` deduplication).
- **Defensive Fallback UI:** If `party_event_id` is `NULL`, the event card renders the standard `FamilyPlatform` reminder layout plus the author's *"✨ Enable RSVPs & Party Studio"* button.

---

## 5. Security Threat Model & Exploitation Proof-of-Concept
- **Authentication & Authorization (IDOR):**
  - Upgrading an event (`POST /member/profilePage/event/{eventNo}/upgrade-party`) or requesting an SSO handoff (`GET /member/profilePage/event/{eventNo}/party-sso`) strictly verifies `$_SESSION['id'] === $event['id']` AND tenant isolation (`$event['eventCode'] === $_SESSION['famCode']`). Cross-user or cross-family attempts return `403 Forbidden`.
  - S2S provisioning (`POST /api/v1/ecosystem/provision-family-event`) and SSO login (`GET /api/v1/ecosystem/sso-handoff`) verify a constant-time `hash_equals()` HMAC-SHA256 signature and enforce a strict timestamp window ($\pm 300\text{s}$ for S2S, $60\text{s}$ for SSO handoff).
- **Input Sanitization & Injection Defense:** 100% parameterized PDO statements (`?` / `:named`), `@csrf` / `CheckToken::tokenCheck()`, and contextual escaping (`x-text` / `esc()`).
- **Dr. Viktor Kovács & Marcus Attack Payload (PoC):**
  ```bash
  # PoC 1: Forged S2S signature to PartyPlatform Bridge
  curl -i -X POST https://partyplatform.test/api/v1/ecosystem/provision-family-event \
    -H "Content-Type: application/json" \
    -H "X-Ecosystem-Timestamp: $(date +%s)" \
    -H "X-Ecosystem-Signature: sha256=deadbeef00000000000000000000000000000000000000000000000000000000" \
    -d '{"host_email":"victim@example.com","event_name":"Forged"}'
  # Target assertion: Must return 401 Unauthorized / 403 Forbidden

  # PoC 2: Cross-Tenant IDOR on FamilyPlatform Event Upgrade
  curl -i -X POST https://olaogun.test/member/profilePage/event/999999/upgrade-party \
    -b "PHPSESSID=attacker_session" -H "X-XSRF-TOKEN: valid_token"
  # Target assertion: Must return 403 Forbidden
  ```

---

## 6. Observability, Telemetry & Monitoring Plan
- **Structured Logs:** `[PartyPlatformBridge]` structured error and audit logs on provisioning, fallback, and signature rejection.
- **Metrics & KPIs:** Tracks `seeded_prospects_count`, `party_sync_status` (`synced` vs `local_only`), and S2S response time.
- **Alerting Thresholds (Ayo Adeyemi):** P95 bridge latency > 2500ms triggers circuit-breaker warning.

---

## 7. Rollback & Zero-Downtime Rollout Strategy
- **Deployment Strategy:** Phased rollout by Oladele & Tunde Olatunji:
  1. Deploy `PartyPlatform` `EcosystemEventBridgeController` & routes.
  2. Run additive migration `2026_10_26_party_platform_event_bridge.sql` on `FamilyPlatform`.
  3. Deploy `FamilyPlatform` `PartyPlatformBridgeService`, `Event` controller, and frontend bundle.
- **Rollback Verification Command:**
  ```bash
  bash scripts/rollback.sh --version <previous_tag>
  ```

---

## 8. Definition of Done (DoD) Sign-Off Checklist
- [x] **Gate 0:** Machine preflight passed (`bash scripts/preflight.sh` exit code 0, PHPStan L8 0 errors, 100% tests).
- [x] **Gate 1:** Peer review signed off by Dami/Kenny; PoC neutralized by Dr. Viktor Kovács & Marcus; David structural clearance.
- [x] **Gate 2:** Documentation updated (`/playbook` and `/how-to-use`); RFC-0008 recorded.
- [x] **Gate 3:** Structured logs and circuit-breaker fallback active.
- [x] **Gate 4:** End-to-end verification and browser inspection completed.
