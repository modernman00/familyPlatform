// Logs in fully, including the mandatory 2FA step. The 2FA code is read directly
// from the local dev DB's code_mgt table (via the cypress.config.js task) rather
// than an inbox, since there's no test-only bypass for the emailed code.
export function loginFully(email = 'cypress_test@myfamilyplatform.com', password = 'National2') {
    cy.session([email, password], () => {
        cy.visit('/login');
        cy.get('form#login').should('be.visible');
        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(password);
        cy.get('button#button').click();

        cy.url({ timeout: 10000 }).should('include', '/login/code');

        cy.task('getLatest2FACode', email).then((code) => {
            expect(code, '2FA code from code_mgt').to.be.a('string');
            cy.get('input#code').type(code);
            cy.get('button#button').click();
        });

        cy.url({ timeout: 10000 }).should('include', '/profilePage');
    });

    cy.visit('/profilePage');
}
