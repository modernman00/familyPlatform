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
            expect(height).to.be.lessThan(52); // Sleek, non-bulky (under 52px)
        });

        cy.get('.stitch-social-btn.google').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(52);
        });

        cy.get('#btn-passkey-login').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(52);
        });

        cy.screenshot('button_login_mobile');
    });

    it('verifies forgot password page buttons on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/login/forgot?verify=1');

        cy.get('.stitch-submit').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(52);
            const borderRadius = parseFloat($btn.css('border-radius'));
            expect(borderRadius).to.be.at.least(10);
        });

        cy.screenshot('button_forgot_mobile');
    });

    it('verifies sanctuary recommendation page button on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/join');

        cy.get('.btn-sanctuary-primary').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44); // Segun touch target >= 44px
            expect(height).to.be.lessThan(50); // Sleek and streamlined, not bulky
            const borderRadius = parseFloat($btn.css('border-radius'));
            expect(borderRadius).to.be.at.least(10);
        });

        cy.screenshot('button_join_mobile');
    });

    it('verifies registration page buttons on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/register');

        cy.get('.stitch-social-btn.google').should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44);
            expect(height).to.be.lessThan(52);
        });

        cy.screenshot('button_register_mobile');
    });

    it('verifies landing page buttons on mobile', () => {
        cy.viewport(390, 844);
        cy.visit('/');

        cy.get('.hero-section .btn-brand').first().should('be.visible').then(($btn) => {
            const height = $btn.outerHeight();
            expect(height).to.be.at.least(44); // Meets touch target >= 44px
            expect(height).to.be.lessThan(52); // Sleek and less bulky (under 52px)
        });

        cy.screenshot('button_landing_mobile');
    });

    it('verifies submit and icon buttons show strictly spinner without Processing text and recover cleanly', () => {
        cy.visit('/login');
        cy.window().then((win) => {
            expect(win.FamilyPlatformSpinner).to.exist;
            const btn = win.document.querySelector('.stitch-submit');
            expect(btn).to.exist;
            
            // Trigger spinner
            win.FamilyPlatformSpinner.start(btn);
            expect(btn.getAttribute('aria-busy')).to.equal('true');
            expect(btn.querySelector('.st-spinner-border')).to.exist;
            expect(btn.innerText.trim()).to.not.include('Processing');
            expect(btn.innerText.trim()).to.equal('');
            
            // Stop spinner
            win.FamilyPlatformSpinner.stop(btn);
            expect(btn.getAttribute('aria-busy')).to.be.null;
            expect(btn.querySelector('.st-spinner-border')).to.be.null;
        });
    });
});
