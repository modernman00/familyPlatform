/**
 * Chaos Testing Suite for Structural Safety (Plan for the Worst)
 * ISO 27001 - Availability & Resiliency
 * Customized for familyPlatform
 */

import { loginFully } from '../support/login';

describe('Chaos & Resiliency Testing', () => {

    it('gracefully handles API network timeouts on login', () => {
        cy.visit('/login');
        
        // Intercept the login endpoint and force a timeout (simulating a dead database or slow network)
        cy.intercept('POST', '/login', { forceNetworkError: true }).as('loginTimeout');
        
        // Fill out the form. The button is type="button" and submission is handled by
        // a JS click handler (not a native form submit), and the password must satisfy
        // the client-side strength check (upper + lower + digit) or the request never fires.
        cy.get('input[name="email"]').type('test@example.com');
        cy.get('input[name="password"]').type('Secretpassword123');
        cy.get('button#button').click();
        
        cy.wait('@loginTimeout', { timeout: 10000 }).then(() => {
             // Assuming sweetalert or native fallback shows an error and not white screen
            cy.get('body').should('not.contain', 'Fatal error');
            cy.get('body').should('not.contain', 'SQLSTATE');
            // Check for SweetAlert error popup or fallback toast
            cy.get('.swal2-popup, .error-message, .alert-danger, .toast').should('exist');
        });
    });

    it('gracefully handles 500 Internal Server Error on Event Creation', () => {
        // Intercept the event creation and force a 500 error
        cy.intercept('POST', '/member/profilePage/event', {
            statusCode: 500,
            body: { message: "Internal Server Error" }
        }).as('eventError');

        loginFully();

        // Open the modal - the live trigger is a data-bs-target, not a #createEventBtn
        cy.get('[data-bs-target="#createEventModal"]').first().click({ force: true });

        // Fill the required fields (live form: eventName/eventDate/eventType/eventDescription/eventFrequency)
        cy.get('#eventName').type('Test Event', { force: true });
        cy.get('#eventDate').type('2026-10-10', { force: true });
        cy.get('#eventType').select('Party', { force: true });
        cy.get('#eventDescription').type('Chaos test event', { force: true });
        cy.get('#eventFrequency').select('One-off', { force: true });

        // Click submit
        cy.get('#submitEventModal').click({ force: true });

        cy.wait('@eventError');

        // Assert that the UI gracefully degrades (does not crash or white screen)
        cy.get('body').should('not.contain', 'Exception');
        cy.get('body').should('not.contain', 'Stack trace');

        // API errors now show via SweetAlert2 directly (createEvent.js was changed to stop
        // writing into the modal's own #error <p>, since failures there were going unnoticed).
        cy.get('.swal2-popup').should('be.visible').and('contain.text', 'Internal Server Error');
    });

    it('gracefully handles 504 Gateway Timeout on Post Comment', () => {
        // Mock post comment route
        cy.intercept('POST', '/postCommentProfile', {
            statusCode: 504,
            body: { message: "Gateway Timeout" }
        }).as('commentTimeout');

        loginFully();

        // The comment form is hidden per-post until the "Comment" toggle button is clicked
        cy.contains('button', 'Comment').first().click({ force: true });
        cy.get('input[placeholder="Write a comment..."]').first().should('be.visible').type('Test comment{enter}');

        cy.wait('@commentTimeout');

        // The comment handler surfaces the intercepted response's message via SweetAlert2
        cy.get('body').should('not.contain', 'Fatal error');
        cy.get('.swal2-popup').should('exist').and('be.visible');
        cy.get('.swal2-html-container').should('contain', 'Gateway Timeout');
    });

    it('gracefully handles 500 Error on Edit Profile', () => {
        cy.intercept('POST', '/member/profilePage/editProfile', {
            statusCode: 500,
            body: { message: "Failed to update profile." }
        }).as('editProfileError');

        loginFully();

        cy.get('[data-bs-target="#editProfileModal"]').first().click({ force: true });
        cy.get('#editProfileBtnModal').click({ force: true });
        cy.wait('@editProfileError');

        // This save button posts via the same shared postFormData() helper as login, which
        // catches HTTP errors itself and renders them into the form's own notification div -
        // it never reaches editProfile.js's own SweetAlert2 catch block.
        cy.get('body').should('not.contain', 'Exception');
        cy.get('#editProfileFormModal_notification').should('be.visible').and('contain.text', 'Failed to update profile.');
    });
});
