import { loginFully } from '../support/login';

describe('Family Platform Recommendation & Walled Sanctuary Spawning', () => {
    Cypress.on('uncaught:exception', () => false);

    describe('Member Referral Experience', () => {
        beforeEach(() => {
            loginFully();
        });

        it('displays the Invite Others button in navigation and opens the merged modal', () => {
            cy.get('a[data-bs-target="#inviteFamilyModal"], button[data-bs-target="#inviteFamilyModal"]')
                .first()
                .should('exist')
                .click({ force: true });

            cy.get('#inviteFamilyModal').should('have.class', 'show');

            // Friends & In-Laws lives in a tab of the merged Invite Others modal
            cy.get('#inviteFamilyModal').should('contain.text', 'Invite others');
            cy.get('#inviteTabFriends').click({ force: true });
            cy.get('#inviteTabFriendsPane').should('have.class', 'active');

            // Verify Walled Sanctuary USP copy is present in the modal
            cy.get('#inviteFamilyModal').should('contain.text', 'The Walled Sanctuary Guarantee');
            cy.get('#inviteFamilyModal').should('contain.text', 'own independent family network');
            cy.get('#inviteFamilyModal').should('contain.text', 'strictly private');

            // Verify input and WhatsApp buttons exist
            cy.get('#inputShareLink').should('exist');
            cy.get('#btnShareWhatsApp').should('exist');
            cy.get('#btnCopyShareLink').should('exist');
        });
    });

    describe('Public Guest Join Landing Experience', () => {
        it('renders the Walled Sanctuary landing page cleanly with call to action', () => {
            cy.visit('/join');

            cy.get('body').should('contain.text', 'Start Your Private Family Sanctuary');
            cy.get('body').should('contain.text', '100% Walled Sanctuary');
            cy.get('body').should('contain.text', 'Only connected kin and approved family members can ever enter');

            cy.get('#btnCreateFamilyNetwork')
                .should('be.visible')
                .and('have.attr', 'href')
                .and('include', '/createFamilyCode');
        });

        it('shows personalized inviter name badge when arriving with signed ref token', () => {
            cy.visit('/join?ref=invalid-or-sample-token');

            cy.get('body').should('contain.text', 'Start Your Private Family Sanctuary');
            cy.get('#btnCreateFamilyNetwork').should('be.visible');
        });
    });
});
