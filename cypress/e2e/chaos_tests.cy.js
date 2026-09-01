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
    beforeEach(() => {
        cy.request({ url: '/tests/clear-rate-limit', failOnStatusCode: false });
        cy.request({ url: '/login', failOnStatusCode: false });
    });

    Cypress.on('uncaught:exception', () => false);


    it('gracefully handles API network timeouts on login', () => {
        // Intercept the login endpoint and force a network error (simulating a dead
        // database or dropped connection) - registered before the visit so it's in
        // place no matter how fast the form submits.
        cy.intercept('POST', '/login', { forceNetworkError: true }).as('loginTimeout');
        cy.intercept('https://www.google.com/recaptcha/**', {
            statusCode: 200,
            body: 'window.grecaptcha = { enterprise: { ready: (cb) => cb && cb(), execute: () => Promise.resolve("mock") } };',
            headers: { 'content-type': 'application/javascript' }
        });
        cy.intercept('https://www.gstatic.com/recaptcha/**', {
            statusCode: 200,
            body: '',
            headers: { 'content-type': 'application/javascript' }
        });
        cy.intercept('https://recaptchaenterprise.googleapis.com/**', {
            statusCode: 200,
            body: { tokenProperties: { valid: true } }
        });

        cy.visit('/login', {
            onBeforeLoad(win) {
                // Lock grecaptcha on window so external scripts cannot overwrite it
                Object.defineProperty(win, 'grecaptcha', {
                    value: {
                        enterprise: {
                            ready: (cb) => { if (typeof cb === 'function') cb(); },
                            execute: () => Promise.resolve('mock-cypress-token'),
                        },
                    },
                    writable: false,
                    configurable: true,
                });
            }
        });

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
        // Mock server failure
        cy.intercept('POST', '/member/profilePage/event', {
            statusCode: 500,
            body: { message: "Internal Server Error" }
        }).as('createEventError');

        loginFully();

        // Open Create Event Modal
        openModal('createEventModal');

        // Fill Form
        cy.get('#eventName').type('Crash Test Event');
        cy.get('#eventDate').type('2026-10-10');
        cy.get('#eventType').select('Party');
        cy.get('#eventDescription').type('Chaos test event');
        cy.get('#eventFrequency').select('One-off');

        // Submit and trigger failure
        cy.get('#submitEventModal').click();

        // Wait for mock error
        cy.wait('@createEventError');

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

        // If no post exists in feed yet, create one so we have a post to comment on
        cy.get('.feed-posts-container').then(($container) => {
            if ($container.find('button:contains("Comment")').length === 0) {
                openModal('postModal');
                cy.get('textarea#postMessage').should('be.visible')
                    .invoke('val', 'Chaos Test Seed Post')
                    .trigger('input')
                    .trigger('change');
                cy.get('#submitPost').click();
                cy.get('.swal2-popup', { timeout: 10000 }).should('be.visible');
            }
        });

        // The comment form is hidden per-post until the "Comment" toggle button is clicked
        cy.contains('button', 'Comment').first().click({ force: true });
        cy.get('input.form-control.rounded-pill[placeholder*="Write a comment"], input[placeholder="Write a comment..."]').first().should('be.visible').type('Test comment{enter}');

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
