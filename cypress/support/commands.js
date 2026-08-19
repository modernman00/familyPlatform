Cypress.Commands.add('login', (email, password) => {
    cy.visit('/login');
    cy.get('form#login').should('be.visible');
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    
    cy.get('button#button').click();
    
    // Wait for redirect to happen (wait for 4000ms because there's a 2000ms timeout in http.js)
    cy.url({timeout: 10000}).should('not.include', '/login');
});
