import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('GIFs in comments', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    const createFreshPost = (text) => {
        openModal('postModal');
        cy.get('textarea#postMessage').should('be.visible')
            .invoke('val', text).trigger('input').trigger('change');
        cy.get('#submitPost').click();
        // Publishing fans out Pusher + push notifications, which can take well over 10s locally.
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.get('.modal-backdrop', { timeout: 10000 }).should('not.exist');
        cy.contains(text, { timeout: 10000 }).should('be.visible');
    };

    it('sits beside the emoji button inside the composer and posts a GIF-only comment that survives a reload', () => {
        const marker = `Cypress GIF Comment ${Date.now()}`;
        createFreshPost(marker);
        cy.intercept('POST', '**/postCommentProfile').as('commentReq');

        cy.contains(marker).parents('.card').within(() => {
            cy.contains('button', 'Comment').click();

            // Emoji + GIF tools live inside the composer pill, next to the input
            cy.get('[data-testid="comment-gif-btn"]').should('be.visible').then(($gif) => {
                const $emoji = $gif.prev('button[title="Add emoji"]');
                expect($emoji.length, 'emoji button precedes GIF button').to.eq(1);
            });

            cy.get('[data-testid="comment-gif-btn"]').click();
            cy.get('[data-testid="comment-gif-picker"]').should('be.visible')
                .find('.gif-item-card').first().click();
            cy.get('[data-testid="comment-gif-preview"]').should('be.visible');

            // No text typed: the GIF alone satisfies the form
            cy.get('button[aria-label="Post comment"]').click();
        });

        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);

        cy.contains(marker).parents('.card').find('[data-testid="comment-gif"]', { timeout: 60000 })
            .should('be.visible');

        cy.reload();
        cy.get('#openPostModalTrigger', { timeout: 60000 }).should('be.visible');
        cy.contains(marker, { timeout: 60000 }).parents('.card')
            .find('[data-testid="comment-gif"]').should('have.attr', 'src').and('match', /^https:\/\/media\d*\.giphy\.com\//);
    });

    it('rejects a non-Giphy gif_url server-side (no tracking-pixel vector)', () => {
        const marker = `Cypress GIF Reject ${Date.now()}`;
        createFreshPost(marker);

        // Without text, a non-Giphy URL is dropped and the empty comment is refused.
        cy.getCookie('XSRF-TOKEN').then((c) => {
            cy.window().then((win) => {
                const fd = new win.FormData();
                fd.append('post_no', '0');
                fd.append('comment', '');
                fd.append('gif_url', 'https://evil.example.com/pixel.gif');
                return win.fetch('/postCommentProfile', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-XSRF-TOKEN': decodeURIComponent(c?.value || '') },
                    credentials: 'same-origin',
                }).then((r) => {
                    expect(r.status).to.not.eq(200);
                });
            });
        });
    });
});
