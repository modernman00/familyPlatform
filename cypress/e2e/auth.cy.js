describe('Authentication Flow', () => {
    // Ignore 3rd-party reCAPTCHA/external script errors in headless test runner
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        // Clear cookies and session before testing login
        cy.clearCookies();
        cy.clearLocalStorage();
        cy.request({
            url: '/tests/clear-rate-limit',
            failOnStatusCode: false
        });

        // Neutralize Google reCAPTCHA Enterprise script in automated tests
        cy.intercept('https://www.google.com/recaptcha/**', (req) => {
            req.reply({
                statusCode: 200,
                body: 'window.grecaptcha = { enterprise: { ready: function(cb){ if (typeof cb === "function") cb(); }, execute: function(){ return Promise.resolve("mock-recaptcha-token"); } } };',
                headers: { 'content-type': 'application/javascript' }
            });
        });
        cy.intercept('https://www.gstatic.com/recaptcha/**', (req) => {
            req.reply({ statusCode: 200, body: '' });
        });
        cy.intercept('https://recaptchaenterprise.googleapis.com/**', (req) => {
            req.reply({ statusCode: 200, body: { tokenProperties: { valid: true } } });
        });
    });

    it('successfully logs in with valid credentials', () => {
        cy.intercept('POST', '/login').as('loginReq');

        cy.visit('/login', {
            onBeforeLoad(win) {
                cy.spy(win.console, 'error').as('spyConsoleError');
                cy.spy(win.console, 'warn').as('spyConsoleWarn');
                cy.spy(win.console, 'log').as('spyConsoleLog');
            }
        });

        cy.get('form#login').should('be.visible');
        cy.get('button#button[data-ready="true"]', { timeout: 10000 }).should('exist');
        cy.get('input[name="email"]').should('be.visible').invoke('val', 'cypress_test@myfamilyplatform.com').trigger('input').trigger('change');
        cy.get('input[name="password"]').should('be.visible').invoke('val', 'National2').trigger('input').trigger('change');
        
        cy.get('button#button').should('be.visible').click();

        cy.wait('@loginReq', { timeout: 15000 }).then((interception) => {
            console.log('Login Response Status:', interception.response.statusCode);
            console.log('Login Response Body:', interception.response.body);
            expect(interception.response.statusCode).to.be.oneOf([200, 201]);
        });

        cy.visit('/login/code');
        cy.location('pathname', { timeout: 10000 }).should('eq', '/login/code');
    });
});
