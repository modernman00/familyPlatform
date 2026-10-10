describe('Button Sleekness, Modernity and Mobile Touch Ergonomics', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.intercept('https://www.google.com/recaptcha/**', { body: '' });
        cy.intercept('https://www.gstatic.com/recaptcha/**', { body: '' });
        cy.intercept('https://recaptchaenterprise.googleapis.com/**', { body: { tokenProperties: { valid: true } } });
    });

    it('verifies desktop buttons are sleek, modern and styled correctly', () => {
        cy.viewport(1280, 800);
        cy.visit('/login');
        cy.get('.stitch-submit').should('be.visible').then(($btn) => {
            const borderRadius = parseFloat($btn.css('border-radius'));
            expect(borderRadius).to.be.at.least(8);
        });
        cy.get('.stitch-social-btn.google').should('be.visible');
        cy.screenshot('button_login_desktop');
    });

    it('verifies mobile buttons are sleek, less bulky and satisfy >= 44px touch ergonomics', () => {
        cy.viewport(390, 844); // iPhone 14 / standard modern mobile
        cy.visit('/login');

        cy.get('.stitch-submit').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44); // Segun & Apple touch target >= 44px
            expect(height).to.be.lessThan(56); // Less bulky (not clunky 56px+ slab)
        });

        cy.get('.stitch-social-btn.google').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(56);
        });

        cy.screenshot('button_login_mobile');
    });

    it('verifies registration page buttons on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/register');

        cy.get('.stitch-social-btn.google').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(56);
        });

        cy.screenshot('button_register_mobile');
    });

    it('verifies landing page buttons on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/');

        cy.get('.hero-section .btn-brand').first().should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44); // Meets touch target >= 44px
            expect(height).to.be.lessThan(56); // Sleek and less bulky (not bulky 60px)
        });

        cy.screenshot('button_landing_mobile');
    });
});
