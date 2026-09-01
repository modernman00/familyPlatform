/**
 * Chaos Testing Suite for Structural Safety (Plan for the Worst)
 * ISO 27001 - Availability & Resiliency
 * Customized for familyPlatform
 */

import { loginFully } from '../support/login';
import { openModal, setInputValue } from '../support/ui';

describe('Chaos & Resiliency Testing', () => {

    // The app pulls in third-party scripts (fontawesome kit, service worker) that
    // occasionally throw async errors unrelated to what these tests assert. A
    // chaos suite in particular must not fail on someone else's stack trace.
    Cypress.on('uncaught:exception', () => false);


    it('gracefully handles API network timeouts on login', () => {
        // Intercept the login endpoint and force a network error (simulating a dead
        // database or dropped connection) - registered before the visit so it's in
        // place no matter how fast the form submits.
        cy.intercept('POST', '/login', { forceNetworkError: true }).as('loginTimeout');

        cy.visit('/login');

        // Wait for the page to finish loading and the login chunk to wire the
        // submit handler (it stamps data-ready as its final init step).
        cy.document().its('readyState').should('eq', 'complete');
        cy.get('button#button[data-ready="true"]', { timeout: 15000 }).should('exist');

        // Setting values that survive the form's own cold-load JS - see setInputValue.
        setInputValue('input[name="email"]', 'test@example.com');
        setInputValue('input[name="password"]', 'Secretpassword123');

        cy.get('button#button').should('be.visible').click();

        cy.wait('@loginTimeout', { timeout: 15000 }).then(() => {
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

        openModal('createEventModal');

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

        openModal('editProfileModal');
        cy.get('#editProfileBtnModal').click({ force: true });
        cy.wait('@editProfileError');

        // This save button posts via the same shared postFormData() helper as login, which
        // catches HTTP errors itself and renders them into the form's own notification div -
        // it never reaches editProfile.js's own SweetAlert2 catch block.
        cy.get('body').should('not.contain', 'Exception');
        cy.get('#editProfileFormModal_notification').should('be.visible').and('contain.text', 'Failed to update profile.');
    });
});
