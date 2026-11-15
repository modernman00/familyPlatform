import { loginFully } from '../support/login';

describe('Profile Page Interactive Tour Engine', () => {
    Cypress.on('uncaught:exception', () => false);

    let consoleErrors = [];

    beforeEach(() => {
        consoleErrors = [];
        cy.intercept('GET', '/resources/images/profile/*.svg', {
            statusCode: 200,
            headers: { 'content-type': 'image/svg+xml' },
            body: '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="#a31621"/></svg>',
        });
        cy.intercept('GET', '/post/getAllPostCommentByFamCode*', {
            statusCode: 200,
            body: { status: 'success', posts: [], data: [], hasMore: false },
        });
        cy.intercept('GET', '/getFriendRequestById*', {
            statusCode: 200,
            body: { status: 'success', message: { message: [] }, data: [] },
        });
        cy.intercept('GET', '/allMembers/processApiData*', {
            statusCode: 200,
            body: { status: 'success', data: [], members: [] },
        });
        cy.intercept('GET', '/api/hashtags/trending*', {
            statusCode: 200,
            body: { status: 'success', data: [], hashtags: [] },
        });
        cy.intercept('GET', '/api/broadcasting/status*', {
            statusCode: 200,
            body: { status: 'success', enabled: false },
        });
        cy.intercept('GET', '/api/engagement/memories*', {
            statusCode: 200,
            body: { status: 'success', data: [] },
        });
        cy.on('window:before:load', (win) => {
            const origError = win.console.error.bind(win.console);
            win.console.error = (...args) => {
                consoleErrors.push(args.map((a) => {
                    if (a instanceof Error) return a.message;
                    if (typeof a === 'object' && a !== null) {
                        try { return JSON.stringify(a); } catch (e) { return String(a); }
                    }
                    return String(a);
                }).join(' '));
                origError(...args);
            };
        });
    });

    function resetTourClientState(win) {
        win.localStorage.removeItem('fp_profile_tour_dismissed_v1');
        win.sessionStorage.removeItem('fp_profile_tour_closed_session_v1');
    }

    function setDarkMode(enabled) {
        cy.window().then((win) => {
            const themeStore = win.Alpine && typeof win.Alpine.store === 'function'
                ? win.Alpine.store('theme')
                : null;
            if (themeStore && typeof themeStore.toggle === 'function') {
                if (Boolean(themeStore.isDark) !== Boolean(enabled)) {
                    themeStore.toggle();
                }
            } else {
                win.document.body.classList.toggle('dark-mode', Boolean(enabled));
            }
        });
        if (enabled) {
            cy.get('body').should('have.class', 'dark-mode');
        } else {
            cy.get('body').should('not.have.class', 'dark-mode');
        }
        cy.wait(300); // Allow 200ms theme/navbar CSS transitions to settle
    }

    function assertSovereignDomAudit(label, tourOnly = false) {
        cy.readFile('scripts/dom_audit.js').then((auditScript) => {
            cy.window().then((win) => {
                if (typeof win.runSovereignDomAudit !== 'function') {
                    win.eval(auditScript);
                }
                const report = win.runSovereignDomAudit();
                if (tourOnly) {
                    const tourViolations = (report.violations || []).filter((v) => {
                        const sel = String(v.selector || '');
                        return sel.includes('sspot') || sel.includes('profileTour');
                    });
                    expect(
                        tourViolations,
                        `${label}: Tour DOM audit violations: ${JSON.stringify(tourViolations)}`
                    ).to.have.length(0);
                } else {
                    expect(
                        report.passed,
                        `${label}: DOM audit failed with violations: ${JSON.stringify(report.violations)}`
                    ).to.equal(true);
                    expect(
                        report.violationCount,
                        `${label}: DOM audit violationCount`
                    ).to.equal(0);
                    expect(
                        report.violations,
                        `${label}: DOM audit violations`
                    ).to.have.length(0);
                }
            });
        });
    }

    it('supports temporary close, permanent dismissal via API, and always-available relaunch on Desktop (1440x900)', () => {
        cy.viewport(1440, 900);
        let allowAutoStart = true;
        let forceServerNotDismissed = true;
        cy.on('window:before:load', (win) => {
            if (allowAutoStart) {
                resetTourClientState(win);
            }
            if (forceServerNotDismissed) {
                let cfg = { dismissed: false, isPendingApproval: false };
                Object.defineProperty(win, '__FP_PROFILE_TOUR_CONFIG__', {
                    configurable: true,
                    get() { return cfg; },
                    set(v) { cfg = Object.assign({}, v || {}, { dismissed: false, isPendingApproval: false }); },
                });
            }
        });
        loginFully();

        // 1. Auto-starts on first visit
        cy.get('.sspot-overlay', { timeout: 6000 }).should('be.visible').then(() => {
            allowAutoStart = false;
        });
        cy.get('.sspot-title').should('contain.text', 'Welcome to Your Family Sanctuary');

        // 2. Temporary close (×) suppresses auto-start in same session, without permanent dismissal
        cy.get('[data-sspot-action="close"]').click();
        cy.get('.sspot-overlay').should('not.exist');
        cy.window().then((win) => {
            expect(win.sessionStorage.getItem('fp_profile_tour_closed_session_v1')).to.equal('1');
            expect(win.localStorage.getItem('fp_profile_tour_dismissed_v1')).to.be.null;
        });

        cy.reload();
        cy.wait(1100);
        cy.get('.sspot-overlay').should('not.exist');

        // 3. Always-available Profile Tour button relaunches anytime
        cy.get('#profileTourCardBtn').should('be.visible').click();
        cy.get('.sspot-overlay').should('be.visible');
        cy.get('.sspot-card').should('be.visible');

        // Run Sovereign DOM Audit in both Light and Dark mode while .sspot-card is open
        setDarkMode(false);
        assertSovereignDomAudit('Desktop (1440x900) Light Mode', false);
        setDarkMode(true);
        assertSovereignDomAudit('Desktop (1440x900) Dark Mode', true);
        setDarkMode(false);

        // 4. Step through all available steps and check layout / touch targets
        cy.get('.sspot-dot').then(($dots) => {
            const totalSteps = $dots.length;
            expect(totalSteps).to.be.at.least(8);

            for (let i = 0; i < totalSteps - 1; i++) {
                cy.get('[data-sspot-card]').should('be.visible').then(($card) => {
                    const r = $card[0].getBoundingClientRect();
                    expect(r.left).to.be.at.least(0);
                    expect(r.right).to.be.at.most(1440);
                    expect(r.top).to.be.at.least(0);
                    expect(r.bottom).to.be.at.most(900);
                });
                cy.get('[data-sspot-action="next"]').click();
            }

            // Last step shows Finish
            cy.get('[data-sspot-action="next"]').should('contain.text', 'Finish');
        });

        // 5. Permanent dismissal persists via POST /api/profile/tour-preference
        cy.intercept('POST', '/api/profile/tour-preference').as('saveTourPref');
        cy.get('[data-sspot-action="dismiss-permanent"]').should('be.visible').click().then(() => {
            forceServerNotDismissed = false;
        });
        cy.wait('@saveTourPref').its('response.statusCode').should('eq', 200);
        cy.get('.sspot-overlay').should('not.exist');

        // Even if sessionStorage is cleared, server + localStorage keep it dismissed
        cy.window().then((win) => {
            win.sessionStorage.removeItem('fp_profile_tour_closed_session_v1');
            expect(win.localStorage.getItem('fp_profile_tour_dismissed_v1')).to.equal('1');
        });
        cy.reload();
        cy.wait(1100);
        cy.get('.sspot-overlay').should('not.exist');

        // 6. Button STILL works even after permanent dismissal
        cy.get('#profileTourCardBtn').should('be.visible').click();
        cy.get('.sspot-overlay').should('be.visible');
        cy.get('[data-sspot-action="close"]').click();

        cy.then(() => {
            expect(consoleErrors, `Desktop console errors: ${JSON.stringify(consoleErrors)}`).to.have.length(0);
        });
    });

    [
        { name: 'iPad Portrait', width: 768, height: 1024 },
        { name: 'iPad Landscape', width: 1024, height: 768 },
        { name: 'Mobile', width: 390, height: 844 },
    ].forEach((vp) => {
        it(`renders cleanly with >=44px tap targets and zero horizontal overflow on ${vp.name} (${vp.width}x${vp.height})`, () => {
            cy.viewport(vp.width, vp.height);
            cy.on('window:before:load', (win) => {
                win.sessionStorage.setItem('fp_profile_tour_closed_session_v1', '1');
            });
            loginFully();

            cy.get('#profileTourCardBtn').scrollIntoView({ block: 'center' }).should('be.visible').then(($btn) => {
                const r = $btn[0].getBoundingClientRect();
                expect(r.height).to.be.at.least(44);
                expect(r.width).to.be.at.least(44);
            }).click();

            cy.get('.sspot-overlay').should('be.visible');
            cy.get('.sspot-card').should('be.visible');

            // Run Sovereign DOM Audit in both Light and Dark mode while .sspot-card is open
            setDarkMode(false);
            assertSovereignDomAudit(`${vp.name} (${vp.width}x${vp.height}) Light Mode`, false);
            setDarkMode(true);
            assertSovereignDomAudit(`${vp.name} (${vp.width}x${vp.height}) Dark Mode`, true);
            setDarkMode(false);

            cy.get('.sspot-dot').then(($dots) => {
                const totalSteps = $dots.length;
                for (let i = 0; i < totalSteps; i++) {
                    cy.get('[data-sspot-card]').should('be.visible').then(($card) => {
                        const r = $card[0].getBoundingClientRect();
                        expect(r.left).to.be.at.least(0);
                        expect(r.right).to.be.at.most(vp.width);
                        expect(r.top).to.be.at.least(0);
                        expect(r.bottom).to.be.at.most(vp.height);
                    });

                    if (vp.width < 768) {
                        cy.get('[data-sspot-card]').should(($card) => {
                            const overlay = $card[0].closest('.sspot-overlay');
                            const bodyEl = $card[0].querySelector('.sspot-body');
                            if (bodyEl) {
                                expect(bodyEl.getBoundingClientRect().height, `Mobile step ${i}: .sspot-body must be visible`).to.be.greaterThan(12);
                            }
                            const hl = overlay ? overlay.querySelector('[data-sspot-highlight]') : null;
                            if (!hl) return;
                            const style = window.getComputedStyle(hl);
                            if (style.display === 'none') return;
                            const hRect = hl.getBoundingClientRect();
                            const cRect = $card[0].getBoundingClientRect();
                            const noVerticalOverlap = (cRect.bottom <= hRect.top + 2) || (cRect.top >= hRect.bottom - 2);
                            expect(
                                noVerticalOverlap,
                                `Mobile step ${i}: card [${Math.round(cRect.top)}..${Math.round(cRect.bottom)}] must not overlap spotlight [${Math.round(hRect.top)}..${Math.round(hRect.bottom)}]`
                            ).to.equal(true);
                        });
                    }

                    cy.window().then((win) => {
                        expect(win.document.documentElement.scrollWidth).to.be.at.most(vp.width + 1);
                    });

                    if (i < totalSteps - 1) {
                        cy.get('[data-sspot-action="next"]').click();
                    }
                }
            });

            cy.get('[data-sspot-action="close"]').click();
            cy.get('.sspot-overlay').should('not.exist');

            cy.then(() => {
                expect(consoleErrors, `${vp.name} console errors: ${JSON.stringify(consoleErrors)}`).to.have.length(0);
            });
        });
    });
});
