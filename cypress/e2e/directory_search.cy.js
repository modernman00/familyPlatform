import { loginFully } from '../support/login';

describe('Directory Search and Discovery', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('can view members outside the family via Directory Search and search the whole directory', () => {
        cy.visit('/allMembers');

        // Verify member cards rendered
        cy.get('.member-card', { timeout: 10000 }).should('have.length.at.least', 1);

        // 1. Initially, connected members are visible
        cy.get('.member-card[data-category="family"], .member-card[data-category="connected"]')
            .filter(':visible')
            .should('have.length.at.least', 1);

        // 2. Click "Directory Search" tab to browse members outside the family
        cy.get('[data-filter="explore"]').click();
        cy.get('.member-card[data-category="explore"]', { timeout: 10000 })
            .filter(':visible')
            .should('have.length.at.least', 1);
        cy.get('.role-chip-directory').filter(':visible').should('exist');

        // 3. Search for a member outside the family by name
        cy.get('#searchFamily').clear().type('Dayo');
        cy.get('.member-card').should('contain.text', 'Dayo');
        cy.get('.member-card .role-chip-directory').should('exist');

        // 4. Clear search input and verify restoration
        cy.get('#searchFamily').clear();
        cy.get('.member-card').should('have.length.at.least', 1);
    });
});
