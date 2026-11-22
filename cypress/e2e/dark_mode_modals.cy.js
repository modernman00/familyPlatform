import { loginFully } from '../support/login';

describe('Dark & Light Mode Modal Contrast & Visual Audit', () => {
    Cypress.on('uncaught:exception', () => false);

    function parseRgb(colorStr) {
        if (!colorStr) return null;
        const m = colorStr.match(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*([\d.]+))?\s*\)/i);
        if (m) {
            return {
                r: parseInt(m[1], 10),
                g: parseInt(m[2], 10),
                b: parseInt(m[3], 10),
                a: m[4] !== undefined ? parseFloat(m[4]) : 1,
            };
        }
        return null;
    }

    function relLuminance(rgb) {
        const srgb = [rgb.r / 255, rgb.g / 255, rgb.b / 255].map((c) =>
            c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4)
        );
        return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
    }

    function contrastRatio(lum1, lum2) {
        const brightest = Math.max(lum1, lum2);
        const darkest = Math.min(lum1, lum2);
        return (brightest + 0.05) / (darkest + 0.05);
    }

    function parseGradFirstRgb(bgImage) {
        if (!bgImage || bgImage === 'none') return null;
        const m = bgImage.match(/rgba?\([^)]+\)/i);
        return m ? parseRgb(m[0]) : null;
    }

    function getEffectiveBg(win, el) {
        let cur = el;
        while (cur && cur !== win.document.documentElement) {
            const st = win.getComputedStyle(cur);
            const parsed = parseRgb(st.backgroundColor);
            if (parsed && parsed.a >= 0.5) return parsed;
            if (st.backgroundImage && st.backgroundImage !== 'none' && st.backgroundClip !== 'text' && st.webkitBackgroundClip !== 'text') {
                const grad = parseGradFirstRgb(st.backgroundImage);
                if (grad && grad.a >= 0.5) return grad;
                return null;
            }
            cur = cur.parentElement;
        }
        const bodyBg = parseRgb(win.getComputedStyle(win.document.body).backgroundColor);
        return bodyBg && bodyBg.a >= 0.5 ? bodyBg : { r: 255, g: 255, b: 255, a: 1 };
    }

    function auditModalElements(win, modalSelector) {
        const root = win.document.querySelector(modalSelector);
        expect(root, `${modalSelector} exists`).to.exist;

        const isVis = (el) => {
            const st = win.getComputedStyle(el);
            if (st.display === 'none' || st.visibility === 'hidden' || parseFloat(st.opacity) === 0) return false;
            const r = el.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        };

        const issues = [];
        const nodes = Array.from(root.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,a,button,label,small,strong,div'));
        for (const el of nodes) {
            if (!isVis(el)) continue;
            // Only audit leaf-ish text elements so wrapper divs with child spans aren't double-counted
            const directText = Array.from(el.childNodes)
                .filter((n) => n.nodeType === 3)
                .map((n) => n.textContent.trim())
                .join(' ')
                .trim();
            if (!directText) continue;

            const st = win.getComputedStyle(el);
            const fg = parseRgb(st.color);
            const bg = getEffectiveBg(win, el);
            if (!fg || fg.a < 0.2 || !bg) continue;

            const fgLum = relLuminance(fg);
            const bgLum = relLuminance(bg);
            const ratio = contrastRatio(fgLum, bgLum);
            const isRedBg = bg.r >= 140 && bg.g <= 65 && bg.b <= 65 && bg.r - Math.max(bg.g, bg.b) >= 80;
            if (isRedBg && fgLum < 0.12) {
                issues.push(`BLACK_ON_RED: "${directText.slice(0, 30)}" fg=${st.color} bg=rgb(${bg.r},${bg.g},${bg.b})`);
            }
            if (ratio < 3.0) {
                issues.push(`LOW_CONTRAST(${ratio.toFixed(2)}): "${directText.slice(0, 30)}" fg=${st.color} bg=rgb(${bg.r},${bg.g},${bg.b}) tag=${el.tagName}.${el.className}`);
            }
        }
        return issues;
    }

    let domAuditSource = '';
    before(() => {
        cy.readFile('scripts/dom_audit.js', { timeout: 15000 }).then((src) => {
            domAuditSource = src;
        });
    });

    function runFullAudit(modalSelector) {
        runPageDomAudit(modalSelector);
    }

    function runPageDomAudit(modalSelector) {
        cy.window().then((win) => {
            win.eval(domAuditSource);
            const report = win.runSovereignDomAudit();
            const criticalViolations = report.violations.filter((v) => v.rule !== 'UNLABELLED_ICON_BUTTON');
            expect(criticalViolations, `Critical DOM Audit violations: ${JSON.stringify(criticalViolations)}`).to.deep.eq([]);

            if (modalSelector) {
                const modalIssues = auditModalElements(win, modalSelector);
                expect(modalIssues, `Modal contrast issues in ${modalSelector}`).to.deep.eq([]);

                const modalIconControls = Array.from(
                    win.document.querySelectorAll(`${modalSelector} button, ${modalSelector} a[href], ${modalSelector} [role="button"], ${modalSelector} .btn`)
                );
                for (const ctrl of modalIconControls) {
                    const st = win.getComputedStyle(ctrl);
                    if (st.display === 'none' || st.visibility === 'hidden' || parseFloat(st.opacity) === 0) continue;
                    const r = ctrl.getBoundingClientRect();
                    if (r.width === 0 || r.height === 0) continue;
                    const txt = (ctrl.innerText || ctrl.value || '').trim();
                    if (txt.length > 0) continue;
                    const hasLabel = Boolean(
                        (ctrl.getAttribute('aria-label') || '').trim() ||
                        (ctrl.getAttribute('aria-labelledby') || '').trim() ||
                        (ctrl.getAttribute('title') || '').trim()
                    );
                    expect(hasLabel, `Icon button inside ${modalSelector} must have aria-label/title`).to.eq(true);
                }
            }
        });
    }

    function setDarkMode(enabled) {
        cy.window().then((win) => {
            try {
                win.localStorage.setItem('familyPlatform_theme', enabled ? 'dark' : 'light');
            } catch (e) {}

            if (win.Alpine?.store && win.Alpine.store('theme')) {
                win.Alpine.store('theme').isDark = enabled;
                win.Alpine.store('theme').apply();
            }

            if (enabled) {
                win.document.body.classList.add('dark-mode');
                win.document.documentElement.classList.add('dark');
            } else {
                win.document.body.classList.remove('dark-mode', 'dark-theme');
                win.document.documentElement.classList.remove('dark', 'dark-mode');
                win.document.documentElement.removeAttribute('data-bs-theme');
                win.document.body.removeAttribute('data-bs-theme');
            }
        });
    }

    function openBootstrapModal(modalSelector) {
        cy.window().then((win) => {
            const el = win.document.querySelector(modalSelector);
            expect(el, `${modalSelector} found`).to.exist;
            win.bootstrap.Modal.getOrCreateInstance(el).show();
        });
        cy.get(`${modalSelector}.show`, { timeout: 10000 }).should('be.visible');
        cy.wait(350);
    }

    function closeBootstrapModal(modalSelector) {
        cy.window().then((win) => {
            const el = win.document.querySelector(modalSelector);
            if (el && win.bootstrap?.Modal) {
                win.bootstrap.Modal.getInstance(el)?.hide();
            }
        });
        cy.wait(250);
    }

    let consoleErrors = [];

    beforeEach(() => {
        consoleErrors = [];
        loginFully();
        cy.window().then((win) => {
            const origErr = win.console.error.bind(win.console);
            win.console.error = (...args) => {
                consoleErrors.push(args.map(String).join(' '));
                origErr(...args);
            };
        });
    });

    it('renders #inviteFamilyModal cleanly in Dark Mode across Mobile, iPad, and Desktop viewports', () => {
        setDarkMode(true);

        // 1. Mobile (390x844) — matches user screenshot
        cy.viewport(390, 844);
        cy.get('button[data-bs-target="#inviteFamilyModal"], a[data-bs-target="#inviteFamilyModal"]').first().click({ force: true });
        cy.get('#inviteFamilyModal.show').should('be.visible');
        cy.wait(350);

        // Verify dark surfaces, crisp light text, and non-italic typography
        cy.window().then((win) => {
            const checks = [
                '.ifm-header',
                '.ifm-tabs-wrap',
                '.ifm-tabs-track',
                '.ifm-auto-badge',
                '.ifm-lineage-card',
                '#inviteFamilyModal .kinship-chip:not(.active)',
                '.ifm-preview-card',
                '.ifm-sec-tile',
                '.ifm-footer',
                '.ifm-done-btn',
            ];
            for (const sel of checks) {
                const el = win.document.querySelector(sel);
                expect(el, sel).to.exist;
                const bg = getEffectiveBg(win, el);
                expect(relLuminance(bg), `${sel} background should be dark (lum < 0.12)`).to.be.lessThan(0.12);
            }

            const textChecks = [
                '#inviteFamilyModalLabel',
                '#modalFamilyLineageTypeLabel',
                '.ifm-lineage-title',
                '#inviteFamilyModal .kinship-chip:not(.active)',
                '#inviteFamilyModal .kinship-chip.active',
                '.ifm-preview-card',
                '.ifm-sec-tile',
                '.ifm-footer-note',
                '.ifm-done-btn',
            ];
            for (const sel of textChecks) {
                const el = win.document.querySelector(sel);
                expect(el, sel).to.exist;
                const st = win.getComputedStyle(el);
                const fg = parseRgb(st.color);
                const bg = getEffectiveBg(win, el);
                const ratio = contrastRatio(relLuminance(fg), relLuminance(bg));
                expect(ratio, `${sel} WCAG AA contrast >= 4.5:1`).to.be.at.least(4.5);
            }

            for (const sel of ['#inviteTabFamily', '#inviteTabFriends', '.ifm-footer-note']) {
                const el = win.document.querySelector(sel);
                expect(win.getComputedStyle(el).fontStyle, `${sel} must not be italic`).to.eq('normal');
            }
        });

        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-dark-mobile-tab1-top', { capture: 'viewport' });

        // Select Mum chip & scroll down inside modal-body to inspect preview, CTA, secondary tiles & footer
        cy.get('#inviteFamilyModal .kinship-chip[data-role="Mum"]').click();
        cy.get('#inviteFamilyModal .modal-body').scrollTo('bottom');
        cy.wait(150);
        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-dark-mobile-tab1-bottom', { capture: 'viewport' });

        // Switch to Friends & In-Laws tab
        cy.get('#inviteTabFriends').click();
        cy.get('#inviteTabFriendsPane.active').should('be.visible');
        cy.wait(250);
        cy.window().then((win) => {
            const copyBtn = win.document.getElementById('btnCopyShareLink');
            const bg = getEffectiveBg(win, copyBtn);
            const fg = parseRgb(win.getComputedStyle(copyBtn).color);
            const ratio = contrastRatio(relLuminance(fg), relLuminance(bg));
            expect(ratio, '#btnCopyShareLink dark contrast >= 4.5:1').to.be.at.least(4.5);
        });
        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-dark-mobile-tab2', { capture: 'viewport' });

        // 2. iPad Portrait (768x1024) & iPad Landscape (1024x768)
        cy.get('#inviteTabFamily').click();
        cy.wait(200);
        cy.viewport(768, 1024);
        cy.wait(150);
        runFullAudit('#inviteFamilyModal');

        cy.viewport(1024, 768);
        cy.wait(150);
        runFullAudit('#inviteFamilyModal');

        // 3. Desktop (1440x900)
        cy.viewport(1440, 900);
        cy.wait(200);
        cy.window().then((win) => {
            const tiles = Array.from(win.document.querySelectorAll('#inviteFamilyModal .ifm-sec-tile'));
            expect(tiles.length).to.be.greaterThan(0);
            for (const tile of tiles) {
                const label = tile.querySelector('span');
                if (!label) continue;
                const tileRect = tile.getBoundingClientRect();
                const labelRect = label.getBoundingClientRect();
                expect(label.scrollWidth, `No clipping in "${label.textContent.trim()}"`).to.be.at.most(label.clientWidth + 1);
                expect(labelRect.right, `Right edge fits for "${label.textContent.trim()}"`).to.be.at.most(tileRect.right + 1);
                expect(labelRect.left, `Left edge fits for "${label.textContent.trim()}"`).to.be.at.least(tileRect.left - 1);
            }
        });
        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-dark-desktop-tab1', { capture: 'viewport' });

        cy.get('#inviteTabFriends').click();
        cy.wait(250);
        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-dark-desktop-tab2', { capture: 'viewport' });

        expect(consoleErrors, 'No console errors in Dark Mode #inviteFamilyModal').to.deep.eq([]);
    });

    it('renders other remediated modals cleanly in Dark Mode', () => {
        setDarkMode(true);
        cy.viewport(1440, 900);

        // Time Capsule Modal (both tabs)
        openBootstrapModal('#timeCapsuleModal');
        runFullAudit('#timeCapsuleModal');
        cy.screenshot('timecapsule-dark-desktop', { capture: 'viewport' });
        cy.get('#tabBtnCreateVault').click();
        cy.wait(150);
        runFullAudit('#timeCapsuleModal');
        closeBootstrapModal('#timeCapsuleModal');

        // Create Reel Modal
        openBootstrapModal('#createReelModal');
        runFullAudit('#createReelModal');
        cy.screenshot('createreel-dark-desktop', { capture: 'viewport' });
        closeBootstrapModal('#createReelModal');

        // Upcoming Events Modal
        openBootstrapModal('#allUpcomingEventsModal');
        cy.wait(400);
        runFullAudit('#allUpcomingEventsModal');
        cy.screenshot('events-dark-desktop', { capture: 'viewport' });
        closeBootstrapModal('#allUpcomingEventsModal');

        // Edit Profile Modal (including Married amber wedding card)
        openBootstrapModal('#editProfileModal');
        cy.get('#marital_status').select('Married', { force: true });
        cy.wait(150);
        runFullAudit('#editProfileModal');
        cy.screenshot('editprofile-dark-desktop', { capture: 'viewport' });
        closeBootstrapModal('#editProfileModal');

        // Lineage Quest & Family Flyer Modals
        openBootstrapModal('#lineageQuestModal');
        runFullAudit('#lineageQuestModal');
        closeBootstrapModal('#lineageQuestModal');

        openBootstrapModal('#familyFlyerModal');
        runFullAudit('#familyFlyerModal');
        closeBootstrapModal('#familyFlyerModal');

        expect(consoleErrors, 'No console errors across remediated Dark Mode modals').to.deep.eq([]);
    });

    it('preserves clean Light Mode styling on #inviteFamilyModal', () => {
        setDarkMode(false);

        cy.viewport(390, 844);
        cy.get('button[data-bs-target="#inviteFamilyModal"], a[data-bs-target="#inviteFamilyModal"]').first().click({ force: true });
        cy.get('#inviteFamilyModal.show').should('be.visible');
        cy.wait(350);

        cy.window().then((win) => {
            const headerBg = getEffectiveBg(win, win.document.querySelector('#inviteFamilyModal .ifm-header'));
            expect(relLuminance(headerBg), 'Light header background luminance > 0.8').to.be.greaterThan(0.8);

            const label = win.document.getElementById('modalFamilyLineageTypeLabel');
            const fg = parseRgb(win.getComputedStyle(label).color);
            const ratio = contrastRatio(relLuminance(fg), relLuminance(headerBg));
            expect(ratio, '#modalFamilyLineageTypeLabel light contrast >= 4.5:1').to.be.at.least(4.5);

            for (const sel of ['#inviteTabFamily', '#inviteTabFriends', '.ifm-footer-note']) {
                expect(win.getComputedStyle(win.document.querySelector(sel)).fontStyle).to.eq('normal');
            }
        });

        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-light-mobile-tab1', { capture: 'viewport' });

        cy.viewport(1440, 900);
        cy.wait(200);
        runFullAudit('#inviteFamilyModal');
        cy.screenshot('invite-light-desktop-tab1', { capture: 'viewport' });

        expect(consoleErrors, 'No console errors in Light Mode #inviteFamilyModal').to.deep.eq([]);
    });
});
