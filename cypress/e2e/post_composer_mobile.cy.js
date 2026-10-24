import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

// 1x1 PNG, enough for the upload pipeline.
const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
const png = (name) => ({ contents: Cypress.Buffer.from(PNG, 'base64'), fileName: name, mimeType: 'image/png' });

describe('Create Post composer: mobile bottom sheet', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.viewport(390, 844); // iPhone 14
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    const assertPostButtonOnScreen = () => {
        cy.get('#submitPost').should('be.visible').then(($btn) => {
            const r = $btn[0].getBoundingClientRect();
            expect(r.bottom, 'Post button bottom edge').to.be.at.most($btn[0].ownerDocument.defaultView.innerHeight);
            expect(r.top, 'Post button top edge').to.be.at.least(0);
            expect(r.height, 'Post button touch target').to.be.at.least(44);
        });
    };

    it('keeps the Post button fully on screen with no photos and with five photos', () => {
        openModal('postModal');
        assertPostButtonOnScreen();

        cy.get('#imageUpload').selectFile(['a', 'b', 'c', 'd', 'e'].map((n) => png(`${n}.png`)), { force: true });
        cy.get('#imagePreviewList .composer-thumb', { timeout: 10000 }).should('have.length.at.least', 1);
        cy.get('#imagePreviewContainer').should('not.have.class', 'd-none');
        assertPostButtonOnScreen();
        cy.get('#postModal').screenshot('post_composer_mobile_with_photos');
    });

    it('does not let the PWA tab bar cover the sheet', () => {
        openModal('postModal');
        cy.get('.pwa-bottom-nav').should('not.be.visible');
        cy.get('#postModal .composer-sheet__footer').should('be.visible');
    });

    // Global rule: body.modal-open hides the tab bar for every modal, not just Create Post.
    ['editProfileModal', 'inviteFamilyModal', 'createAnnouncementModal'].forEach((modalId) => {
        it(`hides the tab bar while #${modalId} is open and restores it after`, () => {
            openModal(modalId);
            cy.get('.pwa-bottom-nav').should('not.be.visible');
            // hide() is a no-op while Bootstrap is mid-transition, so keep nudging it until it sticks
            cy.get(`#${modalId}`, { timeout: 15000 }).should(($m) => {
                const win = $m[0].ownerDocument.defaultView;
                if ($m.hasClass('show')) win.bootstrap.Modal.getInstance($m[0])?.hide();
                expect($m.is(':visible'), `#${modalId} hidden`).to.eq(false);
            });
            cy.get('.pwa-bottom-nav').should('be.visible');
        });
    });

    it('lets the user add more photos and remove one', () => {
        openModal('postModal');
        cy.get('#imageUpload').selectFile(png('one.png'), { force: true });
        cy.get('#imagePreviewList .composer-thumb:not(.composer-thumb--add)', { timeout: 10000 }).should('have.length', 1);
        cy.get('#imagePreviewList .composer-thumb--add').should('exist');
        cy.get('#imagePreviewList .composer-thumb__remove').first().click({ force: true });
        cy.get('#imagePreviewContainer').should('have.class', 'd-none');
    });

    it('every composer tool is a >=44px touch target', () => {
        openModal('postModal');
        cy.get('#postModal .composer-tool').should('have.length', 8).each(($el) => {
            const r = $el[0].getBoundingClientRect();
            expect(r.height).to.be.at.least(44);
            expect(r.width).to.be.at.least(44);
        });
    });
});
