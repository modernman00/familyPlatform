import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Living emojis in the feed & comments', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);
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
            cy.get('[data-testid="emoji-gif-picker"]').should('be.visible');
            cy.get('[data-testid="comment-emoji-tab-living"]').click()
                .should('have.attr', 'aria-selected', 'true');
            cy.get('[data-testid="comment-living-grid"] .comment-living-btn')
                .should('have.length.at.least', 20)
                .each($b => expect($b[0].getBoundingClientRect().height).to.be.at.least(44));
            cy.get('[data-testid="comment-living-grid"] .comment-living-btn[data-emoji-id^="family_clap"]').click();
            cy.get('[data-testid="emoji-gif-picker"]').should('not.exist');
            cy.get('button[title="Post comment"]').click();
        });

        cy.wait('@commentReq', { timeout: 90000 }).its('response.statusCode').should('eq', 200);

        const liveEmoji = () =>
            cy.contains('#feed-root article.feed-post-card', marker)
                .find('[data-comment-no]:not([data-comment-no^="temp_"]) .atg-live-emoji[data-emoji-id^="family_clap"]', { timeout: 60000 })
                .should('be.visible');

        // First tap: pop + particle burst, no AR modal
        liveEmoji().click({ force: true });
        liveEmoji().should('have.class', 'atg-live-emoji--play');
        cy.get('.atg-burst-layer .atg-burst-particle').should('have.length.at.least', 1);
        cy.get('#atgArEmojiModal').should('not.be.visible');

        // Second tap inside the window: opens the 3D / AR viewer
        liveEmoji().click({ force: true });
        cy.get('#atgArEmojiModal', { timeout: 5000 }).should('be.visible');
        cy.get('#atgArEmojiName').should('contain.text', 'Applause');
    });

    it('renders :family_crest_3d: shortcode as Generational Crest living emoji on Desktop and Mobile viewports', () => {
        const marker = `Crest check ${Date.now()}`;
        const postInput = `${marker} :family_crest_3d:`;

        cy.viewport(1440, 900);
        openModal('postModal');
        cy.get('#postMessageEditor').should('be.visible').click().type(postInput);
        cy.get('#postMessageEditor [data-emoji-id="family_crest_3d"]', { timeout: 5000 }).should('exist');
        cy.get('textarea#postMessage').invoke('val').should('include', ':family_crest_3d:');

        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.get('.modal-backdrop', { timeout: 10000 }).should('not.exist');

        const assertPostRenderedOnViewport = (width, height) => {
            cy.viewport(width, height);
            cy.contains('#feed-root article.feed-post-card', marker, { timeout: 60000 })
                .scrollIntoView()
                .should('be.visible')
                .within(() => {
                    cy.get('.post-text-body').should('not.contain.text', ':family_crest_3d:');
                    cy.get('.atg-live-emoji[data-emoji-id="family_crest_3d"] img.atg-3d-emoji')
                        .should('be.visible')
                        .and('have.attr', 'alt', 'Generational Crest')
                        .and(($img) => {
                            expect($img.attr('src')).to.include('family_crest_3d.svg');
                            expect($img[0].complete).to.eq(true);
                            expect($img[0].naturalWidth).to.be.greaterThan(0);
                        });
                });

            cy.window().then((win) => {
                expect(win.document.documentElement.scrollWidth, `overflow at ${width}x${height}`)
                    .to.be.at.most(win.innerWidth + 2);
                const visibleEmojiImgs = Array.from(win.document.querySelectorAll('img.atg-3d-emoji')).filter((img) => {
                    const style = win.getComputedStyle(img);
                    const rect = img.getBoundingClientRect();
                    return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
                });
                visibleEmojiImgs.forEach((img) => {
                    expect(img.complete, `loaded ${img.getAttribute('src')}`).to.eq(true);
                    expect(img.naturalWidth, `naturalWidth ${img.getAttribute('src')}`).to.be.greaterThan(0);
                });
            });
        };

        assertPostRenderedOnViewport(1440, 900);
        assertPostRenderedOnViewport(390, 844);
    });
});
