Cypress.Commands.add('login', (email, password) => {
    cy.visit('/login');
    cy.get('form#login').should('be.visible');
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    
    cy.get('button#button').click();
    
    // Wait for redirect to happen (wait for 4000ms because there's a 2000ms timeout in http.js)
    cy.url({timeout: 10000}).should('not.include', '/login');
});

/**
 * Resets test rate limit buckets for the current test IP/session.
 */
Cypress.Commands.add('resetRateLimits', () => {
    cy.request({
        url: '/api/test/clear-rate-limits',
        failOnStatusCode: false
    });
});

/**
 * Deterministically clears all browser cookies and session storage.
 */
Cypress.Commands.add('clearAuthCookies', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
});

