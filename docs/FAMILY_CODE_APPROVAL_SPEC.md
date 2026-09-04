# Family Code Approval Feature - Technical Specification

**Version:** 1.0  
**Status:** Ready for TAT Review  
**Date:** 2026-09-04

## 1. Overview

This feature adds a **security verification layer** for existing family code registrations to prevent unauthorized access. When a new user attempts to join an existing family using a family code, they must provide details of the family member who invited them, which triggers an approval workflow.

### Problem Statement
- **Current Risk:** A malicious actor could steal a family code and register immediately, gaining access to the entire family network
- **Solution:** Require approval from an existing family member before granting network access

### Key Benefits
- ✅ Prevents unauthorized access via stolen codes
- ✅ Family control over who joins their network
- ✅ Audit trail of all requests
- ✅ Graceful handling of approvals/denials/expiry

---

## 2. Database Schema

### `family_approval_requests` Table

```sql
CREATE TABLE `family_approval_requests` (
  `no` int NOT NULL AUTO_INCREMENT,
  `id` varchar(255) NOT NULL COMMENT 'UUID of new user requesting to join',
  `family_code` varchar(50) NOT NULL,
  `inviter_first_name` varchar(100) NOT NULL,
  `inviter_last_name` varchar(100) NOT NULL,
  `inviter_email_or_mobile` varchar(100) NOT NULL,
  `approver_id` varchar(255) COMMENT 'UUID of approver',
  `temporary_code` varchar(50) NOT NULL,
  `status` enum('pending','approved','denied','expired'),
  `request_expires_at` timestamp,
  `reminder_sent_at` timestamp NULL,
  `created_at` timestamp,
  `approved_at` timestamp NULL,
  `updated_at` timestamp,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `unique_pending_inviter` (`id`, `approver_id`),
  KEY `family_code_idx` (`family_code`),
  KEY `status_expires_idx` (`status`, `request_expires_at`)
);
```

**Key Indexes:**
- `unique_pending_inviter`: Prevents duplicate pending approvals from same inviter
- `family_code_idx`: Quick lookup of all requests for a family
- `status_expires_idx`: Efficient retrieval for expiry/reminder cron jobs

---

## 3. User Flow

### Registration with Existing Family Code

```
1. User enters existing family code
   ↓
2. System checks if code exists
   ↓
3a. [NO] → User creates new family, gets new code
   ↓
3b. [YES] → System shows inviter verification form
   ↓
4. User enters inviter's details (name + email/mobile)
   ↓
5. System verifies matching family member exists
   ↓
6a. [VERIFIED] → Create approval request, assign temporary code, send notification
   ↓
6b. [NOT VERIFIED] → Show error, prompt for correct details
   ↓
7. User registration completes with temporary code
   ↓
8. Inviter receives notification + approval link
   ↓
9. Inviter approves/denies
   ↓
10a. [APPROVED] → Replace temp code with real code, link to family
   ↓
10b. [DENIED] → Keep temp code, send notification to user
   ↓
11. (After 7 days, auto-expire if not approved)
```

---

## 4. Frontend Architecture

### Blade Component: `family-code-verification`

**Location:** `resources/views/components/auth/family-code-verification.blade.php`

**Features:**
- Alpine.js for reactive UI
- Two-stage form: code input → inviter verification
- Real-time code validation via AJAX
- Error handling & user feedback
- Loading states for async operations

**Props:**
- `familyCode` (string): Pre-filled family code if available
- `errors` (array): Validation errors from backend

**Events:**
- `checkFamilyCode()` - Validates code existence
- `verifyInviter()` - Verifies inviter matching

---

## 5. Backend Architecture

### Service: `FamilyCodeApprovalService`

**Location:** `app/service/FamilyCodeApprovalService.php`

**Core Methods:**
- `familyCodeExists()` - Check if code is registered
- `createApprovalRequest()` - Create pending request
- `findMatchingInviter()` - Fuzzy-match inviter by name & contact
- `approveRequest()` - Set status to 'approved'
- `denyRequest()` - Set status to 'denied'
- `expireOldRequests()` - Cron: expire 7-day-old requests
- `getRequestsNeedingReminders()` - Cron: fetch 2-day-old requests
- `linkUserToFamily()` - Replace temp code with real code

**Fuzzy Matching:** Names are matched using Levenshtein distance ≤ 2 chars to handle typos.

### Controller: `FamilyCodeApprovalController`

**Location:** `app/controller/auth/FamilyCodeApprovalController.php`

**Endpoints:**
- `POST /api/family-code/check` - Check if code exists
- `POST /api/family-code/verify-inviter` - Verify inviter details
- `POST /api/family-code/complete-registration` - Finalize registration with approval request
- `POST /api/family-code/approve/{requestId}` - Approve request
- `POST /api/family-code/deny/{requestId}` - Deny request

**Security:**
- CSRF token validation on all POST requests
- Rate limiting per IP (existing middleware)
- No information disclosure (generic error messages)

---

## 6. Cron Jobs

### `FamilyCodeApprovalReminder`

**Location:** `app/cron/FamilyCodeApprovalReminder.php`

**Job 1: Send Reminders** (Daily)
- Find requests created 2+ days ago without reminder
- Send email to approver: "Please review this pending request"
- Send email to requester: "Your approval request expires in X days"
- Mark reminder as sent

**Job 2: Expire Requests** (Daily)
- Find requests with `request_expires_at < NOW()`
- Set status to 'expired'
- Log count for monitoring

---

## 7. Notification Templates

### Email: Approval Request

**Template:** `resources/views/mail/family-approval-request.blade.php`

Sent to inviter when new registration is verified.

**Variables:**
- `approverName` - Name of approver
- `requesterName` - Name of new user
- `requesterEmail` - Email of new user
- `requestId` - Request ID for reference
- `approvalUrl` - Link to approve
- `denyUrl` - Link to deny

### In-App Notifications

Will use existing `PushNotificationClass` for:
- Approval request notification
- Approval confirmation notification
- Reminder notifications (2-day)

---

## 8. Test Coverage

### Feature Tests: `FamilyCodeApprovalFlowTest.php`

- ✅ Family code existence check
- ✅ Temporary code generation uniqueness
- ✅ Approval request creation
- ✅ Inviter matching (exact & fuzzy)
- ✅ Duplicate prevention
- ✅ Request approval
- ✅ Request denial
- ✅ Request expiration
- ✅ Reminder retrieval
- ✅ User-family linking

**Test Database:** Dedicated test DB with seeded data

**Coverage Target:** 90%+

---

## 9. Security Considerations

### Input Validation
- All user inputs sanitized before DB insert
- Email/mobile format validated
- Family code alphanumeric only
- Max length enforcement

### Authorization
- Only inviter (via email/mobile match) can approve request for their family
- User cannot approve their own request
- Deleted requests cannot be re-approved

### CSRF Protection
- All POST endpoints require valid XSRF-TOKEN header
- Token rotated per request

### Rate Limiting
- Per-IP rate limiting on verification endpoints
- Prevent brute-force inviter discovery

### Information Disclosure
- Generic error messages (no leaking of valid emails/names)
- 404 for non-existent requests (no enumeration)
- Approval links include secure token (not just requestId)

---

## 10. Deployment Checklist

- [ ] Run migration: `mysql -u root family < migrations/2026_09_04_family_approval_requests.sql`
- [ ] Include Blade component in registration form
- [ ] Register API routes in main router
- [ ] Add cron jobs to scheduler (daily, 2am & 3am)
- [ ] Deploy notification service integration
- [ ] Run test suite: `phpunit tests/Feature/FamilyCodeApprovalFlowTest.php`
- [ ] Code review by security team
- [ ] Staging deployment + manual testing
- [ ] Monitor error logs for first week
- [ ] Update user documentation

---

## 11. Rollback Plan

If critical issues discovered:

1. Disable feature flag (if using feature flags)
2. Revert to allowing direct code registration without approval
3. Manually approve existing pending requests
4. Restore from pre-migration DB backup if data corruption

---

## 12. Future Enhancements

- [ ] Allow inviter to whitelist email domains
- [ ] Bulk invitations (invite multiple at once)
- [ ] SMS-based approval verification
- [ ] Approval delegation (inviter can delegate to someone else)
- [ ] Admin panel for managing requests
- [ ] Analytics dashboard (approval rates, avg approval time)

---

## 13. Files Included

**Migrations:**
- `migrations/2026_09_04_family_approval_requests.sql`

**Backend:**
- `app/service/FamilyCodeApprovalService.php`
- `app/controller/auth/FamilyCodeApprovalController.php`
- `app/router/familyCodeApprovalRoute.php`
- `app/cron/FamilyCodeApprovalReminder.php`

**Frontend:**
- `resources/views/components/auth/family-code-verification.blade.php`
- `resources/views/mail/family-approval-request.blade.php`

**Tests:**
- `tests/Feature/FamilyCodeApprovalFlowTest.php`

**Documentation:**
- `docs/FAMILY_CODE_APPROVAL_SPEC.md` (this file)

---

## 14. Questions for Review

1. Should we allow multiple approvers, or just the inviter?
2. Should approval require email confirmation link?
3. Should we track denial reasons?
4. Should expired requests be auto-cleaned after N days?
5. Should we send SMS in addition to email?

---

**Prepared by:** Claude Code  
**Date:** 2026-09-04  
**Status:** ⏳ Awaiting TAT Approval
