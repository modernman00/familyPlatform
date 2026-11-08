import { loginFully } from '../support/login';

// RFC-0011 photo Me-stickers, end to end: what the member sees in Emoji Studio must be
// byte-for-byte what lands in the composer and in the feed — for the photo look AND the cartoon.
describe('Me-sticker look stays in sync: studio → composer → feed', () => {
    Cypress.on('uncaught:exception', () => false);
    const EMAIL = 'cypress_test@myfamilyplatform.com';
    let fixture = null;

    const loaded = ($img) => {
        expect($img[0].complete, 'image complete').to.eq(true);
        expect($img[0].naturalWidth, 'image rendered').to.be.greaterThan(0);
    };
    const absolute = (src) => src.replace(/^https?:\/\/[^/]+/, '');

    before(() => {
        cy.task('setStickerPhoto', { email: EMAIL, source: 'resources/images/profile/IMG_0238.jpg' })
            .then((r) => { expect(r, 'test account exists').to.not.eq(null); fixture = r; });
    });
    after(() => {
        if (fixture) cy.task('restoreProfilePic', { id: fixture.id, img: fixture.previous });
    });
    beforeEach(() => {
        loginFully();
        cy.intercept('POST', '/api/emoji/me-stickers/look').as('look');
    });

    const postAndFindSticker = (marker, reaction) => {
        cy.location('pathname', { timeout: 15000 }).should('eq', '/member/profilePage');
        cy.get('#postMessageEditor', { timeout: 15000 }).should('be.visible')
            .find('.atg-me-sticker img', { timeout: 30000 }).should('be.visible').should(loaded)
            .invoke('attr', 'src').as('composerSrc');
        cy.get('#postMessageEditor').type(` ${marker}`);
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 60000 }).should('contain.text', 'Post published successfully');
        cy.get('#postModal', { timeout: 10000 }).should('not.be.visible');
        cy.contains(marker, { timeout: 15000 }).parents('.card').first()
            .find('[data-testid="me-sticker"] img', { timeout: 30000 }).should('have.length', 1).should(loaded)
            .should('have.attr', 'src').and('match', new RegExp(`/${reaction}\\?v=live2$`));
        return cy.contains(marker).parents('.card').first().find('[data-testid="me-sticker"] img').invoke('attr', 'src');
    };

    it('photo look: the real portrait (face-zoomed) is identical in studio, composer and feed', () => {
        cy.intercept('POST', '/api/emoji/me-stickers/face').as('face');
        cy.visit('/member/emoji-studio', { onBeforeLoad: (win) => { try { win.localStorage.removeItem('atgStickerLook'); } catch (e) { /* */ } } });

        // Photo is the default look; cartoon-only controls are hidden so nothing acts on an invisible cartoon.
        cy.get('.atg-look-btn[data-look="photo"]', { timeout: 15000 }).should('have.attr', 'aria-checked', 'true')
            .invoke('outerHeight').should('be.gte', 44);
        cy.get('#atgPhotoStickerStage').should('be.visible');
        cy.get('#atgSaveAvatarBtn').should('not.be.visible');
        cy.get('#atgTalkBtn').should('not.be.visible');

        // The in-browser detector finds the face and the stage refreshes with the zoomed crop.
        cy.wait('@face', { timeout: 120000 }).its('response.statusCode').should('eq', 200);
        cy.get('#atgPhotoStickerStage').should('have.attr', 'src').and('contain', '&f=');

        // Reaction buttons AND tapping the stage both keep "Use in a post" on the reaction shown.
        cy.get('.atg-react-btn[data-reaction="clap"]').click();
        cy.get('#atgPhotoStickerStage').should('have.attr', 'src').and('contain', '/clap?look=photo');
        cy.get('#atgUseInPostBtn').should('have.attr', 'href', '/member/profilePage?compose_sticker=clap');
        cy.get('#atgAvatarViewport').click();
        cy.get('#atgPhotoStickerStage').should('have.attr', 'src').and('contain', '/hello?look=photo');
        cy.get('#atgUseInPostBtn').should('have.attr', 'href', '/member/profilePage?compose_sticker=hello');
        cy.get('.atg-react-btn[data-reaction="clap"]').click();
        cy.get('#atgPhotoStickerStage').should(loaded).invoke('attr', 'src').then((stageSrc) => {
            cy.request(absolute(stageSrc)).its('body').as('stageBody');
        });
        cy.get('@stageBody').should('contain', 'atg-me-photo-sticker').and('contain', 'mp-cl')
            .and('contain', 'data:image/jpeg;base64,');

        cy.get('#atgUseInPostBtn').click();
        cy.wait('@look').then(({ request, response }) => {
            expect(request.body.source).to.eq('photo');
            expect(response.statusCode).to.eq(200);
        });

        postAndFindSticker(`Cypress PhotoSticker ${Date.now()}`, 'clap').then((feedSrc) => {
            cy.get('@stageBody').then((stageBody) => {
                cy.get('@composerSrc').then((composerSrc) => {
                    cy.request(absolute(composerSrc)).its('body').should('eq', stageBody);
                });
                cy.request(absolute(feedSrc)).then((res) => {
                    expect(res.body, 'feed shows exactly the studio sticker').to.eq(stageBody);
                    expect(res.headers['cache-control']).to.contain('no-cache');
                });
            });
        });
    });

    it('cartoon look: editing the cartoon shows it, and the post carries that same cartoon', () => {
        cy.visit('/member/emoji-studio');
        cy.get('.atg-look-btn[data-look="photo"]', { timeout: 15000 }).should('have.attr', 'aria-checked', 'true');
        // Editing a cartoon trait switches the stage to the cartoon (changes are never invisible).
        cy.get('.atg-trait-pill:visible').not('.active').first().click();
        cy.get('.atg-look-btn[data-look="avatar"]').should('have.attr', 'aria-checked', 'true');
        cy.get('#atgAvatarViewport .atg-avatar-svg').should('be.visible');
        cy.get('#atgPhotoStickerStage').should('not.be.visible');
        cy.get('#atgSaveAvatarBtn').should('have.css', 'display').and('not.eq', 'none');

        cy.get('.atg-react-btn[data-reaction="wow"]').click();
        cy.get('#atgUseInPostBtn').click();
        cy.wait('@look').then(({ request, response }) => {
            expect(request.body.source).to.eq('avatar');
            expect(response.statusCode).to.eq(200);
        });
        postAndFindSticker(`Cypress CartoonSticker ${Date.now()}`, 'wow').then((feedSrc) => {
            cy.get('@composerSrc').then((composerSrc) => {
                cy.request(absolute(composerSrc)).its('body').then((composerBody) => {
                    expect(composerBody).to.contain('atg-st-badge').and.not.contain('atg-me-photo-sticker');
                    cy.request(absolute(feedSrc)).its('body').should('eq', composerBody);
                });
            });
        });
    });

    it('Living Photo tab: no fake tuning sliders; both buttons work and the result is visible', () => {
        cy.visit('/member/emoji-studio');
        cy.get('[data-atg-tab="photo-avatar-tab"]', { timeout: 15000 }).click();
        cy.get('#atgParamDenoising').should('not.exist');
        cy.get('#atgIdentityScoreBadge').should('not.exist');

        cy.get('.atg-look-btn[data-look="photo"]').click();
        cy.intercept('POST', '/api/emoji/photo-to-avatar').as('sync');
        cy.get('#atgSyncPhotoToAvatarBtn').click();
        cy.wait('@sync').its('response.body.success').should('eq', true);
        cy.get('.atg-look-btn[data-look="avatar"]').should('have.attr', 'aria-checked', 'true');
        cy.get('#atgAvatarViewport .atg-avatar-svg').should('be.visible');
        cy.get('#atgPhotoMatchSummary').should('be.visible').and('contain', 'Matched complexion');

        cy.intercept('POST', '/api/emoji/save-avatar').as('saveLiving');
        cy.get('#atgSaveLivingPortraitBtn').click();
        cy.wait('@saveLiving').then(({ request, response }) => {
            expect(request.body.mode).to.eq('living_photo');
            expect(response.body.success).to.eq(true);
            cy.request(response.body.url).its('body').should('contain', '<image');
        });
        cy.get('.swal2-popup', { timeout: 15000 }).should('contain.text', 'Living Photo Active');
    });
});
