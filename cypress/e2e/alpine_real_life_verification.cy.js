describe('Alpine Enterprise, Button Spinners & SweetAlert2 Real-Life Verification', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.intercept('https://www.google.com/recaptcha/**', { body: '' });
        cy.intercept('https://www.gstatic.com/recaptcha/**', { body: '' });
        cy.intercept('https://recaptchaenterprise.googleapis.com/**', { body: { tokenProperties: { valid: true } } });
        cy.viewport(1280, 800);
        cy.visit('/login');
    });

    it('verifies Alpine.js is booted with official plugins, stores, and magics', () => {
        cy.window().then((win) => {
            expect(win.Alpine, 'Alpine must be loaded on window').to.exist;
            expect(win.Alpine.version, 'Alpine must have a valid version').to.be.a('string');

            // 1. Verify Global Reactive Stores
            const notifStore = win.Alpine.store('notifications');
            expect(notifStore, '$store.notifications must exist').to.exist;
            expect(notifStore.unreadCount, 'unreadCount must default to 0').to.equal(0);
            expect(notifStore.display, 'display must default to empty').to.equal('');

            const themeStore = win.Alpine.store('theme');
            expect(themeStore, '$store.theme must exist').to.exist;

            const netStore = win.Alpine.store('network');
            expect(netStore, '$store.network must exist').to.exist;
            expect(netStore.isOnline, 'isOnline must be true').to.be.true;

            const modalStore = win.Alpine.store('modal');
            expect(modalStore, '$store.modal must exist').to.exist;
        });
    });

    it('verifies real-time reactive notification badge sync across the UI', () => {
        cy.window().then((win) => {
            const notifStore = win.Alpine.store('notifications');

            // Set count to 7
            notifStore.setCount(7);
            expect(notifStore.unreadCount).to.equal(7);
            expect(notifStore.display).to.equal('7');
            expect(notifStore.hasUnread).to.be.true;

            // Increment count by 3 (becomes 10)
            notifStore.increment(3);
            expect(notifStore.unreadCount).to.equal(10);
            expect(notifStore.display).to.equal('10');

            // Test 99+ ceiling formatting
            notifStore.setCount(150);
            expect(notifStore.display).to.equal('99+');

            // Clear count
            notifStore.clear();
            expect(notifStore.unreadCount).to.equal(0);
            expect(notifStore.display).to.equal('');
            expect(notifStore.hasUnread).to.be.false;
        });
    });

    it('verifies theme reactive store toggles dark mode cleanly', () => {
        cy.window().then((win) => {
            const themeStore = win.Alpine.store('theme');
            const initialDark = themeStore.isDark;

            // Toggle theme
            themeStore.toggle();
            expect(themeStore.isDark).to.equal(!initialDark);
            if (themeStore.isDark) {
                expect(win.document.body.classList.contains('dark-mode')).to.be.true;
                expect(win.document.documentElement.classList.contains('dark')).to.be.true;
            } else {
                expect(win.document.body.classList.contains('dark-mode')).to.be.false;
                expect(win.document.documentElement.classList.contains('dark')).to.be.false;
            }

            // Restore initial state
            themeStore.toggle();
            expect(themeStore.isDark).to.equal(initialDark);
        });
    });

    it('verifies SweetAlert2 (Swal) library is active and renders toasts in real life', () => {
        cy.window().then((win) => {
            expect(win.Swal, 'window.Swal must be loaded').to.exist;
            expect(win.swalToast, 'window.swalToast must be a function').to.be.a('function');

            // Fire real-life toast
            win.swalToast('Real-life verification toast active!', 'success');
        });

        // Assert that SweetAlert2 toast popup appears in the DOM
        cy.get('.swal2-popup.swal2-toast', { timeout: 5000 })
            .should('be.visible')
            .and('contain.text', 'Real-life verification toast active!');
    });

    it('verifies button spinner triggers on submit and stops correctly', () => {
        cy.window().then((win) => {
            expect(win.FamilyPlatformSpinner, 'FamilyPlatformSpinner must be initialized').to.exist;
            expect(win.ScoreTenantSpinner, 'ScoreTenantSpinner alias must be intact').to.exist;

            const submitBtn = win.document.querySelector('button[type="submit"], .stitch-submit');
            expect(submitBtn, 'Submit button must exist on login page').to.exist;

            // Simulate start
            win.FamilyPlatformSpinner.start(submitBtn);
            expect(submitBtn.getAttribute('aria-busy')).to.equal('true');
            expect(submitBtn.querySelector('.st-spinner-border'), 'Spinner markup must be injected').to.exist;

            // Simulate stop
            win.FamilyPlatformSpinner.stop(submitBtn);
            expect(submitBtn.getAttribute('aria-busy')).to.be.null;
            expect(submitBtn.querySelector('.st-spinner-border'), 'Spinner markup must be removed').to.not.exist;
        });
    });
});
