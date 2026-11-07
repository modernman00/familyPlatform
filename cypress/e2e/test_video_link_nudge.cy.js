import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Link-first video nudge in the post composer', () => {
    Cypress.on('uncaught:exception', () => false);

    const bigVideo = () => ({
        contents: Cypress.Buffer.alloc(11 * 1024 * 1024, 1),
        fileName: 'big_family_video.webm',
        mimeType: 'video/webm',
    });

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
        openModal('postModal');
    });

    it('shows the nudge for a large video and "Paste a link" attaches nothing', () => {
        cy.get('#imageUpload').selectFile(bigVideo(), { force: true });

        cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible')
            .and('contain', 'Share it as a link instead');
        cy.get('.swal2-confirm').should('contain', 'Paste a link').click();

        cy.get('#imagePreviewList .composer-thumb--video').should('not.exist');
    });

    it('records the decision in telemetry', () => {
        cy.intercept('POST', '/api/telemetry/event', (req) => {
            if (req.body && req.body.event === 'video_link_nudge') req.alias = 'nudgeTelemetry';
        });
        cy.get('#imageUpload').selectFile(bigVideo(), { force: true });
        cy.get('.swal2-confirm', { timeout: 10000 }).click();
        cy.wait('@nudgeTelemetry').then(({ request, response }) => {
            expect(request.body.metadata.decision).to.eq('link');
            expect(response.statusCode).to.eq(200);
        });
    });

    it('"Cancel" attaches nothing', () => {
        cy.get('#imageUpload').selectFile(bigVideo(), { force: true });
        cy.get('.swal2-cancel', { timeout: 10000 }).click();
        cy.get('#imagePreviewList .composer-thumb--video').should('not.exist');
    });

    it('"Upload anyway" continues into the existing upload flow', () => {
        cy.get('#imageUpload').selectFile(bigVideo(), { force: true });
        cy.get('.swal2-deny', { timeout: 10000 }).should('contain', 'Upload anyway').click();
        // The existing video studio prompt appears next.
        cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible');
    });

    it('does not nudge for a small video', () => {
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_video.webm', { force: true });
        cy.get('.swal2-popup', { timeout: 10000 }).should('not.contain', 'Share it as a link instead');
    });
});
