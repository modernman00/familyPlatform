# TFT Testing Guide: Family Code Approval Feature

**Version:** 1.0  
**For:** QA/Testing Team  
**Environment:** Staging  
**Test Date:** [TO BE FILLED]

---

## Pre-Testing Checklist

- [ ] Migration has been run: `mysql family < migrations/2026_09_04_family_approval_requests.sql`
- [ ] All API routes registered in main router
- [ ] Blade component integrated into registration form
- [ ] Cron jobs scheduled (or manually triggered for testing)
- [ ] Notifications service connected to PushNotificationClass
- [ ] Test data seeded (2 accounts with family codes)

---

## Test Scenarios

### Test 1: New User Registers Without Family Code

**Steps:**
1. Navigate to registration page
2. Leave "Family Code" field empty
3. Fill in required fields (name, email, password)
4. Submit form

**Expected Result:**
- ✅ Registration succeeds
- ✅ User assigned new family code
- ✅ Can access family dashboard immediately
- ✅ Email confirmation sent

**Screenshots:** [To capture]

---

### Test 2: New User Enters Non-Existent Family Code

**Steps:**
1. Navigate to registration page
2. Enter "FAKE1234" in Family Code field
3. Click away from field (blur event)
4. Wait for code check to complete

**Expected Result:**
- ✅ Code validation fails (AJAX call returns `exists: false`)
- ✅ Inviter verification form NOT shown
- ✅ Help text: "Enter the code provided by your family member"
- ✅ No loading spinner shown

**Screenshots:** [To capture]

---

### Test 3: New User Enters Existing Family Code

**Steps:**
1. Navigate to registration page
2. Enter valid family code (e.g., "SMITH999") that belongs to existing user
3. Click away from field (blur event)
4. Wait for validation

**Expected Result:**
- ✅ Code validation succeeds (AJAX call returns `exists: true`)
- ✅ Form transitions to "Verify Your Invitation" section
- ✅ Section shows: "Inviter First Name", "Inviter Last Name", "Inviter Email or Mobile"
- ✅ "Verify & Continue" button appears (disabled until all fields filled)
- ✅ Smooth fade-in animation

**Screenshots:** [To capture]

---

### Test 4: Inviter Details - All Fields Filled Correctly

**Steps:**
1. Complete Test 3 (code exists, form shown)
2. Fill in inviter details:
   - First Name: "John"
   - Last Name: "Smith"
   - Email: "john.smith@example.com" (email of actual inviter)
3. Click "Verify & Continue"
4. Wait for verification

**Expected Result:**
- ✅ Button shows loading spinner: "Verifying..."
- ✅ AJAX call to `/api/family-code/verify-inviter` succeeds
- ✅ Form transitions to green checkmark badge
- ✅ Message: "Invitation Verified! A notification has been sent to John Smith."
- ✅ Registration can proceed

**Test as multiple users:**
- Test with exact name match
- Test with minor typos (1-2 char difference) - should still match
- Test with wrong email - should fail

**Screenshots:** [To capture]

---

### Test 5: Inviter Details - Incorrect Information

**Steps:**
1. Complete Test 3 (code exists, form shown)
2. Fill in incorrect inviter details:
   - First Name: "Jane"
   - Last Name: "Doe"
   - Email: "jane@example.com" (does NOT exist in family)
3. Click "Verify & Continue"

**Expected Result:**
- ✅ Error message: "Could not find a matching family member with the provided information. Please check and try again."
- ✅ Form remains visible for re-entry
- ✅ No temporary code assigned
- ✅ No notification sent

**Screenshots:** [To capture]

---

### Test 6: Registration Completes with Approval Request

**Steps:**
1. Complete Test 4 (inviter verified)
2. Continue with normal registration (email, password, etc.)
3. Submit registration form

**Expected Result:**
- ✅ Registration succeeds
- ✅ New user can log in immediately
- ✅ New user gets temporary code (e.g., "TEMP_abc123def456")
- ✅ New user NOT yet linked to family network (can't see family posts)
- ✅ Inviter receives email notification: "New Family Network Request"
- ✅ Email includes:
  - New user's name & email
  - "Review & Approve Request" button
  - "Deny Request" link
  - 7-day expiration warning

**Screenshots:** [To capture - registration, email]

---

### Test 7: Inviter Approves Request

**Steps:**
1. Complete Test 6 (new user registered, inviter notified)
2. Inviter checks email, clicks "Review & Approve Request"
3. (Optional) Approval page shows request details
4. Click "Approve"

**Expected Result:**
- ✅ Approval succeeds with message: "Request approved! User linked to family."
- ✅ New user's temporary code replaced with family code
- ✅ New user can now access family network
- ✅ New user receives email: "Welcome to the family!"
- ✅ Database shows: `status = 'approved'`, `approved_at = NOW()`

**Screenshots:** [To capture]

---

### Test 8: Inviter Denies Request

**Steps:**
1. Create new approval request (repeat Test 6)
2. Inviter checks email, clicks "Deny Request"

**Expected Result:**
- ✅ Denial succeeds with message: "Request denied."
- ✅ New user's temporary code remains active (NOT deleted)
- ✅ New user can still log in and use platform
- ✅ New user NOT linked to family network
- ✅ New user receives email: "Your family network request was denied"
- ✅ Database shows: `status = 'denied'`, `approved_at = NOW()`

**Screenshots:** [To capture]

---

### Test 9: Request Expires After 7 Days

**Steps:**
1. Create new approval request (repeat Test 6)
2. Manually set `request_expires_at` to past date in DB:
   ```sql
   UPDATE family_approval_requests 
   SET request_expires_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
   WHERE id = 'new-user-id';
   ```
3. Run cron job manually: `php app/cron/FamilyCodeApprovalReminder.php::expirePendingRequests()`
4. Check database

**Expected Result:**
- ✅ Request status changed to `expired`
- ✅ No error logs
- ✅ New user still can log in (temp code still valid)
- ✅ New user NOT linked to family network

**Screenshots:** [To capture - DB query result]

---

### Test 10: 2-Day Reminder Emails

**Steps:**
1. Create new approval request (repeat Test 6)
2. Manually set `created_at` to 2+ days ago:
   ```sql
   UPDATE family_approval_requests 
   SET created_at = DATE_SUB(NOW(), INTERVAL 3 DAY)
   WHERE id = 'new-user-id';
   ```
3. Run cron job manually: `php app/cron/FamilyCodeApprovalReminder.php::sendPendingReminders()`
4. Check emails (inviter + new user)

**Expected Result:**
- ✅ Inviter receives email: "Please review pending family network request from [user]"
- ✅ New user receives email: "Your family network request expires in 4 days"
- ✅ Database shows: `reminder_sent_at = NOW()`
- ✅ Cron runs without errors

**Screenshots:** [To capture - emails]

---

### Test 11: Multiple Pending Requests - Same Inviter Blocked

**Steps:**
1. Create 2 new accounts trying to join same family with same inviter
2. First account: Complete verification for inviter "John Smith"
3. Second account: Try to verify for same inviter "John Smith"

**Expected Result:**
- ✅ First account: Approval request created (status = pending)
- ✅ Second account: Error on verification: "A pending request from this inviter already exists" (or similar)
- ✅ OR: Allow second request but prevent duplicate approvals

**Note:** Current design allows multiple from DIFFERENT inviters - verify this

**Screenshots:** [To capture]

---

### Test 12: Blade Component Responsiveness

**Steps:**
1. Open registration page on mobile (375px width)
2. Test form field responsiveness
3. Test button sizing & touchability

**Expected Result:**
- ✅ Family code input readable & responsive
- ✅ Inviter form fields stack vertically
- ✅ Buttons sized for thumb interaction (min 44px height)
- ✅ Error messages display clearly
- ✅ Loading spinners visible

**Devices:**
- [ ] iPhone 12 Pro (390px)
- [ ] Samsung Galaxy S21 (360px)
- [ ] iPad (768px+)

**Screenshots:** [To capture]

---

### Test 13: CSRF Token Validation

**Steps:**
1. Make API request without XSRF-TOKEN header:
   ```bash
   curl -X POST http://staging.com/api/family-code/check \
     -H "Content-Type: application/json" \
     -d '{"family_code":"SMITH999"}'
   ```
2. Make API request with invalid XSRF token:
   ```bash
   curl -X POST http://staging.com/api/family-code/check \
     -H "X-XSRF-TOKEN: fake-token-here" \
     -H "Content-Type: application/json" \
     -d '{"family_code":"SMITH999"}'
   ```

**Expected Result:**
- ✅ Request without token: 403/401 Forbidden
- ✅ Request with invalid token: 403/401 Forbidden
- ✅ No data leaked in error response

**Screenshots:** [To capture - response headers]

---

### Test 14: Rate Limiting

**Steps:**
1. Make 10 rapid API calls to `/api/family-code/check` from same IP
2. Monitor for rate limit response

**Expected Result:**
- ✅ Requests succeed up to limit
- ✅ Exceeding limit returns 429 Too Many Requests
- ✅ Error message: "Rate limit exceeded. Try again later."
- ✅ Can verify by IP in logs

**Screenshots:** [To capture - rate limit response]

---

## Bug Report Template

**If issues found, use this template:**

```markdown
### Bug: [Short Title]

**Environment:** Staging  
**Date Found:** [Date]  
**Severity:** [Critical/High/Medium/Low]

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**


**Actual Result:**


**Screenshots/Video:**
[Attach if applicable]

**Logs:**
[Error logs if applicable]
```

---

## Performance Testing

### Load Test: 100 Concurrent Verification Requests

**Steps:**
1. Use Apache JMeter or similar tool
2. Configure 100 threads hitting `/api/family-code/verify-inviter`
3. Run for 60 seconds
4. Monitor database & server resources

**Acceptable Results:**
- ✅ Response time < 500ms (p99)
- ✅ CPU usage < 70%
- ✅ Memory < 80%
- ✅ Database connections stable
- ✅ No transaction deadlocks

**Report:** [To be filled by performance team]

---

## Database Integrity Checks

Run these queries to verify data integrity:

```sql
-- Check for orphaned requests
SELECT * FROM family_approval_requests 
WHERE id NOT IN (SELECT id FROM account);

-- Check for inconsistent statuses
SELECT * FROM family_approval_requests 
WHERE status NOT IN ('pending', 'approved', 'denied', 'expired');

-- Check for expired requests that are still pending
SELECT COUNT(*) FROM family_approval_requests 
WHERE status = 'pending' AND request_expires_at < NOW();

-- Check for duplicate pending from same inviter
SELECT id, approver_id, COUNT(*) 
FROM family_approval_requests 
WHERE status = 'pending' AND approver_id IS NOT NULL
GROUP BY id, approver_id
HAVING COUNT(*) > 1;
```

**Expected Results:** All queries return 0 rows

---

## Sign-Off

Testing completed by: ________________  
Date: ________________  
Status: [ ] PASS [ ] FAIL [ ] BLOCKERS

### Issues Found:
- [ ] None
- [ ] [List issues]

### Recommendation:
- [ ] Ready for production
- [ ] Ready with fixes
- [ ] Hold for further investigation

---

**For questions, contact:** [QA Lead Name]
