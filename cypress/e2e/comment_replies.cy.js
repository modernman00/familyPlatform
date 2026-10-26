import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Threaded comment replies', () => {
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
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.get('.modal-backdrop', { timeout: 10000 }).should('not.exist');
        cy.contains(text, { timeout: 10000 }).should('be.visible');
    };

    it('replies under the top-level comment, flattens reply-to-reply, and survives a reload', () => {
        const marker = `Cypress Reply Post ${Date.now()}`;
        const top = `top-${Date.now()}`;
        const reply = `reply-${Date.now()}`;
        const nested = `nested-${Date.now()}`;
        createFreshPost(marker);
        cy.intercept('POST', '**/postCommentProfile').as('commentReq');

        const post = () => cy.contains(marker).parents('.card');

        post().within(() => {
            cy.contains('button', 'Comment').click();
            cy.get('input[aria-label="Write a comment"]').type(top);
            cy.get('button[aria-label="Post comment"]').click();
        });
        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);

        // Reply to the top-level comment
        post().contains('.comment-bubble-box', top).closest('[data-reply]').within(() => {
            cy.get('[data-testid="comment-reply-btn"]').click();
        });
        post().find('[data-testid="comment-replying-to"]').should('be.visible');
        post().find('input[aria-label="Write a comment"]').type(reply);
        post().find('button[aria-label="Post comment"]').click();
        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);
        post().find('[data-testid="comment-replying-to"]').should('not.be.visible');
        post().contains('.comment-bubble-box', reply).closest('[data-reply]').should('have.attr', 'data-reply', 'true');
        post().contains('.comment-bubble-box', top).closest('[data-reply]').should('have.attr', 'data-reply', 'false');

        // Reply to the reply: still one level deep
        post().contains('.comment-bubble-box', reply).closest('[data-reply]').within(() => {
            cy.get('[data-testid="comment-reply-btn"]').click();
        });
        post().find('input[aria-label="Write a comment"]').type(nested);
        post().find('button[aria-label="Post comment"]').click();
        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);
        post().contains('.comment-bubble-box', nested).closest('[data-reply]').should('have.attr', 'data-reply', 'true');

        // Persisted: thread order and indentation survive a reload
        cy.reload();
        cy.get('#openPostModalTrigger', { timeout: 60000 }).should('be.visible');
        cy.contains(marker, { timeout: 60000 }).parents('.card').within(() => {
            cy.contains('.comment-bubble-box', top).closest('[data-reply]').should('have.attr', 'data-reply', 'false');
            cy.contains('.comment-bubble-box', reply).closest('[data-reply]').should('have.attr', 'data-reply', 'true');
            cy.contains('.comment-bubble-box', nested).closest('[data-reply]').should('have.attr', 'data-reply', 'true');
        });
    });

    it('collapses long threads (>2 replies) with progressive disclosure toggle', () => {
        const marker = `Cypress Thread Collapse ${Date.now()}`;
        const top = `top-${Date.now()}`;
        const r1 = `r1-${Date.now()}`;
        const r2 = `r2-${Date.now()}`;
        const r3 = `r3-${Date.now()}`;
        createFreshPost(marker);
        cy.intercept('POST', '**/postCommentProfile').as('commentReq');

        const post = () => cy.contains(marker).parents('.card');

        post().within(() => {
            cy.contains('button', 'Comment').click();
            cy.get('input[aria-label="Write a comment"]').type(top);
            cy.get('button[aria-label="Post comment"]').click();
        });
        cy.wait('@commentReq', { timeout: 90000 });

        // Add 3 replies under the root comment
        [r1, r2, r3].forEach((txt) => {
            post().contains('.comment-bubble-box', top).closest('[data-reply]').within(() => {
                cy.get('[data-testid="comment-reply-btn"]').click();
            });
            post().find('input[aria-label="Write a comment"]').clear().type(txt);
            post().find('button[aria-label="Post comment"]').click();
            cy.wait('@commentReq', { timeout: 90000 });
        });

        // Reload to see the thread collapsed by default (first 2 visible, 3rd hidden)
        cy.reload();
        cy.get('#openPostModalTrigger', { timeout: 60000 }).should('be.visible');
        cy.contains(marker, { timeout: 60000 }).parents('.card').within(() => {
            cy.contains(r1).should('be.visible');
            cy.contains(r2).should('be.visible');
            cy.contains(r3).should('not.exist');
            cy.get('[data-testid="thread-collapse-toggle"]').should('be.visible').and('contain.text', 'View 1 more reply');

            // Click toggle to expand all replies
            cy.get('[data-testid="thread-collapse-toggle"]').click();
            cy.contains(r3).should('be.visible');
            cy.get('[data-testid="thread-collapse-toggle"]').should('contain.text', 'Collapse replies');

            // Click toggle to collapse again
            cy.get('[data-testid="thread-collapse-toggle"]').click();
            cy.contains(r3).should('not.exist');
            cy.get('[data-testid="thread-collapse-toggle"]').should('contain.text', 'View 1 more reply');
        });
    });

    it('refuses a reply to a comment that is not on this post', () => {
        cy.getCookie('XSRF-TOKEN').then((c) => {
            cy.window().then((win) => {
                const fd = new win.FormData();
                fd.append('post_no', '0');
                fd.append('comment', 'cross-post reply');
                fd.append('parent_comment_no', '2147483000');
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
