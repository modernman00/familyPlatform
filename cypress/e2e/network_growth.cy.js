import { loginFully } from '../support/login';

describe('Network Growth (Friend Requests)', () => {

    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('has the incoming-kin-requests section wired up', () => {
        // The "Kin Requests" card now only renders when there ARE pending
        // requests (x-show on friendRequests.length). The test account has none,
        // so assert the section is present in the DOM and bound to the
        // friendRequests state rather than expecting the visible heading.
        cy.get('[x-show*="friendRequests"]', { timeout: 10000 }).should('exist');
        cy.window().should('have.property', 'profileSidebar');
    });

    it('can see accept a friend request buttons if there are requests', () => {
        // Assuming there's a button to accept a request if they exist
        cy.get('body').then(($body) => {
            if ($body.find('a.btn-success').length > 0) {
                cy.get('a.btn-success').contains('Accept').first().should('be.visible');
            }
        });
    });

    it('can view all members to send friend request', () => {
        cy.visit('/allMembers');
        // "All Members" only appears in the <title> tag (not visible body text) -
        // the page's actual on-screen heading is "Family Directory".
        cy.get('body').should('contain.text', 'Family Directory');
    });

});
