import { loginFully } from '../support/login';

describe('Living Photo & Living Heirloom Picture-in-Picture (PiP)', () => {
    Cypress.on('uncaught:exception', () => false);

    function runDomAudit() {
        cy.readFile('scripts/dom_audit.js').then((auditSrc) => {
            cy.window().then((win) => {
                win.eval(auditSrc);
                const report = win.runSovereignDomAudit();
                expect(report.passed, JSON.stringify(report.violations || [])).to.eq(true);
            });
        });
    }

    it('supports Floating PiP, Expand, Minimise, and tab gating on Emoji Studio Living Photo (Desktop 1440x900)', () => {
        cy.viewport(1440, 900);
        loginFully();

        const consoleErrors = [];
        cy.visit('/member/emoji-studio?tab=photo-avatar', {
            onBeforeLoad(win) {
                cy.stub(win.console, 'error').callsFake((...args) => {
                    consoleErrors.push(args.join(' '));
                });
            },
        });

        cy.get('#photo-avatar-tab').should('be.visible');
        cy.get('#photo-avatar-tab .spip-trigger').should('be.visible').and('contain.text', 'Pop out');

        // Pop out PiP dock
        cy.get('#photo-avatar-tab .spip-trigger').click();
        cy.get('.spip-dock').should('be.visible');
        cy.get('.spip-dock__title').should('contain.text', 'Living photo preview');
        cy.get('.spip-dock__canvas').should(($c) => {
            expect($c[0].width).to.be.at.least(960);
            expect($c[0].height).to.be.at.least(1080);
        });

        // Expand modal
        cy.get('.spip-dock button[aria-label="Expand preview"]').click();
        cy.get('.spip-modal').should('be.visible');
        cy.get('#spipModalTitle').should('contain.text', 'Living photo preview');
        cy.get('.spip-modal button').contains('Back to editor').click();
        cy.get('.spip-modal').should('not.be.visible');

        // Minimise dock
        cy.get('.spip-dock button[aria-label="Minimise preview"]').click();
        cy.get('.spip-dock').should('not.be.visible');

        // Switching away from Living Photo tab hides PiP
        cy.get('.atg-tab-btn[data-atg-tab="avatar-tab"]').click();
        cy.get('.spip-dock').should('not.be.visible');
        cy.get('.spip-pill').should('not.be.visible');

        // Switch back to Living Photo tab
        cy.get('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]').click();
        cy.get('#photo-avatar-tab').should('be.visible');

        runDomAudit();
        cy.then(() => {
            expect(consoleErrors, 'Console errors on Desktop Emoji Studio').to.deep.equal([]);
        });
    });

    it('supports Floating PiP on Emoji Studio Living Photo (Mobile 390x844)', () => {
        cy.viewport(390, 844);
        loginFully();

        cy.visit('/member/emoji-studio?tab=photo-avatar');
        cy.get('#photo-avatar-tab').should('be.visible');
        cy.get('#photo-avatar-tab .spip-trigger').should('be.visible').click();
        cy.get('.spip-dock').should('be.visible');
        cy.get('.spip-dock button[aria-label="Move preview corner"]').should('be.visible').click();
        cy.get('.spip-dock').should('have.class', 'spip-dock--bottom');

        // Minimise and scroll stage out of view to verify "Show preview" pill & restore on mobile
        cy.get('.spip-dock button[aria-label="Minimise preview"]').click();
        cy.get('.spip-dock').should('not.be.visible');
        cy.scrollTo('bottom');
        cy.get('.spip-pill').should('be.visible').and('contain.text', 'Show preview').click();
        cy.get('.spip-dock').should('be.visible');
        cy.scrollTo('top');

        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.at.most(win.innerWidth);
        });
        runDomAudit();
    });

    it('supports Floating PiP and tab gating on Living Heirloom Studio (Desktop 1440x900)', () => {
        cy.viewport(1440, 900);
        loginFully();

        const consoleErrors = [];
        cy.visit('/family/living-heirloom', {
            onBeforeLoad(win) {
                cy.stub(win.console, 'error').callsFake((...args) => {
                    consoleErrors.push(args.join(' '));
                });
            },
        });

        cy.get('.lh-card .spip-trigger[aria-pressed]').should('be.visible').and('contain.text', 'Pop out').click();
        cy.get('.spip-dock').should('be.visible');
        cy.get('.spip-dock__title').should('contain.text', 'Living heirloom preview');
        cy.get('.spip-dock__canvas').should(($c) => {
            expect($c[0].width).to.be.at.least(1120);
            expect($c[0].height).to.be.at.least(1280);
        });

        // Expand & close
        cy.get('.spip-dock button[aria-label="Expand preview"]').click();
        cy.get('.spip-modal').should('be.visible');
        cy.get('#spipModalTitle').should('contain.text', 'Living heirloom preview');
        cy.get('.spip-modal button').contains('Back to editor').click();
        cy.get('.spip-modal').should('not.be.visible');

        // Switching to Age Progression hides PiP
        cy.get('.heirloom-tab-btn[data-tab="ageing"]').click();
        cy.get('.spip-dock').should('not.be.visible');

        // Switch back to Living Motion
        cy.get('.heirloom-tab-btn[data-tab="motion"]').click();
        cy.get('.lh-card').should('be.visible');

        runDomAudit();
        cy.then(() => {
            expect(consoleErrors, 'Console errors on Living Heirloom').to.deep.equal([]);
        });
    });
});
