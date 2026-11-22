import { loginFully } from '../support/login';

describe('Family Arcade — 4-Game Suite, Leagues & Sovereign DOM Audit', () => {
    Cypress.on('uncaught:exception', () => false);

    const viewports = [
        { name: 'mobile', width: 390, height: 844 },
        { name: 'desktop', width: 1440, height: 900 },
    ];

    beforeEach(() => {
        loginFully();
    });

    viewports.forEach((vp) => {
        it(`renders /arcade cleanly on ${vp.name} (${vp.width}x${vp.height}) and passes Sovereign DOM Audit`, () => {
            cy.readFile('scripts/dom_audit.js').then((auditScript) => {
                cy.viewport(vp.width, vp.height);
                cy.visit('/arcade?game=kinzip');

                cy.contains('h1', 'Claim the Family Crown').should('be.visible');
                cy.get('[role="grid"]', { timeout: 10000 }).should('be.visible');

                // Switch across all 4 games
                cy.contains('button', 'KinGrid').click();
                cy.get('[role="grid"]').should('be.visible');

                cy.contains('button', 'KinWord').click();
                cy.contains('button', 'Shuffle').should('be.visible');

                cy.contains('button', 'KinPulse').click();
                cy.contains('button', 'Start Sequence').should('be.visible');

                cy.contains('button', 'KinZip').click();
                cy.get('[role="grid"]').should('be.visible');

                // Verify League Tables
                cy.contains('h2', 'Family League Tables').should('be.visible');
                cy.contains('button', 'Family Cup').should('be.visible');

                cy.window().then((win) => {
                    const docWidth = win.document.documentElement.scrollWidth;
                    const winWidth = win.innerWidth;
                    expect(docWidth, `scrollWidth on ${vp.name}`).to.be.lte(winWidth + 2);

                    win.eval(auditScript);
                    const report = win.runSovereignDomAudit();
                    expect(
                        report.violations,
                        `DOM audit violations on ${vp.name}: ${JSON.stringify(report.violations)}`
                    ).to.have.length(0);
                });
            });
        });
    });
});
