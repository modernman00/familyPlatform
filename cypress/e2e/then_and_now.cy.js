import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

// 1x1 PNG, enough for the upload pipeline.
const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
const png = (name) => ({ contents: Cypress.Buffer.from(PNG, 'base64'), fileName: name, mimeType: 'image/png' });

describe('Then & Now photo comparison (RFC-0002)', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    it('refuses to post with only one photo', () => {
        openModal('postModal');
        cy.get('#addThenNowBtn').click();
        cy.get('#thenImgInput').selectFile(png('then.png'), { force: true });
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 10000 }).should('contain.text', 'Then & Now Incomplete');
    });

    it('publishes a comparison and renders an accessible slider', () => {
        const label = `Cypress ${Date.now()}`;
        openModal('postModal');
        cy.get('textarea#postMessage').invoke('val', label).trigger('input').trigger('change');
        cy.get('#addThenNowBtn').click();
        cy.get('input[name="then_label"]').type('1998');
        cy.get('input[name="now_label"]').type('2026');
        cy.get('#thenImgInput').selectFile(png('then.png'), { force: true });
        cy.get('#nowImgInput').selectFile(png('now.png'), { force: true });
        cy.get('#thenPreview').should('not.have.class', 'd-none');
        cy.get('#nowPreview').should('not.have.class', 'd-none');
        cy.get('#submitPost').click();

        cy.get('.swal2-popup', { timeout: 10000 }).should('contain.text', 'Post published successfully');
        cy.get('.then-now', { timeout: 8000 }).first().within(() => {
            cy.get('.then-now__tag--then').should('contain.text', '1998');
            cy.get('.then-now__tag--now').should('contain.text', '2026');
            cy.get('input[type="range"]').should('have.attr', 'aria-label');
            cy.contains('button', 'Side by side').click();
            cy.get('.then-now__side').should('be.visible');
        });
    });

    it('lets another member answer with their own Then & Now', () => {
        cy.get('.then-now', { timeout: 10000 }).first().contains('button', 'Add your Then & Now').click();
        cy.get('#thenNowContainer').should('not.have.class', 'd-none');
        cy.get('#thenNowReplyTo').should('not.have.value', '');
        cy.get('#thenNowTitle').should('contain.text', 'Add your Then & Now');
    });
});
