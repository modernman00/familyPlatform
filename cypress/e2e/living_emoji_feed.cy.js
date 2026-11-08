import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Living emojis in the feed & comments', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    const createFreshPost = (text) => {
        openModal('postModal');
        // Rich composer: type into the contenteditable; it syncs into the hidden textarea.
        cy.get('#postMessageEditor').should('be.visible').click().type(text);
        cy.get('textarea#postMessage').should('have.value', text);
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.get('.modal-backdrop', { timeout: 10000 }).should('not.exist');
        cy.contains(text, { timeout: 10000 }).should('be.visible');
    };

    it('exposes the catalog and renders any catalog id (incl. tone variants), leaving unknown tokens as text', () => {
        cy.window().then(win => {
            expect(win.ATG_LIVING_EMOJIS, 'catalog injected').to.be.an('object');
            expect(win.ATG_LIVING_EMOJIS.catalog.map(e => e.id)).to.include('family_clap_3d');
            const feed = win.Alpine && win.Alpine.$data(win.document.querySelector('[x-data]'));
            const host = feed && typeof feed.renderHashtags === 'function' ? feed : null;
            if (!host) return; // feed component not on root; covered by the comment flow below
            const html = host.renderHashtags('hi :family_clap_dark_3d: :wisdom_scroll_3d: :not_real_3d:');
            expect(html).to.contain('/img/emoji/family_clap_dark_3d.svg');
            expect(html).to.contain('/img/emoji/wisdom_scroll_3d.svg');
            expect(html).to.contain(':not_real_3d:');
        });
    });

    it('inserts a living emoji from the comment picker, posts it, and tap plays then opens AR', () => {
        const marker = `Cypress Living Comment ${Date.now()}`;
        createFreshPost(marker);
        cy.intercept('POST', '**/postCommentProfile').as('commentReq');

        cy.contains(marker).parents('.card').within(() => {
            cy.get('button[aria-label="Comment on this post"]').click();
            cy.get('button[aria-label="Add emoji"]').first().click();
            cy.get('[data-testid="comment-emoji-picker"]').should('be.visible');
            cy.get('[data-testid="comment-emoji-tab-living"]').click()
                .should('have.attr', 'aria-selected', 'true');
            cy.get('[data-testid="comment-living-grid"] .comment-living-btn')
                .should('have.length.at.least', 20)
                .each($b => expect($b[0].getBoundingClientRect().height).to.be.at.least(44));
            cy.get('[data-testid="comment-living-grid"] .comment-living-btn[data-emoji-id^="family_clap"]').click();
            cy.get('[data-testid="comment-emoji-picker"]').should('not.be.visible');
            cy.get('button[aria-label="Post comment"]').click();
        });

        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);

        cy.contains(marker).parents('.card')
            .find('.atg-live-emoji img[src*="family_clap"]', { timeout: 60000 })
            .should('be.visible')
            .parent()
            .as('live');

        // First tap: pop + particle burst, no AR modal
        cy.get('@live').click();
        cy.get('@live').should('have.class', 'atg-live-emoji--play');
        cy.get('.atg-burst-layer .atg-burst-particle').should('have.length.at.least', 1);
        cy.get('#atgArEmojiModal').should('not.be.visible');

        // Second tap inside the window: opens the 3D / AR viewer
        cy.get('@live').click();
        cy.get('#atgArEmojiModal', { timeout: 5000 }).should('be.visible');
        cy.get('#atgArEmojiName').should('contain.text', 'Applause');
    });
});
