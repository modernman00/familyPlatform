describe('Creative Studio & Sub-Studios Mobile CapCut Ergonomics', () => {
    Cypress.on('uncaught:exception', () => false);

    const viewports = [
        { name: 'mobile', width: 390, height: 844 },
        { name: 'ipad-portrait', width: 768, height: 1024 },
        { name: 'ipad-landscape', width: 1024, height: 768 },
        { name: 'desktop', width: 1440, height: 900 },
    ];

    const studioRoutes = [
        { path: '/creative-studio', name: 'Creative Studio Hub' },
        { path: '/familyStudio', name: 'Tree Studio' },
        { path: '/poster-studio', name: 'Flyers & Posters Studio' },
        { path: '/member/festive-cards', name: 'Festive Cards Studio' },
        { path: '/family-studio', name: 'Living Archives Studio' },
    ];

    function loginStudioSession() {
        const email = Cypress.env('TEST_USER_EMAIL') || 'cypress_test@myfamilyplatform.com';
        const password = Cypress.env('TEST_USER_PASSWORD') || 'National2';

        cy.session(['studio', email], () => {
            cy.request({
                method: 'POST',
                url: '/api/test/clear-rate-limit',
                failOnStatusCode: false,
                headers: { 'X-Cypress-Test': 'true' }
            });
            cy.request({
                method: 'POST',
                url: '/login',
                form: true,
                body: { email, password },
                failOnStatusCode: false,
                headers: { 'X-Cypress-Test': 'true' }
            }).then((res) => {
                expect([200, 201]).to.include(res.status);
            });
            cy.task('getLatest2FACode', email).then((code) => {
                expect(code).to.be.a('string').and.not.be.empty;
                cy.request({
                    method: 'POST',
                    url: '/login/code',
                    form: true,
                    body: { code },
                    failOnStatusCode: false,
                    headers: { 'X-Cypress-Test': 'true' }
                }).then((codeRes) => {
                    expect([200, 201]).to.include(codeRes.status);
                });
            });
        });
    }

    beforeEach(() => {
        loginStudioSession();
    });

    it('renders engaging bespoke graphics for all 4 sub-containers on Creative Studio hub', () => {
        cy.viewport(390, 844);
        cy.visit('/creative-studio');

        cy.get('.cs-hero').should('be.visible');
        cy.get('.cs-card').should('have.length', 4);
        cy.get('.cs-stage--tree svg').should('be.visible');
        cy.get('.cs-stage--flyers svg').should('be.visible');
        cy.get('.cs-stage--cards svg').should('be.visible');
        cy.get('.cs-stage--archives svg').should('be.visible');

        cy.get('.cs-card__cta').each(($cta) => {
            expect($cta[0].getBoundingClientRect().height).to.be.gte(44);
        });
    });

    studioRoutes.forEach((route) => {
        it(`${route.name} (${route.path}) has zero horizontal overflow & passes DOM audit across all 4 viewports`, () => {
            cy.readFile('scripts/dom_audit.js').then((auditScript) => {
                viewports.forEach((vp) => {
                    cy.viewport(vp.width, vp.height);
                    cy.visit(route.path);
                    cy.wait(300); // Allow 200ms navbar/theme CSS transitions to settle

                    cy.window().then((win) => {
                        const docWidth = win.document.documentElement.scrollWidth;
                        const winWidth = win.innerWidth;
                        expect(docWidth, `${route.path} scrollWidth on ${vp.name}`).to.be.lte(winWidth + 2);

                        win.eval(auditScript);
                        const report = win.runSovereignDomAudit();
                        expect(
                            report.violations,
                            `${route.path} DOM audit violations on ${vp.name}: ${JSON.stringify(report.violations)}`
                        ).to.have.length(0);
                    });
                });
            });
        });
    });
});
