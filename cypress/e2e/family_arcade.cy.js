import { loginFully } from '../support/login';

describe('Family Arcade — 7-Game Mobile & Multi-Viewport Verification + Sovereign DOM Audit', () => {
    Cypress.on('uncaught:exception', () => false);

    const viewports = [
        { name: 'mobile-narrow', width: 360, height: 800 },
        { name: 'mobile', width: 390, height: 844 },
        { name: 'ipad-portrait', width: 768, height: 1024 },
        { name: 'ipad-landscape', width: 1024, height: 768 },
        { name: 'desktop', width: 1440, height: 900 },
    ];

    const hideTransientBannersAndFrameArena = () => {
        cy.window().then((win) => {
            const selectors = [
                '#pwa-install-banner',
                '#pushNotificationBanner',
                '#cookieConsentBanner',
                '#cookie-consent-banner',
                '.cookie-consent-banner',
                '.cookie-banner',
            ];
            selectors.forEach((sel) => {
                win.document.querySelectorAll(sel).forEach((el) => {
                    el.style.display = 'none';
                });
            });
            win.document.querySelectorAll('div, section, aside').forEach((el) => {
                if (el.textContent && el.textContent.includes('Cookies on Family Platform') && getComputedStyle(el).position === 'fixed') {
                    el.style.display = 'none';
                }
            });
            const arena = win.document.querySelector('section[aria-label$="Arena"]');
            if (arena) {
                const top = Math.max(0, arena.getBoundingClientRect().top + win.scrollY - 64);
                win.scrollTo(0, top);
            }
        });
        cy.wait(120);
    };

    const resetScrollTop = () => {
        cy.window().then((win) => {
            win.scrollTo(0, 0);
            const nav = win.document.querySelector('.brand-navbar-stitch');
            if (nav) nav.classList.remove('nav-hidden');
        });
        cy.wait(100);
    };

    const assertZeroOverflowAndAudit = (auditScript, label, { checkArenaRight = true } = {}) => {
        cy.window().then((win) => {
            const docWidth = win.document.documentElement.scrollWidth;
            const winWidth = win.innerWidth;
            expect(docWidth, `${label} scrollWidth (${docWidth}) <= innerWidth (${winWidth})`).to.be.lte(winWidth + 2);

            if (checkArenaRight) {
                const arena = win.document.querySelector('section[aria-label$="Arena"]');
                expect(arena, `${label} Arena card`).to.exist;
                const arenaRect = arena.getBoundingClientRect();
                expect(arenaRect.right, `${label} Arena right edge`).to.be.lte(winWidth + 1);
            }

            win.eval(auditScript);
            const report = win.runSovereignDomAudit();
            expect(
                report.violations,
                `${label} DOM audit violations: ${JSON.stringify(report.violations)}`
            ).to.have.length(0);
        });
    };

    const assertBoardItemsWithinViewport = (selector, label) => {
        cy.window().then((win) => {
            const winWidth = win.innerWidth;
            const els = Array.from(win.document.querySelectorAll(selector));
            expect(els.length, `${label} elements matched`).to.be.greaterThan(0);
            els.forEach((el, idx) => {
                const rect = el.getBoundingClientRect();
                expect(rect.left, `${label} #${idx} left`).to.be.gte(-1);
                expect(rect.right, `${label} #${idx} right`).to.be.lte(winWidth + 1);
            });
        });
    };

    const assertAcrossAllViewports = (auditScript, gameLabel, itemSelector) => {
        viewports.forEach((vp) => {
            cy.viewport(vp.width, vp.height);
            cy.wait(80);
            assertBoardItemsWithinViewport(itemSelector, `${vp.name} ${gameLabel}`);
            assertZeroOverflowAndAudit(auditScript, `${vp.name} ${gameLabel}`);
        });
        cy.viewport(390, 844);
        cy.wait(80);
    };

    const selectGame = (gameName, readySelector) => {
        cy.get('nav[aria-label="Select Family Arcade game"]')
            .contains('button', gameName)
            .then(($btn) => {
                $btn[0].scrollIntoView({ behavior: 'instant', block: 'center', inline: 'center' });
            })
            .click();
        cy.contains('Generating deterministic puzzle...', { timeout: 25000 }).should('not.exist');
        cy.get(readySelector, { timeout: 25000 }).should('be.visible');
    };

    const selectDifficulty = (diffLabel) => {
        cy.get('[role="group"][aria-label="Difficulty"]')
            .contains('button', diffLabel)
            .then(($btn) => {
                $btn[0].scrollIntoView({ behavior: 'instant', block: 'center', inline: 'center' });
            })
            .click();
        cy.contains('Generating deterministic puzzle...', { timeout: 25000 }).should('not.exist');
    };

    it('verifies all 7 games (including Hard grids), all 5 viewports, modals, Dark Mode & League Tables on /arcade', () => {
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.request({
            method: 'POST',
            url: '/api/test/clear-rate-limit',
            failOnStatusCode: false,
            headers: { 'X-Cypress-Test': 'true' },
        });

        const consoleErrors = [];
        cy.readFile('scripts/dom_audit.js').then((auditScript) => {
            cy.viewport(390, 844);
            cy.visit('/arcade?game=kinword', {
                onBeforeLoad(win) {
                    const origErr = win.console.error.bind(win.console);
                    win.console.error = (...args) => {
                        const msg = args.map((a) => String(a)).join(' ');
                        if (!msg.includes('WebGL')) {
                            consoleErrors.push(msg);
                        }
                        origErr(...args);
                    };
                },
            });

            // 1. KinWord across all 5 viewports in Light Mode
            cy.contains('Generating deterministic puzzle...', { timeout: 25000 }).should('not.exist');
            cy.get('[aria-label="KinWord 4 by 4 tiles"]', { timeout: 25000 }).should('be.visible');
            assertAcrossAllViewports(auditScript, 'KinWord', '[aria-label="KinWord 4 by 4 tiles"] button');

            // 2. Wordie across all 5 viewports + screenshot at 390x844
            selectGame('Wordie', '[aria-label="Wordie 5-letter grid"]');
            assertAcrossAllViewports(auditScript, 'Wordie', '[aria-label="Virtual on-screen keyboard"] button');
            hideTransientBannersAndFrameArena();
            cy.screenshot('arcade-mobile-390-wordie', { capture: 'viewport' });
            resetScrollTop();

            // 3. Cheeseboard Hard (8x8) across all 5 viewports + screenshot at 390x844
            selectGame('Cheeseboard', '[aria-label*="Cheeseboard"]');
            selectDifficulty('Hard');
            cy.get('[aria-label="Cheeseboard 8 by 8 territory grid"]', { timeout: 25000 }).should('be.visible');
            assertAcrossAllViewports(auditScript, 'Cheeseboard Hard 8x8', '[aria-label="Cheeseboard 8 by 8 territory grid"] button');
            hideTransientBannersAndFrameArena();
            cy.screenshot('arcade-mobile-390-cheeseboard-hard', { capture: 'viewport' });
            resetScrollTop();

            // 4. Minesweeper (8x8) across all 5 viewports in Light Mode
            selectGame('Minesweeper', '[aria-label*="Minesweeper"]');
            assertAcrossAllViewports(auditScript, 'Minesweeper 8x8', '[aria-label*="Minesweeper"] button');

            // 5. KinZip Hard (7x7) on 390x844 Mobile
            selectGame('KinZip', '[aria-label*="KinZip"]');
            selectDifficulty('Hard');
            cy.get('[aria-label="KinZip 7 by 7 puzzle grid"]', { timeout: 25000 }).should('be.visible');
            assertBoardItemsWithinViewport('[aria-label="KinZip 7 by 7 puzzle grid"] button', 'mobile KinZip 7x7 cells');
            assertZeroOverflowAndAudit(auditScript, 'mobile KinZip Hard');

            // 6. KinGrid Hard (6x6) on 390x844 Mobile
            selectGame('KinGrid', '[aria-label*="KinGrid"]');
            selectDifficulty('Hard');
            cy.get('[aria-label="KinGrid 6 by 6 deduction grid"]', { timeout: 25000 }).should('be.visible');
            assertBoardItemsWithinViewport('[aria-label="KinGrid 6 by 6 deduction grid"] button', 'mobile KinGrid 6x6 cells');
            assertZeroOverflowAndAudit(auditScript, 'mobile KinGrid Hard');

            // 7. KinPulse (3x3) on 390x844 Mobile
            selectGame('KinPulse', '[aria-label="KinPulse 3D kinetic memory pads"]');
            assertBoardItemsWithinViewport('[aria-label="KinPulse 3D kinetic memory pads"] button', 'mobile KinPulse pads');
            assertZeroOverflowAndAudit(auditScript, 'mobile KinPulse');

            // 8. How to Play Modal (Rules + How to Think)
            cy.contains('button', 'How to Play').click();
            cy.get('[role="dialog"]', { timeout: 5000 }).should('be.visible');
            assertZeroOverflowAndAudit(auditScript, 'mobile HowToPlay Rules');
            cy.get('[role="tab"]').contains('How to Think').click();
            assertZeroOverflowAndAudit(auditScript, 'mobile HowToPlay Strategy');
            cy.get('button[aria-label="Close How to Play guide"]').click();

            // 9. Share Modal
            cy.get('button[aria-label^="Share "]').click();
            cy.get('[role="dialog"]', { timeout: 5000 }).should('be.visible');
            assertZeroOverflowAndAudit(auditScript, 'mobile Share Modal');
            cy.get('button[aria-label="Close share dialog"]').click();

            // 10. League Tables scroll clearance
            cy.contains('h2', 'Family League Tables').scrollIntoView().should('be.visible');
            assertZeroOverflowAndAudit(auditScript, 'mobile League Tables');
            resetScrollTop();

            // 11. Dark Mode Verification & Screenshots (KinWord Dark & Minesweeper Dark on 390x844 Mobile)
            cy.window().then((win) => {
                win.document.documentElement.classList.add('dark-mode');
                win.document.body.classList.add('dark-mode');
                win.document.documentElement.setAttribute('data-bs-theme', 'dark');
            });
            cy.wait(300);

            selectGame('KinWord', '[aria-label="KinWord 4 by 4 tiles"]');
            assertBoardItemsWithinViewport('[aria-label="KinWord 4 by 4 tiles"] button', 'mobile KinWord dark tiles');
            assertZeroOverflowAndAudit(auditScript, 'mobile KinWord dark');
            hideTransientBannersAndFrameArena();
            cy.screenshot('arcade-mobile-390-kinword-dark', { capture: 'viewport' });
            resetScrollTop();

            selectGame('Minesweeper', '[aria-label*="Minesweeper"]');
            cy.get('[aria-label*="Minesweeper"] button').first().click();
            cy.wait(150);
            assertBoardItemsWithinViewport('[aria-label*="Minesweeper"] button', 'mobile Minesweeper 8x8 dark');
            assertZeroOverflowAndAudit(auditScript, 'mobile Minesweeper dark');
            hideTransientBannersAndFrameArena();
            cy.screenshot('arcade-mobile-390-minesweeper-dark', { capture: 'viewport' });
            resetScrollTop();

            cy.then(() => {
                expect(consoleErrors, 'console errors').to.have.length(0);
            });
        });
    });

    it('renders public guest challenge pages (/play/kinword and /play/wordie) cleanly on 390x844 Mobile with 0 overflow', () => {
        cy.clearCookies();
        cy.clearAllSessionStorage();
        cy.readFile('scripts/dom_audit.js').then((auditScript) => {
            cy.viewport(390, 844);

            cy.visit('/play/kinword');
            cy.get('[aria-label="KinWord 4 by 4 tiles"]', { timeout: 15000 }).should('be.visible');
            hideTransientBannersAndFrameArena();
            assertBoardItemsWithinViewport('[aria-label="KinWord 4 by 4 tiles"] button', 'guest KinWord tiles');
            assertZeroOverflowAndAudit(auditScript, 'guest /play/kinword');
            cy.screenshot('arcade-guest-mobile-390-kinword', { capture: 'viewport' });

            cy.visit('/play/wordie');
            cy.get('[aria-label="Wordie 5-letter grid"]', { timeout: 15000 }).should('be.visible');
            assertBoardItemsWithinViewport('[aria-label="Virtual on-screen keyboard"] button', 'guest Wordie keys');
            assertZeroOverflowAndAudit(auditScript, 'guest /play/wordie');
        });
    });
});
