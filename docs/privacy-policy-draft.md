# Privacy Policy — DRAFT for legal review

> **Status:** Draft prepared by Engineering (James) against the live data map, 2026-09-02.
> **Owner:** Olutobi (Legal / Compliance) + Helena (Board). **Do not publish** until reviewed.
> Replaces `resources/views/privacy.blade.php`, which is a lending-product template and
> does not describe this service.
>
> **Open items flagged for legal (⚠️) are inline below.** The biggest are children's
> data (GDPR-2) and the transfer of profile text to DeepSeek (China).

---

## 1. Who we are

Our Family Network ("we", "us", "the Platform") operates FamilyPlatform, a private
social network for families to connect, share memories, build a family tree, and
record family history.

- **Data controller:** ⚠️ *legal entity name + registered address to be confirmed.*
- **Contact / DPO:** ⚠️ *dedicated privacy contact address to be confirmed* (currently `general@myfamilyplatform.com`).
- **Supervisory authority (UK):** Information Commissioner's Office (ICO), ico.org.uk. You may lodge a complaint at any time.

## 2. What we collect

### 2.1 You give us directly
| Data | Where | Purpose |
|---|---|---|
| Email address, password (stored hashed) | Registration | Account creation, sign-in |
| First name, last name | Registration / profile | Identifying you to your family |
| Date of birth, gender, marital status | Profile | Family-tree placement, age-appropriate experience |
| Mobile number, country | Profile / contact | Notifications, invitations |
| Occupation / work details | Profile | Shown on your family profile |
| Declared family members (children, siblings — names, relationships) | Family setup | Building your family tree |
| Family code | Registration | Grouping you with your family |
| Posts, comments, reactions, poll votes | Using the feed | The core social features |
| Reel videos and captions | Reels | Sharing family video |
| Photos (profile, posts, uploads) | Uploads | Displaying your content |
| Events you create | Events | Family calendar / reminders |
| Free-text family biographies / history | Family tree | Recording family history |
| Notification & privacy preferences | Settings | Honouring your choices |
| AI consent flag | Registration | See §5 (AI features) |

### 2.2 We collect automatically
| Data | Purpose | Lawful basis |
|---|---|---|
| IP address, browser/user-agent, session identifiers, timestamps of visits and sign-ins (`audit_logs`, `login_events`) | Security, fraud and account-takeover detection (anomalous-login checks) | Legitimate interest |
| Aggregated usage/interaction data (`platform_analytics`) | Understanding and improving the service | Legitimate interest / consent for non-essential analytics cookies |
| Push-notification subscription tokens | Delivering notifications you asked for | Consent |
| Short-lived one-time verification codes | Two-factor authentication | Contract / security |

### 2.3 From third parties
- **Google / Facebook:** if you sign in with them, we receive your name and email address to create or match your account.

## 3. Why we use your data and our lawful basis

| Purpose | Lawful basis (UK GDPR Art. 6) |
|---|---|
| Creating and running your account; delivering the family-network features you use | Performance of a contract |
| Security, abuse prevention, anomalous-login detection, service integrity | Legitimate interests |
| Sending activity / event notifications by email or SMS | Consent (you can turn these off in Settings) ⚠️ *see note on the current toggle default* |
| AI-generated family biographies and related features | Consent (`ai_consent`) |
| Non-essential cookies / analytics | Consent (cookie banner) |
| Complying with legal obligations (e.g. responding to lawful requests) | Legal obligation |

⚠️ **Legal note:** the email/SMS notification toggles currently default to "off" and
have not historically been enforced on the send path. Product + Legal need to decide
whether family-activity email is opt-in or opt-out before this section is final
(tracked as PUSH-5 / GDPR-5).

## 4. Who we share it with (processors / sub-processors)

We do not sell your data. We use the following service providers, who process data
on our instructions:

| Provider | What they process | Location | Transfer safeguard |
|---|---|---|---|
| **Pusher Ltd** | Real-time delivery of feed/comment/like/event updates | UK / EU / US | ⚠️ SCCs / UK IDTA to confirm |
| **Cloudflare, Inc.** (Stream) | Hosting and streaming of reel videos | US | ⚠️ SCCs / UK IDTA |
| **Google LLC** | Sign-in (OAuth), reCAPTCHA Enterprise bot protection | US | ⚠️ EU-US DPF / SCCs |
| **Meta Platforms, Inc.** | Sign-in (OAuth) | US | ⚠️ EU-US DPF / SCCs |
| **DeepSeek** | Generating family-biography text — **the profile text you submit for a summary is sent to DeepSeek's API** | **China** | ⚠️ **No UK/EU adequacy decision for China. This transfer needs explicit, specific consent and a transfer risk assessment, or the feature should switch to a provider in an adequate jurisdiction.** |
| **Email / SMTP provider** | Sending transactional and notification email | ⚠️ to confirm | ⚠️ to confirm |
| **Browser push services** (Google FCM, Apple, Mozilla) | Delivering push notifications to your device | US / global | Inherent to web push; only an opaque token is shared |

## 5. AI features

If you enable AI features (`ai_consent`), free-text you provide for a family
biography is sent to a third-party large-language-model provider (**DeepSeek**,
see §4) to generate a summary. The output is stored on your family tree. You can
withdraw consent at any time; existing generated text remains until you edit or
delete it. ⚠️ *Legal to confirm the consent wording is specific and informed, and
that the China transfer is lawful.*

## 6. Children ⚠️ **UNRESOLVED — GDPR-2**

A family network will hold personal data about children. The Platform currently
collects date of birth and a children count at registration but **does not
operate an age gate or a parental-consent mechanism**.

Before launch, Legal + the Board must set:
- the minimum age to hold an account;
- how a parent/guardian consents to and manages a child's profile;
- whether a Data Protection Impact Assessment (DPIA) is required (likely yes).

This section cannot be finalised until that decision is made.

## 7. How long we keep it

| Data | Retention |
|---|---|
| Account and profile data | For the life of your account, then ⚠️ *[X] days* after closure for legal/audit, then deleted or anonymised |
| Posts, comments, reels, events | Until you delete them or your account is erased ⚠️ *(note: some content other family members rely on may be retained in anonymised form)* |
| Security / audit logs (`audit_logs`, `login_events`) | ⚠️ *[X] months* |
| One-time verification codes | Minutes (invalidated on use) |
| Push subscription tokens | Until you disable notifications or the token expires |

⚠️ Retention periods to be set by Legal.

## 8. Your rights

You can exercise these from **Settings → Privacy & Security**, or by contacting us:

- **Access** — download a copy of your data ("Download my data" — provided as JSON).
- **Portability** — the same export is machine-readable.
- **Rectification** — edit your profile and content directly.
- **Erasure** — "Request account deletion". We action requests within 30 days;
  content other family members depend on may be retained in anonymised form, and
  data under legal hold is retained as required by law.
- **Objection / restriction** — to processing based on legitimate interests.
- **Withdraw consent** — for AI features, notifications, and non-essential cookies,
  at any time, without affecting prior processing.
- **Complain** to the ICO (or your local supervisory authority).

## 9. Cookies

We use strictly-necessary cookies for sign-in and security (no consent needed) and,
with your permission, cookies to remember preferences and measure usage. Manage
your choice via the cookie banner. ⚠️ *A full cookie table to be added.*

## 10. Security

Passwords are stored hashed. Sign-in is protected by mandatory two-factor
authentication and rate limiting. We monitor for anomalous logins. Data in transit
is encrypted (HTTPS/HSTS).

## 11. Changes

We will notify you of material changes by email or in-app. *Last updated: [date].*

---

### Engineering appendix — data map used for this draft

Tables holding personal data: `account`, `personal`, `contact`, `work`, `children`,
`sibling`, `family_biographies`, `user_families`, `family_nodes`, `family_unions`,
`post`, `comment`, `post_reactions`, `comment_reactions`, `post_poll_votes`,
`events`, `images`, `profilePics`, `uploadPics`, `family_reels`,
`family_reel_comments`, `family_reel_reactions`, `requestMgt`, `notification`,
`kinship_dismissed`, `platform_analytics`, `audit_logs`, `login_events`,
`pushNotification`, `code_mgt`.

Third-party calls in code: `app/services/AIService.php` (DeepSeek),
`app/services/CloudflareStreamService.php` (Cloudflare Stream), Pusher (feed),
`OAuthController` (Google, Facebook), `Recaptcha` (Google reCAPTCHA Enterprise),
`PushNotificationClass` (Web Push / VAPID).
