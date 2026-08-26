import { loginFully } from '../support/login';

describe('Events and Profiles', () => {

    beforeEach(() => {
        loginFully();
    });

    it('can create a new event', () => {
        cy.get('[data-bs-target="#createEventModal"]').first().click({ force: true });

        cy.get('#createEventModal').should('be.visible');
        cy.wait(500); // wait for modal animation

        // Fill event details - the live form is eventName/eventDate/eventType/eventDescription/eventFrequency
        // (there is no eventTitle/eventTime/eventLocation field in the current markup)
        cy.get('#eventName').type('Cypress Automated Event');
        cy.get('#eventDate').type('2026-12-31');
        cy.get('#eventType').select('Party');
        cy.get('#eventDescription').type('Ringing in the new year!');
        cy.get('#eventFrequency').select('One-off');

        cy.get('#submitEventModal').click();

        // The create-event handler doesn't show a success SweetAlert - it just closes the modal
        // (the new event is broadcast to other clients via Pusher, not inserted locally).
        cy.get('#createEventModal').should('not.be.visible');
    });

    it('prevents event creation with missing fields', () => {
        cy.get('[data-bs-target="#createEventModal"]').first().click({ force: true });
        cy.get('#createEventModal').should('be.visible');
        cy.wait(500); // wait for modal animation

        // Only fill the name, omit the rest - client-side validation requires every field
        cy.get('#eventName').type('Incomplete Event');
        cy.get('#submitEventModal').click();

        // Client-side validation failure shows a SweetAlert2 popup (unlike API failures,
        // which render into the modal's own #error notification div instead).
        cy.get('.swal2-popup').should('be.visible');
        cy.get('.swal2-html-container').should('contain', 'cannot be submitted');
    });

    it('can edit profile information', () => {
        // Two triggers with the same data-bs-target exist in the DOM (desktop/mobile layout)
        cy.get('[data-bs-target="#editProfileModal"]').first().click({ force: true });

        cy.get('#editProfileModal').should('be.visible');
        cy.wait(500); // wait for modal animation to complete
        cy.get('#firstName').clear().type('CypressEdited');

        cy.get('#editProfileBtnModal').click();

        // Success is shown via the form's own notification div, not SweetAlert2
        cy.get('#editProfileFormModal_notification', { timeout: 10000 })
            .should('contain.text', 'successfully submitted');
    });

});
