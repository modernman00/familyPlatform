/**
 * Register v2 — guided 3-step sign-up on /register.
 *
 * Covers: step navigation and per-step validation, Enter-to-advance, the
 * password helpers, the language menu, invite links starting on step 2, the
 * join/create modes, every post-submit dialog (stubbed), and two real
 * submissions against the local server (new family + unknown family code).
 *
 * `?src=qa` stops the local controller pre-filling its John Doe demo data.
 */
describe('Register v2 — guided sign-up', () => {
  Cypress.on('uncaught:exception', () => false);

  const created = [];

  const hideOverlays = () =>
    cy.document().then((doc) => {
      const s = doc.createElement('style');
      s.textContent = '#cookieBanner,#push-notification-banner,.pwa-install-banner{display:none!important}';
      doc.head.appendChild(s);
    });

  const visitRegister = (path = '/register?src=qa') => {
    cy.visit(path);
    cy.get('#fpr', { timeout: 15000 }).should('be.visible');
    hideOverlays();
  };

  const fillAboutYou = (email) => {
    cy.get('#firstName').clear().type('Cyra');
    cy.get('#lastName').clear().type('Testwell');
    cy.get('#day').select('4');
    cy.get('#month').select('Jun');
    cy.get('#year').select('1988');
    cy.get('#email').clear().type(email);
    cy.get('#mobile').clear().type('+44 7700900123');
  };

  const fillSecure = () => {
    cy.get('#password').type('Abcdefg1x');
    cy.get('#checkbox').check({ force: true });
  };

  beforeEach(() => {
    cy.request({ url: '/api/test/clear-rate-limits', failOnStatusCode: false });
  });

  after(() => {
    if (!created.length) return;
    cy.wrap(created).each((email) => {
      cy.task('findRegisteredUser', email).then((user) => {
        if (user) cy.task('cleanupE2EUsers', [user]);
      });
    });
  });

  describe('layout and navigation', () => {
    beforeEach(() => visitRegister());

    it('starts on step 1 with "Start a new family" selected and the language menu in the card', () => {
      cy.get('[data-fpr-step="1"]').should('be.visible');
      cy.get('[data-fpr-step="2"]').should('not.be.visible');
      cy.get('#fprStepper li[data-step="1"]').should('have.attr', 'data-state', 'current');
      cy.get('input[name="account_type"][value="create"]').should('be.checked');
      cy.get('#familySurname').should('be.visible');
      cy.get('#famCode').should('be.disabled');
      cy.get('#fprBack').should('not.be.visible');
      cy.get('#btnSubmit').should('not.be.visible');

      cy.get('#fprLangBtn').should('be.visible').click();
      cy.get('.fpr-lang .dropdown-item').should('have.length.at.least', 2).each(($a) => {
        expect($a.outerHeight(), 'language row height').to.be.at.least(44);
      });
    });

    it('blocks Continue until each step is valid and keeps values when going back', () => {
      cy.get('#fprNext').click();
      cy.get('#familySurname_error').should('not.be.empty');
      cy.get('[data-fpr-step="1"]').should('be.visible');

      cy.get('#familySurname').type('Testwell');
      cy.get('[data-fpr-tree="family"]').should('have.text', 'Testwell');
      cy.get('#fprNext').click();
      cy.get('[data-fpr-step="2"]').should('be.visible');

      cy.get('#fprNext').click();
      ['firstName', 'lastName', 'Date_of_Birth', 'email', 'mobile'].forEach((id) => {
        cy.get(`#${id}_error`).should('not.be.empty');
      });

      fillAboutYou('cyra.nav@example.com');
      cy.get('[data-fpr-tree="you"]').should('have.text', 'Cyra');
      cy.get('#mobile').type('{enter}');
      cy.get('[data-fpr-step="3"]').should('be.visible');
      cy.get('#btnSubmit').should('be.visible');

      cy.get('#fprBack').click();
      cy.get('#firstName').should('have.value', 'Cyra');
    });

    it('password helpers: show/hide, rules, strength and the hidden confirm copy', () => {
      cy.get('#familySurname').type('Testwell');
      cy.get('#fprNext').click();
      fillAboutYou('cyra.pw@example.com');
      cy.get('#fprNext').click();

      cy.get('#password').type('abc');
      cy.get('#fprPwRules li[data-rule="len"]').should('have.attr', 'data-ok', 'false');
      cy.get('#password').type('DEF12');
      cy.get('#fprPwRules li[data-ok="true"]').should('have.length', 3);
      cy.get('#confirm_password').should('have.value', 'abcDEF12');
      cy.get('#fprPwToggle').click();
      cy.get('#password').should('have.attr', 'type', 'text');
      cy.get('#fprPwToggle').should('have.attr', 'aria-pressed', 'true');

      cy.get('#btnSubmit').click();
      cy.get('#checkbox_error').should('not.be.empty');
    });

    it('join mode shows the family code and warns (without blocking) on an unknown code', () => {
      cy.get('input[name="account_type"][value="join"]').check({ force: true });
      cy.get('#famCode').should('be.visible').and('not.be.disabled');
      cy.get('#familySurname').should('not.be.visible');
      cy.get('#famCode').type('ZZQ0000').blur();
      cy.get('.fpr-status--warn', { timeout: 8000 }).should('be.visible');
      cy.get('#fprNext').click();
      cy.get('[data-fpr-step="2"]').should('be.visible');
    });
  });

  describe('invitations', () => {
    it('a direct invite link starts on step 2 with one welcome block', () => {
      cy.request('/api/test/get-valid-family-code').then(({ body }) => {
        visitRegister(`/register?famCode=${body.code}&name=Kemi%20Testwell`);
        cy.get('#fpr').should('have.attr', 'data-start-step', '2');
        cy.get('[data-fpr-step="2"]').should('be.visible');
        cy.get('#fprStepper li[data-step="1"]').should('have.attr', 'data-state', 'done');
        cy.get('.fpr-welcome').should('have.length', 1).and('contain', 'Kemi');
        cy.get('.fpr-welcome-chip').should('contain', body.code);
        cy.get('.invited-relative-hero').should('not.exist');
        cy.get('#firstName').should('have.value', 'Kemi');

        cy.get('#fprBack').click();
        cy.get('input[name="account_type"][value="join"]').should('be.checked');
        cy.get('#famCode').should('have.value', body.code);
      });
    });

    it('an existing code typed by hand needs the inviter check before Continue', () => {
      cy.request('/api/test/get-valid-family-code').then(({ body }) => {
        visitRegister();
        cy.get('input[name="account_type"][value="join"]').check({ force: true });
        cy.get('#famCode').type(body.code).blur();
        cy.get('#inviter-check', { timeout: 10000 }).should('be.visible');
        cy.get('#fprNext').click();
        cy.get('#famCode_error').should('not.be.empty');
        cy.get('[data-fpr-step="1"]').should('be.visible');

        cy.get('#inviter-check').contains('button', 'family admin').click();
        cy.get('#inviter-verified', { timeout: 5000 }).should('be.visible');
        cy.get('#joining_via_invitation').should('have.value', 'true');
        cy.get('#fprNext').click();
        cy.get('[data-fpr-step="2"]').should('be.visible');
      });
    });
  });

  describe('after submit (stubbed responses)', () => {
    const goToSubmit = () => {
      visitRegister();
      cy.get('#familySurname').type('Testwell');
      cy.get('#fprNext').click();
      fillAboutYou('cyra.stub@example.com');
      cy.get('#fprNext').click();
      fillSecure();
    };

    it('new family: shows the family code dialog', () => {
      cy.intercept('POST', '/register', { statusCode: 200, body: { status: 'success', message: 'Hello Cyra - Your family has been created!', family_code: 'TES1234', code_not_found: '', show_code_modal: true, redirect: '/profilePage' } }).as('reg');
      goToSubmit();
      cy.get('#btnSubmit').click();
      // Same payload the shared handler sent: no submit/checkbox, hidden confirm copy.
      cy.wait('@reg').its('request.body').should((body) => {
        expect(body).to.include('name="familySurname"').and.include('name="confirm_password"\r\n\r\nAbcdefg1x');
        expect(body).to.not.include('name="checkbox"');
        expect(body).to.not.include('name="famCode"');
      });
      cy.get('#family-code-modal').should('be.visible');
      cy.get('#fprDoneTitle').should('contain', 'family has been created');
      cy.get('#code-display').should('have.text', 'TES1234');
      cy.get('#dashboard-btn').should('have.attr', 'href', '/profilePage');
    });

    it('unknown code: says the account exists but is not linked yet', () => {
      cy.intercept('POST', '/register', { statusCode: 200, body: { status: 'success', message: 'Hello Cyra - Your account has been created.', family_code: 'TES5678', code_not_found: 'ZZQ0000', show_code_modal: true, redirect: '/profilePage' } }).as('reg');
      goToSubmit();
      cy.get('#btnSubmit').click();
      cy.wait('@reg');
      cy.get('#fprDoneTitle').should('contain', 'account has been created');
      cy.get('#family-code-modal').should('contain', 'ZZQ0000').and('contain', 'All Members');
      cy.get('#code-display').should('have.text', 'TES5678');
    });

    it('joining a family: shows the pending message and a sign-in button', () => {
      cy.intercept('POST', '/register', { statusCode: 200, body: { status: 'success', message: 'Your registration is complete! An approval request has been sent.', token: '/login' } }).as('reg');
      goToSubmit();
      cy.get('#btnSubmit').click();
      cy.wait('@reg');
      cy.get('#fprDoneTitle').should('contain', 'signed up');
      cy.get('#code-display').should('not.exist');
      cy.get('#dashboard-btn').should('have.attr', 'href', '/login');
    });

    it('server error: shows it in the form without file paths and re-enables the button', () => {
      cy.intercept('POST', '/register', { statusCode: 500, body: { status: 'error', code: 500, message: 'Error on line 312 in /srv/app/Register.php: Your email is already registered' } }).as('reg');
      goToSubmit();
      cy.get('#btnSubmit').click();
      cy.wait('@reg');
      cy.get('#fprFormError').should('be.visible').and('contain', 'already registered').and('not.contain', '.php');
      cy.get('#family-code-modal').should('not.exist');
      cy.get('#btnSubmit').should('not.be.disabled');
    });

    it('sends exactly one request per click', () => {
      let calls = 0;
      cy.intercept('POST', '/register', (req) => { calls += 1; req.reply({ statusCode: 200, body: { status: 'success', message: 'ok', token: '/login' } }); }).as('reg');
      goToSubmit();
      cy.get('#btnSubmit').click();
      cy.wait('@reg');
      cy.wait(800).then(() => expect(calls, 'POST /register count').to.eq(1));
    });
  });

  describe('real submissions (local server)', () => {
    it('creates a new family and shows its code', () => {
      const email = `cyra.create.${Date.now()}@example.com`;
      created.push(email);
      visitRegister();
      cy.intercept('POST', '/register').as('reg');
      cy.get('#familySurname').type('Testwell');
      cy.get('#fprNext').click();
      fillAboutYou(email);
      cy.get('#fprNext').click();
      fillSecure();
      cy.get('#btnSubmit').click();
      // 200 even though the local mail server rejects @example.com: the welcome
      // email failure is logged, not shown.
      cy.wait('@reg', { timeout: 30000 }).its('response.statusCode').should('eq', 200);
      cy.get('#code-display', { timeout: 10000 }).invoke('text').should('match', /^TES[0-9A-Z]{4}$/);
    });

    it('an unknown family code still creates the account and says how to link later', () => {
      const email = `cyra.unknown.${Date.now()}@example.com`;
      created.push(email);
      visitRegister();
      cy.intercept('POST', '/register').as('reg');
      cy.get('input[name="account_type"][value="join"]').check({ force: true });
      cy.get('#famCode').type('ZZQ0001').blur();
      // Continue waits for the code check to finish before moving on.
      cy.get('#fprNext').click();
      cy.get('[data-fpr-step="2"]', { timeout: 10000 }).should('be.visible');
      fillAboutYou(email);
      cy.get('#fprNext').click();
      fillSecure();
      cy.get('#btnSubmit').click();
      cy.wait('@reg', { timeout: 30000 }).then(({ response }) => {
        expect(response.statusCode).to.eq(200);
        expect(response.body.code_not_found).to.eq('ZZQ0001');
        expect(response.body.family_code).to.not.eq('ZZQ0001');
      });
      cy.get('#fprDoneTitle').should('contain', 'account has been created');
      cy.get('#family-code-modal').should('contain', 'ZZQ0001');
    });
  });
});
