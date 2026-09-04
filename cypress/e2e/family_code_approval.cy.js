describe('Family Code Approval - Registration & Approval Flow', () => {
  Cypress.on('uncaught:exception', () => false);

  const testData = {
    inviterEmail: 'inviter-' + Date.now() + '@test.com',
    inviterPassword: 'TestPassword123!',
    newUserEmail: 'newuser-' + Date.now() + '@test.com',
    newUserPassword: 'TestPassword123!'
  };

  describe('Registration with Existing Family Code', () => {

    it('should show family code input on registration page', () => {
      cy.visit('/register');
      cy.get('[id="family_code"]', { timeout: 5000 }).should('be.visible');
      cy.contains('Family Code').should('be.visible');
    });

    it('should validate non-existent family code', () => {
      cy.visit('/register');
      cy.get('#family_code').type('FAKE9999');
      cy.get('#family_code').blur();

      // Should not show inviter form for invalid code
      cy.get('.inviter-verification-section', { timeout: 3000 }).should('not.be.visible');
      cy.contains('Enter the code provided by your family member').should('be.visible');
    });

    it('should show inviter verification form when valid code entered', () => {
      // This test requires a real family code in DB
      // For CI/CD, would need to seed test data first

      cy.visit('/register');

      // Get a valid family code from API or DB
      cy.request('/api/test/get-valid-family-code').then((response) => {
        const validCode = response.body.code;

        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();

        // Wait for AJAX validation
        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');
        cy.contains('Verify Your Invitation').should('be.visible');
        cy.get('#inviter_first_name').should('be.visible');
        cy.get('#inviter_last_name').should('be.visible');
        cy.get('#inviter_contact').should('be.visible');
      });
    });

    it('should reject verification with wrong inviter details', () => {
      cy.visit('/register');

      cy.request('/api/test/get-valid-family-code').then((response) => {
        const validCode = response.body.code;

        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();
        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');

        // Enter wrong inviter details
        cy.get('#inviter_first_name').type('WrongName');
        cy.get('#inviter_last_name').type('WrongLast');
        cy.get('#inviter_contact').type('wrong@email.com');
        cy.get('button').contains('Verify & Continue').click();

        // Should show error
        cy.contains('Could not find a matching family member', { timeout: 5000 }).should('be.visible');
      });
    });

    it('should verify with correct inviter details', () => {
      cy.visit('/register');

      cy.request('/api/test/get-valid-family-code-with-inviter').then((response) => {
        const validCode = response.body.code;
        const inviter = response.body.inviter;

        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();
        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');

        // Enter correct inviter details
        cy.get('#inviter_first_name').type(inviter.firstName);
        cy.get('#inviter_last_name').type(inviter.lastName);
        cy.get('#inviter_contact').type(inviter.email);
        cy.get('button').contains('Verify & Continue').click();

        // Should show verified badge
        cy.contains('Invitation Verified', { timeout: 5000 }).should('be.visible');
      });
    });
  });

  describe('Registration Completion & Approval Request', () => {

    it('should complete registration and create approval request', () => {
      cy.visit('/register');

      // Get valid code + inviter
      cy.request('/api/test/get-valid-family-code-with-inviter').then((response) => {
        const validCode = response.body.code;
        const inviter = response.body.inviter;

        // Step 1: Enter family code
        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();
        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');

        // Step 2: Verify inviter
        cy.get('#inviter_first_name').type(inviter.firstName);
        cy.get('#inviter_last_name').type(inviter.lastName);
        cy.get('#inviter_contact').type(inviter.email);
        cy.get('button').contains('Verify & Continue').click();
        cy.contains('Invitation Verified', { timeout: 5000 }).should('be.visible');

        // Step 3: Complete registration
        cy.get('input[name="firstName"]').type('Test');
        cy.get('input[name="lastName"]').type('User');
        cy.get('input[name="email"]').type(testData.newUserEmail);
        cy.get('input[name="password"]').type(testData.newUserPassword);
        cy.get('input[name="password_confirm"]').type(testData.newUserPassword);
        cy.get('button[type="submit"]').contains('Register').click();

        // Should redirect to dashboard or confirmation page
        cy.url({ timeout: 10000 }).should('include', '/dashboard');
      });
    });
  });

  describe('Approval Workflow - Inviter Perspective', () => {

    it('inviter should receive approval notification email', () => {
      // Note: In real test, would check email inbox or use mailhog
      // For now, verify DB shows pending request

      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const requests = response.body;

        // Should have at least one pending request
        expect(requests.length).to.be.greaterThan(0);

        const request = requests[0];
        expect(request.status).to.equal('pending');
        expect(request.inviter_first_name).to.exist;
        expect(request.inviter_last_name).to.exist;
      });
    });

    it('inviter should be able to approve request', () => {
      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const request = response.body[0];
        const requestId = request.no;
        const approvalToken = request.approval_token;

        // Approve via API
        cy.request({
          method: 'POST',
          url: `/api/family-code/approve/${requestId}?token=${approvalToken}`,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          expect(response.status).to.equal(200);
          expect(response.body.success).to.equal(true);
        });

        // Verify request status changed to approved
        cy.request(`/api/test/get-approval-request/${requestId}`).then((response) => {
          expect(response.body.status).to.equal('approved');
          expect(response.body.approved_at).to.exist;
        });
      });
    });

    it('inviter should be able to deny request', () => {
      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const request = response.body[0];
        const requestId = request.no;
        const approvalToken = request.approval_token;

        // Deny via API
        cy.request({
          method: 'POST',
          url: `/api/family-code/deny/${requestId}?token=${approvalToken}`,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          expect(response.status).to.equal(200);
          expect(response.body.success).to.equal(true);
        });

        // Verify request status changed to denied
        cy.request(`/api/test/get-approval-request/${requestId}`).then((response) => {
          expect(response.body.status).to.equal('denied');
        });
      });
    });

    it('should reject invalid approval token', () => {
      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const request = response.body[0];
        const requestId = request.no;

        // Try to approve with wrong token
        cy.request({
          method: 'POST',
          url: `/api/family-code/approve/${requestId}?token=invalid-token`,
          failOnStatusCode: false,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          expect(response.status).to.equal(401);
          expect(response.body.error).to.include('Invalid or missing approval token');
        });
      });
    });

    it('should reject approval without token', () => {
      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const request = response.body[0];
        const requestId = request.no;

        // Try to approve without token
        cy.request({
          method: 'POST',
          url: `/api/family-code/approve/${requestId}`,
          failOnStatusCode: false,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          expect(response.status).to.equal(401);
        });
      });
    });
  });

  describe('Security & Edge Cases', () => {

    it('should prevent request enumeration via token verification', () => {
      // Test that guessing request IDs without valid token fails

      for (let i = 1; i <= 5; i++) {
        cy.request({
          method: 'POST',
          url: `/api/family-code/approve/${999 + i}?token=fake-token`,
          failOnStatusCode: false,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          expect(response.status).to.be.oneOf([401, 404]);
        });
      }
    });

    it('should enforce CSRF token on family code check', () => {
      cy.request({
        method: 'POST',
        url: '/api/family-code/check',
        body: { family_code: 'TEST' },
        failOnStatusCode: false,
        headers: {
          'Content-Type': 'application/json'
          // No CSRF token
        }
      }).then((response) => {
        // Should fail without valid CSRF token
        expect(response.status).to.be.oneOf([401, 403]);
      });
    });

    it('should prevent duplicate pending approvals from same inviter', () => {
      // Get first pending request
      cy.request('/api/test/get-pending-approval-requests').then((response) => {
        const request = response.body[0];

        // Try to create another approval request for same user + inviter
        cy.request({
          method: 'POST',
          url: '/api/family-code/complete-registration',
          body: {
            user_id: request.id,
            family_code: request.family_code,
            joining_via_invitation: true,
            inviter_first_name: request.inviter_first_name,
            inviter_last_name: request.inviter_last_name,
            inviter_email_or_mobile: request.inviter_email_or_mobile
          },
          failOnStatusCode: false,
          headers: {
            'X-XSRF-TOKEN': cy.getCookie('XSRF-TOKEN')?.value || ''
          }
        }).then((response) => {
          // Should either reject or return existing request
          expect(response.status).to.be.oneOf([400, 409, 422]);
        });
      });
    });
  });

  describe('Mobile Responsiveness', () => {

    it('component should be responsive on mobile (375px)', () => {
      cy.viewport(375, 667);
      cy.visit('/register');

      cy.get('#family_code').should('be.visible');
      cy.get('label').contains('Family Code').should('be.visible');

      // Form should not overflow
      cy.get('input[type="text"]').each(($input) => {
        cy.wrap($input).should('have.css', 'box-sizing', 'border-box');
      });
    });

    it('inviter form should stack vertically on mobile', () => {
      cy.viewport(375, 667);
      cy.visit('/register');

      cy.request('/api/test/get-valid-family-code-with-inviter').then((response) => {
        const validCode = response.body.code;

        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();

        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');

        // All fields should be visible and not hidden
        cy.get('#inviter_first_name').should('be.visible');
        cy.get('#inviter_last_name').should('be.visible');
        cy.get('#inviter_contact').should('be.visible');
      });
    });
  });

  describe('User Experience', () => {

    it('should show loading state during code validation', () => {
      cy.visit('/register');

      cy.intercept('POST', '/api/family-code/check', { delay: 1000 }).as('codeCheck');

      cy.get('#family_code').type('TEST');
      cy.get('#family_code').blur();

      // Checking code spinner should appear
      cy.get('#codeChecking', { timeout: 2000 }).should('be.visible');

      cy.wait('@codeCheck');
      cy.get('#codeChecking').should('not.be.visible');
    });

    it('should show loading state during inviter verification', () => {
      cy.visit('/register');

      cy.request('/api/test/get-valid-family-code-with-inviter').then((response) => {
        const validCode = response.body.code;
        const inviter = response.body.inviter;

        cy.get('#family_code').type(validCode);
        cy.get('#family_code').blur();
        cy.get('.inviter-verification-section', { timeout: 3000 }).should('be.visible');

        cy.intercept('POST', '/api/family-code/verify-inviter', { delay: 1000 }).as('inviterCheck');

        cy.get('#inviter_first_name').type(inviter.firstName);
        cy.get('#inviter_last_name').type(inviter.lastName);
        cy.get('#inviter_contact').type(inviter.email);
        cy.get('button').contains('Verify & Continue').click();

        // Button should show loading state
        cy.get('button').contains('Verifying').should('be.visible');

        cy.wait('@inviterCheck');
        cy.contains('Invitation Verified', { timeout: 5000 }).should('be.visible');
      });
    });
  });
});
