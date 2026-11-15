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
    }

    function assertSovereignDomAudit(label) {
        cy.readFile('scripts/dom_audit.js').then((auditScript) => {
            cy.window().then((win) => {
                if (typeof win.runSovereignDomAudit !== 'function') {
                    win.eval(auditScript);
                }
                const report = win.runSovereignDomAudit();
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
            });
        });
    }

    it('supports temporary close, permanent dismissal via API, and always-available relaunch on Desktop (1440x900)', () => {
        cy.viewport(1440, 900);
        loginFully();

        // Ensure server preference is reset to undismissed before testing auto-start
        cy.window().then((win) => {
            resetTourClientState(win);
            const csrf = win.document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            return win.fetch('/api/profile/tour-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ tour_key: 'profile_page_v1', dismissed: false, token: csrf }),
            });
        });

        cy.reload();

        // 1. Auto-starts on first visit
        cy.get('.sspot-overlay', { timeout: 6000 }).should('be.visible');
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
        assertSovereignDomAudit('Desktop (1440x900) Light Mode');
        setDarkMode(true);
        assertSovereignDomAudit('Desktop (1440x900) Dark Mode');
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
        cy.get('[data-sspot-action="dismiss-permanent"]').should('be.visible').click();
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
            loginFully();

            // Ensure any auto-started tour from a prior run is closed before testing manual launch
            cy.get('body').then(($body) => {
                if ($body.find('[data-sspot-action="close"]').length > 0) {
                    cy.get('[data-sspot-action="close"]').click();
                    cy.get('.sspot-overlay').should('not.exist');
                }
            });

            cy.get('#profileTourCardBtn').scrollIntoView({ block: 'center' }).should('be.visible').then(($btn) => {
                const r = $btn[0].getBoundingClientRect();
                expect(r.height).to.be.at.least(44);
                expect(r.width).to.be.at.least(44);
            }).click();

            cy.get('.sspot-overlay').should('be.visible');
            cy.get('.sspot-card').should('be.visible');

            // Run Sovereign DOM Audit in both Light and Dark mode while .sspot-card is open
            setDarkMode(false);
            assertSovereignDomAudit(`${vp.name} (${vp.width}x${vp.height}) Light Mode`);
            setDarkMode(true);
            assertSovereignDomAudit(`${vp.name} (${vp.width}x${vp.height}) Dark Mode`);
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
                        cy.get('[data-sspot-highlight]').then(($hl) => {
                            const style = window.getComputedStyle($hl[0]);
                            if (style.display !== 'none') {
                                const hRect = $hl[0].getBoundingClientRect();
                                cy.get('[data-sspot-card]').then(($card) => {
                                    const cRect = $card[0].getBoundingClientRect();
                                    const tTop = hRect.top + 8;
                                    const tBottom = hRect.bottom - 8;
                                    const availableSpace = vp.height - cRect.height - 24;
                                    if ((tBottom - tTop) <= availableSpace) {
                                        const noVerticalOverlap = (cRect.bottom <= tTop + 2) || (cRect.top >= tBottom - 2);
                                        expect(
                                            noVerticalOverlap,
                                            `Mobile step ${i}: card [${Math.round(cRect.top)}..${Math.round(cRect.bottom)}] must not overlap target [${Math.round(tTop)}..${Math.round(tBottom)}]`
                                        ).to.equal(true);
                                    }
                                });
                            }
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
