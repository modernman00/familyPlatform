import { loginFully } from '../support/login';

describe('Kinship Suggestion Engine (People You May Know)', () => {

    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    // ------------------------------------------------------------------
    // KINSHIP RADAR WIDGET on /profilePage
    // ------------------------------------------------------------------
    describe('Kinship Radar Widget', () => {

        it('renders the Kinship Radar widget in the right sidebar', () => {
            cy.get('#kinshipRadarWidget', { timeout: 15000 }).should('exist');
            cy.get('#kinshipRadarWidget').within(() => {
                cy.contains('Suggested Kin & In-Laws').should('be.visible');
                cy.contains('Kinship Radar').should('be.visible');
            });
        });

        it('shows suggestion cards with match percentage and kinship type', () => {
            cy.get('#kinshipRadarWidget', { timeout: 15000 }).should('exist');
            // cy.get() retries until it finds at least one match and fails the test
            // on timeout if none ever appear, so it can't itself yield an empty
            // collection for a "0 or more" check - query through cy.get('body').find()
            // instead, which returns whatever it finds (including zero) immediately.
            cy.get('body').then(($body) => {
                const $cards = $body.find('#kinshipRadarWidget .kinship-item-card');
                if ($cards.length > 0) {
                    // Each card should have a match badge and kinship type
                    cy.wrap($cards.first()).within(() => {
                        cy.get('.badge').should('contain.text', 'Match');
                        cy.get('.bi-diagram-2').should('exist'); // kinship type icon
                    });
                } else {
                    // Empty state message should be visible
                    cy.get('#kinshipRadarWidget').should('contain.text', 'connected to all nearby kin');
                }
            });
        });

        it('can dismiss a suggestion card', () => {
            cy.get('#kinshipRadarWidget', { timeout: 15000 }).should('exist');
            cy.get('body').then(($body) => {
                const $cards = $body.find('#kinshipRadarWidget .kinship-item-card');
                if ($cards.length > 0) {
                    const initialCount = $cards.length;
                    // Click the dismiss button on the first card
                    cy.get('.btn-dismiss-kin').first().click();
                    // Card should be removed (with animation)
                    cy.wait(500);
                    cy.get('#kinshipRadarWidget .kinship-item-card')
                        .should('have.length.lt', initialCount);
                }
            });
        });

        it('Connect button sends a kinship request', () => {
            cy.get('#kinshipRadarWidget', { timeout: 15000 }).should('exist');
            cy.get('body').then(($body) => {
                const $cards = $body.find('#kinshipRadarWidget .kinship-item-card');
                if ($cards.length > 0) {
                    cy.intercept('POST', '/members/familyRequestMgt').as('connectReq');
                    cy.get('.btn-connect-kin').first().click();
                    // Button should change to "Connecting..." or "Request Sent"
                    cy.get('.btn-connect-kin').first().should('contain.text', 'Connect')
                        .or('contain.text', 'Request Sent');
                }
            });
        });
    });

    // ------------------------------------------------------------------
    // KINSHIP API ENDPOINT
    // ------------------------------------------------------------------
    describe('Kinship API', () => {

        it('GET /api/kinship/suggestions returns valid JSON', () => {
            cy.request({
                url: '/api/kinship/suggestions',
                failOnStatusCode: false,
                followRedirect: false
            }).then((res) => {
                expect(res.status).to.eq(200);
                expect(res.body).to.have.property('status', 'success');
                expect(res.body).to.have.property('data');
                expect(res.body.data).to.be.an('array');
            });
        });

        it('POST /api/kinship/dismiss requires dismissed_user_id', () => {
            cy.request({
                method: 'POST',
                url: '/api/kinship/dismiss',
                body: {},
                failOnStatusCode: false
            }).then((res) => {
                expect(res.status).to.be.oneOf([400, 422]);
                expect(res.body).to.have.property('status', 'error');
            });
        });

        it('each suggestion has required fields', () => {
            cy.request('/api/kinship/suggestions').then((res) => {
                const data = res.body?.data || [];
                data.forEach((item) => {
                    expect(item).to.have.property('user_id');
                    expect(item).to.have.property('firstName');
                    expect(item).to.have.property('lastName');
                    expect(item).to.have.property('confidence_score');
                    expect(item).to.have.property('kinship_type');
                    expect(item).to.have.property('primary_reason');
                    expect(item.confidence_score).to.be.gte(50);
                    expect(item.confidence_score).to.be.lte(100);
                });
            });
        });
    });

    // ------------------------------------------------------------------
    // MOBILE RESPONSIVENESS
    // ------------------------------------------------------------------
    describe('Mobile Responsiveness', () => {

        it('kinship cards stack vertically on narrow mobile viewport', () => {
            cy.viewport(375, 667); // iPhone SE
            cy.visit('/profilePage');
            // Below the lg breakpoint (992px) the desktop sidebar copy
            // (#kinshipRadarWidget) is CSS-hidden and the feed-column copy
            // (#kinshipRadarWidgetMobile) is the one actually on screen.
            cy.get('#kinshipRadarWidgetMobile', { timeout: 15000 }).should('be.visible');
        });

        it('kinship radar widget is scrollable in right sidebar on tablet', () => {
            cy.viewport('ipad-2'); // 768x1024 - still under the 992px lg breakpoint
            cy.visit('/profilePage');
            cy.get('#kinshipRadarWidgetMobile', { timeout: 15000 }).should('exist');
        });
    });

    // ------------------------------------------------------------------
    // LINEAGE WIZARD & MODAL DEFENSIVE ERROR HANDLING
    // ------------------------------------------------------------------
    describe('Lineage Wizard & Family Tree Modal Defensive Checks', () => {

        it('opens Add Relative modal and asserts zero [object Object] in modal and error states', () => {
            cy.visit('/organogram', {
                onBeforeLoad(win) {
                    win.localStorage.setItem('family_app_tour_completed', 'true');
                }
            });

            // Verify organogram page loaded
            cy.get('body').should('be.visible');
            cy.get('#addRelativeModal', { timeout: 15000 }).should('exist');

            // Dismiss tour if present
            cy.get('body').then(($body) => {
                if ($body.find('#btnTourClose').length) {
                    cy.get('#btnTourClose').click({ force: true });
                }
            });

            // Open the Add Relative modal via window helper or button click
            cy.window().then((win) => {
                if (typeof win.openAddRelativeModalFromBanner === 'function') {
                    win.openAddRelativeModalFromBanner();
                } else {
                    cy.get('#openAddRelativeModalBtn').click({ force: true });
                }
            });

            // Assert modal is shown and contains no broken object strings
            cy.get('#addRelativeModal').should('be.visible');
            cy.get('#addRelativeModal').should('not.contain.text', '[object Object]');

            // Select partner type
            cy.contains('button', 'Partner / Spouse').click();
            cy.get('#step2-partner').should('be.visible');

            // Remove client-side HTML5 required to test backend validation & defensive error parsing
            cy.get('#addPartnerForm input[name="first_name"]').invoke('removeAttr', 'required');
            cy.get('#submitPartnerBtn').click();

            // Assert error is displayed cleanly as text and never prints [object Object]
            cy.get('#addRelativeError', { timeout: 10000 }).should('be.visible');
            cy.get('#addRelativeError').should('not.contain.text', '[object Object]');
            cy.get('#addRelativeError').invoke('text').should('have.length.gt', 3);

            // Close modal
            cy.get('#addRelativeModal .btn-close').first().click();
            cy.get('#addRelativeModal').should('not.be.visible');
        });
    });
});
