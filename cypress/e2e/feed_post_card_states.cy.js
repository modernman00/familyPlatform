import { loginFully } from '../support/login';

describe('Feed Post Card 4-State & DOM Audit Regression', () => {
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

    it('1. Happy Path (Desktop 1440x900): media stage renders >= 220px and DOM audit passes', () => {
        cy.viewport(1440, 900);
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 12000 }).should('be.visible');

        // Wait for initial feed posts to settle
        cy.get('.feed-post-card', { timeout: 12000 }).should('have.length.greaterThan', 0);

        // Verify any rendered .post-media-stage is >= 220px tall
        cy.get('body').then(($body) => {
            if ($body.find('.post-media-stage:visible').length > 0) {
                cy.get('.post-media-stage:visible').each(($stage) => {
                    expect($stage[0].getBoundingClientRect().height).to.be.at.least(220);
                });
            }
        });

        runAuditOnPage((report) => {
            const mediaViolations = report.violations.filter(
                (v) => v.rule === 'BROKEN_IMAGE' || v.rule === 'COLLAPSED_MEDIA_STAGE' || v.rule === 'PILL_BAR_CLIPPED'
            );
            expect(mediaViolations, JSON.stringify(mediaViolations)).to.have.length(0);
        });
    });

    it('2. Simulated 404 Image Fallback: stage retains >= 220px height and shows fallback', () => {
        cy.viewport(1440, 900);
        loginFully();
        cy.visit('/profilePage');
        cy.get('.feed-post-card', { timeout: 12000 }).should('have.length.greaterThan', 0);

        cy.intercept('GET', '**/resources/images/post/missing-e2e-404-test.jpg*', {
            statusCode: 404,
            body: 'Not Found',
        }).as('missingImg');

        cy.window().then((win) => {
            const feedEl = win.document.querySelector('[x-data*="profileFeed"]');
            const feedData = feedEl && win.Alpine ? win.Alpine.$data(feedEl) : null;
            expect(feedData, 'Alpine profileFeed data').to.exist;

            feedData.posts.unshift({
                post_no: 9999991,
                id: feedData.currentUserId || 'test_user',
                fullName: 'E2E Fallback Tester',
                profileImg: '/public/avatar/avatarM.png',
                postMessage: 'Testing 404 fallback',
                displayMessage: 'Testing 404 fallback',
                post_time: new Date().toISOString(),
                images: ['missing-e2e-404-test.jpg'],
                videos: [],
                comments: [],
                post_likes: 0,
                views_count: 1,
            });
        });

        cy.get('#post9999991 .post-media-stage', { timeout: 8000 }).scrollIntoView().should(($stage) => {
            expect($stage[0].getBoundingClientRect().height).to.be.at.least(220);
        });
        cy.get('#post9999991 img.post-media-first-img').then(($img) => {
            $img.attr('loading', 'eager');
        });
        cy.get('#post9999991 .post-media-stage__fallback', { timeout: 10000 }).should('be.visible').and('contain.text', 'Photo unavailable');

        runAuditOnPage((report) => {
            const broken = report.violations.filter(
                (v) => v.rule === 'BROKEN_IMAGE' || v.rule === 'COLLAPSED_MEDIA_STAGE'
            );
            expect(broken, JSON.stringify(broken)).to.have.length(0);
        });
    });

    it('3. Link-Only Post: suppresses 3D stage converter & orphaned translate option', () => {
        cy.viewport(1440, 900);
        loginFully();
        cy.visit('/profilePage');
        cy.get('.feed-post-card', { timeout: 12000 }).should('have.length.greaterThan', 0);

        cy.window().then((win) => {
            const feedEl = win.document.querySelector('[x-data*="profileFeed"]');
            const feedData = feedEl && win.Alpine ? win.Alpine.$data(feedEl) : null;
            expect(feedData, 'Alpine profileFeed data').to.exist;

            feedData.posts.unshift({
                post_no: 9999992,
                id: feedData.currentUserId || feedData.loggedInUserId || 'test_user',
                is_own_post: true,
                fullName: 'E2E Link Tester',
                profileImg: '/public/avatar/avatarM.png',
                postMessage: 'https://example.com/family-reunion',
                displayMessage: 'https://example.com/family-reunion',
                post_time: new Date().toISOString(),
                images: [],
                videos: [],
                comments: [],
                post_likes: 0,
                views_count: 1,
                link_preview: {
                    url: 'https://example.com/family-reunion',
                    original_url: 'https://example.com/family-reunion',
                    domain: 'example.com',
                    title: 'Annual Family Reunion 2026',
                    description: 'Join us for the summer gathering.',
                },
            });
        });

        cy.get('#post9999992 .link-preview-card', { timeout: 6000 }).should('be.visible').and('contain.text', 'Annual Family Reunion 2026');
        cy.get('#post9999992 [data-testid="infeed-stage-converter"]').should('not.exist');
        cy.get('#post9999992 .dropdown-menu').should('not.contain.text', 'Translate post');
    });

    it('4. Mobile 390x844: stage converter pills wrap cleanly without horizontal clipping', () => {
        cy.viewport(390, 844);
        loginFully();
        cy.visit('/profilePage');
        cy.get('.feed-post-card', { timeout: 12000 }).should('have.length.greaterThan', 0);

        cy.window().then((win) => {
            const feedEl = win.document.querySelector('[x-data*="profileFeed"]');
            const feedData = feedEl && win.Alpine ? win.Alpine.$data(feedEl) : null;
            expect(feedData, 'Alpine profileFeed data').to.exist;

            feedData.posts.unshift({
                post_no: 9999993,
                id: feedData.currentUserId || feedData.loggedInUserId || 'test_user',
                is_own_post: true,
                fullName: 'E2E Mobile Tester',
                profileImg: '/public/avatar/avatarM.png',
                postMessage: 'Hello family from mobile viewport!',
                displayMessage: 'Hello family from mobile viewport!',
                post_time: new Date().toISOString(),
                images: [],
                videos: [],
                comments: [],
                post_likes: 0,
                views_count: 1,
            });
        });

        cy.get('#post9999993 .fp-stage-converter__row', { timeout: 6000 }).should('be.visible').then(($row) => {
            const el = $row[0];
            expect(el.scrollWidth).to.be.at.most(el.clientWidth + 2);
        });

        runAuditOnPage((report) => {
            const mobileViolations = report.violations.filter(
                (v) => v.rule === 'PILL_BAR_CLIPPED' || v.rule === 'HORIZONTAL_OVERFLOW'
            );
            expect(mobileViolations, JSON.stringify(mobileViolations)).to.have.length(0);
        });
    });
});
