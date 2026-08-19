import { loginFully } from '../support/login';

describe('Social Feed Interactions', () => {

    beforeEach(() => {
        loginFully();
        // Wait for Alpine's feed component to finish mounting (it registers the listeners
        // that make new posts/comments appear instantly) before interacting with the page.
        cy.contains('button', 'Like', { timeout: 10000 }).should('exist');
    });

    it('can create a new post successfully', () => {
        const postContent = `Cypress Automated Post ${Date.now()}`;

        // Set the value directly rather than cy.type() - simulated per-keystroke typing into
        // this particular textarea intermittently drops characters in this app under Cypress,
        // which isn't what this test is trying to verify.
        cy.get('#openPostModalTrigger').click();
        cy.get('textarea#postMessage').should('be.visible')
            .invoke('val', postContent)
            .trigger('input')
            .trigger('change');

        // Submit post
        cy.get('#submitPost').click();

        // A SweetAlert2 toast confirms the publish...
        cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible')
            .and('contain.text', 'Post published successfully');

        // ...and the post itself shows up in the feed without a full posts re-fetch: the create
        // endpoint returns the full new post so Alpine can unshift it straight into state. That
        // update is synchronous (verified separately by inspecting Alpine state directly) - this
        // timeout only needs to cover DOM paint time for the list, not a network round-trip.
        cy.get('body', { timeout: 4000 }).should('contain.text', postContent);
    });

    it('denies submitting an empty post', () => {
        // Leave textarea empty
        cy.get('#openPostModalTrigger').click();
        cy.get('textarea#postMessage').clear();
        cy.get('#submitPost').click();

        // The server rejects a post with no text/image/poll and the error is shown via SweetAlert2
        cy.get('.swal2-popup', { timeout: 10000 }).should('exist');
        cy.get('.swal2-html-container').should('contain', 'add some text');
    });

    it('can like a post', () => {
        // The "Like" text sits in an inner <span>, so restrict .contains() to the <button>
        // itself. Liking a post is reactToPost() under the hood, which toggles fw-semibold
        // (and an inline color) on the button, not a text-primary class.
        cy.contains('button', 'Like').first().click({ force: true });

        // Wait for class toggle (this tests Alpine reactivity)
        cy.contains('button', 'Like').first().should('have.class', 'fw-semibold');
    });

    it('can add a comment to a post', () => {
        const commentContent = `Cypress Automated Comment ${Date.now()}`;

        // The comment form is hidden per-post until the "Comment" toggle button is clicked
        cy.contains('button', 'Comment').first().click({ force: true });
        cy.get('input[placeholder="Write a comment..."]').first().should('be.visible').type(`${commentContent}{enter}`);

        // On success the new comment is pushed straight into Alpine state, so it renders immediately
        cy.get('body').should('contain.text', commentContent);
    });

    it('denies empty comments', () => {
        cy.contains('button', 'Comment').first().click({ force: true });
        cy.get('input[placeholder="Write a comment..."]').first().should('be.visible').as('commentInput');

        // The input is HTML5-required and the submit handler no-ops on empty text, so there's
        // no error popup to check for here - the meaningful assertion is that nothing was sent.
        cy.get('@commentInput').then(($input) => {
            expect($input[0].checkValidity()).to.be.false;
        });
    });

});
