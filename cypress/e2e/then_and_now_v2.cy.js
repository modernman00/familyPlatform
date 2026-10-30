import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

// 1x1 PNG, and a JPEG header whose EXIF says it was taken in 1998.
const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
const EXIF_JPEG = '/9j/4QBIRXhpZgAASUkqAAgAAAABAGmHBAABAAAAGgAAAAAAAAABAAOQAgAUAAAALAAAAAAAAAAxOTk4OjA3OjA0IDEyOjAwOjAwAP/aAAI=';
const file = (b64, fileName, mimeType) => ({ contents: Cypress.Buffer.from(b64, 'base64'), fileName, mimeType });
const png = (name) => file(PNG, name, 'image/png');

describe('Then & Now v2 (RFC-0003)', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    const publishPair = () => {
        openModal('postModal');
        cy.get('#addThenNowBtn').click();
        cy.get('#thenImgInput').selectFile(png('then.png'), { force: true });
        cy.get('#nowImgInput').selectFile(png('now.png'), { force: true });
        cy.get('#thenPreview').should('not.have.class', 'd-none');
        cy.get('#nowPreview').should('not.have.class', 'd-none');
        cy.get('#submitPost').click();
        cy.get('.swal2-popup', { timeout: 10000 }).should('contain.text', 'Post published successfully');
        cy.get('.swal2-container').click('topLeft', { force: true });
    };

    it('puts Then & Now one tap away on the feed composer and labels it in the post window', () => {
        // The composer quick-actions are icon-only buttons; the Then & Now one is identified by its accessible name.
        cy.get('.composer-quick-actions button[aria-label*="Then & Now"]').should('be.visible').click();
        cy.get('#postModal').should('be.visible');
        cy.get('#thenNowContainer').should('not.have.class', 'd-none');
        cy.get('#thenNowFlag').should('have.value', '1');
        cy.get('#addThenNowBtn').scrollIntoView().should('be.visible').and('have.attr', 'title').and('contain', 'Then & Now'); // icon-only tool, labelled by its title
        cy.screenshot('then-now-entry-points', { capture: 'viewport' });
    });

    it('suggests the year from a photo\'s EXIF date, and never overwrites a typed label', () => {
        openModal('postModal');
        cy.get('#addThenNowBtn').click();
        cy.get('#thenImgInput').selectFile(file(EXIF_JPEG, 'old.jpg', 'image/jpeg'), { force: true });
        cy.get('input[name="then_label"]').should('have.value', '1998');

        cy.get('input[name="now_label"]').type('Today');
        cy.get('#nowImgInput').selectFile(file(EXIF_JPEG, 'new.jpg', 'image/jpeg'), { force: true });
        cy.get('input[name="now_label"]').should('have.value', 'Today');
    });

    it('opens the ghost camera over the Then photo and captures the Now photo', () => {
        cy.window().then((win) => {
            cy.stub(win.navigator.mediaDevices, 'getUserMedia').callsFake(() => {
                const canvas = win.document.createElement('canvas');
                canvas.width = 320;
                canvas.height = 240;
                canvas.getContext('2d').fillRect(0, 0, 320, 240);
                return Promise.resolve(canvas.captureStream(10));
            });
        });
        openModal('postModal');
        cy.get('#addThenNowBtn').click();

        // Camera needs the Then photo first
        cy.get('#nowCameraBtn').click();
        cy.get('.swal2-popup').should('contain.text', 'Pick the Then photo first');
        cy.get('.swal2-confirm').click();

        cy.get('#thenImgInput').selectFile(png('then.png'), { force: true });
        cy.get('#nowCameraBtn').click();
        cy.get('.ghost-cam[role="dialog"]').should('be.visible');
        cy.get('.ghost-cam__ghost').should('have.attr', 'src').and('match', /^blob:/);
        cy.get('.ghost-cam__bar input[type="range"]').should('have.attr', 'aria-label');

        // Escape closes without a capture
        cy.get('body').type('{esc}');
        cy.get('.ghost-cam').should('not.exist');
        cy.get('#nowPreview').should('have.class', 'd-none');

        cy.get('#nowCameraBtn').click();
        cy.get('.ghost-cam__video').should($v => expect($v[0].videoWidth).to.be.greaterThan(0));
        cy.get('.ghost-cam__shutter').click();
        cy.get('.ghost-cam').should('not.exist');
        cy.get('#nowPreview').should('not.have.class', 'd-none');
    });

    it('explains itself instead of failing when the camera is blocked', () => {
        cy.window().then((win) => {
            cy.stub(win.navigator.mediaDevices, 'getUserMedia').rejects(new DOMException('no', 'NotAllowedError'));
        });
        openModal('postModal');
        cy.get('#addThenNowBtn').click();
        cy.get('#thenImgInput').selectFile(png('then.png'), { force: true });
        cy.get('#nowCameraBtn').click();
        cy.get('.ghost-cam__notice').should('contain.text', 'blocked');
        cy.get('.ghost-cam__shutter').should('be.disabled');
        cy.get('.ghost-cam__btn[aria-label="Close camera"]').click();
        cy.get('.ghost-cam').should('not.exist');
    });

    it('lists a birthday that is today as a milestone without any share button (upcoming milestones are non-shareable)', () => {
        cy.intercept('GET', '/api/memories/today', {
            statusCode: 200,
            body: { status: 'success', data: { memories: [], milestones: [{
                type: 'birthday', title: "Ada Lovelace's Birthday", subtitle: 'Turning 40',
                target_date: 'today', days_remaining: 0, date_badge: 'Today! 🎉', user_id: 'X', profile_img: '',
                icon: 'bi-gift-fill', color_class: 'bg-warning text-dark',
            }] } },
        }).as('milestones');
        cy.viewport(1440, 900); // the milestones widget lives in the desktop right column
        cy.reload();
        cy.wait('@milestones');
        // Milestone cards no longer carry a Then & Now share button (CEO/TAT mandate: zero share buttons
        // on upcoming milestones); Then & Now stays one tap away on the composer instead.
        cy.get('#memoriesMilestonesWidget, [data-id="memoriesMilestonesWidget"]').first()
            .should('contain.text', "Ada Lovelace's Birthday");
        cy.get('button[aria-label^="Share a Then and Now for"]').should('not.exist');
        cy.get('.composer-quick-actions button[aria-label*="Then & Now"]').should('exist');
    });

    it('shows the Then & Now timeline on the member\'s own profile, with no share-image button (private family content)', () => {
        publishPair();
        cy.reload();
        cy.get('.then-now-timeline', { timeout: 10000 }).should('not.have.class', 'd-none')
            .find('figure').should('have.length.at.least', 2);

        cy.get('.then-now', { timeout: 10000 }).first().should('not.contain.text', 'Share image');
    });
});
