import { setInputValue } from '../support/ui';

describe('Authentication Flow', () => {
    // Ignore 3rd-party reCAPTCHA/external script errors in headless test runner
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        // Clear cookies and session before testing login
        cy.clearCookies();
        cy.clearLocalStorage();
        cy.request({
            url: '/tests/clear-rate-limit',
            failOnStatusCode: false
        });
        // Warm the /login route (PHP opcache, session, DB pool) so the first real
        // page load of the run isn't racing a cold server for a worker.
        cy.request({ url: '/login', failOnStatusCode: false });

        // Neutralize Google reCAPTCHA Enterprise network script
        cy.intercept('https://www.google.com/recaptcha/**', { body: '' });
        cy.intercept('https://www.gstatic.com/recaptcha/**', { body: '' });
        cy.intercept('https://recaptchaenterprise.googleapis.com/**', { body: { tokenProperties: { valid: true } } });
    });

    it('successfully logs in with valid credentials', () => {
        cy.intercept('POST', '/login').as('loginReq');

        cy.visit('/login', {
            onBeforeLoad(win) {
                // Define grecaptcha as non-writable so external scripts cannot overwrite it
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

        cy.get('form#login').should('be.visible');
        cy.document().its('readyState').should('eq', 'complete');
        cy.get('button#button[data-ready="true"]', { timeout: 15000 }).should('exist');
        setInputValue('input[name="email"]', 'cypress_test@myfamilyplatform.com');
        setInputValue('input[name="password"]', 'National2');

        cy.get('button#button').should('be.visible').click();

        cy.wait('@loginReq', { timeout: 30000 }).then((interception) => {
            expect(interception.response.statusCode).to.be.oneOf([200, 201]);
        });

        // Ensure the notification confirmation renders and the session settles
        cy.get('#login_notification', { timeout: 10000 })
            .should('be.visible')
            .and('contain.text', 'Verification code sent');

        // Confirm session state and navigate to 2FA verification step
        cy.request({
            url: '/login/code',
            failOnStatusCode: false,
            followRedirect: false
        }).its('status').should('eq', 200);

        cy.visit('/login/code');
        cy.location('pathname', { timeout: 10000 }).should('eq', '/login/code');
        cy.get('form#code').should('be.visible');
    });
});

