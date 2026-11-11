import { loginFully } from '../support/login';

describe('Family Hub tool dock', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.clearLocalStorage();
        loginFully();
    });

    it('defaults to Fridge Chef, switches tools, updates the hash and remembers the choice', () => {
        cy.visit('/family/hub');
        cy.get('#familyAiTabs .fhub-tile').should('have.length', 7);
        cy.get('#tab-fridge-btn').should('have.class', 'active');
        cy.get('#tab-fridge').should('be.visible');

        cy.get('#tab-journal-btn').click();
        cy.get('#tab-journal').should('be.visible');
        cy.location('search').should('contain', 'tool=journal');

        cy.visit('/family/hub');
        cy.get('#tab-journal-btn').should('have.class', 'active');
    });

    it('opens the tool named in the link', () => {
        cy.visit('/family/hub?tool=outings');
        cy.get('#tab-outings-btn').should('have.class', 'active');
    });

    it('keeps 44px touch targets on the mobile rail', () => {
        cy.viewport(390, 844);
        cy.visit('/family/hub');
        cy.get('#familyAiTabs .fhub-tile').each(($t) => {
            expect($t[0].getBoundingClientRect().height).to.be.gte(44);
        });
        cy.get('#tab-trivia-btn').click();
        cy.get('#tab-trivia').should('be.visible');
    });

    it('mounts the Universal Poster Engine in Flyer Studio on first open', () => {
        cy.visit('/family/hub');
        cy.get('#flyerEngineFrame').should('not.have.attr', 'src');
        cy.get('#tab-flyer-btn').click();
        cy.get('#flyerEngineFrame').should('have.attr', 'src', '/poster-studio?app=familyplatform');
        cy.get('#flyerEngineCard .fhub-engine-stage', { timeout: 20000 }).should('have.class', 'is-ready');
        cy.get('#flyerEngineFrame').its('0.contentDocument.body').should('not.be.empty')
            .find('#psCanvas').should('exist');
        cy.get('#btnFlyerEngineGuide').should('not.be.disabled');
    });
});
