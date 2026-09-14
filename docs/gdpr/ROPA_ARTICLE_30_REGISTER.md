# 🏛️ UK GDPR ARTICLE 30: RECORD OF PROCESSING ACTIVITIES (ROPA)
**Master Compliance Register for Portfolio Applications & FamilyPlatform**  
**Classification:** Enterprise Legal & Compliance Architecture (S6)  
**Lead Auditor:** Ajibike (AI Governance & Agentic Compliance Lead) & Olutobi (Deloitte Audit)  
**Last Audited:** 13 September 2026 — 19:30 BST  

---

## 1. Controller & Governance Information

- **Data Controller:** Family Platform / ModernMan Portfolio Applications
- **Data Protection Officer (DPO):** Ajibike (Compliance Lead) — `support@myfamilyplatform.com`
- **Supervisory Authority:** Information Commissioner’s Office (ICO), United Kingdom
- **Applicable Frameworks:** UK General Data Protection Regulation (UK GDPR), Data Protection Act 2018, Privacy and Electronic Communications Regulations (PECR)

---

## 2. Portfolio Master Processing Matrix (All 7 Applications)

| Application | Processing Activity | Data Categories Processed | Special Category / Sensitive Data? | Legal Basis (UK GDPR Art. 6 / 9) | Retention Period | Third-Party Sub-Processors |
| :--- | :--- | :--- | :---: | :--- | :--- | :--- |
| **`FamilyPlatform`** | Account Management, Family Trees & Organograms, Photos/Stories, Milestones, Nostalgia Prompts | Name, email, hashed password, kinship relationships, birth dates, anniversary dates, photos, audio/video | **Yes (Art. 9):** Biometric/photo tags, family unions, minor node relations (with parental consent) | **Art. 6(1)(b)** Contractual necessity;<br>**Art. 6(1)(a)** Explicit Consent for media/kinship | Account lifetime + 30-day grace post-deletion request | Cloudflare (Stream), Cloudmersive (Malware/AV), WebPush VAPID, Host provider |
| **`PartyPlatform`** | Public/Private RSVP, Waitlist, Event Ticketing, Guest Check-In, Affiliate Referrals | Guest names, emails, phone numbers, RSVP status, dietary notes, QR ticket codes | **No** (dietary requirements processed only with explicit consent) | **Art. 6(1)(b)** Contractual performance;<br>**Art. 6(1)(f)** Legitimate interest (RSVP safety) | Event date + 90 days (auto-anonymized) | Native SMTP Queue, Pusher (real-time chat/checkin) |
| **`iDecideApp`** | Multi-Criteria Decision Analysis, Template Questionnaires, Scoring Telemetry | Anonymized decision criteria, weights, custom trade-off scores, user IDs | **No** (purely quantitative parameters) | **Art. 6(1)(b)** Contractual necessity | User session / Account lifetime | In-house DB only |
| **`iAccountApp`** | Cash-flow scoring, Expense categorization, Invoice tracking | Expense amounts, transaction categories, invoices, business tax estimates | **No** (financial metadata, no raw banking card numbers stored) | **Art. 6(1)(b)** Contractual necessity | 7 years (UK HMRC statutory accounting standard) | Host provider |
| **`ExecMindApp`** | Executive Decision Briefings, AI Strategic Summaries, Cohort Telemetry | Anonymized strategic notes, daily priority flags, cohort engagement metrics | **No** | **Art. 6(1)(b)** Contractual necessity | Account lifetime | OpenAI / DeepMind (Zero-retention enterprise API) |
| **`LoanEasyFinance`** | Real-Time Affordability Scoring & Pre-Qualification Checks | Stated income, monthly commitments, loan term preferences | **No** (soft pre-check only, zero credit file impact) | **Art. 6(1)(b)** Pre-contractual steps;<br>**Art. 6(1)(c)** Legal compliance | 6 years (FCA consumer compliance) | Host provider |
| **`TenantScore`** | Tenant Passport Generation & Open Banking Affordability Verification | Identity verification, rental payment history, landlord reference tokens | **Yes (High Risk):** Open Banking read-only transaction summaries | **Art. 6(1)(a)** Explicit Consent;<br>**Art. 6(1)(b)** Contractual necessity | Active rental verification period + 1 year | FCA-regulated Open Banking AISP Partner |

---

## 3. Data Subject Rights & Fulfillment Architecture

1. **Right of Access & Portability (Art. 15 & 20):**
   - Automated 1-click JSON/CSV export engine at `POST /account/data-export`.
   - Generates encrypted zip containing all user profile data, post history, photo references, and family tree connections.
2. **Right to Erasure / "To Be Forgotten" (Art. 17):**
   - Self-service deletion trigger at `POST /account/request-deletion`.
   - Anonymizes posts, unlinks genealogy organogram nodes without destroying legitimate shared lineage, and hard-deletes photos from storage within 30 days.
3. **Right to Rectification (Art. 16):**
   - Direct user editing on settings and profile nodes (`/accountSetting`, `/member/profilePage/editProfile`).
4. **Cookie Consent & PECR Compliance:**
   - Client-side cookie consent banner with granular options (`Necessary only` vs. `Accept all`).
   - Zero non-essential tracking cookies fired prior to explicit consent.

---

## 4. Technical and Organizational Measures (TOMs)

- **Encryption at Rest:** AES-256 for all persistent database storage and backups.
- **Encryption in Transit:** TLS 1.3 enforced with HTTP Strict Transport Security (`HSTS max-age=31536000; includeSubDomains; preload`).
- **Password Hashing:** Strict `PASSWORD_ARGON2ID` with auto-rehashing across all 7 applications.
- **Access Control:** Role-Based Access Control (RBAC) with prepared PDO statements and fail-closed router gates (`RouteDispatch.php`).
- **Malware & Virus Scanning:** All uploaded media scanned via Cloudmersive Antivirus API prior to storage.
- **Zero Third-Party Ad Trackers:** No Facebook Pixel, Google Ads, or commercial data brokers embedded anywhere in the code.
