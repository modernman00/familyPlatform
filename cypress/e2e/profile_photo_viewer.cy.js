import { loginFully } from '../support/login';

describe('Profile Photo Viewer (Facebook-Style Lightbox)', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('expands profile photo into Facebook-style lightbox on click', () => {
        // Locate profile avatar
        cy.get('.avatar.profileImg, [data-profile-expand="true"]').first().should('be.visible');

        // Click the avatar
        cy.get('.avatar.profileImg, [data-profile-expand="true"]').first().click({ force: true });

        // Lightbox overlay should appear with active class
        cy.get('#pfViewerOverlay', { timeout: 8000 }).should('be.visible').and('have.class', 'pf-active');

        // Main enlarged photo should be visible
        cy.get('#pfViewerMainImg').should('be.visible');

        // Verify HD Zoom toggle
        cy.get('#pfViewerZoomBtn').click();
        cy.get('#pfViewerMainImg').should('have.class', 'pf-zoomed');

        // Zoom out
        cy.get('#pfViewerZoomBtn').click();
        cy.get('#pfViewerMainImg').should('not.have.class', 'pf-zoomed');

        // Verify Close button dismisses lightbox
        cy.get('#pfViewerCloseBtn').click();
        cy.get('#pfViewerOverlay').should('not.have.class', 'pf-active');
    });

    it('dismisses lightbox via Escape key', () => {
        cy.get('.avatar.profileImg, [data-profile-expand="true"]').first().click({ force: true });
        cy.get('#pfViewerOverlay').should('be.visible').and('have.class', 'pf-active');

        // Press Escape
        cy.get('body').type('{esc}');
        cy.get('#pfViewerOverlay').should('not.have.class', 'pf-active');
    });
});
