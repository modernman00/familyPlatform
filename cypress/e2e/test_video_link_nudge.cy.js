import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Link-first video nudge in the post composer', () => {
    Cypress.on('uncaught:exception', () => false);

    // Construct a synthetic >150MB File in-browser so we exercise the exact production
    // 150MB link-nudge threshold without serializing 150MB through CDP.
    const selectBigVideo = () => {
        cy.get('#imageUpload').then(($input) => {
            const win = $input[0].ownerDocument.defaultView;
            const file = new win.File([new Uint8Array(1024)], 'big_family_video.webm', { type: 'video/webm' });
            Object.defineProperty(file, 'size', { value: 151 * 1024 * 1024 });
            const dt = new win.DataTransfer();
            dt.items.add(file);
            $input[0].files = dt.files;
            $input[0].dispatchEvent(new win.Event('change', { bubbles: true }));
        });
    };

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
        openModal('postModal');
    });

    it('shows the nudge for a large video and "Paste a link" attaches nothing', () => {
        selectBigVideo();

        cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible')
            .and('contain', 'Share it as a link instead');
        cy.get('.swal2-confirm').should('contain', 'Paste a link').click();

        cy.get('#imagePreviewList .composer-thumb--video').should('not.exist');
    });

    it('records the decision in telemetry', () => {
        cy.intercept('POST', '/api/telemetry/event', (req) => {
            if (req.body && req.body.event === 'video_link_nudge') req.alias = 'nudgeTelemetry';
        });
        selectBigVideo();
        cy.get('.swal2-confirm', { timeout: 10000 }).click();
        cy.wait('@nudgeTelemetry').then(({ request, response }) => {
            expect(request.body.metadata.decision).to.eq('link');
            expect(response.statusCode).to.eq(200);
        });
    });

    it('"Cancel" attaches nothing', () => {
        selectBigVideo();
        cy.get('.swal2-cancel', { timeout: 10000 }).click();
        cy.get('#imagePreviewList .composer-thumb--video').should('not.exist');
    });

    it('"Upload anyway" continues into the existing upload flow', () => {
        selectBigVideo();
        cy.get('.swal2-deny', { timeout: 10000 }).should('contain', 'Upload anyway').click();
        // The existing video studio prompt appears next (>80MB auto-trim confirmation).
        cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible')
            .and('contain', 'Auto-Trim Video');
    });

    it('does not nudge for a small video', () => {
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_video.webm', { force: true });
        cy.get('#imagePreviewList .composer-thumb--video', { timeout: 20000 }).should('exist');
        cy.contains('.swal2-popup', 'Share it as a link instead').should('not.exist');
    });
});
