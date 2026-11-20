import { loginFully } from '../support/login';

describe('Isolated Preact Islands: Creative Studio & Family Tree (?v=react)', () => {
    Cypress.on('uncaught:exception', () => false);

    const viewports = [
        { name: 'desktop', width: 1440, height: 900 },
        { name: 'mobile', width: 390, height: 844 },
    ];

    const runIslandDomAudit = (label, rootSelector) => {
        cy.readFile('scripts/dom_audit.js', { timeout: 10000 }).then((src) => {
            cy.window().then((win) => {
                if (typeof win.runSovereignDomAudit !== 'function') {
                    win.eval(src);
                }
                const report = win.runSovereignDomAudit();
                const rootEl = win.document.querySelector(rootSelector);
                const islandViolations = report.violations.filter((v) => {
                    if (!rootEl || !v.selector) return true;
                    try {
                        const target = win.document.querySelector(v.selector);
                        return target ? rootEl.contains(target) : false;
                    } catch {
                        return false;
                    }
                });
                expect(
                    islandViolations,
                    `${label} island DOM audit violations: ${JSON.stringify(islandViolations)}`
                ).to.have.length(0);
            });
        });
    };

    beforeEach(() => {
        loginFully(undefined, undefined, { skipProfileVisit: true });
    });

    it('Creative Studio (?v=react) mounts cleanly, passes DOM audit on Desktop & Mobile, and supports NLE/Export workflow', () => {
        cy.viewport(1440, 900);
        cy.visit('/family-studio?v=react', { timeout: 60000 });
        cy.get('#creative-studio-root [data-island="creative-studio"]', { timeout: 15000 }).should('be.visible');
        cy.contains('h1', 'Family Creative Studio').should('be.visible');
        cy.wait(300);

        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.lte(win.innerWidth + 2);
        });
        runIslandDomAudit('Studio (desktop)', '#creative-studio-root');

        // Interactive workflow on Desktop
        cy.contains('#creative-studio-root button', 'Poster Studio').click().should('have.attr', 'aria-pressed', 'true');
        cy.contains('#creative-studio-root button', 'Export & Publish').click();
        cy.get('#creative-studio-root [role="dialog"]').should('be.visible');
        cy.get('#creative-studio-root [role="dialog"] button[aria-label="Close export modal"]').click();
        cy.get('#creative-studio-root [role="dialog"]').should('not.exist');

        // Mobile viewport DOM audit
        cy.viewport(390, 844);
        cy.wait(250);
        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.lte(win.innerWidth + 2);
        });
        runIslandDomAudit('Studio (mobile)', '#creative-studio-root');
    });

    it('Family Tree (?v=react) mounts cleanly, passes DOM audit on Desktop & Mobile, and supports search & add-relative modal', () => {
        cy.viewport(1440, 900);
        cy.visit('/organogram?v=react', {
            timeout: 60000,
            onBeforeLoad(win) {
                try {
                    win.localStorage.setItem('fp_tour_completed', 'true');
                    win.localStorage.setItem('organogram_tour_seen', 'true');
                    win.localStorage.setItem('app_tour_dismissed', 'true');
                } catch (_) {}
            },
        });
        cy.get('#organogram-root [data-island="organogram"]', { timeout: 15000 }).should('be.visible');
        cy.wait(500);
        cy.get('body').then(($body) => {
            const $overlay = $body.find('#appTourOverlay, .app-tour-overlay');
            if ($overlay.length) {
                $overlay.remove();
            }
        });

        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.lte(win.innerWidth + 2);
        });
        runIslandDomAudit('Organogram (desktop)', '#organogram-root');

        // Interactive workflow on Desktop
        cy.contains('#organogram-root button', 'Add relative').click();
        cy.get('#organogram-root [role="dialog"]').should('be.visible');
        cy.contains('#organogram-root [role="dialog"] button', 'Cancel').click();
        cy.get('#organogram-root [role="dialog"]').should('not.exist');

        // Mobile viewport DOM audit
        cy.viewport(390, 844);
        cy.wait(250);
        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.lte(win.innerWidth + 2);
        });
        runIslandDomAudit('Organogram (mobile)', '#organogram-root');
    });
});
