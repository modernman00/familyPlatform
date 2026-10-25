import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Events and Profiles', () => {

    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('can create a new event', () => {
        openModal('createEventModal');

        // Fill event details - the live form is eventName/eventDate/eventType/eventDescription/eventFrequency
        // (there is no eventTitle/eventTime/eventLocation field in the current markup)
        cy.get('#eventName').type('Cypress Automated Event');
        cy.get('#eventDate').type('2026-12-31');
        cy.get('#eventType').select('Party');
        cy.get('#eventDescription').type('Ringing in the new year!');
        cy.get('#eventFrequency').select('One-off');

        cy.get('#submitEventModal').click();

        // The create-event handler doesn't show a success SweetAlert - it just closes the modal
        // (the new event is broadcast to other clients via Pusher, not inserted locally). Give
        // the round-trip room on a cold server before asserting the close.
        cy.get('#createEventModal', { timeout: 15000 }).should('not.be.visible');
    });

    // RFC-0007: the server notifies the family only after the event is saved, so the
    // client must send exactly one request and never call the retired notification endpoints.
    it('creates an event with a single request and no separate notification call', () => {
        let legacyNotificationCalls = 0;
        cy.intercept('POST', '/member/profilePage/event').as('createEvent');
        cy.intercept({ method: /GET|POST/, url: '/member/notification/event*' }, (req) => {
            legacyNotificationCalls++;
            req.continue();
        });

        openModal('createEventModal');
        cy.get('#eventName').type('Cypress Single Request Event');
        cy.get('#eventDate').type('2026-12-30');
        cy.get('#eventType').select('Party');
        cy.get('#eventDescription').type('One request only');
        cy.get('#eventFrequency').select('One-off');
        cy.get('#submitEventModal').click();

        cy.wait('@createEvent').its('response.statusCode').should('eq', 200);
        cy.get('@createEvent.all').should('have.length', 1);
        cy.get('#createEventModal', { timeout: 15000 }).should('not.be.visible');
        cy.then(() => expect(legacyNotificationCalls, 'calls to /member/notification/event').to.eq(0));
    });

    it('prevents event creation with missing fields', () => {
        openModal('createEventModal');

        // Only fill the name, omit the rest - client-side validation requires every field
        cy.get('#eventName').type('Incomplete Event');
        cy.get('#submitEventModal').click();

        // Client-side validation failure shows a SweetAlert2 popup (unlike API failures,
        // which render into the modal's own #error notification div instead).
        cy.get('.swal2-popup').should('be.visible');
        cy.get('.swal2-html-container').should('contain', 'cannot be submitted');
    });

    it('can edit profile information', () => {
        cy.intercept('POST', '/member/profilePage/editProfile').as('editProfileReq');
        openModal('editProfileModal');
        cy.get('#firstName').clear().type('CypressEdited');

        cy.get('#editProfileBtnModal').click();
        cy.wait('@editProfileReq').then((interception) => {
            expect(interception.response.statusCode).to.eq(200);
            expect(interception.response.body.message).to.contain('successfully saved');
        });
    });

    const csrf = () => cy.getCookie('XSRF-TOKEN').then((c) => (c ? c.value : ''));

    it('member can export their own data (GDPR Art. 15)', () => {
        csrf().then((token) => {
            cy.request({
                method: 'POST',
                url: '/account/data-export',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': token },
            }).then((res) => {
                expect(res.status).to.eq(200);
                expect(res.headers['content-disposition']).to.contain('attachment');
                expect(res.body).to.have.property('export_format');
                expect(res.body.data).to.have.property('account');
                expect(res.body.data.account[0].password).to.eq('[redacted]');
            });
        });
    });

    it('data-export rejects a request with no CSRF token (SEC)', () => {
        cy.request({
            method: 'POST',
            url: '/account/data-export',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            failOnStatusCode: false,
        }).then((res) => {
            expect(res.status).to.be.oneOf([401, 403]);
        });
    });

    it('member can request account deletion (GDPR Art. 17)', () => {
        csrf().then((token) => {
            cy.request({
                method: 'POST',
                url: '/account/request-deletion',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': token },
            }).then((res) => {
                expect(res.status).to.eq(200);
                const body = typeof res.body === 'string' ? JSON.parse(res.body) : res.body;
                expect(body.message).to.contain('received your deletion request');
            });
        });
    });

});
