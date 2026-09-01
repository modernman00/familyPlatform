import { loginFully } from '../support/login';

describe('Network Growth (Friend Requests)', () => {

    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('displays incoming friend requests', () => {
        // Assert there is a section for requests (e.g. sidebar or dedicated page)
        cy.get('body').should('contain.text', 'Friend Request');
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
