describe('Authentication Flow', () => {
    beforeEach(() => {
        // Clear rate limits before running auth tests
        cy.request({
            url: '/tests/clear-rate-limit',
            failOnStatusCode: false
        });
    });

    it('successfully logs in with valid credentials', () => {
        cy.visit('/login');
        cy.get('form#login').should('be.visible');
        cy.get('input[name="email"]').type('waleolaogunrac@gmail.com');
        cy.get('input[name="password"]').type('National2');
        cy.get('button#button').click();
        
        // Login only sends the 2FA code at this point (mandatory 2FA) - it does not
        // fully authenticate the session. The login form's notification div (not
        // SweetAlert2, which the login flow doesn't use) shows the "code sent" message,
        // then the app redirects to /login/code.
        cy.get('#login_notification', {timeout: 10000}).should('be.visible')
            .and('contain.text', 'Verification code sent');
        cy.url({timeout: 10000}).should('include', '/login/code');
    });
});
