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
        openModal('editProfileModal');
        cy.get('#firstName').clear().type('CypressEdited');

        cy.get('#editProfileBtnModal').click();

        // Success is shown via the form's own notification div, not SweetAlert2
        cy.get('#editProfileFormModal_notification', { timeout: 10000 })
            .should('contain.text', 'successfully submitted');
    });

});
