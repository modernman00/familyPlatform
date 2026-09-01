// Helpers for driving the member-area UI deterministically.

// Sets a form field's value and keeps re-setting it until it sticks.
//
// On a cold page load the app's own form JS (and, on member pages, an Alpine
// re-render) can wipe a value written a moment too early, which then surfaces
// far away as an empty-field validation error or a request that never fires.
// Putting the write inside `.should()` lets Cypress retry it until the value is
// still there on the next tick — deterministic without a blind wait.
export function setInputValue(selector, value) {
    cy.get(selector, { timeout: 15000 }).should('be.visible');
    cy.get(selector).should(($el) => {
        const el = $el[0];
        if (el.value !== value) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
        expect(el.value, `${selector} value settled`).to.eq(value);
    });
}

// Opens a Bootstrap modal by id and resolves once it is fully on screen.
//
// The markup opens modals through Bootstrap's `data-bs-toggle="modal"` data-api,
// a single delegated listener on `document`. On a cold page load that listener
// is occasionally not attached yet when a test clicks the trigger, so the click
// is silently swallowed; and an Alpine feed re-render right after can tear a
// half-open modal back down. These specs aren't testing the data-api — they're
// testing what's inside the modal — so drive the Bootstrap instance directly and
// keep nudging it until the modal is actually visible and its backdrop is up.
export function openModal(modalId) {
    cy.window({ timeout: 20000 }).its('bootstrap.Modal').should('be.a', 'function');

    const show = () => {
        cy.window().then((win) => {
            const el = win.document.getElementById(modalId);
            expect(el, `#${modalId} in DOM`).to.exist;
            win.bootstrap.Modal.getOrCreateInstance(el).show();
        });
    };

    // Retry the show() itself (not just the assertion) — a single call can be
    // undone by a concurrent re-render.
    const showUntilVisible = (attempt = 1) => {
        show();
        cy.get(`#${modalId}`, { timeout: 4000 }).then(($m) => {
            const visible = $m.is(':visible') && $m.hasClass('show');
            if (!visible && attempt < 5) {
                cy.wait(500);
                showUntilVisible(attempt + 1);
            }
        });
    };

    showUntilVisible();

    cy.get(`#${modalId}`, { timeout: 10000 })
        .should('be.visible')
        .and('have.class', 'show');
    cy.get('.modal-backdrop', { timeout: 10000 }).should('exist');
}
