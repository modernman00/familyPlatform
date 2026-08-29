// Logs in fully, including the mandatory 2FA step. The 2FA code is read directly
// from the local dev DB's code_mgt table (via the cypress.config.js task) rather
// than an inbox, since there's no test-only bypass for the emailed code.
export function loginFully(email = 'cypress_test@myfamilyplatform.com', password = 'National2') {
    // Clear the brute-force limits via the test-only endpoint before logging in
    cy.request({
        url: '/tests/clear-rate-limit',
        failOnStatusCode: false // if it 404s/403s, don't crash the test immediately
    });

    cy.session([email, password], () => {
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

        cy.intercept('POST', '/login').as('loginPost');
        cy.intercept('POST', '/login/code').as('codePost');

        cy.visit('/login');
        cy.get('form#login').should('be.visible');
        cy.get('button#button[data-ready="true"]', { timeout: 10000 }).should('be.visible');
        cy.get('input[name="email"]').should('be.visible').invoke('val', email).trigger('input').trigger('change');
        cy.get('input[name="password"]').should('be.visible').invoke('val', password).trigger('input').trigger('change');
        cy.get('button#button[data-ready="true"]').click();

        cy.wait('@loginPost', { timeout: 15000 });
        cy.visit('/login/code');

        cy.get('button#button', { timeout: 10000 }).should('be.visible');
        cy.task('getLatest2FACode', email).then((code) => {
            expect(code, '2FA code from code_mgt').to.be.a('string');
            cy.get('input#code').should('be.visible').invoke('val', code).trigger('input').trigger('change');
            cy.get('button#button').should('be.visible').click();
            cy.wait('@codePost', { timeout: 15000 });
            cy.visit('/profilePage');
        });
    });

    cy.visit('/profilePage');
}
