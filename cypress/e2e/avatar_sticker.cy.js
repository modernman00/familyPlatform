import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

// RFC-0011 Me-stickers: the member's own avatar, reacting, inside posts and comments.
describe('Me-stickers (avatar stickers in posts & comments)', () => {
    Cypress.on('uncaught:exception', () => false);

    const saveProceduralAvatar = () => {
        cy.intercept('POST', '/api/emoji/save-avatar').as('saveAvatarReq');
        cy.visit('/member/emoji-studio');
        cy.get('#atgAvatarViewport .atg-avatar-svg').should('exist');
        cy.get('#atgSaveAvatarBtn').click();
        cy.wait('@saveAvatarReq').its('response.statusCode').should('eq', 200);
        cy.get('.swal2-confirm').click({ force: true });
    };

    const expectImageLoaded = ($img) => {
        expect($img[0].complete, 'image complete').to.eq(true);
        expect($img[0].naturalWidth, 'image rendered').to.be.greaterThan(0);
    };

    beforeEach(() => {
        loginFully();
    });

    it('serves my sticker set, and 404s for a non-family member and bad reactions', () => {
        saveProceduralAvatar();
        cy.request('/api/emoji/me-stickers').then(({ body }) => {
            expect(body.available).to.eq(true);
            expect(body.stickers).to.have.length(8);
            const clap = body.stickers.find(s => s.reaction === 'clap');
            expect(clap.token).to.eq(`:me_clap@${body.user_id}:`);

            cy.request(clap.url).then(res => {
                expect(res.status).to.eq(200);
                expect(res.headers['content-type']).to.contain('image/svg+xml');
                expect(res.headers['x-content-type-options']).to.contain('nosniff');
                expect(res.body).to.contain('atg-st-badge');
            });
            cy.request({ url: `/api/emoji/avatar-sticker/${body.user_id}/explode`, failOnStatusCode: false })
                .its('status').should('eq', 404);
            cy.request({ url: '/api/emoji/avatar-sticker/cypress_not_family_zz9/clap', failOnStatusCode: false })
                .its('status').should('eq', 404);
        });
    });

    it('Use in a post: studio reaction → composer with my sticker → posted sticker in the feed; forged tokens are stripped', () => {
        saveProceduralAvatar();
        cy.get('.atg-react-btn[data-reaction="clap"]').click();
        cy.get('#atgUseInPostBtn').should('have.attr', 'href', '/member/profilePage?compose_sticker=clap')
            .invoke('outerHeight').should('be.gte', 44);
        cy.get('#atgUseInPostBtn').click();

        cy.location('search', { timeout: 15000 }).should('not.contain', 'compose_sticker');
        cy.get('#postMessageEditor', { timeout: 15000 }).should('be.visible')
            .find('.atg-me-sticker img').should('be.visible').should(expectImageLoaded);

        const marker = `Cypress MeSticker ${Date.now()}`;
        cy.get('#postMessageEditor').type(` ${marker} :me_love@cypress_victim_zz9: `);
        cy.get('textarea#postMessage').invoke('val').should('match', /:me_clap@[A-Za-z0-9_-]+:/);
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');

        cy.contains(marker, { timeout: 15000 }).parents('.card').first().within(() => {
            cy.get('[data-testid="me-sticker"] img').should('have.length', 1)
                .and('have.attr', 'src').and('match', /\/api\/emoji\/avatar-sticker\/[^/]+\/clap$/);
            cy.get('[data-testid="me-sticker"] img').should(expectImageLoaded);
            cy.root().should('not.contain.text', 'cypress_victim_zz9');
            cy.root().screenshot('me-sticker-feed-post');
            cy.get('[data-testid="me-sticker"]').click().should('have.class', 'atg-live-emoji--play');
        });
        cy.get('.atg-burst-layer .atg-burst-particle').should('have.length.at.least', 1);
    });

    it('adds a Me-sticker to a comment from the 🙋 Me tab', () => {
        saveProceduralAvatar();
        cy.visit('/member/profilePage');
        cy.get('#openPostModalTrigger', { timeout: 15000 }).should('be.visible');

        const marker = `Cypress MeSticker Comment ${Date.now()}`;
        openModal('postModal');
        cy.get('#postMessageEditor').should('be.visible').click().type(marker);
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.get('.modal-backdrop', { timeout: 10000 }).should('not.exist');

        cy.intercept('POST', '**/postCommentProfile').as('commentReq');
        cy.contains(marker, { timeout: 15000 }).parents('.card').within(() => {
            cy.get('button[aria-label="Comment on this post"]').click();
            cy.get('button[aria-label="Add emoji"]').first().click();
            cy.get('[data-testid="comment-emoji-tab-me"]').click().should('have.attr', 'aria-selected', 'true');
            cy.get('[data-testid="comment-me-grid"] .me-sticker-btn', { timeout: 10000 }).should('have.length', 8)
                .each($b => expect($b[0].getBoundingClientRect().height).to.be.at.least(44));
            cy.get('[data-testid="comment-me-grid"] .me-sticker-btn[aria-label="Add my avatar: Dancing"]').click();
            cy.get('button[aria-label="Post comment"]').click();
        });
        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);
        cy.contains(marker).parents('.card')
            .find('[data-testid="me-sticker"] img[src$="/dance"]', { timeout: 60000 })
            .should('be.visible').should(expectImageLoaded);
    });
});
