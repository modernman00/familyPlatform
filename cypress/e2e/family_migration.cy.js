import { loginFully } from '../support/login';

describe('Family Code Migration & Discovery Nudge E2E Journey', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('displays Nudge and Join buttons on directory cards for members with different codes', () => {
        cy.visit('/allMembers');

        // Wait for member cards to load
        cy.get('.member-card', { timeout: 15000 }).should('have.length.at.least', 1);

        // Click "Directory Search" to view relatives with different family codes
        cy.get('[data-filter="explore"]').click();

        // Cards in directory category should be visible
        cy.get('.member-card[data-category="explore"]', { timeout: 10000 })
            .filter(':visible')
            .should('have.length.at.least', 1);

        // Verify the presence of Nudge and Request to Join buttons
        cy.get('.member-card[data-category="explore"]')
            .filter(':visible')
            .first()
            .within(() => {
                cy.get('.btn-nudge-family').should('exist').and('contain.text', 'Nudge to Join Your Family Code');
                cy.get('.btn-request-migration').should('exist').and('contain.text', 'Join #');
            });
    });

    it('opens interactive modal when clicking Nudge to Join Your Family Code', () => {
        cy.visit('/allMembers');
        cy.get('.member-card', { timeout: 15000 }).should('have.length.at.least', 1);
        cy.get('[data-filter="explore"]').click();

        cy.get('.member-card[data-category="explore"]', { timeout: 10000 })
            .filter(':visible')
            .first()
            .find('.btn-nudge-family')
            .click({ force: true });

        // SweetAlert modal should open
        cy.get('.swal2-modal', { timeout: 8000 }).should('be.visible');
        cy.get('#swal2-title').should('contain.text', 'Nudge');
        cy.get('#swal-nudge-note').should('exist');
        cy.get('.swal2-confirm').should('be.visible').and('contain.text', 'Send Nudge');
        cy.get('.swal2-cancel').should('be.visible').click();
    });

    it('opens interactive modal when clicking Join Family Code', () => {
        cy.visit('/allMembers');
        cy.get('.member-card', { timeout: 15000 }).should('have.length.at.least', 1);
        cy.get('[data-filter="explore"]').click();

        cy.get('.member-card[data-category="explore"]', { timeout: 10000 })
            .filter(':visible')
            .first()
            .find('.btn-request-migration')
            .click({ force: true });

        // SweetAlert modal should open
        cy.get('.swal2-modal', { timeout: 8000 }).should('be.visible');
        cy.get('#swal2-title').should('contain.text', 'Join Family');
        cy.get('#swal-req-note').should('exist');
        cy.get('.swal2-confirm').should('be.visible').and('contain.text', 'Submit Request');
        cy.get('.swal2-cancel').should('be.visible').click();
    });

    it('submits a nudge through the UI and receives a real controller response', () => {
        cy.intercept('POST', '**/api/family-migration/nudge').as('nudge');

        cy.visit('/allMembers');
        cy.get('.member-card', { timeout: 15000 }).should('have.length.at.least', 1);
        cy.get('[data-filter="explore"]').click();

        cy.get('.member-card[data-category="explore"]', { timeout: 10000 })
            .filter(':visible')
            .first()
            .find('.btn-nudge-family')
            .click({ force: true });

        cy.get('.swal2-confirm', { timeout: 8000 }).should('be.visible').click();

        // Either a fresh nudge succeeds (200) or a duplicate is politely refused (4xx JSON),
        // but it must never be a 403 CSRF rejection or a 5xx.
        cy.wait('@nudge').then(({ response }) => {
            expect(response.statusCode).to.be.lessThan(500);
            expect(response.statusCode).to.not.eq(403);
            if (response.body.success !== undefined) {
                expect(response.body).to.have.property('success');
            } else {
                expect(response.body).to.have.property('error');
            }
        });
        cy.get('.swal2-modal', { timeout: 8000 }).should('be.visible');
    });

    it('verifies profile dashboard renders without errors', () => {
        cy.visit('/profilePage');
        cy.get('.feed-column', { timeout: 15000 }).should('exist');
    });

    describe('Dashboard Migration Approval & Decline E2E Actions', () => {
        beforeEach(() => {
            cy.exec('php scripts/e2e_family_migration_fixture.php seed_for_cypress_user');
        });

        afterEach(() => {
            cy.exec('php scripts/e2e_family_migration_fixture.php cleanup');
        });

        it('displays incoming migration request card and successfully approves it', () => {
            cy.intercept('POST', '**/api/family-migration/approve-request').as('approveReq');

            cy.visit('/profilePage');

            // Verify incoming request card is visible
            cy.get('.family-migration-request-card', { timeout: 15000 }).should('be.visible');
            cy.get('.family-migration-request-card').within(() => {
                cy.contains('requested to migrate to your #SHO family network').should('exist');
                cy.get('button[id^="btnApproveMigReq-"]').should('exist').and('contain.text', 'Approve Request').click();
            });

            // Assert AJAX approval response is 200 with success: true
            cy.wait('@approveReq').then(({ response }) => {
                expect(response.statusCode).to.eq(200);
                expect(response.body).to.have.property('success', true);
            });

            // Assert tactile UI update to success notification inside card
            cy.get('.family-migration-request-card', { timeout: 10000 })
                .should('contain.text', 'Successfully approved');
        });

        it('displays incoming migration nudge card and successfully declines it', () => {
            cy.intercept('POST', '**/api/family-migration/decline').as('declineNudge');

            cy.visit('/profilePage');

            // Verify incoming nudge card is visible
            cy.get('.family-migration-nudge-card', { timeout: 15000 }).should('be.visible');
            cy.get('.family-migration-nudge-card').within(() => {
                cy.contains('invited you to migrate').should('exist');
                cy.get('button[id^="btnDeclineNudge-"]').should('exist').and('contain.text', 'Decline').click();
            });

            // Assert AJAX decline response is 200 with success: true
            cy.wait('@declineNudge').then(({ response }) => {
                expect(response.statusCode).to.eq(200);
                expect(response.body).to.have.property('success', true);
            });

            // Assert tactile UI update to decline notification inside card
            cy.get('.family-migration-nudge-card', { timeout: 10000 })
                .should('contain.text', 'Invitation declined');
        });
    });
});
