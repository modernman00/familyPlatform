import { loginFully } from '../support/login';

describe('Reels Feature', () => {

    // Ignore third-party script errors
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    // ------------------------------------------------------------------
    // REELS TRAY on /profilePage
    // ------------------------------------------------------------------
    describe('Reels Tray on Profile Page', () => {

        it('renders the reels tray with Facebook-style cards', () => {
            cy.get('.reels-tray-wrapper', { timeout: 15000 }).should('be.visible');
            // Should have at least the "Create Reel" card
            cy.get('.reel-fb-create-card, .reel-fb-card').should('have.length.gte', 1);
        });

        it('shows the Create Reel card with plus icon', () => {
            cy.get('.reel-fb-create-card', { timeout: 15000 }).should('be.visible');
            cy.get('.reel-fb-create-card').within(() => {
                cy.get('.bi-plus-lg, .bi-plus-circle').should('exist');
            });
        });

        it('opens the create reel modal when Create card is clicked', () => {
            cy.get('.reel-fb-create-card', { timeout: 15000 }).click();
            cy.get('#createReelModal, .modal.show', { timeout: 10000 }).should('be.visible');
        });
    });

    // ------------------------------------------------------------------
    // REELS THEATER (/reels page)
    // ------------------------------------------------------------------
    describe('Reels Theater Page', () => {

        it('loads the /reels page without 500 error', () => {
            cy.request({ url: '/reels', failOnStatusCode: false, followRedirect: false })
                .its('status')
                .should('be.oneOf', [200, 302]);
        });

        it('renders the reels theater viewport', () => {
            cy.visit('/reels');
            cy.get('.reels-theater-container, .reels-viewport', { timeout: 15000 }).should('exist');
        });
    });

    // ------------------------------------------------------------------
    // MOBILE RESPONSIVENESS
    // ------------------------------------------------------------------
    describe('Mobile Responsiveness', () => {

        it('renders reels tray properly on iPhone viewport', () => {
            cy.viewport('iphone-x');
            cy.visit('/profilePage');
            cy.get('.reels-tray-wrapper', { timeout: 15000 }).should('be.visible');
            // Cards should be visible and scrollable
            cy.get('.reel-fb-create-card, .reel-fb-card').should('have.length.gte', 1);
        });

        it('renders reels theater full-width on mobile', () => {
            cy.viewport('iphone-x');
            cy.visit('/reels');
            cy.get('.reels-theater-container, .reels-viewport', { timeout: 15000 }).should('exist');
        });
    });
});
