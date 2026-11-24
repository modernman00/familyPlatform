import { loginFully } from '../support/login';

describe('Preact Feed Island (Phases 1–4) Browser & DOM Audit', () => {
    Cypress.on('uncaught:exception', () => false);

    const runAuditOnPage = (assertionFn) => {
        cy.readFile('scripts/dom_audit.js', { timeout: 10000 }).then((src) => {
            cy.window().then((win) => {
                if (typeof win.runSovereignDomAudit !== 'function') {
                    win.eval(src);
                }
                expect(typeof win.runSovereignDomAudit).to.eq('function');
                const report = win.runSovereignDomAudit();
                assertionFn(report);
            });
        });
    };

    it('Live E2E Mutation Cycle: classic #postModal create -> Preact edit -> comment -> delete without page reload', () => {
        const uniqueText = `Cypress Preact Classic E2E ${Date.now()}`;
        const editedText = `${uniqueText} - Edited`;
        const commentText = `Cypress comment ${Date.now()}`;

        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });

        cy.visit('/profilePage', { timeout: 120000 });
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);

        // Dismiss cookie banner if present so it does not cover feed actions
        cy.get('body').then(($body) => {
            const acceptBtn = $body.find('button:contains("Accept all")');
            if (acceptBtn.length > 0) {
                cy.wrap(acceptBtn.first()).click({ force: true });
            }
        });

        // 1. Open classic #postModal via #openPostModalTrigger and create a post
        cy.get('#openPostModalTrigger .post-composer__cta').click({ force: true });
        cy.get('#postModal', { timeout: 15000 }).should('be.visible');
        cy.get('#postMessageEditor').click({ force: true }).invoke('text', uniqueText).trigger('input', { force: true });
        cy.get('#postMessage').invoke('val', uniqueText).trigger('input', { force: true });
        cy.get('#submitPost').click({ force: true });
        cy.get('#postModal', { timeout: 65000 }).should('not.be.visible');

        // Verify composer state is 100% flushed after post submission (zero bleed into next post)
        cy.get('#postMessageEditor').should('have.text', '');
        cy.get('#postMessage').should('have.value', '');
        cy.get('#imageUpload').should('have.value', '');
        cy.get('#attachedVideoUrl').should('have.value', '');
        cy.get('#attachedVideoUrls').should('have.value', '');
        cy.get('#composerLinkPreviewContainer').should('have.class', 'd-none');
        cy.get('#imagePreviewContainer').should('have.class', 'd-none');

        cy.contains('#feed-root article.feed-post-card', uniqueText, { timeout: 65000 })
            .should('be.visible')
            .and(($el) => {
                expect($el.attr('id')).to.match(/^post\d+$/);
            })
            .then(($article) => {
                const postId = $article.attr('id');

                // 2. Edit post via kebab menu -> classic #postModal
                cy.get(`#${postId}`).scrollIntoView({ block: 'center' });
                cy.get(`#${postId} button[aria-label="Post options"]`).click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Edit Post').click({ force: true });
                cy.get('#postModal', { timeout: 15000 }).should('be.visible');
                cy.get('#editPostNo').should('have.value', postId.replace(/^post/, ''));
                cy.get('#postMessageEditor').click({ force: true }).invoke('text', editedText).trigger('input', { force: true });
                cy.get('#postMessage').invoke('val', editedText).trigger('input', { force: true });
                cy.get('#submitPost').click({ force: true });
                cy.get('#postModal', { timeout: 50000 }).should('not.be.visible');
                cy.get(`#${postId}`, { timeout: 50000 }).should('contain.text', editedText);

                // 3. Add comment via CommentThread
                cy.get(`#${postId} button[aria-label="Comment on this post"]`).click({ force: true });
                cy.get(`#${postId} input[aria-label="Write a comment"]`).type(commentText, { force: true });
                cy.get(`#${postId} button[aria-label="Send"]`).click({ force: true });
                cy.get(`#${postId} input[aria-label="Write a comment"]`, { timeout: 50000 }).should('have.value', '');
                cy.get(`#${postId}`, { timeout: 50000 }).should('contain.text', commentText);

                // 4. Delete post via kebab menu + confirmation
                cy.get(`#${postId} button[aria-label="Post options"]`).click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Delete Post').click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Confirm Delete').click({ force: true });
                cy.get(`#${postId}`, { timeout: 50000 }).should('not.exist');
            });
    });

    it('Desktop (1440x900): restores classic Hero Composer, mounts Preact feed & sidebars with real posts, and passes DOM Audit', () => {
        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });

        cy.get('#feed-root', { timeout: 30000 }).should('exist');
        cy.get('#kin-requests-root', { timeout: 30000 }).should('exist');
        cy.get('#upcoming-events-root', { timeout: 30000 }).should('exist');
        cy.get('#upcoming-events-root #upcomingEventsSection', { timeout: 30000 }).should('exist').and('contain.text', 'Upcoming Events');
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');

        // Classic Hero Composer with quick-action pills
        cy.get('#openPostModalTrigger').should('be.visible');
        cy.get('#openPostModalTrigger .composer-pill-btn').should('have.length', 7);
        cy.get('[role="tab"]').contains('All Family Memories').should('be.visible');
        cy.get('[role="tab"]').contains('Tagged With You').should('be.visible');

        // Real feed posts must load (not empty state)
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);
        cy.get('#feed-root [role="alert"]').should('not.exist');

        cy.screenshot('preact-feed-desktop-verified', { capture: 'viewport' });
        cy.get('#feed-root article.feed-post-card').first().scrollIntoView({ block: 'center' });
        cy.screenshot('preact-feed-posts-verified', { capture: 'viewport' });

        runAuditOnPage((report) => {
            const criticalViolations = (report.violations || []).filter((v) =>
                ['HORIZONTAL_OVERFLOW', 'MISSING_CSRF_DOM', 'BLACK_ON_RED', 'GHOST_TEXT'].includes(v.rule)
            );
            expect(criticalViolations, JSON.stringify(criticalViolations)).to.have.length(0);
            expect(report.horizontalOverflow, 'No horizontal overflow on Desktop').to.not.be.true;
        });
    });

    it('Mobile (390x844): restores classic mobile Hero Composer, loads real posts with zero horizontal overflow, and passes DOM Audit', () => {
        cy.viewport(390, 844);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });

        cy.get('#feed-root', { timeout: 30000 }).should('exist');
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#openPostModalTriggerMobile').should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);
        cy.get('#feed-root [role="alert"]').should('not.exist');

        cy.screenshot('preact-feed-mobile-verified', { capture: 'viewport' });

        runAuditOnPage((report) => {
            const criticalViolations = (report.violations || []).filter((v) =>
                ['HORIZONTAL_OVERFLOW', 'MISSING_CSRF_DOM', 'BLACK_ON_RED', 'GHOST_TEXT'].includes(v.rule)
            );
            expect(criticalViolations, JSON.stringify(criticalViolations)).to.have.length(0);
            expect(report.horizontalOverflow, 'No horizontal overflow on Mobile').to.not.be.true;
        });
    });

    it('Legacy path retired (?v=legacy): still renders Preact #feed-root instead of legacy profileFeed', () => {
        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/member/ProfilePage?v=legacy', { timeout: 120000 });

        cy.get('#feed-root', { timeout: 30000 }).should('exist');
        cy.get('[x-data*="profileFeed"]').should('not.exist');
    });

    it('Comment Composer Visual & Dark/Light Mode Audit (Mobile, iPad, Desktop): zero inner box, normal placeholder, 44px controls, and DOM audit pass', () => {
        const criticalRules = [
            'HORIZONTAL_OVERFLOW',
            'MISSING_CSRF_DOM',
            'BLACK_ON_RED_SURFACE',
            'GHOST_TYPOGRAPHY_CONTRAST',
            'UNLABELLED_ICON_BUTTON',
        ];

        const assertNoCriticalAuditViolations = (label) => {
            runAuditOnPage((report) => {
                const criticalViolations = (report.violations || []).filter((v) =>
                    criticalRules.includes(v.rule)
                );
                expect(criticalViolations, `${label}: ${JSON.stringify(criticalViolations)}`).to.have.length(0);
            });
            cy.window().then((win) => {
                expect(
                    win.document.documentElement.scrollWidth,
                    `${label}: zero horizontal overflow`
                ).to.be.at.most(win.innerWidth + 2);
            });
        };

        const assertComposerGeometry = (label, { dark, mobile }) => {
            cy.get('#feed-root article.feed-post-card').first().within(() => {
                cy.get('form.comment-composer').should('be.visible');
                cy.get('.comment-composer__field').should(($field) => {
                    const bg = getComputedStyle($field[0]).backgroundColor;
                    expect(bg, `${label} field bg`).to.eq(dark ? 'rgb(31, 32, 36)' : 'rgb(243, 244, 246)');
                });
                cy.get('input.comment-composer__input').should(($inp) => {
                    const el = $inp[0];
                    const cs = getComputedStyle(el);
                    const ph = getComputedStyle(el, '::placeholder');
                    expect(cs.backgroundColor, `${label} input bg transparent`).to.eq('rgba(0, 0, 0, 0)');
                    expect(cs.borderTopWidth, `${label} input border 0`).to.eq('0px');
                    expect(ph.textTransform, `${label} placeholder text-transform none`).to.eq('none');
                });
                cy.get('.comment-composer__tools .comment-composer__tool').should(($tools) => {
                    expect($tools.length, `${label} 3 tools`).to.eq(3);
                    $tools.each((_, btn) => {
                        const rect = btn.getBoundingClientRect();
                        expect(rect.width, `${label} tool width >= 44`).to.be.at.least(44);
                        expect(rect.height, `${label} tool height >= 44`).to.be.at.least(44);
                    });
                });
                cy.get('button.comment-composer__send').should(($send) => {
                    const rect = $send[0].getBoundingClientRect();
                    expect(rect.width, `${label} send width >= 44`).to.be.at.least(44);
                    expect(rect.height, `${label} send height >= 44`).to.be.at.least(44);
                });
                if (mobile) {
                    cy.get('.comments-section').should(($sec) => {
                        const cs = getComputedStyle($sec[0]);
                        expect(cs.paddingLeft, `${label} mobile comments paddingLeft`).to.eq('12px');
                        expect(cs.paddingRight, `${label} mobile comments paddingRight`).to.eq('12px');
                    });
                }
            });
        };

        const setThemeMode = (dark) => {
            cy.window().then((win) => {
                try {
                    win.localStorage.setItem('familyPlatform_theme', dark ? 'dark' : 'light');
                } catch { /* ignore */ }
                if (win.Alpine && typeof win.Alpine.store === 'function') {
                    const themeStore = win.Alpine.store('theme');
                    if (themeStore) {
                        themeStore.isDark = Boolean(dark);
                        if (typeof themeStore.apply === 'function') {
                            themeStore.apply();
                        }
                    }
                }
                const doc = win.document;
                if (dark) {
                    doc.body.classList.add('dark-mode');
                    doc.documentElement.classList.add('dark');
                    doc.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    doc.body.classList.remove('dark-mode', 'dark-theme');
                    doc.documentElement.classList.remove('dark', 'dark-mode');
                    doc.documentElement.removeAttribute('data-bs-theme');
                }
            });
        };

        const centerFirstComposer = () => {
            cy.get('#feed-root article.feed-post-card').first().find('form.comment-composer').then(($form) => {
                $form[0].scrollIntoView({ block: 'center', behavior: 'instant' });
            });
            cy.wait(150);
        };

        cy.viewport(390, 844);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });

        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);

        cy.get('body').then(($body) => {
            const acceptBtn = $body.find('button:contains("Accept all")');
            if (acceptBtn.length > 0) {
                cy.wrap(acceptBtn.first()).click({ force: true });
            }
            const pwaClose = $body.find('#pwa-install-banner .pwa-btn-close:visible');
            if (pwaClose.length > 0) {
                cy.wrap(pwaClose.first()).click({ force: true });
            }
        });

        // 1. Mobile (390x844) Dark Mode
        setThemeMode(true);

        cy.get('#feed-root article.feed-post-card').first().within(() => {
            cy.get('button[aria-label="Comment on this post"]').click({ force: true });
            cy.get('form.comment-composer').should('be.visible');
        });
        centerFirstComposer();

        assertComposerGeometry('Mobile Dark', { dark: true, mobile: true });
        cy.screenshot('comment-box-mobile-dark', { capture: 'viewport' });

        // Test 3-char and 80-char input -> active crimson Send button with white icon
        cy.get('#feed-root article.feed-post-card').first().within(() => {
            cy.get('input.comment-composer__input')
                .type('Hi!', { force: true })
                .clear({ force: true })
                .type('This is a wonderful family memory that everyone will cherish for years to come!', { force: true });
            cy.get('button.comment-composer__send')
                .should('have.class', 'is-active')
                .and('not.be.disabled')
                .and(($send) => {
                    expect(getComputedStyle($send[0]).color).to.eq('rgb(255, 255, 255)');
                });
        });
        centerFirstComposer();
        cy.screenshot('comment-box-mobile-dark-active', { capture: 'viewport' });

        cy.get('#feed-root article.feed-post-card').first().within(() => {
            cy.get('input.comment-composer__input').clear({ force: true });
            cy.get('button[aria-label="Add emoji"]').click({ force: true });
        });
        cy.get('[data-testid="emoji-gif-picker"]', { timeout: 15000 }).should('be.visible');
        cy.window().then((win) => {
            expect(win.document.documentElement.scrollWidth).to.be.at.most(win.innerWidth + 2);
        });
        cy.get('[data-testid="emoji-gif-picker"] button[aria-label="Close picker"]').click({ force: true });
        cy.get('[data-testid="emoji-gif-picker"]').should('not.exist');

        cy.get('#feed-root').then(($root) => {
            const $bubbles = $root.find('.comment-bubble-box:visible');
            if ($bubbles.length > 0) {
                expect(getComputedStyle($bubbles[0]).backgroundColor).to.eq('rgb(31, 32, 36)');
            }
        });
        assertNoCriticalAuditViolations('Mobile Dark');

        // 2. Mobile (390x844) Light Mode
        setThemeMode(false);
        centerFirstComposer();
        assertComposerGeometry('Mobile Light', { dark: false, mobile: true });
        cy.screenshot('comment-box-mobile-light', { capture: 'viewport' });
        assertNoCriticalAuditViolations('Mobile Light');

        // 3. iPad Portrait (768x1024), iPad Landscape (1024x768), and Desktop (1440x900) Dark Mode
        setThemeMode(true);

        cy.viewport(768, 1024);
        centerFirstComposer();
        assertComposerGeometry('iPad Portrait Dark', { dark: true, mobile: false });
        assertNoCriticalAuditViolations('iPad Portrait Dark');

        cy.viewport(1024, 768);
        centerFirstComposer();
        assertComposerGeometry('iPad Landscape Dark', { dark: true, mobile: false });
        assertNoCriticalAuditViolations('iPad Landscape Dark');

        cy.viewport(1440, 900);
        centerFirstComposer();
        assertComposerGeometry('Desktop Dark', { dark: true, mobile: false });
        cy.screenshot('comment-box-desktop-dark', { capture: 'viewport' });
        assertNoCriticalAuditViolations('Desktop Dark');
    });
});
